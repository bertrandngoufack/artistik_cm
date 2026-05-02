<?php

namespace WPStaging\Pro\Push\Service;

use WPStaging\Framework\Filesystem\PathIdentifier;
use WPStaging\Staging\Dto\DirectoryNodeDto;
use WPStaging\Staging\Service\DirectoryScanner as BaseDirectoryScanner;

class DirectoryScanner extends BaseDirectoryScanner
{
    /**
     * @var bool
     */
    protected $scanSubWpContentByDefault = true;

    public function setupPush()
    {
        $this->absPath       = trailingslashit($this->stagingSetup->getStagingSiteDto()->getPath());
        $this->wpContentPath = $this->absPath . 'wp-content/';
    }

    public function renderFilesSelection()
    {
        $directories = $this->scanDirectory($this->wpContentPath, $this->absPath, PathIdentifier::IDENTIFIER_WP_CONTENT);

        /** Value of parent checked will be ignored instead the default selection will be used */
        $this->useDefaultSelection = true;

        $result = $this->directoryListing($directories, true, false);

        echo $result; // phpcs:ignore
    }

    protected function getShouldBeChecked(bool $shouldBeChecked, DirectoryNodeDto $directory): bool
    {
        $path = trailingslashit($directory->getPath());
        if (
            $this->strUtils->startsWith($path, $this->wpContentPath . 'mu-plugins/') ||
            $this->strUtils->startsWith($path, $this->wpContentPath . 'plugins/') ||
            $this->strUtils->startsWith($path, $this->wpContentPath . 'themes/') ||
            $this->strUtils->startsWith($path, $this->wpContentPath . 'uploads/')
        ) {
            return true;
        }

        return $shouldBeChecked;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function getDirectoryType(string $path): string
    {
        $dirType = 'other';
        if ($this->strUtils->startsWith($path, $this->wpContentPath . 'plugins/') !== false) {
            $pluginPath = $this->strUtils->strReplaceFirst($this->wpContentPath . 'plugins/', '', $path);
            $dirType    = strpos($pluginPath, '/') === false ? 'plugin' : 'other';
        } elseif ($this->strUtils->startsWith($path, $this->wpContentPath . 'themes/') !== false) {
            $themePath = $this->strUtils->strReplaceFirst($this->wpContentPath . 'themes/', '', $path);
            $dirType   = strpos($themePath, '/') === false ? 'theme' : 'other';
        }

        return $dirType;
    }
}
