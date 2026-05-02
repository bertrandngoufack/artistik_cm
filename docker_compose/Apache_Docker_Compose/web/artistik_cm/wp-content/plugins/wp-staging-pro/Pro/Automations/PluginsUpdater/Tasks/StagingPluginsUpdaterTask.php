<?php

namespace WPStaging\Pro\Automations\PluginsUpdater\Tasks;

use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Framework\Job\Task\AbstractTask;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Automations\PluginsUpdater\Dto\JobPluginsUpdaterDataDto;
use WPStaging\Pro\Automations\PluginsUpdater\WordPressPluginsUpdater;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

class StagingPluginsUpdaterTask extends AbstractTask
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
        return 'staging_plugins_updating';
    }

    /**
     * @return string
     */
    public static function getTaskTitle(): string
    {
        return 'Updating Plugins';
    }

    /**
     * @return TaskResponseDto
     */
    public function execute(): TaskResponseDto
    {
        if (count($this->jobDataDto->getOutdatedPlugins()) === 0) {
            return $this->generateResponse();
        }

        $stagingUrl  = $this->preparePluginsUpdaterTask();
        $accessToken = $this->jobDataDto->getAuthToken();
        if (empty($stagingUrl) || empty($accessToken)) {
            return $this->generateResponse();
        }

        $response   = [];
        if ($this->stepsDto->getTotal() > 0) {
            $response = $this->jobDataDto->getApiResponse();
        }

        while (!$this->isThreshold() && !$this->stepsDto->isFinished()) {
            $pluginData = $this->jobDataDto->getPluginToUpdate($this->stepsDto->getCurrent());
            $pluginFile = $pluginData['file'];

            if (array_key_exists($pluginFile, $response)) {
                $this->stepsDto->incrementCurrentStep();
                continue;
            }

            $this->logger->info('Checking available updates');
            $checkAvailableUpdates = $this->wpPluginsUpdater->checkAvailableUpdates($stagingUrl, true, $accessToken);
            $this->logger->info(sprintf('Available updates response:  %s', $checkAvailableUpdates));

            $isNetworkClone = $this->jobDataDto->getIsNetworkClone();
            $this->logger->info(sprintf('Updating Plugin %s', $pluginFile));

            try {
                $apiResponse = $this->wpPluginsUpdater->sendUpdateRequest($stagingUrl, $pluginFile, $pluginData['new_version'], $pluginData['current_version'], $isNetworkClone, $accessToken);
                $this->logger->info(sprintf('Api response: %s', json_encode($apiResponse)));
            } catch (\Throwable $ex) {
                $this->logger->info(sprintf("Something went wrong while updating plugin %s. Error: %s", $pluginFile, $ex->getMessage()));
            }

            $networkSites = 0;
            $retries      = 0;
            if ($isNetworkClone) {
                $networkSites = $this->getNetworkSites();
                $retries      = $this->jobDataDto->getNetworkUpdateRetries();
            }

            if ($isNetworkClone && empty($apiResponse['network_upto_date']) && $retries <= $networkSites) {
                $this->jobDataDto->setNetworkUpdateRetries(++$retries);
                continue;
            }

            // need to retry one time before proceeding to next step
            if (isset($apiResponse['new_version']) && isset($apiResponse['old_version']) && version_compare($apiResponse['new_version'], $apiResponse['old_version'], '=') && !$this->jobDataDto->getPluginRetry() && empty($apiResponse['network_upto_date'])) {
                $this->jobDataDto->setPluginRetry(true);
                $this->logger->info(sprintf('Retrying Updating Plugin %s', $pluginFile));
                continue;
            }

            $response[$pluginFile] = $apiResponse;
            $this->jobDataDto->setPluginRetry();
            $this->jobDataDto->setNetworkUpdateRetries(0);
            $this->stepsDto->incrementCurrentStep();
        }

        $this->jobDataDto->setApiResponse($response);
        if ($this->taskQueue->isFinished()) {
            $this->stepsDto->finish();
        }

        return $this->generateResponse(false);
    }

    /**
     * @return string
     */
    private function preparePluginsUpdaterTask(): string
    {
        $stagingUrl = $this->jobDataDto->getStagingUrl();
        if ($this->stepsDto->getTotal() > 0) {
            return $stagingUrl;
        }

        $this->taskQueue->seek(0);
        $this->stepsDto->setTotal(count($this->jobDataDto->getOutdatedPlugins()));
        return $stagingUrl;
    }

    /**
     * @return int
     */
    private function getNetworkSites(): int
    {
        $sites = get_sites();
        return count($sites);
    }
}
