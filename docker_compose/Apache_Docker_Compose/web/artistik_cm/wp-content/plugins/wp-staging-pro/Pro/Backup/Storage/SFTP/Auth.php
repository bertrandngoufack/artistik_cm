<?php

namespace WPStaging\Pro\Backup\Storage\SFTP;

use WPStaging\Core\WPStaging;
use WPStaging\Backup\Exceptions\StorageException;
use WPStaging\Framework\Security\Auth as WPStagingAuth;
use WPStaging\Pro\Backup\Storage\AbstractStorage;
use WPStaging\Framework\Utils\Sanitize;
use WPStaging\Framework\Utils\Strings;
use WPStaging\Pro\Backup\Storage\SFTP\Clients\AbstractClient;
use WPStaging\Pro\Backup\Storage\SFTP\Clients\FtpClient;
use WPStaging\Pro\Backup\Storage\SFTP\Clients\FtpCurlClient;
use WPStaging\Pro\Backup\Storage\SFTP\Clients\FtpException;
use WPStaging\Pro\Backup\Storage\SFTP\Clients\SftpClient;

use function WPStaging\functions\debug_log;

class Auth extends AbstractStorage
{
    /** @var string */
    const FOLDER_NAME = 'wpstaging-backups';

    /** @var string */
    const CONNECTION_TYPE_FTP  = 'ftp';

    /** @var string */
    const CONNECTION_TYPE_SFTP = 'sftp';

    /** @var int */
    const FTP_UPLOAD_MODE_PUT          = 1;

    /** @var int */
    const FTP_UPLOAD_MODE_APPEND       = 2;

    /** @var int */
    const FTP_UPLOAD_MODE_NON_BLOCKING = 3;

    /** @var Sanitize */
    protected $sanitize;

    /** @var AbstractClient */
    protected $client;

    /** @var string Temp file path for FTP certificate (cleaned up after use) */
    private $tempCertPath = '';

    public function __construct(WPStagingAuth $wpstagingAuth, Sanitize $sanitize)
    {
        parent::__construct($wpstagingAuth);
        $this->identifier  = 'sftp';
        $this->label       = 'FTP / SFTP';
        $this->sanitize    = $sanitize;
    }

    /**
     * Ensure the temporary certificate file is always cleaned up,
     * even when getClient() is called directly (e.g. during backup uploads)
     * without going through testConnection()/authenticate() which have
     * explicit finally blocks.
     */
    public function __destruct()
    {
        $this->cleanupTempCertFile();
    }

    public function authenticate()
    {
        // no-op
    }

    /**
     * @return bool
     */
    public function testConnection()
    {
        $options = $this->sanitizeData($_POST);

        if ($options['ftpType'] === self::CONNECTION_TYPE_SFTP) {
            $this->readUploadedFileIntoOptions($options, 'key_file', 'key', 'SSH key upload');
        }

        if ($options['ftpType'] === self::CONNECTION_TYPE_FTP) {
            $this->readUploadedFileIntoOptions($options, 'ftp-certificate-file', 'ftpCertContent', 'FTP certificate upload');
        }

        $client  = false;
        $result  = false;

        try {
            $client = $this->getClient($options);
            if ($client === false) {
                return false;
            }

            $result = $client->login();
            if ($result === false) {
                $this->error = $client->getError();
                debug_log("(Test Connection) Error: " . $this->error);
                return $result;
            }

            $client->setMode($options['ftpMode']);
            $result = $this->isWriteableStoragePath($client, $options['location']);
            if ($result === false) {
                $this->error .= ' ' . sprintf(
                    esc_html__('Could not write to the remote folder "%s". Make sure the path exists on the server and the user has write permission. Use an absolute path like /home/user/backups if a relative path does not work.', 'wp-staging'),
                    esc_html($options['location'])
                );
                debug_log("(Test Connection) Backup path does not exist or has no write permission! Full path: " . $options['location']);
            }

            return $result;
        } finally {
            if ($client !== false) {
                $client->close();
            }

            $this->cleanupTempCertFile();
        }

        return $result;
    }

    /**
     * @param array $options Optional
     *
     * @return AbstractClient|false
     */
    public function getClient($options = null)
    {
        if ($options === null) {
            $options = $this->getOptions();
        }

        if (empty($options['host']) || empty($options['port']) || empty($options['username'])) {
            $this->error = esc_html__('Host, port, and username cannot be empty.', 'wp-staging');
            debug_log("Host, port, and username cannot be empty.");
            return false;
        }

        if ($options['ftpType'] === 'ftp' && empty($options['password'])) {
            $this->error = esc_html__('For FTP, password cannot be empty.', 'wp-staging');
            debug_log("For FTP, password cannot be empty.");
            return false;
        }

        $sshKeyContent = '';
        if ($options['ftpType'] === 'sftp') {
            $useSshKey = !array_key_exists('useSshKey', $options) || !empty($options['useSshKey']);
            if ($useSshKey) {
                $sshKeyContent = $this->getCredentialValue($options, 'WPSTG_STORAGE_SFTP_KEY', 'key');
            }

            if (empty($options['password']) && empty($sshKeyContent)) {
                $this->error = esc_html__('For SFTP, either password or key must be provided.', 'wp-staging');
                debug_log("For SFTP, either password or key must be provided.");
                return false;
            }
        }

        if ($options['ftpType'] === self::CONNECTION_TYPE_SFTP) {
            return new SftpClient($options['host'], $options['username'], $options['password'] ?: '', $sshKeyContent, $options['passphrase'] ?? '', $options['port'], $options['fingerprint'] ?? '');
        }

        $useFtpExtension = array_key_exists('useFtpExtension', $options) ? $options['useFtpExtension'] : false;
        $useFtpExtension = apply_filters_deprecated(
            'wpstg.ftpclient.forceUseFtpExtension', // filter name
            [$useFtpExtension], // args including the default value
            '5.3.1', // version from which it is deprecated
            '', // new filter to use i.e. none in this case as we will remove this filter
            esc_html__('This filter will be removed in the upcoming version, use the option provided in FTP settings UI instead.', 'wp-staging')
        );
        if ($useFtpExtension === false) {
            try {
                $certPath  = $this->writeCertToTempFile($this->getCredentialValue($options, 'WPSTG_STORAGE_FTPS_CERT', 'ftpCertContent'));
                $verifyCert = $options['verifyCert'] ?? true;
                $ftpClient = new FtpCurlClient($options['host'], $options['username'], $options['password'], $options['ssl'], $options['passive'], $options['port'], $certPath ?: null, $verifyCert, !$verifyCert);
                if (isset($options['ftpMode'])) {
                    $ftpClient->setMode($options['ftpMode']);
                } else {
                    $ftpClient->setMode(self::FTP_UPLOAD_MODE_PUT);
                }

                return $ftpClient;
            } catch (FtpException $ex) {
                debug_log("Curl Extension Not Loaded");
            }
        }

        try {
            $ftpClient = new FtpClient($options['host'], $options['username'], $options['password'], $options['ssl'], $options['passive'], $options['port']);
            if (isset($options['ftpMode'])) {
                $ftpClient->setMode($options['ftpMode']);
            } else {
                $ftpClient->setMode(self::FTP_UPLOAD_MODE_PUT);
            }

            return $ftpClient;
        } catch (FtpException $ex) {
            debug_log("FTP Extension Not Loaded");
        }

        return false;
    }

    /**
     * @param array<string, mixed> $settings
     * @return bool
     */
    public function updateSettings($settings)
    {
        $options                    = $this->sanitizeData($settings);
        $options['isAuthenticated'] = false;

        if (!$this->isValidLocation($options['location'])) {
            return false;
        }

        if (!in_array($options['ftpType'], [self::CONNECTION_TYPE_FTP, self::CONNECTION_TYPE_SFTP])) {
            $this->error = esc_html__('Invalid FTP type!', 'wp-staging');
            debug_log('Invalid FTP type: ' . $options['ftpType']);

            return false;
        }

        if (!in_array($options['ftpMode'], [self::FTP_UPLOAD_MODE_PUT, self::FTP_UPLOAD_MODE_APPEND, self::FTP_UPLOAD_MODE_NON_BLOCKING])) {
            $this->error = esc_html__('Invalid FTP mode!', 'wp-staging');
            debug_log('Invalid FTP mode: ' . $options['ftpMode']);

            return false;
        }

        if ($options['ftpType'] === self::CONNECTION_TYPE_SFTP) {
            $this->readUploadedFileIntoOptions($options, 'key_file', 'key', 'SSH key upload');
        }

        if ($options['ftpType'] === self::CONNECTION_TYPE_FTP) {
            $this->readUploadedFileIntoOptions($options, 'ftp-certificate-file', 'ftpCertContent', 'FTP certificate upload');
        }

        $client = $this->getClient($options);
        if ($client === false) {
            $this->cleanupTempCertFile();
            $options['lastUpdated'] = time();
            return $this->saveOptions($options);
        }

        try {
            $result = $client->login();
            if ($result === false) {
                debug_log($client->getError());
            }

            // If no specific path is supplied, this method determines and provides the standard path depending on the connection type.
            if (empty($options['location'])) {
                $options['location'] = $client->getDefaultPath();
            }
        } finally {
            $client->close();
            $this->cleanupTempCertFile();
        }

        $options['isAuthenticated'] = $result;
        $options['lastUpdated']     = time();
        return $this->saveOptions($options);
    }

    /**
     * Clean all FTP / SFTP Settings,
     * Also unauthenticate the provider
     *
     * @return bool|array
     */
    public function revoke()
    {
        if (!$this->wpstagingAuth->isAuthenticatedRequest()) {
            return false;
        }

        return parent::saveOptions([]);
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        $this->error  = '';
        $options      = $this->getOptions();
        $this->client = $this->getClient($options);
        if (!$this->client) {
            $this->error = esc_html__('Failed to initialize FTP / SFTP client.', 'wp-staging');
            $this->cleanupTempCertFile();
            return [];
        }

        $this->client->login();

        $files = [];
        try {
            $files = $this->client->getFiles($options['location']);
        } catch (StorageException $ex) {
            $this->error = $this->client->getError();
            $this->cleanupTempCertFile();
            return [];
        }

        $this->cleanupTempCertFile();
        return $files;
    }

    /**
     * @return array
     */
    public function getBackups()
    {
        try {
            $files = $this->getFiles();
            $this->client->close();
            if (!is_array($files)) {
                $this->error = $this->client->getError() . ' - ' . __('Unable to fetch existing backups for cleanup', 'wp-staging');
                return [];
            }

            $backups = [];
            $strings = WPStaging::make(Strings::class);
            foreach ($files as $key => $file) {
                if ($strings->endsWith($file['name'], '.wpstg') || $strings->endsWith($file['name'], '.sql')) {
                    $date = new \DateTime();
                    $date->setTimestamp($file['time']);
                    $backups[$key]                       = json_decode(json_encode($file));
                    $backups[$key]->storageProviderName  = $this->getIdentifier();
                    $backups[$key]->type                 = $this->label;
                    $backups[$key]->id                   = $file['name'];
                    $backups[$key]->name                 = $file['name'];
                    $backups[$key]->dateCreatedTimestamp = $date->format('Y-m-d H:i:s');
                }
            }

            return $backups;
        } catch (\Exception $ex) {
            $this->error = $ex->getMessage();
            return [];
        }
    }

    public function deleteFile(string $file): bool
    {
        $options = $this->getOptions();
        $this->client->setPath($options['location']);

        // Concatenate path and filename since client no longer does this automatically
        $fullPath = !empty($options['location']) ? trailingslashit($options['location']) . $file : $file;
        return $this->client->deleteFile($fullPath);
    }

    public function isWriteableStoragePath(AbstractClient $client, string $path): bool
    {
        $this->error = '';
        if (empty($path)) {
            $this->error = esc_html__('Remote folder path is empty. Please enter a folder path.', 'wp-staging');
            debug_log("FTP/SFTP path checking aborted: remote path is empty or not provided.");
            return false;
        }

        $testFileName = 'wpstaging-write-upload-test.file';
        $txtToUpload  = 'testing upload';
        $client->makeDirectory($path);

        // Cleanup any leftover test file
        try {
            $file = $this->getFile($client, $path, $testFileName);
            if ($file !== null) {
                $client->deleteFile(trailingslashit($path) . $testFileName);
            }
        } catch (StorageException $ex) {
            debug_log("FTP/SFTP's path checking: unable to delete previous test file (may not exist). Error: " . $ex->getMessage());
        }

        // Upload test file
        $result = $client->upload($path, $testFileName, $txtToUpload);
        if (!$result || !empty($this->error)) {
            $this->error .= esc_html__(' Could not create a test file in the remote folder.', 'wp-staging');
            debug_log("FTP/SFTP's path checking: failed to create test file. Error: " . $this->error);
            return false;
        }

        // Verify upload if listing is supported
        try {
            $file = $this->getFile($client, $path, $testFileName);
        } catch (StorageException $ex) {
            debug_log('Unable to verify uploaded test file (SSL listing issue). Upload succeeded. Error: ' . $ex->getMessage());
            $file = null;
        }

        if ($file !== null && $file['size'] !== strlen($txtToUpload)) {
            $this->error = esc_html__('The uploaded test file has an unexpected size. The remote folder may have write issues.', 'wp-staging');
            debug_log("FTP/SFTP's path checking: file size not matched on upload!");
            return false;
        }

        // Append test content
        $client->login();
        $txtToAppend = 'testing append on existing upload';
        $result      = $client->upload($path, $testFileName, $txtToAppend, strlen($txtToUpload));

        if (!$result || !empty($this->error)) {
            $this->error = esc_html__('Could not append data to the test file. The server may not support resume or the user lacks write permission.', 'wp-staging');
            debug_log("FTP/SFTP's path checking: failed to append test file!");
            $client->deleteFile(trailingslashit($path) . $testFileName);
            return false;
        }

        // Verify append if listing is supported
        try {
            $file = $this->getFile($client, $path, $testFileName);
        } catch (StorageException $ex) {
            debug_log('Unable to verify appended test file (SSL listing issue). Append succeeded. Error: ' . $ex->getMessage());
            $file = null;
        }

        if ($file !== null && $file['size'] !== strlen($txtToUpload . $txtToAppend)) {
            $this->error = esc_html__('The appended test file has an unexpected size. The server may not support resume correctly.', 'wp-staging');
            debug_log("FTP/SFTP's path checking: file size not matched on append!");
            $client->deleteFile(trailingslashit($path) . $testFileName);
            return false;
        }

        // Cleanup test file (do not fail test if cleanup fails)
        if (!$client->deleteFile(trailingslashit($path) . $testFileName)) {
            debug_log("FTP/SFTP's path checking: warning - could not delete test file after successful test.");
        }

        return true;
    }

    /**
     * @param AbstractClient $client
     * @param string $path
     * @param string $fileName
     * @return array|null
     * @throws StorageException When listing files fails.
     */
    private function getFile(AbstractClient $client, string $path, string $fileName)
    {
        foreach ($client->getFiles($path) as $file) {
            if ($file['name'] === $fileName) {
                return $file;
            }
        }

        return null;
    }

    /**
     * @param array $data
     * @return array
     */
    private function sanitizeData(array $data): array
    {
        $currentOptions = $this->getOptions();
        return [
            'ftpType'          => isset($data['ftp_type']) ? $this->sanitize->sanitizeString($data['ftp_type']) : '',
            'host'             => isset($data['host']) ? $this->sanitize->sanitizeString($data['host']) : '',
            'port'             => isset($data['port']) ? $this->sanitize->sanitizeInt($data['port']) : 21,
            'username'         => isset($data['username']) ? $this->sanitize->sanitizeString($data['username']) : '',
            'password'         => isset($data['password']) ? $this->sanitize->sanitizePassword($data['password']) : '',
            'passphrase'       => isset($data['passphrase']) ? $this->sanitize->sanitizePassword($data['passphrase']) : '',
            'key'              => !empty($data['key']) ? $this->sanitize->sanitizeCredentialContent($data['key']) : ($currentOptions['key'] ?? ''),
            'fingerprint'      => isset($data['fingerprint']) ? $this->sanitize->sanitizeString($data['fingerprint']) : '',
            'ftpCertContent'   => !empty($data['ftp_cert_content']) ? $this->sanitize->sanitizeCredentialContent($data['ftp_cert_content']) : ($currentOptions['ftpCertContent'] ?? ''),
            'ssl'              => !empty($data['ssl']) && $this->sanitize->sanitizeBool($data['ssl']),
            'passive'          => !empty($data['passive']) && $this->sanitize->sanitizeBool($data['passive']),
            'useFtpExtension'  => !empty($data['use_ftp_extension']) && $this->sanitize->sanitizeBool($data['use_ftp_extension']),
            'verifyCert'       => !empty($data['verify_cert']) && $this->sanitize->sanitizeBool($data['verify_cert']),
            'allowInsecure'    => !empty($data['allow_insecure']) && $this->sanitize->sanitizeBool($data['allow_insecure']),
            'useSshKey'        => !empty($data['use_ssh_key']) && $this->sanitize->sanitizeBool($data['use_ssh_key']),
            'ftpMode'          => isset($data['ftp_mode']) ? $this->sanitize->sanitizeInt($data['ftp_mode']) : self::FTP_UPLOAD_MODE_PUT,
            'location'         => empty($data['location']) ? self::FOLDER_NAME : $this->sanitize->sanitizeString($data['location']),
            'maxBackupsToKeep' => isset($data['max_backups_to_keep']) ? $this->sanitize->sanitizeInt($data['max_backups_to_keep']) : 2,
        ];
    }

    /**
     * Build the list of cumulative directory paths from a path string.
     * e.g. "/home/user/backups" => ["/home", "/home/user", "/home/user/backups"]
     *
     * @return string[]
     */
    private function buildDirectoryHierarchy($path)
    {
        $path       = rtrim($path, '/');
        $isAbsolute = strpos($path, '/') === 0;
        $parts      = array_filter(explode('/', $path), function ($part) {
            return $part !== '' && $part !== '.';
        });

        $directories = [];
        $currentPath = '';
        foreach ($parts as $part) {
            if ($isAbsolute && $currentPath === '') {
                $currentPath = '/' . $part;
            } else {
                $currentPath = $currentPath === '' ? $part : $currentPath . '/' . $part;
            }

            $directories[] = $currentPath;
        }

        return $directories;
    }

    private function getDirectoryHierarchyExistence(AbstractClient $client, string $path): array
    {
        if (empty($path)) {
            return [];
        }

        $existence = [];
        foreach ($this->buildDirectoryHierarchy($path) as $directory) {
            $existence[$directory] = $client->directoryExists($directory);
        }

        return $existence;
    }

    /**
     * @return void
     */
    private function deleteCreatedDirectories(AbstractClient $client, string $path, array $directoriesExistedBefore)
    {
        if (empty($path)) {
            return;
        }

        if (!$client->login()) {
            debug_log("Failed to login for directory cleanup: " . $client->getError());
            return;
        }

        $directories = array_reverse($this->buildDirectoryHierarchy($path));
        foreach ($directories as $directory) {
            if (!isset($directoriesExistedBefore[$directory]) || !$directoriesExistedBefore[$directory]) {
                if ($client->directoryExists($directory)) {
                    $client->deleteDirectory($directory);
                    debug_log("Removed directory created during test: " . $directory);
                }
            }
        }
    }

    /** @var int Maximum upload size for SSH keys and certificates (256 KB) */
    const MAX_CREDENTIAL_FILE_SIZE = 262144;

    /**
     * Read an uploaded file's content into the given options key.
     * The file content is stored in memory only, never written to disk permanently.
     *
     * @param string $fileInputName The $_FILES key for the upload field
     * @param string $optionKey     The key in $options to store the content
     * @param string $logLabel      Label for debug log messages
     */
    private function readUploadedFileIntoOptions(array &$options, $fileInputName, $optionKey, $logLabel)
    {
        $file = isset($_FILES[$fileInputName]) ? $this->sanitize->sanitizeFileUpload($_FILES[$fileInputName]) : null;
        if (empty($file) || !isset($file['size']) || $file['size'] <= 0) {
            return;
        }

        if ($file['size'] > self::MAX_CREDENTIAL_FILE_SIZE) {
            debug_log($logLabel . ': file too large (' . $file['size'] . ' bytes).');
            return;
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            debug_log($logLabel . ': invalid uploaded file.');
            return;
        }

        $content = file_get_contents($file['tmp_name']);
        if ($content === false) {
            debug_log($logLabel . ': failed to read uploaded file content.');
            return;
        }

        $options[$optionKey] = trim($content);
    }

    /**
     * Write certificate content to a temp file for curl's CURLOPT_CAINFO.
     *
     * @param string $certContent
     * @return string The temp file path, or empty string if no content.
     */
    private function writeCertToTempFile($certContent)
    {
        if (empty($certContent)) {
            return '';
        }

        $tempPath = tempnam(get_temp_dir(), 'wpstg_cert_');
        if ($tempPath === false) {
            debug_log('Failed to create temp file for FTP certificate.');
            return '';
        }

        // Restrict permissions before writing sensitive content
        chmod($tempPath, 0600);

        $written = file_put_contents($tempPath, $certContent);
        if ($written === false) {
            debug_log('Failed to write cert content to temp file: ' . $tempPath);
            unlink($tempPath);
            return '';
        }

        $this->tempCertPath = $tempPath;
        return $tempPath;
    }

    /** @return void */
    private function cleanupTempCertFile()
    {
        if (empty($this->tempCertPath) || !file_exists($this->tempCertPath)) {
            $this->tempCertPath = '';
            return;
        }

        if (!unlink($this->tempCertPath)) {
            debug_log('Failed to delete temp certificate file: ' . $this->tempCertPath);
        }

        $this->tempCertPath = '';
    }

    /**
     * Get a credential value, preferring a wp-config constant over the stored option.
     *
     * @param string $constantName The wp-config constant to check first
     * @param string $optionKey    The key in $options to fall back to
     * @return string
     */
    private function getCredentialValue(array $options, $constantName, $optionKey)
    {
        if (defined($constantName) && constant($constantName) !== '') {
            return constant($constantName);
        }

        return !empty($options[$optionKey]) ? $options[$optionKey] : '';
    }
}
