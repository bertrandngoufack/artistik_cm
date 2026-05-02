<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobExtract;

use WPStaging\Pro\Backup\Task\ExtractTask;
use WPStaging\Backup\Entity\BackupMetadata;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Pro\License\Licensing;
use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

/**
 * Validates requirements before starting extraction.
 */
class ExtractRequirementsCheckTask extends ExtractTask
{
    /** @var Directory */
    private $directory;

    /** @var Licensing */
    private $licensing;

    public function __construct(Directory $directory, LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue, Licensing $licensing)
    {
        $this->directory = $directory;
        $this->licensing = $licensing;
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
    }

    public static function getTaskName()
    {
        return 'backup_extract_requirements';
    }

    public static function getTaskTitle()
    {
        return 'Checking Requirements';
    }

    public function execute()
    {
        if (!$this->stepsDto->getTotal()) {
            $this->stepsDto->setTotal(1);
        }

        $this->logger->info('↳ Initializing extraction...');

        if (!$this->licensing->isValidOrExpiredLicenseKey()) {
            $this->logger->warning(__('You need a valid WP Staging Pro license to use the extract feature.', 'wp-staging'));
            $this->jobDataDto->setFinished(true);
            return $this->generateResponse(false);
        }

        if (!$this->licensing->isAgencyOrDeveloperPlan()) {
            if (!empty($this->jobDataDto->getDirectories())) {
                $this->logger->warning(__('Directory extraction requires Developer or Agency plan.', 'wp-staging'));
                $this->jobDataDto->setFinished(true);
                return $this->generateResponse(false);
            }

            if (count($this->jobDataDto->getOffsets()) > 1) {
                $this->logger->warning(__('Your plan allows extracting one file at a time.', 'wp-staging'));
                $this->jobDataDto->setFinished(true);
                return $this->generateResponse(false);
            }
        }

        $metadata = $this->jobDataDto->getBackupMetadata();
        if (!$metadata instanceof BackupMetadata) {
            $this->logger->critical(__('Missing backup metadata for extraction.', 'wp-staging'));
            return $this->generateResponse(false);
        }

        if (empty($this->jobDataDto->getOffsets()) && empty($this->jobDataDto->getDirectories())) {
            $this->logger->warning(__('No files selected for extraction.', 'wp-staging'));
            return $this->generateResponse(false);
        }

        $extractRoot = trailingslashit($this->directory->getPluginWpContentDirectory()) . 'extract/' . $this->jobDataDto->getId() . '/';
        if (!wp_mkdir_p($extractRoot)) {
            $this->logger->critical(__('Destination folder is not writable.', 'wp-staging'));
            return $this->generateResponse(false);
        }

        $this->logger->info(__('Extraction requirements OK.', 'wp-staging'));

        return $this->generateResponse(true);
    }
}
