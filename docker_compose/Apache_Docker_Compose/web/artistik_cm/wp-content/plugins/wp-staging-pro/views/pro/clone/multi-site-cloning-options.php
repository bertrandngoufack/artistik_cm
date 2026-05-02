<?php

use WPStaging\Pro\WPStagingPro;

if (WPStagingPro::isValidLicense()) :
    require_once(WPSTG_VIEWS_DIR . "staging/index.php");
else :?>
<div class="notice notice-error">
    <p>
        <strong>
            <?php esc_html_e('WordPress Multisite is not supported in disabled or unregistered version of WP Staging Pro', 'wp-staging')?>
        </strong>
        <br>
        <?php esc_html_e('You need a valid license key to use all pro feature.', 'wp-staging')?>
        <span class="wpstg-invalid-license-key">
            <span class="wpstg-upgrade-license-container">
                <a href="<?php echo esc_url(admin_url() . 'admin.php?page=wpstg-license') ?>"  class="wpstg-upgrade-license-button" target="_self">Enter License Key</a>
            </span>
            <span class="wpstg-upgrade-license-container">
                <a href="https://wp-staging.com/?utm_source=admin_notice&utm_medium=plugin&utm_campaign=license_invalid"  class="wpstg-upgrade-license-button" target="_blank">Activate Pro Features</a>
            </span>
        </span>
    </p>
</div>
<?php endif;?>
