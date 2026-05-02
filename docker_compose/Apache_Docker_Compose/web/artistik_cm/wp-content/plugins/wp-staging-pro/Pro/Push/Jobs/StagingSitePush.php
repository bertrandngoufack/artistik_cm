<?php

namespace WPStaging\Pro\Push\Jobs;

use WPStaging\Framework\Job\AbstractJob;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Framework\Job\Task\Tasks\CleanupBakTablesTask;
use WPStaging\Pro\Push\Dto\StagingSitePushDataDto;
use WPStaging\Pro\Push\Tasks\Database\Adjustment\CleanupTemporaryLoginsTask;
use WPStaging\Pro\Push\Tasks\Database\Adjustment\PreserveOptionsTask;
use WPStaging\Pro\Push\Tasks\Database\Adjustment\PreserveSessionTokenInUsermetaTableTask;
use WPStaging\Pro\Push\Tasks\Database\Adjustment\RemoveOptionsTask;
use WPStaging\Pro\Push\Tasks\Database\Adjustment\UpdateActivePluginsTask;
use WPStaging\Pro\Push\Tasks\Database\Adjustment\UpdateNetworkDomainPathTask;
use WPStaging\Pro\Push\Tasks\Database\Adjustment\UpdatePrefixInOptionsTableTask;
use WPStaging\Pro\Push\Tasks\Database\Adjustment\UpdatePrefixInUsermetaTableTask;
use WPStaging\Pro\Push\Tasks\Database\CreateDatabaseTablesTask;
use WPStaging\Pro\Push\Tasks\Database\ImportDatabaseRowsTask;
use WPStaging\Pro\Push\Tasks\Database\PrepareDatabaseRowsTask;
use WPStaging\Pro\Push\Tasks\Database\RenameDatabaseTablesTask;
use WPStaging\Pro\Push\Tasks\Filesystem\CopyMuPluginsTask;
use WPStaging\Pro\Push\Tasks\Filesystem\CopyPluginsTask;
use WPStaging\Pro\Push\Tasks\Filesystem\CopyThemesTask;
use WPStaging\Pro\Push\Tasks\Filesystem\CopyUploadsTask;
use WPStaging\Pro\Push\Tasks\Filesystem\CleanupUploadsTask;
use WPStaging\Pro\Push\Tasks\Filesystem\CopyWpContentTask;
use WPStaging\Pro\Push\Tasks\Filesystem\FilesystemScannerTask;
use WPStaging\Pro\Push\Tasks\FinishStagingSitePushTask;
use WPStaging\Pro\Push\Tasks\PushRequirementsCheckTask;

class StagingSitePush extends AbstractJob
{
    /** @var string */
    const ACTION_PUSHING_COMPLETE = 'wpstg_pushing_complete';

    /** @var StagingSitePushDataDto */
    protected $jobDataDto;

    /** @var array The array of tasks to execute for this job. Populated at init(). */
    protected $tasks = [];

    /**
     * @return string
     */
    public static function getJobName()
    {
        return 'staging_site_push';
    }

    /**
     * @return array
     */
    protected function getJobTasks()
    {
        return $this->tasks;
    }

    /**
     * @return TaskResponseDto
     */
    protected function execute()
    {
        try {
            $response = $this->getResponse($this->currentTask->execute());
        } catch (\Exception $e) {
            $this->currentTask->getLogger()->critical($e->getMessage());
            $response = $this->getResponse($this->currentTask->generateResponse(false));
        }

        return $response;
    }

    /**
     * @return void
     */
    protected function init()
    {
        $this->tasks[] = PushRequirementsCheckTask::class;
        $this->addFilesystemTasks();
        $this->addDatabaseTasks();
        $this->tasks[] = FinishStagingSitePushTask::class;
    }

    /**
     * @return void
     */
    private function addDatabaseTasks()
    {
        // Early return if all tables are excluded
        if ($this->jobDataDto->getAllTablesExcluded() && empty($this->jobDataDto->getNonSiteTables())) {
            return;
        }

        $this->tasks[] = CleanupBakTablesTask::class;
        $this->tasks[] = CreateDatabaseTablesTask::class;
        $this->tasks[] = PrepareDatabaseRowsTask::class;
        $this->tasks[] = ImportDatabaseRowsTask::class;

        $this->addAdjustmentTasks();

        $this->tasks[] = RenameDatabaseTablesTask::class;
    }

    /**
     * @return void
     */
    private function addFilesystemTasks()
    {
        $this->tasks[] = FilesystemScannerTask::class;
        $this->tasks[] = CleanupUploadsTask::class;
        $this->tasks[] = CopyUploadsTask::class;
        $this->tasks[] = CopyPluginsTask::class;
        $this->tasks[] = CopyMuPluginsTask::class;
        $this->tasks[] = CopyThemesTask::class;
        $this->tasks[] = CopyWpContentTask::class;
    }

    /**
     * @return void
     */
    private function addAdjustmentTasks()
    {
        $this->tasks[] = UpdatePrefixInOptionsTableTask::class;
        $this->tasks[] = UpdatePrefixInUsermetaTableTask::class;
        $this->tasks[] = UpdateActivePluginsTask::class;
        $this->tasks[] = PreserveSessionTokenInUsermetaTableTask::class;
        $this->tasks[] = PreserveOptionsTask::class;
        $this->tasks[] = RemoveOptionsTask::class;
        $this->tasks[] = CleanupTemporaryLoginsTask::class;
        $this->tasks[] = UpdateNetworkDomainPathTask::class;
    }
}
