<?php

namespace WPStaging\Pro\Backup\Storage;

use Exception;
use WPStaging\Backend\Administrator;
use WPStaging\Framework\DI\ServiceProvider;
use WPStaging\Framework\Job\ProcessLock;
use WPStaging\Framework\Notices\Notices;
use WPStaging\Framework\Security\Auth;
use WPStaging\Framework\Utils\Sanitize;
use WPStaging\Pro\Backup\Storage\GoogleDrive\Auth as GoogleDriveStorage;
use WPStaging\Pro\Backup\Storage\Dropbox\Auth as DropboxStorage;
use WPStaging\Pro\Backup\Storage\OneDrive\Auth as OneDriveStorage;
use WPStaging\Pro\Backup\Storage\PCloud\Auth as PCloudStorage;
use WPStaging\Pro\Backup\Ajax\Download;
use WPStaging\Pro\Backup\Ajax\CloudFileList;
use WPStaging\Pro\Backup\Ajax\CancelDownload;
use WPStaging\Pro\Backup\Task\Tasks\JobCloudDownload\AmazonS3DownloadTask;
use WPStaging\Pro\Backup\Storage\RemoteDownloaderInterface;
use WPStaging\Pro\Backup\Storage\Amazon\S3Downloader;
use WPStaging\Pro\Backup\Storage\DigitalOceanSpaces\Downloader as DigitalOceanDownloader;
use WPStaging\Pro\Backup\Storage\GenericS3\Downloader as GenericS3Downloader;
use WPStaging\Pro\Backup\Storage\Wasabi\Downloader as WasabiDownloader;
use WPStaging\Pro\Backup\Storage\GoogleDrive\Downloader as GoogleDriveDownloader;
use WPStaging\Pro\Backup\Storage\Dropbox\Downloader as DropboxDownloader;
use WPStaging\Pro\Backup\Storage\OneDrive\Downloader as OneDriveDownloader;
use WPStaging\Pro\Backup\Storage\SFTP\Downloader as SftpDownloader;
use WPStaging\Pro\Backup\Storage\PCloud\Downloader as PCloudDownloader;
use WPStaging\Pro\Backup\Task\Tasks\JobCloudDownload\DigitalOceanSpacesDownloadTask;
use WPStaging\Pro\Backup\Task\Tasks\JobCloudDownload\GenericS3DownloadTask;
use WPStaging\Pro\Backup\Task\Tasks\JobCloudDownload\GoogleDriveDownloadTask;
use WPStaging\Pro\Backup\Task\Tasks\JobCloudDownload\DropboxDownloadTask;
use WPStaging\Pro\Backup\Task\Tasks\JobCloudDownload\OneDriveDownloadTask;
use WPStaging\Pro\Backup\Task\Tasks\JobCloudDownload\SFTPDownloadTask;
use WPStaging\Pro\Backup\Task\Tasks\JobCloudDownload\WasabiDownloadTask;
use WPStaging\Pro\Backup\Task\Tasks\JobCloudDownload\PCloudDownloadTask;

class StoragesServiceProvider extends ServiceProvider
{
    protected function registerClasses()
    {
        $this->container->singleton(SettingsTab::class);

        $this->container->when(AmazonS3DownloadTask::class)
                        ->needs(RemoteDownloaderInterface::class)
                        ->give(S3Downloader::class);
        $this->container->when(DigitalOceanSpacesDownloadTask::class)
                        ->needs(RemoteDownloaderInterface::class)
                        ->give(DigitalOceanDownloader::class);
        $this->container->when(GenericS3DownloadTask::class)
                        ->needs(RemoteDownloaderInterface::class)
                        ->give(GenericS3Downloader::class);
        $this->container->when(GoogleDriveDownloadTask::class)
                        ->needs(RemoteDownloaderInterface::class)
                        ->give(GoogleDriveDownloader::class);
        $this->container->when(DropboxDownloadTask::class)
                        ->needs(RemoteDownloaderInterface::class)
                        ->give(DropboxDownloader::class);
        $this->container->when(OneDriveDownloadTask::class)
                        ->needs(RemoteDownloaderInterface::class)
                        ->give(OneDriveDownloader::class);
        $this->container->when(SFTPDownloadTask::class)
                        ->needs(RemoteDownloaderInterface::class)
                        ->give(SftpDownloader::class);
        $this->container->when(WasabiDownloadTask::class)
                        ->needs(RemoteDownloaderInterface::class)
                        ->give(WasabiDownloader::class);
        $this->container->when(PCloudDownloadTask::class)
                        ->needs(RemoteDownloaderInterface::class)
                        ->give(PCloudDownloader::class);
    }

    protected function addHooks()
    {
        add_filter(Administrator::FILTER_MAIN_SETTING_TABS, $this->container->callback(SettingsTab::class, 'addRemoteStoragesSettingsTab'), 10, 1);
        add_action(GoogleDriveStorage::ACTION_ADMIN_POST_GOOGLEDRIVE_AUTH, $this->container->callback(GoogleDriveStorage::class, 'authenticate'), 10, 0); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action(GoogleDriveStorage::ACTION_ADMIN_POST_GOOGLEDRIVE_API_AUTH, $this->container->callback(GoogleDriveStorage::class, 'apiAuthenticate'), 10, 0); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('wp_ajax_wpstg-provider-authenticate', $this->container->callback(StorageBase::class, 'authenticate'), 10, 0); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('wp_ajax_wpstg-provider-revoke', $this->container->callback(StorageBase::class, 'revoke'), 10, 0); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('wp_ajax_wpstg-provider-settings', $this->container->callback(StorageBase::class, 'updateSettings'), 10, 0); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('wp_ajax_wpstg-provider-test-connection', $this->container->callback(StorageBase::class, 'testConnection'), 10, 0); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action(Notices::ACTION_ALL_ADMIN_NOTICES, $this->container->callback(GoogleDriveStorage::class, 'showAdminNotices'), 10, 0); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action(Notices::ACTION_ALL_ADMIN_NOTICES, $this->container->callback(DropboxStorage::class, 'showAdminNotices'), 10);// phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action(Notices::ACTION_ALL_ADMIN_NOTICES, $this->container->callback(OneDriveStorage::class, 'showAdminNotices'), 10);// phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action(DropboxStorage::ACTION_ADMIN_POST_DROPBOX_AUTH, $this->container->callback(DropboxStorage::class, 'authenticate'), 10, 0);// phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action(OneDriveStorage::ACTION_ADMIN_POST_ONEDRIVE_AUTH, $this->container->callback(OneDriveStorage::class, 'authenticate'), 10, 0);// phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action(Notices::ACTION_ALL_ADMIN_NOTICES, $this->container->callback(PCloudStorage::class, 'showAdminNotices'), 10);// phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action(PCloudStorage::ACTION_ADMIN_POST_PCLOUD_AUTH, $this->container->callback(PCloudStorage::class, 'authenticate'), 10, 0);// phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('wp_ajax_wpstg--backups--download--cloud-backup', $this->container->callback(Download::class, 'render')); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('wp_ajax_wpstg--backups--cloud--delete', $this->container->callback(CloudFileList::class, 'deleteCloudFile')); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('wp_ajax_wpstg--backups--cloud--file-list', $this->container->callback(CloudFileList::class, 'render')); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('wp_ajax_wpstg--storage--list', $this->container->callback(CloudFileList::class, 'getStorageList')); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('wp_ajax_wpstg--download--cancel', $this->container->callback(CancelDownload::class, 'render')); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('wp_ajax_wpstg-provider-delete-settings', $this->container->callback(StorageBase::class, 'deleteSettings'), 10, 0); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('wp_ajax_wpstg-provider-remove-credential', $this->container->callback(StorageBase::class, 'removeCredential'), 10, 0); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('wp_ajax_wpstg-render-storage-settings', $this->container->callback(StorageSettings::class, 'ajaxRenderStorageSettings'), 10, 0); // phpcs:ignore WPStaging.Security.AuthorizationChecked
    }
}
