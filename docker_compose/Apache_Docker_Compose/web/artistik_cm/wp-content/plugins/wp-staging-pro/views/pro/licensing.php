<?php

/**
 * License page view with Tailwind UI design
 *
 * @see WPStaging\Backend\Administrator::getLicensePage
 *
 * @var object $license
 */

use WPStaging\Framework\Language\Language;
use WPStaging\Pro\License\Licensing;

?>
<div class="wpstg_admin" id="wpstg-clonepage-wrapper">
    <?php
    require_once($this->viewsPath . 'pro/_main/header.php');

    $isActiveLicensePage = true;
    require_once(WPSTG_VIEWS_DIR . '_main/main-navigation.php');

    $personalPlans = [
        Licensing::PERSONAL_LICENSE_PLAN_KEY,
        Licensing::PERSONAL_LICENSE_2025_PLAN_KEY,
        Licensing::PERSONAL_NON_RECURRING_LICENSE_PLAN_KEY,
    ];

    $businessPlans = [
        Licensing::BUSINESS_LICENSE_PLAN_KEY,
        Licensing::BUSINESS_5_SITES_LICENSE_PLAN_KEY,
        Licensing::BUSINESS_NON_RECURRING_LICENSE_PLAN_KEY,
    ];

    $developerPlans = [
        Licensing::DEVELOPER_LICENSE_PLAN_KEY,
        Licensing::DEVELOPER_30_SITES_LICENSE_PLAN_KEY,
        Licensing::DEVELOPER_NON_RECURRING_LICENSE_PLAN_KEY,
    ];

    $agencyPlans = [
        Licensing::AGENCY_LICENSE_PLAN_KEY,
        Licensing::AGENCY_NON_RECURRING_LICENSE_PLAN_KEY,
        Licensing::DEVELOPER_LEGACY_LICENSE_PLAN_KEY,
        Licensing::DEVELOPER_UNLIMITED_SITES_LICENSE_PLAN_KEY,
    ];

    $customerName        = empty($license->customer_name) ? '[unknown name]' : $license->customer_name;
    $customerEmail       = empty($license->customer_email) ? '[unknown email address]' : $license->customer_email;
    $licensePriceId      = empty($license->price_id) ? '' : $license->price_id;
    $licenseId           = empty($license->license_id) ? '' : $license->license_id;
    $upgradeUrl          = Language::localizeCheckoutUrl("https://wp-staging.com/checkout/?nocache=true&edd_action=sl_license_upgrade&license_id=$licenseId&upgrade_id=");
    $licenseExpiry       = empty($license->expires) ? '' : date_i18n(get_option('date_format'), strtotime($license->expires, current_time('timestamp')));
    $siteCount           = empty($license->site_count) ? '0' : $license->site_count;
    $licenseLimit        = empty($license->license_limit) ? '0' : $license->license_limit;
    $upgradeToBusiness   = $personalPlans;
    $upgradeToDeveloper  = array_merge($upgradeToBusiness, $businessPlans);
    $upgradeToAgency     = array_merge($upgradeToDeveloper, $developerPlans);

    $showUpgradeOptions = in_array($licensePriceId, $upgradeToBusiness) ||
                          in_array($licensePriceId, $upgradeToDeveloper) ||
                          in_array($licensePriceId, $upgradeToAgency);
    $showUpgradeOptions = $showUpgradeOptions && !in_array($licensePriceId, $agencyPlans);

    // Shared UI elements
    $checkIcon = '<svg class="wpstg-w-4 wpstg-h-4 wpstg-mr-2 wpstg-text-green-500 dark:wpstg-text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';

    // Shared features for Developer and Agency plans
    $sharedPlanFeatures = [
        esc_html__('9 Cloud Storage Providers', 'wp-staging'),
        esc_html__('Multisite Ready', 'wp-staging'),
        esc_html__('Local Staging Sites', 'wp-staging'),
        esc_html__('Standalone Extractor', 'wp-staging'),
        esc_html__('WordPress Restorer', 'wp-staging'),
        esc_html__('Magic Login Links', 'wp-staging'),
        esc_html__('And much more...', 'wp-staging'),
    ];
    ?>

    <div class="wpstg-loading-bar-container">
        <div class="wpstg-loading-bar"></div>
    </div>

    <div class="wpstg-metabox-holder wpstg-license-wrapper">
        <div class="wpstg-license-message-wrapper">
            <?php if (isset($license->license) && $license->license === 'valid') : ?>
                <!-- Active License Card -->
                <div class="wpstg-max-w-5xl">
                    <div class="wpstg-space-y-6">

                        <!-- Card Header -->
                        <section class="wpstg-card wpstg-card-body">
                            <div class="wpstg-flex wpstg-flex-col md:wpstg-flex-row wpstg-justify-between md:wpstg-items-center wpstg-mb-4">
                                <div>
                                    <h2 class="wpstg-text-xl wpstg-font-bold wpstg-flex wpstg-items-center wpstg-text-gray-800 dark:wpstg-text-gray-100 wpstg-m-0">
                                        <?php echo esc_html('WP Staging Pro'); ?>
                                        <span class="wpstg-ml-3 wpstg-inline-flex wpstg-items-center wpstg-px-2.5 wpstg-py-0.5 wpstg-rounded-full wpstg-text-xs wpstg-font-medium wpstg-bg-green-100 dark:wpstg-bg-emerald-900/20 wpstg-text-green-800 dark:wpstg-text-emerald-300">
                                            <svg class="wpstg--ml-0.5 wpstg-mr-1.5 wpstg-h-2 wpstg-w-2 wpstg-text-green-400 dark:wpstg-text-emerald-400" fill="currentColor" viewBox="0 0 8 8" aria-hidden="true"><circle cx="4" cy="4" r="3"></circle></svg>
                                            <?php esc_html_e('Active', 'wp-staging'); ?>
                                        </span>
                                    </h2>
                                    <p class="wpstg-text-sm wpstg-text-gray-500 dark:wpstg-text-gray-400 wpstg-m-0 wpstg-mt-1"><?php esc_html_e('Thank you for supporting WP Staging.', 'wp-staging'); ?></p>
                                </div>
                                <div class="wpstg-license-actions">
                                    <a href="javascript:void(0)" id="wpstg-refresh-license-link">
                                        <?php esc_html_e('Refresh Status', 'wp-staging'); ?>
                                    </a>
                                    <span id="wpstg-refresh-license-loader" class="wpstg-loader"></span>
                                    <form method="post" action="#" class="wpstg-m-0 wpstg-p-0 wpstg-inline-flex wpstg-items-center">
                                        <input type="hidden" name="wpstg_deactivate_license" value="1">
                                        <?php wp_nonce_field('wpstg_license_nonce', 'wpstg_license_nonce'); ?>
                                        <button type="submit" class="wpstg-license-deactivate-button">
                                            <?php esc_html_e('Deactivate License', 'wp-staging'); ?>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- License Info Grid -->
                            <div class="wpstg-grid wpstg-grid-cols-1 md:wpstg-grid-cols-3 wpstg-gap-4 wpstg-text-sm wpstg-mt-6 wpstg-p-4 wpstg-rounded-md wpstg-border wpstg-border-solid wpstg-border-gray-200 dark:wpstg-border-gray-700">
                                <div>
                                    <span class="wpstg-block wpstg-text-gray-500 dark:wpstg-text-gray-400 wpstg-font-medium wpstg-mb-1"><?php esc_html_e('License Key Status', 'wp-staging'); ?></span>
                                    <span class="wpstg-text-gray-900 dark:wpstg-text-gray-100 wpstg-font-semibold">
                                        <?php
                                        printf(
                                            esc_html__('Active on %1$s of %2$s sites', 'wp-staging'),
                                            esc_html($siteCount),
                                            esc_html($licenseLimit)
                                        );
                                        ?>
                                    </span>
                                </div>
                                <div>
                                    <span class="wpstg-block wpstg-text-gray-500 dark:wpstg-text-gray-400 wpstg-font-medium wpstg-mb-1"><?php esc_html_e('Registered To', 'wp-staging'); ?></span>
                                    <span class="wpstg-text-gray-900 dark:wpstg-text-gray-100"><?php echo esc_html($customerEmail); ?></span>
                                </div>
                                <div>
                                    <span class="wpstg-block wpstg-text-gray-500 dark:wpstg-text-gray-400 wpstg-font-medium wpstg-mb-1"><?php esc_html_e('Expiration Date', 'wp-staging'); ?></span>
                                    <span class="wpstg-text-gray-900 dark:wpstg-text-gray-100"><?php echo esc_html($licenseExpiry); ?></span>
                                </div>
                            </div>

                            <!-- Important Requirement Box -->
                            <div class="wpstg-callout wpstg-callout-info wpstg-mt-6">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                                <div>
                                    <div class="wpstg-text-sm wpstg-font-semibold"><?php esc_html_e('Important Requirement', 'wp-staging'); ?></div>
                                    <p class="wpstg-m-0 wpstg-mt-1 wpstg-text-sm"><?php esc_html_e('To push a staging site back to live, ensure your license key is also activated on your live site, then start the push process from there.', 'wp-staging'); ?></p>
                                </div>
                            </div>
                        </section>

                        <?php if ($showUpgradeOptions) : ?>
                        <!-- Upgrade Plans Section -->
                        <section class="wpstg-card wpstg-card-body">
                            <h3 class="wpstg-text-lg wpstg-leading-6 wpstg-font-medium wpstg-text-gray-900 dark:wpstg-text-gray-100 wpstg-m-0 wpstg-mb-6"><?php esc_html_e('Need more power? Upgrade your plan.', 'wp-staging'); ?></h3>

                            <div class="wpstg-grid wpstg-grid-cols-1 md:wpstg-grid-cols-3 wpstg-gap-6 wpstg-mb-4">
                                <?php if (in_array($licensePriceId, $upgradeToBusiness)) : ?>
                                <!-- Business Plan -->
                                <div class="wpstg-border wpstg-border-solid wpstg-border-gray-200 dark:wpstg-border-gray-700 wpstg-rounded-lg wpstg-p-6 wpstg-flex wpstg-flex-col">
                                    <h4 class="wpstg-text-lg wpstg-font-bold wpstg-text-gray-900 dark:wpstg-text-gray-100 wpstg-m-0"><?php esc_html_e('Business Plan', 'wp-staging'); ?></h4>
                                    <p class="wpstg-text-sm wpstg-text-gray-500 dark:wpstg-text-gray-400 wpstg-m-0 wpstg-mt-2 wpstg-mb-4"><?php esc_html_e('For small agencies managing a few clients.', 'wp-staging'); ?></p>
                                    <ul class="wpstg-text-sm wpstg-text-gray-600 dark:wpstg-text-gray-400 wpstg-space-y-3 wpstg-mb-6 wpstg-flex-grow wpstg-list-none wpstg-p-0 wpstg-m-0">
                                        <li class="wpstg-flex wpstg-items-center"><?php echo $checkIcon; // phpcs:ignore ?><?php esc_html_e('Up to 3 Sites', 'wp-staging'); ?></li>
                                        <li class="wpstg-flex wpstg-items-center"><?php echo $checkIcon; // phpcs:ignore ?><?php esc_html_e('Standard Support', 'wp-staging'); ?></li>
                                    </ul>
                                    <a href="<?php echo esc_url($upgradeUrl . Licensing::BUSINESS_LICENSE_UPGRADE_PLAN_KEY); ?>" target="_blank" class="wpstg-license-upgrade-button wpstg-block wpstg-w-full wpstg-text-center wpstg-px-4 wpstg-py-2 wpstg-font-medium wpstg-rounded-md wpstg-transition-colors wpstg-no-underline">
                                        <?php esc_html_e('Upgrade to Business', 'wp-staging'); ?>
                                    </a>
                                </div>
                                <?php endif; ?>

                                <?php if (in_array($licensePriceId, $upgradeToDeveloper)) : ?>
                                <!-- Developer Plan (Highlighted) -->
                                <div class="wpstg-border-2 wpstg-border-solid wpstg-border-indigo-500 wpstg-rounded-lg wpstg-p-6 wpstg-flex wpstg-flex-col wpstg-relative">
                                    <div class="wpstg-absolute wpstg-top-0 wpstg-left-1/2 wpstg--translate-x-1/2 wpstg--translate-y-1/2">
                                        <span class="wpstg-inline-flex wpstg-items-center wpstg-px-3 wpstg-py-0.5 wpstg-rounded-full wpstg-text-sm wpstg-font-medium wpstg-bg-indigo-500 wpstg-text-white wpstg-border wpstg-border-solid wpstg-border-indigo-500">
                                            <?php esc_html_e('Most Popular', 'wp-staging'); ?>
                                        </span>
                                    </div>
                                    <h4 class="wpstg-text-lg wpstg-font-bold wpstg-text-gray-900 dark:wpstg-text-gray-100 wpstg-m-0 wpstg-mt-2"><?php esc_html_e('Developer Plan', 'wp-staging'); ?></h4>
                                    <p class="wpstg-text-sm wpstg-text-gray-500 dark:wpstg-text-gray-400 wpstg-m-0 wpstg-mt-2 wpstg-mb-4"><?php esc_html_e('Perfect for growing freelancers and devs.', 'wp-staging'); ?></p>
                                    <ul class="wpstg-text-sm wpstg-text-gray-600 dark:wpstg-text-gray-400 wpstg-space-y-3 wpstg-mb-6 wpstg-flex-grow wpstg-list-none wpstg-p-0 wpstg-m-0">
                                        <li class="wpstg-flex wpstg-items-center">
                                            <?php echo $checkIcon; // phpcs:ignore ?>
                                            <strong><?php esc_html_e('Up to 25 Sites', 'wp-staging'); ?></strong>
                                        </li>
                                        <?php foreach ($sharedPlanFeatures as $feature) : ?>
                                        <li class="wpstg-flex wpstg-items-center"><?php echo $checkIcon; // phpcs:ignore ?><?php echo esc_html($feature); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <a href="<?php echo esc_url($upgradeUrl . Licensing::DEVELOPER_LICENSE_UPGRADE_PLAN_KEY); ?>" target="_blank" class="wpstg-license-upgrade-button wpstg-block wpstg-w-full wpstg-text-center wpstg-px-4 wpstg-py-3 wpstg-font-medium wpstg-rounded-md wpstg-transition-colors wpstg-shadow-sm wpstg-no-underline">
                                        <?php esc_html_e('Upgrade to Developer', 'wp-staging'); ?>
                                    </a>
                                </div>
                                <?php endif; ?>

                                <?php if (in_array($licensePriceId, $upgradeToAgency)) : ?>
                                <!-- Agency Plan -->
                                <div class="wpstg-border wpstg-border-solid wpstg-border-gray-200 dark:wpstg-border-gray-700 wpstg-rounded-lg wpstg-p-6 wpstg-flex wpstg-flex-col">
                                    <h4 class="wpstg-text-lg wpstg-font-bold wpstg-text-gray-900 dark:wpstg-text-gray-100 wpstg-m-0"><?php esc_html_e('Agency Plan', 'wp-staging'); ?></h4>
                                    <p class="wpstg-text-sm wpstg-text-gray-500 dark:wpstg-text-gray-400 wpstg-m-0 wpstg-mt-2 wpstg-mb-4"><?php esc_html_e('For large agencies needing maximum capacity.', 'wp-staging'); ?></p>
                                    <ul class="wpstg-text-sm wpstg-text-gray-600 dark:wpstg-text-gray-400 wpstg-space-y-3 wpstg-mb-6 wpstg-flex-grow wpstg-list-none wpstg-p-0 wpstg-m-0">
                                        <li class="wpstg-flex wpstg-items-center">
                                            <?php echo $checkIcon; // phpcs:ignore ?>
                                            <strong><?php esc_html_e('Up to 99 Sites', 'wp-staging'); ?></strong>
                                        </li>
                                        <?php foreach ($sharedPlanFeatures as $feature) : ?>
                                        <li class="wpstg-flex wpstg-items-center"><?php echo $checkIcon; // phpcs:ignore ?><?php echo esc_html($feature); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <a href="<?php echo esc_url($upgradeUrl . Licensing::AGENCY_LICENSE_UPGRADE_PLAN_KEY); ?>" target="_blank" class="wpstg-license-upgrade-button wpstg-block wpstg-w-full wpstg-text-center wpstg-px-4 wpstg-py-2 wpstg-font-medium wpstg-rounded-md wpstg-transition-colors wpstg-no-underline">
                                        <?php esc_html_e('Upgrade to Agency', 'wp-staging'); ?>
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Card Footer -->
                        <div class="wpstg-mt-4 wpstg-p-4 wpstg-text-center wpstg-border wpstg-border-solid wpstg-border-gray-200 dark:wpstg-border-gray-700 wpstg-rounded-md">
                            <a href="https://wp-staging.com/your-account" target="_blank" class="wpstg-text-indigo-600 dark:wpstg-text-indigo-400 hover:wpstg-text-indigo-800 dark:hover:wpstg-text-indigo-300 wpstg-text-sm wpstg-font-medium wpstg-flex wpstg-items-center wpstg-justify-center wpstg-no-underline">
                                <?php esc_html_e('Manage billing and invoices in your Account Panel', 'wp-staging'); ?>
                                <svg class="wpstg-h-4 wpstg-w-4 wpstg-ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        </div>
                    </section>
                </div>

            <?php else : ?>
                <!-- Inactive License Card -->
                <div class="wpstg-max-w-xl">
                    <div class="wpstg-card">
                        <!-- Card Header -->
                        <div class="wpstg-card-header">
                            <h2 class="wpstg-heading-lg">
                                <?php esc_html_e('Activate WP Staging Pro', 'wp-staging'); ?>
                            </h2>
                            <p class="wpstg-text-muted wpstg-text-sm wpstg-m-0 wpstg-mt-1">
                                <?php esc_html_e('Enter your license key to unlock all Pro features', 'wp-staging'); ?>
                            </p>
                        </div>

                        <!-- Card Body -->
                        <div class="wpstg-card-body">
                            <?php if (isset($license->error) && $license->error === 'expired') :
                                $licenseKey = trim(get_option(Licensing::WPSTG_LICENSE_KEY, ''));
                                $renewUrl   = Language::localizeCheckoutUrl('https://wp-staging.com/checkout/?nocache=true&edd_license_key=' . urlencode($licenseKey) . '&download_id=11');
                                ?>
                            <!-- Expired License Alert -->
                            <div class="wpstg-callout wpstg-callout-danger wpstg-mb-5">
                                <svg class="wpstg-h-5 wpstg-w-5 wpstg-flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="wpstg-text-sm wpstg-font-medium wpstg-m-0">
                                        <?php echo esc_html__('Your license expired on ', 'wp-staging') . esc_html($licenseExpiry); ?>
                                    </p>
                                    <a href="<?php echo esc_url($renewUrl); ?>" target="_blank" class="wpstg-btn wpstg-btn-sm wpstg-btn-primary wpstg-mt-3 wpstg-no-underline">
                                        <?php esc_html_e('Renew License', 'wp-staging'); ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>

                            <form method="post" action="#">
                                <div class="wpstg-mb-4">
                                    <label for="wpstg_input_field_license_key" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-gray-700 dark:wpstg-text-gray-200 wpstg-mb-2">
                                        <?php esc_html_e('License Key', 'wp-staging'); ?>
                                    </label>
                                    <input
                                        type="text"
                                        name="wpstg_license_key"
                                        id="wpstg_input_field_license_key"
                                        class="wpstg-input wpstg-input-lg"
                                        placeholder="<?php esc_attr_e('Enter your license key', 'wp-staging'); ?>"
                                        value="<?php echo esc_attr(get_option('wpstg_license_key', '')); ?>"
                                    >
                                </div>
                                <input type="hidden" name="wpstg_activate_license" value="1">
                                <?php wp_nonce_field('wpstg_license_nonce', 'wpstg_license_nonce'); ?>
                                <button type="submit" class="wpstg-license-activate-button wpstg-btn wpstg-btn-lg wpstg-btn-primary wpstg-w-full">
                                    <?php esc_html_e('Activate License', 'wp-staging'); ?>
                                </button>
                            </form>
                        </div>

                        <!-- Card Footer -->
                        <div class="wpstg-card-footer wpstg-bg-gray-50 dark:wpstg-bg-gray-900">
                            <p class="wpstg-text-muted wpstg-text-sm wpstg-m-0 wpstg-text-center">
                                <?php esc_html_e("Don't have a license key?", 'wp-staging'); ?>
                                <a href="https://wp-staging.com?utm_source=wpstg-license-ui&utm_medium=website&utm_campaign=enter-license-key&utm_id=purchase-key&utm_content=wpstaging" target="_blank" class="wpstg-text-blue-600 dark:wpstg-text-blue-400 hover:wpstg-text-blue-800 dark:hover:wpstg-text-blue-300 wpstg-no-underline wpstg-font-medium">
                                    <?php esc_html_e('Purchase one', 'wp-staging'); ?>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    $hideNewsfeed = true;
    require_once(WPSTG_VIEWS_DIR . '_main/footer.php');
    ?>
</div>
