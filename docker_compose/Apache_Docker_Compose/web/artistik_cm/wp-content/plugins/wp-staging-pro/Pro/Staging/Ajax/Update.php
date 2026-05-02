<?php

namespace WPStaging\Pro\Staging\Ajax;

use WPStaging\Core\WPStaging;
use WPStaging\Pro\Staging\Jobs\StagingSiteUpdate;
use WPStaging\Staging\Ajax\Update as BaseUpdate;

class Update extends BaseUpdate
{
    /**
     * @return StagingSiteUpdate
     */
    protected function getUpdateJob()
    {
        return WPStaging::make(StagingSiteUpdate::class);
    }
}
