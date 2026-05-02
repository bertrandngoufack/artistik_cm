<?php

namespace WPStaging\Pro\Staging\Tasks\StagingSiteReset;

use RuntimeException;
use WPStaging\Staging\Tasks\StagingSiteReset\ResetRequirementsCheckTask as BaseResetRequirementsCheckTask;

class ResetRequirementsCheckTask extends BaseResetRequirementsCheckTask
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
