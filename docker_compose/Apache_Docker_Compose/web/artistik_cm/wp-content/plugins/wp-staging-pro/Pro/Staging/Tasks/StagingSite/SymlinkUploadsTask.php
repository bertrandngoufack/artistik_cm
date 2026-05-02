<?php

namespace WPStaging\Pro\Staging\Tasks\StagingSite;

use WPStaging\Framework\Adapter\Database;
use WPStaging\Framework\Filesystem\WpUploadsFolderSymlinker;
use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Framework\Utils\Urls;
use WPStaging\Staging\Interfaces\AdvanceStagingOptionsInterface;
use WPStaging\Staging\Interfaces\StagingOperationDtoInterface;
use WPStaging\Staging\Interfaces\StagingSiteDtoInterface;
use WPStaging\Staging\Tasks\StagingTask;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

/**
 * Creates a symlink from the staging site's uploads folder to the production site's uploads folder.
 * This allows the staging site to use the same media files as production without copying them.
 */
class SymlinkUploadsTask extends StagingTask
{
    /** @var AdvanceStagingOptionsInterface|StagingSiteDtoInterface|StagingOperationDtoInterface $jobDataDto */
    protected $jobDataDto; // @phpstan-ignore-line

    /** @var WpUploadsFolderSymlinker */
    protected $symlinker;

    /**
     * @return string
     */
    public static function getTaskName(): string
    {
        return 'staging_symlink_uploads';
    }

    /**
     * @return string
     */
    public static function getTaskTitle(): string
    {
        return 'Creating Uploads Symlink';
    }

    public function __construct(LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue, Urls $urls, Database $database, WpUploadsFolderSymlinker $symlinker)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue, $urls, $database);
        $this->symlinker = $symlinker;
    }

    /**
     * @return TaskResponseDto
     */
    public function execute(): TaskResponseDto
    {
        if (!$this->jobDataDto->getIsUploadsSymlinked()) {
            $this->logger->info('Skipping uploads symlink (option not enabled)');
            return $this->generateResponse();
        }

        $stagingSitePath = $this->jobDataDto->getStagingSitePath();
        if (empty($stagingSitePath)) {
            $this->logger->warning('Cannot create uploads symlink: staging site path is empty');
            return $this->generateResponse();
        }

        $this->logger->info('Creating symlink for uploads folder...');

        $this->symlinker->setStagingPath($stagingSitePath);
        if (!$this->symlinker->trySymlink()) {
            $error = $this->symlinker->getError();
            if (!empty($error)) {
                $this->logger->warning(sprintf('Could not create uploads symlink: %s', $error));
            } else {
                $this->logger->warning('Could not create uploads symlink');
            }

            return $this->generateResponse();
        }

        $this->logger->info('Uploads folder successfully symlinked to production');

        return $this->generateResponse();
    }
}
