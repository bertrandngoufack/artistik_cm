<?php

use WPStaging\Framework\TemplateEngine\TemplateEngine;

/**
 * @see \WPStaging\Pro\Auth\TemporaryLogins::ajaxListTemporaryLoginsData()
 * @var TemplateEngine              $this
 * @var array                       $temporaryLoginsData
 * @var string                      $urlAssets
 */
?>
<?php if (!empty($temporaryLoginsData)) :?>
<table id="wpstg-temporary-logins-data" class="wpstg-temp-logins-wrapper">
    <thead>
    <tr>
        <th><?php esc_html_e('User', 'wp-staging') ?></th>
        <th><?php esc_html_e('Role', 'wp-staging') ?></th>
        <th><?php esc_html_e('Last Login', 'wp-staging') ?></th>
        <th><?php esc_html_e('Expiry', 'wp-staging') ?></th>
        <th><?php esc_html_e('Login Count', 'wp-staging') ?></th>
        <th><?php esc_html_e('Actions', 'wp-staging') ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($temporaryLoginsData as $loginLink) :?>
        <tr>
            <td> <?php echo esc_html($loginLink['display_name']); ?> <br> <?php echo esc_html($loginLink['user_email']); ?></td>
            <td> <?php echo esc_html($loginLink['roles']); ?> </td>
            <td> <?php echo esc_html($loginLink['lastLogin']); ?> </td>
            <td class="<?php echo ($loginLink['isExpired'] ? 'wpstg--red' : '') ?>">
                <?php echo $loginLink['isExpired'] ? 'Expired' : esc_html($loginLink['expiration']); ?>
            </td>
            <td> <?php echo esc_html($loginLink['loginAttempts']); ?></td>
            <td>
                <input type="hidden" id="wpstg-temporary-login-link-<?php echo esc_attr($loginLink['id']) ?>" value="<?php echo esc_url($loginLink['loginUrl']); ?>" />
                <div class="wpstg-clone-actions">
                    <div class="wpstg-dropdown wpstg-action-dropdown">
                        <a href="#" class="wpstg-dropdown-toggler">
                            <?php esc_html_e("Actions", "wp-staging"); ?>
                            <span class="wpstg-caret"></span>
                        </a>
                        <div class="wpstg-dropdown-menu wpstg-backup-actions-menu">
                            <a href="javascript:void(0);" class="wpstg-clone-action wpstg-share-login-link" onclick="WPStaging.copyTextToClipboard(this)" title="" data-wpstg-source="#wpstg-temporary-login-link-<?php echo esc_attr($loginLink['id']) ?>">
                                <div class="wpstg-dropdown-item-icon">
                                    <?php $this->getAssets()->renderSvg('share'); ?>
                                </div>
                                <?php esc_html_e('Share Login Link', 'wp-staging') ?>
                            </a>
                            <?php if (!empty($loginLink['user_email'])) :?>
                                <a href="javascript:void(0);" class="wpstg-clone-action wpstg-edit-login-link" data-login-id="<?php echo esc_attr($loginLink['id']) ?>">
                                    <div class="wpstg-dropdown-item-icon">
                                        <?php $this->getAssets()->renderSvg('edit'); ?>
                                    </div>
                                    <?php esc_html_e('Edit', 'wp-staging') ?>
                                </a>
                                <a href="javascript:void(0);" class="wpstg-clone-action wpstg-delete-login-link" data-login-id="<?php echo esc_attr($loginLink['id']) ?>">
                                    <div class="wpstg-dropdown-item-icon">
                                        <?php $this->getAssets()->renderSvg('trash'); ?>
                                    </div>
                                    <?php esc_html_e('Delete', 'wp-staging') ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php else :?>
<div class="wpstg-no-temp-logins-found">
    <strong><?php esc_html_e('No Login links found.', 'wp-staging'); ?></strong>
</div>
<?php endif; ?>
