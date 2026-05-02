<?php

namespace WPStaging\Pro\RemoteSync\Ajax;

use InvalidArgumentException;
use Throwable;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\Facades\Sanitize;
use WPStaging\Framework\Job\JobTransientCache;
use WPStaging\Framework\Logger\SseEventCache;
use WPStaging\Framework\Traits\RestRequestTrait;
use WPStaging\Pro\Analytics\Actions\AnalyticsRemoteSync;
use WPStaging\Pro\RemoteSync\BackgroundProcessing\PreparePull;
use WPStaging\Pro\RemoteSync\ConnectionKey;
use WPStaging\Pro\RemoteSync\RemoteSyncException;
use WPStaging\Pro\RemoteSync\SyncSession;

class RemoteSync extends AbstractTemplateComponent
{
    use RestRequestTrait;

    /**
     * Ajax authenticate method
     * @return void
     */
    public function ajaxAuthenticate()
    {
        if (!$this->canRenderAjax()) {
            wp_send_json_error([
                'message' => esc_html__('Invalid Request!', 'wp-staging'),
            ], 401);
        }

        if ($this->isSyncRunning()) {
            wp_send_json_error([
                'message' => esc_html__('There is a job already work in progress.', 'wp-staging'),
            ], 401);
        }

        $httpAuthUsername = isset($_POST['httpAuthUsername']) ? Sanitize::sanitizeString($_POST['httpAuthUsername']) : '';
        $httpAuthPassword = isset($_POST['httpAuthPassword']) ? Sanitize::sanitizePassword($_POST['httpAuthPassword']) : '';
        $this->setHttpAuth($httpAuthUsername, $httpAuthPassword);

        try {
            $syncSession = $this->authenticate();
        } catch (RemoteSyncException $e) {
            wp_send_json_error([
                'message' => esc_html($e->getMessage()),
            ], $e->getCode());
        }

        $syncSession->authenticate(SyncSession::INITIATOR);

        try {
            $analyticsRemoteSync = WPStaging::make(AnalyticsRemoteSync::class);
            $analyticsRemoteSync->enqueueStartEvent($syncSession->getJobId(), [
                'isInitiator' => true,
                'remoteUrl'   => $syncSession->getRemoteUrl(),
            ]);
        } catch (Throwable $e) {
        }

        wp_send_json_success([
            'remoteUrl' => $syncSession->getRemoteUrl(),
        ]);
    }

    /**
     * Ajax check is two way sync.
     * @return void
     */
    public function ajaxIsTwoWaySync()
    {
        if (!$this->canRenderAjax()) {
            wp_send_json_error([
                'message' => esc_html__('Invalid Request!', 'wp-staging'),
            ], 401);
        }

        $httpAuthUsername = isset($_POST['httpAuthUsername']) ? Sanitize::sanitizeString($_POST['httpAuthUsername']) : '';
        $httpAuthPassword = isset($_POST['httpAuthPassword']) ? Sanitize::sanitizePassword($_POST['httpAuthPassword']) : '';
        $this->setHttpAuth($httpAuthUsername, $httpAuthPassword);

        $syncSession = new SyncSession();
        if ($this->isTwoWaySync($syncSession)) {
            // Save the two way sync setting
            $syncSession->setTwoWaySync(true);
            wp_send_json_success();
        }

        try {
            $analyticsRemoteSync = WPStaging::make(AnalyticsRemoteSync::class);
            $analyticsRemoteSync->enqueueStartEvent($syncSession->getJobId(), [
                'isInitiator'  => true,
                'remoteUrl'    => $syncSession->getRemoteUrl(),
                'isTwoWaySync' => $syncSession->isTwoWaySync(),
            ]);
        } catch (Throwable $e) {
            error_log(sprintf(
                'Failed to enqueue Remote Sync analytics two-way sync event. Error: %s | Job ID: %s',
                $e->getMessage(),
                $syncSession->getJobId()
            ));
        }

        wp_send_json_error();
    }

    /**
     * Ajax prepare method for initiating the sync process.
     * @return void
     */
    public function ajaxStartPull()
    {
        if (!$this->canRenderAjax()) {
            wp_send_json_error([
                'message' => esc_html__('Invalid Request!', 'wp-staging'),
            ], 401);
        }

        if ($this->isSyncRunning()) {
            wp_send_json_error([
                'message' => esc_html__('There is a job already work in progress.', 'wp-staging'),
            ], 401);
        }

        $httpAuthUsername = isset($_POST['httpAuthUsername']) ? Sanitize::sanitizeString($_POST['httpAuthUsername']) : '';
        $httpAuthPassword = isset($_POST['httpAuthPassword']) ? Sanitize::sanitizePassword($_POST['httpAuthPassword']) : '';
        $this->setHttpAuth($httpAuthUsername, $httpAuthPassword);

        $jobId = WPStaging::make(PreparePull::class)->prepare($_POST);
        if ($jobId instanceof \WP_Error) {
            wp_send_json_error([
                'message' => esc_html__('Unable to prepare pull request.', 'wp-staging'),
            ], 401);
        }

        $syncSession = new SyncSession();

        // Pre-initialize the job transient and SSE cache so the SSE endpoint can
        // authenticate and stream events before the background queue starts the job.
        $jobTransientCache = WPStaging::make(JobTransientCache::class);
        $jobTransientCache->startJob(
            $syncSession->getJobId(),
            esc_html__('Initializing...', 'wp-staging'),
            JobTransientCache::JOB_TYPE_PULL_PREPARE
        );
        $jobTransientCache->markAsPreInitialized();

        $sseEventCache = WPStaging::make(SseEventCache::class);
        $sseEventCache->setJobId($syncSession->getJobId());
        $sseEventCache->push([
            'type' => SseEventCache::EVENT_TYPE_TASK,
            'data' => [
                'percentage' => 0,
                'title'      => esc_html__('Initializing...', 'wp-staging'),
            ],
        ]);

        $this->headers = $this->getAuthorizationHeader($syncSession->getToken());
        $this->isBlockingRequest = false;
        try {
            $this->sendRestRequest($syncSession->getRemoteUrl(), 'initiate_sync', $this->getPrepareRequestBody('pull'));
        } finally {
            $this->isBlockingRequest = true;
        }

        try {
            $analyticsRemoteSync = WPStaging::make(AnalyticsRemoteSync::class);
            $analyticsRemoteSync->enqueueStartEvent($syncSession->getJobId(), [
                'isInitiator'        => true,
                'remoteUrl'          => $syncSession->getRemoteUrl(),
                'isTwoWaySync'       => $syncSession->isTwoWaySync(),
                'isSyncUploads'      => $this->isChecked('isSyncUploads'),
                'isSyncPlugins'      => $this->isChecked('isSyncPlugins'),
                'isSyncThemes'       => $this->isChecked('isSyncThemes'),
                'isSyncDatabase'     => $this->isChecked('isSyncDatabase'),
                'isSyncMuPlugins'    => $this->isChecked('isSyncMuPlugins'),
                'isSyncOtherContent' => $this->isChecked('isSyncOtherContent'),
            ]);
        } catch (Throwable $e) {
            error_log(sprintf(
                'Failed to enqueue Remote Sync analytics start pull event. Error: %s | Job ID: %s',
                $e->getMessage(),
                $syncSession->getJobId()
            ));
        }

        wp_send_json_success([
            'jobId' => $syncSession->getJobId(),
        ]);
    }

    /**
     * Ajax check if site already synced.
     * @return void
     */
    public function ajaxCheckAlreadySync()
    {
        if (!$this->canRenderAjax()) {
            wp_send_json_error([
                'message' => esc_html__('Invalid Request!', 'wp-staging'),
            ], 401);
        }

        if ($this->isSyncRunning()) {
            wp_send_json_error([
                'message' => esc_html__('There is a job already work in progress.', 'wp-staging'),
            ]);
        }

        wp_send_json_success();
    }

    protected function parseConnectionKey(): ConnectionKey
    {
        $connectionKey = isset($_POST['connectionKey']) ? Sanitize::sanitizeString($_POST['connectionKey']) : '';
        if (empty($connectionKey)) {
            throw new InvalidArgumentException(esc_html__('Enter a connection key. Go to the remote site. Open WP Staging → Settings → Remote Sync. Copy and paste the connection key and password here.', 'wp-staging'));
        }

        return ConnectionKey::parse($connectionKey);
    }

    protected function isTwoWaySync(SyncSession $syncSession): bool
    {
        $this->headers = $this->getAuthorizationHeader($syncSession->getToken());
        $response = $this->sendRestRequest($syncSession->getRemoteUrl(), 'two_way_sync');
        if (is_wp_error($response)) {
            return false;
        }

        $response = json_decode(wp_remote_retrieve_body($response), true);
        return !empty($response['success']);
    }

    private function enqueueAnalyticFailure(string $remoteUrl, string $errorMessage, string $jobId = '')
    {
        if (empty($jobId)) {
            $jobId = bin2hex(random_bytes(6));
        }

        try {
            // Let start with data
            /** @var AnalyticsRemoteSync $analytics */
            $analytics = WPStaging::make(AnalyticsRemoteSync::class);
            $analytics->enqueueStartEvent($jobId, [
                'isSyncUploads'      => true,
                'isSyncPlugins'      => true,
                'isSyncThemes'       => true,
                'isSyncDatabase'     => true,
                'isSyncMuPlugins'    => true,
                'isSyncOtherContent' => true,
                'isTwoWaySync'       => false,
                'isInitiator'        => true,
                'syncType'           => 'pull',
                'remoteUrl'          => $remoteUrl,
            ]);

            // and enqueue failure
            AnalyticsRemoteSync::enqueueErrorEvent($jobId, $errorMessage);
        } catch (\Throwable $e) {
            // Handle any errors that occur during the analytics event enqueueing
            error_log(sprintf(
                'Failed to enqueue Remote Sync analytics failure event. Error: %s | Error Message: %s | Job ID: %s',
                $e->getMessage(),
                $errorMessage,
                $jobId
            ));
        }
    }

    /**
     * Initiates authentication with the remote site. If couldn't authenticate, it will throw an exception.
     * @return SyncSession
     * @throws RemoteSyncException
     */
    private function authenticate(): SyncSession
    {
        $password = isset($_POST['password']) ? Sanitize::sanitizeString($_POST['password']) : '';
        try {
            $targetSiteConnectionKey = $this->parseConnectionKey();
        } catch (InvalidArgumentException $e) {
            $this->enqueueAnalyticFailure('', $e->getMessage());
            throw new RemoteSyncException($e->getMessage(), 400);
        }

        $currentSiteConnectionKey = new ConnectionKey();
        if ($targetSiteConnectionKey->getRemoteUrl() === $currentSiteConnectionKey->getRemoteUrl()) {
            $this->enqueueAnalyticFailure('', 'Cannot start sync with itself!');
            throw new RemoteSyncException('Cannot start sync with itself!', 400);
        }

        $requestBody = [
            'token'     => $targetSiteConnectionKey->getApiToken(),
            'sender'    => $currentSiteConnectionKey->getRemoteUrl(),
            'multisite' => is_multisite(),
        ];

        if (!empty($password)) {
            $requestBody['password'] = $password;
        }

        $targetSiteUrl = $targetSiteConnectionKey->getRemoteUrl();
        $response = $this->sendRestRequest($targetSiteUrl, 'authenticate', $requestBody);
        if (is_wp_error($response)) {
            $this->enqueueAnalyticFailure($targetSiteUrl, 'Failed to authenticate with the remote site.');
            error_log(sprintf(
                'Failed to authenticate with the remote site. Error: %s',
                $response->get_error_message()
            ));

            throw new RemoteSyncException('Failed to authenticate with the remote site. Error: ' . $response->get_error_message(), 401);
        }

        $httpStatusCode = (int)wp_remote_retrieve_response_code($response);
        if ($httpStatusCode === 401 || $httpStatusCode === 403) {
            $this->enqueueAnalyticFailure($targetSiteUrl, 'Failed to authenticate with the remote site.');
            throw new RemoteSyncException(
                sprintf(
                    'Failed to authenticate with the remote site. The remote site returned HTTP %d. If the remote site is protected by HTTP Basic Authentication, please provide the correct HTTP Auth username and password.',
                    $httpStatusCode
                ),
                $httpStatusCode
            );
        }

        $response = json_decode(wp_remote_retrieve_body($response), true);
        if (!isset($response['success']) || !$response['success']) {
            $errorType = $response['data']['errorType'] ?? '';
            if ($errorType === 'multisite_mismatch') {
                $this->enqueueAnalyticFailure($targetSiteUrl, $response['message'] ?? 'Multisite mismatch');
                throw new RemoteSyncException($response['message'] ?? esc_html__('Cannot sync between single site and multisite installations.', 'wp-staging'), 400);
            }

            $this->enqueueAnalyticFailure($targetSiteUrl, 'Failed to authenticate with the remote site.');
            throw new RemoteSyncException('Failed to authenticate with the remote site. Error: ' . ($response['message'] ?? 'Unknown error'), 401);
        }

        // This should not happen, but just in case
        if (empty($response['data']['session'])) {
            $this->enqueueAnalyticFailure($targetSiteUrl, 'Failed to authenticate with the remote site.');
            throw new RemoteSyncException('Failed to authenticate with the remote site. Error: Not able to start session!', 401);
        }

        $syncSession = SyncSession::parse($response['data']['session']);
        if (empty($syncSession->getJobId())) {
            $this->enqueueAnalyticFailure($targetSiteUrl, 'Failed to authenticate with the remote site.');
            throw new RemoteSyncException('Failed to authenticate with the remote site. Error: Not able to start session!', 401);
        }

        return $syncSession;
    }

    /**
     * Check if a sync job is already running
     * @return bool
     */
    private function isSyncRunning(): bool
    {
        return SyncSession::isAnySyncRunning();
    }

    private function isChecked($key): bool
    {
        return filter_input(INPUT_POST, $key, FILTER_VALIDATE_BOOLEAN);
    }

    private function getPrepareRequestBody(string $syncType): array
    {
        $optionMap = [
            'isSyncUploads'      => 'uploads',
            'isSyncPlugins'      => 'plugins',
            'isSyncThemes'       => 'themes',
            'isSyncDatabase'     => 'database',
            'isSyncMuPlugins'    => 'mu-plugins',
            'isSyncOtherContent' => 'other-content',
        ];

        $includes = [];
        foreach ($optionMap as $key => $value) {
            if ($this->isChecked($key)) {
                $includes[] = $value;
            }
        }

        return [
            'multisite' => is_multisite(),
            'syncType'  => $syncType,
            'includes'  => $includes,
        ];
    }
}
