<?php

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Language\Language;
use WPStaging\Pro\License\Licensing;
use WPStaging\Pro\WPStagingPro;
use WPStaging\Framework\Facades\UI\Alert;

$canCreateTemporaryLogins = false;
$upgradeUrl               = 'https://wp-staging.com/#pricing';
$isValidLicense           = WPStagingPro::isValidLicense();
$licensing                = WPStaging::make(Licensing::class);
$licenseData              = get_option('wpstg_license_status');
$licenseId                = empty($licenseData->license_id) ? '' : $licenseData->license_id;
$canCreateTemporaryLogins = $licensing->isAgencyOrDeveloperPlan();
$upgradeUrl               = Language::localizeCheckoutUrl("https://wp-staging.com/checkout/?nocache=true&edd_action=sl_license_upgrade&license_id=$licenseId&upgrade_id=" . Licensing::DEVELOPER_LICENSE_UPGRADE_PLAN_KEY);
?>

<div class="wpstg-tab-temporary-logins">
    <div class="wpstg-provider-page-header">
        <h1 class="wpstg-text-2xl wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100"><?php esc_html_e('Temporary Logins', 'wp-staging'); ?></h1>
        <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400"><?php esc_html_e('Create and share temporary login links that expire automatically. No password required.', 'wp-staging'); ?></p>
        <?php if (!$isValidLicense) : ?>
            <a href="https://wp-staging.com/docs/create-magic-login-links/" target="_blank" class="wpstg-btn wpstg-btn-md wpstg-btn-primary wpstg-mt-2 wpstg-no-underline">
                <?php echo esc_html__('Open Preview', 'wp-staging'); ?>
            </a>
        <?php endif; ?>
    </div>
    <?php if ($isValidLicense && !$canCreateTemporaryLogins) {
        $title       = __('Upgrade Required', 'wp-staging');
        $description = __('You need a WP Staging Developer plan or higher. Please upgrade your license.', 'wp-staging');
        $buttonText  = __('Upgrade Now', 'wp-staging');
        $buttonUrl   = admin_url('admin.php?page=wpstg-license');
        Alert::render($title, $description, $buttonText, $buttonUrl);
    }
    ?>
    <div class="wpstg-temp-login-header-container">
        <?php if ($isValidLicense) :?>
            <button class="wpstg-button wpstg-blue-primary" id="wpstg-create-temp-login" <?php echo  $canCreateTemporaryLogins ? '' : 'disabled'; ?> >
                <?php esc_html_e('Create Temporary Login', 'wp-staging') ?>
            </button>
        <?php endif;?>
    </div>
    <div id="wpstg-temporary-logins-wrapper">
        <?php if ($isValidLicense) : ?>
        <div class="wpstg-temporary-logins-skeleton wpstg-animate-pulse wpstg-py-4">
            <div class="wpstg-space-y-3">
                <div class="wpstg-h-4 wpstg-bg-gray-200 dark:wpstg-bg-slate-700 wpstg-rounded wpstg-w-1/4"></div>
                <div class="wpstg-h-3 wpstg-bg-gray-200 dark:wpstg-bg-slate-700 wpstg-rounded wpstg-w-full"></div>
                <div class="wpstg-h-3 wpstg-bg-gray-200 dark:wpstg-bg-slate-700 wpstg-rounded wpstg-w-5/6"></div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
