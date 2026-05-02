<?php

namespace WPStaging\Pro\Staging\Tasks\StagingSiteCreate;

use WPStaging\Framework\Facades\Hooks;
use WPStaging\Staging\Dto\StagingSiteDto;
use WPStaging\Staging\Jobs\StagingSiteCreate;
use WPStaging\Staging\Tasks\StagingSiteCreate\FinishStagingSiteCreateTask as BaseFinishStagingSiteCreateTask;

class FinishStagingSiteCreateTask extends BaseFinishStagingSiteCreateTask
{
    protected function triggerOnStagingSiteCreatedEvent(StagingSiteDto $stagingSite)
    {
        Hooks::doAction(StagingSiteCreate::ACTION_CLONING_COMPLETE, $stagingSite->toArray());
    }
}
