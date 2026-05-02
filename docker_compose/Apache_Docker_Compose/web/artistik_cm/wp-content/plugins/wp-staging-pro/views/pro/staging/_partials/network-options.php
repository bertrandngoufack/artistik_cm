<?php

use WPStaging\Pro\Staging\Service\StagingSetup;

/**
 * Used in only multisite and PRO version
 * @var StagingSetup $stagingSetup
 * @var string       $description
 * @see \WPStaging\Pro\Staging\Service\StagingSetup::renderNetworkCloneSettings
 */

?>

<a href="#" class="wpstg-tab-header active expand" data-id="#wpstg-network-options">
    <span class="wpstg-tab-triangle wpstg-u-rotate-90"></span>
    <?php echo esc_html__("Network Options", "wp-staging") ?>
</a>

<fieldset class="wpstg-tab-section" id="wpstg-network-options" style="display: block;">
    <?php
        $stagingSetup->renderSettings(
            'wpstg_network_clone',
            esc_html__('Clone Entire Network', 'wp-staging'),
            $description,
            $stagingSetup->getStagingSiteDto()->getNetworkClone(),
            $stagingSetup->isUpdateOrResetJob()
        );
        ?>
</fieldset>
