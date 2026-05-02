<?php

/**
 * @var \WPStaging\Pro\Backup\Storage\BaseS3\S3Auth $auth
 * @var string $providerId
 * @var string $providerName
 * @var string $settingText
 * @var string $settingLink
 * @var string $settingText1
 * @var string $settingLink1
 * @var string $locationName
 */

use WPStaging\Framework\Facades\Escape;
use WPStaging\Pro\Backup\Storage\BaseS3\S3Auth;

?>
<div class="wpstg-bg-white dark:wpstg-bg-[#141b27] wpstg-provider-settings-container">
    <div class="wpstg-max-w-3xl wpstg-py-1 wpstg-space-y-6 wpstg-provider-settings-container-inner">
        <?php

        if ($auth->isEncrypted()) {
            require_once WPSTG_VIEWS_DIR . "pro/settings/tabs/storages/encrypted-notice.php";
        }

        $regions                = $auth->getRegions();
        $isStorageAuthenticated = $auth->isAuthenticated();
        $options                = $auth->getOptions();
        $accessKey              = empty($options['accessKey']) ? '' : $options['accessKey'];
        $secretKey              = empty($options['secretKey']) ? '' : $options['secretKey'];
        $region                 = empty($options['region']) ? '' : $options['region'];
        $maxBackupsToKeep       = empty($options['maxBackupsToKeep']) ? 2 : $options['maxBackupsToKeep'];
        $maxBackupsToKeep       = $maxBackupsToKeep > 0 ? $maxBackupsToKeep : 15;
        $location               = empty($options['location']) ? S3Auth::FOLDER_NAME : $options['location'];
        $lastUpdated            = empty($options['lastUpdated']) ? 0 : $options['lastUpdated'];
        $locationName           = empty($locationName) ? 'Bucket' : $locationName;

        // Check if the current region is a custom region (not in the predefined list)
        $isCustomRegion    = !empty($region) && !empty($regions) && !array_key_exists($region, $regions);
        $customRegionValue = $isCustomRegion ? $region : '';
        $selectedRegion    = $isCustomRegion ? 'custom' : $region;
        ?>

        <header>
            <h1 class="wpstg-text-2xl wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100"><?php echo esc_html($providerName); ?></h1>
            <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400"><?php echo sprintf(esc_html__('Upload backup files to your %s account.', 'wp-staging'), esc_html($providerName)); ?></p>
        </header>

            <form class="wpstg-space-y-6" id="wpstg-provider-settings-form" method="post">
                <div id="wpstg-provider-test-connection-fields" class="wpstg-space-y-6">
                    <input type="hidden" name="provider" value="<?php echo esc_attr($providerId); ?>" />

                    <!-- Card 1: API Keys -->
                    <section class="wpstg-card wpstg-card-body">
                        <h2 class="wpstg-text-base wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100 wpstg-m-0"><?php esc_html_e('API Keys', 'wp-staging'); ?></h2>
                        <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400"><?php esc_html_e('Enter your access credentials to connect to the storage provider.', 'wp-staging'); ?></p>

                        <?php if (!empty($settingLink) && !empty($settingText)) : ?>
                        <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400">
                            <a href="<?php echo esc_attr($settingLink); ?>" target="_blank"><?php echo esc_html($settingText); ?></a>
                        </p>
                        <?php endif; ?>

                        <fieldset class="wpstg-mt-4">
                            <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-access-key" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php esc_html_e('Access Key', 'wp-staging') ?></label>
                            <input id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-access-key" class="wpstg-input wpstg-input-md wpstg-storage-provider-input-field" type="password" name="access_key" value="<?php echo esc_attr($accessKey); ?>" autocomplete="off" />
                            <p class="wpstg-mt-1 wpstg-mb-0 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400">
                                <?php esc_html_e('Unique identifier for your account, provided by your service provider.', 'wp-staging'); ?>
                            </p>
                        </fieldset>

                        <fieldset class="wpstg-mt-4">
                            <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-secret-key" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php esc_html_e('Secret Key', 'wp-staging'); ?></label>
                            <input id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-secret-key" class="wpstg-input wpstg-input-md wpstg-storage-provider-input-field" type="password" name="secret_key" value="<?php echo esc_attr($secretKey); ?>" autocomplete="off" />
                            <p class="wpstg-mt-1 wpstg-mb-0 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400">
                                <?php esc_html_e('Private key for authentication. Keep this key secure.', 'wp-staging'); ?>
                            </p>
                        </fieldset>
                    </section>

                    <!-- Card 2: Storage -->
                    <section class="wpstg-card wpstg-card-body">
                        <h2 class="wpstg-text-base wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100 wpstg-m-0"><?php esc_html_e('Storage', 'wp-staging'); ?></h2>
                        <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400"><?php esc_html_e('Configure the region and location for your backups.', 'wp-staging'); ?></p>

                        <fieldset class="wpstg-mt-4">
                            <label class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php esc_html_e('Region', 'wp-staging'); ?></label>
                            <?php if (!empty($regions)) {
                                $selectedLabel = esc_html__('Select a region', 'wp-staging');
                                if ($selectedRegion === 'custom') {
                                    $selectedLabel = esc_html__('Custom Region', 'wp-staging');
                                } elseif (isset($regions[$selectedRegion])) {
                                    $selectedLabel = esc_html($regions[$selectedRegion]) . ' ' . esc_html($selectedRegion);
                                }
                                ?>
                                <div class="wpstg-dropdown wpstg-max-w-[400px]" id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-region" data-dropdown-select>
                                    <input type="hidden" name="region" value="<?php echo esc_attr($selectedRegion); ?>" autocomplete="off" />
                                    <button type="button" class="wpstg-dropdown-trigger" data-dropdown-trigger>
                                        <span data-dropdown-value><?php echo wp_kses_post($selectedLabel); ?></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                    </button>
                                    <ul class="wpstg-dropdown-menu" data-dropdown-menu>
                                        <?php foreach ($regions as $regionKey => $regionName) : ?>
                                            <li class="wpstg-dropdown-option" data-value="<?php echo esc_attr($regionKey); ?>" <?php echo ($regionKey === $selectedRegion) ? 'data-selected' : ''; ?>>
                                                <?php if ($regionKey === $selectedRegion) : ?>
                                                    <svg class="wpstg-dropdown-check" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                <?php else : ?>
                                                    <span class="wpstg-dropdown-spacer"></span>
                                                <?php endif; ?>
                                                <?php echo esc_html($regionName) . ' ' . esc_html($regionKey); ?>
                                            </li>
                                        <?php endforeach; ?>
                                        <?php if ($providerId === 'wasabi-s3') : ?>
                                            <li class="wpstg-dropdown-option" data-value="custom" <?php echo ($selectedRegion === 'custom') ? 'data-selected' : ''; ?>>
                                                <?php if ($selectedRegion === 'custom') : ?>
                                                    <svg class="wpstg-dropdown-check" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                <?php else : ?>
                                                    <span class="wpstg-dropdown-spacer"></span>
                                                <?php endif; ?>
                                                <?php esc_html_e('Custom Region', 'wp-staging'); ?>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>

                                <?php if ($providerId === 'wasabi-s3') : ?>
                                    <div id="wpstg-wasabi-custom-region-field" class="wpstg-mt-10px <?php echo $isCustomRegion ? '' : 'wpstg-u-hidden'; ?>">
                                        <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-custom-region" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php esc_html_e('Custom region', 'wp-staging'); ?></label>
                                        <input id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-custom-region" class="wpstg-input wpstg-input-md wpstg-storage-provider-input-field" type="text" name="custom_region" value="<?php echo esc_attr($customRegionValue); ?>" placeholder="<?php esc_attr_e('Enter custom region (e.g., eu-central-3)', 'wp-staging'); ?>" autocomplete="off"/>
                                        <p class="wpstg-mt-1 wpstg-mb-0 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400">
                                            <?php esc_html_e('Enter a custom Wasabi region if it\'s not listed above. Use the exact region identifier from your Wasabi account.', 'wp-staging'); ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            <?php } else { ?>
                                <input class="wpstg-input wpstg-input-md" type="text" name="region" value="<?php echo esc_attr($region); ?>" />
                            <?php } ?>
                        </fieldset>

                        <fieldset class="wpstg-mt-4">
                            <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-folder-name" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php esc_html_e($locationName, 'wp-staging'); ?></label>
                            <div class="wpstg-input-group">
                                <span class="wpstg-input-prefix">s3://</span>
                                <input id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-folder-name" class="wpstg-input wpstg-input-md wpstg-storage-provider-backup-location-field" type="text" name="location" value="<?php echo esc_attr($location); ?>" />
                            </div>
                            <?php if (!empty($settingLink1) && !empty($settingText1)) : ?>
                                    <p class="wpstg-mt-2 wpstg-mb-0 wpstg-text-xs"><a href="<?php echo esc_attr($settingLink1); ?>" target="_blank"><?php echo esc_html($settingText1); ?></a></p>
                            <?php endif; ?>
                            <p class="wpstg-mt-1 wpstg-mb-0 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400">
                            <?php echo sprintf(
                                Escape::escapeHtml(__('To add a directory you can write <code>s3:[%s]/[directory-name]</code>.<br>The directory will be created automatically during backup upload. ', 'wp-staging')),
                                esc_html($locationName),
                                '<br>'
                            ); ?>
                            </p>
                        </fieldset>
                        <?php require_once WPSTG_VIEWS_DIR . "pro/settings/tabs/storages/storage-notice.php";?>
                    </section>

                    <!-- Card 3: Backup Retention -->
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
                    <?php echo sprintf(Escape::escapeHtml(__('None of your backup data is sent to any other party! %s.', 'wp-staging')), '<a href="' . esc_url($privacyUrl) . '" target="_blank">' . esc_html__('Our privacy policy', 'wp-staging') . '</a>'); ?>
                </footer>
            </form>
    </div>
</div>
<!-- Sticky action bar -->
<div id="wpstg-sticky-action-bar" class="wpstg-sticky-action-bar">
    <button type="button" id="wpstg-btn-save-provider-settings-sticky" class="wpstg-btn wpstg-btn-md wpstg-btn-primary"><?php esc_html_e('Save Settings', 'wp-staging'); ?></button>
    <button type="button" id="wpstg-btn-provider-test-connection-sticky" class="!wpstg-mb-0 wpstg-btn wpstg-btn-md wpstg-btn-outline"><?php esc_html_e('Test connection', 'wp-staging'); ?></button>
</div>
