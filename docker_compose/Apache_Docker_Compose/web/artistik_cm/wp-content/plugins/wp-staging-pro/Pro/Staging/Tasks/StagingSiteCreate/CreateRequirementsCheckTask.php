<?php

namespace WPStaging\Pro\Staging\Tasks\StagingSiteCreate;

use RuntimeException;
use WPStaging\Staging\Tasks\StagingSiteCreate\CreateRequirementsCheckTask as BaseCreateRequirementsCheckTask;

class CreateRequirementsCheckTask extends BaseCreateRequirementsCheckTask
{
    protected function cannotCreateStagingSiteOnMultisite()
    {
        // no-op
    }

    protected function cannotCreateIfUsingExternalDatabase()
    {
        // no-op
    }

    protected function cannotCreateIfStagingPrefixSameAsProductionSite()
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
