<?php

namespace WPStaging\Pro\Staging\Tasks\StagingSiteUpdate;

use WPStaging\Framework\Facades\Hooks;
use WPStaging\Staging\Dto\StagingSiteDto;
use WPStaging\Staging\Jobs\StagingSiteUpdate;
use WPStaging\Staging\Tasks\StagingSiteUpdate\FinishStagingSiteUpdateTask as BaseFinishStagingSiteUpdateTask;

class FinishStagingSiteUpdateTask extends BaseFinishStagingSiteUpdateTask
{
    protected function triggerOnStagingSiteCreatedEvent(StagingSiteDto $stagingSite)
    {
        Hooks::doAction(StagingSiteUpdate::ACTION_CLONING_COMPLETE, $stagingSite->toArray());
    }
}
