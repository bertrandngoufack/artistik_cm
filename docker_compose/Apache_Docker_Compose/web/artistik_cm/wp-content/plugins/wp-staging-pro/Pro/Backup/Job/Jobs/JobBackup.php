<?php

namespace WPStaging\Pro\Backup\Job\Jobs;

use WPStaging\Backup\Job\Jobs\JobBackup as BasicJobBackup;
use WPStaging\Backup\Task\Tasks\JobBackup\CleanupValidationFilesTask;
use WPStaging\Backup\Task\Tasks\JobBackup\IncludeDatabaseTask;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Job\Task\Tasks\CleanupTmpBackupsTask;
use WPStaging\Pro\Backup\Task\Tasks\JobBackup\BackupRequirementsCheckTask;
use WPStaging\Pro\Backup\Task\Tasks\JobBackup\DatabaseBackupTask;
use WPStaging\Pro\Backup\Task\Tasks\JobBackup\FilesystemScannerTask;
use WPStaging\Pro\Backup\Task\Tasks\JobBackup\FinalizeBackupTask;
use WPStaging\Pro\Backup\Task\Tasks\JobBackup\FinalizeMultipartDatabaseTask;
use WPStaging\Pro\Backup\Task\Tasks\JobBackup\RemoteStorageTasks\AmazonS3StorageTask;
use WPStaging\Pro\Backup\Task\Tasks\JobBackup\RemoteStorageTasks\DigitalOceanSpacesStorageTask;
use WPStaging\Pro\Backup\Task\Tasks\JobBackup\RemoteStorageTasks\GenericS3StorageTask;
use WPStaging\Pro\Backup\Task\Tasks\JobBackup\RemoteStorageTasks\GoogleDriveStorageTask;
use WPStaging\Pro\Backup\Task\Tasks\JobBackup\RemoteStorageTasks\DropboxStorageTask;
use WPStaging\Pro\Backup\Task\Tasks\JobBackup\RemoteStorageTasks\OneDriveStorageTask;
use WPStaging\Pro\Backup\Task\Tasks\JobBackup\RemoteStorageTasks\PCloudStorageTask;
use WPStaging\Pro\Backup\Task\Tasks\JobBackup\RemoteStorageTasks\SftpStorageTask;
use WPStaging\Pro\Backup\Task\Tasks\JobBackup\RemoteStorageTasks\WasabiStorageTask;
use WPStaging\Pro\Backup\Task\Tasks\JobBackup\ScheduleBackupTask;
use WPStaging\Pro\Backup\Task\Tasks\JobBackup\FinishBackupTask;
use WPStaging\Pro\Backup\Task\Tasks\JobBackup\BackupOtherWpRootFilesTask;
use WPStaging\Pro\Backup\Task\Tasks\JobBackup\ValidateBackupTask;
use WPStaging\Pro\License\Licensing;
use WPStaging\Pro\RemoteSync\Tasks\PullSourceSiteWaitTask;
use WPStaging\Pro\WPStagingPro;

class JobBackup extends BasicJobBackup
{
    use RemoteUploadTasksTrait;

    /**
     * @return void
     */
    protected function addFinalizeTask()
    {
        $this->tasks[] = FinalizeBackupTask::class;
    }

    /**
     * @return void
     */
    protected function addFinishBackupTask()
    {
        $this->tasks[] = FinishBackupTask::class;
    }

    /**
     * @return void
     */
    protected function setRequirementTask()
    {
        if ($this->jobDataDto->getIsSyncRequest()) {
            $this->tasks[] = PullSourceSiteWaitTask::class;
        }

        $this->tasks[] = BackupRequirementsCheckTask::class;
        if ($this->jobDataDto->getIsSyncRequest()) {
            $this->tasks[] = CleanupTmpBackupsTask::class;
        }
    }

    protected function addDatabaseTasks()
    {
        if (!$this->jobDataDto->getIsExportingDatabase()) {
            return;
        }

        $this->tasks[] = DatabaseBackupTask::class;
        if (!$this->jobDataDto->getIsMultipartBackup()) {
            $this->tasks[] = IncludeDatabaseTask::class;
        } elseif (!$this->jobDataDto->getIsBackupFormatV1()) {
            $this->tasks[] = FinalizeMultipartDatabaseTask::class;
        }
    }

    protected function addValidationTasks()
    {
        // Early bail: no need to validate on sync requests
        if ($this->jobDataDto->getIsSyncRequest()) {
            return;
        }

        if (!$this->jobDataDto->getIsMultipartBackup()) {
            $this->tasks[] = ValidateBackupTask::class;
            $this->tasks[] = CleanupValidationFilesTask::class;
        }

        foreach ($this->jobDataDto->getMultipartFilesInfo() as $ignored) {
            $this->tasks[] = ValidateBackupTask::class;
            $this->tasks[] = CleanupValidationFilesTask::class;
        }
    }

    /**
     * @return void
     */
    protected function setScannerTask()
    {
        $this->tasks[] = FilesystemScannerTask::class;
    }

    /**
     * @return void
     */
    protected function addSchedulerTask()
    {
        $this->tasks[] = ScheduleBackupTask::class;
    }

    /**
     * @return void
     */
    protected function addStoragesTasks()
    {
        /**
         * @var Licensing $licensing
         */
        $licensing = WPStaging::make(Licensing::class);
        if (!WPStagingPro::isValidLicense() || $licensing->isPersonalLicense()) {
            return;
        }

        if ($this->jobDataDto->isUploadToGoogleDrive()) {
            $this->tasks[] = GoogleDriveStorageTask::class;
        }

        if ($this->jobDataDto->isUploadToAmazonS3()) {
            $this->tasks[] = AmazonS3StorageTask::class;
        }

        if ($this->jobDataDto->isUploadToDropbox()) {
            $this->tasks[] = DropboxStorageTask::class;
        }

        if ($this->jobDataDto->isUploadToOneDrive() && $licensing->isBusinessPlanOrHigher()) {
            $this->tasks[] = OneDriveStorageTask::class;
        }

        if ($this->jobDataDto->isUploadToPCloud() && $licensing->isBusinessPlanOrHigher()) {
            $this->tasks[] = PCloudStorageTask::class;
        }

        if ($this->jobDataDto->isUploadToSftp()) {
            $this->tasks[] = SftpStorageTask::class;
        }

        if ($this->jobDataDto->isUploadToDigitalOceanSpaces()) {
            $this->tasks[] = DigitalOceanSpacesStorageTask::class;
        }

        if ($this->jobDataDto->isUploadToWasabi()) {
            $this->tasks[] = WasabiStorageTask::class;
        }

        if ($this->jobDataDto->isUploadToGenericS3()) {
            $this->tasks[] = GenericS3StorageTask::class;
        }
    }

    /**
     * @return void
     */
    protected function addBackupOtherWpRootFilesTasks()
    {
        if (!WPStagingPro::isValidLicense()) {
            return;
        }

        if ($this->jobDataDto->getIsExportingOtherWpRootFiles()) {
            $this->tasks[] = BackupOtherWpRootFilesTask::class;
        }
    }
}
