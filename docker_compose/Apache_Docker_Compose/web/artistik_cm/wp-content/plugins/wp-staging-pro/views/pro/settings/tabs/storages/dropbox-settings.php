<?php

/**
 * @var string $providerId
 * @var string $redirectTo
 */

use WPStaging\Framework\Facades\Escape;
use WPStaging\Pro\Backup\Storage\Dropbox\Auth;

if (empty($redirectTo)) {
    $redirectTo = "";
}
?>
<fieldset>
    <?php
    /** @var Auth */
    $dropboxStorage         = \WPStaging\Core\WPStaging::make(Auth::class);
    $isDropboxAuthenticated = $dropboxStorage->isAuthenticated();
    $options                = $dropboxStorage->getOptions();
    $maxBackupsToKeep       = isset($options['maxBackupsToKeep']) ? $options['maxBackupsToKeep'] : 2;
    $folderName             = empty($options['folderName']) ? Auth::FOLDER_NAME : $options['folderName'];
    $lastUpdated            = empty($options['lastUpdated']) ? 0 : $options['lastUpdated'];
    ?>
    <div class="wpstg-bg-white dark:wpstg-bg-[#141b27] wpstg-provider-settings-container">
        <div class="wpstg-max-w-3xl wpstg-py-1 wpstg-space-y-6 wpstg-provider-settings-container-inner">
            <header>
                <h1 class="wpstg-text-2xl wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100"><?php echo esc_html__('Dropbox', 'wp-staging'); ?></h1>
                <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400"><?php echo esc_html__('Upload backup files to your Dropbox account.', 'wp-staging'); ?></p>
            </header>

            <section class="wpstg-card wpstg-card-body">
                    <h2 class="wpstg-text-base wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100 wpstg-m-0"><?php echo esc_html__('Authentication', 'wp-staging'); ?></h2>
                    <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400"><?php echo esc_html__('Connect your Dropbox account to enable backup uploads.', 'wp-staging'); ?></p>
                    <?php
                    if ($isDropboxAuthenticated) {
                        ?>
                        <div class="wpstg-mt-4">
                            <strong class="wpstg-mr-10px">
                                <?php
                                esc_html_e('You are authenticated to Dropbox.', 'wp-staging');
                                ?>
                            </strong>
                            <form class="wpstg-provider-revoke-form" id="wpstg-provider-revoke-form" method="post">
                                <input type="hidden" name="provider" value="<?php echo esc_attr($providerId); ?>" />
                                <button type="button" id="wpstg-btn-provider-revoke" class="wpstg-button--primary wpstg-button--blue"><?php esc_html_e("Logout from Dropbox", "wp-staging") ?></button>
                            </form>
                        </div>
                        <?php
                    } else {
                        $authURL = $dropboxStorage->getAuthenticationURL($redirectTo);
                        ?>
                        <div class="wpstg-mt-4">
                            <a href="<?php echo esc_url($authURL); ?>" class="wpstg-btn-dropbox">
                                <img class="wpstg--dashicons" src="<?php echo esc_url(WPSTG_PLUGIN_URL . 'assets/svg/dropbox-sign-in.svg'); ?>" alt="<?php esc_attr_e("Sign in with Dropbox", "wp-staging") ?>"/>
                            </a>
                        </div>
                        <?php
                    }
                    ?>
                </section>
            <?php if ($isDropboxAuthenticated) : ?>
                <form class="wpstg-space-y-6 wpstg-provider-settings-form" id="wpstg-provider-settings-form" method="post">
                    <input type="hidden" name="provider" value="<?php echo esc_attr($providerId); ?>" />
                    <section class="wpstg-card wpstg-card-body">
                        <h2 class="wpstg-text-base wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100 wpstg-m-0"><?php esc_html_e('Upload Settings', 'wp-staging') ?></h2>
                        <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400"><?php echo esc_html__('Configure where and how many backups to store.', 'wp-staging'); ?></p>
                        <div class="wpstg-mt-4 wpstg-grid wpstg-gap-4">
                            <div>
                                <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-max-backups-to-keep" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php esc_html_e('Max Backups to Keep', 'wp-staging') ?></label>
                                <input id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-max-backups-to-keep" class="wpstg-input wpstg-input-md wpstg-storage-backup-retention-field" type="number" name="max_backups_to_keep" value="<?php echo esc_attr($maxBackupsToKeep); ?>" min="1" />
                                <p class="wpstg-mt-1 wpstg-mb-0 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400"><?php echo esc_html__('Older backups are automatically deleted when this limit is reached.', 'wp-staging'); ?></p>
                            </div>
                            <div>
                                <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-folder-name" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php esc_html_e('Backup Location', 'wp-staging') ?></label>
                                <div class="wpstg-input-group">
                                    <span class="wpstg-input-prefix">/</span>
                                    <input id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-folder-name" class="wpstg-input wpstg-input-md wpstg-storage-provider-input-field" type="text" placeholder="backups" name="folder_name" value="<?php echo esc_attr($folderName); ?>" />
                                </div>
                                <p class="wpstg-mt-1 wpstg-mb-0 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400"><?php echo esc_html__('The directory will be created automatically if it doesn\'t exist.', 'wp-staging'); ?></p>
                            </div>
                        </div>
                        <?php require_once WPSTG_VIEWS_DIR . "pro/settings/tabs/storages/storage-notice.php";?>
                    </section>
                    <section class="wpstg-card wpstg-card-body">
                        <div class="wpstg-storage-provider-action-container">
                            <button type="button" id="wpstg-btn-save-provider-settings" class="wpstg-btn wpstg-btn-md wpstg-btn-primary"><?php esc_html_e("Save Settings", "wp-staging") ?></button><?php require_once WPSTG_VIEWS_DIR . "pro/settings/tabs/storages/last-saved-notice.php"; ?>
                        </div>
                    </section>
                </form>
            <?php endif; ?>
            <footer class="wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400">
                <?php echo sprintf(
                    Escape::escapeHtml(__('Your backup data will not be sent to us! %s.', 'wp-staging')),
                    '<a href="https://wp-staging.com/privacy-policy/#Dropbox" target="_blank">' . esc_html__('Our privacy policy', 'wp-staging') . '</a>'
                ); ?>
            </footer>
        </div>
    </div>
