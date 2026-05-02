<?php

namespace WPStaging\Pro\Staging\Ajax;

use WPStaging\Core\WPStaging;
use WPStaging\Pro\Staging\Jobs\StagingSiteCreate;
use WPStaging\Staging\Ajax\Create as BaseCreate;

class Create extends BaseCreate
{
    /**
     * @return StagingSiteCreate
     */
    protected function getCreateJob()
    {
        return WPStaging::make(StagingSiteCreate::class);
    }
}
