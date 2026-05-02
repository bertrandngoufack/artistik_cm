<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobBackup;

use WPStaging\Backup\Task\BackupTask;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Framework\Filesystem\PartIdentifier;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Job\Exception\ThresholdException;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Backup\Dto\Task\Restore\FinalizeMultipartDatabaseTaskDto;
use WPStaging\Pro\Backup\Service\Archiver;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

class FinalizeMultipartDatabaseTask extends BackupTask
{
    /**
     * @var array
     */
    private $databaseParts = [];

    /** @var int */
    private $currentDatabasePartIndex = 0;

    /** @var string */
    private $currentDatabaseFile = '';

    /** @var Archiver */
    private $archiver;

    /** @var FinalizeMultipartDatabaseTaskDto */
    protected $currentTaskDto;

    /** @return string */
    protected function getCurrentTaskType(): string
    {
        return FinalizeMultipartDatabaseTaskDto::class;
    }

    public function __construct(Archiver $archiver, LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
        $this->archiver = $archiver;
    }

    /**
     * @return string
     */
    public static function getTaskName()
    {
        return 'backup_finalize_multipart_database_parts';
    }

    /**
     * @return string
     */
    public static function getTaskTitle()
    {
        return 'Finalizing Multipart Database Parts';
    }

    /**
     * @return TaskResponseDto
     */
    public function execute()
    {
        $this->prepare();

        if (!$this->currentDatabaseFile && $this->currentTaskDto->currentPartTotalBytes === 0) {
            return $this->generateResponse();
        }

        $this->archiver->getDto()->setWrittenBytesTotal($this->currentTaskDto->currentPartBytesWritten);

        if ($this->archiver->getDto()->getWrittenBytesTotal() !== 0) {
            $this->archiver->getDto()->setIndexPositionCreated(true);
        }

        try {
            $this->archiver->setFileAppendTimeLimit($this->jobDataDto->getFileAppendTimeLimit());
            $this->archiver->appendFileToBackup($this->currentDatabaseFile);
        } catch (ThresholdException $e) {
            // no-op
        } catch (\Throwable $e) {
            $this->logger->critical(sprintf(
                'Failed to include database part %s to the backup: %s (%s)',
                $this->jobDataDto->getCurrentMultipartFileInfoIndex(),
                $this->archiver->getDto()->getFilePath(),
                $e->getMessage()
            ));
        }

        $this->currentTaskDto->currentPartBytesWritten = $this->archiver->getDto()->getWrittenBytesTotal();

        if (!$this->archiver->getDto()->isFinished()) {
            $this->logger->info(sprintf('Database Part %s: %s/%s Prepared.', $this->jobDataDto->getCurrentMultipartFileInfoIndex(), size_format($this->currentTaskDto->currentPartBytesWritten), size_format($this->currentTaskDto->currentPartTotalBytes)));
            return $this->generateResponse(false);
        }

        clearstatcache();
        $this->logger->info(sprintf('Database Part %s: %s/%s Prepared.', $this->jobDataDto->getCurrentMultipartFileInfoIndex(), size_format($this->currentTaskDto->currentPartTotalBytes), size_format($this->currentTaskDto->currentPartTotalBytes)));
        $this->currentTaskDto->currentPartBytesWritten = 0;
        $this->currentTaskDto->currentPartTotalBytes = 0;
        $databasePart = $this->archiver->getFinalizeBackupInfo();
        $databasePart['srcPath'] = $this->currentDatabaseFile;
        $this->jobDataDto->updateMultipartFileInfo($databasePart, $this->currentDatabasePartIndex);
        $this->jobDataDto->setCurrentMultipartFileInfoIndex($this->jobDataDto->getCurrentMultipartFileInfoIndex() + 1);
        (new Filesystem())->delete($this->currentDatabaseFile);

        return $this->generateResponse(true);
    }

    protected function prepare()
    {
        $this->databaseParts = $this->getDatabaseParts();
        if ($this->stepsDto->getTotal() > 0) {
            $this->prepareCurrentDatabasePart();
            return;
        }

        $this->jobDataDto->setCurrentMultipartFileInfoIndex(0);
        $this->currentTaskDto->currentPartBytesWritten = 0;
        $this->currentTaskDto->currentPartTotalBytes = 0;
        $this->stepsDto->setTotal(count($this->databaseParts));

        $this->prepareCurrentDatabasePart();
    }

    private function prepareCurrentDatabasePart()
    {
        $databasePart = $this->getCurrentDatabasePart();
        $this->currentDatabaseFile = $databasePart['filePath'];
        if (!$this->currentDatabaseFile || !file_exists($this->currentDatabaseFile)) {
            $this->logger->warning(sprintf('Database Backup file not found: %s', $this->currentDatabaseFile));
            return;
        }

        $this->archiver->setCategory($databasePart['category']);
        $this->archiver->setCategoryIndex($databasePart['index']);
        if ($this->currentTaskDto->currentPartTotalBytes > 0) {
            return;
        }

        $this->archiver->getDto()->reset();
        $this->archiver->getDto()->setFilePath($this->currentDatabaseFile);
        $this->currentTaskDto->currentPartTotalBytes = filesize($this->currentDatabaseFile);
    }

    /**
     * @return array
     */
    private function getDatabaseParts()
    {
        $multiparts = $this->jobDataDto->getMultipartFilesInfo();
        return array_filter($multiparts, function ($part) {
            return $part['category'] === PartIdentifier::DATABASE_PART_IDENTIFIER;
        });
    }

    /**
     * @return array
     */
    private function getCurrentDatabasePart()
    {
        $this->currentDatabasePartIndex = array_keys($this->databaseParts)[$this->jobDataDto->getCurrentMultipartFileInfoIndex()];

        return $this->databaseParts[$this->currentDatabasePartIndex];
    }
}
