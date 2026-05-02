<?php

namespace WPStaging\Pro\Backup\Storage\GoogleDrive;

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Security\Auth as WPStagingAuth;
use WPStaging\Framework\Utils\Sanitize;
use WPStaging\Framework\Utils\Strings;
use WPStaging\Pro\Backup\Storage\AbstractStorage;
use WPStaging\Framework\Traits\HttpRequestTrait;

use function WPStaging\functions\debug_log;

class Auth extends AbstractStorage
{
    use HttpRequestTrait;

    /** @var string */
    const REDIRECT_URL = 'https://auth.wp-staging.com/googledrive/v2';

    /** @var string */
    const REFRESH_URL = 'https://auth.wp-staging.com/googledrive/v2/refreshToken';

    /** @var string */
    const FOLDER_NAME = 'wpstaging-backups';

    /** @var string */
    const GOOGLEDRIVE_OAUTH2_URL = 'https://oauth2.googleapis.com';

    /** @var string */
    const GOOGLEDRIVE_API_V3_BASE_URL = 'https://www.googleapis.com/drive/v3';

    /** @var string */
    const GOOGLEDRIVE_API_V3_UPLOAD_URL = 'https://www.googleapis.com/upload/drive/v3';

    /** @var string */
    const GOOGLEDRIVE_REQUIRED_SCOPES = 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/drive.file';

    /** @var string */
    const ACTION_ADMIN_POST_GOOGLEDRIVE_AUTH = 'admin_post_wpstg-googledrive-auth';

    /** @var string */
    const ACTION_ADMIN_POST_GOOGLEDRIVE_API_AUTH = 'admin_post_wpstg-googledrive-api-auth';

    /** @var string */
    const DRIVE_TYPE_PERSONAL = 'mydrive';

    /** @var string */
    const DRIVE_TYPE_SHARED_WITH_ME = 'sharedwithme';

    /** @var string */
    const DRIVE_TYPE_SHARED_DRIVE = 'shareddrive';

    /** @var string */
    const DRIVE_VALIDATION_TYPE_SHARED = 'shared';

    /** @var string */
    const DRIVE_VALIDATION_TYPE_FOLDER = 'folder';

    /** @var string */
    const DRIVE_VALIDATION_TYPE_INVALID = 'invalid';

    /** @var string */
    const DRIVE_ROOT_FOLDER_ID = 'root';

    /** @var string */
    private $redirectURI;

    /** @var Sanitize */
    protected $sanitize;

    /** @var array */
    protected $options;

    public function __construct(Sanitize $sanitize, WPStagingAuth $wpstagingAuth)
    {
        parent::__construct($wpstagingAuth);
        $this->identifier = 'googledrive';
        $this->label = 'Google Drive';
        $this->redirectURI = add_query_arg(
            [
                'action' => 'wpstg-googledrive-api-auth',
            ],
            network_admin_url('admin-post.php')
        );
        $this->sanitize = $sanitize;
    }

    /**
     * @return void
     */
    public function testConnection()
    {
        // no-op
    }

    /**
     * Verifies if access token is still valid, if not try to refresh it.
     *
     * @return bool
     */
    public function isAccessTokenValid(): bool
    {
        return $this->saveStorageAccountInfo() || $this->refreshToken();
    }

    /**
     * Get Authorization URL
     * @param string $redirectTo
     * @return string
     */
    public function getAuthenticationURL(string $redirectTo = ""): string
    {
        if (empty($redirectTo)) {
            $redirectTo = 'wpstg-settings';
        }

        set_transient(AbstractStorage::TRANSIENT_REDIRECT_URL, $redirectTo, 300);
        return add_query_arg(
            [
                'state' => urlencode((string)admin_url('admin-post.php')),
            ],
            self::REDIRECT_URL
        );
    }

    /**
     * Authentication of the storage
     * @return void
     */
    public function authenticate()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to access this page.', 'wp-staging'));
        }

        $options = $this->getOptions();
        $options = array_merge($options, [
            'isAuthenticated' => true,
            'refreshToken'    => isset($_GET['refresh_token']) ? $this->sanitize->decodeBase64AndSanitize($_GET['refresh_token']) : '',
            'accessToken'     => isset($_GET['access_token']) ? $this->sanitize->decodeBase64AndSanitize($_GET['access_token']) : '',
            'expiresIn'       => isset($_GET['expires_in']) ? $this->sanitize->sanitizeInt($_GET['expires_in']) : 0,
            'showNotice'      => false,
        ]);

        // Because it is authentication process, at least one token is needed before saving data.
        if (!empty($options['refreshToken']) || !empty($options['accessToken'])) {
            parent::saveOptions($options);
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
                'sub-tab'      => 'googledrive',
                'auth-storage' => 'true',
            ],
            admin_url('admin.php')
        );

        wp_redirect($redirectURL);
    }

    /**
     * Save storage account info in options(displayed in storage page)
     *
     * @return bool
     */
    public function saveStorageAccountInfo(): bool
    {
        $options = $this->getOptions();
        if (empty($options['accessToken'])) {
            return false;
        }

        $args = [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $options['accessToken'],
            ],
            'method'  => 'GET',
        ];
        try {
            $response = $this->getRequestBody(self::GOOGLEDRIVE_API_V3_BASE_URL . '/about?fields=*', $args);
        } catch (\Throwable $th) {
            //no-op;
        }

        if (empty($response['user'])) {
            return false;
        }

        $options['userData']['displayName']                = $response['user']['displayName'] ?? '';
        $options['storageInfo']['used']                    = $response['storageQuota']['usage'] ?? '';
        $options['storageInfo']['allocation']['allocated'] = $response['storageQuota']['limit'] ?? '';
        if (!$options['userData']['displayName'] || !$options['storageInfo']['used'] || !$options['storageInfo']['allocation']['allocated']) {
            return false;
        }

        $this->saveOptions($options);
        return true;
    }

    /**
     * Authenticate when user set his own API credentials
     */
    public function apiAuthenticate()
    {
        $userAuthorizedScopesAsStr     = filter_input(INPUT_GET, 'scope');
        $userAuthorizedScopesAsArr     = array_filter(explode(' ', $userAuthorizedScopesAsStr));
        $authorizedScopesRequiredAsArr = explode(' ', self::GOOGLEDRIVE_REQUIRED_SCOPES);
        $isAuthorizedAllRequiredScopes = true;
        foreach ($authorizedScopesRequiredAsArr as $authorizedScopesRequired) {
            if (!in_array($authorizedScopesRequired, $userAuthorizedScopesAsArr)) {
                $isAuthorizedAllRequiredScopes = false;
                break;
            }
        }

        if (!$isAuthorizedAllRequiredScopes) {
            echo sprintf('<strong style="font-family: arial,sans-serif;font-size:12px;">%s</strong>', esc_html__('You have not granted permissions required by the WP STAGING plugin. Please go back and retry the authorization.', 'wp-staging'));
            die;
        }

        $options = $this->getOptions();
        $code = isset($_GET['code']) ? $this->sanitize->sanitizeString($_GET['code']) : '';
        $body = [
            'code'          => $code,
            'client_id'     => $options['googleClientId'],
            'client_secret' => $options['googleClientSecret'],
            'redirect_uri'  => $this->getRedirectURI(),
            'grant_type'    => 'authorization_code',
        ];
        $args = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'method'  => 'POST',
            'body'    => json_encode($body),
        ];
        try {
            $token = $this->getRequestBody(self::GOOGLEDRIVE_OAUTH2_URL . '/token', $args);
            $urlToRedirect = add_query_arg([
                'action'        => 'wpstg-googledrive-auth',
                'access_token'  => base64_encode($token['access_token']),
                'refresh_token' => base64_encode($token['refresh_token']),
                'expires_in'    => intval($token['expires_in']),
            ], admin_url('admin-post.php'));

            header('Location: ' . $urlToRedirect);
        } catch (\Throwable $th) {
            debug_log('Api authentication failed. Error message: ' . $th->getMessage());
        }
    }

    /**
     * @param array $settings
     * @return bool|\WP_Error
     */
    public function updateSettings($settings)
    {
        $options        = $this->getOptions();
        $backupLocation = empty($settings['folder_name']) ? self::FOLDER_NAME : $this->sanitizeGoogleDriveLocation($settings['folder_name']);
        if (!$this->isValidLocation($backupLocation)) {
            return false;
        }

        $options['folderName']       = $backupLocation;
        $options['maxBackupsToKeep'] = isset($settings['max_backups_to_keep']) ? $settings['max_backups_to_keep'] : 0;
        $options['maxBackupsToKeep'] = $options['maxBackupsToKeep'] > 0 ? $options['maxBackupsToKeep'] : 15;
        $options['driveType']        = isset($settings['drive_type']) ? $settings['drive_type'] : self::DRIVE_TYPE_PERSONAL;
        $options['sharedDriveId']    = isset($settings['shared_drive_id']) ? $settings['shared_drive_id'] : '';

        if ($options['driveType'] === self::DRIVE_TYPE_PERSONAL) {
            $options['sharedDriveId'] = '';
        }

        if (!empty($settings['google_client_id'])) {
            $options['googleClientId'] = $settings['google_client_id'];
        }

        if (!empty($settings['google_client_secret'])) {
            $options['googleClientSecret'] = $settings['google_client_secret'];
        }

        // Validate sharedDriveId if provided and driveType is not mydrive
        if (!empty($options['sharedDriveId']) && $options['driveType'] !== self::DRIVE_TYPE_PERSONAL) {
            $this->saveOptions($options);
            $type = $this->validateSharedDriveOrFolderId($options['sharedDriveId'], $options['driveType']);
            if ($options['driveType'] === self::DRIVE_TYPE_SHARED_DRIVE && $type !== self::DRIVE_VALIDATION_TYPE_SHARED) {
                $this->clearDriveId();
                return new \WP_Error('invalid_shared_drive', "The ID you entered is not Shared Drive ID.");
            }

            if ($options['driveType'] === self::DRIVE_TYPE_SHARED_WITH_ME && $type !== self::DRIVE_VALIDATION_TYPE_FOLDER) {
                $this->clearDriveId();
                return new \WP_Error('invalid_shared_folder', "The ID you entered is not shared folder ID.");
            }
        }

        $options['lastUpdated'] = time();
        $result = $this->saveOptions($options);
        if (empty($options['isAuthenticated']) && !empty($options['googleClientId']) && !empty($options['googleClientSecret'])) {
            $params = [
                'client_id'              => $options['googleClientId'],
                'redirect_uri'           => $this->getRedirectURI(),
                'response_type'          => 'code',
                'scope'                  => self::GOOGLEDRIVE_REQUIRED_SCOPES,
                'access_type'            => 'offline',
                'include_granted_scopes' => 'true',
                'prompt'                 => 'consent',
            ];
            $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
            wp_send_json_success($url);
        }

        return $result;
    }

    /**
     * Get folders from Google Drive Location
     *
     * @param string $backupLocation
     *
     * @return array
     */
    public function getFoldersFromLocation($backupLocation)
    {
        $locationURI = explode('/', $backupLocation);
        return array_filter(array_map('trim', $locationURI), function ($folder) {
            return !empty($folder);
        });
    }

    /**
     * Clear the current storage settings from database (Revoking token should be done by user himself because it will disconnect all other sites, as well).
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

    /** @return array */
    public function getFiles()
    {
        if (!$this->isAccessTokenValid()) {
            $this->showLoginIssueNotice();
            return [];
        }

        $options        = $this->getOptions();
        $backupLocation = isset($options['folderName']) ? $options['folderName'] : self::FOLDER_NAME;
        $driveType      = isset($options['driveType']) ? $options['driveType'] : self::DRIVE_TYPE_PERSONAL;
        $sharedDriveId  = isset($options['sharedDriveId']) ? $options['sharedDriveId'] : '';
        $folderId       = $this->getFolderIdByLocation($backupLocation, $driveType, $sharedDriveId);

        if (!$folderId) {
            return [];
        }

        // If we have folder id then no need to show notice!
        if (!empty($options['showNotice'])) {
            $this->hideLoginIssueNotice();
        }

        $args    = [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $options['accessToken'],
            ],
            'method'  => 'GET',
        ];
        $params = [
            'q'       => 'trashed = false and "' . $folderId . '" in parents',
            'fields'  => 'nextPageToken, files(id, name, mimeType, size, createdTime, modifiedTime)',
            'orderBy' => 'modifiedTime desc',
        ];

        $url = self::GOOGLEDRIVE_API_V3_BASE_URL . '/files?' . http_build_query($params);
        $url = $this->appendSharedDriveParams($url, $driveType, $sharedDriveId);

        try {
            $response = $this->getRequestBody($url, $args);
            return $response['files'] ?? [];
        } catch (\Throwable $th) {
        }

        return [];
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
            if ($strings->endsWith($file['name'], '.wpstg') || $strings->endsWith($file['name'], '.sql')) {
                $date                                = new \DateTime($file['createdTime']);
                $backups[$key]                       = json_decode(json_encode($file));
                $backups[$key]->dateCreatedTimestamp = $date->format('Y-m-d H:i:s');
                $backups[$key]->storageProviderName  = $this->getIdentifier();
                $backups[$key]->type                 = $this->label;
            }
        }

        return $backups;
    }

    /**
     * @param string $location A folder name or path separated with slashes to the backup file
     * @param string $driveType
     * @param string $sharedDriveId
     * @return false|string
     */
    public function getFolderIdByLocation($location, $driveType = self::DRIVE_TYPE_PERSONAL, $sharedDriveId = '')
    {
        $locationURI = $this->getFoldersFromLocation($location);
        $folderId    = self::DRIVE_ROOT_FOLDER_ID;
        if (!empty($sharedDriveId) && ($this->isSharedWithMeDrive($driveType) || $this->isSharedDrive($driveType))) {
            $folderId = $sharedDriveId;
        }

        foreach ($locationURI as $folder) {
            $folderId = $this->getFolderIdByName($folder, $folderId, $driveType, $sharedDriveId);
            if ($folderId === '') {
                return false;
            }
        }

        return $folderId;
    }

    /**
     * @param string $path
     * @param string $parent self::DRIVE_ROOT_FOLDER_ID
     * @param string $driveType
     * @param string $sharedDriveId
     * @return string return empty string if fails!
     */
    public function getFolderIdByName($path, $parent = self::DRIVE_ROOT_FOLDER_ID, $driveType = self::DRIVE_TYPE_PERSONAL, $sharedDriveId = '')
    {
        if (!$this->isAccessTokenValid()) {
            $this->showLoginIssueNotice();
            return '';
        }

        $options = $this->getOptions();
        $args    = [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $options['accessToken'],
            ],
            'method'  => 'GET',
        ];

        $params = [
            'q'      => "name ='" . $path . "' and trashed = false and '" . $parent . "' in parents and mimeType = 'application/vnd.google-apps.folder'",
            'fields' => 'nextPageToken, files(id, name, mimeType)',
        ];

        $url = self::GOOGLEDRIVE_API_V3_BASE_URL . '/files?' . http_build_query($params);
        $url = $this->appendSharedDriveParams($url, $driveType, $sharedDriveId);

        try {
            $response = $this->getRequestBody($url, $args);
        } catch (\Throwable $th) {
            debug_log(sprintf('Can not list files from Google Drive. Please reconnect to Google Drive via WP STAGING > Settings > Storage Providers. Error: %s', $th->getMessage()));
            $this->showLoginIssueNotice();
            return '';
        }

        if (empty($response['files']['0']['id'])) {
            // Should not happen
            return '';
        }

        return $response['files']['0']['id'];
    }

    /**
     * This will refresh access token by using user api credentials.
     * @return bool
     */
    public function refreshAccessToken(): bool
    {
        $options = $this->getOptions();
        if (empty($options['refreshToken'])) {
            return false;
        }

        if (empty($options['googleClientId']) || empty($options['googleClientSecret'])) {
            return false;
        }

        $body = [
            'client_id'     => $options['googleClientId'],
            'client_secret' => $options['googleClientSecret'],
            'grant_type'    => 'refresh_token',
            'refresh_token' => $options['refreshToken'],
        ];

        $args = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'method'  => 'POST',
            'body'    => json_encode($body),
        ];
        try {
            $response = $this->getRequestBody(self::GOOGLEDRIVE_OAUTH2_URL . '/token', $args);
            if (isset($response['access_token'])) {
                $options['accessToken']     = $response['access_token'];
                $options['expiresIn']       = $response['expires_in'];
                $options['isAuthenticated'] = true;
                $options['showNotice']      = false;
                return $this->saveOptions($options);
            }
        } catch (\Throwable $th) {
            debug_log('Failed to refresh token using user api credentials, error message: ' . $th->getMessage());
        }
        return false;
    }

    /**
     * @return string
     */
    public function getRedirectURI()
    {
        return $this->redirectURI;
    }

    /**
     * @return bool
     */
    public function refreshToken()
    {
        $options = $this->getOptions();
        if (empty($options['refreshToken'])) {
            return false;
        }

        $clientSecret = isset($options['googleClientSecret']) ? $options['googleClientSecret'] : '';
        if (empty($clientSecret)) {
            $refreshResult = $this->refreshAccessTokenRemotely();
        } else {
            $refreshResult = $this->refreshAccessToken();
        }

        return $refreshResult;
    }

    /**
     * This will refresh access token by calling our auth api
     *
     * @param  int $retry
     * @return bool
     */
    protected function refreshAccessTokenRemotely(int $retry = 0): bool
    {
        $options = $this->getOptions();
        if (empty($options['refreshToken'])) {
            return false;
        }

        $body = [
            'refresh_token' => $options['refreshToken'],
        ];
        $args = [
            'body'   => $body,
            'method' => 'POST',
        ];

        try {
            /**
             * @see https://developers.google.com/identity/protocols/oauth2/web-server#offline
             */
            $response     = $this->getRemoteRequest(self::REFRESH_URL, $args);
            $responseCode = wp_remote_retrieve_response_code($response);
            if ($responseCode === 403 && $retry < 1) {
                // sleep the code a few seconds before to retry in case the remote server (wpstg server) is overloaded.
                debug_log("Retrying to refresh token in few seconds");
                sleep(5);
                return $this->refreshAccessTokenRemotely(++$retry);
            }

            $responseBody = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($responseBody['access_token'])) {
                $options['accessToken']     = $responseBody['access_token'];
                $options['expiresIn']       = $responseBody['expires_in'];
                $options['isAuthenticated'] = true;
                $options['showNotice']      = false;
                return $this->saveOptions($options);
            }
        } catch (\Throwable $th) {
            debug_log("Failed to refresh token on remote. Error message: " . $th->getMessage());
        }

        return false;
    }

    /**
     * Trim extra spaces from each folder name
     *
     * @param string $backupLocation
     * @return string
     */
    public function sanitizeGoogleDriveLocation($backupLocation)
    {
        $locationURI = $this->getFoldersFromLocation(trim($backupLocation, '/'));
        return implode('/', $locationURI);
    }

    /**
     * @see WPStaging\Pro\Backup\Task\Tasks\JobBackup\AbstractStorageTask::shouldCleanOldBackupsForRemoteUpload
     * @param string $file
     * @return bool
     */
    public function deleteFile(string $file): bool
    {
        $files = $this->getFiles();
        foreach ($files as $fileInfo) {
            if ($fileInfo['name'] === $file) {
                return $this->deleteRemoteFileById($fileInfo['id']);
            }
        }

        return false;
    }

    /**
     * @param string $fileId
     * @return bool
     */
    public function deleteRemoteFileById(string $fileId): bool
    {
        $options = $this->getOptions();
        if (empty($options['accessToken'])) {
            return false;
        }

        $capabilities = $this->getUserCapabilities($fileId);
        if (empty($capabilities)) {
            debug_log("Failed to get user capabilities for file ID: {$fileId}. Access token might be invalid.");
            $this->showLoginIssueNotice();
            return false;
        }

        if (empty($capabilities['canDelete']) && !empty($capabilities['canTrash'])) {
            return $this->trashFileById($fileId);
        }

        if (empty($capabilities['canDelete']) && empty($capabilities['canTrash'])) {
            debug_log("User does not have permission to delete or trash the file with ID: {$fileId}.");
            return false;
        }

        try {
            $args = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $options['accessToken'],
                ],
                'method'  => 'DELETE',
            ];

            $url           = self::GOOGLEDRIVE_API_V3_BASE_URL . "/files/{$fileId}";
            $driveType     = isset($options['driveType']) ? $options['driveType'] : self::DRIVE_TYPE_PERSONAL;
            $sharedDriveId = isset($options['sharedDriveId']) ? $options['sharedDriveId'] : '';

            if ($this->isSharedDrive($driveType) && !empty($sharedDriveId)) {
                $url .= '?supportsAllDrives=true';
            }

            $this->getRequestBody($url, $args);
            return true;
        } catch (\Throwable $th) {
            debug_log("Failed to delete remote file with ID: {$fileId}. Error message: " . $th->getMessage());
        }

        return false;
    }

    public function appendSharedDriveParams(string $baseUrl, string $driveType, string $sharedDriveId): string
    {
        if (empty($sharedDriveId)) {
            return $baseUrl;
        }

        $params = [];
        if ($this->isSharedDrive($driveType)) {
            $params['supportsAllDrives'] = 'true';
            $params['driveId']           = $sharedDriveId;
            $params['includeItemsFromAllDrives'] = 'true';
            $params['corpora']                   = 'drive';
        }

        $separator = strpos($baseUrl, '?') === false ? '?' : '&';
        return $baseUrl . $separator . http_build_query($params);
    }

    /**
     * @return void
     */
    private function showLoginIssueNotice()
    {
        $options               = $this->getOptions();
        $options['showNotice'] = true;

        parent::saveOptions($options);

        return;
    }

    /**
     * @return void
     */
    private function hideLoginIssueNotice()
    {
        $options               = $this->getOptions();
        $options['showNotice'] = false;

        parent::saveOptions($options);

        return;
    }

    /**
     * @param string $driveId
     * @param string $driveType
     * @return string self::DRIVE_VALIDATION_TYPE_SHARED for shared drive, self::DRIVE_VALIDATION_TYPE_FOLDER for shared folder, self::DRIVE_VALIDATION_TYPE_INVALID for invalid/inaccessible
     */
    private function validateSharedDriveOrFolderId(string $driveId, string $driveType): string
    {
        if (empty($driveId) || empty($driveType) || !$this->isAccessTokenValid()) {
            return self::DRIVE_VALIDATION_TYPE_INVALID;
        }

        $options     = $this->getOptions();
        $accessToken = $options['accessToken'] ?? '';
        if (empty($accessToken)) {
            return self::DRIVE_VALIDATION_TYPE_INVALID;
        }

        try {
            $testFileId = $this->createTestFile($accessToken, $driveId, $driveType);
            if (!$testFileId) {
                return self::DRIVE_VALIDATION_TYPE_INVALID;
            }

            $folderType = $this->analyzeFolderTypeFromTestFile($accessToken, $testFileId);
            $this->deleteRemoteFileById($testFileId);

            return $folderType;
        } catch (\Throwable $th) {
            debug_log("Failed to validate drive using test file approach. Error: " . $th->getMessage());
            return self::DRIVE_VALIDATION_TYPE_INVALID;
        }
    }

    /**
     * @param string $accessToken
     * @param string $parentFolderId
     * @param string $driveType
     * @return string|false Test file ID on success, false on failure
     */
    private function createTestFile(string $accessToken, string $parentFolderId, string $driveType)
    {
        if (empty($accessToken)) {
            return false;
        }

        $testFileName    = 'wpstaging_test_' . time() . '_' . bin2hex(random_bytes(4)) . '.txt';
        $testFileContent = 'WP Staging test file - safe to delete';
        $fileMetadata    = [
            'name'    => $testFileName,
            'parents' => [$parentFolderId],
        ];

        $multipartBoundary = 'wpstaging_boundary_' . time() . '_' . bin2hex(random_bytes(4));
        $multipartBody     = "--{$multipartBoundary}\r\n";
        $multipartBody    .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
        $multipartBody    .= json_encode($fileMetadata) . "\r\n";
        $multipartBody    .= "--{$multipartBoundary}\r\n";
        $multipartBody    .= "Content-Type: text/plain\r\n\r\n";
        $multipartBody    .= $testFileContent . "\r\n";
        $multipartBody    .= "--{$multipartBoundary}--";

        $url = self::GOOGLEDRIVE_API_V3_UPLOAD_URL . '/files?uploadType=multipart';
        if ($this->isSharedDrive($driveType)) {
            $url .= '&supportsAllDrives=true&driveId=' . urlencode($parentFolderId);
        }

        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'multipart/related; boundary=' . $multipartBoundary,
            ],
            'method'  => 'POST',
            'body'    => $multipartBody,
        ];

        try {
            $response = $this->getRequestBody($url, $args);
            return $response['id'] ?? false;
        } catch (\Throwable $exception) {
            debug_log("Failed to create test file for validation. Error: " . $exception->getMessage());
            return false;
        }
    }

    /**
     * @param string $accessToken
     * @param string $fileId
     * @return string
     */
    private function analyzeFolderTypeFromTestFile(string $accessToken, string $fileId): string
    {
        if (empty($accessToken) || empty($fileId)) {
            return self::DRIVE_VALIDATION_TYPE_INVALID;
        }

        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
            'method'  => 'GET',
        ];

        $url = self::GOOGLEDRIVE_API_V3_BASE_URL . '/files/' . urlencode($fileId);

        $params = [
            'fields'            => 'driveId,parents,capabilities',
            'supportsAllDrives' => 'true',
        ];

        $url = $url . '?' . http_build_query($params);

        try {
            $response = $this->getRequestBody($url, $args);
            if (!empty($response['driveId'])) {
                return self::DRIVE_VALIDATION_TYPE_SHARED;
            }

            if (!empty($response['parents']) && isset($response['capabilities'])) {
                return self::DRIVE_VALIDATION_TYPE_FOLDER;
            }

            return self::DRIVE_VALIDATION_TYPE_INVALID;
        } catch (\Throwable $th) {
            debug_log("Failed to analyze test file metadata. Error: " . $th->getMessage());
            return self::DRIVE_VALIDATION_TYPE_INVALID;
        }
    }

    /**
     * @param string $fileId
     * @return array
     */
    private function getUserCapabilities(string $fileId): array
    {
        $options = $this->getOptions();
        if (empty($options['accessToken'])) {
            return [];
        }

        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $options['accessToken'],
            ],
        ];
        $baseUrl = self::GOOGLEDRIVE_API_V3_BASE_URL . '/files/' . urlencode($fileId);
        $query = [
            'fields'            => 'capabilities',
            'supportsAllDrives' => 'true',
        ];

        if (isset($options['driveType']) && $options['driveType'] === self::DRIVE_TYPE_SHARED_DRIVE && !empty($options['sharedDriveId'])) {
            $query['driveId']                   = $options['sharedDriveId'];
            $query['includeItemsFromAllDrives'] = 'true';
            $query['corpora']                   = 'drive';
        }

        $url = $baseUrl . '?' . http_build_query($query);

        try {
            $response = $this->getRequestBody($url, $args);
            if (!empty($response['capabilities'])) {
                return $response['capabilities'];
            }
        } catch (\Throwable $th) {
            debug_log(sprintf('Failed to check file capabilities for file ID %s. Error: %s', $fileId, $th->getMessage()));
        }

        return [];
    }

    /**
     * Trash a file on Google Drive.
     *
     * @param string $fileId
     * @return bool True if trashed successfully, false otherwise.
     */
    private function trashFileById(string $fileId): bool
    {
        $options = $this->getOptions();
        if (empty($options['accessToken'])) {
            return false;
        }

        $url  = self::GOOGLEDRIVE_API_V3_BASE_URL . '/files/' . urlencode($fileId) . '?supportsAllDrives=true';
        $args = [
            'method'  => 'PATCH',
            'headers' => [
                'Authorization' => 'Bearer ' . $options['accessToken'],
                'Content-Type'  => 'application/json',
            ],
            'body'    => json_encode(['trashed' => true]),
        ];
        try {
            $response = $this->getRequestBody($url, $args);
            return true;
        } catch (\Throwable $th) {
            debug_log(sprintf('Failed to trash file with ID %s. Error: %s', $fileId, $th->getMessage()));
            return false;
        }
    }

    /**
     * Clear drive ID and set drive type to personal
     * @return void
     */
    private function clearDriveId()
    {
        $options = $this->getOptions();
        $options['sharedDriveId'] = '';
        $options['driveType']     = self::DRIVE_TYPE_PERSONAL;
        $this->saveOptions($options);
    }

    /**
     * @param string $driveType
     * @return bool
     */
    private function isSharedWithMeDrive($driveType): bool
    {
        return $driveType === self::DRIVE_TYPE_SHARED_WITH_ME;
    }

    /**
     * @param string $driveType
     * @return bool
     */
    private function isSharedDrive($driveType): bool
    {
        return $driveType === self::DRIVE_TYPE_SHARED_DRIVE;
    }
}
