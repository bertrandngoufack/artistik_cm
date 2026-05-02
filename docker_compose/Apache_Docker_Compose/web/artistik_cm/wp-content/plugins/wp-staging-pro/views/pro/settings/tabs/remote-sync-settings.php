<?php

/**
 * Connection Keys Settings Tab
 *
 * Displays the connection key and password protection settings for remote site synchronization.
 * Clean SaaS admin aesthetic using Tailwind CSS with wpstg- prefix.
 */

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Language\Language;
use WPStaging\Pro\License\Licensing;
use WPStaging\Pro\RemoteSync\ConnectionKey;
use WPStaging\Pro\WPStagingPro;

$isValidLicense      = WPStagingPro::isValidLicense();
$licensing           = WPStaging::make(Licensing::class);
$licenseData         = get_option('wpstg_license_status');
$licenseId           = empty($licenseData->license_id) ? '' : $licenseData->license_id;
$canUseRemoteSync    = $licensing->isActiveAgencyOrDeveloperPlan();
$upgradeUrl          = Language::localizeCheckoutUrl("https://wp-staging.com/checkout/?nocache=true&edd_action=sl_license_upgrade&license_id=$licenseId&upgrade_id=" . Licensing::DEVELOPER_LICENSE_UPGRADE_PLAN_KEY);
$connectionKeyObj    = WPStaging::make(ConnectionKey::class);
$isProtected         = $connectionKeyObj->isProtected();
$isRemoteSyncEnabled = ConnectionKey::isEnabled();
$connectionKey       = $connectionKeyObj->getConnectionKey();
$connectionKeyMasked = strlen($connectionKey) > 16
    ? substr($connectionKey, 0, 8) . str_repeat("\xE2\x80\xA2", 8) . substr($connectionKey, -8)
    : str_repeat("\xE2\x80\xA2", strlen($connectionKey));

include(WPSTG_PLUGIN_DIR . 'views/pro/remote-sync/modal/protect.php');
include(WPSTG_PLUGIN_DIR . 'views/pro/remote-sync/modal/unprotect.php');
?>

<div class="wpstg-remote-sync-settings wpstg-max-w-3xl wpstg-py-2">

    <div class="wpstg-provider-page-header">
        <h1 class="wpstg-text-2xl wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100"><?php esc_html_e('Remote Sync Connection Key', 'wp-staging'); ?></h1>
        <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400"><?php esc_html_e('Create a connection key on this source site and paste it into Remote Sync on another site. This authorizes the other site to securely pull and import data from this WordPress installation.', 'wp-staging'); ?></p>
    </div>

    <?php if (!$isValidLicense) : ?>
        <!-- Upsell: License not activated -->
        <div class="wpstg-callout wpstg-callout-info wpstg-mb-6">
            <div class="wpstg-icon-box wpstg-icon-box-blue">
                <svg class="wpstg-h-5 wpstg-w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div class="wpstg-flex-1">
                <h3 class="wpstg-heading-lg">
                    <?php esc_html_e('Unlock Remote Sync', 'wp-staging'); ?>
                </h3>
                <p class="wpstg-text-body wpstg-text-sm wpstg-m-0">
                    <?php esc_html_e('Pull a WordPress site from another server using a secure connection key.', 'wp-staging'); ?>
                </p>
                <p class="wpstg-text-body wpstg-text-sm wpstg-m-0 wpstg-mb-4">
                    <?php esc_html_e('Activate your license or upgrade to a Developer or Agency plan to use Remote Sync.', 'wp-staging'); ?>
                </p>
                <a href="<?php echo esc_url(Language::localizePricingUrl('https://wp-staging.com/#pricing')); ?>" target="_blank" rel="noopener noreferrer" class="wpstg-btn wpstg-btn-md wpstg-btn-primary wpstg-no-underline">
                    <?php esc_html_e('Unlock Remote Sync', 'wp-staging'); ?>
                    <svg class="wpstg-btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($isValidLicense && !$canUseRemoteSync) : ?>
        <!-- Upsell: License active, wrong plan -->
        <div class="wpstg-callout wpstg-callout-info wpstg-mb-6">
            <div class="wpstg-icon-box wpstg-icon-box-blue">
                <svg class="wpstg-h-5 wpstg-w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div class="wpstg-flex-1">
                <h3 class="wpstg-heading-lg">
                    <?php esc_html_e('Unlock Remote Sync', 'wp-staging'); ?>
                </h3>
                <p class="wpstg-text-body wpstg-text-sm wpstg-m-0">
                    <?php esc_html_e('Pull a WordPress site from another server using a secure connection key.', 'wp-staging'); ?>
                </p>
                <p class="wpstg-text-body wpstg-text-sm wpstg-m-0 wpstg-mb-4">
                    <?php esc_html_e('Remote Sync requires a Developer or Agency plan.', 'wp-staging'); ?>
                </p>
                <a href="<?php echo esc_url($upgradeUrl); ?>" target="_blank" rel="noopener noreferrer" class="wpstg-btn wpstg-btn-md wpstg-btn-primary wpstg-no-underline">
                    <?php esc_html_e('Unlock Remote Sync', 'wp-staging'); ?>
                </a>
                <p class="wpstg-text-xs wpstg-text-muted wpstg-m-0 wpstg-mt-2">
                    <?php esc_html_e('You only pay the prorated difference.', 'wp-staging'); ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($isValidLicense && $canUseRemoteSync) : ?>
    <div class="wpstg-space-y-6">

        <!-- Remote Sync Enable/Disable Card -->
        <div class="wpstg-card wpstg-card-body">
            <div class="wpstg-flex wpstg-items-center wpstg-justify-between">
                <div class="wpstg-flex-start wpstg-flex-1">
                    <div class="wpstg-icon-box wpstg-icon-box-blue">
                        <svg class="wpstg-h-5 wpstg-w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728M9.172 14.828a4 4 0 010-5.656m5.656 0a4 4 0 010 5.656M12 12h.01"/>
                        </svg>
                    </div>
                    <div class="wpstg-flex-1 wpstg-min-w-0">
                        <div class="wpstg-flex wpstg-items-center wpstg-gap-2 wpstg-mb-1">
                            <h3 class="wpstg-heading-lg wpstg-mb-0">
                                <?php esc_html_e('Accept Incoming Connections', 'wp-staging'); ?>
                            </h3>
                            <span id="wpstg-remote-sync-status-badge" class="wpstg-badge <?php echo $isRemoteSyncEnabled ? 'wpstg-badge-green' : 'wpstg-badge-gray'; ?>">
                                <?php echo $isRemoteSyncEnabled ? esc_html__('Enabled', 'wp-staging') : esc_html__('Disabled', 'wp-staging'); ?>
                            </span>
                        </div>
                        <p class="wpstg-text-muted wpstg-text-sm wpstg-leading-relaxed wpstg-m-0">
                            <?php esc_html_e('When disabled, other sites cannot import data from this site using Remote Sync.', 'wp-staging'); ?>
                        </p>
                    </div>
                </div>
                <input
                    type="checkbox"
                    id="wpstg-remote-sync-enabled-toggle"
                    class="wpstg-switch"
                    <?php echo $isRemoteSyncEnabled ? 'checked' : ''; ?>
                />
            </div>
        </div>

        <div id="wpstg-remote-sync-settings-content" class="wpstg-space-y-6 <?php echo $isRemoteSyncEnabled ? 'wpstg-remote-sync-settings-enabled' : 'wpstg-remote-sync-settings-disabled'; ?>">

        <!-- Connection Key Card -->
        <div class="wpstg-card wpstg-card-body">
            <div class="wpstg-flex-start">
                <div class="wpstg-icon-box wpstg-icon-box-blue">
                    <svg class="wpstg-h-5 wpstg-w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <div class="wpstg-flex-1 wpstg-min-w-0">
                    <h3 class="wpstg-heading-lg">
                        <?php esc_html_e('Connection Key', 'wp-staging'); ?>
                    </h3>
                    <p class="wpstg-text-muted wpstg-text-sm wpstg-leading-relaxed wpstg-m-0">
                        <?php echo sprintf(
                            esc_html__('Copy this key and paste it on the remote site under %s.', 'wp-staging'),
                            '<span class="wpstg-font-medium wpstg-text-gray-900 dark:wpstg-text-gray-100">' . esc_html__('Backup & Migration', 'wp-staging') . ' → ' . esc_html__('Sync from Remote Site', 'wp-staging') . '</span>'
                        ); ?>
                    </p>
                </div>
            </div>

            <div class="wpstg-mt-5 wpstg-space-y-4">
                <!-- Dashed Border Key Display with Copy Button Inside -->
                <div class="wpstg-remote-sync-key-box wpstg-relative wpstg-rounded-xl wpstg-border-2 wpstg-border-dashed wpstg-border-blue-300/50 dark:wpstg-border-blue-600/50 wpstg-p-4 wpstg-pb-14">
                    <code
                        id="wpstg-remote-sync-connection-key"
                        class="wpstg-block wpstg-font-mono wpstg-text-sm wpstg-text-gray-900 dark:wpstg-text-gray-100 wpstg-break-all wpstg-leading-relaxed wpstg-bg-transparent"
                        data-full-key="<?php echo esc_attr($connectionKey); ?>"
                        data-masked-key="<?php echo esc_attr($connectionKeyMasked); ?>"
                        data-masked="true"
                    ><?php echo esc_html($connectionKeyMasked); ?></code>
                    <div class="wpstg-absolute wpstg-bottom-3 wpstg-right-3 wpstg-flex wpstg-items-center wpstg-gap-2">
                    <button
                        id="wpstg-remote-sync-key-visibility-toggle"
                        type="button"
                        class="wpstg-btn wpstg-btn-sm wpstg-btn-ghost"
                        aria-label="<?php echo esc_attr__('Show or hide connection key', 'wp-staging'); ?>"
                        title="<?php echo esc_attr__('Show or hide connection key', 'wp-staging'); ?>"
                    >
                        <svg class="wpstg-key-eye-closed wpstg-h-4 wpstg-w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                        <svg class="wpstg-key-eye-open wpstg-h-4 wpstg-w-4 wpstg-hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                    <button
                        id="wpstg-remote-sync-connection-key-copy-btn"
                        type="button"
                        class="wpstg-btn wpstg-btn-sm wpstg-btn-primary"
                        data-copy-text="<?php echo esc_attr__('Copy Key', 'wp-staging'); ?>"
                        data-copied-text="<?php echo esc_attr__('Copied!', 'wp-staging'); ?>"
                        aria-label="<?php echo esc_attr__('Copy connection key to clipboard', 'wp-staging'); ?>"
                    >
                        <svg class="wpstg-copy-icon wpstg-btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <svg class="wpstg-check-icon wpstg-btn-icon-sm wpstg-hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="wpstg-copy-btn-text"><?php esc_html_e('Copy Key', 'wp-staging'); ?></span>
                    </button>
                    </div>
                </div>

                <!-- Security Hint -->
                <div class="wpstg-flex wpstg-items-center wpstg-gap-2 wpstg-text-sm wpstg-text-muted">
                    <svg class="wpstg-h-4 wpstg-w-4 wpstg-flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span><?php esc_html_e('Treat this key like a password. Don\'t share it publicly.', 'wp-staging'); ?></span>
                </div>

                <!-- Reset Key Button -->
                <button
                    id="wpstg-remote-sync-regenerate-connection-key"
                    type="button"
                    class="wpstg-btn wpstg-btn-ghost"
                >
                    <svg class="wpstg-icon-loader wpstg-btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <?php esc_html_e('Reset Key', 'wp-staging'); ?>
                </button>
            </div>
        </div>

        <!-- Password Protection Card -->
        <div class="wpstg-card wpstg-card-body">
            <div class="wpstg-flex-start">
                <div class="wpstg-icon-box wpstg-icon-box-green">
                    <svg class="wpstg-h-5 wpstg-w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="wpstg-flex-1 wpstg-min-w-0">
                    <div class="wpstg-flex wpstg-items-center wpstg-gap-2 wpstg-mb-1">
                        <h3 class="wpstg-heading-lg wpstg-mb-0">
                            <?php esc_html_e('Password Protection', 'wp-staging'); ?>
                        </h3>
                        <?php if ($isProtected) : ?>
                            <span class="wpstg-badge wpstg-badge-green">
                                <svg class="wpstg-h-3 wpstg-w-3" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <?php esc_html_e('Enabled', 'wp-staging'); ?>
                            </span>
                        <?php else : ?>
                            <span class="wpstg-badge wpstg-badge-gray">
                                <?php esc_html_e('Optional', 'wp-staging'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="wpstg-text-muted wpstg-text-sm wpstg-leading-relaxed wpstg-m-0">
                        <?php if ($isProtected) : ?>
                            <?php esc_html_e('Your connection key is protected by a password.', 'wp-staging'); ?>
                        <?php else : ?>
                            <?php esc_html_e('Adds an extra password layer on top of the connection key. Recommended when importing data into a production site.', 'wp-staging'); ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <div class="wpstg-mt-5">
                <?php if (!$isProtected) : ?>
                    <button
                        id="wpstg-remote-sync-protect"
                        type="button"
                        class="wpstg-btn wpstg-btn-md wpstg-btn-outline"
                    >
                        <svg class="wpstg-btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <?php esc_html_e('Add Password', 'wp-staging'); ?>
                    </button>
                <?php else : ?>
                    <div class="wpstg-flex wpstg-items-center wpstg-gap-3 wpstg-flex-wrap">
                        <button
                            id="wpstg-remote-sync-protect"
                            type="button"
                            class="wpstg-btn wpstg-btn-md wpstg-btn-secondary"
                        >
                            <svg class="wpstg-btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                            <?php esc_html_e('Change Password', 'wp-staging'); ?>
                        </button>
                        <button
                            id="wpstg-remote-sync-unprotect"
                            type="button"
                            class="wpstg-btn wpstg-btn-ghost wpstg-text-red-600 dark:wpstg-text-red-400 hover:wpstg-text-red-700 dark:hover:wpstg-text-red-300"
                        >
                            <?php esc_html_e('Remove Password', 'wp-staging'); ?>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        </div><!-- /#wpstg-remote-sync-settings-content -->

    </div>
    <?php endif; ?>
</div>
