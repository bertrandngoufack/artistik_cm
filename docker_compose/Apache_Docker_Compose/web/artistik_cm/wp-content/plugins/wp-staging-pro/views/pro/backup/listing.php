<?php

use WPStaging\Backup\Ajax\ScheduleList;
use WPStaging\Backup\BackupDownload;
use WPStaging\Backup\BackupScheduler;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Facades\Escape;
use WPStaging\Framework\Notices\CliIntegrationNotice;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Pro\License\Licensing;

/**
 * @see \WPStaging\Pro\Backup\Ajax\Listing::render
 *
 * @var TemplateEngine              $this
 * @var array                       $directories
 * @var string                      $urlAssets
 * @var Directory                   $directory
 * @var bool                        $isValidLicense
 * @var bool                        $isProVersion
 * @var bool                        $hasSchedule
 * @var bool                        $isPersonalLicense
 * @var string                      $licenseType
 */

$disabledProperty   = !$isProVersion || $isValidLicense ? '' : 'disabled';
$licensing          = WPStaging::make(Licensing::class);
$canUseRemoteSync   = $licensing->isActiveAgencyOrDeveloperPlan();
$canUseRemoteSync   = $isProVersion && $isValidLicense && $canUseRemoteSync;
$isExpiredDeveloper = $isProVersion && $licensing->isExpiredDeveloperOrAgencyPlan();
$licensePlanName    = WPStaging::make(CliIntegrationNotice::class)->getLicensePlanName();

WPStaging::make(BackupDownload::class)->deleteUnfinishedDownloads();

$storages              = WPStaging::make(\WPStaging\Backup\Storage\Providers::class);
$isEnabledCloudStorage = false;
foreach ($storages->getStorages(true) as $storage) {
    $isActivated = $storages->isActivated($storage['authClass']);
    if ($isActivated) {
        $isEnabledCloudStorage = true;
        break;
    }
}
?>

<?php
/** @var BackupScheduler */
$backupScheduler = WPStaging::make(BackupScheduler::class);
$cronStatus      = $backupScheduler->checkCronStatus();
$cronMessage     = $backupScheduler->getCronMessage();

// Render cron warning notice using modern callout design
require WPSTG_VIEWS_DIR . 'notices/cron-warning-notice.php';

// Will show a locked message if the process is locked
require WPSTG_VIEWS_DIR . 'job/locked.php';

$disabledPropertyCreateBackup = $isLocked ? 'disabled' : '';

?>

<div class="wpstg-did-you-know">
    <?php
    echo Escape::escapeHtml(
        __('<strong>New:</strong> One-click backup restore and migration even if WordPress is down?', 'wp-staging')
    );
    ?>
    </br>
    <?php

    $downloadText = __('Read More or Upgrade to Pro', 'wp-staging');
    $downloadLink = 'https://wp-staging.com/docs/wp-staging-restore/';

    if ($isValidLicense) {
        $downloadText = __('Download Now', 'wp-staging');
        $downloadLink = get_admin_url() . 'admin.php?page=wpstg-restorer';
    }

    printf(
        '%s %s',
        '<span>' . esc_html__('Download WP Staging Restore and Extraction Tool:', 'wp-staging') . '</span>',
        '<a href="' . esc_url($downloadLink) . '">' . esc_html($downloadText) . '</a>'
    );
    ?>
</div>

<!-- Navigation Bar -->
<div id="wpstg-step-1" class="wpstg-flex wpstg-flex-wrap wpstg-items-center wpstg-gap-3 wpstg-mb-6">
    <!-- Primary: Create Backup -->
    <button
        id="wpstg-new-backup"
        class="wpstg-btn wpstg-btn-lg wpstg-btn-primary wpstg-next-step-link"
        <?php echo esc_attr($disabledProperty); ?>
        <?php echo esc_attr($disabledPropertyCreateBackup); ?>
    >
        <svg class="wpstg-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        <?php esc_html_e('Create Backup', 'wp-staging'); ?>
    </button>

    <!-- Secondary: Upload Backup -->
    <button
        type="button"
        id="wpstg-upload-backup"
        class="wpstg-btn wpstg-btn-lg wpstg-btn-secondary"
        <?php echo esc_attr($disabledProperty); ?>
    >
        <svg class="wpstg-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
        </svg>
        <?php esc_html_e('Upload Backup', 'wp-staging'); ?>
    </button>

    <!-- Secondary: Manage Plans -->
    <button
        id="wpstg-manage-backup-schedules"
        class="wpstg-btn wpstg-btn-lg wpstg-btn-secondary"
        <?php echo esc_attr($disabledProperty); ?>
    >
        <svg class="wpstg-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
        </svg>
        <?php esc_html_e('Edit Backup Plans', 'wp-staging'); ?>
    </button>

    <?php if ($isEnabledCloudStorage && $isValidLicense && !$isPersonalLicense) : ?>
    <!-- Secondary: Load Remote Backups -->
    <button
        id="wpstg-show-cloud-backup"
        class="wpstg-btn wpstg-btn-lg wpstg-btn-secondary wpstg-next-step-link"
        <?php echo esc_attr($disabledProperty); ?>
        <?php echo esc_attr($disabledPropertyCreateBackup); ?>
    >
        <svg class="wpstg-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
        </svg>
        <?php esc_html_e('Load Remote Backups', 'wp-staging'); ?>
    </button>
    <?php endif; ?>

    <!-- Remote Sync: Sync from Remote Site -->
    <?php if ($canUseRemoteSync) : ?>
    <button
        id="wpstg-remote-sync"
        class="wpstg-btn wpstg-btn-lg wpstg-btn-tint wpstg-next-step-link"
        <?php echo $isLocked ? 'disabled' : ''; ?>
    >
        <svg class="wpstg-btn-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M12 3v12"/>
            <path d="m8 11 4 4 4-4"/>
            <path d="M8 5H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-4"/>
        </svg>
        <?php esc_html_e('Sync from Remote Site', 'wp-staging'); ?>
    </button>
    <?php elseif ($isExpiredDeveloper) : ?>
    <div class="wpstg-relative wpstg--tooltip">
        <button
            id="wpstg-remote-sync"
            class="wpstg-btn wpstg-btn-lg wpstg-btn-tint wpstg-opacity-60 wpstg-cursor-not-allowed"
            disabled
        >
            <svg class="wpstg-btn-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M12 3v12"/>
                <path d="m8 11 4 4 4-4"/>
                <path d="M8 5H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-4"/>
            </svg>
            <?php esc_html_e('Sync from Remote Site', 'wp-staging'); ?>
        </button>
        <span class="wpstg--tooltiptext wpstg-remote-sync-tooltip" style="width: 350px; line-height: 1.5; margin-top: -1px; white-space: normal;">
            <span class="wpstg-remote-sync-tooltip-thumb"
                  role="button" tabindex="0"
                  aria-label="<?php echo esc_attr__('Play demo video', 'wp-staging'); ?>"
                  data-vimeo-id="1162852843"
                  data-img="<?php echo esc_url($urlAssets); ?>img/thumbnail-small-dark.webp">
                <img class="wpstg-remote-sync-tooltip-thumb-img"
                     src="<?php echo esc_url($urlAssets); ?>img/thumbnail-small-dark.webp"
                     alt="<?php echo esc_attr__('Remote Sync demo', 'wp-staging'); ?>"
                     width="320" height="180" loading="lazy" />
                <span class="wpstg-remote-sync-tooltip-duration">46s</span>
            </span>
            <span class="wpstg-remote-sync-tooltip-plan-info">
                <?php esc_html_e('Your Pro features are paused', 'wp-staging'); ?>
            </span>
            <span class="wpstg-remote-sync-tooltip-plan-info">
                <?php esc_html_e('Your license has expired. Renew to re-enable Remote Sync and other Pro features.', 'wp-staging'); ?>
            </span>
            <span class="wpstg-remote-sync-tooltip-privacy">
                <?php esc_html_e('Video hosted on Vimeo. Loaded only after click.', 'wp-staging'); ?>
            </span>
        </span>
    </div>
    <?php else : ?>
    <div class="wpstg-relative wpstg--tooltip">
        <button
            id="wpstg-remote-sync"
            class="wpstg-btn wpstg-btn-lg wpstg-btn-tint wpstg-opacity-60 wpstg-cursor-not-allowed"
            disabled
        >
            <svg class="wpstg-btn-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M12 3v12"/>
                <path d="m8 11 4 4 4-4"/>
                <path d="M8 5H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-4"/>
            </svg>
            <?php esc_html_e('Sync from Remote Site', 'wp-staging'); ?>
        </button>
        <span class="wpstg--tooltiptext wpstg-remote-sync-tooltip" style="width: 350px; line-height: 1.5; margin-top: -1px; white-space: normal;">
            <span class="wpstg-remote-sync-tooltip-thumb"
                  role="button" tabindex="0"
                  aria-label="<?php echo esc_attr__('Play demo video', 'wp-staging'); ?>"
                  data-vimeo-id="1162852843"
                  data-img="<?php echo esc_url($urlAssets); ?>img/thumbnail-small-dark.webp">
                <img class="wpstg-remote-sync-tooltip-thumb-img"
                     src="<?php echo esc_url($urlAssets); ?>img/thumbnail-small-dark.webp"
                     alt="<?php echo esc_attr__('Remote Sync demo', 'wp-staging'); ?>"
                     width="320" height="180" loading="lazy" />
                <span class="wpstg-remote-sync-tooltip-duration">46s</span>
            </span>
            <span class="wpstg-remote-sync-tooltip-plan-info">
                <?php esc_html_e('Available in Developer and Agency plans.', 'wp-staging'); ?>
            </span>
            <span class="wpstg-remote-sync-tooltip-plan-info">
                <?php
                    /* translators: %s: license plan name, e.g. "Personal" */
                    echo esc_html(sprintf(__('Your current plan: %s.', 'wp-staging'), $licensePlanName));
                ?>
            </span>
            <span class="wpstg-remote-sync-tooltip-privacy">
                <?php esc_html_e('Video hosted on Vimeo. Loaded only after click.', 'wp-staging'); ?>
            </span>
        </span>
    </div>
    <?php endif; ?>
</div>

<div id="wpstg-backup-runs-info">
    <?php WPStaging::make(ScheduleList::class)->renderNextBackupSnippet(); ?>
</div>
<div class="wpstg-backup-listing-container">
    <div id="wpstg-existing-backups">
        <div id="backup-messages"></div>
        <div class="wpstg-backup-list">
            <span id="local-backup-title"><?php echo esc_html__('Local Backups:', 'wp-staging'); ?></span>
            <ul id="wpstg-backup-list-ul">
                <li><?php esc_html_e('Searching for existing backups...', 'wp-staging'); ?></li>
            </ul>
        </div>
    </div>
    <div id="wpstg-existing-cloud-backups">
        <div class="wpstg-existing-cloud-backups-header">
            <span id="remote-backup-title"><?php echo esc_html__('Remote Backups:', 'wp-staging'); ?></span>
        </div>
        <div class="wpstg-cloud-backup-list">
            <ul id="wpstg-cloud-backup-list-ul">
                <li><?php esc_html_e('Searching for remote backups...', 'wp-staging'); ?></li>
            </ul>
            <ul class="wpstg-cloud-backup-empty-message">
                <li id="wpstg-cloud-backup-no-results" class="wpstg-clone wpstg-backup-no-results-cloud-backup wpstg-backup-list-ul">
                    <img class="wpstg--dashicons" src="<?php echo esc_url($urlAssets); ?>svg/cloud.svg" alt="cloud">
                    <div class="no-backups-found-text">
                        <?php esc_html_e('No remote Backups found. Create your first Backup above!', 'wp-staging'); ?>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>
<?php
include(WPSTG_VIEWS_DIR . 'job/modal/process.php');
include(WPSTG_VIEWS_DIR . 'job/modal/success.php');
include(WPSTG_VIEWS_DIR . 'otp/overlay.php');
include(WPSTG_VIEWS_DIR . 'backup/modal/partials/backup-success.php');
include(WPSTG_VIEWS_DIR . 'backup/modal/backup.php');
include(WPSTG_VIEWS_DIR . 'backup/modal/download-modal.php');
include(WPSTG_VIEWS_DIR . 'backup/modal/upload.php');
include(WPSTG_VIEWS_DIR . 'backup/modal/manage-schedules.php');
include(WPSTG_VIEWS_DIR . 'backup/modal/remote-upload.php');
include(WPSTG_VIEWS_DIR . 'backup/modal/edit-schedule-modal.php');
include(WPSTG_VIEWS_DIR . 'backup/modal/restore.php');
include(WPSTG_VIEWS_DIR . 'backup/restore-wait.php');
if ($canUseRemoteSync) {
    include(WPSTG_VIEWS_DIR . 'pro/remote-sync/modal/sync.php');
    include(WPSTG_VIEWS_DIR . 'pro/remote-sync/remote-sync-wait.php');
}
?>
<div id="wpstg-delete-confirmation"></div>
