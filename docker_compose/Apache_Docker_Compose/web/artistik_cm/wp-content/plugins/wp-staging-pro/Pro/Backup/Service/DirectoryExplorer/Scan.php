<?php

namespace WPStaging\Pro\Backup\Service\DirectoryExplorer;

use DirectoryIterator;
use Throwable;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Staging\Sites;
use WPStaging\Framework\TemplateEngine\TemplateEngine;

use function WPStaging\functions\debug_log;

/**
 * @package WPStaging\Pro\Backup\Service\DirectoryExplorer
 */
class Scan
{
    /**
     * @var array
     */
    private $directories = [];

    /** @var string[] */
    private $disabledDirs = [];

    /**
     * @var Directory
     */
    protected $dirAdapter;

    /**
     * @var TemplateEngine
     */
    private $templateEngine;

    /**
     * @var Sites
     */
    private $stagingSitesHelper;

    public function __construct(Sites $sites, Directory $directory, TemplateEngine $templateEngine)
    {
        $this->dirAdapter   = $directory;
        $this->disabledDirs = $this->dirAdapter->getWpDefaultRootDirectories();

        /** @var Sites */
        $this->stagingSitesHelper = $sites;
        $this->templateEngine     = $templateEngine;

        $this->buildDirectories();
    }

    /**
     * @return void
     */
    public function addStagingSitesDirsToDisabledDirs()
    {
        $this->disabledDirs = array_merge($this->disabledDirs, $this->stagingSitesHelper->getStagingDirectories());
    }

    /**
     * @param null|string $directories
     * @param bool $forceDisabled
     * @return string
     */
    public function listDirectoryForBackup($directories = null, bool $forceDisabled = false): string
    {
        if (empty($directories)) {
            $directories = $this->directories;
        }

        if (empty($directories)) {
            return '<li class="wpstg-no-custom-dirs">' . esc_html__('No available custom folders!', 'wp-staging') . '</li>';
        }

        $output = '';
        foreach ($directories as $dirBaseName => $directory) {
            if (!is_array($directory)) {
                continue;
            }

            // Need to preserve keys so no array_shift()
            $data = reset($directory);
            unset($directory[key($directory)]);

            if (!isset($data["path"])) {
                continue;
            }

            $disabledDirsWithoutSlashit = array_map('untrailingslashit', $this->disabledDirs);
            if (in_array($data["path"], $disabledDirsWithoutSlashit)) {
                $isDisabled = true;
            } else {
                $isDisabled = false;
            }

            $idName = uniqid('wpstg');
            $output .= $this->templateEngine->render('pro/backup/directory-navigation.php', [
                'scan'          => $this,
                'idName'        => $idName,
                'isDisabled'    => $isDisabled,
                'forceDisabled' => $forceDisabled,
                'data'          => $data,
                'dirBaseName'   => $dirBaseName,
            ]);
        }

        return $output;
    }

    /**
     * Build directories and main meta data recursively
     */
    protected function buildDirectories()
    {
        try {
            $directories = new DirectoryIterator(ABSPATH);

            foreach ($directories as $directory) {
                $path = false;
                try {
                    $path = $this->getPath($directory);
                } catch (Throwable $e) {
                    continue;
                }

                if ($path === false) {
                    continue;
                }

                if (in_array(trailingslashit($path), $this->dirAdapter->getWpDefaultRootDirectories())) {
                    continue;
                }

                $this->directories[basename($path)]["metaData"] = [
                    "path" => $path,
                ];
            }
        } catch (Throwable $ex) {
            debug_log('Cannot scan root path. Error: ' . $ex->getMessage());
        }
    }

    /**
     * @param DirectoryIterator $directory
     * @return string|false
     */
    protected function getPath(DirectoryIterator $directory)
    {
        if (!$directory->isDir() || $directory->isDot()) {
            return false;
        }

        return $directory->getRealPath();
    }
}
