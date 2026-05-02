<?php

namespace WPStaging\Pro\Backup\Ajax\Extract;

use WPStaging\Backup\Entity\BackupMetadata;
use WPStaging\Backup\Job\JobExtractProvider;
use WPStaging\Backup\Utils\BackupPathResolver;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Facades\Sanitize;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Framework\Job\Ajax\PrepareJob;
use WPStaging\Framework\Job\Exception\ProcessLockedException;
use WPStaging\Framework\Job\JobTransientCache;
use WPStaging\Framework\Job\ProcessLock;
use WPStaging\Framework\Security\Auth;
use WPStaging\Pro\Backup\Dto\Job\JobExtractDataDto;
use WPStaging\Pro\Backup\Job\Jobs\JobExtract;
use WPStaging\Pro\License\Licensing;

/**
 * Prepares the extraction job by validating input and persisting JobExtractDataDto.
 */
class PrepareExtract extends PrepareJob
{
    /** @var JobExtractDataDto */
    private $jobDataDto;

    /** @var JobExtract */
    private $jobExtract;

    /** @var Licensing */
    private $licensing;

    /** @var BackupPathResolver */
    private $backupPathResolver;

    public function __construct(Filesystem $filesystem, Directory $directory, Auth $auth, ProcessLock $processLock, Licensing $licensing, BackupPathResolver $backupPathResolver)
    {
        parent::__construct($filesystem, $directory, $auth, $processLock);
        $this->licensing          = $licensing;
        $this->backupPathResolver = $backupPathResolver;
    }

    public function ajaxPrepare($data)
    {
        if (!$this->auth->isAuthenticatedRequest()) {
            wp_send_json_error(null, 401);
        }

        if (!$this->licensing->isValidOrExpiredLicenseKey()) {
            wp_send_json_error(__('You need a valid WP Staging Pro license to use the extract feature.', 'wp-staging'), 403);
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

        wp_send_json_success();
    }

    public function prepare($data = null)
    {
        // Lazy-instantiation to avoid process-lock checks conflicting with running processes.
        $container        = WPStaging::getInstance()->getContainer();
        $this->jobDataDto = $container->get(JobExtractDataDto::class);
        $this->jobExtract = $container->get(JobExtractProvider::class)->getJob();

        if (empty($data) && array_key_exists('wpstgExtractData', $_POST)) {
            $data = Sanitize::sanitizeArray($_POST['wpstgExtractData'], [
                'file'        => 'string',
                'files'       => 'arrayString',
                'directories' => 'arrayString',
            ]);
        }

        try {
            $sanitizedData = $this->setupInitialData($data);
        } catch (\Throwable $e) {
            return new \WP_Error('wpstg_extract_prepare_failed', $e->getMessage());
        }

        return $sanitizedData;
    }

    public function getJob()
    {
        return $this->jobExtract;
    }

    public function persist(): bool
    {
        if (!$this->jobExtract instanceof JobExtract) {
            return false;
        }

        $this->jobExtract->persist();

        return true;
    }

    public function validateAndSanitizeData($data): array
    {
        if (!is_array($data)) {
            throw new \UnexpectedValueException('Invalid request data.');
        }

        $expectedKeys = ['file', 'files', 'directories'];

        // Make sure data has no keys other than the expected ones.
        $data = array_intersect_key($data, array_flip($expectedKeys));

        if (!array_key_exists('file', $data)) {
            throw new \UnexpectedValueException("Invalid request. Missing 'file'.");
        }

        if (!array_key_exists('files', $data)) {
            $data['files'] = [];
        }

        if (!array_key_exists('directories', $data)) {
            $data['directories'] = [];
        }

        if (!is_array($data['files']) || !is_array($data['directories'])) {
            throw new \UnexpectedValueException('Invalid request. Files or directories are not arrays.');
        }

        if (empty($data['files']) && empty($data['directories'])) {
            throw new \UnexpectedValueException(__('Select at least one file to extract.', 'wp-staging'));
        }

        if (!$this->licensing->isAgencyOrDeveloperPlan()) {
            if (!empty($data['directories'])) {
                throw new \UnexpectedValueException(
                    __('Directory extraction is available with Developer and Agency plans. Please select a single file instead.', 'wp-staging')
                );
            }

            if (count($data['files']) > 1) {
                throw new \UnexpectedValueException(
                    __('Your plan allows extracting one file at a time. Upgrade to Developer or Agency plan for multi-file extraction.', 'wp-staging')
                );
            }
        }

        return $data;
    }

    private function setupInitialData($sanitizedData)
    {
        $sanitizedData = $this->validateAndSanitizeData($sanitizedData);
        $this->clearCacheFolder();
        $this->deleteSseCacheFiles();

        $file = (string)$sanitizedData['file'];
        if (empty($file)) {
            throw new \UnexpectedValueException(__('Backup file is missing.', 'wp-staging'));
        }

        $backupFile = $this->backupPathResolver->resolveBackupPath($file);
        if (empty($backupFile) || !file_exists($backupFile)) {
            throw new \UnexpectedValueException(__('Backup file not found.', 'wp-staging'));
        }

        $metadata = (new BackupMetadata())->hydrateByFilePath($backupFile);

        $offsets = $this->extractOffsets($sanitizedData['files']);
        if (empty($offsets)) {
            if (empty($sanitizedData['directories'])) {
                throw new \UnexpectedValueException(__('Select at least one file to extract.', 'wp-staging'));
            }
        }

        $this->jobDataDto->setFile($file);
        $this->jobDataDto->setOffsets($offsets);
        $this->jobDataDto->setDirectories($sanitizedData['directories']);
        $this->jobDataDto->setBackupMetadata($metadata);

        $this->jobDataDto->setInit(true);
        $this->jobDataDto->setFinished(false);
        $this->jobDataDto->setId(substr(md5(mt_rand() . time()), 0, 12));

        $this->jobExtract->setJobDataDto($this->jobDataDto);

        if (!$this->jobDataDto->getIsSyncRequest()) {
            $this->jobExtract->getTransientCache()->startJob(
                $this->jobDataDto->getId(),
                esc_html__('Extraction in Progress', 'wp-staging'),
                JobTransientCache::JOB_TYPE_EXTRACT,
                $this->queueId
            );
        }

        return $sanitizedData;
    }

    /**
     * @param mixed $files
     * @return int[]
     */
    private function extractOffsets($files): array
    {
        if (!is_array($files)) {
            return [];
        }

        $offsets = [];
        foreach ($files as $file) {
            if (is_array($file) && isset($file['offset'])) {
                $offsets[] = absint($file['offset']);
                continue;
            }

            if (is_numeric($file)) {
                $offsets[] = absint($file);
            }
        }

        $offsets = array_values(array_filter($offsets));

        return $offsets;
    }
}
