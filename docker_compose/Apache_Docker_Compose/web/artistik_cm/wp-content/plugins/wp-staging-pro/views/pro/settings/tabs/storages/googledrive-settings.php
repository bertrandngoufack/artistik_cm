<?php

/**
 * @var string $providerId
 * @var string $redirectTo
 */

use WPStaging\Core\WPStaging;
use WPStaging\Pro\Backup\Storage\GoogleDrive\Auth;

if (empty($redirectTo)) {
    $redirectTo = "";
}
?>
<fieldset>
    <?php
    /** @var Auth */
    $googleDriveStorage = WPStaging::make(Auth::class);
    $assetsUrl          = trailingslashit(WPSTG_PLUGIN_URL) . 'assets/';

    $providerName = 'Google Drive';
    if ($googleDriveStorage->isEncrypted()) {
        require_once WPSTG_VIEWS_DIR . "pro/settings/tabs/storages/encrypted-notice.php";
    }

    $isGoogleDriveAuthenticated = $googleDriveStorage->isAuthenticated();
    $options                    = $googleDriveStorage->getOptions();

    $maxBackupsToKeep   = isset($options['maxBackupsToKeep']) ? $options['maxBackupsToKeep'] : 2;
    $maxBackupsToKeep   = $maxBackupsToKeep > 0 ? $maxBackupsToKeep : 15;
    $folderName         = empty($options['folderName']) ? Auth::FOLDER_NAME : $options['folderName'];
    $lastUpdated        = empty($options['lastUpdated']) ? 0 : $options['lastUpdated'];
    $googleClientId     = isset($options['googleClientId']) ? $options['googleClientId'] : '';
    $googleClientSecret = isset($options['googleClientSecret']) ? $options['googleClientSecret'] : '';
    $displayStyle       = $isGoogleDriveAuthenticated ? 'display:block' : 'display:none';
    $sharedDriveStyle   = (empty($options['driveType']) || $options['driveType'] === Auth::DRIVE_TYPE_PERSONAL) ? 'display:none' : 'display:block';
    ?>
    <div class="wpstg-bg-white dark:wpstg-bg-[#141b27] wpstg-provider-settings-container">
        <div class="wpstg-max-w-3xl wpstg-py-1 wpstg-space-y-6 wpstg-provider-settings-container-inner">
            <header>
                <h1 class="wpstg-text-2xl wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100"><?php echo esc_html($providerName); ?></h1>
                <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400">
                    <?php esc_html_e('Upload backup files to your personal Google Drive account.', 'wp-staging'); ?>
                </p>
            </header>

            <!-- Authentication Card -->
            <section class="wpstg-card wpstg-card-body">
                <h2 class="wpstg-text-base wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100 wpstg-m-0"><?php esc_html_e('Authentication', 'wp-staging'); ?></h2>
                <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400"><?php esc_html_e('Connect your Google Drive account to enable backup uploads.', 'wp-staging'); ?></p>
                <div>
                    <?php
                    if ($isGoogleDriveAuthenticated) {
                        ?>
                        <p>
                        <strong class="wpstg-mr-10px">
                            <?php esc_html_e('You are already authenticated to Google Drive.', 'wp-staging'); ?>
                            <br>
                            <?php esc_html_e('Remove storage settings to reconnect if you have uploading issues to Google Drive.', 'wp-staging'); ?>
                        </strong>
                        </p>
                        <form class="wpstg-provider-revoke-form" id="wpstg-provider-revoke-form" method="post">
                            <input type="hidden" name="provider" value="<?php echo esc_attr($providerId); ?>" />
                            <button type="button" id="wpstg-btn-provider-revoke" class="wpstg-button--primary wpstg-button--blue"><?php esc_html_e('Remove Google Drive Settings', 'wp-staging'); ?></button>
                        </form>

                        <div class="wpstg-callout wpstg-callout-warning wpstg-mt-4">
                            <p class="wpstg-m-0">
                            <?php echo sprintf(
                                esc_html__('If you want to deauthorize WP Staging from accessing your Google Drive, do so in your %s. %s this action will disconnect all other connected websites, and each one will need to be reconnected, especially important for agencies and users managing many websites.', 'wp-staging'),
                                '<a href="https://myaccount.google.com/permissions" target="_blank">' . esc_html__('Google account settings', 'wp-staging') . '</a>',
                                '<br><strong>' . esc_html__('Please be cautious:', 'wp-staging') . '</strong>'
                            ); ?>
                            </p>
                        </div>
                        <?php
                    } else {
                        ?>
                        <div class="wpstg-form-group">
                            <?php
                            $authURL = $googleDriveStorage->getAuthenticationURL($redirectTo);
                            if ($authURL === false) {
                                ?>
                                <div class="wpstg-form-group">
                                    <b class="wpstg-error"><?php esc_html_e('Unable to generate Google Authentication URL. Google API keys are not correct!', 'wp-staging'); ?></b>
                                </div>
                                <?php
                            }
                            ?>
                            <div class="wpstg-form-group">
                                <a href="<?php echo esc_url($authURL); ?>" class="wpstg-btn-google" id="wpstg-google-auth-button">
                                    <img class="wpstg--dashicons" src="<?php echo esc_url($assetsUrl); ?>svg/google-sign-in.svg" alt="<?php esc_attr_e("Sign in with Google", "wp-staging"); ?>"/>
                                </a>
                                <span><?php esc_html_e("OR", "wp-staging"); ?></span> &nbsp; <a id="wpstg-google-api-credentials" class="wpstg-ml-12px" onclick="WPStaging.handleToggleElement(this)" data-wpstg-target="#wpstg-custom-google-credentials" href="javascript:void(0);"><?php esc_html_e("Connect with API Credentials", "wp-staging"); ?></a>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </section>

            <form class="wpstg-space-y-6 wpstg-provider-settings-form" id="wpstg-provider-settings-form" method="post" style="<?php echo esc_attr($displayStyle); ?>">
                <input type="hidden" name="provider" value="<?php echo esc_attr($providerId); ?>" />

                <!-- API Credentials Card -->
                <div class="hidden" id="wpstg-custom-google-credentials">
                    <section class="wpstg-card wpstg-card-body">
                        <h2 class="wpstg-text-base wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100 wpstg-m-0"><?php esc_html_e('API Keys', 'wp-staging'); ?></h2>
                        <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400">
                            <?php printf(
                                esc_html__('You can use your own Google API keys. This is optional. %s', 'wp-staging'),
                                '<a href="https://wp-staging.com/docs/create-google-api-credentials-to-authenticate-to-google-drive/" target="_blank" rel="noopener noreferrer">' .
                                esc_html__('How to create your own Google API keys', 'wp-staging') .
                                '</a>'
                            );?>
                        </p>

                        <div class="wpstg-mt-4 wpstg-grid wpstg-gap-4">
                            <div>
                                <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-client-id" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php esc_html_e('Google Client Id', 'wp-staging'); ?></label>
                                <input id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-client-id" class="wpstg-input wpstg-input-md wpstg-google-api-credential-input" type="text" name="google_client_id" value="<?php echo esc_attr($googleClientId); ?>" />
                            </div>

                            <div>
                                <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-client-secret" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php esc_html_e('Google Client Secret', 'wp-staging'); ?></label>
                                <input id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-client-secret" class="wpstg-input wpstg-input-md wpstg-google-api-credential-input" type="text" name="google_client_secret" value="<?php echo esc_attr($googleClientSecret); ?>" />
                            </div>

                            <div>
                                <label for="wpstg-google-redirect-uri" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php esc_html_e('Google Redirect URI', 'wp-staging'); ?></label>
                                <div style="position: relative;">
                                    <input class="wpstg-input wpstg-input-md wpstg-google-api-credential-input" type="text" name="google_redirect_uri" id="wpstg-google-redirect-uri" value="<?php echo esc_url($googleDriveStorage->getRedirectURI()); ?>" disabled style="padding-right: 80px;" />
                                    <button type="button" id="wpstg-google-redirect-copy-btn" class="wpstg-absolute wpstg-top-1 wpstg-right-1 wpstg-btn wpstg-btn-sm wpstg-btn-ghost wpstg-flex wpstg-items-center wpstg-gap-1" style="border-radius: 0.5rem;" aria-label="<?php echo esc_attr__('Copy to Clipboard', 'wp-staging'); ?>">
                                        <svg class="wpstg-copy-icon wpstg-h-4 wpstg-w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        <svg class="wpstg-check-icon wpstg-h-4 wpstg-w-4 wpstg-hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span class="wpstg-copy-btn-text wpstg-text-xs"><?php echo esc_html__('Copy', 'wp-staging'); ?></span>
                                    </button>
                                </div>
                                <p class="wpstg-mt-1 wpstg-mb-0 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400"><?php esc_html_e('Copy this URI and add it to your Google API credentials as the authorized redirect URI.', 'wp-staging'); ?></p>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Upload Settings Card -->
                <div id="wpstg-save-settings-form" class="wpstg-space-y-6">
                    <section class="wpstg-card wpstg-card-body">
                        <h2 class="wpstg-text-base wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100 wpstg-m-0"><?php esc_html_e('Upload Settings', 'wp-staging'); ?></h2>
                        <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400"><?php esc_html_e('Configure where and how backups are stored in Google Drive.', 'wp-staging'); ?></p>

                        <div class="wpstg-mt-4 wpstg-grid wpstg-gap-4">
                            <?php if ($isGoogleDriveAuthenticated) :?>
                                <div>
                                    <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-drive-type" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php esc_html_e('Google Drive Type', 'wp-staging'); ?></label>
                                    <select id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-drive-type" data-wpstg-target="#wpstg-shared-drive-id-field" class="wpstg-input wpstg-input-md wpstg-storage-provider-input-field" name="drive_type">
                                        <option value="<?php echo esc_attr(Auth::DRIVE_TYPE_PERSONAL); ?>" <?php echo (empty($options['driveType']) || $options['driveType'] === Auth::DRIVE_TYPE_PERSONAL) ? 'selected' : ''; ?>><?php esc_html_e('Personal', 'wp-staging'); ?></option>
                                        <option value="<?php echo esc_attr(Auth::DRIVE_TYPE_SHARED_DRIVE); ?>" <?php echo (!empty($options['driveType']) && $options['driveType'] === Auth::DRIVE_TYPE_SHARED_DRIVE) ? 'selected' : ''; ?>><?php esc_html_e('Shared Drive', 'wp-staging'); ?></option>
                                        <option value="<?php echo esc_attr(Auth::DRIVE_TYPE_SHARED_WITH_ME); ?>" <?php echo (!empty($options['driveType']) && $options['driveType'] === Auth::DRIVE_TYPE_SHARED_WITH_ME) ? 'selected' : ''; ?>><?php esc_html_e('Shared with Me', 'wp-staging'); ?></option>
                                    </select>
                                </div>
                                <div id="wpstg-shared-drive-id-field" style="<?php echo esc_attr($sharedDriveStyle); ?>">
                                    <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-shared-drive-id" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php esc_html_e('Shared Drive ID', 'wp-staging'); ?></label>
                                    <span class="wpstg-flex wpstg-items-center">
                                        <input id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-shared-drive-id" class="wpstg-input wpstg-input-md wpstg-storage-provider-input-field" type="text" name="shared_drive_id" value="<?php echo esc_attr(isset($options['sharedDriveId']) ? $options['sharedDriveId'] : ''); ?>" placeholder="1JUyTvw38JCxExXqld1lFYWbpqtCpl3Z0" />
                                        <span class='wpstg--tooltip wpstg--tooltip-sftp'>
                                            <img class='wpstg--dashicons wpstg--grey' src='<?php echo esc_url($assetsUrl); ?>svg/info-outline.svg' alt='info'/>
                                            <span class='wpstg--tooltiptext'>
                                                <strong><?php esc_html_e('Enter the Shared Drive ID', 'wp-staging'); ?></strong>
                                                <?php esc_html_e('You can find this in the URL when viewing the Shared Drive in Google Drive.', 'wp-staging');?>
                                                <span class="wpstg-drive-type-example-container">
                                                    <strong><?php esc_html_e('Example:', 'wp-staging'); ?></strong>
                                                    <span>https://drive.google.com/drive/folders/<code>1JUyTvw38JCxExXqld1lFYWbpqtCpl3Z0</code></span>
                                                    <br>
                                                    <?php esc_html_e('In this case, the ID is: 1JUyTvw38JCxExXqld1lFYWbpqtCpl3Z0', 'wp-staging'); ?>
                                                </span>
                                                <span>
                                                    <strong><?php esc_html_e('Important:', 'wp-staging'); ?></strong>
                                                    <?php esc_html_e('Only enter the main ID of the Shared Drive or top-level Shared Folder.', 'wp-staging'); ?>
                                                    <br>
                                                    <span class="wpstg--red-warning"><?php esc_html_e('Do not use the ID of any subfolder inside it.', 'wp-staging'); ?></span>
                                                </span>
                                                <span>
                                                    <strong><?php esc_html_e('Note:', 'wp-staging'); ?></strong>
                                                    <?php esc_html_e('You must have at least the Content Manager role to access a Shared Drive.', 'wp-staging'); ?>
                                                </span>
                                            </span>
                                        </span>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div>
                                <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-max-backups-to-keep" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php esc_html_e('Max Backups to Keep', 'wp-staging'); ?></label>
                                <input id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-max-backups-to-keep" class="wpstg-input wpstg-input-md wpstg-storage-backup-retention-field" type="number" name="max_backups_to_keep" value="<?php echo esc_attr($maxBackupsToKeep); ?>" min="1" />
                                <p class="wpstg-mt-1 wpstg-mb-0 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400"><?php esc_html_e('Older backups are automatically deleted when this limit is reached.', 'wp-staging'); ?></p>
                            </div>

                            <div>
                                <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-folder-name" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php esc_html_e('Backup Location', 'wp-staging'); ?></label>
                                <div class="wpstg-input-group">
                                    <span class="wpstg-input-prefix">//Google Drive/</span>
                                    <input id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-folder-name" class="wpstg-input wpstg-input-md wpstg-storage-provider-input-field" type="text" placeholder="backups/example.com/" name="folder_name" value="<?php echo esc_attr($folderName); ?>" />
                                </div>
                                <p class="wpstg-mt-1 wpstg-mb-0 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400"><?php esc_html_e('The folder path inside Google Drive where backups will be stored.', 'wp-staging'); ?></p>
                            </div>
                        </div>
                        <?php require_once WPSTG_VIEWS_DIR . "pro/settings/tabs/storages/storage-notice.php";?>
                    </section>

                    <!-- Save Card -->
                    <section class="wpstg-flex wpstg-gap-6 wpstg-card wpstg-card-body wpstg-items-center">
                        <button type="button" id="wpstg-btn-save-provider-settings" class="wpstg-btn wpstg-btn-md wpstg-btn-primary"><?php esc_html_e("Save Settings", "wp-staging"); ?></button>
                        <?php require_once WPSTG_VIEWS_DIR . "pro/settings/tabs/storages/last-saved-notice.php"; ?>
                        <div id="wpstg-save-settings-loader" class="wpstg-loader"></div>
                    </section>
                </div>
            </form>

            <footer class="wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400">
                <?php
                echo sprintf(
                    esc_html__('None of your backup data is sent to any other party! %s', 'wp-staging'),
                    '<a href="https://wp-staging.com/privacy-policy/#Google_Drive" target="_blank">' . esc_html__('Our privacy policy', 'wp-staging') . '</a>'
                ); ?>
            </footer>
        </div>
    </div>
