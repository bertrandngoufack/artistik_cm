<?php

namespace WPStaging\Pro\Frontend;

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Facades\Sanitize;
use WPStaging\Framework\Security\AccessToken;
use WPStaging\Framework\SiteInfo;

class LoginAfterPull
{
    /**
     * @return void
     * @see \WPStaging\Pro\Frontend\FrontendServiceProvider::registerLoginAfterPull
     */
    public function showMessage()
    {
        // Early bail: Not after Restore
        if (!isset($_GET['wpstgAfterPull']) || !Sanitize::sanitizeBool($_GET['wpstgAfterPull'])) {
            return;
        }

        // Early bail: No access token
        if (!isset($_GET['accessToken'])) {
            return;
        }

        // Late instantiation, since this runs on the FE on every request
        /** @var AccessToken $auth */
        $auth = WPStaging::make(AccessToken::class);

        // Early bail: Invalid access token
        if (!$auth->isValidToken($_GET['accessToken'])) {
            return;
        }

        $isPullFromWpCom          = $this->getIsPullFromWpCom();
        $resetPasswordArticleLink = 'https://wp-staging.com/reset-your-wordpress-admin-password-manually/';

        include WPSTG_VIEWS_DIR . 'pro/frontend/loginAfterPull.php';
    }

    /**
     * @return bool
     */
    protected function getIsPullFromWpCom(): bool
    {
        /** @var SiteInfo */
        $siteInfo = WPStaging::make(SiteInfo::class);
        // Should not be shown when pull data into wp.com from another wp.com site
        if ($siteInfo->isHostedOnWordPressCom()) {
            return false;
        }

        if (isset($_GET['wpstgIsPullFromWpCom']) && Sanitize::sanitizeBool($_GET['wpstgIsPullFromWpCom'])) {
            return true;
        }

        return false;
    }
}
