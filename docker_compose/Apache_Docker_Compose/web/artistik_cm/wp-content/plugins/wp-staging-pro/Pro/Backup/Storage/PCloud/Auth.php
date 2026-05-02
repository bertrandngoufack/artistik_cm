<?php

namespace WPStaging\Pro\Backup\Storage\PCloud;

use UnexpectedValueException;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Utils\Sanitize;
use WPStaging\Framework\Utils\Strings;
use WPStaging\Framework\Security\Auth as WPStagingAuth;
use WPStaging\Pro\Backup\Storage\AbstractStorage;
use WPStaging\Framework\Traits\HttpRequestTrait;

use function WPStaging\functions\debug_log;

class Auth extends AbstractStorage
{
    use HttpRequestTrait;

    /** @var string */
    const FOLDER_NAME = 'wpstaging-backups';

    /** @var string */
    const REDIRECT_BASE_URL = 'https://auth.wp-staging.com/pcloud';

    /** @var string */
    const ACTION_ADMIN_POST_PCLOUD_AUTH = 'admin_post_wpstg-pcloud-auth';

    /** @var array */
    protected $options;

    /** @var Sanitize */
    protected $sanitize;

    /**
     * @see https://docs.pcloud.com/methods/oauth_2.0/authorize.html
     *
     * @var string
     */
    protected $pCloudHostname;

    public function __construct(WPStagingAuth $wpstagingAuth, Sanitize $sanitize)
    {
        if (!$this->isBusinessPlanOrHigher()) {
            return;
        }

        parent::__construct($wpstagingAuth);
        $this->identifier     = 'pcloud';
        $this->label          = 'pCloud';
        $this->sanitize       = $sanitize;
        $this->options        = $this->getOptions();
        $this->pCloudHostname = empty($this->options['hostname']) ? '' : 'https://' . $this->options['hostname'];
    }

    /**
     * @param  array $options
     * @return bool
     */
    public function saveOptions($options = [])
    {
        $isSaved       = parent::saveOptions($options);
        $this->options = $this->getOptions();
        return $isSaved;
    }

    /**
     * @param string $file
     * @return string
     */
    public function computeFileHash(string $file): string
    {
        clearstatcache();
        if (!file_exists($file) || !filesize($file)) {
            throw new UnexpectedValueException("File does not exist or is empty, file: $file");
        }

        $hash = sha1_file($file);
        return $hash;
    }

    /**
     * @param string $redirectTo
     * @return string Authorization URL
     */
    public function getAuthenticationURL(string $redirectTo = ""): string
    {
        if (empty($redirectTo)) {
            $redirectTo = 'wpstg-settings';
        }

        set_transient(AbstractStorage::TRANSIENT_REDIRECT_URL, $redirectTo, 300);
        $link = add_query_arg(
            [
                'state' => base64_encode(urlencode((string) admin_url('admin-post.php'))),
            ],
            self::REDIRECT_BASE_URL
        );
        return $link;
    }

    /**
     * @return bool
     */
    public function testConnection()
    {
        // no-op
        return true;
    }

    /**
     * Refresh the access token based on the refresh token
     *
     * @return bool
     */
    public function refreshToken()
    {
        // no-op, pCloud does not refresh token
        return true;
    }

    /**
     * Authentication of the storage
     *
     * @return void
     */
    public function authenticate()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to access this page.', 'wp-staging'));
        }

        $this->options['accessToken'] = isset($_GET['access_token']) ? $this->sanitize->decodeBase64AndSanitize($_GET['access_token']) : '';
        $this->options['accountId']   = isset($_GET['uid']) ? $this->sanitize->decodeBase64AndSanitize($_GET['uid']) : 0;
        $this->options['locationId']  = isset($_GET['location_id']) ? $this->sanitize->decodeBase64AndSanitize($_GET['location_id']) : 0;
        $this->options['hostname']    = isset($_GET['hostname']) ? $this->sanitize->decodeBase64AndSanitize($_GET['hostname']) : '';

        if (empty($this->options['accessToken']) || empty($this->options['accountId']) || !isset($this->options['locationId']) || empty($this->options['hostname'])) {
            debug_log('Fail to authenticate to pCloud account some data are missing. Options: ' . print_r($this->options, true));
            wp_die(esc_html__('Authentication failed please try again.', 'wp-staging'));
        }

        $this->options['isAuthenticated'] = true;
        $this->pCloudHostname             = 'https://' . $this->options['hostname'];

        $this->saveOptions($this->options);
        $this->saveStorageAccountInfo();
        $redirectTo = get_transient(AbstractStorage::TRANSIENT_REDIRECT_URL, '');
        if (!empty($redirectTo) && $redirectTo === 'wpstg_backup') {
            $this->updateSettings($this->options);
            wp_redirect(admin_url('admin.php?page=wpstg_backup'));
            return;
        }

        $redirectURL = add_query_arg(
            [
                'page'         => 'wpstg-settings',
                'tab'          => 'remote-storages',
                'sub-tab'      => 'pcloud',
                'auth-storage' => 'true',
            ],
            admin_url('admin.php')
        );

        wp_redirect($redirectURL);
    }

    /**
     * Save storage account info in options(displayed in storage page)
     *
     * @return void
     */
    public function saveStorageAccountInfo()
    {
        $options = $this->getOptions();
        $response = $this->getUserData();

        $options['userData']                = $response;
        $options['userData']['displayName'] = $response['email'] ?? '';

        $options['storageInfo']['used']                    = $response['usedquota'] ?? '';
        $options['storageInfo']['allocation']['allocated'] = $response['quota'] ?? '';

        $this->saveOptions($options);
    }

    /**
     * Authenticate when user set his own API credentials
     */
    public function apiAuthenticate()
    {
        // no-op
    }

    /**
     * @param array $settings
     * @return bool
     */
    public function updateSettings($settings)
    {
        $backupLocation = empty($settings['folder_name']) ? self::FOLDER_NAME : sanitize_text_field($settings['folder_name']);
        if (!$this->isValidLocation($backupLocation)) {
            return false;
        }

        $this->options['folderName']       = $backupLocation;
        $this->options['maxBackupsToKeep'] = !empty($settings['max_backups_to_keep']) && $settings['max_backups_to_keep'] > 0 ? sanitize_text_field($settings['max_backups_to_keep']) : 2;
        $this->options['lastUpdated']      = time();

        return $this->saveOptions($this->options);
    }

    /**
     * Revoke both access and refresh token
     * @return bool
     */
    public function revoke()
    {
        if (!$this->wpstagingAuth->isAuthenticatedRequest()) {
            return false;
        }

        if (!empty($this->options['accessToken'])) {
            $args = [
                'timeout' => 60,
            ];
            $params = [
                'access_token' => $this->options['accessToken'],
            ];

            try {
                $this->getRequestBody($this->pCloudHostname . '/logout?' . http_build_query($params), $args);
            } catch (\Throwable $th) {
            }
        }

        return parent::saveOptions([]);
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->fetchFiles();
    }

    /**
     * @param  int $retry
     * @return array
     */
    private function fetchFiles(int $retry = 0): array
    {
        if (empty($this->options['accessToken'])) {
            return [];
        }

        $destinationId = $this->getBackupsDestinationId();
        if ($destinationId === false || $destinationId === '') {
            debug_log($this->identifier . ': could not resolve backups destination folder, skipping file listing');
            return [];
        }

        try {
            $url = $this->pCloudHostname . "/listfolder";

            $args = [
                'method' => 'GET',
            ];

            $params = [
                'folderid'     => $destinationId,
                'access_token' => $this->options['accessToken'],
            ];

            $url = $url . '?' . http_build_query($params);
            $response = $this->getRequestBody($url, $args);
            if (!isset($response['metadata'])) {
                debug_log($this->identifier . ' fail to get backups folder data.');
                return [];
            }

            return $response['metadata']['contents'];
        } catch (\Throwable $th) {
            if ($this->isRetryableError($th) && $retry < 3) {
                debug_log($this->identifier . ': retryable error on getFiles, attempt ' . ($retry + 1) . '. Waiting 1s. Error: ' . $th->getMessage());
                sleep(1);
                return $this->fetchFiles($retry + 1);
            }

            return [];
        }
    }

    /**
     * @return array
     */
    public function getBackups()
    {
        $files = $this->getFiles();

        $backups = [];
        $strings = WPStaging::make(Strings::class);
        foreach ($files as $key => $file) {
            if (!isset($file['fileid'])) {
                continue;
            }

            $fileName = $file['name'];
            if ($strings->endsWith($fileName, '.wpstg') || $strings->endsWith($fileName, '.sql')) {
                $date                                  = new \DateTime($file['created']);
                $backups[$key]                         = $file;
                $backups[$key]['dateCreatedTimestamp'] = $date->format('Y-m-d H:i:s');
                $backups[$key]['storageProviderName']  = $this->getIdentifier();
                $backups[$key]['type']                 = $this->label;
            }
        }

        return $backups;
    }

    /**
     * @return string|false
     */
    public function getBackupsDestinationId()
    {
        $location       = $this->getBackupsLocation();
        $locationURI    = $this->getFoldersFromLocation($location);
        $parentFolderId = 0;
        foreach ($locationURI as $folder) {
            $folderId = $this->createFolderIfNotExists($folder, $parentFolderId);
            if ($folderId === false) {
                return false; // Early bail: fail to get or to create the current folder, no need to continue the loop. Something is wrong!
            }

            $parentFolderId = $folderId;
        }

        return (string)$folderId;
    }

    /**
     * @return false|array Returns false on api request failure.
     */
    public function getUserData()
    {
        if (empty($this->options['accessToken'])) {
            return false;
        }

        $args = [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $this->options['accessToken'],
            ],
            'method'  => 'GET',
        ];

        try {
            return $this->getRequestBody($this->pCloudHostname . '/userinfo', $args);
        } catch (\Throwable $th) {
            debug_log('Failed to get user data. ' . $th->getMessage());
            return false;
        }
    }

    /**
     * @return false|array Returns false on api request failure.
     */
    public function getStorageInfo()
    {
        // no-op
        return [];
    }

    /**
     * @see WPStaging\Pro\Backup\Task\Tasks\JobBackup\AbstractStorageTask::shouldCleanOldBackupsForRemoteUpload
     * @param string $fileName
     * @return bool
     */
    public function deleteFile(string $fileName): bool
    {
        $remoteFile = $this->getBackup($fileName);
        if (empty($remoteFile)) {
            return false;
        }

        return $this->deleteFileById($remoteFile['fileid']);
    }

    /**
     * @see WPStaging\Pro\Backup\Task\Tasks\JobBackup\AbstractStorageTask::shouldCleanOldBackupsForRemoteUpload
     * @param string $fileName
     * @return bool
     */
    public function deleteFileById(string $fileId): bool
    {
        if (empty($this->pCloudHostname) && !empty($this->options['hostname'])) {
            $this->pCloudHostname = 'https://' . $this->options['hostname'];
        }

        $url  = $this->pCloudHostname . "/deletefile";
        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->options['accessToken'],
            ],
            'method'  => 'GET',
        ];
        $params = [
            'fileid' => $fileId,
        ];

        $url = $url . '?' . http_build_query($params);
        try {
            $response = $this->getRequestBody($url, $args);
            if (!empty($response['metadata']['isdeleted'])) {
                return true;
            }

            debug_log("Failed to delete $fileId. response: " . print_r($response, true));
        } catch (\Throwable $th) {
            debug_log("Failed to delete $fileId. Error message: " . $th->getMessage());
        }

        return false;
    }

    /**
     * @see WPStaging\Pro\Backup\Task\Tasks\JobBackup\AbstractStorageTask::shouldCleanOldBackupsForRemoteUpload
     * @param string $dirId
     * @param int $retry
     * @return bool
     */
    public function deleteDirectoryRecursively(string $dirId, int $retry = 0): bool
    {
        $url  = $this->pCloudHostname . "/deletefolderrecursive";
        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->options['accessToken'],
            ],
            'method'  => 'GET',
        ];
        $params = [
            'folderid' => $dirId,
        ];

        $url = $url . '?' . http_build_query($params);
        try {
            $response = $this->getRequestBody($url, $args);
            if (isset($response['result'])) {
                return true;
            }

            debug_log("Fail to delete dir $dirId. response: " . print_r($response, true));
        } catch (\Throwable $th) {
            if ($this->isRetryableError($th) && $retry < 3) {
                debug_log($this->identifier . ': retryable error on deleteDirectoryRecursively, attempt ' . ($retry + 1) . '. Waiting 1s. Error: ' . $th->getMessage());
                sleep(1);
                return $this->deleteDirectoryRecursively($dirId, $retry + 1);
            }

            debug_log("Failed to delete dir $dirId. Error message: " . $th->getMessage());
        }

        return false;
    }

    /**
     * Retrieves a specific backup file from remote
     * @param  string $file could be the file name or its id.
     * @return array
     */
    public function getBackup(string $file): array
    {
        $backup       = [];
        $backupsFiles = $this->getBackups();

        foreach ($backupsFiles as $remoteFile) {
            if ($remoteFile['name'] === $file || $remoteFile['id'] === $file) {
                $backup = $remoteFile;
                break;
            }
        }

        return $backup;
    }

    /**
     * @param string $location
     *
     * @return array
     */
    public function getFoldersFromLocation(string $location)
    {
        $locationURI = explode('/', $location);
        return array_filter(array_map('trim', $locationURI), function ($folder) {
            return !empty($folder);
        });
    }

    /**
     * @throws UnexpectedValueException
     *
     * @return string
     */
    public function getPCloudHostname(): string
    {
        if (!empty($this->pCloudHostname)) {
            return $this->pCloudHostname;
        }

        if (empty($this->options['hostname'])) {
            throw new UnexpectedValueException("PCloud error: hostname is missing!");
        }

        $this->pCloudHostname = 'https://' . $this->options['hostname'];
        return $this->pCloudHostname;
    }

    /**
     * Whether a Throwable represents a transient network error that warrants a retry.
     *
     * @param \Throwable $th
     * @return bool
     */
    public function isRetryableError(\Throwable $th)
    {
        $message = $th->getMessage();
        return strpos($message, 'Operation timed out after') !== false
            || strpos($message, 'cURL error 52') !== false
            || strpos($message, 'Empty reply from server') !== false;
    }

    /**
     * @return string
     */
    protected function getBackupsLocation(): string
    {
        $options = $this->getOptions();
        return !empty($options['folderName']) ? $options['folderName'] : Auth::FOLDER_NAME;
    }

    /**
     * @param  string $folderName
     * @param  string $parentFolderId
     * @param  int $retry
     * @return string|false
     */
    private function createFolderIfNotExists(string $folderName, string $parentFolderId, int $retry = 0)
    {
        if (empty($this->pCloudHostname) && !empty($this->options['hostname'])) {
            $this->pCloudHostname = 'https://' . $this->options['hostname'];
        }

        try {
            $url = $this->pCloudHostname . "/createfolderifnotexists";

            $args = [
                'method' => 'GET',
            ];
            $params = [
                'folderid'     => $parentFolderId,
                'name'         => $folderName,
                'access_token' => $this->options['accessToken'],
            ];

            $url = $url . '?' . http_build_query($params);
            $response = $this->getRequestBody($url, $args);

            if (!isset($response['metadata']['folderid'])) {
                debug_log($this->identifier . ' fail to create backups folder.');
                return false;
            }

            return (string)$response['metadata']['folderid'];
        } catch (\Throwable $th) {
            if ($this->isRetryableError($th) && $retry < 3) {
                debug_log($this->identifier . ': retryable error on createFolderIfNotExists, attempt ' . ($retry + 1) . '. Waiting 1s. Error: ' . $th->getMessage());
                sleep(1);
                return $this->createFolderIfNotExists($folderName, $parentFolderId, $retry + 1);
            }

            debug_log($this->identifier . ' fail to create backups folder. Error message: ' . $th->getMessage());
            return false;
        }
    }
}
