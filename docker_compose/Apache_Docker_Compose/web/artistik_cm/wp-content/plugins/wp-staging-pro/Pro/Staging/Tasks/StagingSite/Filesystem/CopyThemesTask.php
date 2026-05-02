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
use WPStaging\Staging\Tasks\StagingSite\Filesystem\CopyThemesTask as BaseCopyThemesTask;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

/**
 * Pro version of CopyThemesTask that adds cleanup functionality before copying.
 * When isCleanPluginsThemes option is enabled, this task will delete the staging
 * themes directory before copying new themes from production.
 */
class CopyThemesTask extends BaseCopyThemesTask
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
                $this->logger->info('Cleaning up themes directory...');
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
            && !$this->jobDataDto->getIsThemesCleanupDone();
    }

    /**
     * Clean up the themes directory before copying
     * @return bool True if cleanup is complete, false if needs more iterations
     */
    private function cleanupBeforeCopy(): bool
    {
        $stagingPath = $this->jobDataDto->getStagingSitePath();
        if (empty($stagingPath)) {
            $this->jobDataDto->setIsThemesCleanupDone(true);
            return true;
        }

        $allDeleted = true;
        foreach ($this->directory->getAllThemesDirectories() as $themesDirectory) {
            $relativeThemesPath = str_replace($this->directory->getAbsPath(), '', $themesDirectory);
            $stagingThemesPath  = trailingslashit($stagingPath) . $relativeThemesPath;

            if (!is_dir($stagingThemesPath)) {
                continue;
            }

            $deleted = $this->filesystem
                ->setRecursive(true)
                ->setShouldStop(function () {
                    return $this->isThreshold();
                })
                ->delete($stagingThemesPath);

            if (!$deleted) {
                $allDeleted = false;
            }
        }

        if ($allDeleted) {
            $this->logger->info('Themes directories cleaned up successfully.');
            $this->jobDataDto->setIsThemesCleanupDone(true);
            return true;
        }

        return false;
    }
}
