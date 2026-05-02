<?php

namespace WPStaging\Pro\Push\Service;

use WPStaging\Staging\Service\FileCopier as BaseFileCopier;

/**
 * This class extends the base FileCopier to handle file copying specifically for push operations.
 */
class FileCopier extends BaseFileCopier
{
    /** @var bool */
    protected $isTmpPath = false;

    public function setIsTmpPath(bool $isTmpPath)
    {
        $this->isTmpPath = $isTmpPath;
    }

    protected function maybePrependSitePath(string $filePath): string
    {
        return $this->stagingSitePath . $filePath;
    }

    /**
     * Gets destination file and checks if the directory exists, if it does not attempts to create it.
     * If creating destination directory fails, it will throw exception.
     * @param string $filePath
     * @param string $indexPath
     * @return string
     * @throws \RuntimeException
     */
    protected function getDestinationPath(string $filePath, string $indexPath): string
    {
        if (empty($indexPath)) {
            $sourcePath = $filePath;
        } else {
            $sourcePath = $indexPath;
        }

        $sourcePath = $this->filesystem->normalizePath($sourcePath);
        if ($this->isWpContentOutsideAbspath && $this->isWpContent) {
            $relativePath    = $this->strings->replaceStartWith($this->stagingSitePath . 'wp-content/', '', $sourcePath);
            $destinationPath = $this->wpContentDir . $relativePath;
        } else {
            $relativePath    = $this->strings->replaceStartWith($this->stagingSitePath, '', $sourcePath);
            $destinationPath = $this->absPath . $relativePath;
        }

        if ($this->isTmpPath) {
            $destinationPath = $this->filesystem->tmpDestinationPath($destinationPath);
        }

        $destinationDirectory = dirname($destinationPath);
        // If directory already exists, return the destination path
        if (is_dir($destinationDirectory)) {
            return $this->filesystem->normalizePath($destinationPath);
        }

        // If directory does not exist, create it
        if ($this->filesystem->mkdir($destinationDirectory)) {
            return $this->filesystem->normalizePath($destinationPath);
        }

        // If directory still does not exist, throw an exception
        if (!is_dir($destinationDirectory)) {
            throw new \RuntimeException("Can not create directory {$destinationDirectory}." . $this->filesystem->getLogs()[0]);
        }

        return $this->filesystem->normalizePath($destinationPath);
    }
}
