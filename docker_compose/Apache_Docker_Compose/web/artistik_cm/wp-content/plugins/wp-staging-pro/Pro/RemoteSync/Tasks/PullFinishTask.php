<?php

namespace WPStaging\Pro\RemoteSync\Tasks;

use WPStaging\Backup\Task\Tasks\JobRestore\RestoreFinishTask;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Logger\SseEventCache;
use WPStaging\Framework\Traits\RestRequestTrait;
use WPStaging\Framework\Notices\ObjectCacheNotice;
use WPStaging\Framework\SiteInfo;
use WPStaging\Pro\Analytics\Actions\AnalyticsRemoteSync;
use WPStaging\Pro\RemoteSync\SyncSession;

class PullFinishTask extends RestoreFinishTask
{
    use RestRequestTrait;

    /** @var ObjectCacheNotice */
    protected $objectCacheNotice;

    /** @var SiteInfo */
    protected $siteInfo;

    /**
     * @return string
     */
    public static function getTaskName()
    {
        return 'pull_restore_finish';
    }

    /**
     * @return string
     */
    public static function getTaskTitle()
    {
        return 'Finishing Pull';
    }

    /**
     * @return void
     */
    protected function performRestoreFinishAction()
    {
        $this->logger->info("✓ Remote sync completed successfully.");
        WPStaging::make(AnalyticsRemoteSync::class)->enqueueFinishEvent($this->jobDataDto->getId(), $this->jobDataDto);

        $backupMetadata = $this->jobDataDto->getBackupMetadata();

        unlink($this->jobDataDto->getFile());

        $syncSession = new SyncSession();
        $this->sendPullFinishRequest($syncSession);
        $syncSession->stop();

        $isDatabaseSync = $backupMetadata->getIsExportingDatabase() && !$this->jobDataDto->getIsDatabaseRestoreSkipped();
        $this->logRemoteSyncCompleted($this->jobDataDto->getBackupMetadata());
        $this->logger->pushSseEvent(SseEventCache::EVENT_TYPE_COMPLETE, [
            'status' => 'success',
            'data'   => [
                'message'         => $isDatabaseSync ?
                    esc_html__('Pull request successfully completed! Database changes have been applied. You will be prompted to log in again', 'wp-staging') :
                    esc_html__('Pull request successfully completed!', 'wp-staging'),
                'type'            => 'remote-sync',
                'isDatabaseSync'  => $isDatabaseSync,
                'isPullFromWpCom' => $backupMetadata->getIsCreatedOnWordPressCom(),
            ],
        ]);

        $this->getJobTransientCache()->completeJob();
    }

    /**
     * @return void
     */
    private function sendPullFinishRequest(SyncSession $syncSession)
    {
        try {
            $this->headers = $this->getAuthorizationHeader($syncSession->getToken());
            $this->sendRestRequest($syncSession->getPushSiteUrl(), 'finish_pull', [
                'events' => $syncSession->getEvents(),
            ]);
        } catch (\Throwable $e) {
        }
    }
}
