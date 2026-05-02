<?php

namespace WPStaging\Pro;

use WPStaging\Framework\SiteInfo;
use WPStaging\Pro\License\Licensing;

final class WPStagingPro
{
    /**
     * @return bool
     */
    public static function isValidLicense(): bool
    {
        $siteInfo  = new SiteInfo();
        $licensing = new Licensing();
        return $siteInfo->isStagingSite() || $licensing->isValidOrExpiredLicenseKey();
    }
}
