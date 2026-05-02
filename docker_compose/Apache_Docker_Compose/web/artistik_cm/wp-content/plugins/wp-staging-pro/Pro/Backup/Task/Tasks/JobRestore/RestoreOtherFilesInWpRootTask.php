<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobRestore;

use WPStaging\Framework\Filesystem\PartIdentifier;
use WPStaging\Framework\Filesystem\PathIdentifier;
use WPStaging\Backup\Task\FileRestoreTask;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Adapter\Directory;

class RestoreOtherFilesInWpRootTask extends FileRestoreTask
{
    public static function getTaskName(): string
    {
        return 'backup_restore_wp_root';
    }

    public static function getTaskTitle(): string
    {
        return 'Restoring Other Files in WP root';
    }

    protected function isSkipped(): bool
    {
        return $this->isBackupPartSkipped(PartIdentifier::WP_ROOT_PART_IDENTIFIER);
    }

    /**
     * @return array
     */
    protected function getParts(): array
    {
        return $this->jobDataDto->getBackupMetadata()->getMultipartMetadata()->getOtherWpRootParts();
    }

    /**
     * The most critical step because it has to run in one request
     *
     * @return void
     */
    protected function buildQueue()
    {
        /** @var Directory */
        $dirAdapter = WPStaging::make(Directory::class);
        if (is_writeable($dirAdapter->getAbsPath())) {
            $this->moveBackupFilesToDestination();
        } else {
            $this->logger->info($this->getTaskTitle() . ': Skipped - the root dir is not writable.');
        }
    }

    /**
     * @return void
     */
    protected function moveBackupFilesToDestination()
    {
        try {
            $otherFilesToRestore = $this->getOtherRootFilesToRestore();
        } catch (\Exception $e) {
            // Folder does not exist. Likely there are no other files in root dir to restore.
            $otherFilesToRestore = [];
        }

        $destinationWpRootDir = $this->directory->getAbsPath();

        foreach ($otherFilesToRestore as $relativePath => $absSourcePath) {
            $absDestPath = $destinationWpRootDir . $relativePath;

            if ($this->isExcludedFile($absDestPath)) {
                continue;
            }

            /*
             * Scenario: Restoring another file that exists or do not exist
             * 1. Overwrite conflicting files with what's in the backup
             */
            $this->enqueueMove($absSourcePath, $absDestPath);
        }
    }

    /**
     * @example  [
     *         'wp-activate.php' => '/var/www/single_tests/wp-content/uploads/wp-staging/tmp/restore/8ac042d7ef3d/wpstg_a_/wp-activate.php',
     *         'wp-config.php' => '/var/www/single_tests/wp-content/uploads/wp-staging/tmp/restore/8ac042d7ef3d/wpstg_a_/wp-config.php',
     *          ]
     *
     * @return array An array of paths of [other] files found in the root of the temporary extracted wp-content backup folder,
     *               where the index is the path relative to the wp-content folder, and the value is the absolute path.
     */
    private function getOtherRootFilesToRestore(): array
    {
        $path = $this->jobDataDto->getTmpDirectory() . PathIdentifier::IDENTIFIER_ABSPATH;
        $path = trailingslashit($path);

        return $this->filesystem->findFilesInDir($path);
    }

    /**
     * @param  \SplFileInfo $fileInfo
     * @return bool
     */
    protected function isDot(\SplFileInfo $fileInfo): bool
    {
        return $fileInfo->getBasename() === '.' || $fileInfo->getBasename() === '..';
    }
}
