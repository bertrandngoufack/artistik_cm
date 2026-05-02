<?php

namespace WPStaging\Pro\RemoteSync;

use Exception;
use RuntimeException;
use WP_REST_Request;
use WPStaging\Backup\BackgroundProcessing\Backup\PrepareBackup;
use WPStaging\Backup\Entity\BackupMetadata;
use WPStaging\Backup\Service\BackupsFinder;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Job\BackgroundProcessing\PrepareCancel;
use WPStaging\Framework\Job\JobTransientCache;
use WPStaging\Framework\Traits\BearerTokenTrait;
use WPStaging\Framework\Traits\RestRequestTrait;
use WPStaging\Pro\Analytics\Actions\AnalyticsRemoteSync;
use WPStaging\Pro\License\Licensing;
use WPStaging\Pro\RemoteSync\ConnectionKey;
use WPStaging\Pro\WPStagingPro;
use WPStaging\Pro\WPStagingRestEndpoint;

class RemoteSyncRestEndpoints extends WPStagingRestEndpoint
{
    use RestRequestTrait;
    use BearerTokenTrait;
    use WithStartPullMethods;

    public function verifyRequest(): bool
    {
        if (!ConnectionKey::isEnabled()) {
            return false;
        }

        if (!$this->isDeveloperOrHigherPlan()) {
            return false;
        }

        try {
            $syncSession = new SyncSession();
            return $syncSession->validateSessionToken($this->getBearerToken());
        } catch (RuntimeException $e) {
            return false;
        }
    }

    /**
     * @param \WP_REST_Request $request
     * @return \WP_Error|\WP_REST_Response
     */
    public function restAuthenticate(WP_REST_Request $request)
    {
        if (!ConnectionKey::isEnabled()) {
            return $this->errorResponse(
                esc_html__('Remote Sync is disabled on this site. Ask the site administrator to enable it under Settings > Connection Keys.', 'wp-staging'),
                403
            );
        }

        if (!$this->isDeveloperOrHigherPlan()) {
            return $this->errorResponse('You need a WP Staging Developer plan or higher to use this feature.');
        }

        $params = $request->get_json_params();
        if (!is_array($params)) {
            return $this->errorResponse('Invalid request');
        }

        if (!isset($params['token'])) {
            return $this->errorResponse('Missing token');
        }

        if (!isset($params['sender'])) {
            return $this->errorResponse('Missing sender');
        }

        if (!isset($params['multisite'])) {
            return $this->errorResponse('Missing multisite flag');
        }

        $password = '';
        if (isset($params['password'])) {
            $password = $params['password'];
        }

        $connectionKey = new ConnectionKey();
        if ($params['sender'] === $connectionKey->getRemoteUrl()) {
            return $this->errorResponse('Cannot start sync with itself!');
        }

        $authenticated = $connectionKey->authenticate($params['token'], $password);
        if (!$authenticated) {
            return $this->errorResponse('Connection key or password invalid. Go to the remote site → WP Staging → Settings → Remote Sync and copy and paste again the connection key and password.');
        }

        if ($this->isSyncRunning()) {
            return $this->errorResponse('A sync is already in progress. Please wait until it finishes.');
        }

        if (is_multisite() !== (bool)$params['multisite']) {
            if ((bool)$params['multisite']) {
                $message = esc_html__('This site is a multisite network, but the remote site is a single site installation. Remote Sync only supports single site to single site or multisite to multisite.', 'wp-staging');
            } else {
                $message = esc_html__('This site is a single site installation, but the remote site is a multisite network. Remote Sync only supports single site to single site or multisite to multisite.', 'wp-staging');
            }

            return $this->errorResponse($message, 400, ['errorType' => 'multisite_mismatch']);
        }

        $sessionToken = bin2hex(random_bytes(32));
        $jobId        = bin2hex(random_bytes(16));
        $syncSession  = new SyncSession($jobId, SyncSession::SYNC_TYPE_PULL, $sessionToken, $params['sender'], $connectionKey->getRemoteUrl());

        $syncSession->authenticate();

        return $this->successResponse(
            esc_html__('Authenticated successfully.', 'wp-staging'),
            200,
            [
                'session' => $syncSession->toString(),
            ]
        );
    }

    /**
     * @param \WP_REST_Request $request
     * @return \WP_Error|\WP_REST_Response
     */
    public function restTwoWaySync(WP_REST_Request $request)
    {
        $params = $request->get_json_params();
        if (!is_array($params)) {
            $params = [];
        }

        try {
            $syncSession   = new SyncSession();
            $this->headers = $this->getAuthorizationHeader($syncSession->getToken());
            $response = $this->sendRestRequest($syncSession->getPullSiteUrl(), 'sync_status');

            if (is_wp_error($response)) {
                return $this->errorResponse('Failed to verify connection with the remote site: ' . $response->get_error_message());
            }

            $response = json_decode(wp_remote_retrieve_body($response), true);
            if (empty($response) || !isset($response['success']) || !$response['success']) {
                return $this->errorResponse('Failed to verify connection with the remote site: ' . ($response['message'] ?? ''));
            }

            if ($this->isSyncRunning()) {
                return $this->errorResponse('A sync is already in progress. Please wait until it finishes.');
            }

            $syncSession->setTwoWaySync(true);
            return $this->successResponse(
                "",
                200
            );
        } catch (Exception $e) {
            return $this->errorResponse('Exception thrown in rest two way sync while preparing the sync data. Error: ' . $e->getMessage());
        }
    }

    /**
     * @param \WP_REST_Request $request
     * @return \WP_Error|\WP_REST_Response
     */
    public function restSyncStatus(WP_REST_Request $request)
    {
        $params = $request->get_json_params();
        if (!is_array($params)) {
            $params = [];
        }

        try {
            $syncSession = new SyncSession();
            $status      = $syncSession->getProgressStatus();

            if ($status === SyncSession::PROGRESS_STATUS_INITIATED && !$syncSession->isInitiator()) {
                $syncSession->start();
                $status = $syncSession->getProgressStatus();
            }

            $data           = $syncSession->getData($status);
            $data['events'] = $syncSession->getEvents();

            return $this->successResponse(
                "",
                200,
                [
                    'status' => $status,
                    'data'   => $data,
                ]
            );
        } catch (Exception $e) {
            return $this->errorResponse('Exception thrown in rest sync status while preparing the sync data. Error: ' . $e->getMessage());
        }
    }

    /**
     * @param \WP_REST_Request $request
     * @return \WP_Error|\WP_REST_Response
     */
    public function restInitiateSync(WP_REST_Request $request)
    {
        $params = $request->get_json_params();
        if (!is_array($params)) {
            $params = [];
        }

        try {
            $syncSession = new SyncSession();
            $syncSession->initiate();

            $jobId        = $syncSession->getJobId();
            $params['id'] = $jobId;

            $data  = $this->setupSyncOptions($params);
            $jobId = WPStaging::make(PrepareBackup::class)->prepare($data);

            if ($jobId instanceof \WP_Error) {
                return $this->errorResponse('Failed to initiate sync: ' . $jobId->get_error_message());
            }

            /**
             * lazy load the TransientCache
             * @var JobTransientCache $transientCache
             */
            $transientCache = WPStaging::make(JobTransientCache::class);
            $transientCache->startJob($syncSession->getJobId(), esc_html__('Preparing data for Pull', 'wp-staging'), JobTransientCache::JOB_TYPE_PULL_PREPARE, $syncSession->getJobId());

            return $this->successResponse(
                sprintf(
                    "Sync is initiated on remote site with Job ID %s",
                    $jobId
                ),
                200,
                [
                    'jobId' => $jobId,
                ]
            );
        } catch (Exception $e) {
            return $this->errorResponse('Exception thrown while initiating the sync: ' . $e->getMessage());
        }
    }

    /**
     * @param \WP_REST_Request $request
     * @return \WP_Error|\WP_REST_Response|void
     */
    public function restPreparePull(WP_REST_Request $request)
    {
        try {
            $syncSession = new SyncSession();
            $syncSession->start();
        } catch (Exception $e) {
            return $this->errorResponse('Exception thrown in rest prepare pull while preparing the sync data: ' . $e->getMessage());
        }
    }

    /**
     * @param \WP_REST_Request $request
     * @return \WP_Error|\WP_REST_Response
     */
    public function restRemoteEvents(WP_REST_Request $request)
    {
        $params = $request->get_json_params();
        if (!is_array($params)) {
            $params = [];
        }

        $events = $params['events'] ?? [];
        if (!is_array($events)) {
            $events = [];
        }

        try {
            /** @var JobTransientCache */
            $jobTransientCache = WPStaging::make(JobTransientCache::class);
            $jobTransientCache->update();

            $syncSession = new SyncSession();

            $this->pushEvents($syncSession->getJobId(), $events);

            return $this->successResponse("Events sent", 200);
        } catch (Exception $e) {
            return $this->errorResponse('Exception thrown while adding remote logs: ' . $e->getMessage());
        }
    }

    /**
     * @param \WP_REST_Request $request
     * @return \WP_Error|\WP_REST_Response
     */
    public function restStartPull(WP_REST_Request $request)
    {
        $params = $request->get_json_params();
        if (!is_array($params)) {
            $params = [];
        }

        try {
            $jobId = $this->startPull($params);

            return $this->successResponse(
                sprintf(
                    "Pull with start with Job ID %s",
                    $jobId
                )
            );
        } catch (Exception $e) {
            return $this->errorResponse('Exception thrown while starting the pull: ' . $e->getMessage());
        }
    }

    /**
     * @param \WP_REST_Request $request
     * @return \WP_Error|\WP_REST_Response
     */
    public function restSyncCancelled(WP_REST_Request $request)
    {
        $params = $request->get_json_params();
        if (!is_array($params)) {
            $params = [];
        }

        try {
            $syncSession = new SyncSession();
            $jobId = $syncSession->getJobId();
            $isSessionRunning = $syncSession->isRunning();
            $hasRunningPullJob = $this->hasRunningPullJob($syncSession);
            $hasAnyRunningJob = WPStaging::make(JobTransientCache::class)->getJobStatus() === JobTransientCache::STATUS_RUNNING;
            $shouldAttemptCancel = $hasRunningPullJob || ($isSessionRunning && !$hasAnyRunningJob);

            if (!empty($jobId)) {
                $events = $params['events'] ?? [];
                if ($shouldAttemptCancel) {
                    $events[] = [
                        'type'    => 'warning',
                        'message' => esc_html__('Pull cancellation was requested from the initiator site.', 'wp-staging'),
                    ];
                }

                $this->pushEvents($jobId, $events);
            }

            if (!$shouldAttemptCancel) {
                $syncSession->stop();
                return $this->successResponse('Sync already cancelled');
            }

            $prepareCancelResponse = WPStaging::make(PrepareCancel::class)->prepare([]);
            if ($prepareCancelResponse instanceof \WP_Error) {
                // Cancellation can race with a just-finished pull. Treat it as an already-cancelled state.
                if (stripos($prepareCancelResponse->get_error_message(), 'Job is not running') !== false) {
                    $syncSession->stop();
                    return $this->successResponse('Sync already cancelled');
                }

                return $this->errorResponse('Failed to cancel pull on remote site: ' . $prepareCancelResponse->get_error_message());
            }

            // Clean up any partially downloaded temp backup file
            /** @var BackupsFinder $backupsFinder */
            $backupsFinder = WPStaging::make(BackupsFinder::class);
            $backupsFinder->deleteTempBackupByJobId($jobId);

            $syncSession->stop();

            // Mark analytics as cancelled
            try {
                AnalyticsRemoteSync::enqueueCancelEvent($jobId);
            } catch (\Throwable $e) {
                // no-op
            }

            return $this->successResponse(
                "Sync cancel requested"
            );
        } catch (Exception $e) {
            return $this->errorResponse('Exception thrown while cancelling the sync: ' . $e->getMessage());
        }
    }

    /**
     * Check whether there is an active pull job that can still be cancelled.
     *
     * Accepts a running sync session as a secondary signal because some pull states
     * can temporarily expose a non-pull type in transient cache while still belonging
     * to the current sync session queue.
     *
     * @param SyncSession|null $syncSession
     * @return bool
     */
    private function hasRunningPullJob($syncSession = null): bool
    {
        /** @var JobTransientCache $jobTransientCache */
        $jobTransientCache = WPStaging::make(JobTransientCache::class);
        $jobData           = $jobTransientCache->getJob();
        if (empty($jobData) || empty($jobData['status'])) {
            return false;
        }

        if ($jobData['status'] !== JobTransientCache::STATUS_RUNNING) {
            return false;
        }

        if (
            !empty($jobData['type']) &&
            in_array($jobData['type'], [JobTransientCache::JOB_TYPE_PULL_PREPARE, JobTransientCache::JOB_TYPE_PULL_RESTORE], true)
        ) {
            return true;
        }

        if (!$syncSession instanceof SyncSession || !$syncSession->isRunning()) {
            return false;
        }

        $sessionJobId = $syncSession->getJobId();
        if (empty($sessionJobId)) {
            return false;
        }

        $jobId = $jobData['jobId'] ?? '';
        $queueId = $jobData['queueId'] ?? '';

        return $jobId === $sessionJobId || $queueId === $sessionJobId;
    }

    /**
     * @return \WP_Error|\WP_REST_Response
     */
    public function restFinishDownload()
    {
        try {
            $syncSession = new SyncSession();
            $jobId       = $syncSession->getJobId();

            /**
             * @var BackupsFinder $backupsFinder
             */
            $backupsFinder = WPStaging::make(BackupsFinder::class);
            $backupsFinder->deleteTempBackupByJobId($jobId);

            return $this->successResponse(
                sprintf(
                    "Removed temp data with Job ID %s",
                    $jobId
                )
            );
        } catch (Exception $e) {
            return $this->errorResponse('Exception thrown while starting the pull: ' . $e->getMessage());
        }
    }

    /**
     * @param \WP_REST_Request $request
     * @return \WP_Error|\WP_REST_Response
     */
    public function restFinishPull(WP_REST_Request $request)
    {
        $params = $request->get_json_params();
        if (!is_array($params)) {
            $params = [];
        }

        try {
            $syncSession = new SyncSession();
            $this->pushEvents($syncSession->getJobId(), $params['events'] ?? []);
            $syncSession->stop();

            /** @var JobTransientCache $jobTransientCache */
            $jobTransientCache = WPStaging::make(JobTransientCache::class);
            $jobTransientCache->completeJob();

            // Mark analytics as finished
            try {
                /** @var AnalyticsRemoteSync $analytics */
                $analytics = WPStaging::make(AnalyticsRemoteSync::class);
                $analytics->enqueueFinishEvent($syncSession->getJobId(), (object)[]);
            } catch (\Throwable $e) {
                // no-op
            }

            return $this->successResponse(
                "Pull finished"
            );
        } catch (Exception $e) {
            return $this->errorResponse('Exception thrown while finishing the pull: ' . $e->getMessage());
        }
    }

    /**
     * @param \WP_REST_Request $request
     * @return \WP_Error|\WP_REST_Response
     */
    public function restSyncFailed(WP_REST_Request $request)
    {
        $params = $request->get_json_params();
        if (!is_array($params)) {
            $params = [];
        }

        try {
            $syncSession = new SyncSession();
            $jobId = $syncSession->getJobId();
            $this->pushEvents($jobId, $params['events'] ?? []);

            // Clean up any partially downloaded temp backup file
            /** @var BackupsFinder $backupsFinder */
            $backupsFinder = WPStaging::make(BackupsFinder::class);
            $backupsFinder->deleteTempBackupByJobId($jobId);

            $syncSession->stop();

            /** @var JobTransientCache $jobTransientCache */
            $jobTransientCache = WPStaging::make(JobTransientCache::class);
            $jobTransientCache->failJob();

            // Mark analytics as error
            try {
                $errorMsg = isset($params['error']) ? (string)$params['error'] : 'Remote sync failed';
                AnalyticsRemoteSync::enqueueErrorEvent($jobId, $errorMsg);
            } catch (\Throwable $e) {
                // no-op
            }

            return $this->successResponse(
                "Sync Failed"
            );
        } catch (Exception $e) {
            return $this->errorResponse('Exception thrown while stopping the sync: ' . $e->getMessage());
        }
    }

    protected function setupSyncOptions(array $options): array
    {
        $data = [];
        if (isset($options['includes'])) {
            $data = $this->validateIncludes($options['includes'], $data);
        }

        $data['id']            = $options['id'] ?? null;
        $data['isRestRequest'] = true;
        $data['isSyncRequest'] = true;
        $options['multisite']  = $options['multisite'] ?? false;

        if (is_multisite() !== $options['multisite']) {
            throw new Exception('Cannot sync single site to multisite or vice versa');
        }

        if (is_multisite()) {
            $data['backupType'] = BackupMetadata::BACKUP_TYPE_MULTISITE;
        } else {
            $data['backupType'] = BackupMetadata::BACKUP_TYPE_SINGLE;
        }

        return $data;
    }

    protected function validateIncludes(array $includes, array $data): array
    {
        $includes = array_map('trim', $includes);
        $includes = array_filter($includes, function ($include) {
            return !empty($include);
        });

        if (empty($includes)) {
            $data['isExportingPlugins']             = true;
            $data['isExportingMuPlugins']           = true;
            $data['isExportingThemes']              = true;
            $data['isExportingUploads']             = true;
            $data['isExportingOtherWpContentFiles'] = true;
            $data['isExportingOtherWpRootFiles']    = false;
            $data['isExportingDatabase']            = true;

            return $data;
        }

        $data['isExportingPlugins']             = in_array('plugins', $includes);
        $data['isExportingMuPlugins']           = in_array('mu-plugins', $includes);
        $data['isExportingThemes']              = in_array('themes', $includes);
        $data['isExportingUploads']             = in_array('uploads', $includes);
        $data['isExportingOtherWpContentFiles'] = in_array('other-content', $includes);
        $data['isExportingOtherWpRootFiles']    = false;
        $data['isExportingDatabase']            = in_array('database', $includes);

        return $data;
    }

    private function isDeveloperOrHigherPlan(): bool
    {
        if (!WPStagingPro::isValidLicense()) {
            return false;
        }

        $licensing = WPStaging::make(Licensing::class);

        return $licensing->isActiveAgencyOrDeveloperPlan();
    }

    /**
     * Check if a sync job is already running
     * @return bool
     */
    private function isSyncRunning(): bool
    {
        return SyncSession::isAnySyncRunning();
    }
}
