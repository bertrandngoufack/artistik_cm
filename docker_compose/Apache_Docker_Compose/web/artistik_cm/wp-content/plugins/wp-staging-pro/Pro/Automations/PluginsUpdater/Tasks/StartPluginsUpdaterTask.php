<?php

namespace WPStaging\Pro\Automations\PluginsUpdater\Tasks;

use Exception;
use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Framework\Job\Task\AbstractTask;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Automations\PluginsUpdater\Dto\JobPluginsUpdaterDataDto;
use WPStaging\Pro\Automations\PluginsUpdater\WordPressPluginsUpdater;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

class StartPluginsUpdaterTask extends AbstractTask
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
        return 'staging_outdated_plugins';
    }

    /**
     * @return string
     */
    public static function getTaskTitle(): string
    {
        return 'Outdated Plugins';
    }

    /**
     * @return TaskResponseDto
     * @throws Exception
     */
    public function execute(): TaskResponseDto
    {
        $this->logger->info('Start Plugins Auto Update');
        $stagingUrl = $this->jobDataDto->getStagingUrl();
        if (empty($stagingUrl)) {
            return $this->generateResponse();
        }

        $this->logger->info(sprintf('Staging Url:  %s', $stagingUrl));
        $accessToken     = $this->jobDataDto->getAuthToken();
        $outdatedPlugins = $this->wpPluginsUpdater->getOutdatedPluginsFromStagingSite($stagingUrl, $accessToken);
        $this->jobDataDto->setOutdatedPlugins($outdatedPlugins);
        $this->logger->info(sprintf('Outdated plugins : %s', json_encode($outdatedPlugins)));
        return $this->generateResponse();
    }
}
