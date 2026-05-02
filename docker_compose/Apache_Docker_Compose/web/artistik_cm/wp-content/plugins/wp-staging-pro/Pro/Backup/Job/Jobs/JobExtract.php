<?php

namespace WPStaging\Pro\Backup\Job\Jobs;

use WPStaging\Pro\Backup\Dto\Job\JobExtractDataDto;
use WPStaging\Pro\Backup\Task\Tasks\JobExtract\ExtractRequirementsCheckTask;
use WPStaging\Pro\Backup\Task\Tasks\JobExtract\ExtractSelectedFilesTask;
use WPStaging\Pro\Backup\Task\Tasks\JobExtract\ResolveDirectoryOffsetsTask;
use WPStaging\Pro\Backup\Task\Tasks\JobExtract\FinishExtractTask;
use WPStaging\Framework\Job\AbstractJob;
use WPStaging\Framework\Job\Dto\TaskResponseDto;

/**
 * Job pipeline for extracting user-selected files from a backup.
 */
class JobExtract extends AbstractJob
{
    /** @var JobExtractDataDto */
    protected $jobDataDto;

    /** @var array<class-string> */
    protected $tasks = [];

    public static function getJobName(): string
    {
        return 'backup_extract';
    }

    /**
     * @return array
     */
    protected function getJobTasks(): array
    {
        return $this->tasks;
    }

    /**
     * @return TaskResponseDto
     */
    protected function execute(): TaskResponseDto
    {
        try {
            return $this->getResponse($this->currentTask->execute());
        } catch (\Exception $e) {
            $this->currentTask->getLogger()->critical($e->getMessage());
            return $this->getResponse($this->currentTask->generateResponse(false));
        }
    }

    /**
     * @return void
     */
    protected function init()
    {
        $this->tasks = [
            ExtractRequirementsCheckTask::class,
            ResolveDirectoryOffsetsTask::class,
            ExtractSelectedFilesTask::class,
            FinishExtractTask::class,
        ];
    }
}
