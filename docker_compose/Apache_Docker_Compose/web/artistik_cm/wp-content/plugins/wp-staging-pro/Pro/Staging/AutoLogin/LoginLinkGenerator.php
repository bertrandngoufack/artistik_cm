<?php

namespace WPStaging\Pro\Staging\AutoLogin;

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Adapter\SourceDatabase;
use WPStaging\Framework\Rest\Rest;
use WPStaging\Framework\Traits\IpResolverTrait;
use WPStaging\Pro\Auth\TemporaryLogins;
use WPStaging\Pro\Staging\Ajax\UserAccountSynchronizer;
use WPStaging\Staging\Sites;

/**
 * Class LoginLinkGenerator
 * Generates secure tokens for cross-site authentication
 */
class LoginLinkGenerator
{
    use IpResolverTrait;

    /** @var string */
    const OPTION_STAGING_PARENT_SITE = 'wpstg_parent_site';

    /** @var string */
    protected $stagingURL = '';

    /** @var string */
    protected $cloneID = '';

    /** @var Sites */
    private $sites;

    /** @var \wpdb */
    private $database;

    /** @var string */
    private $databasePrefix = '';

    /** @var array */
    private $currentClone = [];

    /** @var UserAccountSynchronizer */
    private $accountSync;

    /** @var int */
    private $tokenExpiry;

    /**
     * @param Sites $sites
     * @param UserAccountSynchronizer $accountSync
     */
    public function __construct(Sites $sites, UserAccountSynchronizer $accountSync)
    {
        $this->sites       = $sites;
        $this->accountSync = $accountSync;
    }

    /**
     * Generate a complete staging login URL for the current user on staging site
     * @param string $cloneID
     * @return string|\WP_Error The login URL or WP_Error on failure
     */
    public function generate(string $cloneID)
    {
        $currentUserId = get_current_user_id();
        if (!$currentUserId || !current_user_can('manage_options')) {
            return new \WP_Error('unauthorized', 'User must be logged in and have sufficient permissions.');
        }

        return $this->generateLoginUrlForUser($cloneID, $currentUserId, LoginAuthenticator::AUTO_LOGIN_ACTION);
    }

    /**
     * Generate a login URL for the current site with specified user details and expiry
     * @param string $userName
     * @param string $email
     * @param string $role
     * @param int $expiry $expiry = strtotime("+{$days} days +{$hours} hours +{$minutes} minutes") - time();
     * @return int|string|\WP_Error
     */
    public function generateCurrentSiteLoginUrl(string $userName, string $email, string $role, int $expiry)
    {
        if (empty($userName) || empty($email) || empty($role)) {
            return new \WP_Error('missing_parameters', 'Username, email and role are required');
        }

        $user = get_user_by('email', $email); // This is required to allow expiring links for existing users
        if (!$user && $expiry <= 0) {
            return new \WP_Error('missing_parameters', 'Expiry time must be set in the future.');
        }

        $this->tokenExpiry = time() + $expiry;
        // Generate secure token
        $token = $this->createSecureToken(false);
        if (is_wp_error($token)) {
            return $token;
        }

        if ($user) {
            $user->set_role($role);
            $userId        = $user->ID;
            $existingToken = $this->getTokenByUserId($userId);
        } else {
            $userId   = wp_insert_user([
                'user_login' => $userName,
                'user_pass'  => wp_generate_password(16, true, true),
                'role'       => $role,
                'user_email' => $email,
            ]);

            if (is_wp_error($userId)) {
                return $userId;
            }
        }

        if (!empty($existingToken)) {
            $token = $existingToken;
        }

        $hashed   = hash('sha256', $token);
        $metaKeys = array_values(LoginAuthenticator::AUTO_LOGIN_META_KEYS);

        // Clean old tokens
        foreach ($metaKeys as $metaKey) {
            delete_user_meta($userId, $metaKey);
        }

        // Insert new token/expiry
        $entries = [
            LoginAuthenticator::AUTO_LOGIN_META_KEYS['token']  => $hashed,
            LoginAuthenticator::AUTO_LOGIN_META_KEYS['expiry'] => $this->tokenExpiry,
        ];

        foreach ($entries as $metaKey => $metaValue) {
            if (!update_user_meta($userId, $metaKey, $metaValue)) {
                return new \WP_Error('meta_error', "Failed to store $metaKey in user meta.");
            }
        }

        return trailingslashit(home_url()) . "?action=" . LoginAuthenticator::TEMP_AUTO_LOGIN_ACTION . "&token=" . urlencode($token);
    }

    /**
     * Create a temporary user in the staging site and generate a login URL
     * @param string $cloneID The clone ID
     * @param string $role The user role for the temporary user
     * @param int $expiry Expiration time in seconds
     * @return string|\WP_Error The login URL or WP_Error on failure
     */
    public function generateTempUserLoginUrl(string $cloneID, string $role, int $expiry)
    {
        if (empty($cloneID) || empty($role)) {
            return new \WP_Error('missing_parameters', 'Clone ID and role are required');
        }

        $this->cloneID = $cloneID;
        if (!$this->isValidClone()) {
            return new \WP_Error('invalid_clone', 'The specified clone does not exist or is broken');
        }

        try {
            $this->setupStagingDBConnection();
            $this->cleanExistingTempUsers();
            $uniqueId          = substr(md5(uniqid((string)mt_rand(), true)), 0, 8);
            $username          = LoginAuthenticator::LOGIN_LINK_PREFIX . $uniqueId;
            $this->tokenExpiry = time() + $expiry;
            $domain            = parse_url($this->stagingURL, PHP_URL_HOST);
            $email             = $username . '@' . $domain;

            $userId = $this->createTempUserInStagingDB($username, $email, $role);
            if (is_wp_error($userId)) {
                return $userId;
            }

            return $this->generateLoginUrlForUser($cloneID, $userId, LoginAuthenticator::TEMP_AUTO_LOGIN_ACTION);
        } catch (\Exception $e) {
            if (isset($this->database)) {
                $this->database->query('ROLLBACK');
            }

            return new \WP_Error('temp_user_creation_failed', $e->getMessage());
        }
    }

    /**
     * @param string $url
     * @return bool
     */
    public function canUseMagicLogin(string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        $apiUrl   = trailingslashit($url) . '?rest_route=/' . Rest::WPSTG_ROUTE_NAMESPACE_V1 . '/check_magic_login';
        $response = wp_remote_get(
            $apiUrl,
            [
                'timeout'   => 15,
                'sslverify' => false,
            ]
        );

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }

        return json_decode(wp_remote_retrieve_body($response), true) === true;
    }

    /**
     * Generates a secure one-time login URL for the given user ID and action
     * @param string $cloneID
     * @param int $userId
     * @param string $action
     * @return string|\WP_Error
     */
    private function generateLoginUrlForUser(string $cloneID, int $userId, string $action)
    {
        if (empty($cloneID)) {
            return new \WP_Error('missing_parameters', 'Clone ID is required');
        }

        $this->cloneID = $cloneID;
        if (!$this->isValidClone()) {
            return new \WP_Error('invalid_clone', 'The specified clone does not exist or is broken');
        }

        if (!$this->canUseMagicLogin($this->stagingURL)) {
            return new \WP_Error('magic_login_unavailable', 'Magic login is not available on this site. Please update WP STAGING on the staging site to the latest version.');
        }

        $this->tokenExpiry = ($this->tokenExpiry ?? time()) + LoginAuthenticator::MAX_TOKEN_AGE; // Set token expiry to the maximum allowed age

        $token = $this->createSecureToken();
        if (is_wp_error($token)) {
            return $token;
        }

        $result = $this->storeTokenInStagingDB($userId, $token);
        if (is_wp_error($result)) {
            return $result;
        }

        $this->maybeStoreParentSite();
        // Clean existing login sessions for the user on staging site to avoid session conflicts
        $this->clearUserSessions($userId);

        return trailingslashit($this->stagingURL) . "?action=" . $action . "&token=" . urlencode($token);
    }

    /**
     * @param bool $appendExpiry Whether to append token expiry
     * @return string|\WP_Error The token or WP_Error on failure
     */
    private function createSecureToken(bool $appendExpiry = true)
    {
        try {
            $token = bin2hex(random_bytes(32));
            if ($appendExpiry) {
                $token .= '_' . $this->tokenExpiry;
            }

            return $token;
        } catch (\Exception $e) {
            return new \WP_Error('token_generation_exception', $e->getMessage());
        }
    }

    /**
     * @param int $userId
     * @param string $token
     * @return true|\WP_Error
     */
    private function storeTokenInStagingDB(int $userId, string $token)
    {
        try {
            $this->setupStagingDBConnection();
            if (!$this->checkIfUserExistsInStagingDb($userId)) {
                return new \WP_Error('user_not_found', 'User does not exist in staging database');
            }

            return $this->insertTokenMeta($userId, $token);
        } catch (\Exception $e) {
            if (isset($this->database)) {
                $this->database->query('ROLLBACK');
            }

            return new \WP_Error('token_store_exception', $e->getMessage());
        }
    }

    /**
     * @return bool
     */
    private function isValidClone(): bool
    {
        $existingClones = get_option($this->sites::STAGING_SITES_OPTION, []);
        if (empty($existingClones[$this->cloneID])) {
            return false;
        }

        $this->currentClone   = $existingClones[$this->cloneID];
        $this->stagingURL     = $this->currentClone['url'];
        $this->databasePrefix = $this->currentClone['prefix'];
        if (empty($this->currentClone['status']) || $this->currentClone['status'] !== 'finished' || empty($this->databasePrefix) || empty($this->stagingURL)) {
            return false;
        }

        return true;
    }

    /**
     * @return void
     */
    private function setupStagingDBConnection()
    {
        $sourceDatabase = WPStaging::make(SourceDatabase::class);
        $sourceDatabase->setOptions((object)$this->currentClone);
        $this->database = $sourceDatabase->getDatabase();
    }

    /**
     * @param int $userId
     * @param string $token
     * @return true|\WP_Error
     */
    private function insertTokenMeta(int $userId, string $token)
    {
        $hashed   = hash('sha256', $token);
        $expires  = $this->tokenExpiry + LoginAuthenticator::MAX_TOKEN_AGE;
        $userIp   = $this->getClientIP();
        $usermeta = $this->databasePrefix . 'usermeta';
        $metaKeys = array_values(LoginAuthenticator::AUTO_LOGIN_META_KEYS);

        // Delete existing meta
        $placeholders = implode(', ', array_fill(0, count($metaKeys), '%s'));
        $deleteQuery  = $this->database->prepare("DELETE FROM `{$usermeta}` WHERE `user_id` = %d AND `meta_key` IN ($placeholders)", array_merge([$userId], $metaKeys));
        $this->database->query($deleteQuery);

        // Start transaction
        $this->database->query('START TRANSACTION');

        $entries = [
            LoginAuthenticator::AUTO_LOGIN_META_KEYS['token']  => $hashed,
            LoginAuthenticator::AUTO_LOGIN_META_KEYS['expiry'] => $expires,
            LoginAuthenticator::AUTO_LOGIN_META_KEYS['ip']     => $userIp,
        ];

        foreach ($entries as $metaKey => $metaValue) {
            $query = $this->database->prepare("INSERT INTO `{$usermeta}` (`user_id`, `meta_key`, `meta_value`) VALUES (%d, %s, %s)", $userId, $metaKey, $metaValue);

            if ($this->database->query($query) === false) {
                $this->database->query('ROLLBACK');
                return new \WP_Error('db_error', 'Failed to store token in database');
            }
        }

        $this->database->query('COMMIT');
        return true;
    }

    /**
     * Store the parent site URL in staging database if not already stored.
     * @return true|\WP_Error
     */
    private function maybeStoreParentSite()
    {
        $optionsTable = $this->databasePrefix . 'options';
        $optionName   = self::OPTION_STAGING_PARENT_SITE;
        $siteUrl      = home_url();

        // Check if option already exists
        $checkQuery = $this->database->prepare(
            "SELECT `option_value` FROM `{$optionsTable}` WHERE `option_name` = %s LIMIT 1",
            $optionName
        );
        $existingValue = $this->database->get_var($checkQuery);

        if (!empty($existingValue)) {
            return true;
        }

        // Upsert option atomically
        $upsertQuery = $this->database->prepare(
            "INSERT INTO `{$optionsTable}` (`option_name`, `option_value`, `autoload`) 
         VALUES (%s, %s, 'no') 
         ON DUPLICATE KEY UPDATE 
         `option_value` = VALUES(`option_value`), 
         `autoload` = 'no'",
            $optionName,
            $siteUrl
        );

        if ($this->database->query($upsertQuery) === false) {
            return new \WP_Error('db_error', 'Failed to store parent site option in database');
        }

        return true;
    }

    /**
     * Check if a user exists in the staging database
     * @param int $userId User ID
     * @return bool True if user exists, false otherwise
     */
    private function checkIfUserExistsInStagingDb(int $userId): bool
    {
        if ($userId <= 0 || empty($this->databasePrefix)) {
            return false;
        }

        $usersTable = $this->databasePrefix . 'users';
        $query      = $this->database->prepare("SELECT COUNT(*) as total FROM `{$usersTable}` WHERE `ID` = %d", $userId);
        $result     = $this->database->get_results($query);
        if (empty($result)) {
            return false;
        }

        if ((int)$result[0]->total !== 0) {
            return true;
        }

        // Should create user on staging site if it does not exist!
        if (is_wp_error($this->accountSync->syncCurrentUser())) {
            return false;
        }

        return true;
    }

    /**
     * Create a temporary user in the staging database
     * @param string $username
     * @param string $email
     * @param string $role
     * @return int|\WP_Error User ID or error object
     */
    private function createTempUserInStagingDB(string $username, string $email, string $role)
    {
        $usersTable    = $this->databasePrefix . 'users';
        $usermetaTable = $this->databasePrefix . 'usermeta';
        $password      = wp_generate_password(16, true, true);
        // Start transaction
        $this->database->query('START TRANSACTION');
        try {
            $insertUser = $this->database->prepare(
                "INSERT INTO `{$usersTable}` (`user_login`, `user_pass`, `user_nicename`, `user_email`, `user_registered`, `display_name`) 
                VALUES (%s, %s, %s, %s, %s, %s)",
                $username,
                wp_hash_password($password),
                $username,
                $email,
                date('Y-m-d H:i:s'),
                'WP Staging Temp User'
            );

            $this->database->query($insertUser);
            $userId = $this->database->insert_id;

            if (empty($userId)) {
                $this->database->query('ROLLBACK');
                return new \WP_Error('user_creation_failed', 'Failed to create temporary user');
            }

            // Set user role
            $roleQuery = $this->database->prepare("INSERT INTO `{$usermetaTable}` (`user_id`, `meta_key`, `meta_value`) VALUES (%d, %s, %s)", $userId, $this->databasePrefix . 'capabilities', serialize([$role => true]));
            $this->database->query($roleQuery);

            // Set user level based on role
            $userLevel   = $this->getUserLevelFromRole($role);
            $insertQuery = $this->database->prepare("INSERT INTO `{$usermetaTable}` (`user_id`, `meta_key`, `meta_value`) VALUES (%d, %s, %d)", $userId, $this->databasePrefix . 'user_level', $userLevel);
            $this->database->query($insertQuery);
            $this->database->query('COMMIT');
            return $userId;
        } catch (\Exception $e) {
            $this->database->query('ROLLBACK');
            return new \WP_Error('user_creation_exception', $e->getMessage());
        }
    }

    /**
     * Clean existing temporary users from the staging database
     * @return void
     */
    private function cleanExistingTempUsers()
    {
        $usersTable    = $this->databasePrefix . 'users';
        $usermetaTable = $this->databasePrefix . 'usermeta';
        $escapedPrefix = $this->database->esc_like(LoginAuthenticator::LOGIN_LINK_PREFIX);
        $query         = $this->database->prepare("DELETE t1, t2 FROM {$usersTable} as t1 INNER JOIN {$usermetaTable} as t2 ON t1.ID = t2.user_id WHERE t1.user_login LIKE %s", $escapedPrefix . '%');
        $this->database->query($query);
    }

    /**
     * Get WordPress user level from role
     * @param string $role User role
     * @return int User level
     */
    private function getUserLevelFromRole(string $role): int
    {
        switch ($role) {
            case 'administrator':
            case LoginAuthenticator::WPSTG_SUPER_ADMIN_ROLE:
                return 10;
            case 'editor':
                return 7;
            case 'author':
                return 2;
            case 'contributor':
                return 1;
            default:
                return 0;
        }
    }

    /**
     * Retrieve the login token from stored login URL by user ID.
     * @param int $userId
     * @return string
     */
    private function getTokenByUserId(int $userId): string
    {
        $userData = get_option(TemporaryLogins::OPTION_CURRENT_SITE_LOGIN_LINKS, []);
        foreach ($userData as $loginData) {
            if ((int)$loginData['userId'] !== $userId) {
                continue;
            }

            $loginUrl = $loginData['loginUrl'] ?? '';
            if (empty($loginUrl)) {
                return '';
            }

            $parsedUrl = wp_parse_url($loginUrl);
            if (!isset($parsedUrl['query'])) {
                return '';
            }

            parse_str($parsedUrl['query'], $queryParams);
            return $queryParams['token'] ?? '';
        }

        return '';
    }

    /**
     * @param int $userId User ID
     * @return void|\WP_Error
     */
    private function clearUserSessions(int $userId)
    {
        if ($userId <= 0) {
            return;
        }

        $usermetaTable = $this->databasePrefix . 'usermeta';
        $this->database->query('START TRANSACTION');

        try {
            $deleteQuery = $this->database->prepare(
                "DELETE FROM `{$usermetaTable}` WHERE user_id = %d AND meta_key = %s",
                $userId,
                'session_tokens'
            );

            $this->database->query($deleteQuery);
            $this->database->query('COMMIT');
        } catch (\Throwable $e) {
            $this->database->query('ROLLBACK');
            return new \WP_Error('session_destroy_failed', 'Failed to remove session tokens: ' . $e->getMessage());
        }
    }
}
