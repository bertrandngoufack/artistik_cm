<?php

namespace WPStaging\Pro\Backup\Dto\Task\Extract;

use WPStaging\Framework\Job\Dto\AbstractTaskDto;

class ExtractSelectedFilesTaskDto extends AbstractTaskDto
{
    /** @var int */
    public $currentOffsetIndex = 0;

    /** @var int */
    public $currentFileWrittenBytes = 0;

    /** @var int */
    public $currentFileReadBytes = 0;

    /** @var int */
    public $currentHeaderBytesRemoved = 0;

    /** @var int */
    public $skipped = 0;
}
