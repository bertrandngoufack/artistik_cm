<?php

/**
 * @var string                            $cloneID The ID of the clone.
 * @var array                             $data    An array of Clone data.
 * @var mixed                             $license
 * @var bool                              $isValidLicense
 * @var bool                              $newStagingFeatureEnabled
 * @var WPStaging\Framework\Assets\Assets $assets
 *
 * @see src/views/clone/ajax/single-overview.php:62
 */

?>
<?php
/*
 * Hey there! This is friendly reminder that overriding, bypassing, or modifying
 * the license check code is a copyright infringement liable to legal actions.
 *
 * If you need help with your license, please feel free to contact us to normalize it:
 *
 * @link https://wp-staging.com/support/ The link to renew your license.
 *
 * @link https://www.copyright.gov/title17/92chap5.html The link to U.S copyright law information.
 * @link https://europa.eu/youreurope/business/running-business/intellectual-property/copyright/index_en.htm The link to EU copyright law information.
 */

$cloneName = isset($data["cloneName"]) ? $data["cloneName"] : $data["directoryName"];
if ($isValidLicense && $newStagingFeatureEnabled) : ?>
<a href="#" class="wpstg--push--staging-site--setup wpstg-clone-action"
    data-cloneId="<?php echo esc_attr($cloneID); ?>"
    data-cloneName="<?php echo esc_attr($cloneName); ?>"
    title="<?php echo esc_html__("Push and overwrite current production website with the selected staging site. Select specific folders and database tables in the next step.", "wp-staging"); ?>">
    <div class="wpstg-dropdown-item-icon">
        <?php $assets->renderSvg('push'); ?>
    </div>
    <?php esc_html_e("Push Changes", "wp-staging") ?>
</a>
<?php elseif ($isValidLicense && !$newStagingFeatureEnabled) : ?>
<a href="#" class="wpstg-push-changes wpstg-merge-clone wpstg-clone-action"
    data-clone="<?php echo esc_attr($cloneID); ?>"
    title="<?php echo esc_html__("Push and overwrite current production website with the selected staging site. Select specific folders and database tables in the next step.", "wp-staging"); ?>">
    <div class="wpstg-dropdown-item-icon">
        <?php $assets->renderSvg('push'); ?>
    </div>
    <?php esc_html_e("Push Changes", "wp-staging") ?>
</a>
<?php else : ?>
<a href="javascript:void(0)" class="wpstg-pro-clone-feature wpstg-element-disabled wpstg-merge-clone wpstg-clone-action"
    data-clone="<?php echo esc_attr($cloneID); ?>"
    title="<?php echo esc_html__("Activate the license first!", "wp-staging"); ?>">
    <div class="wpstg-dropdown-item-icon">
        <?php $assets->renderSvg('push'); ?>
    </div>
    <?php esc_html_e("Push Changes", "wp-staging") ?>
    <span class="wpstg--red-warning">&nbsp;<?php esc_html_e("(Unregistered)", "wp-staging"); ?></span>
</a>
<?php endif; ?>
