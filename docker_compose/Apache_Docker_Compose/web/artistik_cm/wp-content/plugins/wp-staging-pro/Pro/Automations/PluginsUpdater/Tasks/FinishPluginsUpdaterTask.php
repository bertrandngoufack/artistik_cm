<?php

namespace WPStaging\Pro\Automations\PluginsUpdater\Tasks;

use Exception;
use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Job\Task\AbstractTask;
use WPStaging\Pro\Automations\PluginsUpdater\Dto\JobPluginsUpdaterDataDto;
use WPStaging\Pro\Automations\PluginsUpdater\WordPressPluginsUpdater;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

class FinishPluginsUpdaterTask extends AbstractTask
{
    /**
     * @var JobPluginsUpdaterDataDto
     */
    protected $jobDataDto;

    /**
     * @var WordPressPluginsUpdater
     */
    protected $wpPluginsUpdater;

    /**
     * @param WordPressPluginsUpdater $wpPluginsUpdater
     * @param LoggerInterface $logger
     * @param Cache $cache
     * @param StepsDto $stepsDto
     * @param SeekableQueueInterface $taskQueue
     */
    public function __construct(WordPressPluginsUpdater $wpPluginsUpdater, LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
        $this->wpPluginsUpdater = $wpPluginsUpdater;
    }

    /**
     * @return string
     */
    public static function getTaskName(): string
    {
        return 'finish_plugins_auto_update';
    }

    /**
     * @return string
     */
    public static function getTaskTitle(): string
    {
        return 'Finish Plugins Auto Update';
    }

    /**
     * @return TaskResponseDto
     * @throws Exception
     */
    public function execute(): TaskResponseDto
    {
        $this->wpPluginsUpdater->sendNotification($this->jobDataDto->getApiResponse(), $this->jobDataDto->getStagingUrl(), $this->jobDataDto->getOutdatedPlugins());
        $this->wpPluginsUpdater->deleteAuthToken($this->jobDataDto->getCloneId());
        $this->stepsDto->finish();
        $this->getJobTransientCache()->completeJob();
        $this->jobDataDto->setEndTime(time());
        return $this->generateResponse();
    }
}
