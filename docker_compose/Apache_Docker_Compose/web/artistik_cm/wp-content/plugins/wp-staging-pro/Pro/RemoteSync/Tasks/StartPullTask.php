<?php

namespace WPStaging\Pro\RemoteSync\Tasks;

use RuntimeException;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Framework\Job\Task\AbstractTask;
use WPStaging\Framework\Traits\RestRequestTrait;
use WPStaging\Pro\RemoteSync\Dto\Job\PullInitiatorDataDto;
use WPStaging\Pro\RemoteSync\SyncSession;

class StartPullTask extends AbstractTask
{
    use RestRequestTrait;

    /** @var PullInitiatorDataDto */
    protected $jobDataDto;

    /**
     * @return string
     */
    public static function getTaskName()
    {
        return 'pull_start';
    }

    /**
     * @return string
     */
    public static function getTaskTitle()
    {
        return 'Starting Pull';
    }

    /**
     * @return TaskResponseDto
     */
    public function execute()
    {
        if (!$this->stepsDto->getTotal()) {
            $this->stepsDto->setTotal(1);
        }

        try {
            $this->logger->info('↳ Initializing pulling data');
            $this->logger->writeLogHeader($this->jobDataDto->getIsSyncRequest() ? ' - Initiator Site: ' . home_url() : '');
            $this->logger->writeInstalledPluginsAndThemes();
            $this->logSyncDetails();

            $syncSession   = new SyncSession();
            $this->headers = $this->getAuthorizationHeader($syncSession->getToken());
            $this->setHttpAuth($this->jobDataDto->getHttpAuthUsername(), $this->jobDataDto->getHttpAuthPassword());
            $this->sendRestRequest($syncSession->getRemoteUrl(), 'prepare_pull');
        } catch (RuntimeException $e) {
            $this->logger->critical($e->getMessage());

            $this->jobDataDto->setRequirementFailReason($e->getMessage());

            return $this->generateResponse(false);
        }

        return $this->generateResponse();
    }

    private function logSyncDetails()
    {
        $remoteUrl = $this->jobDataDto->getRemoteUrl();
        $this->logger->info(sprintf('Source Site: %s', esc_html($remoteUrl)));
        $this->logger->info(sprintf('Destination Site: %s', esc_html(home_url())));
        $this->logger->info(sprintf('Sync Mode: %s', $this->jobDataDto->getIsTwoWaySync() ? 'Two-Way' : 'One-Way'));

        $selectedItems = [];
        if ($this->jobDataDto->getIsSyncDatabase()) {
            $selectedItems[] = 'Database';
        }

        if ($this->jobDataDto->getIsSyncPlugins()) {
            $selectedItems[] = 'Plugins';
        }

        if ($this->jobDataDto->getIsSyncThemes()) {
            $selectedItems[] = 'Themes';
        }

        if ($this->jobDataDto->getIsSyncUploads()) {
            $selectedItems[] = 'Uploads';
        }

        if ($this->jobDataDto->getIsSyncMuPlugins()) {
            $selectedItems[] = 'MU-Plugins';
        }

        if ($this->jobDataDto->getIsSyncOtherContent()) {
            $selectedItems[] = 'Other Content';
        }

        $this->logger->info(sprintf('Selected Items: %s', implode(', ', $selectedItems)));
    }
}
