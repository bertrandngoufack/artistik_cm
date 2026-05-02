<?php

namespace WPStaging\Pro\RemoteSync\Dto\Task;

use WPStaging\Framework\Job\Dto\AbstractTaskDto;

class WaitTaskDto extends AbstractTaskDto
{
    /** @var int */
    public $retried = 0;

    /** @var int */
    public $delayBetweenRequestsSeconds = 1;

    /** @var int */
    public $waitRequestStarted = 0;
}
