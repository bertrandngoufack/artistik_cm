<?php

namespace WPStaging\Pro\Staging\Traits;

use WPStaging\Pro\Staging\Tasks\StagingSite\DatabaseAdjustment\AdjustSubsiteStagingActivePluginsTask;
use WPStaging\Pro\Staging\Tasks\StagingSite\DatabaseAdjustment\AdjustSubsiteStagingAdministratorsTask;
use WPStaging\Pro\Staging\Tasks\StagingSite\DatabaseAdjustment\UpdateDomainAndPathTask;
use WPStaging\Pro\Staging\Tasks\StagingSite\DatabaseAdjustment\UpdateOptionsInOptionsTableTask;
use WPStaging\Pro\Staging\Tasks\StagingSite\DatabaseAdjustment\UpdatePrefixInOptionsTableTask;
use WPStaging\Pro\Staging\Tasks\StagingSite\DatabaseAdjustment\UpdateSiteUrlAndHomeTask;
use WPStaging\Staging\Tasks\StagingSite\DatabaseAdjustment\UpdatePrefixInUserMetaTableTask;
use WPStaging\Staging\Tasks\StagingSite\FileAdjustment\AdjustThirdPartyFilesTask;
use WPStaging\Staging\Tasks\StagingSite\FileAdjustment\UpdateWpConfigConstantsTask;
use WPStaging\Staging\Tasks\StagingSite\FileAdjustment\UpdateWpConfigTask;
use WPStaging\Staging\Tasks\StagingSite\FileAdjustment\VerifyIndexTask;
use WPStaging\Staging\Tasks\StagingSite\FileAdjustment\VerifyWpConfigTask;

trait WithDataAdjustmentTasks
{
    public function addDataAdjustmentTasks()
    {
        $this->tasks[] = VerifyWpConfigTask::class;
        if (!$this->jobDataDto->getAllTablesExcluded()) {
            $this->tasks[] = UpdateSiteUrlAndHomeTask::class;
            $this->tasks[] = UpdateOptionsInOptionsTableTask::class;
            $this->tasks[] = UpdatePrefixInUserMetaTableTask::class;
        }

        $this->tasks[] = UpdateWpConfigTask::class;
        $this->tasks[] = VerifyIndexTask::class;
        if (!$this->jobDataDto->getAllTablesExcluded()) {
            $this->tasks[] = UpdatePrefixInOptionsTableTask::class;
        }

        $this->tasks[] = UpdateWpConfigConstantsTask::class;
        $this->tasks[] = AdjustThirdPartyFilesTask::class;

        if (!is_multisite() || $this->jobDataDto->getAllTablesExcluded()) {
            return;
        }

        if ($this->jobDataDto->getIsStagingNetwork()) {
            $this->tasks[] = UpdateDomainAndPathTask::class;
            return;
        }

        // Add subsite adjustment tasks when cloning from a subsite (sourceBlogId > 1)
        if ($this->jobDataDto->getSourceBlogId() > 1) {
            $this->tasks[] = AdjustSubsiteStagingAdministratorsTask::class;
            $this->tasks[] = AdjustSubsiteStagingActivePluginsTask::class;
        }
    }
}
