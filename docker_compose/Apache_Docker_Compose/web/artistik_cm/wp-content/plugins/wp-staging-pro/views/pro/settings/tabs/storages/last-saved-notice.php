<?php

/**
 * @var object $options
 *
 * @see src/views/settings/tabs/storages/googledrive-settings.php
 * @see src/views/settings/tabs/storages/sftp-settings.php
 * @see src/views/pro/settings/tabs/storages/pcloud-settings.php
 * @see src/views/pro/settings/tabs/storages/base-s3-settings.php
 * @see src/views/pro/settings/tabs/storages/dropbox-settings.php
 * @see src/views/pro/settings/tabs/storages/generic-s3-settings.php
 * @see src/views/pro/settings/tabs/storages/one-drive-settings.php
 */

use WPStaging\Framework\Adapter\DateTimeAdapter;
use WPStaging\Framework\Facades\Sanitize;
use WPStaging\Core\WPStaging;

$lastUpdated = Sanitize::sanitizeInt($options['lastUpdated'] ?? 0);
?>

<?php if ($lastUpdated > 0) : ?>
    <?php
    $date = (new DateTime())->setTimestamp($lastUpdated);
    /** @var DateTimeAdapter $dateTimeAdapter */
    $dateTimeAdapter = WPStaging::make(DateTimeAdapter::class);
    ?>
    <span class="wpstg-badge wpstg-badge-info">
        <?php esc_html_e('Last Saved:', 'wp-staging'); ?>
        <span id="wpstg-provider-updated-at-time">
            <?php echo esc_html($dateTimeAdapter->transformToWpFormat($date)); ?>
        </span>
    </span>
<?php else : ?>
    <span class="wpstg-badge wpstg-badge-warning">
        <?php esc_html_e('Not saved yet!', 'wp-staging'); ?>
    </span>
<?php endif; ?>
