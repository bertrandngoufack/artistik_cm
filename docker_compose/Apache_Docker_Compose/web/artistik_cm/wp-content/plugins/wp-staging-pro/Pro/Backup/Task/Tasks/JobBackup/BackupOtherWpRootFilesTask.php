<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobBackup;

use WPStaging\Framework\Filesystem\PartIdentifier;
use WPStaging\Backup\Task\FileBackupTask;

class BackupOtherWpRootFilesTask extends FileBackupTask
{
    protected function getFileIdentifier(): string
    {
        return PartIdentifier::OTHER_WP_ROOT_PART_IDENTIFIER;
    }

    /**
     * @return string
     */
    public static function getTaskName(): string
    {
        return parent::getTaskName() . '_' . PartIdentifier::OTHER_WP_ROOT_PART_IDENTIFIER;
    }

    /**
     * @return string
     */
    public static function getTaskTitle(): string
    {
        return 'Adding Other Files In WP Root to Backup';
    }

    /**
     * @return bool
     */
    protected function isOtherWpRootFilesTask(): bool
    {
        return true;
    }
}
