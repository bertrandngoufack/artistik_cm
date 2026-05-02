<?php

namespace WPStaging\Pro\RemoteSync\Tasks;

use WPStaging\Core\WPStaging;
use WPStaging\Backup\Task\BackupTask;
use WPStaging\Framework\BackgroundProcessing\Queue;
use WPStaging\Framework\Job\JobTransientCache;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Pro\RemoteSync\Dto\Task\WaitTaskDto;
use WPStaging\Pro\RemoteSync\SyncSession;

/**
 * The purpose of this task is to wait enough until the initiator (pulling site) logs the site header and then triggers the pull process.
 * If somehow the initiator does not trigger the pull within 30 seconds, we proceed to the next task anyway.
 */
class PullSourceSiteWaitTask extends BackupTask
{
    const MAX_WAIT_TIME_SECONDS = 30; // 30 seconds

    /** @var bool */
    protected $isWaitTask = true;

    /** @var WaitTaskDto */
    protected $currentTaskDto;

    /**
     * @return string
     */
    public static function getTaskName()
    {
        return 'remote_sync_pull_source_site_wait';
    }

    /**
     * @return string
     */
    public static function getTaskTitle()
    {
        return 'Waiting for initiator to trigger the pull';
    }

    /**
     * @return TaskResponseDto
     */
    public function execute()
    {
        $this->stepsDto->setTotal(1);
        $this->stepsDto->setCurrent(0);

        if ($this->currentTaskDto->waitRequestStarted === 0) {
            $this->currentTaskDto->waitRequestStarted = time();
        }

        do {
            if ($this->isCancellationRequested()) {
                return $this->generateResponse(false);
            }

            sleep($this->currentTaskDto->delayBetweenRequestsSeconds);

            if ($this->isCancellationRequested()) {
                return $this->generateResponse(false);
            }

            if ($this->conditionToWaitFor()) {
                $this->stepsDto->finish();
                return $this->generateResponse();
            }
        } while (!$this->isWaitTaskThreshold());

        $this->setCurrentTaskDto($this->currentTaskDto);

        return $this->generateResponse();
    }

    /** @return string */
    protected function getCurrentTaskType(): string
    {
        return WaitTaskDto::class;
    }

    /**
     * Check if the progress status has changed to started i.e. done through the initiator.
     * After total of 30 seconds, proceed regardless to next task.
     * @return bool
     */
    private function conditionToWaitFor(): bool
    {
        $syncSession = new SyncSession();
        if (!$syncSession->isRunning()) {
            $this->cancelSourceSync();
            return false;
        }

        $progressStatus = $syncSession->getProgressStatus();
        if ($progressStatus === SyncSession::PROGRESS_STATUS_STARTED) {
            return true;
        }

        if (time() - $this->currentTaskDto->waitRequestStarted >= self::MAX_WAIT_TIME_SECONDS) {
            $syncSession->start();
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    private function isCancellationRequested(): bool
    {
        return $this->getJobTransientCache()->getJobStatus() === JobTransientCache::STATUS_CANCELLED;
    }

    /**
     * @return void
     */
    private function cancelSourceSync()
    {
        /** @var JobTransientCache $jobTransientCache */
        $jobTransientCache = $this->getJobTransientCache();
        $jobData           = $jobTransientCache->getJob();
        $queueId           = $jobData['queueId'] ?? '';

        if (!empty($queueId)) {
            /** @var Queue $queue */
            $queue = WPStaging::make(Queue::class);
            $queue->cancelJob($queueId);
        }

        if ($jobTransientCache->getJobStatus() !== JobTransientCache::STATUS_CANCELLED) {
            $jobTransientCache->cancelJob(esc_html__('Canceling Pull', 'wp-staging'));
        }
    }
}
