<?php

namespace WPStaging\Pro\Staging\AutoLogin;

use WPStaging\Framework\SiteInfo;
use WPStaging\Framework\Utils\DatabaseOptions;
use WPStaging\Staging\Sites;

require_once ABSPATH . 'wp-admin/includes/user.php'; // need for wp_delete_user();
require_once ABSPATH . 'wp-admin/includes/ms.php'; // need for wpmu_delete_user();

class LoginAccessRevoker
{
    /** @var bool */
    private $isStagingSite = false;

    /** @var DatabaseOptions */
    private $databaseOptions;

    /**
     * @param SiteInfo $siteInfo
     * @param DatabaseOptions $databaseOptions
     */
    public function __construct(SiteInfo $siteInfo, DatabaseOptions $databaseOptions)
    {
        $this->isStagingSite   = $siteInfo->isStagingSite();
        $this->databaseOptions = $databaseOptions;
    }

    /**
     * @return void
     */
    public function maybeRevokeLoginAccess()
    {
        if (!$this->isStagingSite || !is_user_logged_in()) {
            return;
        }

        $userId = get_current_user_id();
        if (!$userId) {
            return;
        }

        $user = get_user_by('ID', $userId);
        if (!$user) {
            return;
        }

        if (strpos($user->user_login, LoginAuthenticator::LOGIN_LINK_PREFIX) === 0) {
            $this->maybeRemoveTemporaryUser($user);
            return;
        }

        $expiry = get_user_meta($userId, LoginAuthenticator::AUTO_LOGIN_META_KEYS['expiry'], true);
        if (!empty($expiry) && time() >= intval($expiry)) {
            wp_logout();
            // delete the user meta to prevent further checks
            delete_user_meta($userId, LoginAuthenticator::AUTO_LOGIN_META_KEYS['expiry']);
            delete_user_meta($userId, LoginAuthenticator::AUTO_LOGIN_META_KEYS['token']);
            $errorMessage = esc_html__("Oops, your login link has expired. Use the form below to log in manually.", 'wp-staging');
            set_transient(LoginAuthenticator::TRANSIENT_AUTO_LOGIN_FAILED, true, LoginAuthenticator::MAX_TOKEN_AGE);
            set_transient(LoginAuthenticator::TRANSIENT_AUTO_LOGIN_FAILED_REASON, $errorMessage, LoginAuthenticator::MAX_TOKEN_AGE);
            wp_safe_redirect(site_url());
            exit;
        }
    }

    /**
     * Adds a custom error message to the login page if a temporary login error is present.
     * @param string $message Existing login message.
     * @return string Modified login message.
     */
    public function mayShowFailedLoginError(string $message): string
    {
        if (get_transient(LoginAuthenticator::TRANSIENT_AUTO_LOGIN_FAILED) === false) {
            return $message;
        }

        delete_transient(LoginAuthenticator::TRANSIENT_AUTO_LOGIN_FAILED);
        $error    = get_transient(LoginAuthenticator::TRANSIENT_AUTO_LOGIN_FAILED_REASON);
        $message .= sprintf('<div class="notice notice-error"><p>%s</p></div>', esc_html($error));
        return $message;
    }

    /**
     * @param \WP_User|null $user
     * @return void
     */
    private function maybeRemoveTemporaryUser($user = null)
    {
        if (empty($user)) {
            $user = wp_get_current_user();
            $user = $user->data;
        }

        $login = $user->user_login ?? '';
        if (strpos($login, LoginAuthenticator::LOGIN_LINK_PREFIX) !== 0) {
            return;
        }

        $loginID = substr($login, strlen(LoginAuthenticator::LOGIN_LINK_PREFIX));
        if (empty($loginID)) {
            return;
        }

        $loginData  = $this->databaseOptions->getOption(Sites::STAGING_LOGIN_LINK_SETTINGS, []);
        $userId     = $user->ID ?? 0;
        $metaExpiry = $userId ? get_user_meta($userId, LoginAuthenticator::AUTO_LOGIN_META_KEYS['expiry'], true) : null;

        $isLegacyExpired = is_array($loginData) && in_array($loginID, $loginData, true) && !empty($loginData['expiration']) && time() > $loginData['expiration'];
        $isMetaExpired   = (!empty($metaExpiry) && time() >= (int) $metaExpiry);
        if (!($isLegacyExpired || $isMetaExpired)) {
            return;
        }

        if ($userId) {
            is_multisite() ? wpmu_delete_user($userId) : wp_delete_user($userId);
        }

        wp_logout();
    }
}
