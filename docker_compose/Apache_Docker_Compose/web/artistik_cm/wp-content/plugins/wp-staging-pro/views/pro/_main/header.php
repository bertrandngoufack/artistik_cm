<?php

/**
 * @see src/views/pro/licensing.php
 * @see src/views/clone/index.php
 *
 * @var object $license
 */

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Language\Language;
use WPStaging\Pro\License\Licensing;
use WPStaging\Pro\WPStagingPro;

$Licensing                      = WPStaging::make(Licensing::class);
$availableLicensePlansByPriceId = $Licensing->getAvailableLicensePlansByPriceId();
$notUpgradableLicensePlans      = [
    Licensing::AGENCY_LICENSE_PLAN_KEY,
    Licensing::DEVELOPER_LEGACY_LICENSE_PLAN_KEY,
    Licensing::AGENCY_NON_RECURRING_LICENSE_PLAN_KEY,
    Licensing::DEVELOPER_UNLIMITED_SITES_LICENSE_PLAN_KEY,
];

$customerName      = !empty($license->customer_name) ? $license->customer_name : '';
$customerEmail     = !empty($license->customer_email) ? $license->customer_email : '';
$licensePriceId    = !empty($license->price_id) ? $license->price_id : '';
$licensePlanName   = !empty($availableLicensePlansByPriceId[$licensePriceId]['name']) ? $availableLicensePlansByPriceId[$licensePriceId]['name'] : '';
$showUpgradeButton = !empty($licensePriceId) && !in_array($licensePriceId, $notUpgradableLicensePlans);
$isValidLicense    = WPStagingPro::isValidLicense();
$proButtonLabel    = '';
$proButtonLink     = '';
$licensekey        = get_option('wpstg_license_key', '');

if (empty($license->license) || $license->license === 'invalid') {
    $licensePlanName = 'Unregistered';
    $proButtonLabel  = 'Activate Pro Features';
    $proButtonLink   = Language::localizeUrl('https://wp-staging.com/#pricing');
}

if ((!empty($license->license) && $license->license === 'disabled') || (!empty($license->error) && $license->error === 'disabled')) {
    $licensePlanName = 'License Disabled';
    $proButtonLabel  = 'Activate Pro Features';
    $proButtonLink   = Language::localizeUrl('https://wp-staging.com/#pricing');
}

if ((!empty($license->license) && $license->license === 'expired') || (!empty($license->error) && $license->error === 'expired')) {
    $licensePlanName = 'License Expired';
    $proButtonLabel  = 'Renew Pro License';
    $proButtonLink   = Language::localizeCheckoutUrl('https://wp-staging.com/checkout/?nocache=true&edd_license_key=' . esc_html($licensekey) . '&download_id=11');
}
?>

<div id="wpstg-top-header">
    <span class="wpstg-logo">
        <img src="<?php echo esc_url($this->assets->getAssetsUrl("img/logo-white-transparent.png")) ?>" width="212" alt="">
    </span>

    <div class="wpstg-version">
    <?php

    echo 'WP Staging Pro v. ' . esc_html(WPStaging::getVersion());

    if (!empty($licensePlanName)) {
        echo ' <a href="' . esc_url(Language::localizeUrl('https://wp-staging.com')) . '" target="_blank">' . esc_html($licensePlanName) . '</a>';
    }

    if (!empty($customerName) || !empty($customerEmail)) {
        echo '<br>';
    }

    if (!empty($customerName)) {
        echo esc_html($customerName) . ' ';
    }

    if (!empty($customerEmail)) {
        echo sprintf('&lt;%s&gt', esc_html($customerEmail));
    }

    if ($isValidLicense && $showUpgradeButton) {
        echo '<div class="wpstg-upgrade-license-container">
                            <a href="' . esc_url(admin_url('admin.php?page=wpstg-license')) . '" class="wpstg-upgrade-license-button" target="_self">' . esc_html__('Upgrade License', 'wp-staging') . '</a>
                      </div>';
    }

    if (!$isValidLicense) {
        echo '<div class="wpstg-upgrade-license-container">
                            <a href="' . esc_url($proButtonLink) . '" class="wpstg-upgrade-license-button" target="_blank">' . esc_html($proButtonLabel) . '</a>
                      </div>';
    }
    ?>
    </div>
</div>
