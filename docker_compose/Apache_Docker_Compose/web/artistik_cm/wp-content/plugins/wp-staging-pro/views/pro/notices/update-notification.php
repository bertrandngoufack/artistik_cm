<?php

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Notices\Notices;
use WPStaging\Pro\License\Version;

/**
 * @var Version $outdatedNotice
 */
$outdatedNotice = WPStaging::make(Version::class);
if (isset($_GET['page']) && $_GET['page'] === 'wpstg_clone' || $_GET['page'] === 'wpstg_backup') {
    $display               = 'none;';
    $latestReleasedVersion = 'undefined';

    if (Notices::SHOW_ALL_NOTICES || $outdatedNotice->isOutdatedWpStagingProVersion()) {
        $latestReleasedVersion = $outdatedNotice->getLatestWpstgProVersion();
        $display = 'block;';
    }
    ?>
    <div id="wpstg-update-notify" style="display:<?php echo esc_attr($display); ?>">
        <strong><?php echo sprintf(esc_html__("New: WP Staging Pro v. %s is available.", 'wp-staging'), esc_html($latestReleasedVersion)); ?></strong><br/>
        <?php esc_html_e("Important: We recommend updating before pushing your staging site live to ensure optimal compatibility and performance.", 'wp-staging'); ?>
        <a href="https://wp-staging.com/wp-staging-pro-changelog/" target="_blank"><?php esc_html_e("What's New?", 'wp-staging'); ?></a><br/>
        <?php esc_html_e("If the update isn't available for you yet, it will appear soon, or you can download it anytime from", 'wp-staging'); ?>
        <a href="https://wp-staging.com/your-account/" target="_blank"><?php esc_html_e("Your Account.", 'wp-staging'); ?></a>
    </div>
<?php } ?>
