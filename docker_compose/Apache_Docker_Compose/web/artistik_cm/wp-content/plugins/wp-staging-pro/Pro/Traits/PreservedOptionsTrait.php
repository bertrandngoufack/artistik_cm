<?php

namespace WPStaging\Pro\Traits;

use WPStaging\Backup\BackupRetentionHandler;
use WPStaging\Backup\BackupScheduler;
use WPStaging\Backup\Task\Tasks\JobBackup\FinishBackupTask;
use WPStaging\Framework\BackgroundProcessing\Queue;
use WPStaging\Framework\Security\AccessToken;
use WPStaging\Pro\Auth\TemporaryLogins;
use WPStaging\Pro\License\Version;
use WPStaging\Pro\Staging\AutoLogin\LoginLinkGenerator;
use WPStaging\Staging\CloneOptions;
use WPStaging\Staging\Sites;

/**
 * Provides shared option-name lists used during push preservation and prefix updates.
 */
trait PreservedOptionsTrait
{
    public function getPreservedOptions(): array
    {
        return [
            'wpstg_optimizer_excluded',
            'wpstg_version_upgraded_from',
            'wpstg_version',
            'wpstg_installDate',
            'wpstg_free_install_date',
            'wpstgpro_install_date',
            'wpstgpro_upgrade_date',
            'wpstgpro_version',
            'wpstgpro_version_upgraded_from',
            Version::OPTION_PRO_LATEST_VERSION,
            Queue::QUEUE_TABLE_VERSION_KEY,
            Queue::QUEUE_TABLE_STRUCTURE_VERSION_KEY,
            'upload_path',
            'wpstg_free_upgrade_date',
            'wpstg_googledrive',
            'wpstg_amazons3',
            'wpstg_sftp',
            'wpstg_digitalocean',
            'wpstg_digitalocean-spaces',
            'wpstg_wasabi',
            'wpstg_generic-s3',
            'wpstg_dropbox',
            'wpstg_one-drive',
            'wpstg_pcloud',
            FinishBackupTask::OPTION_LAST_BACKUP,
            BackupScheduler::OPTION_BACKUP_SCHEDULES,
            AccessToken::OPTION_NAME,
            BackupRetentionHandler::OPTION_BACKUPS_RETENTION,
            TemporaryLogins::OPTION_CURRENT_SITE_LOGIN_LINKS,
            LoginLinkGenerator::OPTION_STAGING_PARENT_SITE,
        ];
    }

    public function getPrefixProtectedOptions(): array
    {
        return array_values(array_unique(array_merge(
            $this->getPreservedOptions(),
            [
                Sites::STAGING_SITES_OPTION,
                'wpstg_existing_clones_beta',
                'wpstg_existing_clones',
                'wpstg_login_link_settings',
                CloneOptions::WPSTG_CLONE_SETTINGS_KEY,
                'wpstg_settings',
                'wpstg_license_key',
                'wpstg_license_status',
                'wpstg_connection',
                'wpstg_emails_disabled',
                'wpstg_analytics_modal_dismissed',
                'wpstg_analytics_consent_remind_me',
                'wpstg_last_backup_info',
                'wpstg_missing_cloneName_routine_executed',
                'wpstg_unique_identifier',
                'db_version',
            ]
        )));
    }
}
