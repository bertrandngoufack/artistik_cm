<?php

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Facades\Escape;
use WPStaging\Pro\License\Licensing;

$licensing      = WPStaging::make(Licensing::class);
$backupFileName = '';
if (!empty($_GET['hash'])) {
    $backupFileHash                     = sanitize_text_field($_GET['hash']);
    list($backupFileUrl, $backupFileId) = explode(".backupid:", base64_decode($backupFileHash));
    $backupFileName                     = basename($backupFileUrl);
    if (substr($backupFileName, -6) !== '.wpstg') {
        $backupFileName = '';
        $backupFileId   = '';
    }
}

$downloadAttribute = 'download';
if (WPStaging::isOnWordPressPlayground()) {
    $downloadAttribute = 'target=_blank';
}

/**
 * @see WPStaging\Backend\Administrator::getRestorerPage
 *
 * @var object $license
 */
?>
<div class="wpstg_admin" id="wpstg-clonepage-wrapper">
<?php
    require_once(WPSTG_VIEWS_DIR . 'pro/_main/header.php');

    $isActiveRestorerPage = true;
    require_once(WPSTG_VIEWS_DIR . '_main/main-navigation.php');
?>
    <div class="wpstg-metabox-holder wpstg-restorer-wrapper">
        <div class="wpstg-grid wpstg-grid-cols-1 lg:wpstg-grid-cols-2 wpstg-gap-6 wpstg-items-stretch">

            <!-- LEFT COLUMN: Actions -->
            <div class="wpstg-space-y-6 wpstg-flex wpstg-flex-col">
                <?php if ($licensing->isActiveAgencyOrDeveloperPlan()) : ?>
                    <!-- Download Section -->
                    <section class="wpstg-card wpstg-card-body">
                        <h2 class="wpstg-text-base wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100 wpstg-m-0">
                            <?php
                            esc_html_e('Download the Restore Script', 'wp-staging');
                            if (!empty($backupFileName)) {
                                esc_html_e(' and Backup', 'wp-staging');
                            }
                            ?>
                        </h2>
                        <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400"><?php esc_html_e('Download the restore script to restore or migrate a backup on another server.', 'wp-staging'); ?></p>
                        <div class="wpstg-mt-4 wpstg-flex wpstg-flex-wrap wpstg-gap-3 wpstg-items-center">
                            <form method="post" action="<?php echo esc_url(admin_url("admin-post.php?action=wpstg_download_restorer")); ?>" class="wpstg-m-0 wpstg-p-0 wpstg-inline-flex">
                                <input type="submit" name="wpstg-download-restorer" id="wpstg-download-restorer" class="wpstg-btn wpstg-btn-md wpstg-btn-primary" value="<?php esc_html_e('Download Restore Script', 'wp-staging');?>">
                                <?php wp_nonce_field('wpstg_restorer_nonce', 'wpstg_restorer_nonce');?>
                                <?php if (!empty($backupFileName)) : ?>
                                    <input type="hidden" name="backup-file-name" value="<?php echo esc_attr($backupFileName);?>">
                                    <input type="hidden" name="backup-file-id" value="<?php echo esc_attr($backupFileId);?>">
                                <?php endif; ?>
                            </form>
                            <?php if (!empty($backupFileName)) : ?>
                                <a <?php echo esc_attr($downloadAttribute);?> id="wpstg-download-restore-ui-backup" class="wpstg-btn wpstg-btn-md wpstg-btn-secondary wpstg-no-underline" href="<?php echo esc_url($backupFileUrl);?>"><?php esc_html_e('Download Linked Backup', 'wp-staging');?></a>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($backupFileName)) : ?>
                            <p class="wpstg-mt-4 wpstg-mb-0 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400">
                                <?php printf(Escape::escapeHtml(__("<strong>Linked backup file:</strong> <code style='font-size:inherit'>%s</code> &mdash; <strong>Backup ID:</strong> <code style='font-size:inherit'>%s</code>", 'wp-staging')), esc_html($backupFileName), esc_html($backupFileId));?>
                            </p>
                        <?php endif; ?>
                    </section>

                    <!-- Install on This Site Section -->
                    <section class="wpstg-card wpstg-card-body wpstg-flex-1">
                        <h2 class="wpstg-text-base wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100 wpstg-m-0"><?php esc_html_e('Install Restore Script on This Site', 'wp-staging');?></h2>
                        <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400">
                            <?php printf(Escape::escapeHtml(__('Clicking the button below will create the restore file at: <code style="font-size:inherit">%s/wpstg-restore.php</code>', 'wp-staging')), esc_url(get_site_url()));?>
                        </p>
                        <div class="wpstg-callout wpstg-callout-warning wpstg-mt-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                            <p class="wpstg-m-0 wpstg-text-sm"><?php echo Escape::escapeHtml(__("<strong>Important:</strong> Delete the restore script file manually when it's no longer needed.", 'wp-staging'));?></p>
                        </div>
                        <div class="wpstg-mt-4">
                            <form method="post" action="<?php echo esc_url(admin_url("admin-post.php?action=wpstg_download_restorer")); ?>" target=_blank class="wpstg-m-0 wpstg-p-0 wpstg-inline-flex">
                                <input type="submit" name="wpstg-download-restorer" id="wpstg-install-restorer" class="wpstg-btn wpstg-btn-md wpstg-btn-primary" value="<?php esc_html_e('Install Restore Script on This Site', 'wp-staging');?>">
                                <?php wp_nonce_field('wpstg_restorer_nonce', 'wpstg_restorer_nonce');?>
                                <?php if (!empty($backupFileName)) : ?>
                                    <input type="hidden" name="backup-file-name" value="<?php echo esc_attr($backupFileName);?>">
                                    <input type="hidden" name="backup-file-id" value="<?php echo esc_attr($backupFileId);?>">
                                <?php endif; ?>
                                <input type="hidden" name="copy-to-current-site" value="<?php echo esc_url(get_site_url());?>">
                            </form>
                        </div>
                        <p class="wpstg-mt-4 wpstg-mb-0 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400">
                            <?php printf(Escape::escapeHtml(__('Read the <a href="%s" target="new" rel="noopener">documentation</a> for detailed instructions.', 'wp-staging')), 'https://wp-staging.com/docs/wp-staging-restore/');?>
                        </p>
                    </section>
                <?php else : ?>
                    <!-- Inactive License -->
                    <section class="wpstg-card wpstg-card-body">
                        <h2 class="wpstg-text-base wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100 wpstg-m-0"><?php esc_html_e('Download the Restore Script', 'wp-staging');?></h2>
                        <div class="wpstg-callout wpstg-callout-warning wpstg-mt-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                            <div class="wpstg-text-sm">
                                <p class="wpstg-m-0"><?php printf(esc_html__('You need a valid Developer or Agency license to download %s.', 'wp-staging'), 'WP Staging Restore');?></p>
                                <p class="wpstg-m-0 wpstg-mt-2">
                                    <?php
                                    printf(
                                        Escape::escapeHtml(
                                            __('Please <a href="%s">activate</a> your license key or purchase a license at <a href="%s" rel="noopener" target="new">wp-staging.com</a>', 'wp-staging')
                                        ),
                                        esc_url(get_admin_url()) . 'admin.php?page=wpstg-license',
                                        'https://wp-staging.com/'
                                    );
                                    ?>
                                </p>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>
            </div>

            <!-- RIGHT COLUMN: Info -->
            <section class="wpstg-card wpstg-card-body">
                <h2 class="wpstg-text-base wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100 wpstg-m-0"><?php esc_html_e('What is WP Staging Restore?', 'wp-staging');?></h2>
                <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400">
                    <?php printf(esc_html__('%s is a standalone recovery tool for WP Staging Pro license holders. It works independently from WordPress and can perform the following tasks:', 'wp-staging'), 'WP Staging Restore');?>
                </p>
                <ul class="wpstg-mt-2 wpstg-text-sm wpstg-text-slate-700 dark:wpstg-text-slate-300">
                    <li><strong><?php esc_html_e('Extract backups:', 'wp-staging'); ?></strong> <?php esc_html_e('Extract a backup to inspect its contents or retrieve individual files.', 'wp-staging');?></li>
                    <li><strong><?php esc_html_e('Restore backups:', 'wp-staging'); ?></strong> <?php esc_html_e('Restore a backup, even if your WordPress site is broken or cannot load.', 'wp-staging');?></li>
                    <li><strong><?php esc_html_e('Install & Migrate:', 'wp-staging'); ?></strong> <?php esc_html_e('Set up WordPress on a new server and restore your backup there.', 'wp-staging');?></li>
                </ul>

                <hr class="wpstg-my-4 wpstg-border-gray-200 dark:wpstg-border-gray-700">

                <h3 class="wpstg-text-sm wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100 wpstg-m-0"><?php esc_html_e('Getting Started', 'wp-staging');?></h3>
                <ol class="wpstg-mt-3 wpstg-text-sm wpstg-text-slate-700 dark:wpstg-text-slate-300 wpstg-space-y-2">
                    <li><?php echo Escape::escapeHtml(__('Download <code style="font-size:inherit">wpstg-restore.php</code> (and optionally the backup file).', 'wp-staging')); ?></li>
                    <li><?php esc_html_e("Upload the restore file to your website's main folder using FTP or your hosting file manager.", 'wp-staging');?></li>
                    <li><?php echo Escape::escapeHtml(__('Open <code style="font-size:inherit">https://yoursite.com/wpstg-restore.php</code> in your browser.', 'wp-staging')); ?></li>
                    <li><?php esc_html_e('Log in with your license key or your exact backup file name.', 'wp-staging'); ?></li>
                </ol>
                <p class="wpstg-mt-4 wpstg-mb-0 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400">
                    <strong><?php esc_html_e('Where does it look for backups?', 'wp-staging');?></strong>
                </p>
                <ul class="wpstg-mt-2 wpstg-text-sm wpstg-text-slate-700 dark:wpstg-text-slate-300">
                    <li><?php esc_html_e('Your WordPress root directory', 'wp-staging');?></li>
                    <li><?php echo Escape::escapeHtml(__('Default backup directory: <code style="font-size:inherit">wp-content/uploads/wp-staging/backups/</code>', 'wp-staging')); ?></li>
                </ul>
            </section>
        </div>
    </div>
    <?php require_once(WPSTG_VIEWS_DIR . '_main/footer.php'); ?>
</div>
