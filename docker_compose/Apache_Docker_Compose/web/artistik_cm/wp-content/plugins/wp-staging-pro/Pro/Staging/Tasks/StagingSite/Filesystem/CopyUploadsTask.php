<?php

namespace WPStaging\Pro\Staging\Tasks\StagingSite\Filesystem;

use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Staging\Dto\Job\StagingSiteJobsDataDto;
use WPStaging\Staging\Service\FileCopier;
use WPStaging\Staging\Tasks\StagingSite\Filesystem\CopyUploadsTask as BaseCopyUploadsTask;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

/**
 * Pro version of CopyUploadsTask that adds cleanup functionality before copying.
 * When isCleanUploads option is enabled, this task will delete the staging
 * uploads directory before copying new uploads from production.
 */
class CopyUploadsTask extends BaseCopyUploadsTask
{
    /** @var Filesystem */
    protected $filesystem;

    /** @var Directory */
    protected $directory;

    /** @var StagingSiteJobsDataDto $jobDataDto */
    protected $jobDataDto; // @phpstan-ignore-line

    public function __construct(
        FileCopier $fileCopier,
        LoggerInterface $logger,
        Cache $cache,
        StepsDto $stepsDto,
        SeekableQueueInterface $taskQueue,
        Filesystem $filesystem,
        Directory $directory
    ) {
        parent::__construct($fileCopier, $logger, $cache, $stepsDto, $taskQueue);
        $this->filesystem = $filesystem;
        $this->directory  = $directory;
    }

    public function execute(): TaskResponseDto
    {
        // Early bail if the task is excluded
        if ($this->getIsExcluded()) {
            return $this->generateResponse(true);
        }

        if ($this->shouldCleanup()) {
            $cleanupComplete = $this->cleanupBeforeCopy();
            if (!$cleanupComplete) {
                $this->logger->info('Cleaning up uploads directory...');
                return $this->generateResponse(false);
            }
        }

        return parent::execute();
    }

    /**
     * Check if we should run cleanup before copying
     */
    private function shouldCleanup(): bool
    {
        // Don't cleanup if uploads are symlinked
        if ($this->jobDataDto->getIsUploadsSymlinked()) {
            return false;
        }

        return $this->jobDataDto->getIsCleanUploads()
            && !$this->jobDataDto->getIsUploadsCleanupDone();
    }

    /**
     * Clean up the uploads directory before copying
     * @return bool True if cleanup is complete, false if needs more iterations
     */
    private function cleanupBeforeCopy(): bool
    {
        $stagingPath = $this->jobDataDto->getStagingSitePath();
        if (empty($stagingPath)) {
            $this->jobDataDto->setIsUploadsCleanupDone(true);
            return true;
        }

        $uploadsPath = trailingslashit($stagingPath) . $this->directory->getRelativeUploadsDirectory();

        if (!is_dir($uploadsPath)) {
            $this->jobDataDto->setIsUploadsCleanupDone(true);
            return true;
        }

        $deleted = $this->filesystem
            ->setRecursive(true)
            ->setShouldStop(function () {
                return $this->isThreshold();
            })
            ->setExcludePaths(['wp-staging'])
            ->delete($uploadsPath);

        if ($deleted) {
            $this->logger->info('Uploads directory cleaned up successfully.');
            $this->jobDataDto->setIsUploadsCleanupDone(true);
            return true;
        }

        return false;
    }
}
