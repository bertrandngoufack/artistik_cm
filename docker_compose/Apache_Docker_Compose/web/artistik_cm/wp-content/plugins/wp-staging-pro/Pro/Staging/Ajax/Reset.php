<?php

namespace WPStaging\Pro\Staging\Ajax;

use WPStaging\Core\WPStaging;
use WPStaging\Pro\Staging\Jobs\StagingSiteReset;
use WPStaging\Staging\Ajax\Reset as BaseReset;

class Reset extends BaseReset
{
    /**
     * @return StagingSiteReset
     */
    protected function getResetJob()
    {
        return WPStaging::make(StagingSiteReset::class);
    }
}
