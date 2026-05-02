<?php

namespace WPStaging\Pro\Push\Service;

use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Framework\Filesystem\FilesystemExceptions;
use WPStaging\Framework\Filesystem\PartIdentifier;
use WPStaging\Framework\SiteInfo;
use WPStaging\Framework\Traits\RestoreFileExclusionTrait;

/**
 * This class is used for deploying plugins and themes during push operation as they are first copied to a temporary location
 * and then moved to their final destination.
 */
class PushFileDeployer
{
    use RestoreFileExclusionTrait;

    const BACKUP_PREFIX = 'wpstg-backup-';

    /** @var Filesystem */
    protected $filesystem;

    /** @var Directory */
    protected $directory;

    /** @var SiteInfo */
    protected $siteInfo;

    /** @var bool */
    protected $preserveExistingItems = true;

    /** @var string[] */
    protected $errors = [];

    /** @var string */
    protected $fileIdentifier;

    public function __construct(Filesystem $filesystem, Directory $directory, SiteInfo $siteInfo)
    {
        $this->filesystem = $filesystem;
        $this->directory  = $directory;
        $this->siteInfo   = $siteInfo;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param bool $preserveExistingItems
     * @return void
     */
    public function setPreserveExistingItems(bool $preserveExistingItems)
    {
        $this->preserveExistingItems = $preserveExistingItems;
    }

    /**
     * @param string $fileIdentifier
     * @return void
     */
    public function setFileIdentifier(string $fileIdentifier)
    {
        $this->fileIdentifier = $fileIdentifier;
    }

    /**
     * @return void
     */
    public function deployItems()
    {
        $tempDir = $this->getTmpDirectory();
        try {
            $itemsToActivate = $this->findItemsInDir($tempDir);
        } catch (\Exception $e) {
            // Folder does not exist. Likely there are no items to activate.
            $itemsToActivate = [];
        }

        $destDir = $this->getCurrentDirectory();

        try {
            $existingItems = $this->findItemsInDir($destDir);
        } catch (\Exception $e) {
            $this->errors[] = sprintf('Destination folder could not be found nor created at "%s"', $destDir);

            return;
        }

        $defaultExcluded = [
            $destDir . 'wp-staging' // Skip wp staging plugin, e.g wp-staging-pro, wp-staging-dev, wp-staging-pro_1.
        ];

        $backupPrefix = self::BACKUP_PREFIX;

        foreach ($itemsToActivate as $itemSlug => $itemPath) {
            if ($this->isExcludedFile("$destDir$itemSlug", $defaultExcluded)) {
                continue;
            }

            /**
             * Scenario: Restoring a item that already exists
             * 1. Backup old item
             * 2. Restore new item
             */
            if (array_key_exists($itemSlug, $existingItems)) {
                // backup
                if (!$this->move($existingItems[$itemSlug], "{$destDir}{$backupPrefix}{$itemSlug}")) {
                    continue;
                }

                // activate
                if (!$this->move($itemsToActivate[$itemSlug], "{$destDir}{$itemSlug}")) {
                    // rollback backup if activation fails
                    $this->move("{$destDir}{$backupPrefix}{$itemSlug}", $existingItems[$itemSlug]);
                    continue;
                }

                $this->rm("{$destDir}{$backupPrefix}{$itemSlug}");
                continue;
            }

            /**
             * Scenario 2: Restoring a item that does not yet exist
             */
            $this->move($itemsToActivate[$itemSlug], "{$destDir}{$itemSlug}");
        }

        $tempDir = untrailingslashit($tempDir);
        if (is_dir($tempDir)) {
            $this->filesystem->delete($tempDir);
        }

        if ($this->preserveExistingItems) {
            return;
        }

        // Remove items which are not in the push list
        foreach ($existingItems as $itemSlug => $itemPath) {
            if (!array_key_exists($itemSlug, $itemsToActivate)) {
                $this->rm($itemPath);
            }
        }
    }

    /**
     * @return void
     */
    public function cleanupTmpDirectory()
    {
        $tempDir = $this->getTmpDirectory();
        if (empty($tempDir)) {
            throw new \RuntimeException("Temporary directory is not set.");
        }

        if ($this->siteInfo->isBitnami()) {
            $tempDir = wp_normalize_path(realpath($tempDir));
        }

        if (!is_dir($tempDir)) {
            return;
        }

        $iterator = null;

        try {
            $iterator = $this->filesystem
                ->setRecursive(false)
                ->setDirectory($tempDir)
                ->setDotSkip()
                ->get();
        } catch (FilesystemExceptions $ex) {
            $this->errors[] = $ex->getMessage();
            return;
        }

        foreach ($iterator as $item) {
            if ($item->isFile()) {
                unlink($item->getPathname());
                continue;
            }

            if ($item->isDir() && $this->isAllowedToRenameOrRemove($item->getPathname())) {
                $this->filesystem->delete($item->getPathname());
            }
        }

        $tempDir = untrailingslashit($tempDir);
        if (is_dir($tempDir)) {
            $this->filesystem->delete($tempDir);
        }
    }

    /**
     * Make sure we are renaming or removing only sub directories of
     * wp-content/plugins or wp-content/themes.
     *
     * @param string $path The full path to be renamed or removed.
     *
     * @return bool Whether given path is allowed to be renamed or removed.
     */
    protected function isAllowedToRenameOrRemove(string $path): bool
    {
        $realPath = wp_normalize_path(realpath($path));

        if ($realPath === false) {
            return false;
        }

        $tmpDir  = $this->getTmpDirectory();
        $origDir = $this->getCurrentDirectory();
        if ($this->siteInfo->isBitnami()) {
            $tmpDir  = wp_normalize_path(realpath($tmpDir));
            $origDir = wp_normalize_path(realpath($origDir));
        }

        return strpos($realPath, $tmpDir) === 0 || strpos($realPath, $origDir) === 0;
    }

    protected function getTmpDirectory(): string
    {
        if ($this->fileIdentifier === PartIdentifier::PLUGIN_PART_IDENTIFIER) {
            return $this->directory->getPluginsTmpDirectory();
        }

        if ($this->fileIdentifier === PartIdentifier::THEME_PART_IDENTIFIER) {
            return $this->directory->getThemesTmpDirectory();
        }

        throw new \RuntimeException("Temporary directory is not defined for file identifier: " . $this->fileIdentifier);
    }

    protected function getCurrentDirectory(): string
    {
        if ($this->fileIdentifier === PartIdentifier::PLUGIN_PART_IDENTIFIER) {
            return $this->directory->getPluginsDirectory();
        }

        if ($this->fileIdentifier === PartIdentifier::THEME_PART_IDENTIFIER) {
            return $this->directory->getActiveThemeParentDirectory();
        }

        throw new \RuntimeException("Current directory is not defined for file identifier: " . $this->fileIdentifier);
    }

    /**
     * @param string $path Folder to look for items, eg: '/var/www/wp-content/plugins|themes'
     *
     * @example [
     *              'foo' => '/var/www/wp-content/plugins|themes/foo',
     *              'foo.php' => '/var/www/wp-content/plugins|themes/foo.php',
     *          ]
     *
     * @return array An array of paths of items found in the root of given directory,
     *               where the index is the name of the item, and the value it's path.
     */
    private function findItemsInDir(string $path)
    {
        $it = @new \DirectoryIterator($path);

        $items = [];

        $itemsToExclude = [
            WPSTG_PLUGIN_SLUG, // Skip the current active wp staging plugin slug e.g wp-staging-pro, wp-staging-dev, wp-staging-pro_1, etc.
            'wp-staging',
            'wp-staging-pro',
        ];

        /** @var \DirectoryIterator $fileInfo */
        foreach ($it as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->isLink()) {
                continue;
            }

            if ($fileInfo->isDir() && !in_array($fileInfo->getFilename(), $itemsToExclude)) {
                $items[$fileInfo->getBasename()] = $fileInfo->getPathname();

                continue;
            }

            // wp-content/plugins/foo.php
            if ($fileInfo->isFile() && $fileInfo->getExtension() === 'php') {
                $items[$fileInfo->getBasename()] = $fileInfo->getPathname();

                continue;
            }
        }

        return $items;
    }

    /**
     * @param string $fullPath
     * @return void
     */
    private function rm(string $fullPath)
    {
        if (!$this->isAllowedToRenameOrRemove($fullPath)) {
            $this->errors[] = 'Trying to remove a file/folder that is outside the expected path: ' . $fullPath;
            return;
        }

        try {
            $this->filesystem->setRecursive()->delete($fullPath);
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();

            return;
        }
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return bool Whether the rename was successful or not.
     */
    private function move(string $from, string $to): bool
    {
        if (!$this->isAllowedToRenameOrRemove($from)) {
            $this->errors[] = 'Trying to rename a file/folder that is outside the expected path: ' . $from;

            return false;
        }

        return @rename($from, $to);
    }
}
