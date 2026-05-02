<?php

namespace WPStaging\Pro\Backup\Storage\BaseS3;

use Exception;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Utils\Sanitize;
use WPStaging\Framework\Utils\Strings;
use WPStaging\Framework\Security\Auth as WPStagingAuth;
use WPStaging\Pro\Backup\Storage\AbstractStorage;

use function WPStaging\functions\debug_log;

abstract class S3Auth extends AbstractStorage
{
    /** @var string */
    const FOLDER_NAME = 'wpstaging-backups';

    /** @var string */
    protected $version = 'latest';

    /** @var string */
    protected $provider = '';

    /** @var null|string */
    protected $endpoint = null;

    /** @var bool */
    protected $ssl = true;

    /** @var bool */
    protected $usePathStyleEndpoint = true;

    /** @var string */
    protected $bucketName;

    /** @var Sanitize */
    protected $sanitize;

    /** @var S3Client */
    protected $client;

    public function __construct(WPStagingAuth $wpstagingAuth, Sanitize $sanitize)
    {
        parent::__construct($wpstagingAuth);
        $this->sanitize = $sanitize;
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
        try {
            $endpoint                   = isset($_POST['endpoint']) ? $this->sanitize->sanitizeString($_POST['endpoint']) : '';
            $this->provider             = isset($_POST['s3_provider']) ? $this->sanitize->sanitizeString($_POST['s3_provider']) : '';
            $this->version              = isset($_POST['version']) ? $this->sanitize->sanitizeString($_POST['version']) : $this->version;
            $this->ssl                  = isset($_POST['ssl']) ? $this->sanitize->sanitizeBool($_POST['ssl']) : $this->ssl;
            $this->usePathStyleEndpoint = isset($_POST['use_path_style_endpoint']) ? $this->sanitize->sanitizeBool($_POST['use_path_style_endpoint']) : $this->usePathStyleEndpoint;
            $accessKey                  = isset($_POST['access_key']) ? $this->sanitize->sanitizePassword($_POST['access_key']) : '';
            $secretKey                  = isset($_POST['secret_key']) ? $this->sanitize->sanitizePassword($_POST['secret_key']) : '';
            $region                     = isset($_POST['region']) ? $this->sanitize->sanitizeString($_POST['region']) : '';
            $location                   = isset($_POST['location']) ? $this->sanitize->sanitizeString($_POST['location']) : '';

            if (empty($accessKey) || empty($secretKey) || empty($location)) {
                $this->error = 'Access Key, Secret Key, and Bucket cannot be empty.';
                debug_log('Access Key, Secret Key, and Bucket cannot be empty.');

                return false;
            }

            // Instantiate our custom S3 client
            $s3Client = new S3Client($this->getConfigOptions($accessKey, $secretKey, $region, $endpoint));
        } catch (Exception $ex) {
            debug_log("S3 Client : " . $ex->getMessage());
            return false;
        }
        $this->client = $s3Client;

        return $this->isWriteableStoragePath($location);
    }

    /**
     * @param  string $location
     * @return bool
     */
    public function isWriteableStoragePath($location)
    {
        $locationFormatted = $this->getLocation($location);
        $bucketName        = $locationFormatted[0];
        $path              = $locationFormatted[1];
        $testFileName      = $path . 'test.file';

        try {
            $this->testUpload($bucketName, $testFileName);

            $this->client->deleteObject([
                'Bucket' => $bucketName,
                'Key'    => $testFileName,
            ]);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();

            if (strpos($errorMessage, 'InvalidAccessKeyId') !== false || strpos($errorMessage, 'InvalidArgument') !== false) {
                $message = "Your Access Key is incorrect or doesn't exist. Please double-check your access key and try again.";
            } elseif (strpos($errorMessage, 'SignatureDoesNotMatch') !== false) {
                $message = "Authentication failed: The request signature doesn't match. Please check your secret access key and ensure your system time is correct.";
            } elseif (strpos($errorMessage, 'NoSuchBucket') !== false) {
                $message = "The bucket '$bucketName' could not be found. Please verify that the bucket name is correct and exists.";
            } elseif (strpos($errorMessage, 'AccessDenied') !== false || strpos($errorMessage, 'InvalidRegionName') !== false) {
                $message = "Can't connect to the bucket - the region might be incorrect. Check your bucket's region and try again.";
            } else {
                if (!empty($errorMessage)) {
                    $message = $errorMessage;
                } else {
                    $message = 'Unknown error occurred.';
                }
            }

            $this->error = $message;
            debug_log($this->error);

            return false;
        }

        return true;
    }

    /** @return S3Client|false */
    public function getClient($options = null)
    {
        if ($options === null) {
            $options = $this->getOptions();
        }

        $this->provider             = isset($options['provider']) ? $options['provider'] : '';
        $this->ssl                  = isset($options['ssl']) ? $options['ssl'] : $this->ssl;
        $this->version              = isset($options['version']) ? $options['version'] : $this->version;
        $this->usePathStyleEndpoint = isset($options['usePathStyleEndpoint']) ? $options['usePathStyleEndpoint'] : $this->usePathStyleEndpoint;

        $endpoint = (isset($options['endpoint']) && $this->endpoint !== $options['endpoint']) ? $options['endpoint'] : null;

        try {
            // Instantiate our custom S3 client
            $s3Client = new S3Client($this->getConfigOptions($options['accessKey'], $options['secretKey'], $options['region'], $endpoint));
        } catch (Exception $ex) {
            debug_log($ex->getMessage());
            return false;
        }

        return $s3Client;
    }

    /**
     * @param array $settings
     * @return bool
     */
    public function updateSettings($settings)
    {
        $options       = $this->getOptions();
        $s3Provider    = isset($settings['s3_provider']) ? $this->sanitize->sanitizeString($settings['s3_provider']) : '';
        $endpoint      = isset($settings['endpoint']) ? $this->sanitize->sanitizeString($settings['endpoint']) : '';
        $accessKey     = isset($settings['access_key']) ? $this->sanitize->sanitizePassword($settings['access_key']) : '';
        $secretKey     = isset($settings['secret_key']) ? $this->sanitize->sanitizePassword($settings['secret_key']) : '';
        $region        = isset($settings['region']) ? $this->sanitize->sanitizeString($settings['region']) : '';
        $location      = empty($settings['location']) ? self::FOLDER_NAME : sanitize_text_field($settings['location']);
        $backupsToKeep = isset($settings['max_backups_to_keep']) ? $this->sanitize->sanitizeInt($settings['max_backups_to_keep']) : 2;

        if (!$this->isValidLocation($location)) {
            return false;
        }

        $backupsToKeep = $backupsToKeep > 0 ? $backupsToKeep : 15;

        $options['location']         = $location;
        $options['region']           = $region;
        $options['accessKey']        = $accessKey;
        $options['secretKey']        = $secretKey;
        $options['provider']         = $s3Provider;
        $options['endpoint']         = empty($endpoint) ? $this->endpoint : $endpoint;
        $options['maxBackupsToKeep'] = $backupsToKeep;

        if (isset($settings['provider_name'])) {
            $options['providerName'] = $this->sanitize->sanitizeString($settings['provider_name']);
        }

        if (isset($settings['ssl'])) {
            $options['ssl'] = $this->sanitize->sanitizeBool($settings['ssl']);
        }

        if (isset($settings['version'])) {
            $options['version'] = $this->sanitize->sanitizeString($settings['version']);
        }

        if (isset($settings['use_path_style_endpoint'])) {
            $options['usePathStyleEndpoint'] = $this->sanitize->sanitizeBool($settings['use_path_style_endpoint']);
        }

        $options['isAuthenticated'] = false;

        $isConnected = $this->testConnection($options);
        if ($isConnected !== false) {
            $options['isAuthenticated'] = true;
        }

        $options['lastUpdated'] = time();

        return $this->saveOptions($options);
    }

    /**
     * Revoke both access and refresh token,
     * Also unauthenticate the provider
     *
     * @return bool
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
        $options = $this->getOptions();
        $client  = $this->getClient($options);
        if ($client === false) {
            return [];
        }

        $this->client            = $client;
        list($bucketName, $path) = $this->getLocation();
        $this->bucketName        = $bucketName;

        try {
            $searchParams = [
                'Bucket' => $bucketName,
            ];

            if (!empty($path)) {
                $searchParams['Prefix'] = $path;
            }

            $result = $client->listObjects($searchParams);

            // array key is lazy loaded
            $backups = $result['Contents']; // @phpstan-ignore-line

            if (!is_array($backups)) {
                return [];
            }

            return $backups;
        } catch (Exception $ex) {
            debug_log('Error listing S3 objects: ' . $ex->getMessage());
        }

        return [];
    }

    public function getBackups(): array
    {
        try {
            $files = $this->getFiles();

            // Sort by date in ascending order
            uasort($files, function ($object1, $object2) {
                $date1 = (new \DateTime($object1['LastModified']));
                $date2 = (new \DateTime($object2['LastModified']));

                return $date1 < $date2 ? -1 : 1;
            });

            $backups = [];
            $strings = WPStaging::make(Strings::class);
            foreach ($files as $key => $file) {
                if ($strings->endsWith($file['Key'], '.wpstg') || $strings->endsWith($file['Key'], '.sql')) {
                    $date                                = new \DateTime($file['LastModified']);
                    $backups[$key]                       = json_decode(json_encode($file));
                    $backups[$key]->name                 = basename($file['Key']);
                    $backups[$key]->size                 = $file['Size'];
                    $backups[$key]->storageProviderName  = $this->getIdentifier();
                    $backups[$key]->type                 = $this->label;
                    $backups[$key]->id                   = $file['Key'];
                    $backups[$key]->dateCreatedTimestamp = $date->format('Y-m-d H:i:s');
                }
            }

            return $backups;
        } catch (Exception $ex) {
            $this->error = $ex->getMessage();
            return [];
        }
    }

    /**
     * @param $backupFile
     * @return bool
     */
    public function isBackupUploaded($backupFile)
    {
        $options = $this->getOptions();
        $client  = $this->getClient($options);
        if ($client === false) {
            return false;
        }

        list($bucketName, $path) = $this->getLocation();

        try {
            $searchParams = [
                'Bucket' => $bucketName,
            ];

            $prefix = '';
            if (!empty($path)) {
                $prefix                 = $path;
                $searchParams['Prefix'] = $prefix;
            }

            $result = $client->listObjects($searchParams);

            $objects = $result['Contents']; // @phpstan-ignore-line

            foreach ($objects as $object) {
                if ($object['Key'] === $prefix . $backupFile) {
                    return true;
                }
            }
        } catch (Exception $ex) {
            debug_log($ex->getMessage());
        }

        debug_log('Could not find backup. Error: ' . $bucketName);
        return false;
    }

    /** @return array Bucket Name of 0 index and rest of path on 1 index */
    public function getLocation($location = null): array
    {
        if ($location === null) {
            $options  = $this->getOptions();
            $location = $options['location'] ?? '';
        }

        $location = $this->explodeLocation($location);

        $bucketName = $location[0];
        $path       = '';
        for ($i = 1; $i < count($location); $i++) {
            $path .= $location[$i] . '/';
        }

        return [$bucketName, $path];
    }

    /**
     * Return list of regions supported by Provider
     * @return array
     */
    public function getRegions()
    {
        return [];
    }

    /**
     * @param string $file
     * @return bool
     */
    public function deleteFile(string $file): bool
    {
        $options = $this->getOptions();
        $client  = $this->getClient($options);
        if ($client === false) {
            return false;
        }

        list($bucketName, $path) = $this->getLocation();

        $fileToDelete = $file;
        if (!empty($path)) {
            $fileToDelete = trailingslashit($path) . $file;
        }

        $client->deleteObject([
            'Bucket' => $bucketName,
            'Key'    => $fileToDelete,
        ]);

        return true;
    }

    /**
     * @param  string $bucketName
     * @param  S3Client $client
     * @param  bool $reportError
     * @return bool
     */
    public function getIsObjectLocked(string $bucketName, S3Client $client, bool $reportError = false): bool
    {
        try {
            $result = $client->getObjectLockConfiguration([
                'Bucket' => $bucketName,
            ]);

            return !empty($result['ObjectLockConfiguration']['ObjectLockEnabled']) && $result['ObjectLockConfiguration']['ObjectLockEnabled'] === 'Enabled'; // @phpstan-ignore-line
        } catch (Exception $ex) {
            if (strpos($ex->getMessage(), 'ObjectLockConfigurationNotFoundError') !== false) {
                return false;
            }

            debug_log("Fail to check object lock configuration: " . $ex->getMessage(), 'info', false);
            if ($reportError) {
                $this->error = __('IAM user does not have s3:GetBucketObjectLockConfiguration permission. Therefore cannot retrieve the Object Lock Configuration! Treatment as Object Lock Disabled. If upload fails extend user permission.', 'wp-staging');
            }

            return false;
        }
    }

    abstract protected function setupProvider();

    protected function getEndpoint($region)
    {
        if (empty($this->endpoint)) {
            return null;
        }

        $scheme = $this->ssl ? 'https' : 'http';
        $region = empty($region) ? '' : ($region . '.');

        return $scheme . '://' . str_replace('[region]', $region, $this->endpoint);
    }

    /**
     * @return array
     */
    protected function getConfigOptions($accessKey, $secretKey, $region, $endpoint = null)
    {
        $this->setupProvider();

        $config = [
            'version'                 => $this->version,
            'region'                  => $region,
            'endpoint'                => empty($endpoint) ? $this->getEndpoint($region) : $endpoint,
            'credentials'             => [
                'key'    => $accessKey,
                'secret' => $secretKey,
            ],
            'use_path_style_endpoint' => $this->usePathStyleEndpoint,
        ];

        if ($this->ssl) {
            $config['http'] = [
                'verify' => $this->getCertPath(),
            ];
        }

        return $config;
    }

    /**
     * @param  string $bucketName
     * @param  string $testFileName
     * @return void
     */
    private function testUpload(string $bucketName, string $testFileName)
    {
        $options = [];
        $body    = 'test body';
        if ($this->identifier === 'amazons3' && $this->getIsObjectLocked($bucketName, $this->client)) {
            $options = [
                'params' => [
                    'ContentMD5' => base64_encode(md5($body, true)),
                ],
            ];
        }

        $this->client->upload(
            $bucketName,
            $testFileName,
            $body,
            'private',
            $options
        );
    }

    private function explodeLocation($location): array
    {
        if (empty($location)) {
            return [''];
        }

        return explode('/', $location);
    }
}
