<?php

namespace WPStaging\Pro\RemoteSync\Ajax;

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Database\TableService;
use WPStaging\Framework\Facades\Hooks;
use WPStaging\Framework\Facades\Sanitize;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Framework\Job\Ajax\PrepareJob;
use WPStaging\Framework\Job\Exception\ProcessLockedException;
use WPStaging\Framework\Job\JobTransientCache;
use WPStaging\Framework\Job\ProcessLock;
use WPStaging\Framework\Job\Traits\WithTmpDatabasePrefix;
use WPStaging\Framework\Security\Auth;
use WPStaging\Pro\RemoteSync\Dto\Job\PullInitiatorDataDto;
use WPStaging\Pro\RemoteSync\Jobs\PullInitiator;
use WPStaging\Pro\RemoteSync\SyncSession;

class PreparePull extends PrepareJob
{
    use WithTmpDatabasePrefix;

    /** @var PullInitiatorDataDto */
    private $jobDataDto;

    /** @var PullInitiator */
    private $jobPull;

    /** @var TableService */
    private $tableService;

    public function __construct(Filesystem $filesystem, Directory $directory, Auth $auth, ProcessLock $processLock, TableService $tableService)
    {
        parent::__construct($filesystem, $directory, $auth, $processLock);
        $this->tableService = $tableService;
    }

    /**
     * @param array|null $data
     * @return void
     */
    public function ajaxPrepare($data)
    {
        if (!$this->auth->isAuthenticatedRequest()) {
            wp_send_json_error(null, 401);
        }

        try {
            $this->processLock->checkProcessLocked();
        } catch (ProcessLockedException $e) {
            wp_send_json_error($e->getMessage(), $e->getCode());
        }

        $response = $this->prepare($data);

        if ($response instanceof \WP_Error) {
            wp_send_json_error($response->get_error_message(), $response->get_error_code());
            return;
        }

        wp_send_json_success();
    }

    /**
     * @param array|null $data
     * @return array|\WP_Error
     */
    public function prepare($data = null)
    {
        // Lazy-instantiation to avoid process-lock checks conflicting with running processes.
        $container        = WPStaging::getInstance()->getContainer();
        $this->jobDataDto = $container->get(PullInitiatorDataDto::class);
        $this->jobPull    = $container->get(PullInitiator::class);

        if (empty($data) && array_key_exists('wpstgPullData', $_POST)) {
            $data = Sanitize::sanitizeArray($_POST['wpstgPullData'], [
                'isSyncUploads'      => 'bool',
                'isSyncPlugins'      => 'bool',
                'isSyncThemes'       => 'bool',
                'isSyncDatabase'     => 'bool',
                'isSyncMuPlugins'    => 'bool',
                'isSyncOtherContent' => 'bool',
            ]);
        }

        try {
            $sanitizedData = $this->setupInitialData($data);
        } catch (\Exception $e) {
            return new \WP_Error(400, $e->getMessage());
        }

        return $sanitizedData;
    }

    /**
     * @param array|null $data
     * @return array
     */
    public function validateAndSanitizeData($data): array
    {
        $expectedKeys = [
            'isSyncUploads',
            'isSyncPlugins',
            'isSyncThemes',
            'isSyncDatabase',
            'isSyncMuPlugins',
            'isSyncOtherContent',
        ];

        // Make sure data has no keys other than the expected ones.
        $data = array_intersect_key($data, array_flip($expectedKeys));

        // Make sure data has all expected keys.
        foreach ($expectedKeys as $expectedKey) {
            if (!array_key_exists($expectedKey, $data)) {
                throw new \UnexpectedValueException("Invalid request. Missing '$expectedKey'.");
            }
        }

        return $data;
    }

    /**
     * @return PullInitiator
     */
    public function getJob()
    {
        return $this->jobPull;
    }

    public function persist(): bool
    {
        if (!$this->jobPull instanceof PullInitiator) {
            return false;
        }

        $this->jobPull->persist();

        return true;
    }

    /**
     * @param array|null $data
     * @return array
     */
    private function setupInitialData($sanitizedData)
    {
        $syncSession   = new SyncSession();
        $sanitizedData = $this->validateAndSanitizeData($sanitizedData);
        $this->clearCacheFolder();

        $this->jobDataDto->setId($syncSession->getJobId());
        $this->jobDataDto->hydrate($sanitizedData);
        $this->jobDataDto->setInit(true);
        $this->jobDataDto->setFinished(false);
        $this->jobDataDto->setTmpDatabasePrefix($this->getTmpDatabasePrefix());
        $this->jobDataDto->setIsRestRequest(true);
        $this->jobDataDto->setIsSyncRequest(true);
        $this->jobDataDto->setIsTwoWaySync($syncSession->isTwoWaySync());
        $this->jobDataDto->setRemoteUrl($syncSession->getRemoteUrl());
        $httpAuthUsername = isset($_POST['httpAuthUsername']) ? Sanitize::sanitizeString($_POST['httpAuthUsername']) : '';
        $httpAuthPassword = isset($_POST['httpAuthPassword']) ? Sanitize::sanitizePassword($_POST['httpAuthPassword']) : '';
        $this->jobDataDto->setHttpAuthUsername($httpAuthUsername);
        $this->jobDataDto->setHttpAuthPassword($httpAuthPassword);
        $this->jobDataDto->setFile('');

        $syncSession->start();
        $this->jobPull->getTransientCache()->startJob($this->jobDataDto->getId(), esc_html__('Pull in Progress', 'wp-staging'), JobTransientCache::JOB_TYPE_PULL_RESTORE, $this->jobDataDto->getId());
        $this->jobPull->setJobDataDto($this->jobDataDto);

        return $sanitizedData;
    }
}
