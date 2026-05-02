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
use WPStaging\Staging\Tasks\StagingSite\Filesystem\CopyPluginsTask as BaseCopyPluginsTask;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

/**
 * Pro version of CopyPluginsTask that adds cleanup functionality before copying.
 * When isCleanPluginsThemes option is enabled, this task will delete the staging
 * plugins directory before copying new plugins from production.
 */
class CopyPluginsTask extends BaseCopyPluginsTask
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
        if ($this->shouldCleanup()) {
            $cleanupComplete = $this->cleanupBeforeCopy();
            if (!$cleanupComplete) {
                $this->logger->info('Cleaning up plugins directory...');
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
        return $this->jobDataDto->getIsCleanPluginsThemes()
            && !$this->jobDataDto->getIsPluginsCleanupDone();
    }

    /**
     * Clean up the plugins directory before copying
     * @return bool True if cleanup is complete, false if needs more iterations
     */
    private function cleanupBeforeCopy(): bool
    {
        $stagingPath = $this->jobDataDto->getStagingSitePath();
        if (empty($stagingPath)) {
            $this->jobDataDto->setIsPluginsCleanupDone(true);
            return true;
        }

        $pluginsPath = trailingslashit($stagingPath) . $this->directory->getRelativePluginsDirectory();

        if (!is_dir($pluginsPath)) {
            $this->jobDataDto->setIsPluginsCleanupDone(true);
            return true;
        }

        $deleted = $this->filesystem
            ->setRecursive(true)
            ->setShouldStop(function () {
                return $this->isThreshold();
            })
            ->setExcludePaths(['wp-staging*'])
            ->delete($pluginsPath);

        if ($deleted) {
            $this->logger->info('Plugins directory cleaned up successfully.');
            $this->jobDataDto->setIsPluginsCleanupDone(true);
            return true;
        }

        return false;
    }
}
