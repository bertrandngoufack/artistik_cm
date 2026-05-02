<?php

/**
 * @see \WPStaging\Pro\Notices\Notices::getLicenseKeyInvalidNotice
 */

use WPStaging\Framework\Facades\Escape;

?>
<div class="notice notice-error">
    <p>
        <?php
        echo sprintf(
            Escape::escapeHtml(
                __('<strong>Your license key of WP Staging Pro is invalid or deactivated.</strong><br>You need a valid license key to use the backup & push feature and to get further updates. Updates are important to make sure that your version of WP Staging is compatible with your version of WordPress and to prevent any data loss while using WP Staging Pro.' .
                '<span class="wpstg-invalid-license-key">
                    <span class="wpstg-upgrade-license-container">
                        <a href="%2$s"  class="wpstg-upgrade-license-button" target="_self">Enter License Key</a>
                    </span>
                    <span class="wpstg-upgrade-license-container">
                        <a href="%1$s"  class="wpstg-upgrade-license-button" target="_blank">Activate Pro Features</a>
                    </span>
                </span>', 'wp-staging')
            ),
            'https://wp-staging.com/?utm_source=admin_notice&utm_medium=plugin&utm_campaign=license_invalid',
            esc_url(admin_url() . 'admin.php?page=wpstg-license'),
            'your account'
        );
        ?>
    </p>
</div>
