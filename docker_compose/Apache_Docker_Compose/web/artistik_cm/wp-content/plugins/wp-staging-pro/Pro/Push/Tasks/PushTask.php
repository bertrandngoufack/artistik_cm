<?php

namespace WPStaging\Pro\Push\Tasks;

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Framework\Job\Task\AbstractTask;
use WPStaging\Pro\Push\Dto\StagingSitePushDataDto;
use WPStaging\Staging\Sites;
use WPStaging\Staging\Dto\StagingSiteDto;

abstract class PushTask extends AbstractTask
{
    /** @var StagingSitePushDataDto */
    protected $jobDataDto;

    protected function getStagingSiteDto(string $cloneId): StagingSiteDto
    {
        /** @var Sites */
        $sites = WPStaging::make(Sites::class);
        return $sites->getStagingSiteDtoByCloneId($cloneId);
    }

    public static function getTaskName(): string
    {
        return 'push_task_name';
    }

    public static function getTaskTitle(): string
    {
        return 'Push Task';
    }

    public function execute(): TaskResponseDto
    {
        return $this->generateResponse(false);
    }
}
