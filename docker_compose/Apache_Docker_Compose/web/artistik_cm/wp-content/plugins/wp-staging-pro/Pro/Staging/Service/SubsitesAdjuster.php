<?php

namespace WPStaging\Pro\Staging\Service;

use WPStaging\Pro\Multisite\Service\AbstractAdjustSubsitesMeta;

/**
 * Class responsible for adjusting subsites meta data
 * Source site is current site
 * Destination site is staging site
 */
class SubsitesAdjuster extends AbstractAdjustSubsitesMeta
{
    protected function getFilterToUse(): string
    {
        return 'wpstg.staging.multisite.subsites';
    }
}
