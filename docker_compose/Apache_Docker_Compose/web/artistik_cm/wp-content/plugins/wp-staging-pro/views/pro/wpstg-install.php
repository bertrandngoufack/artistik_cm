<?php
$nonce      = wp_create_nonce('wpstg-enable-free-nonce');
$buttonText = __('Install & Activate Now', 'wp-staging');
$message    = '';

if (!empty(wpstgGetFreeVersionNumberIfInstalled()) && !wpstgIsFreeVersionCompatible()) {
    $buttonText = __('Update & Activate Now', 'wp-staging');
    $message    = __('Please update the WP STAGING core plugin! The installed core plugin version is not up to date and it must be at least ', 'wp-staging') . esc_html(WPSTGPRO_MINIMUM_FREE_VERSION);
}
?>
<div class="wpstg-install-container">
    <div class="wpstg-logo-container">
        <a href="https://wp-staging.com/tell-me-more/" target="_blank" class="wpstg-logo-link">
            <img src="<?php echo esc_url(trailingslashit(WPSTG_PLUGIN_URL) . "assets/img/logo.svg") ?> " width="140" alt="WP Staging">
        </a>
    </div>
    <div class="wpstg-require-free">
        <div class="wpstg-notice">
            <?php echo esc_html__("Please activate the free ", "wp-staging");?>
            <a href="https://wordpress.org/plugins/wp-staging/" target="_blank"><?php echo esc_html__("WP Staging core plugin", "wp-staging");?></a>.
            <?php echo esc_html__("This is required to activate ", "wp-staging");?>
            <strong><?php echo esc_html__("WP Staging Pro!", "wp-staging");?></strong>
            <?php if (!empty($message)) : ?>
                <br/>
                <br/>
                <?php echo esc_html($message) ?>
            <?php endif; ?>
        </div>
        <span class="wpstg-install-message"></span>
    </div>
    <div class="wpstg-install-action-container">
        <a href="#" class="button" id="wpstg-activate-free" data-nonce="<?php echo esc_attr($nonce) ?>">
            <span id="wpstg-plugin-activation-text"><?php echo esc_html($buttonText) ?></span>
            <span id="wpstg-plugin-activation-loader"></span>
        </a>
    </div>
</div>
