<?php

/**
 * Password Protection Modal
 *
 * Modal content for setting or changing the connection password.
 * Matches Remote Sync modal design system with full dark mode support.
 */

?>
<div id="wpstg--modal--remote-sync--protect" data-confirmButtonText="<?php esc_attr_e('Save', 'wp-staging'); ?>" style="display: none">
    <div class="wpstg-remote-sync-modal-content wpstg-bg-white dark:wpstg-bg-dark-boxes wpstg-text-left">

        <!-- Header -->
        <div class="wpstg-remote-sync-header">
            <div class="wpstg-remote-sync-header-row">
                <div class="wpstg-remote-sync-header-left">
                    <h3 class="wpstg-remote-sync-modal-title dark:wpstg-text-gray-100">
                        <?php esc_html_e('Password Protection', 'wp-staging'); ?>
                    </h3>
                    <span class="wpstg-remote-sync-steps-indicator dark:wpstg-text-gray-400">
                        <?php esc_html_e('Secure your connection key', 'wp-staging'); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="wpstg-remote-sync-body">
            <!-- Info Callout -->
            <div class="wpstg-remote-sync-callout wpstg-remote-sync-callout--info">
                <svg class="wpstg-remote-sync-callout-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="wpstg-remote-sync-callout-text">
                    <?php esc_html_e('Add a password to secure the connection. You will need to enter this password each time you import data.', 'wp-staging'); ?>
                </p>
            </div>

            <!-- Password Input Section -->
            <div class="wpstg-remote-sync-field-section">
                <label for="wpstg--remote-sync--connection-password" class="wpstg-remote-sync-label dark:wpstg-text-gray-200 wpstg-mb-2">
                    <?php esc_html_e('New Password', 'wp-staging'); ?>
                </label>

                <div class="wpstg-remote-sync-password-input-wrapper wpstg-password-toggle-wrapper wpstg-mt-2">
                    <input
                        id="wpstg--remote-sync--connection-password"
                        type="password"
                        placeholder="<?php echo esc_attr__('Enter a secure password...', 'wp-staging'); ?>"
                        class="wpstg-input wpstg-input-lg wpstg-remote-sync-password-input"
                        autocomplete="new-password"
                    >
                    <?php require WPSTG_VIEWS_DIR . '_main/partials/password-toggle-button.php'; ?>
                </div>
            </div>

            <!-- Confirm Password Input Section -->
            <div class="wpstg-remote-sync-field-section wpstg-mt-4">
                <label for="wpstg--remote-sync--connection-password-confirm" class="wpstg-remote-sync-label dark:wpstg-text-gray-200 wpstg-mb-2">
                    <?php esc_html_e('Confirm Password', 'wp-staging'); ?>
                </label>

                <div class="wpstg-remote-sync-password-input-wrapper wpstg-password-toggle-wrapper wpstg-mt-2">
                    <input
                        id="wpstg--remote-sync--connection-password-confirm"
                        type="password"
                        placeholder="<?php echo esc_attr__('Re-enter password...', 'wp-staging'); ?>"
                        class="wpstg-input wpstg-input-lg wpstg-remote-sync-password-input"
                        autocomplete="new-password"
                    >
                    <?php require WPSTG_VIEWS_DIR . '_main/partials/password-toggle-button.php'; ?>
                </div>

                <!-- Error message for password mismatch -->
                <p id="wpstg--remote-sync--password-mismatch-error" class="wpstg-remote-sync-error-text wpstg-text-red-600 dark:wpstg-text-red-400 wpstg-text-sm wpstg-mt-2 wpstg-m-0" style="display: none;">
                    <?php esc_html_e('Passwords do not match.', 'wp-staging'); ?>
                </p>

                <p class="wpstg-remote-sync-hint-secondary wpstg-mt-2">
                    <?php esc_html_e('Choose a strong password to protect your remote connection.', 'wp-staging'); ?>
                </p>
            </div>
        </div>
    </div>
</div>
