<?php

namespace WPStaging\Pro\RemoteSync\Tasks;

use WPStaging\Core\WPStaging;
use WPStaging\Framework\BackgroundProcessing\Queue;
use WPStaging\Framework\Job\JobTransientCache;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Framework\Job\Task\AbstractTask;
use WPStaging\Framework\Traits\RestRequestTrait;
use WPStaging\Pro\RemoteSync\Dto\Task\WaitTaskDto;
use WPStaging\Pro\RemoteSync\SyncSession;
use WPStaging\Pro\RemoteSync\Dto\Job\PullInitiatorDataDto;
use WPStaging\Pro\RemoteSync\WithStartPullMethods;

/**
 * The purpose of this task is to wait for the remote site to prepare data before proceeding.
 */
class PullInitiatorWaitTask extends AbstractTask
{
    use RestRequestTrait;
    use WithStartPullMethods;

    /**
     * Maximum number of retries before giving up while waiting for the remote site to prepare data.
     * @var int
     */
    const MAX_RETRIES_ON_MAX_DELAY = 3;

    /** @var PullInitiatorDataDto */
    protected $jobDataDto;

    /** @var bool */
    protected $isWaitTask = true;

    /** @var WaitTaskDto */
    protected $currentTaskDto;

    /**
     * @return string
     */
    public static function getTaskName()
    {
        return 'remote_sync_pull_initiator_wait';
    }

    /**
     * @return string
     */
    public static function getTaskTitle()
    {
        return 'Waiting for remote site to prepare data';
    }

    /**
     * @return TaskResponseDto
     */
    public function execute()
    {
        $this->stepsDto->setTotal(1);
        $this->stepsDto->setCurrent(0);

        do {
            if ($this->isCancellationRequested()) {
                $this->logger->info('Stopping wait task because cancellation was requested.');
                return $this->generateResponse(false);
            }

            sleep($this->currentTaskDto->delayBetweenRequestsSeconds);

            if ($this->isCancellationRequested()) {
                $this->logger->info('Stopping wait task because cancellation was requested.');
                return $this->generateResponse(false);
            }

            if ($this->conditionToWaitFor()) {
                $this->stepsDto->finish();
                return $this->generateResponse();
            }

            if ($this->isIncrementDelay() || $this->isStopProcessing()) {
                break;
            }
        } while (!$this->isWaitTaskThreshold());

        $this->setCurrentTaskDto($this->currentTaskDto);
        if ($this->isStopProcessing()) {
            $this->logger->error('Maximum retries reached while waiting for remote site to prepare data.');
            $this->stepsDto->finish();
            return $this->generateResponse(false);
        }

        if ($this->isIncrementDelay()) {
            $this->currentTaskDto->delayBetweenRequestsSeconds++;
            $this->currentTaskDto->retried = 0;
            $this->setCurrentTaskDto($this->currentTaskDto);
            $this->logger->warning(sprintf('Increasing wait time between requests to %d seconds.', $this->currentTaskDto->delayBetweenRequestsSeconds));
        }

        return $this->generateResponse(false);
    }

    /** @return string */
    protected function getCurrentTaskType(): string
    {
        return WaitTaskDto::class;
    }

    private function conditionToWaitFor(): bool
    {
        $jobTransientCache = $this->getJobTransientCache();
        $jobType           = $jobTransientCache->getJob()['type'] ?? '';
        $syncSession       = new SyncSession();
        $remoteSiteUrl     = $jobType === JobTransientCache::JOB_TYPE_PULL_PREPARE ? $syncSession->getPullSiteUrl() : $syncSession->getPushSiteUrl();
        $this->headers     = $this->getAuthorizationHeader($syncSession->getToken());
        $this->setHttpAuth($this->jobDataDto->getHttpAuthUsername(), $this->jobDataDto->getHttpAuthPassword());

        if (!$syncSession->isRunning() || empty($remoteSiteUrl)) {
            $this->cancelInitiatorSync('Remote sync session is no longer available while waiting for remote preparation.');
            return false;
        }

        $response = $this->sendRestRequest($remoteSiteUrl, 'sync_status', [
            'triggerStart' => true,
        ]);

        if (is_wp_error($response)) {
            $this->currentTaskDto->retried++;
            $errorCode    = $response->get_error_code();
            $errorMessage = $response->get_error_message();
            $logMessage   = sprintf('Waiting for remote site preparation. Request failed: [%s] %s (Current Delay: %d sec) URL: %s Response: %s', $errorCode, $errorMessage, $this->currentTaskDto->delayBetweenRequestsSeconds, $remoteSiteUrl, wp_json_encode($response));
            $this->logger->warning($logMessage);

            return false;
        }

        $response = json_decode(wp_remote_retrieve_body($response), true);
        if (
            isset($response['code'], $response['data']['status']) &&
            $response['code'] === 'rest_forbidden' &&
            (int)$response['data']['status'] === 401
        ) {
            $this->cancelInitiatorSync('Remote site rejected sync status requests (401). Marking initiator pull as cancelled.');
            return false;
        }

        if (isset($response['success']) && $response['success']) {
            $this->currentTaskDto->retried = 0;
            $jobTransientCache->update();
            return $this->processResponse($response['data'] ?? []);
        }

        $this->logger->debug('We were able to connect to the remote site but it returned an unsuccessful response while waiting for remote sync preparation. Response: ' . wp_json_encode($response));
        $this->currentTaskDto->retried++;

        return false;
    }

    private function isStopProcessing(): bool
    {
        return $this->currentTaskDto->retried >= self::MAX_RETRIES_ON_MAX_DELAY && $this->currentTaskDto->delayBetweenRequestsSeconds >= self::MAX_WAIT_TASK_THRESHOLD_SECONDS;
    }

    /**
     * We increment the delay even on one failure
     * @return bool
     */
    private function isIncrementDelay(): bool
    {
        return $this->currentTaskDto->retried >= 1 && $this->currentTaskDto->delayBetweenRequestsSeconds < self::MAX_WAIT_TASK_THRESHOLD_SECONDS;
    }

    /**
     * @return bool
     */
    private function isCancellationRequested(): bool
    {
        return $this->getJobTransientCache()->getJobStatus() === JobTransientCache::STATUS_CANCELLED;
    }

    /**
     * @param string $reason
     * @return void
     */
    private function cancelInitiatorSync(string $reason)
    {
        /** @var JobTransientCache $jobTransientCache */
        $jobTransientCache = $this->getJobTransientCache();
        $jobData           = $jobTransientCache->getJob();
        $queueId           = $jobData['queueId'] ?? $this->jobDataDto->getId();

        if (!empty($queueId)) {
            /** @var Queue $queue */
            $queue = WPStaging::make(Queue::class);
            $queue->cancelJob($queueId);
        }

        if ($jobTransientCache->getJobStatus() !== JobTransientCache::STATUS_CANCELLED) {
            $jobTransientCache->cancelJob(esc_html__('Canceling Pull', 'wp-staging'));
        }

        $this->logger->warning($reason);
    }

    /**
     * @param array $data
     * @return bool
     */
    private function processResponse(array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $status = $data['status'] ?? '';
        $data   = $data['data'] ?? [];
        if ($status === SyncSession::PROGRESS_STATUS_PREPARED) {
            $this->pushEvents($this->jobDataDto->getId(), $data['events'] ?? []);
            $this->jobDataDto->setDataUrl($data['dataUrl'] ?? '');
            $this->updateJob();
            return true;
        }

        $this->pushEvents($this->jobDataDto->getId(), $data['events'] ?? []);
        return false;
    }
}
