<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobExtract;

use WPStaging\Backup\BackupFileIndex;
use WPStaging\Backup\Entity\BackupMetadata;
use WPStaging\Backup\Entity\FileBeingExtracted;
use WPStaging\Backup\Exceptions\EmptyChunkException;
use WPStaging\Backup\FileHeader;
use WPStaging\Backup\Service\ZlibCompressor;
use WPStaging\Backup\Utils\BackupPathResolver;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Filesystem\FileObject;
use WPStaging\Framework\Filesystem\PathIdentifier;
use WPStaging\Framework\Job\Exception\DiskNotWritableException;
use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Backup\Dto\Task\Extract\ExtractSelectedFilesTaskDto;
use WPStaging\Pro\Backup\Task\ExtractTask;
use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Framework\Queue\SeekableQueueInterface;

/**
 * Extracts user-selected files from a backup using incremental requests.
 */
class ExtractSelectedFilesTask extends ExtractTask
{
    /** @var PathIdentifier */
    private $pathIdentifier;

    /** @var Directory */
    private $directory;

    /** @var ZlibCompressor */
    private $zlibCompressor;

    /** @var ExtractSelectedFilesTaskDto */
    protected $currentTaskDto;

    /** @var BackupPathResolver */
    private $backupPathResolver;

    public function __construct(PathIdentifier $pathIdentifier, Directory $directory, ZlibCompressor $zlibCompressor, LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue, BackupPathResolver $backupPathResolver)
    {
        $this->pathIdentifier     = $pathIdentifier;
        $this->directory          = $directory;
        $this->zlibCompressor     = $zlibCompressor;
        $this->backupPathResolver = $backupPathResolver;
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
    }

    public static function getTaskName()
    {
        return 'backup_extract_selected_files';
    }

    public static function getTaskTitle()
    {
        return 'Extracting Selected Files';
    }

    public function execute()
    {
        if (!WPStaging::isPro()) {
            $this->logger->warning(__('Extracting files from backups is available in WP STAGING Pro.', 'wp-staging'));
            return $this->generateResponse(false);
        }

        $metadata = $this->jobDataDto->getBackupMetadata();
        if (!$metadata instanceof BackupMetadata) {
            $this->logger->critical(__('Missing backup metadata for extraction.', 'wp-staging'));
            return $this->generateResponse(false);
        }

        $offsets      = $this->jobDataDto->getOffsets();
        $totalOffsets = count($offsets);
        $this->stepsDto->setTotal($totalOffsets);

        if ($totalOffsets === 0) {
            $this->logger->warning(__('No files selected for extraction.', 'wp-staging'));
            $this->stepsDto->setCurrent($totalOffsets);
            return $this->generateResponse(false);
        }

        $backupFile = $this->backupPathResolver->resolveBackupPath($this->jobDataDto->getFile());
        if (empty($backupFile) || !file_exists($backupFile)) {
            $this->logger->critical(__('Backup file not found.', 'wp-staging'));
            return $this->generateResponse(false);
        }

        $extractRoot = trailingslashit($this->directory->getPluginWpContentDirectory()) . 'extract/' . $this->jobDataDto->getId() . '/';

        try {
            $backupFileObject = new FileObject($backupFile, FileObject::MODE_READ);
        } catch (\Throwable $e) {
            $this->logger->critical($e->getMessage());
            return $this->generateResponse(false);
        }

        $indexStart = (int)$metadata->getHeaderStart();
        $indexEnd   = (int)$metadata->getHeaderEnd();

        $this->currentTaskDto->currentOffsetIndex = isset($this->currentTaskDto->currentOffsetIndex) ? (int)$this->currentTaskDto->currentOffsetIndex : 0;
        $this->currentTaskDto->currentFileWrittenBytes = isset($this->currentTaskDto->currentFileWrittenBytes) ? (int)$this->currentTaskDto->currentFileWrittenBytes : 0;
        $this->currentTaskDto->currentFileReadBytes = isset($this->currentTaskDto->currentFileReadBytes) ? (int)$this->currentTaskDto->currentFileReadBytes : 0;
        $this->currentTaskDto->currentHeaderBytesRemoved = isset($this->currentTaskDto->currentHeaderBytesRemoved) ? (int)$this->currentTaskDto->currentHeaderBytesRemoved : 0;

        $currentIndex = $this->currentTaskDto->currentOffsetIndex;

        while (!$this->isThreshold() && $currentIndex < $totalOffsets) {
            $offset = absint($offsets[$currentIndex]);

            if ($offset <= 0 || $offset < $indexStart || $offset > $indexEnd) {
                $this->incrementSkipped();
                $currentIndex++;
                $this->resetCurrentFileProgress();
                continue;
            }

            $indexLineDto = $this->createIndexLineDto($metadata);
            $backupFileObject->fseek($offset);
            $rawIndexFile = $backupFileObject->readAndMoveNext();

            if (!$indexLineDto->isIndexLine($rawIndexFile)) {
                $this->incrementSkipped();
                $currentIndex++;
                $this->resetCurrentFileProgress();
                continue;
            }

            $backupFileIndex   = $indexLineDto->readIndexLine($rawIndexFile);
            $identifiablePath  = $backupFileIndex->getIdentifiablePath();

            if (empty($identifiablePath)) {
                $this->incrementSkipped();
                $currentIndex++;
                $this->resetCurrentFileProgress();
                continue;
            }

            $identifier    = $this->pathIdentifier->getIdentifierFromPath($identifiablePath);
            $extractFolder = trailingslashit($extractRoot . $this->pathIdentifier->getRelativePath($identifier));

            if (!wp_mkdir_p($extractFolder)) {
                $this->addError(sprintf(__('Could not create extraction folder for %s.', 'wp-staging'), $identifiablePath));
                $this->incrementSkipped();
                $currentIndex++;
                $this->resetCurrentFileProgress();
                continue;
            }

            $fileBeingExtracted = new FileBeingExtracted($identifiablePath, $extractFolder, $this->pathIdentifier, $backupFileIndex);
            $fileBeingExtracted->setWrittenBytes($this->currentTaskDto->currentFileWrittenBytes);
            $fileBeingExtracted->setReadBytes($this->currentTaskDto->currentFileReadBytes);
            $fileBeingExtracted->setHeaderBytesRemoved($this->currentTaskDto->currentHeaderBytesRemoved);

            $destinationPath = $fileBeingExtracted->getBackupPath();

            if (!$this->isPathWithinRoot($destinationPath, $extractRoot)) {
                $this->addError(sprintf(__('Invalid extraction path for %s.', 'wp-staging'), $identifiablePath));
                $this->incrementSkipped();
                $currentIndex++;
                $this->resetCurrentFileProgress();
                continue;
            }

            $overwrite = $this->jobDataDto->getOverwrite();
            if (file_exists($destinationPath) && !$overwrite) {
                $this->currentTaskDto->skipped++;
                $currentIndex++;
                $this->resetCurrentFileProgress();
                continue;
            }

            if ($overwrite && file_exists($destinationPath) && $fileBeingExtracted->getWrittenBytes() === 0 && !@unlink($destinationPath)) {
                $this->addError(sprintf(__('Could not overwrite %s.', 'wp-staging'), $identifiablePath));
                $this->currentTaskDto->skipped++;
                $currentIndex++;
                $this->resetCurrentFileProgress();
                continue;
            }

            try {
                $this->extractFileChunk($backupFileObject, $fileBeingExtracted);
                $this->currentTaskDto->currentFileWrittenBytes = $fileBeingExtracted->getWrittenBytes();
                $this->currentTaskDto->currentFileReadBytes = $fileBeingExtracted->getReadBytes();
                $this->currentTaskDto->currentHeaderBytesRemoved = $fileBeingExtracted->getHeaderBytesRemoved();

                if ($fileBeingExtracted->isFinished()) {
                    $this->incrementExtracted();
                    $currentIndex++;
                    $this->resetCurrentFileProgress();
                }
            } catch (DiskNotWritableException $e) {
                $this->addError($e->getMessage());
                break;
            } catch (\Throwable $e) {
                $this->addError($e->getMessage());
                $this->incrementSkipped();
                $currentIndex++;
                $this->resetCurrentFileProgress();
            }
        }

        $backupFileObject = null;

        $this->currentTaskDto->currentOffsetIndex = (int)$currentIndex;
        $this->setCurrentTaskDto($this->currentTaskDto);
        $this->persistJobDataDto();

        $completed = min($totalOffsets, $currentIndex);
        $this->stepsDto->setCurrent($completed);

        if ($this->stepsDto->isFinished()) {
            $extractedCount = $this->jobDataDto->getExtracted();
            $skippedCount   = $this->jobDataDto->getSkipped();
            if ($skippedCount > 0) {
                $extractedLabel = $extractedCount === 1
                    ? __('%1$d file extracted, %2$d file skipped.', 'wp-staging')
                    : __('%1$d files extracted, %2$d files skipped.', 'wp-staging');
                $this->logger->info(sprintf(__('Extraction complete.', 'wp-staging') . ' ' . $extractedLabel, $extractedCount, $skippedCount));
            } else {
                $extractedLabel = $extractedCount === 1
                    ? __('%d file extracted.', 'wp-staging')
                    : __('%d files extracted.', 'wp-staging');
                $this->logger->info(sprintf(__('Extraction complete.', 'wp-staging') . ' ' . $extractedLabel, $extractedCount));
            }
            $this->logger->info(sprintf(__('Extracted to: %s', 'wp-staging'), $extractRoot));
        } else {
            $this->logger->info(sprintf(__('Extracting files... (%1$d/%2$d)', 'wp-staging'), $completed, $totalOffsets));
        }

        return $this->generateResponse(false);
    }

    protected function getCurrentTaskType(): string
    {
        return ExtractSelectedFilesTaskDto::class;
    }

    /**
     * @return void
     */
    private function resetCurrentFileProgress()
    {
        $this->currentTaskDto->currentFileWrittenBytes = 0;
        $this->currentTaskDto->currentFileReadBytes = 0;
        $this->currentTaskDto->currentHeaderBytesRemoved = 0;
    }

    /**
     * @return void
     */
    private function incrementSkipped()
    {
        $this->jobDataDto->incrementSkipped();
    }

    /**
     * @return void
     */
    private function incrementExtracted()
    {
        $this->jobDataDto->incrementExtracted();
    }

    /**
     * @return void
     */
    private function addError(string $error)
    {
        $error = (string)$error;
        if ($error === '') {
            return;
        }

        $this->jobDataDto->addError($error);
    }

    private function createIndexLineDto(BackupMetadata $metadata)
    {
        if ($metadata->getIsBackupFormatV1()) {
            return new BackupFileIndex();
        }

        return WPStaging::make(FileHeader::class);
    }

    /**
     * Extracts at most one chunk for the current file, respecting task time/memory thresholds.
     *
     * @param FileObject $backupFile
     * @param FileBeingExtracted $fileBeingExtracted
     * @return void
     */
    private function extractFileChunk(FileObject $backupFile, FileBeingExtracted $fileBeingExtracted)
    {
        $destinationPath = $fileBeingExtracted->getBackupPath();
        $destinationDir  = dirname($destinationPath);

        if (!wp_mkdir_p($destinationDir)) {
            throw new DiskNotWritableException(__('Destination folder is not writable.', 'wp-staging'));
        }

        if (!file_exists($destinationPath) && file_put_contents($destinationPath, '') === false) {
            throw new DiskNotWritableException(__('Could not create destination file for writing.', 'wp-staging'));
        }

        $destinationResource = @fopen($destinationPath, FileObject::MODE_APPEND);
        if (!$destinationResource) {
            throw new DiskNotWritableException(__('Could not open destination file for writing.', 'wp-staging'));
        }

        $backupFile->fseek($fileBeingExtracted->getCurrentOffset());

        while (!$this->isThreshold() && !$fileBeingExtracted->isFinished()) {
            $readBytesBefore = $backupFile->ftell();

            try {
                $chunk = $this->zlibCompressor->getService()->readChunk($backupFile, $fileBeingExtracted);
            } catch (EmptyChunkException $e) {
                $fileBeingExtracted->setWrittenBytes($fileBeingExtracted->getTotalBytes());
                break;
            }

            $writtenBytes = fwrite($destinationResource, $chunk);
            if ($writtenBytes === false || $writtenBytes <= 0) {
                fclose($destinationResource);
                throw new DiskNotWritableException(__('Failed to write extracted file.', 'wp-staging'));
            }

            $fileBeingExtracted->addWrittenBytes($writtenBytes);
            $fileBeingExtracted->addReadBytes($backupFile->ftell() - $readBytesBefore);
        }

        fclose($destinationResource);
    }

    private function isPathWithinRoot(string $path, string $root): bool
    {
        $normalizedPath = wp_normalize_path($path);
        $normalizedRoot = wp_normalize_path(trailingslashit($root));

        return strpos($normalizedPath, $normalizedRoot) === 0;
    }
}
