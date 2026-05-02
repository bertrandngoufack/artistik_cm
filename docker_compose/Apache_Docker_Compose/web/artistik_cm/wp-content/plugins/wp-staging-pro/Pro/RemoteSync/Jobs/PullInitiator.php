<?php

namespace WPStaging\Pro\RemoteSync\Jobs;

use RuntimeException;
use WPStaging\Backup\Entity\BackupMetadata;
use WPStaging\Backup\Task\Tasks\JobRestore\CleanExistingMediaTask;
use WPStaging\Backup\Task\Tasks\JobRestore\RenameDatabaseTask;
use WPStaging\Backup\Task\Tasks\JobRestore\RestoreLanguageFilesTask;
use WPStaging\Backup\Task\Tasks\JobRestore\RestoreMuPluginsTask;
use WPStaging\Backup\Task\Tasks\JobRestore\RestoreOtherFilesInWpContentTask;
use WPStaging\Backup\Task\Tasks\JobRestore\RestorePluginsTask;
use WPStaging\Backup\Task\Tasks\JobRestore\RestoreThemesTask;
use WPStaging\Backup\Task\Tasks\JobRestore\UpdateBackupsScheduleTask;
use WPStaging\Framework\Job\AbstractJob;
use WPStaging\Framework\Job\Task\Tasks\CleanupBakTablesTask;
use WPStaging\Framework\Job\Task\Tasks\CleanupTmpFilesTask;
use WPStaging\Framework\Job\Task\Tasks\CleanupTmpTablesTask;
use WPStaging\Framework\SiteInfo;
use WPStaging\Pro\Backup\Task\Tasks\JobRestore\AdjustSubsitesOptionsTask;
use WPStaging\Pro\Backup\Task\Tasks\JobRestore\ExtractFilesTask;
use WPStaging\Pro\Backup\Task\Tasks\JobRestore\RestoreDatabaseTask;
use WPStaging\Pro\Backup\Task\Tasks\JobRestore\RestoreRequirementsCheckTask;
use WPStaging\Pro\Backup\Task\Tasks\JobRestore\UpdateSiteHomeUrlTask;
use WPStaging\Pro\Backup\Task\Tasks\JobRestore\UpdateSubsitesDomainAndPathTask;
use WPStaging\Pro\Backup\Task\Tasks\JobRestore\UpdateSubsitesUrlsTask;
use WPStaging\Pro\Backup\Task\Tasks\JobRestore\WordPressCom\PreserveWordPressComDataTask;
use WPStaging\Pro\RemoteSync\Dto\Job\PullInitiatorDataDto;
use WPStaging\Pro\RemoteSync\Tasks\DownloadPullDataTask;
use WPStaging\Pro\RemoteSync\Tasks\PullFinishTask;
use WPStaging\Pro\RemoteSync\Tasks\PullInitiatorWaitTask;
use WPStaging\Pro\RemoteSync\Tasks\StartPullTask;

class PullInitiator extends AbstractJob
{
    /** @var string */
    const TMP_DIRECTORY = 'tmp/restore/';

    /** @var PullInitiatorDataDto $jobDataDto */
    protected $jobDataDto;

    /** @var BackupMetadata */
    protected $backupMetadata;

    /** @var array The array of tasks to execute for this job. Populated at init(). */
    protected $tasks = [];

    public static function getJobName()
    {
        return 'pull_initiator';
    }

    protected function getJobTasks()
    {
        return $this->tasks;
    }

    /**
     * @return void
     */
    public function onWpShutdown()
    {
        if ($this->jobDataDto->isFinished()) {
            //WPStaging::make(AnalyticsBackupRestore::class)->enqueueFinishEvent($this->jobDataDto->getId(), $this->jobDataDto);
        }

        parent::onWpShutdown();
    }

    protected function execute()
    {
        try {
            $response = $this->getResponse($this->currentTask->execute());
        } catch (\Exception $e) {
            $this->currentTask->getLogger()->critical('Pull job failed! Error: ' . $e->getMessage());
            $response = $this->getResponse($this->currentTask->generateResponse(false));
        }

        return $response;
    }

    /**
     * @throws \Exception
     * @return void
     */
    protected function init()
    {
        $this->tasks = [];

        $this->setupBackupMetadata();

        $this->tasks[] = StartPullTask::class;
        $this->tasks[] = PullInitiatorWaitTask::class;
        $this->tasks[] = DownloadPullDataTask::class;
        $this->tasks[] = CleanupTmpFilesTask::class;
        if ($this->backupMetadata->getIsExportingDatabase()) {
            $this->tasks[] = CleanupTmpTablesTask::class;
            $this->tasks[] = CleanupBakTablesTask::class;
        }

        $this->tasks[] = RestoreRequirementsCheckTask::class;

        if ($this->backupMetadata->getIsExportingUploads()) {
            $this->tasks[] = CleanExistingMediaTask::class;
        }

        $this->tasks[] = ExtractFilesTask::class;

        if ($this->backupMetadata->getIsExportingThemes()) {
            $this->tasks[] = RestoreThemesTask::class;
        }

        if ($this->backupMetadata->getIsExportingPlugins()) {
            $this->tasks[] = RestorePluginsTask::class;
        }

        if (
            $this->backupMetadata->getIsExportingThemes()
            || $this->backupMetadata->getIsExportingPlugins()
            || $this->backupMetadata->getIsExportingMuPlugins()
            || $this->backupMetadata->getIsExportingOtherWpContentFiles()
        ) {
            $this->tasks[] = RestoreLanguageFilesTask::class;
        }

        if ($this->backupMetadata->getIsExportingOtherWpContentFiles()) {
            $this->tasks[] = RestoreOtherFilesInWpContentTask::class;
        }

        if ($this->backupMetadata->getIsExportingDatabase()) {
            $this->tasks[] = RestoreDatabaseTask::class;
            $this->addMultisiteTasks();
            $this->addWordPressComTasks();
            $this->tasks[] = UpdateBackupsScheduleTask::class;
            $this->addUpdateSiteHomeUrlTask();
            $this->tasks[] = RenameDatabaseTask::class;
            $this->tasks[] = CleanupTmpTablesTask::class;
        }

        if ($this->backupMetadata->getIsExportingMuPlugins()) {
            $this->tasks[] = RestoreMuPluginsTask::class;
        }

        $this->tasks[] = CleanupTmpFilesTask::class;
        $this->tasks[] = PullFinishTask::class;
    }

    /**
     * @return void
     */
    protected function addWordPressComTasks()
    {
        if ($this->backupMetadata->getHostingType() !== SiteInfo::HOSTED_ON_WP) {
            return;
        }

        $this->tasks[] = PreserveWordPressComDataTask::class;
    }

    /**
     * @return void
     */
    protected function addMultisiteTasks()
    {
        if (!is_multisite()) {
            return;
        }

        $backupType = $this->backupMetadata->getBackupType();
        if ($backupType !== BackupMetadata::BACKUP_TYPE_MULTISITE) {
            return;
        }

        $this->tasks[] = UpdateSubsitesDomainAndPathTask::class;
        $this->tasks[] = UpdateSubsitesUrlsTask::class;
        $this->tasks[] = AdjustSubsitesOptionsTask::class;
    }

    /**
     * @return void
     */
    protected function addUpdateSiteHomeUrlTask()
    {
        // Is single site or multisite main site
        if (!is_multisite() || is_main_site()) {
            $this->tasks[] = UpdateSiteHomeUrlTask::class;
        }
    }

    /**
     * @return void
     */
    protected function setupBackupMetadata()
    {
        if (!$this->jobDataDto->getIsDataDownloaded()) {
            $this->backupMetadata = new BackupMetadata();
        } else {
            $this->backupMetadata = (new BackupMetadata())->hydrateByFilePath($this->jobDataDto->getFile());

            if (!$this->isValidMetadata($this->backupMetadata)) {
                throw new RuntimeException('Failed to get backup metadata.');
            }
        }

        $this->jobDataDto->setBackupMetadata($this->backupMetadata);
        $this->jobDataDto->setTmpDirectory($this->getJobTmpDirectory());
        $this->jobDataDto->determineIsSameSiteRestore();
    }

    /**
     * @return string
     */
    protected function getJobTmpDirectory(): string
    {
        $dir = $this->directory->getTmpDirectory() . $this->jobDataDto->getId();
        $this->filesystem->mkdir($dir);

        return trailingslashit($dir);
    }

    /**
     * @param BackupMetadata $this->backupMetadata
     *
     * @return bool
     */
    protected function isValidMetadata(BackupMetadata $backupMetadata): bool
    {
        $extension = pathinfo($this->jobDataDto->getFile(), PATHINFO_EXTENSION);
        if ($extension !== 'sql') {
            return !empty($backupMetadata->getHeaderStart());
        }

        return !empty($backupMetadata->getMaxTableLength()) && !empty($backupMetadata->getMultipartMetadata()->getDatabaseParts());
    }
}
