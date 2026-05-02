<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobCloudDownload;

use WPStaging\Pro\Backup\Task\Tasks\JobCloudDownload\AbstractCloudDownloadTask;

class PCloudDownloadTask extends AbstractCloudDownloadTask
{
    public static function getTaskName(): string
    {
        return 'download_backup_from_pcloud';
    }

    public static function getTaskTitle(): string
    {
        return 'Downloading backup from pCloud';
    }
}
