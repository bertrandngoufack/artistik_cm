<?php

namespace WPStaging\Pro\Backup\Dto\Task\Restore;

use WPStaging\Framework\Job\Dto\AbstractTaskDto;

class FinalizeMultipartDatabaseTaskDto extends AbstractTaskDto
{
    /** @var int */
    public $currentPartBytesWritten;

    /** @var int */
    public $currentPartTotalBytes;
}
