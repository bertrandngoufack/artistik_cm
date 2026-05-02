<?php

/**
 * Remove Password Modal
 *
 * Modal content for removing the connection password protection.
 * Matches Remote Sync modal design system with full dark mode support.
 */

?>
<div id="wpstg--modal--remote-sync--unprotect" data-confirmButtonText="<?php esc_attr_e('Remove', 'wp-staging'); ?>" style="display: none">
    <div class="wpstg-remote-sync-modal-content wpstg-bg-white dark:wpstg-bg-dark-boxes wpstg-text-left">

        <!-- Header -->
        <div class="wpstg-remote-sync-header">
            <div class="wpstg-remote-sync-header-row">
                <div class="wpstg-remote-sync-header-left">
                    <h3 class="wpstg-remote-sync-modal-title dark:wpstg-text-gray-100">
                        <?php esc_html_e('Remove Password', 'wp-staging'); ?>
                    </h3>
                    <span class="wpstg-remote-sync-steps-indicator dark:wpstg-text-gray-400">
                        <?php esc_html_e('This action cannot be undone', 'wp-staging'); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="wpstg-remote-sync-body">
            <!-- Warning Callout -->
            <div class="wpstg-remote-sync-callout wpstg-remote-sync-callout--warning">
                <svg class="wpstg-remote-sync-callout-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="wpstg-remote-sync-callout-content">
                    <p class="wpstg-remote-sync-callout-title">
                        <?php esc_html_e('Are you sure?', 'wp-staging'); ?>
                    </p>
                    <p class="wpstg-remote-sync-callout-text">
                        <?php esc_html_e('This will remove password protection from your connection key. Anyone with the key will be able to connect without authentication.', 'wp-staging'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
