<?php

/**
 * @var string $providerId
 */

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Facades\Escape;
use WPStaging\Framework\Facades\UI\Checkbox;
use WPStaging\Pro\Backup\Storage\GenericS3\Auth;
use WPStaging\Pro\Backup\Storage\GenericS3\Providers;

?>
<div class="wpstg-bg-white dark:wpstg-bg-[#141b27] wpstg-provider-settings-container">
    <div class="wpstg-max-w-3xl wpstg-py-1 wpstg-space-y-6 wpstg-provider-settings-container-inner">
        <?php
        /** @var Auth */
        $auth = WPStaging::make(Auth::class);

        $providerName = 'Generic S3';
        if ($auth->isEncrypted()) {
            require_once WPSTG_VIEWS_DIR . "pro/settings/tabs/storages/encrypted-notice.php";
        }

        $providers              = Providers::PROVIDERS;
        $isStorageAuthenticated = $auth->isAuthenticated();
        $options                = $auth->getOptions();
        $s3provider             = empty($options['provider']) ? '' : $options['provider'];
        $accessKey              = empty($options['accessKey']) ? '' : $options['accessKey'];
        $secretKey              = empty($options['secretKey']) ? '' : $options['secretKey'];
        $region                 = empty($options['region']) ? '' : $options['region'];
        $maxBackupsToKeep       = empty($options['maxBackupsToKeep']) ? 2 : $options['maxBackupsToKeep'];
        $maxBackupsToKeep       = $maxBackupsToKeep > 0 ? $maxBackupsToKeep : 15;
        $location               = empty($options['location']) ? Auth::FOLDER_NAME : $options['location'];
        $lastUpdated            = empty($options['lastUpdated']) ? 0 : $options['lastUpdated'];

        if ($s3provider === '') {
            $customProviderName   = empty($options['providerName']) ? '' : $options['providerName'];
            $endpoint             = empty($options['endpoint']) ? '' : $options['endpoint'];
            $version              = empty($options['version']) ? '' : $options['version'];
            $ssl                  = isset($options['ssl']) ? $options['ssl'] : false;
            $usePathStyleEndpoint = isset($options['usePathStyleEndpoint']) ? $options['usePathStyleEndpoint'] : false;
        }

        $locationName = empty($locationName) ? 'Bucket' : $locationName;
        $assetsUrl    = trailingslashit(WPSTG_PLUGIN_URL);
        ?>

        <header>
            <h1 class="wpstg-text-2xl wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100"><?php echo esc_html($providerName); ?></h1>
            <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400"><?php echo esc_html__('Upload backup files to your S3-compatible storage provider.', 'wp-staging'); ?></p>
        </header>

            <form class="wpstg-space-y-6" id="wpstg-provider-settings-form" method="post">
                <div id="wpstg-provider-test-connection-fields" class="wpstg-space-y-6">
                    <input type="hidden" name="provider" value="<?php echo esc_attr($providerId); ?>" />

                    <!-- Card 1: S3 Provider -->
                    <section class="wpstg-card wpstg-card-body">
                        <h2 class="wpstg-text-base wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100 wpstg-m-0"><?php esc_html_e('S3 Provider', 'wp-staging'); ?></h2>
                        <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400"><?php esc_html_e('Select a known provider or configure a custom S3-compatible endpoint.', 'wp-staging'); ?></p>

                        <fieldset class="wpstg-mt-4">
                            <label for="wpstg-storage-provider-s3" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php esc_html_e('S3 Compatible Provider', 'wp-staging'); ?></label>
                            <select id="wpstg-storage-provider-s3" class="wpstg-input wpstg-input-md wpstg-storage-provider-input-field" name="s3_provider">
                                <option value="" <?php echo ($s3provider === '') ? 'selected' : '' ; ?>><?php esc_html_e('Custom Provider', 'wp-staging'); ?></option>
                                <?php foreach ($providers as $providerArr) : ?>
                                    <option value="<?php echo esc_attr($providerArr['key']); ?>" <?php echo ($providerArr['key'] === $s3provider) ? 'selected' : '' ; ?>><?php echo esc_html($providerArr['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </fieldset>

                        <div id="wpstg-s3-custom-provider-fields" class="hidden" <?php echo ($s3provider === '') ? 'style="display: block;"' : '' ; ?>>
                            <h3 class="wpstg-text-sm wpstg-font-semibold wpstg-text-slate-800 dark:wpstg-text-slate-200 wpstg-mt-4 wpstg-mb-0"><?php esc_html_e('Custom Provider', 'wp-staging'); ?></h3>

                            <fieldset class="wpstg-mt-4">
                                <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-name" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php esc_html_e('Name', 'wp-staging'); ?></label>
                                <input id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-name" class="wpstg-input wpstg-input-md wpstg-storage-provider-input-field" type="text" name="provider_name" value="<?php echo esc_attr($customProviderName); ?>" placeholder="Provider Name" />
                            </fieldset>

                            <fieldset class="wpstg-mt-4">
                                <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-endpoint" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php esc_html_e('Endpoint', 'wp-staging'); ?></label>
                                <input id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-endpoint" class="wpstg-input wpstg-input-md wpstg-storage-provider-input-field" type="text" name="endpoint" value="<?php echo esc_attr($endpoint); ?>" placeholder="https://example.com:8888" />
                            </fieldset>

                            <fieldset class="wpstg-mt-4">
                                <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-version" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php esc_html_e('Version', 'wp-staging'); ?></label>
                                <input id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-version" class="wpstg-input wpstg-input-md wpstg-storage-provider-input-field" type="text" name="version" value="<?php echo esc_attr($version); ?>" />
                                <p class="wpstg-mt-1 wpstg-mb-0 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400">
                                    <?php echo Escape::escapeHtml(__("If your S3 provider does not specify a version in their guide, enter <code>latest</code> or <code>2006-03-01</code>.", 'wp-staging')); ?>
                                </p>
                            </fieldset>

                            <fieldset class="wpstg-mt-4">
                                <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-ssl" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200">
                                    <?php esc_html_e('SSL', 'wp-staging'); ?>
                                    <span class='wpstg--tooltip wpstg--tooltip-sftp'>
                                        <img class='wpstg--dashicons wpstg--grey' src='<?php echo esc_html($assetsUrl); ?>assets/svg/info-outline.svg' alt='info'/>
                                        <span class='wpstg--tooltiptext'>
                                            <?php esc_html_e('Enable SSL for secure encrypted connections to the S3 server.', 'wp-staging'); ?>
                                        </span>
                                    </span>
                                </label>
                                <?php Checkbox::render("wpstg-storage-provider-{$providerId}-ssl", 'ssl', 'true', $ssl === true); ?>
                            </fieldset>

                            <fieldset class="wpstg-mt-4">
                                <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-use-path-style-endpoint" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200">
                                    <?php esc_html_e('Use path style endpoint', 'wp-staging'); ?>
                                    <span class='wpstg--tooltip wpstg--tooltip-sftp'>
                                        <img class='wpstg--dashicons wpstg--grey' src='<?php echo esc_html($assetsUrl); ?>assets/svg/info-outline.svg' alt='info'/>
                                        <span class='wpstg--tooltiptext'>
                                            <?php esc_html_e('Use path-style URLs for accessing buckets (e.g., s3.example.com/bucket).', 'wp-staging'); ?>
                                        </span>
                                    </span>
                                </label>
                                <?php Checkbox::render("wpstg-storage-provider-{$providerId}-use-path-style-endpoint", 'use_path_style_endpoint', 'true', $usePathStyleEndpoint === true); ?>
                            </fieldset>
                        </div>
                    </section>

                    <!-- Card 2: API Keys -->
                    <section class="wpstg-card wpstg-card-body">
                        <h2 class="wpstg-text-base wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100 wpstg-m-0"><?php esc_html_e('API Keys', 'wp-staging'); ?></h2>
                        <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400"><?php esc_html_e('Enter your access credentials to connect to the storage provider.', 'wp-staging'); ?></p>

                        <fieldset class="wpstg-mt-4">
                            <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-access-key" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php esc_html_e('Access Key', 'wp-staging'); ?></label>
                            <input id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-access-key" class="wpstg-input wpstg-input-md wpstg-storage-provider-input-field" type="password" name="access_key" value="<?php echo esc_attr($accessKey); ?>" autocomplete="off" />
                            <p class="wpstg-mt-1 wpstg-mb-0 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400">
                                <?php esc_html_e('Unique identifier for your account, provided by your S3 service.', 'wp-staging'); ?>
                            </p>
                        </fieldset>

                        <fieldset class="wpstg-mt-4">
                            <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-secret-key" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php esc_html_e('Secret Key', 'wp-staging'); ?></label>
                            <input id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-secret-key" class="wpstg-input wpstg-input-md wpstg-storage-provider-input-field" type="password" name="secret_key" value="<?php echo esc_attr($secretKey); ?>" autocomplete="off"/>
                            <p class="wpstg-mt-1 wpstg-mb-0 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400">
                                <?php esc_html_e('Private key for authentication. Keep this key secure.', 'wp-staging'); ?>
                            </p>
                        </fieldset>
                    </section>

                    <!-- Card 3: Storage -->
                    <section class="wpstg-card wpstg-card-body">
                        <h2 class="wpstg-text-base wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100 wpstg-m-0"><?php esc_html_e('Storage', 'wp-staging'); ?></h2>
                        <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400"><?php esc_html_e('Configure the region and bucket for your backups.', 'wp-staging'); ?></p>

                        <fieldset class="wpstg-mt-4">
                            <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-region" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php esc_html_e('Region', 'wp-staging'); ?></label>
                            <input id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-region" class="wpstg-input wpstg-input-md wpstg-storage-provider-input-field" type="text" name="region" value="<?php echo esc_attr($region); ?>" autocomplete="off"/>
                            <p class="wpstg-mt-1 wpstg-mb-0 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400">
                                <?php esc_html_e('The geographic region of your S3 storage. Match your bucket\'s region.', 'wp-staging'); ?>
                            </p>
                        </fieldset>

                        <fieldset class="wpstg-mt-4">
                            <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-folder-name" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php esc_html_e('Bucket Name', 'wp-staging'); ?></label>
                            <div class="wpstg-input-group">
                                <span class="wpstg-input-prefix">s3://</span>
                                <input id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-folder-name" class="wpstg-input wpstg-input-md wpstg-storage-provider-input-field" type="text" name="location" value="<?php echo esc_attr($location); ?>" />
                            </div>
                            <p class="wpstg-mt-1 wpstg-mb-0 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400">
                            <?php echo sprintf(
                                Escape::escapeHtml(__('To add a directory you can write <code>s3:[%s]/[directory-name]</code>.<br>The directory will be created automatically during backup upload. ', 'wp-staging')),
                                esc_html($locationName)
                            ); ?>
                            </p>
                        </fieldset>
                        <?php require_once WPSTG_VIEWS_DIR . "pro/settings/tabs/storages/storage-notice.php";?>
                    </section>

                    <!-- Card 4: Backup Retention -->
                    <section class="wpstg-card wpstg-card-body">
                        <h2 class="wpstg-text-base wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100 wpstg-m-0"><?php esc_html_e('Backup Retention', 'wp-staging'); ?></h2>
                        <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400"><?php esc_html_e('Control how many backups to keep in your storage.', 'wp-staging'); ?></p>

                        <div class="wpstg-flex wpstg-items-center wpstg-justify-between wpstg-mt-4">
                            <div>
                                <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-max-backups-to-keep" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php esc_html_e('Maximum backups to keep', 'wp-staging'); ?></label>
                                <p class="wpstg-mt-1 wpstg-mb-0 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400"><?php esc_html_e('Older backups are automatically deleted when this limit is reached.', 'wp-staging'); ?></p>
                            </div>
                            <input id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-max-backups-to-keep" class="wpstg-input wpstg-input-lg wpstg-w-1/4" type="number" name="max_backups_to_keep" value="<?php echo esc_attr($maxBackupsToKeep); ?>" min="1" max="100" placeholder="15" />
                        </div>
                    </section>
                </div>

                <!-- Actions -->
                <section id="wpstg-static-action-buttons" class="wpstg-card wpstg-card-body">
                    <div class="wpstg-flex wpstg-gap-3 wpstg-items-center">
                        <button type="button" id="wpstg-btn-save-provider-settings" class="wpstg-btn wpstg-btn-md wpstg-btn-primary"><?php esc_html_e("Save Settings", "wp-staging"); ?></button>
                        <button type="button" id="wpstg-btn-provider-test-connection" class="!wpstg-mb-0 wpstg-btn wpstg-btn-md wpstg-btn-outline"><?php esc_html_e("Test connection", "wp-staging"); ?></button>
                        <span class="wpstg-action-badge-inline"><?php require_once WPSTG_VIEWS_DIR . "pro/settings/tabs/storages/last-saved-notice.php"; ?></span>
                        <button type="button" id="wpstg-btn-delete-provider-settings" class="wpstg-btn-link-danger wpstg-ml-auto"><?php esc_html_e("Delete Settings", "wp-staging"); ?></button>
                    </div>
                </section>

                <footer class="wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400">
                    <?php echo Escape::escapeHtml(sprintf(__('None of your backup data is sent to any other party! %s.', 'wp-staging'), '<a href="https://wp-staging.com/privacy-policy/" target="_blank">' . esc_html__('Our privacy policy', 'wp-staging') . '</a>')); ?>
                </footer>
            </form>
    </div>
</div>
<!-- Sticky action bar -->
<div id="wpstg-sticky-action-bar" class="wpstg-sticky-action-bar">
    <button type="button" id="wpstg-btn-save-provider-settings-sticky" class="wpstg-btn wpstg-btn-md wpstg-btn-primary"><?php esc_html_e('Save Settings', 'wp-staging'); ?></button>
    <button type="button" id="wpstg-btn-provider-test-connection-sticky" class="!wpstg-mb-0 wpstg-btn wpstg-btn-md wpstg-btn-outline"><?php esc_html_e('Test connection', 'wp-staging'); ?></button>
</div>
