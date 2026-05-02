<?php

/**
 * @see \WPStaging\Pro\Notices\Notices::getLicenseKeyExpiredNotice
 * @var string $licensekey The license key
 */

use WPStaging\Framework\Facades\Escape;
use WPStaging\Framework\Language\Language;

?>

<div class="notice notice-error">
    <p>
        <?php
        echo sprintf(
            Escape::escapeHtml(
                __('<strong>Your WP STAGING | PRO license key has been expired.</strong><br> You need a valid license key to use the backup & push feature and to get further updates. Updates are important to make sure that your version of WP STAGING is compatible with your version of WordPress and to prevent any data loss while using WP STAGING | PRO.' .
                '<span class="wpstg-invalid-license-key">
                    <span class="wpstg-upgrade-license-container">
                        <a href="%s"  class="wpstg-upgrade-license-button" target="_blank">Renew Pro License</a>
                    </span>
                </span>', 'wp-staging')
            ),
            Language::localizeCheckoutUrl('https://wp-staging.com/checkout/?nocache=true&edd_license_key=' . esc_html($licensekey) . '&download_id=11')
        );
        ?>
    </p>
</div>
