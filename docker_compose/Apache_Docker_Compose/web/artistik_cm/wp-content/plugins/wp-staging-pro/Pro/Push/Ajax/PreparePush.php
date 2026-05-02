<?php

namespace WPStaging\Pro\Push\Ajax;

use WPStaging\Backup\Ajax\Backup\PrepareBackup;
use WPStaging\Backup\BackupDeleter;
use WPStaging\Backup\Entity\BackupMetadata;
use WPStaging\Backup\Service\Database\DatabaseImporter;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Facades\Sanitize;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Framework\Filesystem\Scanning\ScanConst;
use WPStaging\Framework\Job\Ajax\PrepareJob;
use WPStaging\Framework\Job\Exception\ProcessLockedException;
use WPStaging\Framework\Job\JobTransientCache;
use WPStaging\Framework\Job\ProcessLock;
use WPStaging\Framework\Security\Auth;
use WPStaging\Pro\Push\Dto\StagingSitePushDataDto;
use WPStaging\Pro\Push\Jobs\StagingSitePush;
use WPStaging\Staging\Dto\StagingSiteDto;
use WPStaging\Staging\Service\StagingSetup;
use WPStaging\Staging\Sites;

class PreparePush extends PrepareJob
{
    /** @var string */
    const RESPONSE_TYPE_PUSH = 'push';

    /** @var string */
    const RESPONSE_TYPE_BACKUP = 'backup';

    /** @var StagingSitePushDataDto */
    protected $jobDataDto;

    /** @var StagingSitePush */
    protected $jobPush;

    /** @var BackupDeleter */
    private $backupDeleter;

    /** @var PrepareBackup */
    private $prepareBackup;

    public function __construct(Filesystem $filesystem, Directory $directory, Auth $auth, ProcessLock $processLock, BackupDeleter $backupDeleter, PrepareBackup $prepareBackup)
    {
        parent::__construct($filesystem, $directory, $auth, $processLock);
        $this->backupDeleter = $backupDeleter;
        $this->prepareBackup = $prepareBackup;
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
        }

        wp_send_json_success([
            'responseType' => $response['responseType'],
        ]);
    }

    /**
     * @param array|null $data
     * @return array|\WP_Error
     */
    public function prepare($data = null)
    {
        if (empty($data) && array_key_exists('wpstgPushData', $_POST)) {
            $data = Sanitize::sanitizeArray($_POST['wpstgPushData'], [
                'cloneId'                => 'string',
                'allTablesExcluded'      => 'bool',
                'isCreateDatabaseBackup' => 'bool',
                'isCleanPluginsThemes'   => 'bool',
                'isCleanUploads'         => 'bool',
                'isBackupUploads'        => 'bool',
            ]);

            $data['excludedTables']      = isset($_POST['wpstgPushData']['excludedTables']) ? $this->parseAndSanitizeTables($_POST['wpstgPushData']['excludedTables']) : []; // phpcs:ignore
            $data['includedTables']      = isset($_POST['wpstgPushData']['includedTables']) ? $this->parseAndSanitizeTables($_POST['wpstgPushData']['includedTables']) : []; // phpcs:ignore
            $data['nonSiteTables']       = isset($_POST['wpstgPushData']['nonSiteTables']) ? $this->parseAndSanitizeTables($_POST['wpstgPushData']['nonSiteTables']) : []; // phpcs:ignore
            $data['excludedDirectories'] = isset($_POST['wpstgPushData']['excludedDirectories']) ? $this->parseAndSanitizeDirectories($_POST['wpstgPushData']['excludedDirectories']) : []; // phpcs:ignore
            $data['extraDirectories']    = isset($_POST['wpstgPushData']['extraDirectories']) ? $this->parseAndSanitizeDirectories($_POST['wpstgPushData']['extraDirectories']) : []; // phpcs:ignore
        }

        try {
            $sanitizedData = $this->validateAndSanitizeData($data);

            if ($this->shouldPrepareBackup($sanitizedData)) {
                $backupResponse = $this->prepareBackupForPush($sanitizedData);
                if ($backupResponse instanceof \WP_Error) {
                    return $backupResponse;
                }

                $this->deleteSseCacheFiles();

                $sanitizedData['responseType'] = self::RESPONSE_TYPE_BACKUP;

                return $sanitizedData;
            }

            $sanitizedData = $this->setupInitialData($sanitizedData, true);
        } catch (\Exception $e) {
            return new \WP_Error(400, $e->getMessage());
        }

        $this->deleteSseCacheFiles();

        $sanitizedData['responseType'] = self::RESPONSE_TYPE_PUSH;

        return $sanitizedData;
    }

    /**
     * @param array|null $data
     * @return array
     */
    public function validateAndSanitizeData($data): array
    {
        if (empty($data)) {
            $data = [];
        }

        // Unset any empty value so that we replace them with the defaults.
        foreach ($data as $key => $value) {
            if (empty($value)) {
                unset($data[$key]);
            }
        }

        $defaults = $this->getDefaults();

        $data = wp_parse_args($data, $defaults);

        // Make sure data has no keys other than the expected ones.
        $data = array_intersect_key($data, $defaults);

        // Make sure data has all expected keys.
        foreach ($defaults as $expectedKey => $value) {
            if (!array_key_exists($expectedKey, $data)) {
                throw new \UnexpectedValueException("Invalid request. Missing '$expectedKey'.");
            }
        }

        // Clone ID
        $data['cloneId'] = sanitize_text_field($data['cloneId']);

        if (empty($data['cloneId'])) {
            throw new \UnexpectedValueException("Invalid request. Missing 'cloneId'.");
        }

        // Included/Excluded tables
        $data['excludedTables'] = array_map('sanitize_text_field', $data['excludedTables']);
        $data['includedTables'] = array_map('sanitize_text_field', $data['includedTables']);
        $data['nonSiteTables']  = array_map('sanitize_text_field', $data['nonSiteTables']);

        // Extra directories and directories exclusion and rules
        $data['extraDirectories']    = array_map('sanitize_text_field', $data['extraDirectories']);
        $data['excludedDirectories'] = array_map('sanitize_text_field', $data['excludedDirectories']);

        // Cleanup existing plugins/themes and uploads
        $data['isCleanPluginsThemes'] = $this->jsBoolean($data['isCleanPluginsThemes']);
        $data['isCleanUploads']       = $this->jsBoolean($data['isCleanUploads']);
        $data['isBackupUploads']      = $this->jsBoolean($data['isBackupUploads']);

        // Database backup
        $data['isCreateDatabaseBackup'] = $this->jsBoolean($data['isCreateDatabaseBackup']);

        return $data;
    }

    protected function getDefaults(): array
    {
        return [
            'cloneId'                => '',
            'allTablesExcluded'      => false,
            'excludedTables'         => [],
            'includedTables'         => [],
            'nonSiteTables'          => [],
            'excludedDirectories'    => [],
            'extraDirectories'       => [],
            // cleanup existing plugins/themes and uploads
            'isCleanPluginsThemes'   => false,
            'isCleanUploads'         => false,
            'isBackupUploads'        => false,
            // database backup
            'isCreateDatabaseBackup' => false,
            'isBackupCreated'        => false,
        ];
    }

    /**
     * @param array|null $sanitizedData
     * @return array
     */
    private function setupInitialData(array $sanitizedData, bool $isSanitized = false): array
    {
        if (!$isSanitized) {
            $sanitizedData = $this->validateAndSanitizeData($sanitizedData);
        }

        $this->clearCacheFolder();

        // Lazy-instantiation to avoid process-lock checks conflicting with running processes.
        $services = WPStaging::getInstance()->getContainer();
        /** @var StagingSitePushDataDto */
        $this->jobDataDto = $services->get(StagingSitePushDataDto::class);
        /** @var StagingSitePush */
        $this->jobPush = $services->get($this->getJobClass());

        $this->populateJobDataDtoByCloneId($sanitizedData['cloneId']);

        $this->jobDataDto->hydrate($sanitizedData);
        $this->jobDataDto->setInit(true);
        $this->jobDataDto->setFinished(false);
        $this->jobDataDto->setStartTime(time());
        $this->jobDataDto->setStagingSiteUploads($this->directory->getRelativeUploadsDirectory());
        $this->jobDataDto->setJobType(StagingSetup::JOB_PUSH);
        $this->jobDataDto->setTmpPrefix(DatabaseImporter::TMP_DATABASE_PREFIX);

        $this->prepareStagingSiteDto();

        $this->jobDataDto->setId(substr(md5(mt_rand() . time()), 0, 12));

        $this->jobPush->getTransientCache()->startJob($this->jobDataDto->getId(), esc_html__('Staging Site Push in Progress', 'wp-staging'), JobTransientCache::JOB_TYPE_STAGING_PUSH, $this->queueId);

        $this->jobPush->setJobDataDto($this->jobDataDto);

        return $sanitizedData;
    }

    private function shouldPrepareBackup(array $sanitizedData): bool
    {
        $backupDatabase = !empty($sanitizedData['isCreateDatabaseBackup']);
        $backupUploads  = !empty($sanitizedData['isBackupUploads']) && !empty($sanitizedData['isCleanUploads']);
        $backupCreated  = !empty($sanitizedData['isBackupCreated']);

        return !$backupCreated && ($backupDatabase || $backupUploads);
    }

    /**
     * @param array $sanitizedData
     * @return array|\WP_Error
     */
    private function prepareBackupForPush(array $sanitizedData)
    {
        $backupDatabase = !empty($sanitizedData['isCreateDatabaseBackup']);
        $backupUploads  = !empty($sanitizedData['isBackupUploads']) && !empty($sanitizedData['isCleanUploads']);

        $this->deleteOldAutomatedBackups($backupDatabase, $backupUploads);

        return $this->prepareBackup->prepare([
            'isExportingDatabase' => $backupDatabase,
            'isExportingUploads'  => $backupUploads,
            'isAutomatedBackup'   => true,
            'storages'            => ['localStorage'],
            'name'                => $this->getBackupName($backupDatabase, $backupUploads),
            'pushPrepareData'     => $sanitizedData,
            'backupType'          => is_multisite() ? BackupMetadata::BACKUP_TYPE_MULTISITE : BackupMetadata::BACKUP_TYPE_SINGLE,
        ]);
    }

    private function deleteOldAutomatedBackups(bool $backupDatabase, bool $backupUploads)
    {
        if ($backupDatabase && $backupUploads) {
            $this->backupDeleter->deleteAllAutomatedPushBackups();
        } elseif ($backupDatabase) {
            $this->backupDeleter->deleteAllAutomatedDbOnlyBackups();
        } else {
            $this->backupDeleter->deleteAllAutomatedUploadsOnlyBackups();
        }
    }

    private function getBackupName(bool $backupDatabase, bool $backupUploads): string
    {
        if ($backupDatabase && $backupUploads) {
            return __('Database & Uploads Backup Before Push', 'wp-staging');
        }

        if ($backupDatabase) {
            return __('Database Backup Before Push', 'wp-staging');
        }

        return __('Uploads Backup Before Push', 'wp-staging');
    }

    /**
     * Returns the reference to the current Job, if any.
     *
     * @return StagingSitePush|null The current reference to the Staging Site Push Job, if any.
     */
    public function getJob()
    {
        return $this->jobPush;
    }

    /**
     * @return string
     */
    protected function getJobClass(): string
    {
        return StagingSitePush::class;
    }

    /**
     * Persists the current Job status.
     *
     * @return bool Whether the current Job status was persisted or not.
     */
    public function persist(): bool
    {
        if (!$this->jobPush instanceof StagingSitePush) {
            return false;
        }

        $this->jobPush->persist();

        return true;
    }

    protected function parseAndSanitizeTables(string $tables): array
    {
        $tables = $tables === '' ? [] : explode(ScanConst::DIRECTORIES_SEPARATOR, $tables);

        return array_map('sanitize_text_field', $tables);
    }

    protected function parseAndSanitizeDirectories(string $directories): array
    {
        $directories = $directories === '' ? [] : explode(ScanConst::DIRECTORIES_SEPARATOR, $directories);

        return array_map('sanitize_text_field', $directories);
    }

    protected function prepareStagingSiteDto()
    {
        $stagingSite = $this->jobDataDto->getStagingSite();
        $stagingSite->setStatus(StagingSiteDto::STATUS_UNFINISHED_BROKEN);
        $stagingSite->setDatetime(time());
        $stagingSite->setVersion(WPStaging::getVersion());
        $stagingSite->setOwnerId(get_current_user_id());

        $this->jobDataDto->setStagingSite($stagingSite);
    }

    protected function populateJobDataDtoByCloneId(string $cloneId)
    {
        /**
         * @var Sites $stagingSites
         */
        $stagingSites = WPStaging::make(Sites::class); // @phpstan-ignore-line
        $stagingSite  = $stagingSites->getStagingSiteDtoByCloneId($cloneId);
        $this->jobDataDto->setStagingSite($stagingSite);
        $this->jobDataDto->setCloneId($cloneId);
        $this->jobDataDto->setStagingSiteUrl($stagingSite->getUrl());
        $this->jobDataDto->setStagingSitePath($stagingSite->getPath());
        $this->jobDataDto->setDatabasePrefix($stagingSite->getPrefix());
        $this->jobDataDto->setIsExternalDatabase($stagingSite->getIsExternalDatabase());
    }
}
