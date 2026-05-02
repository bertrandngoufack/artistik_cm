<?php

use WPStaging\Backup\Entity\BackupMetadata;

?>
<div class="wpstg-backup-options-section">
    <h4 class="swal2-title wpstg-w-100">
        <?php esc_html_e('Network Options', 'wp-staging') ?>
    </h4>
    <?php if (is_super_admin()) :
        $mainSiteUrl = add_query_arg(
            [
                'page' => 'wpstg_backup',
            ],
            self_admin_url('admin.php')
        );
        ?>
    <div class="wpstg-backup-network-info">
        <span>
            <?php echo sprintf(esc_html__('You can back up your network sites by opening WP Staging', 'wp-staging')); ?>
        <br>
            <?php echo sprintf(esc_html__('on the %s or any specific network site.', 'wp-staging'), '<a href=' . esc_url($mainSiteUrl) . ' target="_blank">' . esc_html__('main site', 'wp-staging') . '</a>'); ?>
        </span>
    </div>
    <?php endif; ?>
    <div class="wpstg-backup-network-options wpstg-u-container">
        <div class="wpstg-form-group">
            <select class="wpstg-input wpstg-input-md wpstg-backup-select" name="backupType">
                <option value="<?php echo esc_attr(BackupMetadata::BACKUP_TYPE_NETWORK_SUBSITE); ?>" <?php echo (!is_main_site() ? 'selected' : '') ?>><?php is_main_site() ? esc_html_e('Backup current main site', 'wp-staging') : esc_html_e('Backup current network subsite', 'wp-staging') ?></option>
                <?php if (is_super_admin()) : ?>
                <option value="<?php echo esc_attr(BackupMetadata::BACKUP_TYPE_MULTISITE); ?>" <?php echo (is_main_site() ? 'selected' : '') ?>><?php esc_html_e('Backup entire network', 'wp-staging') ?></option>
                <?php endif; ?>
            </select>
        </div>
    </div>
</div>
