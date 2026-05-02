<?php

namespace WPStaging\Pro\Job\Ajax;

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Job\AbstractJob;
use WPStaging\Framework\Job\Ajax\Status as BaseStatus;
use WPStaging\Pro\Backup\Job\Jobs\JobRemoteUpload;
use WPStaging\Pro\Push\Jobs\StagingSitePush;

class Status extends BaseStatus
{
    /**
     * @return AbstractJob
     */
    protected function getPushJob(): AbstractJob
    {
        return WPStaging::make(StagingSitePush::class);
    }

    /**
     * @return AbstractJob
     */
    protected function getRemoteUploadJob(): AbstractJob
    {
        return WPStaging::make(JobRemoteUpload::class);
    }
}
