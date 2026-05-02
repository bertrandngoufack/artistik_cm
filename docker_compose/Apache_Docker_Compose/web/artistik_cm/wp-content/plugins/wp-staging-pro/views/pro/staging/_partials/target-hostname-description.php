<?php

/**
 * Used in only PRO version
 * @var string $hostname
 * @see \WPStaging\Pro\Staging\Service\StagingSetup::renderCustomDirectorySettings
 */

?>

<span class="wpstg-code-segment">
    <a id="wpstg-use-target-hostname" data-base-uri="<?php echo esc_attr($hostname) ?>" data-uri="<?php echo esc_attr($hostname) ?>" class="wpstg-pointer">
        <?php esc_html_e('Set Default: ', 'wp-staging') ?>
    </a>
    <span class="wpstg-use-target-hostname--value"><?php echo esc_url(get_site_url()); ?></span>
</span>
