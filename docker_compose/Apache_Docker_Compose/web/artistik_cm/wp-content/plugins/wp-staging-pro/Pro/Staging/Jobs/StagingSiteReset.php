<?php

namespace WPStaging\Pro\Staging\Jobs;

use WPStaging\Pro\Staging\Tasks\StagingSiteReset\FinishStagingSiteResetTask;
use WPStaging\Pro\Staging\Tasks\StagingSiteReset\ResetRequirementsCheckTask;
use WPStaging\Pro\Staging\Traits\WithDataAdjustmentTasks;
use WPStaging\Staging\Jobs\StagingSiteReset as StagingSiteResetBase;

class StagingSiteReset extends StagingSiteResetBase
{
    use WithDataAdjustmentTasks;

    protected function addRequirementsCheckTask()
    {
        $this->tasks[] = ResetRequirementsCheckTask::class;
    }

    protected function addFinishStagingSiteResetTask()
    {
        $this->tasks[] = FinishStagingSiteResetTask::class;
    }
}
