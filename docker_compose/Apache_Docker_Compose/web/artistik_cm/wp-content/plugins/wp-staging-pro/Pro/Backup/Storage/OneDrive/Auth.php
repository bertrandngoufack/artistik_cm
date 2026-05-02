<?php

namespace WPStaging\Pro\Backup\Storage\OneDrive;

use UnexpectedValueException;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Utils\Sanitize;
use WPStaging\Framework\Utils\Strings;
use WPStaging\Framework\Security\Auth as WPStagingAuth;
use WPStaging\Pro\Backup\Storage\AbstractStorage;
use WPStaging\Framework\Traits\HttpRequestTrait;
use WPStaging\Pro\Backup\Storage\OneDrive\QuickXorHash;

use function WPStaging\functions\debug_log;

class Auth extends AbstractStorage
{
    use HttpRequestTrait;

    /** @var string */
    const FOLDER_NAME = 'wpstaging-backups';

    /** @var string */
    const REDIRECT_BASE_URL = 'https://auth.wp-staging.com/onedrive';

    /** @var string */
    const ONE_DRIVE_API_BASE_URL = 'https://graph.microsoft.com/v1.0/me';

    /** @var string */
    const ACTION_ADMIN_POST_ONEDRIVE_AUTH = 'admin_post_wpstg-onedrive-auth';

    /** @var array */
    protected $options;

    /** @var Sanitize */
    protected $sanitize;

    public function __construct(WPStagingAuth $wpstagingAuth, Sanitize $sanitize)
    {
        if (!$this->isBusinessPlanOrHigher()) {
            return;
        }

        parent::__construct($wpstagingAuth);
        $this->identifier = 'one-drive';
        $this->label      = 'OneDrive';
        $this->sanitize   = $sanitize;
        $this->options    = empty($this->getOptions()) ? [] : $this->getOptions();
    }

    /**
     * @return bool
     */
    public function createBackupsDestination(): bool
    {
        $location       = $this->getBackupsLocation();
        $locationURI    = $this->getFoldersFromLocation($location);
        $parentFolderId = 'root';
        foreach ($locationURI as $folder) {
            $folderId = $this->getFolderIdByName($folder, $parentFolderId);
            if (!$folderId && $parentFolderId) {
                $folderId = $this->createFolder($folder, $parentFolderId);
            }

            if (!$folderId) {
                return false; // Early bail: fail to get or to create the current folder, no need to continue the loop. Something is wrong!
            }

            $parentFolderId = $folderId;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getBackupsLocation(): string
    {
        $options = $this->getOptions();
        return !empty($options['folderName']) ? $options['folderName'] : Auth::FOLDER_NAME;
    }

    /**
     * @param  string $folderName
     * @param  string $parentId
     * @return string empty string is returned on fail, and folder's id is returned on success!
     */
    public function createFolder(string $folderName, string $parentId = 'root'): string
    {
        try {
            $body = [
                'name'                              => $folderName,
                '@microsoft.graph.conflictBehavior' => 'rename',
                "folder"                            => new \stdClass(),
            ];
            $args = [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $this->options['accessToken'],
                ],
                'body'    => json_encode($body),
                'method'  => 'POST',
            ];

            $response = $this->getRequestBody(self::ONE_DRIVE_API_BASE_URL . "/drive/items/$parentId/children/", $args);
            if (!isset($response['id'])) {
                return '';
            }

            return $response['id'];
        } catch (\Throwable $th) {
            debug_log("Failed to create folder. Error message: " . $th->getMessage());
            return '';
        }
    }

    /**
     * Get folders from storage Location
     *
     * @param string $backupLocation
     *
     * @return array
     */
    public function getFoldersFromLocation(string $backupLocation): array
    {
        $locationURI = explode('/', $backupLocation);
        return array_filter(array_map('trim', $locationURI), function ($folder) {
            return !empty($folder);
        });
    }

    /**
     * @param string $folderName
     * @param string $parent 'root'
     * @return string empty string is returned if not found
     */
    public function getFolderIdByName(string $folderName, string $parentID = 'root'): string
    {
        try {
            $url = self::ONE_DRIVE_API_BASE_URL . "/drive/items/$parentID/children";
            $args = [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $this->options['accessToken'],
                ],
                'method'  => 'GET',
            ];

            $response = $this->getRequestBody($url, $args);
            if (!isset($response['value'][0]['id'])) {
                return ''; // Folder not found
            }

            // Search for the folder by name in the response
            foreach ($response['value'] as $item) {
                if ($item['name'] === $folderName && $item['folder']) {
                    return $item['id'];
                }
            }
        } catch (\Throwable $th) {
            debug_log("Failed to get folder id. Error message: " . $th->getMessage());
        }

        return '';
    }

    public function getFolderByPath(string $folderPath): array
    {
        $folderPath = trim($folderPath, '/');
        if (empty($this->options['accessToken']) || $folderPath === '') {
            return [];
        }

        $encodedPath = implode('/', array_map('rawurlencode', $this->getFoldersFromLocation($folderPath)));
        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->options['accessToken'],
            ],
            'method'  => 'GET',
        ];

        try {
            $response = $this->getRequestBody(self::ONE_DRIVE_API_BASE_URL . '/drive/root:/' . $encodedPath . ':?$select=id,name,folder', $args);
            if (!is_array($response) || empty($response['id']) || empty($response['folder'])) {
                return [];
            }

            return $response;
        } catch (\Throwable $th) {
            // A 404 means the folder path does not exist anymore.
            if (strpos($th->getMessage(), 'Error Code: 404') !== false) {
                return [];
            }

            debug_log("Failed to get OneDrive folder by path: " . $folderPath . ". Error: " . $th->getMessage());
            return [];
        }
    }

    /**
     * @param  array $options
     * @return bool
     */
    public function saveOptions($options = []): bool
    {
        $toReturn      = parent::saveOptions($options);
        $this->options = $this->getOptions();
        return $toReturn;
    }

    /**
     * Get Authorization URL
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
                'state' => urlencode((string)admin_url('admin-post.php')),
            ],
            self::REDIRECT_BASE_URL
        );

        return $link;
    }

    /**
     * @return bool Returns false if the request fails, otherwise returns the response.
     */
    public function testConnection(): bool
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
            $this->getRequestBody(self::ONE_DRIVE_API_BASE_URL, $args);
        } catch (\Throwable $th) {
            return false;
        }

        return true;
    }

    /**
     * Refresh the access token based on the refresh token
     *
     * @return bool
     */
    public function refreshToken(): bool
    {
        if (empty($this->options['refreshToken'])) {
            return false;
        }

        $body = [
            'refresh_token' => $this->options['refreshToken'],
        ];
        $args = [
            'body'   => $body,
            'method' => 'POST',
        ];
        $link = self::REDIRECT_BASE_URL . '/refreshToken';

        try {
            /**
             * @see https://learn.microsoft.com/en-us/graph/auth-v2-user?tabs=http#response-1
             */
            $response = $this->getRequestBody($link, $args);
            if (isset($response['access_token'])) {
                $this->options['accessToken']     = $response['access_token'];
                $this->options['expiresIn']       = $response['expires_in'];
                $this->options['isAuthenticated'] = true;
                return $this->saveOptions($this->options);
            }
        } catch (\Throwable $th) {
            debug_log("Failed to refresh token. Error message: " . $th->getMessage());
        }

        return false;
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

        $this->options['accessToken']  = isset($_GET['access_token']) ? $this->sanitize->decodeBase64AndSanitize($_GET['access_token']) : '';
        $this->options['refreshToken'] = isset($_GET['refresh_token']) ? $this->sanitize->decodeBase64AndSanitize($_GET['refresh_token']) : '';

        if (empty($this->options['accessToken']) || empty($this->options['refreshToken'])) {
            debug_log('Fail to authenticate to oneDrive account some data are missing.');
            wp_die(esc_html__('Authentication failed please try again.', 'wp-staging'));
        }

        $this->options['expiresIn']       = isset($_GET['expires_in']) ? $this->sanitize->sanitizeInt($_GET['expires_in']) : 0;
        $this->options['isAuthenticated'] = true;

        // Because it is authentication process, at least one token is needed before saving data.
        if (!empty($this->options['refreshToken']) || !empty($this->options['accessToken'])) {
            parent::saveOptions($this->options);
            $this->saveStorageAccountInfo();
        }

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
                'sub-tab'      => $this->getIdentifier(),
                'auth-storage' => 'true',
            ],
            admin_url('admin.php')
        );

        wp_redirect($redirectURL);
    }

    /**
     * @param array $settings
     * @return bool
     */
    public function updateSettings($settings): bool
    {
        $backupLocation = empty($settings['folder_name']) ? self::FOLDER_NAME : $this->sanitize->sanitizeString($settings['folder_name']);
        if (!$this->isValidLocation($backupLocation)) {
            return false;
        }

        $this->options['folderName']       = $backupLocation;
        $this->options['maxBackupsToKeep'] = !empty($settings['max_backups_to_keep']) && $settings['max_backups_to_keep'] > 0 ? $this->sanitize->sanitizeInt($settings['max_backups_to_keep']) : 2;
        $this->options['lastUpdated']      = time();

        return $this->saveOptions($this->options);
    }

    /**
     * Revoke both access and refresh token,
     * Also unauthenticate the provider
     * @return bool
     */
    public function revoke(): bool
    {
        if (!$this->wpstagingAuth->isAuthenticatedRequest()) {
            return false;
        }

        if (!empty($this->options['accessToken'])) {
            $args = [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $this->options['accessToken'],
                ],
                'method'  => 'POST',
            ];
            try {
                $this->getRequestBody(self::ONE_DRIVE_API_BASE_URL . "/revokeSignInSessions", $args);
            } catch (\Throwable $th) {
                //no-op;
            }
        }

        return parent::saveOptions([]);
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        $toReturn = [];
        if (empty($this->options['accessToken'])) {
            return $toReturn;
        }

        $folderName = empty($this->options['folderName']) ? self::FOLDER_NAME : $this->options['folderName'];

        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->options['accessToken'],
            ],
        ];

        try {
            /**
             * @see https://learn.microsoft.com/en-us/graph/api/driveitem-list-children?view=graph-rest-1.0&tabs=http for how to get files
             * @see https://learn.microsoft.com/en-us/graph/query-parameters?tabs=http#orderby-parameter for how to use query
             */
            $responseBody = $this->getRequestBody(self::ONE_DRIVE_API_BASE_URL . '/drive/root:/' . trim($folderName, '/') . ':/children?$orderby=lastModifiedDateTime desc', $args);
            if (empty($responseBody['value'])) {
                return $toReturn;
            }

            $toReturn = array_filter($responseBody['value'], function ($file) {
                if (empty($file['file'])) {
                    return false;
                }

                return true;
            });

            return $toReturn;
        } catch (\Throwable $th) {
            debug_log("Failed to get files. Error message: " . $th->getMessage());
            return [];
        }
    }

    /**
     * @return array
     */
    public function getBackups(): array
    {
        $files = $this->getFiles();

        $backups = [];
        $strings = WPStaging::make(Strings::class);
        foreach ($files as $key => $file) {
            if (empty($file['name'])) {
                continue;
            }

            $fileName = $file['name'];
            if ($strings->endsWith($fileName, '.wpstg') || $strings->endsWith($fileName, '.sql')) {
                $date                                  = new \DateTime($file['lastModifiedDateTime']);
                $backups[$key]                         = $file;
                $backups[$key]['dateCreatedTimestamp'] = $date->format('Y-m-d H:i:s');
                $backups[$key]['storageProviderName']  = $this->getIdentifier();
                $backups[$key]['type']                 = $this->label;
            }
        }

        return $backups;
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
     * @see WPStaging\Pro\Backup\Task\Tasks\JobBackup\AbstractStorageTask::shouldCleanOldBackupsForRemoteUpload
     * @param string $fileName
     * @return bool
     */
    public function deleteFile(string $fileName): bool
    {
        $remoteFile = $this->getBackup($fileName);
        $args = [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $this->options['accessToken'],
            ],
            'method'  => 'DELETE',
        ];
        try {
            $this->getRequestBody(self::ONE_DRIVE_API_BASE_URL . '/drive/items/' . $remoteFile['id'], $args);
        } catch (\Throwable $th) {
            debug_log("Failed to delete file. Error message: " . $th->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Compute the Hash of the uploaded file.
     *
     * @param string $file
     * @return string
     * @throws UnexpectedValueException on failure
     */
    public function computeFileHash(string $file): string
    {
        /**
         * @todo Find a way to chunk calculate the hash; otherwise the current code will trigger memory error.
         * @see https://github.com/wp-staging/wp-staging-pro/issues/4112
         */
        // clearstatcache();
        // if (!file_exists($file) || !filesize($file)) {
        //     throw new UnexpectedValueException("File does not exist or is empty, file: $file");
        // }

        // /** @var QuickXorHash $quickXorHash */
        // $quickXorHash = WPStaging::make(QuickXorHash::class);
        // return $quickXorHash->computeHash($file);

        return '';
    }

    /**
     * Save storage account info in options(displayed in storage page)
     *
     * @return void
     */
    public function saveStorageAccountInfo()
    {
        $options  = $this->getOptions();
        $response = $this->getUserData();
        if (!empty($response['displayName'])) {
            $options['userData']                = $response;
            $options['userData']['displayName'] = $response['displayName'];
        }

        $response = $this->getStorageInfo();
        if (!empty($response['quota'])) {
            $options['storageInfo']                            = $response;
            $options['storageInfo']['used']                    = $response['quota']['used'];
            $options['storageInfo']['allocation']['allocated'] = $response['quota']['total'];
        }

        $this->saveOptions($options);
    }

    /**
     * @return false|array Returns false on api request failure.
     * @see https://learn.microsoft.com/en-us/graph/api/user-get?view=graph-rest-1.0&tabs=http for more information.
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
            return $this->getRequestBody(self::ONE_DRIVE_API_BASE_URL, $args);
        } catch (\Throwable $th) {
            debug_log('Failed to get user data. ' . $th->getMessage());
            return false;
        }
    }

    /**
     * @return false|array Returns false on api request failure.
     * @see https://learn.microsoft.com/en-us/graph/api/drive-get?view=graph-rest-1.0&tabs=http for more information.
     */
    public function getStorageInfo()
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
            return $this->getRequestBody(self::ONE_DRIVE_API_BASE_URL . '/drive?$select=quota', $args);
        } catch (\Throwable $th) {
            debug_log('Failed to get storage data. ' . $th->getMessage());
            return false;
        }
    }
}
