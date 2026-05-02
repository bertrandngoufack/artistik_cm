<?php

namespace WPStaging\Pro\Backup\Task;

use WPStaging\Pro\Backup\Dto\Job\JobExtractDataDto;
use WPStaging\Framework\Job\Dto\JobDataDto;
use WPStaging\Framework\Job\Task\AbstractTask;

/**
 * Base task for the backup extraction job (selected files only).
 */
abstract class ExtractTask extends AbstractTask
{
    /** @var JobExtractDataDto */
    protected $jobDataDto;

    public function setJobDataDto(JobDataDto $jobDataDto)
    {
        /** @var JobExtractDataDto $jobDataDto */
        parent::setJobDataDto($jobDataDto);
    }
}
