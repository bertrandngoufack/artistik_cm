<?php

namespace WPStaging\Pro\Staging\Jobs;

use WPStaging\Pro\Staging\Tasks\StagingSite\Filesystem\CopyPluginsTask;
use WPStaging\Pro\Staging\Tasks\StagingSite\Filesystem\CopyThemesTask;
use WPStaging\Pro\Staging\Tasks\StagingSite\Filesystem\CopyUploadsTask;
use WPStaging\Pro\Staging\Tasks\StagingSiteUpdate\FinishStagingSiteUpdateTask;
use WPStaging\Pro\Staging\Tasks\StagingSiteUpdate\UpdateRequirementsCheckTask;
use WPStaging\Pro\Staging\Traits\WithDataAdjustmentTasks;
use WPStaging\Staging\Jobs\StagingSiteUpdate as StagingSiteUpdateBase;
use WPStaging\Staging\Tasks\StagingSite\Filesystem\CopyMuPluginsTask;
use WPStaging\Staging\Tasks\StagingSite\Filesystem\CopyWpAdminTask;
use WPStaging\Staging\Tasks\StagingSite\Filesystem\CopyWpContentTask;
use WPStaging\Staging\Tasks\StagingSite\Filesystem\CopyWpIncludesTask;
use WPStaging\Staging\Tasks\StagingSite\Filesystem\CopyWpRootDirectoriesTask;
use WPStaging\Staging\Tasks\StagingSite\Filesystem\CopyWpRootFilesTask;
use WPStaging\Staging\Tasks\StagingSite\Filesystem\FilesystemScannerTask;

class StagingSiteUpdate extends StagingSiteUpdateBase
{
    use WithDataAdjustmentTasks;

    protected function addRequirementsCheckTask()
    {
        $this->tasks[] = UpdateRequirementsCheckTask::class;
    }

    protected function addFinishStagingSiteUpdateTask()
    {
        $this->tasks[] = FinishStagingSiteUpdateTask::class;
    }

    /**
     * Override to use Pro versions of Copy*Tasks that include cleanup functionality
     */
    protected function addFilesystemTasks()
    {
        $this->tasks[] = FilesystemScannerTask::class;
        $this->tasks[] = CopyWpRootFilesTask::class;
        $this->tasks[] = CopyWpAdminTask::class;
        $this->tasks[] = CopyWpIncludesTask::class;
        $this->tasks[] = CopyPluginsTask::class;
        $this->tasks[] = CopyMuPluginsTask::class;
        $this->tasks[] = CopyThemesTask::class;
        $this->tasks[] = CopyUploadsTask::class;
        $this->tasks[] = CopyWpContentTask::class;
        $this->tasks[] = CopyWpRootDirectoriesTask::class;
    }
}
