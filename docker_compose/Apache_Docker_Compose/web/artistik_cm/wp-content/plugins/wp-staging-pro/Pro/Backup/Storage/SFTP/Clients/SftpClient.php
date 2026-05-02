<?php

namespace WPStaging\Pro\Backup\Storage\SFTP\Clients;

use Exception;
use WPStaging\Backup\Exceptions\StorageException;
use WPStaging\Backup\WithBackupIdentifier;
use WPStaging\Vendor\phpseclib3\Crypt\PublicKeyLoader;
use WPStaging\Vendor\phpseclib3\Net\SFTP;
use WPStaging\Vendor\phpseclib3\Net\SSH2;
use WPStaging\Vendor\phpseclib3\Math\BigInteger;

use function WPStaging\functions\debug_log;

class SftpClient extends AbstractClient
{
    use WithBackupIdentifier;

    /** @var SFTP */
    protected $sftp;

    /** @var string */
    protected $hostname;

    /** @var int */
    protected $port;

    /** @var string */
    protected $username;

    /** @var string */
    protected $password;

    /** @var string */
    protected $key;

    /** @var string */
    protected $passphrase;

    /** @var string|false */
    protected $error;

    /** @var bool */
    private $isBadKey;

    /** @var bool */
    private $isLogin;

    /** @var string */
    protected $path;

    /** @var string */
    protected $defaultPath = '';

    /** @var string */
    protected $fingerprint = '';

    /** @var string */
    protected $observedFingerprint = '';

    public function __construct(string $hostname, string $username, string $password, string $key, string $passphrase, int $port, string $fingerprint = '')
    {
        $this->username    = $username;
        $this->password    = $password;
        $this->passphrase  = $passphrase;
        $this->hostname    = $hostname;
        $this->port        = $port;
        $this->isLogin     = false;
        $this->key         = $key;
        $this->fingerprint = $this->normalizeFingerprint($fingerprint);

        if (!defined('NET_SFTP_LOGGING')) {
            define('NET_SFTP_LOGGING', defined('WP_DEBUG') && WP_DEBUG ? SSH2::LOG_SIMPLE : 0);
        }

        if (!defined('NET_SSH2_LOGGING')) {
            define('NET_SSH2_LOGGING', defined('WP_DEBUG') && WP_DEBUG ? SSH2::LOG_SIMPLE : 0);
        }
    }

    /**
     * @return void
     */
    public function setPath(string $path)
    {
        $this->path = $path;
    }

    public function getDefaultPath(): string
    {
        return $this->defaultPath;
    }

    /**
     * @return void
     */
    public function setMode(int $mode)
    {
        // No-op
    }

    public function connect(): bool
    {
        $this->setBigIntegerEngine();
        $this->sftp = $this->createSftpConnection();

        if (!$this->verifyServerFingerprint()) {
            return false;
        }

        // SSH key takes priority when available
        if (!empty($this->key)) {
            if ($this->authenticateWithKey()) {
                return true;
            }

            // Key auth failed — fall back to password if available
            if (!empty($this->password)) {
                return $this->authenticateWithPassword();
            }

            return false;
        }

        if (!empty($this->password)) {
            return $this->authenticateWithPassword();
        }

        return false;
    }

    /**
     * Retrieve and cache the current server host fingerprint without authenticating.
     * This supports explicit TOFU confirmation before credentials are used.
     */
    public function fetchServerFingerprint(): string
    {
        if (!$this->sftp) {
            $this->setBigIntegerEngine();
            $this->sftp = $this->createSftpConnection();
        }

        try {
            $publicKey = $this->sftp->getServerPublicHostKey();
        } catch (Exception $e) {
            $this->error = 'Unable to retrieve server public host key.';
            debug_log('Error: ' . $e->getMessage());
            return '';
        }

        $serverFingerprint = $this->calculateFingerprintFromPublicKey(is_string($publicKey) ? $publicKey : '');
        if ($serverFingerprint === '') {
            $this->error = 'Unable to calculate server host key fingerprint.';
            return '';
        }

        $this->observedFingerprint = $serverFingerprint;
        return $this->observedFingerprint;
    }

    public function getObservedFingerprint(): string
    {
        return $this->observedFingerprint;
    }

    protected function createSftpConnection()
    {
        $connection = new SFTP($this->hostname, $this->port, 30);
        $this->configurePreferredAlgorithms($connection);
        return $connection;
    }

    /** @return void */
    protected function configurePreferredAlgorithms($connection)
    {
        if (!is_object($connection) || !method_exists($connection, 'setPreferredAlgorithms')) {
            return;
        }

        $preferredAlgorithms = [
            'kex'                  => [
                'curve25519-sha256',
                'curve25519-sha256@libssh.org',
                'diffie-hellman-group16-sha512',
                'diffie-hellman-group14-sha256',
            ],
            'hostkey'              => [
                'ssh-ed25519',
                'ecdsa-sha2-nistp256',
                'ecdsa-sha2-nistp384',
                'ecdsa-sha2-nistp521',
                'rsa-sha2-512',
                'rsa-sha2-256',
            ],
            'client_to_server'     => [
                'aes256-gcm@openssh.com',
                'aes128-gcm@openssh.com',
                'aes256-ctr',
                'aes192-ctr',
                'aes128-ctr',
            ],
            'server_to_client'     => [
                'aes256-gcm@openssh.com',
                'aes128-gcm@openssh.com',
                'aes256-ctr',
                'aes192-ctr',
                'aes128-ctr',
            ],
            'client_to_server_mac' => [
                'hmac-sha2-512-etm@openssh.com',
                'hmac-sha2-256-etm@openssh.com',
                'hmac-sha2-512',
                'hmac-sha2-256',
            ],
            'server_to_client_mac' => [
                'hmac-sha2-512-etm@openssh.com',
                'hmac-sha2-256-etm@openssh.com',
                'hmac-sha2-512',
                'hmac-sha2-256',
            ],
        ];

        $connection->setPreferredAlgorithms($preferredAlgorithms);
    }

    protected function verifyServerFingerprint(): bool
    {
        if (empty($this->fingerprint)) {
            return true;
        }

        $expectedFingerprint = $this->normalizeFingerprint($this->fingerprint);
        if ($expectedFingerprint === '') {
            $this->error = 'SSH host key verification is required. Provide a host fingerprint or confirm and trust the current host key first.';
            return false;
        }

        $serverFingerprint = $this->fetchServerFingerprint();
        if ($serverFingerprint === '') {
            if (empty($this->error)) {
                $this->error = 'Unable to retrieve server public host key.';
            }

            return false;
        }

        if (!hash_equals($expectedFingerprint, $serverFingerprint)) {
            $this->error = sprintf(
                'Server fingerprint mismatch. Expected %s but received %s. Connection aborted to prevent a possible man-in-the-middle attack.',
                $expectedFingerprint,
                $serverFingerprint
            );
            debug_log('Error: ' . $this->error);
            return false;
        }

        return true;
    }

    protected function authenticateWithPassword(): bool
    {
        try {
            return $this->sftp->login($this->username, $this->password);
        } catch (Exception $e) {
            debug_log("Error: " . $e->getMessage());
            return false;
        }
    }

    protected function authenticateWithKey(): bool
    {
        $key = '';
        try {
            $key = PublicKeyLoader::load(trim($this->key), empty($this->passphrase) ? false : $this->passphrase);
        } catch (Exception $e) {
            $this->isBadKey = true;
            debug_log("Error: " . $e->getMessage());
            return false;
        }

        try {
            return $this->sftp->login($this->username, $key);
        } catch (Exception $e) {
            debug_log("Error: " . $e->getMessage());
            return false;
        }
    }

    protected function calculateFingerprintFromPublicKey(string $publicKey): string
    {
        if ($publicKey === '') {
            return '';
        }

        $parts = preg_split('/\s+/', trim($publicKey));
        $keyBase64 = '';

        if (is_array($parts)) {
            foreach ($parts as $part) {
                if (preg_match('/^[A-Za-z0-9+\/]+=*$/', $part)) {
                    $keyBase64 = $part;
                    break;
                }
            }
        }

        $keyBinary = $keyBase64 !== '' ? base64_decode($keyBase64, true) : false;
        if ($keyBinary === false) {
            $keyBinary = $publicKey;
        }

        if ($keyBinary === '') {
            return '';
        }

        $sha256 = hash('sha256', $keyBinary, true);
        return $this->normalizeFingerprint('SHA256:' . base64_encode($sha256));
    }

    protected function normalizeFingerprint(string $fingerprint): string
    {
        $fingerprint = trim($fingerprint);
        if ($fingerprint === '') {
            return '';
        }

        $fingerprintFromKnownHosts = $this->extractFingerprintFromKnownHosts($fingerprint);
        if ($fingerprintFromKnownHosts !== '') {
            return $fingerprintFromKnownHosts;
        }

        $fingerprint = preg_replace('/\s+/', '', $fingerprint);
        if (stripos($fingerprint, 'SHA256:') === 0) {
            $fingerprint = substr($fingerprint, 7);
        }

        $paddedFingerprint = $fingerprint;
        $paddingLength     = strlen($paddedFingerprint) % 4;
        if ($paddingLength > 0) {
            $paddedFingerprint .= str_repeat('=', 4 - $paddingLength);
        }

        $decodedFingerprint = base64_decode($paddedFingerprint, true);
        if ($decodedFingerprint !== false) {
            return 'SHA256:' . rtrim(base64_encode($decodedFingerprint), '=');
        }

        return 'SHA256:' . rtrim($fingerprint, '=');
    }

    protected function extractFingerprintFromKnownHosts(string $input): string
    {
        $parts = preg_split('/\s+/', trim($input));
        if (!is_array($parts) || count($parts) < 3) {
            return '';
        }

        $keyBase64 = '';
        $count     = count($parts);

        for ($index = 0; $index < $count - 1; $index++) {
            if (preg_match('/^(ssh-|ecdsa-|sk-)/', $parts[$index])) {
                $keyBase64 = $parts[$index + 1] ?? '';
                break;
            }
        }

        if ($keyBase64 === '') {
            return '';
        }

        $keyBinary = base64_decode($keyBase64, true);
        if ($keyBinary === false) {
            return '';
        }

        $sha256 = hash('sha256', $keyBinary, true);
        return 'SHA256:' . rtrim(base64_encode($sha256), '=');
    }

    public function login(int $retry = 3): bool
    {
        if ($this->isLogin) {
            return true;
        }

        $result = $this->connect();

        if ($result === true) {
            $this->isLogin = true;
            $this->defaultPath = ($this->sftp && ($currentPath = $this->sftp->pwd()) !== false) ? $currentPath : '';
            return true;
        }

        if (!empty($this->error)) {
            $this->isLogin = false;
            return false;
        }

        if ($this->isBadKey) {
            $this->error = 'The key appears to be invalid or corrupted. Please provide a valid key.';
            return false;
        }

        $this->isLogin = false;
        if (!$this->sftp || !$this->sftp->isConnected()) {
            $this->error = "Unable to connect to SFTP server at {$this->hostname}:{$this->port}. Host might be unreachable.";
            debug_log("Error: " . $this->error);
            return false;
        }

        if (!$this->sftp->isAuthenticated()) {
            $this->error = "Unable to login to SFTP server. ";
            debug_log("Error: " . $this->error);
            if ($this->isBadKey) {
                $this->error .= ' - Either the passphrase or key provided is not correct. ';
                debug_log("Error: " . $this->error);
            }

            return false;
        }

        $this->error = "Error: Unable to login via sFTP. Unknown error.";
        debug_log("Error: Unable to login via sFTP. Unknown error.");
        return false;
    }

    public function upload(string $remotePath, string $file, string $chunk, int $offset = 0): bool
    {
        if (!$this->sftp->isConnected()) {
            $this->connect();
        }

        $handle = fopen('php://temp', 'wb+');
        if (!$handle) {
            return false;
        }

        if (fwrite($handle, $chunk)) {
            rewind($handle);
        }

        if (!empty($remotePath) && !$this->isDirectoryChanged($remotePath)) {
            $file = trailingslashit($remotePath) . $file;
        }

        if ($offset === 0) {
            $this->makeDirectory($remotePath);
        }

        $result = false;
        try {
            $result = $this->sftp->put($file, $handle, SFTP::SOURCE_LOCAL_FILE | SFTP::RESUME_START, $offset);
        } catch (Exception $e) {
            debug_log("Error: " . $e->getMessage());
        }

        fclose($handle);

        return $result;
    }

    public function nonBlockingUpload(string $remoteFile, string $localFile, int $offset = 0): int
    {
        return 0;
    }

    /**
     * @return void
     */
    public function close()
    {
        $this->isLogin = false;
        if ($this->sftp !== null) {
            $this->sftp->disconnect();
        }
    }

    public function getError(): string
    {
        return $this->error ?: '';
    }

    /**
     * @throws StorageException
     */
    public function getFiles(string $path): array
    {
        if (!$this->sftp->isConnected()) {
            $this->connect();
        }

        if ($this->isDirectoryChanged($path)) {
            $path = '.';
        }

        try {
            $items = @$this->sftp->rawlist($path);
        } catch (Exception $ex) {
            $this->error = $ex->getMessage();
            throw new StorageException($this->error);
        }

        if (empty($items)) {
            $this->error .= "Could not upload backup via SFTP to " . $path . " Does the folder exist on the remote server? ";
            $this->error .= "The backup is still available on the local file system.";
            throw new StorageException($this->error);
        }

        $files = [];
        foreach ($items as $file) {
            if ($file['type'] !== 1) {
                continue;
            }

            $files[] = [
                'name' => $file['filename'],
                'time' => $file['mtime'],
                'size' => $file['size'],
            ];
        }

        uasort($files, function ($file1, $file2) {
            return $file1['time'] < $file2['time'] ? -1 : 1;
        });

        return array_values($files);
    }

    public function deleteFile(string $path): bool
    {
        try {
            return @$this->sftp->delete($path);
        } catch (Exception $ex) {
            return false;
        }
    }

    public function getIsSupportNonBlockingUpload(): bool
    {
        return false;
    }

    public function downloadAsChunks(string $backupPath, string $filePath, string $fileName, int $chunkStart, int $chunkSize): bool
    {
        try {
            $output = $this->sftp->get($backupPath . $fileName, false, $chunkStart, $chunkSize);
            if ($output === false) {
                return false;
            }

            $fileHandle = fopen($filePath, 'a+');
            if ($fileHandle === false) {
                return false;
            }

            $bytesWritten = fwrite($fileHandle, $output);
            if ($bytesWritten === false || $bytesWritten !== strlen($output)) {
                fclose($fileHandle);
                return false;
            }

            fclose($fileHandle);
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    public function directoryExists(string $directory): bool
    {
        try {
            return $this->sftp->is_dir($directory);
        } catch (Exception $e) {
            debug_log("SFTP: Error checking if directory exists {$directory}: " . $e->getMessage());
            return false;
        }
    }

    protected function maybeLogin(): bool
    {
        if (!$this->sftp->isConnected()) {
            return $this->connect();
        }

        return true;
    }

    protected function isDirectoryChanged(string $path): bool
    {
        $path = rtrim($path);
        $path = untrailingslashit($path);
        $currentPath = $this->sftp->pwd();
        if (empty($path)) {
            return false;
        }

        if ('/' . $path === $currentPath) {
            return true;
        }

        return $this->sftp->chdir($path);
    }

    /**
     * By default, phpseclib uses the following order for engines for BigInteger calculations.
     * 1. GMP
     * 2. PHP64
     * 3. BCMath
     * 4. PHP32
     *
     * But during our testing BCMath performs better than PHP64 and PHP32.
     */
    protected function setBigIntegerEngine()
    {
        // if gmp extension is installed then use default preference
        if (extension_loaded('gmp')) {
            return;
        }

        // if bcmath extension is not installed then use default preference
        if (!extension_loaded('bcmath')) {
            return;
        }

        try {
            BigInteger::setEngine('BCMath', ["OpenSSL"]);
        } catch (Exception $e) {
            BigInteger::setEngine('BCMath', ["DefaultEngine"]);
        }
    }

    protected function createDirectory(string $directory): bool
    {
        try {
            $result = $this->sftp->mkdir($directory, 0755, false);
            if (!$result) {
                debug_log("SFTP: Error creating directory: {$directory}");
            }

            return $result;
        } catch (Exception $e) {
            debug_log("SFTP: Error creating directory {$directory}: " . $e->getMessage());
            return false;
        }
    }

    protected function getClientType(): string
    {
        return 'SFTP';
    }

    protected function removeDirectory(string $directory): bool
    {
        if (!$this->sftp->isConnected()) {
            $this->connect();
        }

        try {
            return @$this->sftp->rmdir($directory);
        } catch (Exception $ex) {
            debug_log("Error removing SFTP directory {$directory}: " . $ex->getMessage());
            return false;
        }
    }
}
