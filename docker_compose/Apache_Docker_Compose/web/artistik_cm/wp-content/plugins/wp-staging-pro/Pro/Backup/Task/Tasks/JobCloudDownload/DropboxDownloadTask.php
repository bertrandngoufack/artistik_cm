<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobCloudDownload;

use WPStaging\Pro\Backup\Task\Tasks\JobCloudDownload\AbstractCloudDownloadTask;

class DropboxDownloadTask extends AbstractCloudDownloadTask
{
    public static function getTaskName(): string
    {
        return 'download_backup_from_dropbox';
    }

    public static function getTaskTitle(): string
    {
        return 'Downloading backup from Dropbox';
    }
}
