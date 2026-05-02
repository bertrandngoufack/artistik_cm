<?php

/**
 * @see \WPStaging\Pro\Staging\Ajax\MagicLoginLink::ajaxLoginLinkUserInterface
 * @var \WPStaging\Framework\TemplateEngine\TemplateEngine $this
 * @var string $cloneId
 * @var string $cloneName
 * @var string $urlAssets
 * @var bool $isNetworkClone
 * @var string $canUseMagicLogin
 */

if (!defined("WPINC")) {
    die();
}

use WPStaging\Framework\Security\Capabilities;
use WPStaging\Pro\Staging\AutoLogin\LoginAuthenticator;
use WPStaging\Framework\Facades\UI\Alert;

?>
<input type="hidden" id="wpstg-generate-login-link-clone-id" name="wpstg-generate-login-link-clone-id" value="<?php echo esc_attr($cloneId); ?>">
<div class="wpstg-form-horizontal">
    <div class="wpstg-form-row">
        <h3><?php esc_html_e('Generate Login Link', 'wp-staging'); ?></h3>
    </div>
    <div>
        <p>
            <?php
            echo sprintf(esc_html__("This will generate a login link for the staging site \"%s\". You can use this link to login to the staging site without having to enter your username and password. This can be useful to share quick login links with your clients or team members.", "wp-staging"), esc_html($cloneName));
            ?>
        </p>
    </div>
    <?php if (!$canUseMagicLogin) {
        $title       = esc_html__("Update Required", "wp-staging");
        $description = sprintf(esc_html__("It seems like magic login cannot be used on the staging site(%s). Please update WP STAGING on the staging site before creating a magic login link!", "wp-staging"), esc_html($cloneName));
        Alert::render($title, $description);
    }?>
    <div class="wpstg-form-row" style="display: flex; align-items: center; gap: 8px;">
        <label id="wpstg-generate-login-link-user-role-label" for="wpstg-generate-login-link-role" style="width: 170px; min-width: 170px; max-width: 170px; flex-shrink: 0;">
            <?php esc_html_e("Login Permissions:", "wp-staging"); ?>
        </label>
        <select name="wpstg-generate-login-link-role" id="wpstg-generate-login-link-role" style="max-width: 200px;">
            <?php
            $roleList                                   = wp_roles()->get_names();
            $roleList[Capabilities::WPSTG_VISITOR_ROLE] = esc_html__('Visitor', 'wp-staging');
            $roleList                                   = array_reverse($roleList);
            if ($isNetworkClone) {
                $roleList[LoginAuthenticator::WPSTG_SUPER_ADMIN_ROLE] = esc_html__('Super Admin', 'wp-staging');
            }
            ?>
            <?php foreach ($roleList as $roleKey => $roleName) : ?>
                <option value="<?php echo esc_attr($roleKey) ?>" <?php echo ($roleKey === Capabilities::WPSTG_VISITOR_ROLE) ? 'selected="selected"' : '' ?>>
                    <?php echo esc_html(translate_user_role($roleName)); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="wpstg--tooltip">
            <img class="wpstg--dashicons" src="<?php echo esc_url($urlAssets); ?>svg/info-outline.svg" alt="Info"/>
            <span class="wpstg--tooltiptext">
                <?php esc_html_e('These are the permissions a user will get when using the login link created by this function.', 'wp-staging'); ?>
                <br/><br/>
                <?php echo sprintf(
                    esc_html__("If you want to give a developer access to the backend, make sure you select the %s role or something with similar privileges.", "wp-staging"),
                    '<b>' . esc_html__('administrator', 'wp-staging') . '</b>'
                );?>
            </span>
        </div>
    </div>
    <br />
    <div style="display: flex; align-items: center; gap: 8px;">
        <label id="wpstg-generate-login-link-minutes-label" for="wpstg-generate-login-link-minutes" style="width: 170px; min-width: 170px; max-width: 170px; flex-shrink: 0;">
            <?php esc_html_e("Login will expire in", "wp-staging"); ?>
        </label>
        <?php
        $days = [];
        for ($i = 0; $i <= 10; $i++) {
            $days[] = "{$i}";
        }
        ?>
        <select name="wpstg-generate-login-link-days" id="wpstg-generate-login-link-days">
            <?php foreach ($days as $day) : ?>
                <option value="<?php echo esc_attr($day) ?>" <?php echo ($day === '1') ? 'selected="selected"' : '' ?>>
                    <?php echo esc_html($day); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php esc_html_e("days", "wp-staging"); ?>
        <?php
        $hours = [];
        for ($i = 0; $i <= 23; $i++) {
            $hours[] = "{$i}";
        }
        ?>
        <select name="wpstg-generate-login-link-hours" id="wpstg-generate-login-link-hours">
            <?php foreach ($hours as $hour) : ?>
                <option value="<?php echo esc_attr($hour) ?>">
                    <?php echo esc_html($hour); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php esc_html_e("hours", "wp-staging"); ?>
        <?php
        $minutes = [];
        for ($i = 0; $i <= 55; $i += 5) {
            $minutes[] = "{$i}";
        }
        ?>
        <select name="wpstg-generate-login-link-minutes" id="wpstg-generate-login-link-minutes">
            <?php foreach ($minutes as $minute) : ?>
                <option value="<?php echo esc_attr($minute) ?>">
                    <?php echo esc_html($minute); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php esc_html_e("mins", "wp-staging"); ?>
    </div>
    <br />
    <hr>
    <br />
    <div id="wpstg-generate-login-link-container">
        <h3 id="wpstg-generate-login-link-head"><?php echo esc_html__("Click on the link to copy it! (Will be displayed only once)", "wp-staging") ?></h3>
        <div id="wpstg-generate-login-link-generated-container">
            <span id="wpstg-generate-login-link-generated"></span>
            <span id="wpstg-generate-login-link-copy-text" data-copy="<?php echo esc_attr__('Copy!', 'wp-staging'); ?>" data-copied="<?php echo esc_attr__('Copied!', 'wp-staging'); ?>"></span>
        </div>
    </div>
</div>

<p>
<button type="button" class="wpstg-prev-step-link wpstg-button--primary wpstg-button-back-arrow">
    <i class="wpstg-back-arrow"></i>
    <?php esc_html_e("Back", "wp-staging") ?>
</button>
<button
    type="button"
    id="wpstg-generate-login-link"
    data-alert-title="<?php esc_attr_e('Do you want to create a new login link?', 'wp-staging') ?>"
    data-alert-body="<?php echo esc_html__("This action will remove and invalidate all prior login links and create a new one. Do you want to proceed?", "wp-staging") ?>"
    data-confirm-btn-text="<?php esc_attr_e('Proceed', 'wp-staging') ?>"
    class="wpstg-button--blue"
    >
    <?php esc_html_e('Create Login Link', 'wp-staging'); ?>
</button>
</p>
