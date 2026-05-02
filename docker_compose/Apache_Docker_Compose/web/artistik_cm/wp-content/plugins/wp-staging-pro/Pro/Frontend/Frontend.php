<?php

namespace WPStaging\Pro\Frontend;

use WPStaging\Framework\Security\Capabilities;
use WPStaging\Frontend\Frontend as BaseFrontend;
use WPStaging\Pro\Auth\TemporaryLogins;
use WPStaging\Pro\Staging\AutoLogin\LoginAuthenticator;
use WPStaging\Pro\WPStagingPro;

/**
 * Extended Frontend class for PRO version
 * @package WPStaging\Pro\Frontend
 */
class Frontend extends BaseFrontend
{
    /**
     * @return bool
     */
    protected function showLoginForm(): bool
    {
        if ($this->isLoggedInUsingTempLoginLink()) {
            return false;
        }

        if (!parent::showLoginForm()) {
            return false;
        }

        // Allow access for wp staging user role "all"
        if (!empty($this->settings->userRoles) && in_array('all', $this->settings->userRoles)) {
            return false;
        }

        if (!is_user_logged_in()) {
            return true;
        }

        $currentUser = wp_get_current_user();

        if ($currentUser->has_cap(Capabilities::WPSTG_VISITOR_ROLE)) {
            return false;
        }

        // Allow access for administrators if no user roles are defined
        if (!isset($this->settings->userRoles) || !is_array($this->settings->userRoles)) {
            $this->accessDenied = true;
            return true;
        }

        if (!empty($this->settings->usersWithStagingAccess)) {
            $usersWithStagingAccess = explode(',', $this->settings->usersWithStagingAccess);

            // check against usernames
            if (in_array($currentUser->user_login, $usersWithStagingAccess, true)) {
                return false;
            }

            // check against emails
            if (in_array($currentUser->user_email, $usersWithStagingAccess, true)) {
                return false;
            }
        }

        // Require login form if user is not in specific user role
        $activeUserRoles = $currentUser->roles;
        $result          = isset($this->settings->userRoles) && is_array($this->settings->userRoles) ? array_intersect($activeUserRoles, $this->settings->userRoles) : [];

        if (empty($result) && !$this->isLoginPage() && !is_admin()) {
            $this->accessDenied = true;
            return true;
        }

        return false;
    }

    /**
     * @return void
     */
    protected function resetPermaLinks()
    {
        if (!$this->isStagingSite() || get_option("wpstg_rmpermalinks_executed") === "true") {
            return;
        }

        // Keep permalinks if setting is enabled
        if (isset($this->settings->keepPermalinks) && $this->settings->keepPermalinks === "1") {
            return;
        }

        parent::resetPermaLinks();
    }

    /**
     * Check if user is logged in using a temporary login link
     * @return bool
     */
    private function isLoggedInUsingTempLoginLink(): bool
    {
        if (!is_user_logged_in() || !$this->isStagingSite() || !WPStagingPro::isValidLicense()) {
            return false;
        }

        $currentUser = wp_get_current_user();

        return $this->isTemporaryLoginLink($currentUser->ID) || $this->isStagingSiteLoginLink($currentUser->data);
    }

    /**
     * Check temporary login link
     * @param int $userId
     * @return bool
     */
    private function isTemporaryLoginLink($userId): bool
    {
        $tempLoginLinks = get_option(TemporaryLogins::OPTION_CURRENT_SITE_LOGIN_LINKS, []);
        $loggedInUserData = array_filter($tempLoginLinks, function ($loginLinkData) use ($userId) {
            return $loginLinkData['userId'] === $userId;
        });

        return !empty($loggedInUserData);
    }

    /**
     * Check staging site login link
     * @param $userData
     * @return bool
     */
    private function isStagingSiteLoginLink($userData): bool
    {
        $prefix  = LoginAuthenticator::LOGIN_LINK_PREFIX;
        $loginID = strpos($userData->user_login, $prefix) === 0 ? substr($userData->user_login, strlen($prefix)) : false;

        if (empty($loginID)) {
            return false;
        }

        return true;
    }
}
