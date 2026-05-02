<?php

namespace WPStaging\Pro\Staging\Tasks\StagingSiteReset;

use WPStaging\Framework\Facades\Hooks;
use WPStaging\Staging\Dto\StagingSiteDto;
use WPStaging\Staging\Jobs\StagingSiteReset;
use WPStaging\Staging\Tasks\StagingSiteReset\FinishStagingSiteResetTask as BaseFinishStagingSiteResetTask;

class FinishStagingSiteResetTask extends BaseFinishStagingSiteResetTask
{
    protected function triggerOnStagingSiteCreatedEvent(StagingSiteDto $stagingSite)
    {
        Hooks::doAction(StagingSiteReset::ACTION_CLONING_COMPLETE, $stagingSite->toArray());
    }
}
