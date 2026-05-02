<?php

namespace WPStaging\Pro\Push\Tasks\Filesystem;

use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Framework\Filesystem\FilesystemScanner;
use WPStaging\Framework\Filesystem\FilesystemScannerDto;
use WPStaging\Framework\Filesystem\PartIdentifier;
use WPStaging\Framework\Filesystem\PathChecker;
use WPStaging\Framework\Job\Exception\DiskNotWritableException;
use WPStaging\Framework\Queue\FinishedQueueException;
use WPStaging\Pro\Push\Tasks\FileCopierTask;
use WPStaging\Pro\Push\Tasks\PushTask;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

/**
 * @todo: In Finalizing PR, re-use the existing push filters here
 */
class FilesystemScannerTask extends PushTask
{
    /** @var int */
    const STEP_SCAN_PLUGINS_DIRECTORY = 0;

    /** @var int */
    const STEP_SCAN_MU_PLUGINS_DIRECTORY = 1;

    /** @var int */
    const STEP_SCAN_THEMES_DIRECTORY = 2;

    /** @var int */
    const STEP_SCAN_UPLOADS_DIRECTORY = 3;

    /** @var int */
    const STEP_SCAN_OTHER_WP_CONTENT_DIRECTORIES = 4;

    /** @var int */
    const STEP_SCAN_EXTRA_DIRECTORIES = 5;

    /** @var string */
    const FILTER_IGNORE_FILE_EXTENSION = 'wpstg.push.files.ignore.file_extension';

    /** @var string */
    const FILTER_IGNORE_FILE_BIGGER_THAN = 'wpstg.push.files.ignore.file_bigger_than';

    /** @var string */
    const FILTER_EXCLUDE_DIRECTORIES = 'wpstg.push.exclude.directories';

    /**
     * 7 steps, one for scanning each identifier, 6th one for extra directories and last one for deep non-scanned directories
     * @var int
     */
    const TOTAL_STEPS = 7;

    /** @var Directory */
    protected $directory;

    /** @var Filesystem */
    protected $filesystem;

    /** @var FilesystemScanner */
    protected $filesystemScanner;

    /** @var PathChecker */
    protected $pathChecker;

    /** @var array */
    protected $ignoreFileExtensions = [];

    /** @var int */
    protected $ignoreFileBiggerThan = 0;

    /** @var array */
    protected $ignoreFileExtensionFilesBiggerThan = [];

    /**
     * @param LoggerInterface $logger
     * @param Cache $cache
     * @param StepsDto $stepsDto
     * @param SeekableQueueInterface $taskQueue
     * @param Directory $directory
     * @param Filesystem $filesystem
     * @param FilesystemScanner $filesystemScanner
     * @param PathChecker $pathChecker
     */
    public function __construct(
        LoggerInterface $logger,
        Cache $cache,
        StepsDto $stepsDto,
        SeekableQueueInterface $taskQueue,
        Directory $directory,
        Filesystem $filesystem,
        FilesystemScanner $filesystemScanner,
        PathChecker $pathChecker
    ) {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);

        $this->directory         = $directory;
        $this->filesystem        = $filesystem;
        $this->filesystemScanner = $filesystemScanner;
        $this->pathChecker       = $pathChecker;
    }

    /**
     * @return string
     */
    public static function getTaskName(): string
    {
        return 'push_filesystem_scan';
    }

    /**
     * @return string
     */
    public static function getTaskTitle(): string
    {
        return 'Discovering Files';
    }

    /**
     * @inheritDoc
     * @throws DiskNotWritableException
     */
    public function execute(): TaskResponseDto
    {
        $this->setupFilters();
        $this->setupFilesystemScanner();

        if ($this->stepsDto->getCurrent() === self::STEP_SCAN_PLUGINS_DIRECTORY) {
            return $this->scanPluginsDirectories();
        }

        if ($this->stepsDto->getCurrent() === self::STEP_SCAN_MU_PLUGINS_DIRECTORY) {
            return $this->scanMuPluginsDirectory();
        }

        if ($this->stepsDto->getCurrent() === self::STEP_SCAN_THEMES_DIRECTORY) {
            return $this->scanThemesDirectory();
        }

        if ($this->stepsDto->getCurrent() === self::STEP_SCAN_UPLOADS_DIRECTORY) {
            return $this->scanUploadsDirectory();
        }

        if ($this->stepsDto->getCurrent() === self::STEP_SCAN_OTHER_WP_CONTENT_DIRECTORIES) {
            return $this->scanWpContentDirectory();
        }

        if ($this->stepsDto->getCurrent() === self::STEP_SCAN_EXTRA_DIRECTORIES) {
            return $this->scanExtraDirectories();
        }

        while (!$this->isThreshold() && !$this->stepsDto->isFinished()) {
            try {
                $this->filesystemScanner->processQueue();
            } catch (FinishedQueueException $e) {
                $this->stepsDto->finish();
            }

            $this->updateJobDataDto();
        }

        if ($this->stepsDto->isFinished()) {
            $this->stepsDto->setManualPercentage(100);
            $this->logger->info(sprintf('Finished discovering Files. (%d files)', $this->jobDataDto->getDiscoveredFiles()));
        } else {
            $this->jobDataDto->setDiscoveringFilesRequests($this->jobDataDto->getDiscoveringFilesRequests() + 1);

            // The manual percentage increments 30% per request, until it hits 90%, point of which it increments 1%
            if ($this->jobDataDto->getDiscoveringFilesRequests() <= 3) {
                // 30%, 60%, 90%...
                $manualPercentage = $this->jobDataDto->getDiscoveringFilesRequests() * 30;
            } elseif ($this->jobDataDto->getDiscoveringFilesRequests() >= 4 && $this->jobDataDto->getDiscoveringFilesRequests() <= 14) {
                // 91%, 92%, 93%...
                $manualPercentage = 90;
                $manualPercentage += $this->jobDataDto->getDiscoveringFilesRequests() - 3;
            } else {
                // 99%
                $manualPercentage = 99;
            }

            $this->stepsDto->setManualPercentage(min($manualPercentage, 100));
            $this->logger->info(sprintf('Discovering Files (%d files)', $this->jobDataDto->getDiscoveredFiles()));
        }

        return $this->generateResponse(false);
    }

    /**
     * @return void
     */
    protected function setupFilters()
    {
        /**
         * Allow user to exclude certain file extensions from being copied.
         */
        $this->ignoreFileExtensions = array_merge($this->jobDataDto->getExcludeExtensionRules(), [
            'log',
            'wpstg', // WP STAGING backup files
            'svn',
            'tmp',
            'git',
        ]);

        $this->ignoreFileExtensions = (array)apply_filters(self::FILTER_IGNORE_FILE_EXTENSION, $this->ignoreFileExtensions);

        $excludeSizeGreaterThanInMb = $this->jobDataDto->getExcludeSizeGreaterThan();

        /**
         * Allow user to exclude files larger than given size from being copied.
         */
        $this->ignoreFileBiggerThan = (int)apply_filters(self::FILTER_IGNORE_FILE_BIGGER_THAN, $excludeSizeGreaterThanInMb * MB_IN_BYTES);

        // Allows us to use isset for performance
        $this->ignoreFileExtensions = array_flip($this->ignoreFileExtensions);
    }

    /**
     * @return void
     */
    protected function setupFilesystemScanner()
    {
        if (empty($this->stepsDto->getTotal())) {
            $excludedDirs = array_map(function ($path) {
                return $this->filesystem->normalizePath($path, true);
            }, $this->getExcludedDirectories());

            $this->jobDataDto->setExcludedDirectoriesForScanner($excludedDirs);

            $this->stepsDto->setTotal(self::TOTAL_STEPS);
            $this->taskQueue->seek(0);
        }

        $this->filesystemScanner->setFilters($this->ignoreFileBiggerThan, $this->ignoreFileExtensions, $this->ignoreFileExtensionFilesBiggerThan);
        $this->filesystemScanner->setNameExcludeRules(
            $this->jobDataDto->getExcludeFolderRules(),
            $this->jobDataDto->getExcludeFileRules()
        );
        $this->filesystemScanner->setLogTitle(static::getTaskTitle());
        $this->filesystemScanner->setQueueCacheName(FileCopierTask::getTaskName());
        $this->filesystemScanner->inject($this->logger, $this->taskQueue, $this->getScannerDto());
        $this->filesystemScanner->setRootPath($this->getRootPath());
        $this->filesystemScanner->setContentPath(trailingslashit($this->getRootPath()) . 'wp-content');
    }

    protected function scanPluginsDirectories(): TaskResponseDto
    {
        $dirToScan = trailingslashit($this->jobDataDto->getStagingSitePath()) . 'wp-content/plugins';
        if ($this->isExcluded($dirToScan)) {
            $this->logger->warning('Skipping scanning of plugins directory because it is excluded.');
            $this->jobDataDto->setIsPluginsExcluded(true);
            return $this->generateResponse();
        }

        $excludeRules = [
            '/wp-content/plugins/wp-staging*',
        ];

        $this->preScanPath($dirToScan, PartIdentifier::PLUGIN_PART_IDENTIFIER, $excludeRules);

        return $this->generateResponse();
    }

    protected function scanMuPluginsDirectory(): TaskResponseDto
    {
        // Early bail: mu-plugins directory doesn't exist
        $dirToScan = trailingslashit($this->jobDataDto->getStagingSitePath()) . 'wp-content/mu-plugins';
        if (!is_dir($dirToScan)) {
            $this->jobDataDto->setIsMuPluginsExcluded(true);
            return $this->generateResponse();
        }

        if ($this->isExcluded($dirToScan)) {
            $this->logger->warning('Skipping scanning of mu-plugins directory because it is excluded.');
            $this->jobDataDto->setIsMuPluginsExcluded(true);
            return $this->generateResponse();
        }

        $excludeRules = [];

        $this->preScanPath($dirToScan, PartIdentifier::MU_PLUGIN_PART_IDENTIFIER, $excludeRules);

        return $this->generateResponse();
    }

    protected function scanThemesDirectory(): TaskResponseDto
    {
        // Early bail: themes directory doesn't exist
        $dirToScan = trailingslashit($this->jobDataDto->getStagingSitePath()) . 'wp-content/themes';
        if (!is_dir($dirToScan)) {
            $this->jobDataDto->setIsThemesExcluded(true);
            return $this->generateResponse();
        }

        if ($this->isExcluded($dirToScan)) {
            $this->logger->warning('Skipping scanning of themes directory because it is excluded.');
            $this->jobDataDto->setIsThemesExcluded(true);
            return $this->generateResponse();
        }

        $excludeRules = [];

        $this->preScanPath($dirToScan, PartIdentifier::THEME_PART_IDENTIFIER, $excludeRules);

        return $this->generateResponse();
    }

    protected function scanUploadsDirectory(): TaskResponseDto
    {
        $dirToScan = trailingslashit($this->jobDataDto->getStagingSitePath()) . 'wp-content/uploads';
        // Early bail: Uploads directory doesn't exist
        if (!is_dir($dirToScan)) {
            $this->jobDataDto->setIsUploadsExcluded(true);
            return $this->generateResponse();
        }

        if ($this->isExcluded($dirToScan)) {
            $this->logger->warning('Skipping scanning of uploads directory because it is excluded.');
            $this->jobDataDto->setIsUploadsExcluded(true);
            return $this->generateResponse();
        }

        $excludeRules = [];

        $this->preScanPath($dirToScan, PartIdentifier::UPLOAD_PART_IDENTIFIER, $excludeRules);

        return $this->generateResponse();
    }

    /**
     * Scan wp-content directory (wp-content/) but doesn't scan plugins,mu-plugins,themes,uploads folders.
     */
    protected function scanWpContentDirectory(): TaskResponseDto
    {
        $dirToScan = trailingslashit($this->jobDataDto->getStagingSitePath()) . 'wp-content';
        if ($this->isExcluded($dirToScan)) {
            $this->logger->warning('Skipping scanning of wp-content directory because it is excluded.');
            $this->jobDataDto->setIsWpContentExcluded(true);
            return $this->generateResponse();
        }

        $excludeRules = [
            $dirToScan . '/plugins',
            $dirToScan . '/mu-plugins',
            $dirToScan . '/themes',
            $dirToScan . '/uploads',
        ];

        $this->preScanPath($dirToScan, PartIdentifier::WP_CONTENT_PART_IDENTIFIER, $excludeRules);

        return $this->generateResponse();
    }

    /**
     * Scan for extra directories.
     */
    protected function scanExtraDirectories(): TaskResponseDto
    {
        $excludeRules = [];

        $this->filesystemScanner->setCurrentPathScanning(PartIdentifier::WP_ROOT_PART_IDENTIFIER);
        $this->filesystemScanner->setupFilesystemQueue();
        $this->filesystemScanner->setRootPath($this->getRootPath());
        $this->filesystemScanner->setExcludeRules($excludeRules);

        $isExtraDirectoriesExcluded = true;
        foreach ($this->jobDataDto->getExtraDirectories() as $extraDirectory) {
            if ($this->isExcluded($extraDirectory)) {
                continue;
            }

            $isExtraDirectoriesExcluded = false;
            $this->filesystemScanner->preScanPath($extraDirectory);
        }

        $this->filesystemScanner->unlockQueue();
        $this->updateJobDataDto();
        if ($isExtraDirectoriesExcluded) {
            $this->logger->info('No extra directories to scan.');
        }

        return $this->generateResponse();
    }

    protected function getRootPath(): string
    {
        return $this->jobDataDto->getStagingSitePath();
    }

    /**
     * @return array
     */
    protected function getExcludedDirectories(): array
    {
        $excludedDirs = [];

        $stagingWpContentDirectory = trailingslashit($this->jobDataDto->getStagingSitePath()) . 'wp-content/';
        $stagingUploadsDirectory   = $stagingWpContentDirectory . 'uploads/';

        $excludedDirs[] = $stagingUploadsDirectory . 'wp-staging';
        $excludedDirs[] = $stagingWpContentDirectory . 'wp-staging';
        $excludedDirs[] = $stagingWpContentDirectory . 'cache';
        // @see BackupUploadsDir::BACKUP_UPLOADS_DIR_POSTFIX
        $excludedDirs[] = $stagingWpContentDirectory . 'uploads.wpstg_backup';

        /**
         * @see https://wordpress.org/plugins/all-in-one-wp-migration/
         *      This folder contains backups generated by All In One WP Migration.
         */
        $excludedDirs[] = $stagingWpContentDirectory . 'ai1wm-backups';

        /**
         * @see https://wordpress.org/plugins/robin-image-optimizer/
         *      This folder contains a duplicate of the uploads folder, for optimized images.
         *      It can be manually re-generated from the existing media library later.
         */
        $excludedDirs[] = $stagingUploadsDirectory . 'wio_backup';

        /**
         * Allow user to filter the excluded directories in a site backup.
         *
         * @param array $excludedDirectories
         *
         * @return array An array of directories to exclude.
         */
        $excludedDirs = (array)apply_filters(self::FILTER_EXCLUDE_DIRECTORIES, $excludedDirs);

        return $excludedDirs;
    }

    protected function getScannerDto(): FilesystemScannerDto
    {
        $scannerDto = new FilesystemScannerDto();

        $scannerDto->setExcludedDirectories($this->jobDataDto->getExcludedDirectoriesForScanner() ?? []);
        $scannerDto->setDiscoveredFiles($this->jobDataDto->getDiscoveredFiles() ?? 0);
        $scannerDto->setDiscoveredFilesArray($this->jobDataDto->getDiscoveredFilesIdentifiers() ?? []);
        $scannerDto->setFilesystemSize($this->jobDataDto->getFilesystemSize() ?? 0);
        $scannerDto->setTotalDirectories($this->jobDataDto->getTotalDirectories() ?? 0);

        return $scannerDto;
    }

    /**
     * @return void
     */
    protected function updateJobDataDto()
    {
        $scannerDto = $this->filesystemScanner->getFilesystemScannerDto();

        $this->jobDataDto->setDiscoveredFiles($scannerDto->getDiscoveredFiles());
        $this->jobDataDto->setDiscoveredFilesIdentifiers($scannerDto->getDiscoveredFilesArray());
        $this->jobDataDto->setFilesystemSize($scannerDto->getFilesystemSize());
        $this->jobDataDto->setTotalDirectories($scannerDto->getTotalDirectories());
    }

    /**
     * Pre scan path
     * This is common method for pre scanning path, but cannot be used for scanning themes folders i.e. because there can be multiple themes folders
     * @param string $dirToScan
     * @param string $partIdentifier
     * @param array $excludeRules
     * @param bool $processLinks
     * @return void
     */
    protected function preScanPath(string $dirToScan, string $partIdentifier, array $excludeRules = [], bool $processLinks = false)
    {
        $this->filesystemScanner->setCurrentPathScanning($partIdentifier);
        $this->filesystemScanner->setupFilesystemQueue();
        $this->filesystemScanner->setRootPath($this->getRootPath());
        $this->filesystemScanner->setContentPath(trailingslashit($this->getRootPath()) . 'wp-content');
        $this->filesystemScanner->setExcludeRules($excludeRules);
        $this->filesystemScanner->preScanPath($dirToScan, $processLinks);
        $this->filesystemScanner->unlockQueue();
        $this->updateJobDataDto();
    }

    protected function isExcluded(string $directory): bool
    {
        return $this->pathChecker->isPathInPathsList($directory, $this->jobDataDto->getExcludedDirectories());
    }
}
