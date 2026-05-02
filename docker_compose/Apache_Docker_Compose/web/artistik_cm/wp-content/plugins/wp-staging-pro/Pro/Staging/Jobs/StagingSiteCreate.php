<?php

namespace WPStaging\Pro\Staging\Jobs;

use WPStaging\Pro\Staging\Tasks\StagingSite\NewAdministratorAccountTask;
use WPStaging\Pro\Staging\Tasks\StagingSite\SymlinkUploadsTask;
use WPStaging\Pro\Staging\Tasks\StagingSiteCreate\CreateRequirementsCheckTask;
use WPStaging\Pro\Staging\Tasks\StagingSiteCreate\FinishStagingSiteCreateTask;
use WPStaging\Pro\Staging\Traits\WithDataAdjustmentTasks;
use WPStaging\Staging\Jobs\StagingSiteCreate as StagingSiteCreateBase;

class StagingSiteCreate extends StagingSiteCreateBase
{
    use WithDataAdjustmentTasks;

    protected function addRequirementsCheckTask()
    {
        $this->tasks[] = CreateRequirementsCheckTask::class;
    }

    protected function addFinishStagingSiteCreateTask()
    {
        $this->tasks[] = FinishStagingSiteCreateTask::class;
    }

    protected function addAdvanceTasks()
    {
        $this->tasks[] = SymlinkUploadsTask::class;

        if ($this->jobDataDto->getAllTablesExcluded()) {
            return;
        }

        $this->tasks[] = NewAdministratorAccountTask::class;
    }
}
