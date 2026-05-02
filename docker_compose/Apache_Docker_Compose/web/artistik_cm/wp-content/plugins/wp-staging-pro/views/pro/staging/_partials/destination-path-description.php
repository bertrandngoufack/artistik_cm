<?php

/**
 * Used in only PRO version
 * @var string $directory
 * @see \WPStaging\Pro\Staging\Service\StagingSetup::renderCustomDirectorySettings
 */

?>

<span class="wpstg-code-segment">
    <a id="wpstg-use-target-dir" data-base-path="<?php echo esc_attr($directory) ?>" data-path="<?php echo esc_attr($directory) ?>" class="wpstg-pointer">
        <?php esc_html_e('Set Default: ', 'wp-staging') ?>
    </a>
    <span class="wpstg-use-target-dir--value"><?php echo esc_attr($directory); ?></span>
</span>
