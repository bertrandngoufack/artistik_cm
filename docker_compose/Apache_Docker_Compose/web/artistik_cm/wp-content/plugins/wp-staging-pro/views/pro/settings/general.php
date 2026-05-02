<?php

use WPStaging\Framework\Facades\Escape;
use WPStaging\Pro\WPStagingPro;

/**
 * @var \WPStaging\Core\Forms\Form $form
 */

?>
<?php if (WPStagingPro::isValidLicense()) : ?>
    <div class="wpstg-settings-field wpstg-settings-has-toggle">
        <div>
            <div class="wpstg-settings-field-header">
                <label class="wpstg-settings-field-label">
                    <?php $form->renderLabel("wpstg_settings[keepPermalinks]"); ?>
                </label>
            </div>
            <div class="wpstg-settings-field-description">
                <?php
                echo wp_kses_post(sprintf(
                    __(
                        'Use on the staging site the same permalink structure and do not set permalinks to plain structure. <br/>Read more: <a href="%1$s" target="_blank">Permalink Settings</a> ',
                        'wp-staging'
                    ),
                    'https://wp-staging.com/docs/activate-permalinks-staging-site/'
                )); ?>
            </div>
        </div>
        <div class="wpstg-settings-field-input">
            <?php $form->renderInput("wpstg_settings[keepPermalinks]"); ?>
        </div>
    </div>

    <div class="wpstg-settings-field">
        <div>
            <div class="wpstg-settings-field-header">
                <label class="wpstg-settings-field-label">
                    <?php $form->renderLabel("wpstg_settings[userRoles][]"); ?>
                </label>
            </div>
            <div class="wpstg-settings-field-description">
                <?php
                echo Escape::escapeHtml(__(
                    'Select the user role you want to give access to the staging site. You can select multiple roles by holding CTRL or ⌘ Cmd key while clicking. <br/><strong>Change this option on the staging site if you want to change the authentication behavior there.</strong>',
                    'wp-staging'
                )); ?>
            </div>
        </div>
        <div class="wpstg-settings-field-input wpstg-select wpstg-multi-select">
            <?php $form->renderInput("wpstg_settings[userRoles][]"); ?>
        </div>
    </div>
    <div class="wpstg-settings-field">
        <div>
            <div class="wpstg-settings-field-header">
                <label class="wpstg-settings-field-label">
                    <?php $form->renderLabel("wpstg_settings[usersWithStagingAccess]"); ?>
                </label>
            </div>
            <div class="wpstg-settings-field-description">
                <?php
                echo Escape::escapeHtml(__(
                    'Specify users who will have access to the staging site regardless of their role. You can enter multiple user names separated by a comma. <br/><strong>Change this option on the staging site if you want to change the authentication behavior there.</strong>',
                    'wp-staging'
                )); ?>
            </div>
        </div>
        <div class="wpstg-settings-field-input">
            <?php $form->renderInput("wpstg_settings[usersWithStagingAccess]"); ?>
        </div>
    </div>
    <div class="wpstg-settings-field wpstg-settings-has-toggle">
        <div>
            <div class="wpstg-settings-field-header">
                <label class="wpstg-settings-field-label">
                    <?php $form->renderLabel("wpstg_settings[adminBarColor]"); ?>
                </label>
            </div>
            <div class="wpstg-settings-field-description">
            </div>
        </div>
        <div class="wpstg-settings-field-input">
            <?php $form->renderInput("wpstg_settings[adminBarColor]"); ?>
        </div>
    </div>
<?php endif;?>
