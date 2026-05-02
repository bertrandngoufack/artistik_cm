<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobRemoteUpload;

use WPStaging\Backup\WithBackupIdentifier;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Traits\EventLoggerTrait;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Pro\Backup\Dto\Traits\SaveBackupsInDBTrait;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

class FinishUploadTask extends RemoteUploadTask
{
    use WithBackupIdentifier;
    use EventLoggerTrait;
    use SaveBackupsInDBTrait;

    /**
     * @param LoggerInterface $logger
     * @param Cache $cache
     * @param StepsDto $stepsDto
     * @param SeekableQueueInterface $taskQueue
     */
    public function __construct(LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
    }

    /**
     * @return string
     */
    public static function getTaskName(): string
    {
        return 'remote_upload_finish';
    }

    /**
     * @return string
     */
    public static function getTaskTitle(): string
    {
        return 'Finishing Upload';
    }

    /**
     * @return TaskResponseDto
     */
    public function execute(): TaskResponseDto
    {
        $backupFileId = $this->extractBackupIdFromFilename($this->jobDataDto->getFile());
        $this->saveBackupsInDB($backupFileId, $this->jobDataDto);
        $this->stepsDto->finish();
        $this->getJobTransientCache()->completeJob();

        $this->jobDataDto->setEndTime(time());

        return $this->generateResponse();
    }
}
