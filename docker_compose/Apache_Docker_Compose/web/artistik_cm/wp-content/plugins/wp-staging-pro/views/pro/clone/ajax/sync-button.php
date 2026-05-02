<?php

/**
 * @var string $cloneID The ID of the clone.
 * @var array  $data    An array of Clone data.
 * @var $license
 * @var bool $isValidLicense
 * @var WPStaging\Framework\Assets\Assets $assets
 *
 * @see src/views/clone/ajax/single-overview.php:62
 * @see WPStaging\Pro\Template\ProTemplateIncluder::addSyncAccountButton()
 * @see WPStaging\Pro\Template\TemplateServiceProvider()
 */

// @see src/views/clone/ajax/single-overview.php:36

$cloneName = isset($data["cloneName"]) ? $data["cloneName"] : $data["directoryName"];
?>

<?php if ($isValidLicense) : ?>
<a
    href="#"
    class="wpstg-sync-account wpstg-clone-action"
    data-clone="<?php echo esc_attr($cloneID) ?>"
    data-alert-title="<?php esc_attr_e('Confirm User Synchronization!', 'wp-staging') ?>"
    data-alert-body="<?php echo sprintf(esc_html__("This action synchronizes the current user's data with that of the staging site (%s). This will update the current user account password on the staging site or create the same user with identical username and password if it does not already exist. Do you want to continue?", "wp-staging"), esc_html($cloneName)) ?>"
    data-confirm-btn-text="<?php esc_attr_e('Proceed', 'wp-staging') ?>"
    title="<?php echo sprintf(esc_html__("Sync the current user account with this staging site (%s). This will either add the current logged in user account to the staging site or create the same user with identical username and password if it does not exist.", "wp-staging"), esc_html($cloneName)) ?>"
    >
    <div class="wpstg-dropdown-item-icon">
        <?php $assets->renderSvg('sync-user'); ?>
    </div>
    <?php esc_html_e("Sync User Account", "wp-staging"); ?>
</a>
<?php else : ?>
    <a href="javascript:void(0)" class="wpstg-pro-clone-feature wpstg-element-disabled wpstg-clone-action" title="<?php echo esc_html__("Activate the license first!", "wp-staging") ?>">
        <div class="wpstg-dropdown-item-icon">
            <?php $assets->renderSvg('sync-user'); ?>
        </div>
        <?php esc_html_e("Sync User Account", "wp-staging"); ?>
        <span class="wpstg--red-warning">&nbsp;<?php esc_html_e("(Unregistered)", "wp-staging"); ?></span>
    </a>
<?php endif;?>
