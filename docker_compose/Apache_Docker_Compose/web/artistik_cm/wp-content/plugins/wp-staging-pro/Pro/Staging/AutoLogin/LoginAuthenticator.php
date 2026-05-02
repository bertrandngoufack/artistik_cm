<?php

namespace WPStaging\Pro\Staging\AutoLogin;

use WPStaging\Framework\Rest\Rest;
use WPStaging\Framework\Security\Capabilities;
use WPStaging\Framework\SiteInfo;
use WPStaging\Framework\Traits\IpResolverTrait;
use WPStaging\Framework\Utils\DatabaseOptions;
use WPStaging\Pro\Auth\TemporaryLogins;
use WPStaging\Staging\Sites;

use function WPStaging\functions\debug_log;

require_once ABSPATH . 'wp-admin/includes/user.php'; // need for wp_delete_user();
require_once ABSPATH . 'wp-admin/includes/ms.php'; // need for wpmu_delete_user();

/**
 * Class LoginAuthenticator
 * Handles automatic login using one-time tokens and legacy login links
 */
class LoginAuthenticator
{
    use IpResolverTrait;

    /** @var array */
    const AUTO_LOGIN_META_KEYS = [
        'token'  => 'wpstg_pwdless_token',
        'expiry' => 'wpstg_pwdless_token_expiry',
        'ip'     => 'wpstg_pwdless_token_ip',
    ];

    /** @var int Token expiration time in seconds */
    const MAX_TOKEN_AGE = 30;

    /** @var int */
    const LOGIN_THROTTLE_LIMIT = 3; // Max failed attempts

    /** @var string */
    const TRANSIENT_FAILED_LOGIN_ATTEMPTS = 'wpstg_failed_auto_login_attempts';

    /** @var string */
    const TRANSIENT_AUTO_LOGIN_FAILED = 'wpstg_auto_login_failed';

    /** @var string */
    const TRANSIENT_AUTO_LOGIN_FAILED_REASON = 'wpstg_auto_login_failed_reason';

    /** @var string */
    const AUTO_LOGIN_ACTION = 'wpstg_auto_login'; // for this action we will check for ip, referer

    /** @var string */
    const TEMP_AUTO_LOGIN_ACTION = 'wpstg_temp_auto_login'; // no need to check for ip, referer

    /** @var string */
    const LOGIN_LINK_PREFIX = 'wpstg_login_link_';

    /** @var string */
    const WPSTG_SUPER_ADMIN_ROLE = 'wpstg_super_admin';

    /** @var string */
    const FILTER_LOGIN_REDIRECT = 'login_redirect';

    /** @var bool */
    protected $isStagingSite = false;

    /** @var bool */
    private $processTempLoginLink = false;

    /** @var bool */
    private $cleanTemporaryLogins = false;

    /** @var SiteInfo */
    private $siteInfo;

    /** @var DatabaseOptions */
    private $databaseOptions;

    /** @var array */
    private $loginLinkData = [];

    /** @var array */
    private $currentSiteLoginLinkData = [];

    /** @var string */
    private $clientIp = '';

    /**
     * @param SiteInfo $siteInfo
     * @param DatabaseOptions $databaseOptions
     */
    public function __construct(SiteInfo $siteInfo, DatabaseOptions $databaseOptions)
    {
        $this->siteInfo                 = $siteInfo;
        $this->databaseOptions          = $databaseOptions;
        $this->isStagingSite            = $this->siteInfo->isStagingSite();
        $this->clientIp                 = $this->getClientIP();
    }

    /**
     * Register routes for endpoints.
     * @return void
     */
    public function registerRestRoutes()
    {
        register_rest_route(Rest::WPSTG_ROUTE_NAMESPACE_V1, '/check_magic_login', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [$this, 'canUseMagicLogicEndpoint'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * @return \WP_REST_Response|\WP_Error
     */
    public function canUseMagicLogicEndpoint()
    {
        return rest_ensure_response(true);
    }

    /**
     * Main entry point for all login authentication methods(legacy and new token-based)
     * @return void
     * @throws \Exception
     */
    public function processAuthentication()
    {
        $this->loginUserByLegacyToken();
        $this->loginUserByAccessToken();
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function loginUserByLegacyToken()
    {
        if ($this->isStagingSite && !empty($_GET[TemporaryLogins::LOGIN_PREFIX])) {
            $this->loginUserByLink();
            return;
        }

        // Handle temporary login links
        if (!empty($_GET[TemporaryLogins::LOGIN_PREFIX]) || !empty($_GET[TemporaryLogins::STAGING_LOGIN_PREFIX])) {
            $this->currentSiteLoginLinkData = $this->databaseOptions->getOption(TemporaryLogins::OPTION_CURRENT_SITE_LOGIN_LINKS, []);
            $this->loginUserByTemporaryLink();
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function loginUserByLink()
    {
        if (!isset($_GET[TemporaryLogins::LOGIN_PREFIX])) {
            return;
        }

        $this->cleanTemporaryLogins = true;
        $loginID             = sanitize_text_field($_GET[TemporaryLogins::LOGIN_PREFIX]);
        $login               = self::LOGIN_LINK_PREFIX . $loginID;
        $user                = get_user_by('login', $login);
        $userId              = is_object($user) ? $user->ID : null;
        $this->loginLinkData = $this->databaseOptions->getOption(Sites::STAGING_LOGIN_LINK_SETTINGS, []);

        $this->validateLoginLink($loginID, $userId);
        if (empty($userId)) {
            $userId = $this->createTemporaryUser($login);
        }

        $this->authenticateAndRedirect($userId);
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function loginUserByTemporaryLink()
    {
        $loginID = $this->extractLoginID();
        if (empty($loginID) || empty($this->currentSiteLoginLinkData)) {
            $this->handleSecurityFailure('Oops! Something went wrong with the login link. Please log in manually.', $this->clientIp);
        }

        $this->loginLinkData = $this->findLoginDataByID($loginID);
        if (empty($this->loginLinkData['name'])) {
            $this->handleSecurityFailure('The login link is invalid or has expired. Please log in manually.', $this->clientIp);
        }

        $user   = get_user_by('login', $this->loginLinkData['name']);
        $userId = is_object($user) ? $user->ID : null;
        $this->validateLoginLink($loginID, $userId);
        $this->authenticateAndRedirect($userId);
    }

    /**
     * @return string
     */
    private function extractLoginID(): string
    {
        if ($this->isStagingSite && !empty($_GET[TemporaryLogins::STAGING_LOGIN_PREFIX])) {
            return sanitize_text_field($_GET[TemporaryLogins::STAGING_LOGIN_PREFIX]);
        }

        if (!$this->isStagingSite && !empty($_GET[TemporaryLogins::LOGIN_PREFIX])) {
            return sanitize_text_field($_GET[TemporaryLogins::LOGIN_PREFIX]);
        }

        return '';
    }

    /**
     * @param string $loginID
     * @return array
     */
    private function findLoginDataByID(string $loginID): array
    {
        foreach ($this->currentSiteLoginLinkData as $loginData) {
            if ($loginData['loginID'] !== $loginID) {
                continue;
            }

            return $loginData;
        }

        return [];
    }

    /**
     * @param string $login
     * @return int
     * @throws \Exception
     */
    private function createTemporaryUser(string $login): int
    {
        $role = $this->loginLinkData['role'];
        if ($role === self::WPSTG_SUPER_ADMIN_ROLE) {
            $role = 'administrator';
        }

        $userId = wp_insert_user([
            'user_login' => $login,
            'user_pass'  => uniqid('wpstg'),
            'role'       => $role,
        ]);

        if (is_wp_error($userId)) {
            $this->handleSecurityFailure('Oops!, Something went wrong with the link. Please use the form below to sign in manually.', $this->clientIp);
        }

        return $userId;
    }

    /**
     * @return void
     */
    private function loginUserByAccessToken()
    {
        if (!$this->shouldProcessAutoLogin()) {
            return;
        }

        try {
            $token           = !empty($_GET['token']) ? sanitize_text_field($_GET['token']) : '';
            $tokenValidation = $this->validateTokenRequest($token);
            if (is_wp_error($tokenValidation)) {
                $this->handleSecurityFailure($tokenValidation->get_error_message(), $this->clientIp);
            }

            $referrerValidation = $this->validateReferrer();
            if (is_wp_error($referrerValidation)) {
                $this->handleSecurityFailure($referrerValidation->get_error_message(), $this->clientIp);
            }

            $tokenHash = hash('sha256', $token);
            $user      = $this->findUserByToken($tokenHash, $this->clientIp);
            if (is_wp_error($user)) {
                $this->handleSecurityFailure($user->get_error_message(), $this->clientIp);
            }

            $this->currentSiteLoginLinkData = $this->databaseOptions->getOption(TemporaryLogins::OPTION_CURRENT_SITE_LOGIN_LINKS, []);
            $this->cleanupUserMeta($user->ID);
            $this->performAuthentication($user->ID);
            $this->handlePostAuthenticationRedirect($user);
        } catch (\Exception $e) {
            $this->handleSecurityFailure('An error occurred during login. Please use the form below to login manually.', $this->clientIp);
        }
    }

    /**
     * @param int $userId
     * @return void
     * @throws \Exception
     */
    private function authenticateAndRedirect(int $userId)
    {
        if (!is_int($userId)) {
            wp_die('Something went wrong, please contact the administrator.');
        }

        if (is_multisite() && ($this->loginLinkData['role'] === self::WPSTG_SUPER_ADMIN_ROLE)) {
            grant_super_admin($userId);
        }

        $this->performAuthentication($userId);
        if (!$this->cleanTemporaryLogins) {
            $this->updateTemporaryLoginCounter($userId);
        }

        if ($this->loginLinkData['role'] === Capabilities::WPSTG_VISITOR_ROLE) {
            wp_redirect(home_url());
            exit;
        }

        wp_redirect(admin_url());
        exit;
    }

    /**
     * @param int $userId
     * @return void
     */
    private function performAuthentication(int $userId)
    {
        wp_clear_auth_cookie();
        wp_set_auth_cookie($userId, true);
        wp_set_current_user($userId);
        $this->updateTemporaryLoginCounter($userId);

        $user = get_user_by('ID', $userId);
        if ($user) {
            if ((is_multisite() && is_super_admin($user->ID)) || (!empty($user->allcaps[self::WPSTG_SUPER_ADMIN_ROLE]))) {
                grant_super_admin($user->ID);
            }

            do_action('wp_login', $user->user_login, $user);
        }
    }

    /**
     * @param \WP_User $user
     * @return never
     */
    private function handlePostAuthenticationRedirect(\WP_User $user)
    {
        if (!empty($_GET['action']) && $_GET['action'] === self::AUTO_LOGIN_ACTION) {
            // delete the user meta to prevent further checks if the same user tries to log in again using username and password
            $this->processTempLoginLink = false;
            $this->cleanupUserMeta($user->ID);
        }

        if (!empty($user->allcaps[Capabilities::WPSTG_VISITOR_ROLE])) {
            wp_safe_redirect(home_url());
            exit;
        }

        $adminUrl    = admin_url();
        $redirectUrl = apply_filters(self::FILTER_LOGIN_REDIRECT, $adminUrl, $adminUrl, $user);
        wp_safe_redirect($redirectUrl);
        exit;
    }

    /**
     * @param string $loginID
     * @param int|null $userId
     * @return void
     */
    private function validateLoginLink(string $loginID, $userId)
    {
        if (empty($this->loginLinkData) || !in_array($loginID, $this->loginLinkData, true) || empty($this->loginLinkData['expiration'])) {
            $this->handleLoginError($userId, 'We\'re missing some information to log you in automatically. Use the form below to log in manually.');
        }

        if (time() > $this->loginLinkData['expiration']) {
            $this->handleLoginError($userId, 'Oops, your login link has expired. Use the form below to log in manually.');
        }
    }

    /**
     * @param $userId
     * @param string $errorMessage
     * @return void
     */
    private function handleLoginError($userId, string $errorMessage)
    {
        $this->cleanExistingLoginData($userId);
        $this->handleSecurityFailure($errorMessage, $this->clientIp);
    }

    /**
     * @param int $userId
     * @return void
     */
    private function updateTemporaryLoginCounter(int $userId)
    {
        if (empty($this->currentSiteLoginLinkData)) {
            return;
        }

        foreach ($this->currentSiteLoginLinkData as $key => $loginData) {
            if ($loginData['userId'] !== $userId) {
                continue;
            }

            $this->currentSiteLoginLinkData[$key]['attempts']  = empty($loginData['attempts']) ? 1 : (int)$loginData['attempts'] + 1;
            $this->currentSiteLoginLinkData[$key]['lastLogin'] = time();
        }

        $this->databaseOptions->updateOption(TemporaryLogins::OPTION_CURRENT_SITE_LOGIN_LINKS, $this->currentSiteLoginLinkData);
    }

    /**
     * @param int $userID
     * @return void
     */
    private function deleteUser(int $userID)
    {
        if (is_multisite()) {
            wpmu_delete_user($userID);
        } else {
            wp_delete_user($userID);
        }
    }

    /**
     * @param int|null $userId
     * @return void
     */
    private function cleanExistingLoginData($userId)
    {
        if (!$this->cleanTemporaryLogins || !is_int($userId)) {
            return;
        }

        $this->deleteUser($userId);
        $this->databaseOptions->deleteOption(Sites::STAGING_LOGIN_LINK_SETTINGS);
    }

    /**
     * @return bool True if auto login should be processed
     */
    private function shouldProcessAutoLogin(): bool
    {
        if (is_user_logged_in()) {
            return false;
        }

        if (empty($_GET['action']) || ($_GET['action'] !== self::AUTO_LOGIN_ACTION && $_GET['action'] !== self::TEMP_AUTO_LOGIN_ACTION)) {
            return false;
        }

        if (empty($_GET['token'])) {
            return false;
        }

        if ($this->isAutoLoginThrottled()) {
            $this->handleSecurityFailure('Automatic login is temporarily turned off. Use the form below to log in manually.', $this->clientIp);
        }

        if ($_GET['action'] === self::TEMP_AUTO_LOGIN_ACTION) {
            $this->processTempLoginLink = true;
        }

        return true;
    }

    /**
     * @param string $ip current user Ip address
     * @return bool True if throttled
     */
    private function isAutoLoginThrottled(string $ip = ""): bool
    {
        if (empty($ip)) {
            $ip = $this->getClientIP();
        }

        $failedAttempts = (int)get_transient(self::TRANSIENT_FAILED_LOGIN_ATTEMPTS . md5($ip));
        return $failedAttempts >= self::LOGIN_THROTTLE_LIMIT;
    }

    /**
     * @param string $ip current user Ip address
     * @return void
     */
    private function incrementFailedLoginAttempts(string $ip)
    {
        $transientKey   = self::TRANSIENT_FAILED_LOGIN_ATTEMPTS . md5($ip);
        $failedAttempts = (int)get_transient($transientKey);
        set_transient($transientKey, $failedAttempts + 1, 5 * MINUTE_IN_SECONDS);
    }

    /**
     * @param string $token The login token
     * @return \WP_Error|true WP_Error on failure, true on success
     */
    private function validateTokenRequest(string $token)
    {
        if (strpos($token, '_') !== false) {
            return $this->validateTimestampToken($token);
        }

        return $this->validateHexToken($token);
    }

    /**
     * @return \WP_Error|true WP_Error on failure, true on success
     */
    private function validateReferrer()
    {
        if ($this->processTempLoginLink) {
            return true; // bypass http referer verification for shareable login link
        }

        $parentSite = $this->databaseOptions->getOption(LoginLinkGenerator::OPTION_STAGING_PARENT_SITE);
        if (empty($parentSite)) {
            return $this->refererError();
        }

        $referer = empty($_SERVER['HTTP_REFERER']) ? '' : filter_var($_SERVER['HTTP_REFERER'], FILTER_SANITIZE_URL);
        if (empty($referer)) {
            return $this->refererError('missing_referer');
        }

        $refererHost = parse_url($referer, PHP_URL_HOST);
        $parentHost  = parse_url($parentSite, PHP_URL_HOST);
        if ($refererHost !== $parentHost) {
            return $this->refererError('host_mismatch');
        }

        if (stripos($referer, $parentSite) !== 0) {
            return $this->refererError('referer_mismatch');
        }

        if (stripos(parse_url($referer, PHP_URL_PATH), 'wpstg_clone') !== false) {
            return $this->refererError('clone_referer_blocked');
        }

        return true;
    }

    /**
     * @param string $tokenHash Hashed token value
     * @param string $ip User's IP address
     * @return \WP_Error|\WP_User WP_Error on failure, WP_User on success
    */
    private function findUserByToken(string $tokenHash, string $ip)
    {
        $metaQuery = [
            'relation' => 'AND',
            [
                'key'     => self::AUTO_LOGIN_META_KEYS['token'],
                'value'   => $tokenHash,
                'compare' => '=',
            ],
            [
                'key'     => self::AUTO_LOGIN_META_KEYS['expiry'],
                'value'   => time(),
                'compare' => '>',
                'type'    => 'NUMERIC',
            ]
        ];

        if (!$this->processTempLoginLink) {
            $metaQuery[] = [
                'key'     => self::AUTO_LOGIN_META_KEYS['ip'],
                'value'   => $ip,
                'compare' => '=',
            ];
        }

        $userQuery = new \WP_User_Query([
            'meta_query' => $metaQuery,
            'fields'     => 'all',
            'number'     => 1,
        ]);

        $users = $userQuery->get_results();

        if (empty($users)) {
            $checkExpiryQuery = new \WP_User_Query([
                'meta_query' => [
                    [
                        'key'     => self::AUTO_LOGIN_META_KEYS['token'],
                        'value'   => $tokenHash,
                        'compare' => '=',
                    ],
                ],
                'fields'     => 'ID',
            ]);
            $matchedUsers = $checkExpiryQuery->get_results();

            if (!empty($matchedUsers)) {
                return new \WP_Error('token_expired', 'Oops, your login link has expired. Use the form below to log in manually.');
            }

            return new \WP_Error('user_not_found', 'We couldn\'t find an account for the auto login link. Use the form below to log in manually.');
        }

        return $users[0];
    }

    /**
     * @param int $userID User ID
     * @return void
     */
    private function cleanupUserMeta(int $userID)
    {
        if ($this->processTempLoginLink) {
            return;
        }

        foreach (self::AUTO_LOGIN_META_KEYS as $key) {
            delete_user_meta($userID, $key);
        }
    }

    /**
     * @param string $errorMessage Error message
     * @param string $ip current user ip address
     * @return never
     */
    private function handleSecurityFailure(string $errorMessage, string $ip = '')
    {
        $this->incrementFailedLoginAttempts($ip);
        if (defined('WPSTG_DEBUG') && WPSTG_DEBUG) {
            debug_log(sprintf("WPStaging auto login failed: %s | IP: %s", $errorMessage, $ip));
        }

        set_transient(self::TRANSIENT_AUTO_LOGIN_FAILED, true, self::MAX_TOKEN_AGE);
        set_transient(self::TRANSIENT_AUTO_LOGIN_FAILED_REASON, $errorMessage, self::MAX_TOKEN_AGE);

        $redirectUrl = $this->isStagingSite ? site_url() : wp_login_url();
        wp_safe_redirect($redirectUrl);
        exit;
    }

    /**
     * @param string $code
     * @return \WP_Error
     */
    private function refererError(string $code = 'missing_parent_url'): \WP_Error
    {
        return new \WP_Error($code, sprintf('We\'re missing some information to log you in automatically. Use the form below to log in manually. Error code: %s', $code));
    }

    /**
     * @param string $token
     * @return \WP_Error|true
     */
    private function validateTimestampToken(string $token)
    {
        if (!preg_match('/^[a-f0-9]+_\d+$/', $token)) {
            return new \WP_Error('invalid_token_format', 'The login link appears to be malformed or tampered with. Please generate a new one.');
        }

        $tokenParts = explode('_', $token);
        if (count($tokenParts) !== 2) {
            return new \WP_Error('invalid_token_structure', 'The login link is not structured correctly. It might have been modified or corrupted.');
        }

        $timestamp = $tokenParts[1];
        if (!ctype_digit($timestamp)) {
            return new \WP_Error('invalid_token_timestamp', 'The login token timestamp is invalid.');
        }

        if ((time() - (int)$timestamp) > self::MAX_TOKEN_AGE) {
            return new \WP_Error('token_expired', 'Oops, your login link has expired. Use the form below to log in manually.');
        }

        return true;
    }

    /**
     * @param string $token
     * @return \WP_Error|true
     */
    private function validateHexToken(string $token)
    {
        if (!preg_match('/^[a-f0-9]+$/', $token)) {
            return new \WP_Error('invalid_token_format', 'The login token format is invalid.');
        }

        return true;
    }
}
