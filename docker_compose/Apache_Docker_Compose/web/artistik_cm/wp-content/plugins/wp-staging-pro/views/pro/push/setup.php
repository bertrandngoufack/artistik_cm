<?php

use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Facades\UI\Checkbox;
use WPStaging\Staging\Dto\StagingSiteDto;
use WPStaging\Staging\Service\DirectoryScanner;
use WPStaging\Staging\Service\AbstractStagingSetup;
use WPStaging\Staging\Service\TableScanner;

/**
 * @var AbstractStagingSetup $stagingSetup
 * @var StagingSiteDto       $stagingSiteDto
 * @var DirectoryScanner     $directoryScanner
 * @var Directory            $directoryAdapter
 * @var TableScanner         $tableScanner
 */

include WPSTG_VIEWS_DIR . 'job/modal/success.php';
include WPSTG_VIEWS_DIR . 'job/modal/process.php';

?>

<div class="wpstg-tabs-wrapper">
    <a href="#" class="wpstg-tab-header active" data-id="#wpstg-scanning-db" style="display:block;">
        <span class="wpstg-tab-triangle"></span>
        <?php echo esc_html__("Database Tables", "wp-staging") ?>
        <span id="wpstg-tables-count" class="wpstg-selection-preview"></span>
    </a>

    <div class="wpstg-tab-section" id="wpstg-scanning-db">
        <?php do_action("wpstg_scanning_db"); ?>
        <h4 style="margin:0">
            <?php echo esc_html__("Select tables to push to production website", "wp-staging"); ?>
        </h4>
        <p>
            <strong class='wpstg--red'><?php echo esc_html__('Note:', 'wp-staging'); ?></strong>
            <?php echo esc_html__('This database table selection will be automatically saved', 'wp-staging'); ?>
            <br/>
            <?php echo esc_html__('and preselected the next time you push this staging site.', 'wp-staging'); ?>
        </p>
        <p>
            <strong> <?php esc_html_e("Database Name: ", "wp-staging"); ?> </strong> <?php echo esc_html($stagingSiteDto->getDatabaseName()) ?>
            <br />
            <strong> <?php esc_html_e("Table Prefix: ", "wp-staging"); ?> </strong> <?php echo esc_html($stagingSiteDto->getPrefix()); ?>
        </p>
        <div class="wpstg-my-10px">
            <a href="javascript:void(0);" class="wpstg-button-unselect button"> <?php esc_html_e('Unselect All', 'wp-staging'); ?> </a>
            <a href="javascript:void(0);" class="wpstg-button-db-prefix button"> <?php echo esc_html($stagingSiteDto->getPrefix()); ?> </a>
            <span class="wpstg--tooltip wpstg--tooltip-normal">
                <a href="javascript:void(0);" class="wpstg-button-show-tables button"> <?php esc_html_e('Show All Tables', 'wp-staging'); ?> </a>
                <span class="wpstg--tooltiptext">
                    <?php esc_html_e('Show all custom and extra tables existing in the staging sites database, even those belonging to other sites.', 'wp-staging'); ?>
                </span>
            </span>
        </div>
        <select id="wpstg_select_tables_pushing"
            data-clone="<?php echo esc_attr($stagingSiteDto->getCloneId()); ?>"
            data-prefix="<?php echo esc_attr($stagingSiteDto->getPrefix()); ?>"
            data-network="<?php echo $stagingSiteDto->getNetworkClone() ? 'true' : 'false'; ?>"
            multiple="multiple">
            <?php $tableScanner->renderTablesSelection() ?>
        </select>
    </div>

    <a href="#" class="wpstg-tab-header" data-id="#wpstg-scanning-files">
        <span class="wpstg-tab-triangle"></span>
        <?php echo esc_html__("Select Files", "wp-staging"); ?>
        <span id="wpstg-files-count" class="wpstg-selection-preview"></span>
    </a>

    <div class="wpstg-tab-section" id="wpstg-scanning-files">
        <h4>
            <?php echo esc_html__("Select plugins, themes & uploads folder to push to production website.", "wp-staging"); ?>
        </h4>
        <p>
            <strong class='wpstg--red'><?php echo esc_html__('Note:', 'wp-staging'); ?></strong>
            <?php echo esc_html__('This folder selection will be automatically saved', 'wp-staging'); ?>
            <br/>
            <?php echo esc_html__('and preselected the next time you push this staging site.', 'wp-staging'); ?>
        </p>
        <?php $directoryScanner->renderFilesSelection() ?>

        <h4 style="margin:10px 0 10px 0">
            <?php echo esc_html__("Extra Directories to Copy", "wp-staging") ?>
        </h4>

        <textarea id="wpstg_extraDirectories" name="wpstg_extraDirectories" style="width:100%;height:100px;"></textarea>
        <p>
            <span>
                <?php
                echo sprintf(esc_html__(
                    "Enter one directory path per line. %s" .
                    "Directory must start with absolute path: %s",
                    "wp-staging"
                ), '<br />', esc_html($stagingSiteDto->getPath()));
                ?>
            </span>
        </p>

        <p>
            <span>
                <?php
                echo esc_html__("Plugins will be pushed to: ", "wp-staging") . esc_html($directoryAdapter->getPluginsDirectory());
                echo '<br>';
                echo esc_html__("Themes will be pushed to: ", "wp-staging") . esc_html($directoryAdapter->getActiveThemeParentDirectory());

                if (!$stagingSiteDto->getIsUploadsSymlink()) {
                    echo '<br>';
                    echo esc_html__("Medias will be pushed to: ", "wp-staging") . esc_html($directoryAdapter->getUploadsDirectory());
                }
                ?>
            </span>
        </p>
    </div>
    <p>
        <label>
            <?php Checkbox::render('wpstg-remove-uninstalled-plugins-themes', 'wpstg-remove-uninstalled-plugins-themes'); ?>
            <?php echo esc_html__("Uninstall all plugins/themes on production site that are not installed on staging site.", "wp-staging"); ?>
        </label>
    </p>
    <p>
        <label> <?php echo ($stagingSiteDto->getIsUploadsSymlink() ? "<b>" . esc_html__("Note: This option is disabled as uploads dir was symlinked", "wp-staging") . "</b><br/>" : ''); ?>
            <?php Checkbox::render('wpstg-delete-upload-before-pushing', 'wpstg-delete-upload-before-pushing', '', false, ['isDisabled' => $stagingSiteDto->getIsUploadsSymlink()]);?>
            <span class="<?php echo $stagingSiteDto->getIsUploadsSymlink() ? 'wpstg-storage-settings-disabled' : ''; ?>">
                <?php echo esc_html__("Delete wp-content/uploads folder on production site including all images before starting push process.", "wp-staging"); ?>
            </span>
        </label>
    </p>
    <p id="wpstg-backup-upload-container" style="display: none;">
        <label>
            <?php Checkbox::render('wpstg-backup-upload-before-pushing', 'wpstg-backup-upload-before-pushing', '', false, ['isDisabled' => $stagingSiteDto->getIsUploadsSymlink()]);?>
            <?php echo esc_html__("Create a backup of folder wp-content/uploads before deleting it."
                . " Helpful in case the push process fails."
                . " Make sure you have enough space for the uploads folder backup on your hosting otherwise the process will fail."
                . " Backup will be written to wp-content/uploads.wpstg_backup."
                . " If there is already a backup at wp-content/uploads.wpstg_backup then it will be replaced with the new one."
                . " This backup will automatically be deleted after 7 days.", "wp-staging"); ?>
        </label>
    </p>
    <p>
        <label>
            <?php if (is_multisite() && !is_main_site()) : ?>
                <?php Checkbox::render('wpstg-create-backup-before-pushing', 'wpstg-create-backup-before-pushing', '', false, ['isDisabled' => true]); ?>
                <?php echo esc_html__("Create database backup (Not supported in subsites for now, coming soon!)", "wp-staging"); ?>
            <?php else : ?>
                <?php Checkbox::render('wpstg-create-backup-before-pushing', 'wpstg-create-backup-before-pushing', '', true); ?>
                <?php echo esc_html__("Create database backup", "wp-staging"); ?>
            <?php endif; ?>
        </label>
    </p>
</div>

<div id="wpstg-error-wrapper">
    <div id="wpstg-error-details"></div>
</div>

<div class="wpstg-push-site-actions">
    <button type="button" class="wpstg-prev-step-link wpstg-button--primary wpstg-button-back-arrow">
        <i class="wpstg-back-arrow"></i>
        <?php esc_html_e("Back", "wp-staging"); ?>
    </button>

    <button type="button" class="wpstg-button--primary wpstg-button--blue wpstg--push--staging-site">
        <?php echo esc_html__('Confirm Push', 'wp-staging'); ?>
    </button>
    <p id="wpstg-push-changes-details" class="wpstg-push-site-text-color wpstg-ml-4px">
        <span class="wpstg-ml-4px"><?php echo esc_html__('and transfer ', "wp-staging"); ?></span>
        <br>
        <span class="wpstg-push-site-hostname"><?php echo esc_url($stagingSiteDto->getUrl()); ?></span>
        <?php echo esc_html__(' to ', "wp-staging"); ?>
        <span class="wpstg-push-site-hostname"><?php echo esc_url(get_home_url()); ?></span>
    </p>
</div>
