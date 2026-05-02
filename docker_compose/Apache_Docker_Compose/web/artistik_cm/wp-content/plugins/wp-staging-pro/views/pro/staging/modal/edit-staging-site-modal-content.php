<?php

/**
 * @var string $cloneId
 * @var \WPStaging\Staging\Dto\StagingSiteDto $stagingSite
 */

use WPStaging\Framework\Facades\UI\Checkbox;
use WPStaging\Staging\Dto\StagingSiteDto;

$assetsUrl = trailingslashit(WPSTG_PLUGIN_URL);
$allowedStatuses = [
    StagingSiteDto::STATUS_FINISHED,
    StagingSiteDto::STATUS_UNFINISHED_BROKEN,
];
?>
<form id="editStagingSiteForm" class="wpstg-edit-staging-site-form" name="editStagingSiteForm">
    <input type="hidden" id="wpstg-edit-clone-data-clone-id" name="cloneId" value="<?php echo esc_attr($cloneId); ?>">

    <div class="wpstg-edit-grid">
        <div class="wpstg-form-row">
            <label id="wpstg-edit-clone-data-clone-name-label" for="wpstg-edit-clone-data-clone-name">
                <?php esc_html_e("Site Name", "wp-staging"); ?>
            </label>
            <input type="text" class="wpstg-input wpstg-input-md" id="wpstg-edit-clone-data-clone-name" name="cloneName" value="<?php
            echo esc_attr($stagingSite->getCloneName()) ?>">
        </div>
        <div class="wpstg-form-row">
            <label id="wpstg-edit-clone-data-directory-name-label" for="wpstg-edit-clone-data-directory-name">
                <?php esc_html_e("Subdirectory Name", "wp-staging"); ?>
            </label>
            <input type="text" class="wpstg-input wpstg-input-md" id="wpstg-edit-clone-data-directory-name" name="directoryName" value="<?php
            echo esc_attr($stagingSite->getDirectoryName()) ?>">
        </div>
        <div class="wpstg-form-row">
            <label id="wpstg-edit-clone-data-path-label" for="wpstg-edit-clone-data-path">
                <?php esc_html_e("Target Directory", "wp-staging"); ?>
            </label>
            <input type="text" class="wpstg-input wpstg-input-md" id="wpstg-edit-clone-data-path" name="path" value="<?php
            echo esc_attr($stagingSite->getPath()) ?>">
        </div>
        <div class="wpstg-form-row">
            <label id="wpstg-edit-clone-data-url-label" for="wpstg-edit-clone-data-url">
                <?php esc_html_e("Target Hostname", "wp-staging"); ?>
            </label>
            <input type="text" class="wpstg-input wpstg-input-md" id="wpstg-edit-clone-data-url" name="url" value="<?php
            echo esc_attr($stagingSite->getUrl()) ?>">
        </div>
        <div class="wpstg-form-row">
            <label id="wpstg-edit-clone-data-prefix-label" for="wpstg-edit-clone-data-prefix">
                <?php esc_html_e("Database Table Prefix", "wp-staging"); ?>
                <span class='wpstg--tooltip wpstg--tooltip-normal'>
                    <img class='wpstg--dashicons wpstg--grey wpstg-symlink-dir-tooltip' src='<?php echo esc_url($assetsUrl); ?>assets/svg/info-outline.svg' alt='info'/>
                    <span class='wpstg--tooltiptext'>
                        <?php esc_html_e('This prefix is used if the staging site uses the same database as the production website.', 'wp-staging'); ?>
                    </span>
                </span>
            </label>
            <input type="text" class="wpstg-input wpstg-input-md" id="wpstg-edit-clone-data-prefix" name="prefix" value="<?php
            echo esc_attr($stagingSite->getPrefix()) ?>">
        </div>
        <div class="wpstg-form-row">
            <label id="wpstg-edit-clone-data-site-status-label" for="wpstg-edit-clone-data-site-status">
                <?php esc_html_e("Status", "wp-staging"); ?>
                <span class='wpstg--tooltip wpstg--tooltip-normal'>
                    <img class='wpstg--dashicons wpstg--grey wpstg-symlink-dir-tooltip' src='<?php echo esc_url($assetsUrl); ?>assets/svg/info-outline.svg' alt='info'/>
                    <span class='wpstg--tooltiptext'>
                        <?php
                        echo sprintf(
                            esc_html__('If the staging site is working correctly and is not broken, you can change the status to %s to remove the unfinished or broken warning.', 'wp-staging'),
                            '<code>finished</code>'
                        ); ?>
                    </span>
                </span>
            </label>
            <select class="wpstg-input wpstg-input-md" id="wpstg-edit-clone-data-site-status" name="status">
                <?php foreach ($allowedStatuses as $status) : ?>
                    <option value="<?php echo esc_attr($status); ?>" <?php echo selected($stagingSite->getStatus(), $status, false); ?>>
                        <?php echo esc_html($status); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="wpstg-card wpstg-mt-3" style="border-radius: 8px; border: 1px solid hsl(var(--wpstg-border, 220 13% 85%)); overflow: hidden;">
        <div class="wpstg-edit-db-header" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; cursor: pointer; user-select: none;" onclick="this.nextElementSibling.classList.toggle('wpstg-hidden'); this.querySelector('.wpstg-chevron').classList.toggle('wpstg-rotate-180');">
            <div>
                <strong style="font-size: 14px;"><?php esc_html_e('External Database', 'wp-staging'); ?></strong>
                <span style="display: block; font-size: 12px; color: hsl(var(--wpstg-text-muted, 220 9% 46%)); margin-top: 2px;"><?php esc_html_e("Only needed when the staging site uses a separate database.", "wp-staging"); ?></span>
            </div>
            <svg class="wpstg-chevron" style="width: 16px; height: 16px; flex-shrink: 0; transition: transform 0.2s ease; color: hsl(var(--wpstg-text-muted, 220 9% 46%));" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
        </div>
        <div class="wpstg-hidden" style="padding: 0 16px 16px 16px;">
            <div class="wpstg--staging-site--edit--external-connection">
                <div class="wpstg-edit-grid">
                    <div class="wpstg-form-row">
                        <label id="wpstg-edit-clone-data-database-user-label" for="wpstg-edit-clone-data-database-user">
                            <?php esc_html_e("Database User", "wp-staging"); ?>
                        </label>
                        <input type="text" class="wpstg-input wpstg-input-md" id="wpstg-edit-clone-data-database-user" name="databaseUser" value="<?php
                        echo esc_attr($stagingSite->getDatabaseUser()) ?>">
                    </div>
                    <div class="wpstg-form-row">
                        <label id="wpstg-edit-clone-data-database-password-label" for="wpstg-edit-clone-data-database-password">
                            <?php esc_html_e("Database Password", "wp-staging"); ?>
                        </label>
                        <input type="password" class="wpstg-input wpstg-input-md" id="wpstg-edit-clone-data-database-password" name="databasePassword" value="<?php
                        echo esc_attr($stagingSite->getDatabasePassword()) ?>">
                    </div>
                    <div class="wpstg-form-row">
                        <label id="wpstg-edit-clone-data-database-database-label" for="wpstg-edit-clone-data-database-database">
                            <?php esc_html_e("Database Name", "wp-staging"); ?>
                        </label>
                        <input type="text" class="wpstg-input wpstg-input-md" id="wpstg-edit-clone-data-database-database" name="databaseDatabase" value="<?php
                        echo esc_attr($stagingSite->getDatabaseDatabase()) ?>">
                    </div>
                    <div class="wpstg-form-row">
                        <label id="wpstg-edit-clone-data-database-server-label" for="wpstg-edit-clone-data-database-server">
                            <?php esc_html_e("Database Hostname", "wp-staging"); ?>
                        </label>
                        <input type="text" class="wpstg-input wpstg-input-md" id="wpstg-edit-clone-data-database-server" name="databaseServer" value="<?php
                        echo esc_attr($stagingSite->getDatabaseServer()) ?>">
                    </div>
                    <div class="wpstg-form-row">
                        <label id="wpstg-edit-clone-data-database-prefix-label" for="wpstg-edit-clone-data-database-prefix">
                            <?php esc_html_e("Database Table Prefix", "wp-staging"); ?>
                        </label>
                        <input type="text" class="wpstg-input wpstg-input-md" id="wpstg-edit-clone-data-database-prefix" name="databasePrefix" value="<?php
                        echo esc_attr($stagingSite->getDatabasePrefix()) ?>">
                    </div>
                    <div class="wpstg-form-row" style="align-self: end;">
                        <label class="wpstg-checkbox-wrapper" style="height: 36px;">
                            <input type="checkbox" class="wpstg-checkbox" name="databaseSsl" id="wpstg-edit-clone-data-database-ssl" value="true" <?php checked($stagingSite->getDatabaseSsl()); ?> />
                            <span class="wpstg-label"><?php esc_html_e("Database Use SSL", "wp-staging"); ?></span>
                            <span class='wpstg--tooltip wpstg--tooltip-normal' style="margin-top: 5px;">
                                <img class='wpstg--dashicons wpstg--grey' src='<?php echo esc_url($assetsUrl); ?>assets/svg/info-outline.svg' alt='info'/>
                                <span class='wpstg--tooltiptext' style="z-index: 999999; min-width: 280px; bottom: 100%; top: auto; left: auto; right: 0; margin-bottom: 8px;">
                                    <?php
                                    echo sprintf(
                                        esc_html__('If your database server does not support SSL or you disabled databaseSsl and see "Error establishing a database connection", remove this %s from wp-config.php', 'wp-staging'),
                                        '<code>MYSQL_CLIENT_FLAGS</code>'
                                    ); ?>
                                </span>
                            </span>
                        </label>
                    </div>
                </div>
                <div class="wpstg-form-group wpstg-text-field wpstg-mt-20px">
                    <span id="wpstg-db-connection-running"><?php esc_html_e("Testing db connection...", "wp-staging"); ?></span>
                    <a href="javascript:void(0)" id="wpstg-test-db-connection"><?php esc_html_e("Test Database Connection", "wp-staging"); ?></a>
                </div>
            </div>
        </div>
    </div>
</form>
