<?php

namespace WPStaging\Pro\Backup;

use WPStaging\Backup\Dto\Job\JobBackupDataDto;
use WPStaging\Pro\Backup\Dto\Job\JobExtractDataDto;
use WPStaging\Backup\Dto\Job\JobRestoreDataDto;
use WPStaging\Backup\Job\JobBackupProvider;
use WPStaging\Backup\Job\JobExtractProvider;
use WPStaging\Backup\Job\JobRestoreProvider;
use WPStaging\Backup\Service\Archiver;
use WPStaging\Backup\Service\BackupSigner;
use WPStaging\Backup\Service\Database\DatabaseImporter;
use WPStaging\Backup\Service\Compression\CompressionInterface;
use WPStaging\Backup\Service\Database\Exporter\DDLExporterProvider;
use WPStaging\Backup\Service\Database\Exporter\RowsExporterProvider;
use WPStaging\Backup\Service\Database\Importer\DatabaseSearchReplacerInterface;
use WPStaging\Backup\Service\Database\Importer\SubsiteManagerInterface;
use WPStaging\Backup\Service\FileBackupServiceProvider;
use WPStaging\Backup\Service\ServiceInterface;
use WPStaging\Backup\Service\ZlibCompressor;
use WPStaging\Backup\Task\Tasks\JobBackup\IncludeDatabaseTask;
use WPStaging\Backup\Task\Tasks\JobBackup\SignBackupTask;
use WPStaging\Framework\Database\Exporter\AbstractExporter;
use WPStaging\Framework\DI\ServiceProvider;
use WPStaging\Framework\Job\AbstractJob;
use WPStaging\Framework\Job\Dto\JobDataDto;
use WPStaging\Pro\Backup\Ajax\ManageSchedules;
use WPStaging\Pro\Backup\Ajax\RemoteUpload;
use WPStaging\Pro\Backup\Ajax\RemoteUpload\PrepareRemoteUpload;
use WPStaging\Pro\Backup\Dto\Job\JobRemoteUploadDataDto;
use WPStaging\Pro\Backup\Job\Jobs\JobBackup;
use WPStaging\Pro\Backup\Job\Jobs\JobRemoteUpload;
use WPStaging\Pro\Backup\Job\Jobs\JobRestore;
use WPStaging\Pro\Backup\Service\BackupSigner as ProBackupSigner;
use WPStaging\Pro\Backup\Service\Compression\ZlibService;
use WPStaging\Pro\Backup\Service\Database\Exporter\DDLExporter;
use WPStaging\Pro\Backup\Service\Database\Exporter\RowsExporter;
use WPStaging\Pro\Backup\Service\Database\Importer\DatabaseSearchReplacer;
use WPStaging\Pro\Backup\Service\Database\Importer\SubsiteManager;
use WPStaging\Pro\Backup\Service\FileBackupService;
use WPStaging\Pro\Backup\Storage\StoragesServiceProvider;
use WPStaging\Pro\Backup\Task\Tasks\JobRestore\RestoreDatabaseTask;
use WPStaging\Pro\Backup\Ajax\FileList;
use WPStaging\Pro\Backup\Ajax\Listing;
use WPStaging\Pro\Backup\Service\Archiver as ProArchiver;
use WPStaging\Pro\Backup\Ajax\Extract;
use WPStaging\Pro\Backup\Ajax\Extract\PrepareExtract;
use WPStaging\Pro\Backup\Job\Jobs\JobExtract;

/**
 * Class BackupServiceProvider
 * @package WPStaging\Pro\Backup
 *
 * This class is used to register all the services related to the Backup feature that are PRO only features like
 * Multisite Support, Multipart Backups, Remote Storages, Migration, Multiple Backup Schedules etc etc
 */
class BackupServiceProvider extends ServiceProvider
{
    protected function registerClasses()
    {
        $this->container->when(JobBackup::class)
                ->needs(JobDataDto::class)
                ->give(JobBackupDataDto::class);

        $this->container->when(JobRestore::class)
                ->needs(JobDataDto::class)
                ->give(JobRestoreDataDto::class);

        $this->container->when(JobExtract::class)
            ->needs(JobDataDto::class)
            ->give(JobExtractDataDto::class);

        $this->container->when(ZlibCompressor::class)
                ->needs(CompressionInterface::class)
                ->give(ZlibService::class);

        $this->container->when(JobRemoteUpload::class)
                ->needs(JobDataDto::class)
                ->give(JobRemoteUploadDataDto::class);

        $this->container->register(StoragesServiceProvider::class);

        $container = $this->container;

        $this->container->when(JobBackupProvider::class)
                        ->needs(AbstractJob::class)
                        ->give(function () use (&$container) {
                            return $container->make(JobBackup::class);
                        });

        $this->container->when(JobRestoreProvider::class)
                        ->needs(AbstractJob::class)
                        ->give(function () use (&$container) {
                            return $container->make(JobRestore::class);
                        });

        $this->container->when(JobExtractProvider::class)
                        ->needs(AbstractJob::class)
                        ->give(function () use (&$container) {
                            return $container->make(JobExtract::class);
                        });

        $this->container->when(FileBackupServiceProvider::class)
                        ->needs(ServiceInterface::class)
                        ->give(function () use (&$container) {
                            return $container->make(FileBackupService::class);
                        });

        $this->container->when(DDLExporterProvider::class)
                        ->needs(AbstractExporter::class)
                        ->give(function () use (&$container) {
                            return $container->make(DDLExporter::class);
                        });

        $this->container->when(RowsExporterProvider::class)
                        ->needs(AbstractExporter::class)
                        ->give(function () use (&$container) {
                            return $container->make(RowsExporter::class);
                        });

        $this->container->when(RestoreDatabaseTask::class)
                        ->needs(DatabaseSearchReplacerInterface::class)
                        ->give(DatabaseSearchReplacer::class);

        $this->container->when(IncludeDatabaseTask::class)
                        ->needs(Archiver::class)
                        ->give(ProArchiver::class);

        $this->container->when(DatabaseImporter::class)
                        ->needs(SubsiteManagerInterface::class)
                        ->give(SubsiteManager::class);

        $this->container->when(SignBackupTask::class)
                        ->needs(BackupSigner::class)
                        ->give(ProBackupSigner::class);
    }

    protected function addHooks()
    {
        add_action('wp_ajax_wpstg--backups-edit-schedule', $this->container->callback(ManageSchedules::class, 'editSchedule'), 10, 1); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('wp_ajax_wpstg--backups-edit-schedule-modal', $this->container->callback(ManageSchedules::class, 'editScheduleModal'), 10, 1); // phpcs:ignore WPStaging.Security.AuthorizationChecked

        add_action('wp_ajax_wpstg--backups--prepare-remote-upload', $this->container->callback(PrepareRemoteUpload::class, 'ajaxPrepare')); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('wp_ajax_wpstg--backups--remote-upload', $this->container->callback(RemoteUpload::class, 'render')); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('wp_ajax_wpstg--backups--listing', $this->container->callback(Listing::class, 'render')); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('wp_ajax_wpstg--backups--restore--file-list', $this->container->callback(FileList::class, 'render')); // phpcs:ignore WPStaging.Security.AuthorizationChecked

        add_action('wp_ajax_wpstg--backups--prepare-extract', $this->container->callback(PrepareExtract::class, 'ajaxPrepare')); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('wp_ajax_wpstg--backups--extract', $this->container->callback(Extract::class, 'render')); // phpcs:ignore WPStaging.Security.AuthorizationChecked
    }
}
