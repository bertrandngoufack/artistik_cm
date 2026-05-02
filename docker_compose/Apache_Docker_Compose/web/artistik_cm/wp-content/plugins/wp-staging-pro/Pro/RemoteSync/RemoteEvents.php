<?php

namespace WPStaging\Pro\RemoteSync;

use WPStaging\Backup\Dto\Job\JobBackupDataDto;
use WPStaging\Backup\Exceptions\BackupRuntimeException;
use WPStaging\Backup\Service\BackupsFinder;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Job\Dto\JobDataDto;
use WPStaging\Framework\Job\JobTransientCache;
use WPStaging\Framework\Queue\FinishedQueueException;
use WPStaging\Framework\Traits\RestRequestTrait;
use WPStaging\Framework\Utils\Urls;

class RemoteEvents
{
    use RestRequestTrait;

    /**
     * @param JobDataDto $jobDataDto
     * @param JobTransientCache $jobTransientCache
     * @param bool $isWaitTask
     * @return void
     */
    public function maybeSendEventsOnTaskResponse(JobDataDto $jobDataDto, JobTransientCache $jobTransientCache, bool $isWaitTask)
    {
        if ($isWaitTask) {
            return;
        }

        $jobType = $jobTransientCache->getJob()['type'] ?? '';
        if (!in_array($jobType, [JobTransientCache::JOB_TYPE_PULL_PREPARE, JobTransientCache::JOB_TYPE_PULL_RESTORE])) {
            return;
        }

        $syncSession = new SyncSession();
        if (!$syncSession->isRunning()) {
            return;
        }

        $isFinished = false;
        try {
            $jobDataDto->checkNextTask();
        } catch (FinishedQueueException $e) {
            $isFinished = true;
        }

        if ($isFinished && $jobType === JobTransientCache::JOB_TYPE_PULL_PREPARE) {
            $this->setupInitiatorPull($jobDataDto, $jobTransientCache, $syncSession);
            return;
        }

        if ($isFinished && $jobType === JobTransientCache::JOB_TYPE_PULL_RESTORE) {
            return;
        }

        // Early bail: One way sync and not the initiator
        if ((!$syncSession->isTwoWaySync() && !$syncSession->isInitiator())) {
            return;
        }

        $remoteSiteUrl = $this->getRemoteSiteUrl($jobType, $syncSession);
        $this->headers = $this->getAuthorizationHeader($syncSession->getToken());
        $this->sendRestRequest($remoteSiteUrl, 'remote_events', [
            'events' => $syncSession->getEvents(),
        ]);
    }

    /**
     * @param JobDataDto $jobDataDto
     * @param JobTransientCache $jobTransientCache
     * @param SyncSession $syncSession
     * @return void
     * @throws BackupRuntimeException
     */
    private function setupInitiatorPull(JobDataDto $jobDataDto, JobTransientCache $jobTransientCache, SyncSession $syncSession)
    {
        if ($jobDataDto instanceof JobBackupDataDto) {
            $jobTransientCache->updateTitle(esc_html__('Waiting for Pull to be completed', 'wp-staging'));

            /**
             * @var Urls $urls
             */
            $urls        = WPStaging::make(Urls::class);
            $downloadUrl = $urls->getBackupUrl() . basename($jobDataDto->getBackupFilePath());
            if ($syncSession->getProgressStatus() === SyncSession::PROGRESS_STATUS_PREPARED) {
                // Already prepared
                return;
            }

            $syncSession->setProgressStatus(SyncSession::PROGRESS_STATUS_PREPARED);
            $syncSession->setData(SyncSession::PROGRESS_STATUS_PREPARED, [
                'dataUrl' => $downloadUrl,
                'events'  => $syncSession->getEvents(),
            ]);
        }
    }

    /**
     * @param JobTransientCache $jobTransientCache
     * @param string $errorMessage
     * @return void
     */
    public function maybeHandleFailure(JobTransientCache $jobTransientCache, string $errorMessage)
    {
        $this->cleanupLocalTempBackup($jobTransientCache);
        $this->sendRemoteSyncEvent($jobTransientCache, 'remote_sync_failed', [
            'error' => $errorMessage,
        ]);
    }

    /**
     * @param JobTransientCache $jobTransientCache
     * @return void
     */
    public function maybeHandleCancel(JobTransientCache $jobTransientCache)
    {
        $this->cleanupLocalTempBackup($jobTransientCache);
        $this->sendRemoteSyncEvent($jobTransientCache, 'remote_sync_cancelled');
    }

    /**
     * Clean up any partially downloaded temp backup file on the initiator site.
     *
     * @param JobTransientCache $jobTransientCache
     * @return void
     */
    private function cleanupLocalTempBackup(JobTransientCache $jobTransientCache)
    {
        $jobData = $jobTransientCache->getJob();
        $jobId = $jobData['jobId'] ?? '';
        if (empty($jobId)) {
            return;
        }

        /** @var BackupsFinder $backupsFinder */
        $backupsFinder = WPStaging::make(BackupsFinder::class);
        $backupsFinder->deleteTempBackupByJobId($jobId);
    }

    /**
     * @param JobTransientCache $jobTransientCache
     * @param string $endpoint
     * @param array $additionalData
     * @return void
     */
    private function sendRemoteSyncEvent(JobTransientCache $jobTransientCache, string $endpoint, array $additionalData = [])
    {
        $jobType = $jobTransientCache->getJob()['type'] ?? '';
        if (!in_array($jobType, [JobTransientCache::JOB_TYPE_PULL_PREPARE, JobTransientCache::JOB_TYPE_PULL_RESTORE])) {
            return;
        }

        $syncSession = new SyncSession();
        if (!$syncSession->isRunning()) {
            return;
        }

        $remoteSiteUrl = $this->getRemoteSiteUrl($jobType, $syncSession);
        $this->headers = $this->getAuthorizationHeader($syncSession->getToken());

        $data = array_merge(['events' => $syncSession->getEvents()], $additionalData);
        $this->sendRestRequest($remoteSiteUrl, $endpoint, $data);

        $syncSession->stop();
    }

    /**
     * @param string $jobType
     * @param SyncSession $syncSession
     * @return string
     */
    private function getRemoteSiteUrl(string $jobType, SyncSession $syncSession): string
    {
        return $jobType === JobTransientCache::JOB_TYPE_PULL_PREPARE
            ? $syncSession->getPullSiteUrl()
            : $syncSession->getPushSiteUrl();
    }
}
