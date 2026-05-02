<?php

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Security\Capabilities;
use WPStaging\Pro\License\Licensing;
use WPStaging\Backup\Storage\Providers;
use WPStaging\Framework\Facades\UI\Alert;

// This is already covered, but just to make sure, since this data is sensitive.
if (!current_user_can(WPStaging::make(Capabilities::class)->manageWPSTG())) {
    return;
}

/**
 * @var Licensing
 */
$licensing = WPStaging::make(Licensing::class);

/**
 * @var Providers
 */
$storages = WPStaging::make(Providers::class);
$provider = 'googledrive';
$providerId = '';
if (isset($_REQUEST['sub-tab'])) {
    $provider = strtolower(sanitize_file_name($_REQUEST['sub-tab']));
}


?>
<div class="wpstg-provider-page-header">
    <h1 class="wpstg-text-2xl wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100"><?php esc_html_e('Storage Providers', 'wp-staging'); ?></h1>
    <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400">
        <?php esc_html_e('Upload backups to cloud or server destinations. Configure one provider at a time.', 'wp-staging'); ?>
    </p>
</div>
<div class="wpstg-storages-postbox">
    <?php foreach ($storages->getStorages(true) as $storage) : ?>
        <?php
            $isActive = $provider === strtolower($storage['id']);
        if ($isActive) {
            $providerId = $storage['id'];
        }
        ?>
        <a class="wpstg-storage-provider <?php echo $isActive ? 'wpstg-storage-provider-active' : '' ?>" href="<?php echo $isActive ? 'javascript:void(0);' : esc_url($storage['settingsPath']); ?>">
            <?php echo esc_html($storage['name']); ?>
        </a>
    <?php endforeach; ?>
</div>

<?php
if (in_array($provider, ['one-drive', 'pcloud'], true) && !$licensing->isBusinessPlanOrHigher()) {
    $title       = __('Upgrade Required', 'wp-staging');
    $description = __('You need a WP Staging Business plan or higher to use this storage provider.', 'wp-staging');
    $buttonText  = __('Upgrade Now', 'wp-staging');
    $buttonUrl   = admin_url('admin.php?page=wpstg-license');
    Alert::render($title, $description, $buttonText, $buttonUrl);
    return;
}

$providerPath = WPSTG_VIEWS_DIR . "pro/settings/tabs/storages/" . $provider . "-settings.php";
$providerPath = wp_normalize_path($providerPath);
// Additional check to make sure no file is accessed outside the plugin storage setting directory
if (strpos($providerPath, wp_normalize_path(WPSTG_VIEWS_DIR) . "pro/settings/tabs/storages/") !== 0) {
    ?>
    <div class="notice notice-error"><p><?php esc_html_e('Error: Wrong URL for remote settings provided!', 'wp-staging'); ?></p></div>
    <?php
    return;
}

if (file_exists($providerPath)) {
    ?>
    <div class="wpstg-storage-postbox">
    <?php
    require_once($providerPath);
    ?>
    </div>
    <?php
    return;
}
?>

<div class="notice notice-error"><p><?php esc_html_e('Error: Wrong URL for remote settings provided!', 'wp-staging'); ?></p></div>
