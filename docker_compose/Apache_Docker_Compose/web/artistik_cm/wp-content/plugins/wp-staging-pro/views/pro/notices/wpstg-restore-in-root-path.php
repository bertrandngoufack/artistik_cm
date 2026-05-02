<?php

/**
 * @see \WPStaging\Pro\Notices\Notices::showWpstgRestoreFileExistsInRootPath
 * @var string $filePath The wpstg-restore.php file path
 */

use WPStaging\Framework\Facades\Escape;

?>

<div class="notice notice-error">
        <?php
        echo sprintf(
            Escape::escapeHtml(
                __('<p><strong>Important:</strong></p><p>The WP Staging restore script file <strong>wpstg-restore.php</strong> was detected in the root directory <code>%s</code>. For security reasons, delete it if it is no longer needed.</p>', 'wp-staging')
            ),
            esc_html($filePath)
        );
        ?>
        <p>
        <form method="post" action="<?php echo esc_url(admin_url("admin-post.php?action=wpstg_download_restorer")); ?>">
            <input type="submit" name="wpstg-download-restorer" id="wpstg-download-restorer" style="border-radius: 4px; background-color: #1a73e8; color: white; padding:10px 20px 10px 20px;text-decoration: none;" class="wpstg-button--link" value="<?php esc_html_e('Delete wpstg-restore.php Now', 'wp-staging');?>">
            <input type="hidden" name="wpstg-remove-restorer" value="1">
            <?php wp_nonce_field('wpstg_restorer_nonce', 'wpstg_restorer_nonce');?>
        </form>
        </p>
</div>
