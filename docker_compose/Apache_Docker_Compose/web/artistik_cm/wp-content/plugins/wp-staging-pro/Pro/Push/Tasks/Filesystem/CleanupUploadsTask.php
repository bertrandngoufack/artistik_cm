<?php

namespace WPStaging\Pro\Push\Tasks\Filesystem;

use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Framework\Job\Task\AbstractTask;
use WPStaging\Pro\Push\Dto\StagingSitePushDataDto;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

/**
 * Dedicated task to cleanup production uploads directory before push.
 */
class CleanupUploadsTask extends AbstractTask
{
    /** @var Filesystem */
    protected $filesystem;

    /** @var Directory */
    protected $directory;

    /** @var StagingSitePushDataDto $jobDataDto */
    protected $jobDataDto; // @phpstan-ignore-line

    public function __construct(
        LoggerInterface $logger,
        Cache $cache,
        StepsDto $stepsDto,
        SeekableQueueInterface $taskQueue,
        Filesystem $filesystem,
        Directory $directory
    ) {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
        $this->filesystem = $filesystem;
        $this->directory  = $directory;
    }

    public static function getTaskName(): string
    {
        return 'push_cleanup_uploads';
    }

    public static function getTaskTitle(): string
    {
        return 'Cleaning Production Uploads Directory';
    }

    public function execute(): TaskResponseDto
    {
        // If option not enabled or cleanup already done, skip
        if (!$this->jobDataDto->getIsCleanUploads() || $this->jobDataDto->getIsUploadsCleanupDone()) {
            return $this->generateResponse(true);
        }

        $uploadsPath = $this->directory->getUploadsDirectory();

        if (!is_dir($uploadsPath)) {
            $this->jobDataDto->setIsUploadsCleanupDone(true);
            return $this->generateResponse(true);
        }

        $deleted = $this->filesystem
            ->setRecursive(true)
            ->setShouldStop(function () {
                return $this->isThreshold();
            })
            ->setExcludePaths(['wp-staging'])
            ->delete($uploadsPath);

        if ($deleted) {
            $this->logger->info('Production uploads directory cleaned up successfully.');
            $this->jobDataDto->setIsUploadsCleanupDone(true);
            return $this->generateResponse(true);
        }

        // Not finished yet, continue later
        $this->logger->info('Cleaning up production uploads directory...');
        return $this->generateResponse(false);
    }
}
