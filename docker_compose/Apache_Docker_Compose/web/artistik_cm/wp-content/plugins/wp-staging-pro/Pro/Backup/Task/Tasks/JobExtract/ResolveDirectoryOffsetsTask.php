<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobExtract;

use WPStaging\Backup\BackupFileIndex;
use WPStaging\Backup\Entity\BackupMetadata;
use WPStaging\Backup\FileHeader;
use WPStaging\Backup\Utils\BackupPathResolver;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Filesystem\FileObject;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Framework\Filesystem\PathIdentifier;
use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Pro\Backup\Dto\Task\Extract\ResolveDirectoryOffsetsTaskDto;
use WPStaging\Pro\Backup\Task\ExtractTask;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

/**
 * Resolves directory selections into backup index offsets for extraction.
 */
class ResolveDirectoryOffsetsTask extends ExtractTask
{
    /** @var PathIdentifier */
    private $pathIdentifier;

    /** @var ResolveDirectoryOffsetsTaskDto */
    protected $currentTaskDto;

    /** @var BackupPathResolver */
    private $backupPathResolver;

    /** @var Filesystem */
    private $filesystem;

    public function __construct(PathIdentifier $pathIdentifier, LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue, BackupPathResolver $backupPathResolver, Filesystem $filesystem)
    {
        $this->pathIdentifier     = $pathIdentifier;
        $this->backupPathResolver = $backupPathResolver;
        $this->filesystem         = $filesystem;
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
    }

    public static function getTaskName()
    {
        return 'backup_extract_resolve_directories';
    }

    public static function getTaskTitle()
    {
        return 'Resolving Directory Selection';
    }

    public function execute()
    {
        $directories = $this->jobDataDto->getDirectories();
        if (empty($directories)) {
            $this->stepsDto->setTotal(1);
            $this->stepsDto->setCurrent(1);
            return $this->generateResponse(true);
        }

        $metadata   = $this->jobDataDto->getBackupMetadata();
        $backupFile = $this->backupPathResolver->resolveBackupPath($this->jobDataDto->getFile());
        $indexStart = (int)$metadata->getHeaderStart();
        $indexEnd   = (int)$metadata->getHeaderEnd();
        $totalRange = max(1, $indexEnd - $indexStart);

        $this->stepsDto->setTotal($totalRange);

        $this->currentTaskDto->currentIndexOffset = isset($this->currentTaskDto->currentIndexOffset) ? (int)$this->currentTaskDto->currentIndexOffset : 0;
        $currentOffset = $this->currentTaskDto->currentIndexOffset;
        if ($currentOffset <= 0) {
            $currentOffset = $indexStart;
        }

        if ($currentOffset >= $indexEnd) {
            $this->stepsDto->finish();
            return $this->generateResponse(true);
        }

        $prefixes = $this->normalizeDirectories($directories);
        if (empty($prefixes)) {
            $this->stepsDto->finish();
            return $this->generateResponse(true);
        }

        $newOffsets = [];
        $indexLineDto = $this->createIndexLineDto($metadata);

        try {
            $fileObject = new FileObject($backupFile, FileObject::MODE_READ);
            $fileObject->fseek($currentOffset);

            while (!$this->isThreshold() && $fileObject->valid() && $fileObject->ftell() < $indexEnd) {
                $indexOffset = $fileObject->ftell();
                $rawIndexFile = $fileObject->readAndMoveNext();
                if (!$indexLineDto->isIndexLine($rawIndexFile)) {
                    continue;
                }

                $backupFileIndex = $indexLineDto->readIndexLine($rawIndexFile);
                $relativePath = $this->pathIdentifier->transformIdentifiableToRelativePath($backupFileIndex->getIdentifiablePath());
                $relativePath = $this->filesystem->normalizePath($relativePath);

                foreach ($prefixes as $prefix) {
                    if ($prefix !== '' && strpos($relativePath, $prefix) === 0) {
                        $newOffsets[] = (int)$indexOffset;
                        break;
                    }
                }
            }

            $currentOffset = $fileObject->ftell();
            $fileObject = null;
        } catch (\Throwable $e) {
            $this->logger->critical($e->getMessage());
            return $this->generateResponse(false);
        }

        if (!empty($newOffsets)) {
            $existingOffsets = $this->jobDataDto->getOffsets();
            $mergedOffsets = array_values(array_unique(array_merge($existingOffsets, $newOffsets)));
            $this->jobDataDto->setOffsets($mergedOffsets);
        }

        $this->currentTaskDto->currentIndexOffset = (int)$currentOffset;
        $this->setCurrentTaskDto($this->currentTaskDto);
        $this->persistJobDataDto();

        $progress = max(0, min($totalRange, $currentOffset - $indexStart));
        $this->stepsDto->setCurrent($progress);

        if ($currentOffset >= $indexEnd) {
            $this->stepsDto->finish();
            $this->logger->info(__('Directory selection resolved.', 'wp-staging'));
            return $this->generateResponse(true);
        }

        $this->logger->info(sprintf(__('Resolving directory selection... (%d%%)', 'wp-staging'), $this->stepsDto->getPercentage()));

        return $this->generateResponse(false);
    }

    protected function getCurrentTaskType(): string
    {
        return ResolveDirectoryOffsetsTaskDto::class;
    }

    private function normalizeDirectories(array $directories): array
    {
        $normalized = [];
        foreach ($directories as $directory) {
            $directory = trim((string)$directory);
            if ($directory === '') {
                continue;
            }

            $directory = trim($this->filesystem->normalizePath($directory), '/');
            if ($directory === '') {
                continue;
            }

            $normalized[] = trailingslashit($directory);
        }

        return array_values(array_unique($normalized));
    }



    private function createIndexLineDto(BackupMetadata $metadata)
    {
        if ($metadata->getIsBackupFormatV1()) {
            return new BackupFileIndex();
        }

        return WPStaging::make(FileHeader::class);
    }
}
