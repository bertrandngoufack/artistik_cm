<?php

namespace WPStaging\Pro\Backup\Storage\SFTP\Clients;

use Exception;
use WPStaging\Backup\Exceptions\StorageException;
use WPStaging\Framework\Facades\Sanitize;

use function WPStaging\functions\debug_log;

class FtpCurlClient extends AbstractClient
{
    /** @var resource|null */
    protected $handler = null;

    /** @var string */
    protected $hostname;

    /** @var string */
    protected $username;

    /** @var string */
    protected $password;

    /** @var bool */
    protected $passive;

    /** @var bool */
    protected $ssl;

    /** @var int */
    protected $port;

    /** @var string|false */
    protected $error;

    /** @var string */
    protected $path;

    /** @var string */
    protected $defaultPath = '';

    /** @var int */
    protected $httpCode;

    /** @var string|null */
    protected $certificatePath;

    /** @var bool */
    protected $verifyCertificate;

    /** @var bool */
    protected $allowInsecure;

    /**
     * @throws FtpException
     */
    public function __construct(string $hostname, string $username, string $password, bool $ssl, bool $passive, int $port, $certificatePath = null, bool $verifyCertificate = true, bool $allowInsecure = false)
    {
        if (!extension_loaded('curl')) {
            throw new FtpException("PHP cURL extension not loaded");
        }

        $this->hostname          = $hostname;
        $this->username          = $username;
        $this->password          = $password;
        $this->port              = $port;
        $this->passive           = $passive;
        $this->ssl               = $ssl;
        $this->certificatePath   = $certificatePath;
        $this->verifyCertificate = $verifyCertificate;
        $this->allowInsecure     = $allowInsecure;
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

    public function login(int $retry = 3): bool
    {
        $this->error = '';
        try {
            $this->sendCurlRequest("", [
                CURLOPT_TIMEOUT => 120,
            ]);
        } catch (Exception $ex) {
            debug_log('FTP CURL error, login');
            $this->error = $ex->getMessage();
            return false;
        }

        if (!empty($this->error)) {
            debug_log('FTP CURL login error');
            return false;
        }

        return true;
    }

    public function upload(string $remotePath, string $file, string $chunk, int $offset = 0): bool
    {
        $remotePath = Sanitize::sanitizeRemotePath($remotePath);
        $handle = fopen('php://temp', 'wb+');
        if (!$handle) {
            return false;
        }

        if (($fileSize = fwrite($handle, $chunk))) {
            rewind($handle);
        }

        $curlOptions = [
            CURLOPT_UPLOAD     => true,
            CURLOPT_FTPAPPEND  => true,
            CURLOPT_INFILE     => $handle,
            CURLOPT_INFILESIZE => $fileSize,
        ];

        if ($remotePath !== '') {
            $remotePath = trailingslashit($remotePath);
        }

        if ($offset === 0) {
            $this->makeDirectory($remotePath);
        }

        $this->error = '';
        try {
            $this->sendCurlRequest($remotePath . $file, $curlOptions);
        } catch (Exception $ex) {
            $this->error = $ex->getMessage();
            debug_log("FTP CURL upload error: " . $this->error);
        }

        fclose($handle);

        // return true when no error
        if (!empty($this->error)) {
            debug_log("FTP CURL upload failed with error: " . $this->error);
        }

        return empty($this->error);
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
        // CurlHandle is only available from PHP 8
        // @phpstan-ignore-next-line
        if (class_exists('\CurlHandle') && ($this->handler instanceof \CurlHandle)) {
            curl_close($this->handler);
            $this->handler = null;
            return;
        }

        if (is_resource($this->handler)) {
            curl_close($this->handler);
            $this->handler = null;
        }
    }

    /**
     * @throws StorageException
     */
    public function getFiles(string $path): array
    {
        $path = Sanitize::sanitizeRemotePath($path);
        $this->error = '';
        try {
            $response = $this->sendCurlRequest(sprintf('/%s/', $path), [
                CURLOPT_CUSTOMREQUEST => 'LIST -tr',
            ]);
        } catch (Exception $ex) {
            $this->error = $ex->getMessage();
            throw new StorageException($ex->getMessage());
        }

        $items = explode(PHP_EOL, $response);
        $files = [];
        foreach ($items as $item) {
            if (empty($item)) {
                continue;
            }

            $metas = preg_split('/\s+/', trim($item));

            if (substr($metas[0], 0, 1) === 'd') {
                continue;
            }

            $fileName = empty($metas[count($metas) - 1]) ? '' : $metas[count($metas) - 1];

            preg_match('/_(\d{8})-(\d{6})_/', $fileName, $matches);
            if (isset($matches[1]) && isset($matches[2])) {
                $dateTime = $matches[1] . $matches[2];
                $timestamp = (new \DateTime($dateTime))->getTimestamp();
            }

            $files[] = [
                'time' => empty($timestamp) ? null : $timestamp,
                'name' => $fileName,
                'size' => isset($metas[4]) ? (int)$metas[4] : null,
            ];
        }

        return $files;
    }

    public function deleteFile(string $path): bool
    {
        $this->error = '';
        $curlPath    = '';

        try {
            $response = $this->sendCurlRequest($curlPath, [
                CURLOPT_QUOTE => [
                    sprintf('DELE /%s', $path)
                ],
            ]);
            if ($response) {
                return true;
            }
        } catch (Exception $ex) {
            $this->error = $ex->getMessage();
            return false;
        }

        return empty($this->error);
    }

    public function getError(): string
    {
        return $this->error ?: '';
    }

    public function getIsSupportNonBlockingUpload(): bool
    {
        return false;
    }

    public function downloadAsChunks(string $backupPath, string $filePath, string $fileName, int $chunkStart, int $chunkSize): bool
    {
        $chunkEnd = $chunkStart + $chunkSize - 1;
        $curlPath = $this->path . $fileName;
        $curlOptions = [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RANGE          => sprintf('%d-%d', $chunkStart, $chunkEnd),
        ];
        try {
            $response = $this->sendCurlRequest($curlPath, $curlOptions);
            if ($response === false) {
                $this->error = 'Unable to download file';
                return false;
            }

            $fileHandle = fopen($filePath, 'a+');
            if ($fileHandle === false) {
                $this->error = 'Unable to open file for writing';
                return false;
            }

            $bytesWritten = fwrite($fileHandle, $response);
            if ($bytesWritten === false || $bytesWritten !== strlen($response)) {
                fclose($fileHandle);
                $this->error = 'Unable to write to file';
                return false;
            }

            fclose($fileHandle);
            return true;
        } catch (Exception $ex) {
            $this->error = $ex->getMessage();
            return false;
        }
    }

    /**
     * @return string|bool
     */
    protected function sendCurlRequest(string $path, array $options = [])
    {
        // @phpstan-ignore-next-line
        $this->handler = curl_init();

        // Set FTP URL
        curl_setopt($this->handler, CURLOPT_URL, $this->buildFtpUrl($path));

        // Set username and password
        curl_setopt($this->handler, CURLOPT_USERPWD, sprintf('%s:%s', $this->username, $this->password));

        // Set default configuration
        curl_setopt($this->handler, CURLOPT_HEADER, false);
        curl_setopt($this->handler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handler, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($this->handler, CURLOPT_TIMEOUT, 0);

        if ($this->ssl) {
            $this->configureSslVerification();
            curl_setopt($this->handler, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
            curl_setopt($this->handler, CURLOPT_FTPSSLAUTH, CURLFTPAUTH_TLS);
        }

        // Is passive
        if ($this->passive) {
            curl_setopt($this->handler, CURLOPT_FTP_USE_EPSV, true);
        } else {
            curl_setopt($this->handler, CURLOPT_FTP_USE_EPRT, true);
            curl_setopt($this->handler, CURLOPT_FTPPORT, 0);
        }

        // Apply cURL options
        foreach ($options as $name => $value) {
            curl_setopt($this->handler, $name, $value);
        }

        // HTTP request
        $response = curl_exec($this->handler);
        if ($response !== false) {
            $this->handleHttpCodeError();

            $effectiveUrl = curl_getinfo($this->handler, CURLINFO_EFFECTIVE_URL);
            $parsedUrl = parse_url($effectiveUrl);
            if (isset($parsedUrl['path'])) {
                $this->defaultPath = $parsedUrl['path'];
            }

            $this->close();

            return $response;
        }

        $errno = curl_errno($this->handler);
        if (!$errno) {
            $this->handleHttpCodeError();

            $this->close();

            return $response;
        }

        switch ($errno) {
            case 6:
            case 7:
                $this->error = esc_html__("Unable to connect to FTP server. Please check your FTP host and port.", 'wp-staging');
                break;
            case 9:
                $this->error = esc_html__("Unable to connect to FTP server. Please check your FTP permissions.", 'wp-staging');
                break;
            case 25:
                $this->error = esc_html__("Failed to upload file to FTP server. Check write permissions and the target path.", 'wp-staging');
                break;
            case 28:
                $this->error = esc_html__("Unable to connect to FTP server. Server timeout. Please verify FTP settings.", 'wp-staging');
                break;
            case 51:
                $this->error = esc_html__("SSL peer certificate verification failed. The server may be using a self-signed certificate. SSL verification is disabled for FTPS connections.", 'wp-staging');
                break;
            case 60:
                $this->error = esc_html__("SSL certificate verification failed. The server may be using a self-signed or invalid certificate. SSL verification is disabled for FTPS connections.", 'wp-staging');
                break;
            case 67:
                $this->error = esc_html__("Unable to login to FTP server. Please check your credentials.", 'wp-staging');
                break;
            default:
                $this->error = sprintf(esc_html__("Unable to connect to FTP server. Error code: %s", 'wp-staging'), $errno);
        }

        $this->httpCode = curl_getinfo($this->handler, CURLINFO_HTTP_CODE);

        $this->close();

        return $response;
    }

    protected function handleHttpCodeError()
    {
        $this->httpCode = curl_getinfo($this->handler, CURLINFO_HTTP_CODE);
        if ($this->httpCode === 429) {
            $this->error = esc_html__("FTP Curl Client - Too many requests!", 'wp-staging');
        }

        if ($this->httpCode >= 500) {
            $this->error = esc_html__("FTP Curl Client - Internal Server Error", 'wp-staging');
        } elseif ($this->httpCode >= 400) {
            $this->error = sprintf(esc_html__("FTP Curl Client - Error code: %s", 'wp-staging'), $this->httpCode);
        }
    }

    public function directoryExists(string $directory): bool
    {
        $previousError = $this->error;
        $this->error   = '';
        $ftpPath       = $this->normalizeDirectoryPath($directory);

        // Try to change to the directory - if successful, it exists
        $curlOptions = [
            CURLOPT_QUOTE  => [
                sprintf('CWD %s', $ftpPath),
                'PWD', // Print working directory to confirm
            ],
            CURLOPT_NOBODY => true,
        ];

        try {
            $this->sendCurlRequest('', $curlOptions);
            $exists = empty($this->error);
            // Restore previous error only if directory check failed
            if (!$exists) {
                $this->error = $previousError;
            }

            return $exists;
        } catch (Exception $e) {
            // Directory doesn't exist or can't be accessed
            $this->error = $previousError;
            return false;
        }
    }

    protected function maybeLogin(): bool
    {
        return true;
    }

    protected function createDirectory(string $directory): bool
    {
        $this->error = '';
        $ftpPath     = $this->normalizeDirectoryPath($directory);

        $curlOptions = [
            CURLOPT_QUOTE  => [
                sprintf('MKD %s', $ftpPath)
            ],
            CURLOPT_NOBODY => true,
        ];

        try {
            $this->sendCurlRequest('', $curlOptions);
            if (!empty($this->error)) {
                debug_log("FTP CURL: Warning creating directory {$directory}: " . $this->error);
                $this->error = '';
                return false;
            }

            return true;
        } catch (Exception $e) {
            debug_log("FTP CURL: Exception creating directory {$directory}: " . $e->getMessage());
            return false;
        }
    }

    protected function getClientType(): string
    {
        return 'FTP CURL';
    }

    protected function removeDirectory(string $directory): bool
    {
        $this->error = '';
        $ftpPath     = $this->normalizeDirectoryPath($directory);

        $curlOptions = [
            CURLOPT_QUOTE  => [
                sprintf('RMD %s', $ftpPath)
            ],
            CURLOPT_NOBODY => true,
        ];

        try {
            $this->sendCurlRequest('', $curlOptions);
            if (!empty($this->error)) {
                debug_log("FTP CURL: Warning removing directory {$directory}: " . $this->error);
                $this->error = '';
                return false;
            }

            return true;
        } catch (Exception $e) {
            debug_log("FTP CURL: Exception removing directory {$directory}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Configure SSL verification for the cURL handle.
     *
     * Priority:
     * 1. If allowInsecure or verifyCertificate is off → disable verification.
     * 2. If a custom certificate file was provided → use it.
     * 3. Otherwise → use the system CA bundle (curl default).
     *
     * @return void
     */
    protected function configureSslVerification()
    {
        if ($this->allowInsecure || !$this->verifyCertificate) {
            $this->disableSslVerification();
            return;
        }

        // Enable peer + host verification
        curl_setopt($this->handler, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($this->handler, CURLOPT_SSL_VERIFYHOST, 2);

        // Use custom certificate if provided, otherwise let curl use the system CA bundle
        if ($this->hasValidCertificate()) {
            curl_setopt($this->handler, CURLOPT_CAINFO, $this->certificatePath);
        }
    }

    protected function hasValidCertificate(): bool
    {
        if (empty($this->certificatePath) || !file_exists($this->certificatePath) || !is_readable($this->certificatePath)) {
            return false;
        }

        return true;
    }

    /** @return void */
    protected function disableSslVerification()
    {
        curl_setopt($this->handler, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->handler, CURLOPT_SSL_VERIFYHOST, 0);
    }

    /**
     * Build the FTP URL with an encoded hostname to prevent URL injection.
     *
     * @param string $path
     * @return string
     */
    protected function buildFtpUrl(string $path): string
    {
        return sprintf('ftp://%s:%d/%s', rawurlencode($this->hostname), $this->port, $path);
    }

    private function normalizeDirectoryPath(string $directory): string
    {
        return strpos($directory, '/') === 0 ? $directory : '/' . ltrim($directory, '/');
    }
}
