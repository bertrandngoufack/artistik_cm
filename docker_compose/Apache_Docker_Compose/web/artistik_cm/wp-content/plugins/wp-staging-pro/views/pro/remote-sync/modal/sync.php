<?php

/**
 * Remote Sync Modal - Two-Step Import Wizard
 *
 * Modal content for connecting to a remote site and importing data.
 * Clean design using Tailwind CSS with wpstg- prefix.
 *
 * @var string $urlAssets
 */

use WPStaging\Framework\Language\Language;
use WPStaging\Framework\Facades\UI\Alert;

?>
<div id="wpstg--modal--remote-sync" style="display: none">
    <div class="wpstg--steps wpstg--steps--slider" role="group" aria-label="<?php echo esc_attr__('Remote Sync Steps', 'wp-staging'); ?>" data-wpstg-current-step="1">

        <!-- Step 1: Authorization -->
        <div class="wpstg--step is-active" aria-hidden="false" data-wpstg-step="1" id="wpstg--step--sync-authenticate" role="dialog" aria-labelledby="wpstg-remote-sync-modal-title" aria-describedby="wpstg-remote-sync-modal-desc">
            <div class="wpstg-remote-sync-step1-content wpstg-bg-white wpstg-text-left">

                <!-- Compact Header -->
                <div class="wpstg-remote-sync-header">
                    <div class="wpstg-remote-sync-header-row">
                        <div class="wpstg-remote-sync-header-left">
                            <h3 class="wpstg-remote-sync-modal-title wpstg-text-[22px] wpstg-leading-[1.2] wpstg-font-bold" id="wpstg-remote-sync-modal-title">
                                <?php esc_html_e('Sync from Remote Site', 'wp-staging'); ?>
                            </h3>
                            <span class="wpstg-remote-sync-steps-indicator wpstg-text-[12px] wpstg-leading-[1.5] wpstg-font-medium">
                                <?php esc_html_e('Step 1 of 2', 'wp-staging'); ?>
                                <span class="wpstg-remote-sync-step-separator">·</span>
                                <?php esc_html_e('Next: Select data', 'wp-staging'); ?>
                            </span>
                        </div>
                        <button
                            type="button"
                            id="wpstg--remote-sync--close"
                            class="wpstg-remote-sync-close-btn"
                            data-action="close-modal"
                            aria-label="<?php echo esc_attr__('Close', 'wp-staging'); ?>"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 6 6 18"/>
                                <path d="m6 6 12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Slim Direction Indicator -->
                    <div class="wpstg-remote-sync-direction" id="wpstg-remote-sync-modal-desc">
                        <svg class="wpstg-remote-sync-direction-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <ellipse cx="12" cy="5" rx="9" ry="3"/>
                            <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
                            <path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"/>
                        </svg>
                        <span class="wpstg-remote-sync-direction-label"><?php esc_html_e('Remote Site', 'wp-staging'); ?></span>
                        <svg class="wpstg-remote-sync-direction-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"/>
                            <path d="m12 5 7 7-7 7"/>
                        </svg>
                        <svg class="wpstg-remote-sync-direction-icon wpstg-remote-sync-direction-icon-dest" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        <span class="wpstg-remote-sync-direction-label wpstg-remote-sync-direction-label-dest"><?php esc_html_e('This Site', 'wp-staging'); ?></span>
                    </div>
                </div>

                <!-- Content area -->
                <div class="wpstg-remote-sync-body">
                    <!-- Key Input Section (Primary Focus) -->
                    <div class="wpstg-remote-sync-key-section">
                        <div class="wpstg-remote-sync-label-row">
                            <label for="wpstg--remote-sync--connection-key" class="wpstg-remote-sync-label wpstg-text-[14px] wpstg-leading-[1.4] wpstg-font-semibold">
                                <?php esc_html_e('Remote Sync Connection Key', 'wp-staging'); ?>
                            </label>
                            <button
                                type="button"
                                class="wpstg-remote-sync-help-toggle wpstg-text-[13px] wpstg-leading-[1.5] wpstg-font-semibold"
                                aria-expanded="false"
                                aria-controls="wpstg-remote-sync-help-content"
                            ><?php esc_html_e('Where do I find this?', 'wp-staging'); ?></button>
                        </div>

                        <!-- Collapsible help ABOVE textarea (hidden by default) -->
                        <div id="wpstg-remote-sync-help-content" class="wpstg-remote-sync-help-content" role="region" hidden>
                            <span class="wpstg-remote-sync-help-text wpstg-text-[12px] wpstg-leading-[1.5] wpstg-font-medium"><?php esc_html_e('On the source site:', 'wp-staging'); ?></span>
                            <code class="wpstg-remote-sync-help-breadcrumb wpstg-text-[12px] wpstg-leading-[1.5] wpstg-font-medium"><?php esc_html_e('WP Staging → Settings → Remote Sync Connection Key', 'wp-staging'); ?></code>
                        </div>

                        <div class="wpstg-remote-sync-key-input-wrapper">
                            <textarea
                                name="remote_pull_connection_key"
                                id="wpstg--remote-sync--connection-key"
                                rows="3"
                                class="wpstg-remote-sync-key-input wpstg-text-[14px] wpstg-leading-[1.4] wpstg-font-medium"
                                placeholder="<?php echo esc_attr__('Paste your connection key here...', 'wp-staging'); ?>"
                                aria-describedby="wpstg-key-hint"
                                spellcheck="false"
                                autocomplete="off"
                            ><?php echo defined('WPSTG_REMOTE_SYNC_KEY') ? esc_textarea(WPSTG_REMOTE_SYNC_KEY) : ''; ?></textarea>
                            <button
                                type="button"
                                id="wpstg--remote-sync--clear-key"
                                class="wpstg-remote-sync-clear-btn"
                                aria-label="<?php echo esc_attr__('Clear key', 'wp-staging'); ?>"
                                hidden
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 6 6 18"/>
                                    <path d="m6 6 12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div id="wpstg-key-hint" class="wpstg-remote-sync-hint">
                            <span class="wpstg-remote-sync-hint-primary wpstg-text-[13px] wpstg-leading-[1.5] wpstg-font-medium"><?php esc_html_e('Paste the key from the source site to verify the connection.', 'wp-staging'); ?></span>
                            <span class="wpstg-remote-sync-hint-secondary wpstg-text-[12px] wpstg-leading-[1.5] wpstg-font-medium"><?php esc_html_e('You can revoke this key anytime on the source site.', 'wp-staging'); ?></span>
                        </div>
                    </div>

                    <!-- Password Section (Collapsed by Default) -->
                    <div class="wpstg-remote-sync-password-section">
                        <label class="wpstg-checkbox-wrapper">
                            <input type="checkbox" class="wpstg-checkbox wpstg-checkbox-sm" id="wpstg--remote-sync--has-password" aria-controls="wpstg-remote-sync-password-field">
                            <span class="wpstg-label-muted"><?php esc_html_e('Connection key requires a password', 'wp-staging'); ?></span>
                        </label>

                        <!-- Shown only when checkbox is checked -->
                        <div id="wpstg-remote-sync-password-field" class="wpstg-remote-sync-password-field" hidden>
                            <div class="wpstg-remote-sync-password-input-wrapper wpstg-password-toggle-wrapper">
                                <input
                                    type="password"
                                    name="remote_pull_connection_password"
                                    id="wpstg--remote-sync--connection-password"
                                    class="wpstg-input wpstg-input-sm wpstg-text-[14px] wpstg-leading-[1.4] wpstg-font-medium"
                                    placeholder="<?php echo esc_attr__('Connection key password', 'wp-staging'); ?>"
                                    aria-describedby="wpstg-password-hint"
                                />
                                <?php require WPSTG_VIEWS_DIR . '_main/partials/password-toggle-button.php'; ?>
                            </div>
                            <p id="wpstg-password-hint" class="wpstg-remote-sync-password-hint wpstg-text-[12px] wpstg-leading-[1.5] wpstg-font-medium">
                                <?php esc_html_e('Enter the password set when the key was created.', 'wp-staging'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- HTTP Authentication Section (Collapsed by Default) -->
                    <div class="wpstg-remote-sync-password-section">
                        <label class="wpstg-checkbox-wrapper">
                            <input type="checkbox" class="wpstg-checkbox wpstg-checkbox-sm" id="wpstg--remote-sync--has-http-auth" aria-controls="wpstg-remote-sync-http-auth-fields">
                            <span class="wpstg-label-muted"><?php esc_html_e('Remote site requires HTTP authentication', 'wp-staging'); ?></span>
                        </label>

                        <!-- Shown only when checkbox is checked -->
                        <div id="wpstg-remote-sync-http-auth-fields" class="wpstg-remote-sync-password-field" hidden>
                            <div class="wpstg-remote-sync-password-input-wrapper wpstg-mb-3">
                                <input
                                    type="text"
                                    name="remote_pull_http_auth_username"
                                    id="wpstg--remote-sync--http-auth-username"
                                    class="wpstg-input wpstg-input-sm wpstg-text-[14px] wpstg-leading-[1.4] wpstg-font-medium"
                                    placeholder="<?php echo esc_attr__('HTTP username', 'wp-staging'); ?>"
                                    autocomplete="username"
                                />
                            </div>
                            <div class="wpstg-remote-sync-password-input-wrapper wpstg-password-toggle-wrapper">
                                <input
                                    type="password"
                                    name="remote_pull_http_auth_password"
                                    id="wpstg--remote-sync--http-auth-password"
                                    class="wpstg-input wpstg-input-sm wpstg-text-[14px] wpstg-leading-[1.4] wpstg-font-medium"
                                    placeholder="<?php echo esc_attr__('HTTP password', 'wp-staging'); ?>"
                                    autocomplete="current-password"
                                />
                                <?php require WPSTG_VIEWS_DIR . '_main/partials/password-toggle-button.php'; ?>
                            </div>
                            <p class="wpstg-remote-sync-password-hint wpstg-text-[12px] wpstg-leading-[1.5] wpstg-font-medium">
                                <?php esc_html_e('Enter the HTTP Basic Auth credentials if the remote site is protected (e.g. by .htpasswd or Plesk).', 'wp-staging'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Connection Test Result -->
                    <div id="wpstg--remote-sync--connection-test--result">
                        <?php Alert::renderCloseable(); ?>
                    </div>
                </div>

                <!-- Footer -->
                <div class="wpstg-remote-sync-footer">
                    <a href="<?php echo esc_url(Language::localizeDocsUrl('https://wp-staging.com/docs/pull-a-wordpress-site-from-one-server-to-another/')); ?>" target="_blank" rel="noopener noreferrer" class="wpstg-remote-sync-docs-link">
                        <?php esc_html_e('Documentation', 'wp-staging'); ?>
                        <svg class="wpstg-remote-sync-external-icon" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>
                    <div class="wpstg-remote-sync-footer-buttons">
                        <button
                            id="wpstg--remote-sync--cancel-step1"
                            type="button"
                            class="wpstg-btn wpstg-btn-md wpstg-btn-secondary wpstg-text-[14px] wpstg-leading-[1.2]"
                            data-action="close-modal"
                        >
                            <?php esc_html_e('Cancel', 'wp-staging'); ?>
                        </button>
                        <button
                            id="wpstg--remote-sync--connect"
                            type="button"
                            class="wpstg-btn wpstg-btn-md wpstg-btn-primary wpstg-text-[14px] wpstg-leading-[1.2]"
                            disabled
                        >
                            <span class="wpstg--button-loader">
                                <span class="wpstg-loader"></span>
                            </span>
                            <span class="wpstg-btn-text"><?php esc_html_e('Verify Connection', 'wp-staging'); ?></span>
                            <span class="wpstg-btn-loading" hidden><?php esc_html_e('Verifying...', 'wp-staging'); ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Select Data to Import -->
        <div class="wpstg--step" aria-hidden="true" data-wpstg-step="2" id="wpstg--step--sync-start" role="dialog" aria-labelledby="wpstg-remote-sync-step2-title" aria-describedby="wpstg-remote-sync-step2-desc">
            <div class="wpstg-remote-sync-step2-content wpstg-bg-white wpstg-text-left">

                <!-- Compact Header (matches Step 1) -->
                <div class="wpstg-remote-sync-header">
                    <div class="wpstg-remote-sync-header-row">
                        <div class="wpstg-remote-sync-header-left">
                            <h3 class="wpstg-remote-sync-modal-title wpstg-text-[22px] wpstg-leading-[1.2] wpstg-font-bold" id="wpstg-remote-sync-step2-title">
                                <?php esc_html_e('Select what to overwrite', 'wp-staging'); ?>
                            </h3>
                            <span class="wpstg-remote-sync-steps-indicator wpstg-text-[12px] wpstg-leading-[1.5] wpstg-font-medium">
                                <?php esc_html_e('Step 2 of 2', 'wp-staging'); ?>
                                <span class="wpstg-remote-sync-step-separator">·</span>
                                <span id="wpstg--remote-sync--auth-expiry-inline"></span>
                            </span>
                        </div>
                        <button
                            type="button"
                            id="wpstg--remote-sync--close-step2"
                            class="wpstg-remote-sync-close-btn"
                            data-action="close-modal"
                            aria-label="<?php echo esc_attr__('Close', 'wp-staging'); ?>"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 6 6 18"/>
                                <path d="m6 6 12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Slim Direction Indicator (same as Step 1) -->
                    <div class="wpstg-remote-sync-direction" id="wpstg-remote-sync-step2-desc">
                        <svg class="wpstg-remote-sync-direction-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <ellipse cx="12" cy="5" rx="9" ry="3"/>
                            <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
                            <path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"/>
                        </svg>
                        <span class="wpstg-remote-sync-direction-label"><?php esc_html_e('Remote Site', 'wp-staging'); ?></span>
                        <svg class="wpstg-remote-sync-direction-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"/>
                            <path d="m12 5 7 7-7 7"/>
                        </svg>
                        <svg class="wpstg-remote-sync-direction-icon wpstg-remote-sync-direction-icon-dest" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        <span class="wpstg-remote-sync-direction-label wpstg-remote-sync-direction-label-dest"><?php esc_html_e('This Site', 'wp-staging'); ?></span>
                    </div>
                </div>

                <!-- Content area -->
                <div class="wpstg-remote-sync-body wpstg-remote-sync-body--compact">
                    <!-- From/To Summary Block -->
                    <div class="wpstg-remote-sync-from-to-summary">
                        <div class="wpstg-remote-sync-from-to-row">
                            <span class="wpstg-remote-sync-from-to-label"><?php esc_html_e('From', 'wp-staging'); ?></span>
                            <span class="wpstg-remote-sync-from-to-value wpstg-remote-sync-from-value wpstg-remote-sync-success-url">{remoteSiteUrl}</span>
                        </div>
                        <div class="wpstg-remote-sync-from-to-row">
                            <span class="wpstg-remote-sync-from-to-label"><?php esc_html_e('To', 'wp-staging'); ?></span>
                            <span class="wpstg-remote-sync-from-to-value wpstg-remote-sync-to-value wpstg-font-semibold"><?php echo esc_html(preg_replace('#^https?://#', '', site_url())); ?></span>
                            <span class="wpstg-remote-sync-to-badge"><?php esc_html_e('Target', 'wp-staging'); ?></span>
                        </div>
                    </div>

                    <!-- Data Selection Grid (2-column on wider screens) -->
                    <div class="wpstg-remote-sync-data-section wpstg-remote-sync-data-section--compact">
                        <p class="wpstg-remote-sync-data-label wpstg-text-[14px] wpstg-leading-[1.4] wpstg-font-semibold"><?php esc_html_e('Select what to overwrite:', 'wp-staging'); ?></p>

                        <div class="wpstg-remote-sync-data-grid" role="group" aria-label="<?php echo esc_attr__('Data selection options', 'wp-staging'); ?>">
                            <label class="wpstg-remote-sync-data-item wpstg-remote-sync-data-item--compact">
                                <input type="checkbox" class="wpstg-checkbox wpstg-checkbox-sm" id="wpstg--remote-sync--media" name="sync-media" value="true" checked>
                                <span class="wpstg-remote-sync-data-item-label wpstg-text-[14px] wpstg-leading-[1.4] wpstg-font-semibold"><?php esc_html_e('Media Library', 'wp-staging'); ?></span>
                            </label>

                            <label class="wpstg-remote-sync-data-item wpstg-remote-sync-data-item--compact">
                                <input type="checkbox" class="wpstg-checkbox wpstg-checkbox-sm" id="wpstg--remote-sync--themes" name="sync-themes" value="true" checked>
                                <span class="wpstg-remote-sync-data-item-label wpstg-text-[14px] wpstg-leading-[1.4] wpstg-font-semibold"><?php esc_html_e('Themes', 'wp-staging'); ?></span>
                            </label>

                            <label class="wpstg-remote-sync-data-item wpstg-remote-sync-data-item--compact">
                                <input type="checkbox" class="wpstg-checkbox wpstg-checkbox-sm" id="wpstg--remote-sync--plugins" name="sync-plugins" value="true" checked>
                                <span class="wpstg-remote-sync-data-item-label wpstg-text-[14px] wpstg-leading-[1.4] wpstg-font-semibold"><?php esc_html_e('Plugins', 'wp-staging'); ?></span>
                            </label>

                            <label class="wpstg-remote-sync-data-item wpstg-remote-sync-data-item--compact">
                                <input type="checkbox" class="wpstg-checkbox wpstg-checkbox-sm" id="wpstg--remote-sync--mu-plugins" name="sync-mu-plugins" value="true" checked>
                                <span class="wpstg-remote-sync-data-item-label wpstg-text-[14px] wpstg-leading-[1.4] wpstg-font-semibold"><?php esc_html_e('MU-Plugins', 'wp-staging'); ?></span>
                            </label>

                            <label class="wpstg-remote-sync-data-item wpstg-remote-sync-data-item--compact">
                                <input type="checkbox" class="wpstg-checkbox wpstg-checkbox-sm" id="wpstg--remote-sync--database" name="pull_database" value="true" checked>
                                <span class="wpstg-remote-sync-data-item-label wpstg-text-[14px] wpstg-leading-[1.4] wpstg-font-semibold"><?php esc_html_e('Database', 'wp-staging'); ?></span>
                                <span class="wpstg-remote-sync-data-item-help wpstg--tooltip wpstg-text-[12px] wpstg-leading-[1.5] wpstg-font-medium" tabindex="0" role="button" aria-label="<?php echo esc_attr__('Database info', 'wp-staging'); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                                    <span class="wpstg--tooltiptext wpstg--tooltiptext-backups">
                                        <?php printf(esc_html__('All database tables with prefix "%s".', 'wp-staging'), isset($GLOBALS['wpdb']->prefix) ? esc_html($GLOBALS['wpdb']->prefix) : 'wp_'); ?>
                                    </span>
                                </span>
                            </label>

                            <label class="wpstg-remote-sync-data-item wpstg-remote-sync-data-item--compact">
                                <input type="checkbox" class="wpstg-checkbox wpstg-checkbox-sm" id="wpstg--remote-sync--other-content" name="sync-other-wp-content" value="true" checked>
                                <span class="wpstg-remote-sync-data-item-label wpstg-text-[14px] wpstg-leading-[1.4] wpstg-font-semibold"><?php esc_html_e('Other Files', 'wp-staging'); ?></span>
                                <span class="wpstg-remote-sync-data-item-help wpstg--tooltip wpstg-text-[12px] wpstg-leading-[1.5] wpstg-font-medium" tabindex="0" role="button" aria-label="<?php echo esc_attr__('Other files info', 'wp-staging'); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                                    <span class="wpstg--tooltiptext wpstg--tooltiptext-backups"><?php esc_html_e('All files in wp-content that are not plugins, themes, mu-plugins and uploads.', 'wp-staging'); ?></span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Warning Callout -->
                    <div class="wpstg-remote-sync-callout wpstg-remote-sync-callout--warning">
                        <svg class="wpstg-remote-sync-callout-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                            <path d="M12 9v4"/>
                            <path d="M12 17h.01"/>
                        </svg>
                        <div class="wpstg-remote-sync-callout-content">
                            <p class="wpstg-remote-sync-callout-title wpstg-text-[14px] wpstg-leading-[1.4] wpstg-font-semibold"><?php esc_html_e('This will overwrite this site', 'wp-staging'); ?></p>
                            <p class="wpstg-remote-sync-callout-text wpstg-text-[13px] wpstg-leading-[1.5] wpstg-font-medium">
                                <?php esc_html_e('Selected items will replace existing data on the target site.', 'wp-staging'); ?>
                                <a href="#" id="wpstg--remote-sync--create-backup-link" class="wpstg-remote-sync-callout-link wpstg-text-[13px] wpstg-leading-[1.5] wpstg-font-semibold wpstg-underline wpstg-underline-offset-2"><?php esc_html_e('Create a backup first', 'wp-staging'); ?></a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer (matches Step 1) -->
                <div class="wpstg-remote-sync-footer">
                    <button
                        id="wpstg--remote-sync--back"
                        type="button"
                        class="wpstg-btn wpstg-btn-md wpstg-btn-secondary wpstg-text-[14px] wpstg-leading-[1.2]"
                    >
                        <?php esc_html_e('Back', 'wp-staging'); ?>
                    </button>
                    <button
                        id="wpstg--remote-sync--start"
                        type="button"
                        class="wpstg-btn wpstg-btn-md wpstg-btn-primary wpstg-text-[14px] wpstg-leading-[1.2]"
                        disabled
                    >
                        <?php esc_html_e('Review overwrite', 'wp-staging'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="wpstg--modal--remote-sync--start-sync" style="display: none">
    <div class="wpstg-flex wpstg-flex-col wpstg-items-center wpstg-justify-center wpstg-py-12 wpstg-px-6">
        <span class="wpstg-loader wpstg-mb-4"></span>
        <h2 class="wpstg-m-0 wpstg-mb-2 wpstg-text-lg wpstg-font-medium wpstg-text-gray-900 wpstg--modal--title">
            <?php esc_html_e('Starting Remote Sync', 'wp-staging'); ?>
        </h2>
        <p class="wpstg-m-0 wpstg-text-sm wpstg-text-gray-500 wpstg-text-center">
            <?php esc_html_e('Connecting to the remote site and preparing data transfer...', 'wp-staging'); ?>
        </p>
    </div>
</template>
