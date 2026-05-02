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

class PluginsActivationTask extends AbstractTask
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
     * @var array
     */
    private $outdatedPlugins;

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
        return 'staging_plugins_activation';
    }

    /**
     * @return string
     */
    public static function getTaskTitle(): string
    {
        return 'Activating Updated Plugins';
    }

    /**
     * @return TaskResponseDto
     * @throws Exception
     */
    public function execute(): TaskResponseDto
    {
        if (count($this->jobDataDto->getOutdatedPlugins()) === 0) {
            return $this->generateResponse();
        }

        $stagingUrl = $this->preparePluginsReActivation();
        $this->outdatedPlugins = $this->jobDataDto->getOutdatedPlugins();
        while (!$this->isThreshold() && !$this->stepsDto->isFinished()) {
            $pluginData = $this->jobDataDto->getPluginToUpdate($this->stepsDto->getCurrent());
            if (!$this->isPluginActiveOnStagingSite($pluginData['file'])) {
                $this->logger->info(sprintf('Skipped activating plugin:  %s', $pluginData['file']));
                $this->stepsDto->incrementCurrentStep();
                continue;
            }

            $this->logger->info(sprintf('Activating plugin:  %s', $pluginData['file']));

            try {
                $accessToken = $this->jobDataDto->getAuthToken();
                $apiResponse = $this->wpPluginsUpdater->activatePlugin($stagingUrl, $pluginData['file'], $accessToken);
                $this->logger->info(sprintf('Api response:  %s', $apiResponse));
            } catch (\Throwable $ex) {
                $this->logger->info(sprintf("something went wrong while activating plugin %s . Error: %s", $pluginData['file'], $ex->getMessage()));
            }

            $this->stepsDto->incrementCurrentStep();
        }

        if ($this->taskQueue->isFinished()) {
            $this->stepsDto->finish();
        }

        return $this->generateResponse(false);
    }

    private function preparePluginsReActivation(): string
    {
        if ($this->stepsDto->getTotal() > 0) {
            return $this->jobDataDto->getStagingUrl();
        }

        $this->jobDataDto->setOutdatedPlugins(array_reverse($this->jobDataDto->getOutdatedPlugins()));
        $this->taskQueue->seek(0);
        $this->stepsDto->setTotal(count($this->jobDataDto->getOutdatedPlugins()));
        return $this->jobDataDto->getStagingUrl();
    }

    /**
     * @param string $pluginSlug
     * @return bool
     */
    private function isPluginActiveOnStagingSite(string $pluginSlug): bool
    {
        foreach ($this->outdatedPlugins as $plugin) {
            if ($plugin['file'] !== $pluginSlug) {
                continue;
            }

            return $plugin['is_active'];
        }

        return false;
    }
}
