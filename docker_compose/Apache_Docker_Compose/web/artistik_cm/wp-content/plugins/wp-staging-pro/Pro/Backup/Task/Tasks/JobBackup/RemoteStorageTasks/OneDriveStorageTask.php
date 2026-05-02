<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobBackup\RemoteStorageTasks;

use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Pro\Backup\Storage\OneDrive\Auth;
use WPStaging\Pro\Backup\Storage\OneDrive\Uploader;
use WPStaging\Pro\Backup\Task\Tasks\JobBackup\AbstractStorageTask;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

class OneDriveStorageTask extends AbstractStorageTask
{
    public function __construct(LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue, Uploader $remoteUploader, Auth $auth)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue, $remoteUploader, $auth);
    }

    public function getStorageProvider(): string
    {
        return 'Microsoft OneDrive';
    }

    public static function getTaskName(): string
    {
        return 'backup_one_drive_upload';
    }

    public static function getTaskTitle(): string
    {
        return 'Uploading Backup to Microsoft OneDrive';
    }
}
