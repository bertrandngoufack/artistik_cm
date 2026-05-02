<?php

/**
 * Pro version file backup service with multipart and compression support
 *
 * Extends the basic file backup service to handle multipart backups and compressed archives,
 * enabling creation of split backups for large sites and optimized storage with compression.
 */

namespace WPStaging\Pro\Backup\Service;

use WPStaging\Backup\Service\FileBackupService as BaseFileBackupService;
use WPStaging\Backup\Service\ZlibCompressor;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Framework\SiteInfo;
use WPStaging\Pro\Backup\Dto\Service\ArchiverDto;

class FileBackupService extends BaseFileBackupService
{
    /** @var string */
    protected $fileIdentifier;

    /** @var Archiver */
    protected $archiver;

    /** @var bool */
    protected $isOtherWpRootFilesTask = false;

    /** @var bool */
    private $isCompressionEnabled = false;

    public function __construct(Archiver $archiver, Directory $directory, Filesystem $filesystem, SiteInfo $siteInfo, ZlibCompressor $zlibCompressor)
    {
        parent::__construct($archiver, $directory, $filesystem, $siteInfo);

        $this->isCompressionEnabled = $zlibCompressor->isCompressionEnabled();
    }

    /**
     * @param string $fileIdentifier
     * @param bool $isOtherWpRootFilesTask
     * @return void
     */
    public function setupArchiver(string $fileIdentifier, bool $isOtherWpRootFilesTask = false)
    {
        $this->fileIdentifier         = $fileIdentifier;
        $this->isOtherWpRootFilesTask = $isOtherWpRootFilesTask;

        if (!$this->jobDataDto->getIsMultipartBackup()) {
            $this->archiver->createArchiveFile(Archiver::CREATE_BINARY_HEADER);
            return;
        }

        $this->archiver->setCategory($this->fileIdentifier);
        if ($this->stepsDto->getTotal() > 0) {
            $indices = $this->jobDataDto->getFileBackupIndices();
            if (array_key_exists($this->fileIdentifier, $indices)) {
                $this->archiver->setCategoryIndex($indices[$this->fileIdentifier] ?? 0);
                return;
            }
        }

        $this->archiver->setCategoryIndex(0);
    }

    /**
     * @return void
     */
    protected function updateMultipartInfo()
    {
        if (!$this->jobDataDto->getIsMultipartBackup()) {
            return;
        }

        if ($this->stepsDto->isFinished() && $this->stepsDto->getTotal() > 0) {
            $backupPartInfo = $this->archiver->getFinalizeBackupInfo();
            $this->jobDataDto->addMultipartFileInfo($backupPartInfo);
        }
    }

    /**
     * @param string $path
     * @return void
     */
    protected function maybeIncrementPartNo(string $path)
    {
        if (!$this->jobDataDto->getIsMultipartBackup()) {
            return;
        }

        $fileSize = filesize($path);
        $maxPartSize = $this->jobDataDto->getMaxMultipartBackupSize();
        if (!$this->archiver->doExceedMaxPartSize($fileSize, $maxPartSize)) {
            return;
        }

        $backupPartInfo = $this->archiver->getFinalizeBackupInfo();
        $this->jobDataDto->addMultipartFileInfo($backupPartInfo);

        $index = 0;
        $fileBackupIndices = $this->jobDataDto->getFileBackupIndices();
        if (array_key_exists($this->fileIdentifier, $fileBackupIndices)) {
            $index = $fileBackupIndices[$this->fileIdentifier];
        }

        $fileBackupIndices[$this->fileIdentifier] = $index + 1;
        $this->jobDataDto->setFileBackupIndices($fileBackupIndices);
        $this->archiver->setCategoryIndex($fileBackupIndices[$this->fileIdentifier] ?? 0);
    }

    protected function shouldPrependAbsPath(): bool
    {
        return $this->isOtherWpRootFilesTask || ($this->isWpContentOutsideAbspath === false);
    }

    /**
     * This method logs how many files processed in the current request.
     * @return void
     */
    protected function logExecution()
    {
        parent::logExecution();

        if (!$this->isCompressionEnabled) {
            return;
        }

        $this->logger->debug(sprintf(
            'Compression: Processed %d %s. %d/%d files actually compressed. Chunk number: %d',
            $this->jobDataDto->getTotalFiles(),
            $this->jobDataDto->getTotalFiles() === 1 ? 'file' : 'files',
            $this->jobDataDto->getTotalFilesCompressed(),
            $this->jobDataDto->getDiscoveredFiles(),
            $this->jobDataDto->getTotalChunks()
        ));

        if ($this->bigFileBeingProcessed instanceof ArchiverDto) {
            $totalBytesRead       = $this->bigFileBeingProcessed->getWrittenBytesTotal();
            $remainingBytesToRead = $this->bigFileBeingProcessed->getFileSize() - $totalBytesRead;

            $this->logger->debug(sprintf(
                'Compressing Big File: Will continue compression of file %s (%s remaining)',
                $this->bigFileBeingProcessed->getIndexPath(),
                size_format($remainingBytesToRead)
            ));
        }
    }
}
