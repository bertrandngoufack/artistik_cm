<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobBackup;

use DirectoryIterator;
use WPStaging\Core\WPStaging;
use WPStaging\Staging\Sites;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Framework\Filesystem\PartIdentifier;
use WPStaging\Backup\Task\Tasks\JobBackup\FilesystemScannerTask as BasicFilesystemScannerTask;

class FilesystemScannerTask extends BasicFilesystemScannerTask
{
    /**
     * @return array
     */
    protected function getExcludedDirectories(): array
    {
        $excludedDirs = parent::getExcludedDirectories();

        if (!$this->isBaseNetworkSite()) {
            return $excludedDirs;
        }

        $refresh = true;

        if ($this->jobDataDto->getIsNetworkSiteBackup()) {
            $excludedDirs[] = $this->directory->getUploadsDirectory($refresh) . 'sites';
            return $excludedDirs;
        }

        // Exclude all wp staging uploads directories from subsites
        $sitesDirectory = $this->directory->getUploadsDirectory($refresh) . 'sites';

        if (is_dir($sitesDirectory) === false) {
            return $excludedDirs;
        }

        $uploadsIt = new DirectoryIterator($sitesDirectory);

        foreach ($uploadsIt as $uploadItem) {
            // Early bail: We don't touch links and we also skip dots
            if ($uploadItem->isLink() || $uploadItem->isDot()) {
                continue;
            }

            if ($uploadItem->isFile()) {
                continue;
            }

            if ($uploadItem->isDir()) {
                $excludedDirs[] = trailingslashit($uploadItem->getPathname()) . 'wp-staging';
            }
        }

        return $excludedDirs;
    }

    /**
     * @return string
     */
    protected function getUploadsDirectory(): string
    {
        if ($this->jobDataDto->getIsNetworkSiteBackup()) {
            switch_to_blog($this->jobDataDto->getSubsiteBlogId());
            $uploadsDir = $this->directory->getUploadsDirectory($refresh = true);
            restore_current_blog();

            return $uploadsDir;
        }

        return $this->directory->getMainSiteUploadsDirectory();
    }

    /**
     * @return bool
     */
    protected function isBaseNetworkSite(): bool
    {
        if (!is_multisite()) {
            return false;
        }

        $blogId = get_current_blog_id();
        return $blogId === 1 || $blogId === 0;
    }

    /**
     * Scan WP root directory(ABSPATH) but doesn't scan sub folders.
     *
     * @return TaskResponseDto
     */
    protected function scanWpRootDirectory(): TaskResponseDto
    {
        if (!$this->jobDataDto->getIsExportingOtherWpRootFiles()) {
            return $this->generateResponse();
        }

        /** @var Sites */
        $stagingSites     = WPStaging::make(Sites::class);
        $stagingSitesDirs = $stagingSites->getStagingDirectories();

        $dirsToSkip = $this->directory->getWpDefaultRootDirectories();
        $dirsToSkip = array_merge($dirsToSkip, $stagingSitesDirs);
        $dirsToSkip = array_unique(array_merge($dirsToSkip, $this->jobDataDto->getBackupExcludedDirectories()));
        $dirsToSkip = array_map(function ($path) {
            return rtrim($path, "/");
        }, $dirsToSkip);

        $dirToScan  = $this->directory->getAbsPath();

        /**
         * Files are excluded because:
         *  1. @see https://github.com/wp-staging/wp-staging-pro/pull/2987#issuecomment-1876554675
         *  2. We are using directories navigation and we have excluded files @see https://github.com/wp-staging/wp-staging-pro/pull/2987#issuecomment-1913743495
         *  3. @see https://wpstaging.slack.com/archives/C014UPMLNUX/p1734950210190799?thread_ts=1734870771.022599&cid=C014UPMLNUX
         */
        $this->filesystemScanner->setOnlyDirectories();
        $this->preScanPath($dirToScan, PartIdentifier::OTHER_WP_ROOT_PART_IDENTIFIER, $dirsToSkip);

        return $this->generateResponse();
    }
}
