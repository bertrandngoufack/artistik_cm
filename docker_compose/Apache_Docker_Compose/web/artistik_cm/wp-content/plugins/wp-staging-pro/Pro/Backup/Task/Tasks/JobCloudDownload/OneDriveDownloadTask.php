<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobCloudDownload;

use WPStaging\Pro\Backup\Task\Tasks\JobCloudDownload\AbstractCloudDownloadTask;

class OneDriveDownloadTask extends AbstractCloudDownloadTask
{
    public static function getTaskName(): string
    {
        return 'download_backup_from_onedrive';
    }

    public static function getTaskTitle(): string
    {
        return 'Downloading backup from Microsoft OneDrive';
    }
}
