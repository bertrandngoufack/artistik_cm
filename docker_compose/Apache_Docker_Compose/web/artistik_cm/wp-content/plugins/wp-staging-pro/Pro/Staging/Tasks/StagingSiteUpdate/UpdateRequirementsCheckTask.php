<?php

namespace WPStaging\Pro\Staging\Tasks\StagingSiteUpdate;

use RuntimeException;
use WPStaging\Staging\Tasks\StagingSiteUpdate\UpdateRequirementsCheckTask as BaseUpdateRequirementsCheckTask;

class UpdateRequirementsCheckTask extends BaseUpdateRequirementsCheckTask
{
    protected function cannotUpdateStagingSiteOnMultisite()
    {
        // no-op
    }

    protected function cannotUpdateIfUsingExternalDatabase()
    {
        // no-op
    }

    protected function cannotUpdateIfStagingPrefixSameAsProductionSite()
    {
        if ($this->jobDataDto->getIsExternalDatabase()) {
            return; // Skip check for external databases
        }

        $isSamePrefix = $this->database->getBasePrefix() === $this->jobDataDto->getDatabasePrefix();
        if ($isSamePrefix) {
            throw new RuntimeException(esc_html__('Staging site prefix is same as production site prefix. Use different prefix for staging site.', 'wp-staging'));
        }
    }
}
