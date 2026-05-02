<?php

namespace WPStaging\Pro\Backup\Dto\Task\Extract;

use WPStaging\Framework\Job\Dto\AbstractTaskDto;

/**
 * Stores progress for resolving directory offsets during extraction.
 */
class ResolveDirectoryOffsetsTaskDto extends AbstractTaskDto
{
    /** @var int */
    public $currentIndexOffset = 0;
}
