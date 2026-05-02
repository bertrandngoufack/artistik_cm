<?php

use WPStaging\Framework\TemplateEngine\TemplateEngine;

/**
 * @see WPStaging\Pro\Auth\TemporaryLogins::ajaxLoadTemporaryLoginInterface
 * @var TemplateEngine $this
 * @var array $roleList
 * @var array $days
 * @var array $hours
 * @var array $minutes
 * @var array $loginData
 * @var array $expiry
 * @var bool $isUpdate
 */

$disabled    = $isUpdate ? 'disabled' : '';
$loginID     = empty($loginData['loginID']) ? '' : $loginData['loginID'];
$selectedRole = empty($loginData['role']) ? key($roleList) : $loginData['role'];
$selectedDays = isset($expiry['days']) ? $expiry['days'] : '1';
$selectedHours = isset($expiry['hours']) ? $expiry['hours'] : '0';
$selectedMinutes = isset($expiry['minutes']) ? $expiry['minutes'] : '0';

$chevronSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>';
$checkSvg = '<svg class="wpstg-dropdown-check" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
$svgAllowed = (new \WPStaging\Framework\Utils\Escape())->htmlAllowedDuringEscape([]);
?>

<div class="temporary-logins-container">
    <h2>
        <?php
        if ($isUpdate) {
            esc_html_e('Update Temporary Login', 'wp-staging');
        } else {
            esc_html_e('Create Temporary Login', 'wp-staging');
        }
        ?>
    </h2>
    <div class="wpstg-temporary-login-form-group">
        <label for="wpstg-temporary-login-email" class="wpstg-temporary-login-label"><?php esc_html_e('Email', 'wp-staging') ?></label>
        <input id="wpstg-temporary-login-email" class="wpstg-input wpstg-input-md wpstg-temporary-login-input-field" type="email" name="wpstg-temporary-login-email" value="<?php echo empty($loginData['email']) ? '' : esc_attr($loginData['email']) ?>" <?php echo esc_attr($disabled) ?> />
        <input id="wpstg-temporary-login-id" type="hidden" value="<?php echo esc_attr($loginID) ?>" />
    </div>
    <div class="wpstg-temporary-login-form-group">
        <label class="wpstg-temporary-login-label"><?php esc_html_e('Role', 'wp-staging') ?></label>
        <div class="wpstg-dropdown" data-dropdown-select>
            <input type="hidden" name="wpstg-temporary-login-role" id="wpstg-temporary-login-role" value="<?php echo esc_attr($selectedRole); ?>" />
            <button type="button" class="wpstg-dropdown-trigger" data-dropdown-trigger>
                <span data-dropdown-value><?php
                if (!empty($selectedRole) && isset($roleList[$selectedRole])) {
                    echo esc_html(translate_user_role($roleList[$selectedRole]));
                } else {
                    $firstRole = reset($roleList);
                    echo esc_html(translate_user_role($firstRole));
                }
                ?></span>
                <?php echo wp_kses($chevronSvg, $svgAllowed); ?>
            </button>
            <ul class="wpstg-dropdown-menu" data-dropdown-menu>
                <?php foreach ($roleList as $roleKey => $roleName) :
                    $isSelected = ($roleKey === $selectedRole);
                    ?>
                    <li class="wpstg-dropdown-option" data-value="<?php echo esc_attr($roleKey); ?>" <?php echo $isSelected ? 'data-selected' : ''; ?>>
                        <?php echo wp_kses($isSelected ? $checkSvg : '<span class="wpstg-dropdown-spacer"></span>', $svgAllowed); ?>
                        <?php echo esc_html(translate_user_role($roleName)); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <label class="wpstg-temporary-login-label"><?php esc_html_e('Expiry', 'wp-staging') ?></label>
    <div class="wpstg-temporary-login-expiry-group">
        <div class="wpstg-dropdown" data-dropdown-select>
            <input type="hidden" name="wpstg-temporary-login-days" id="wpstg-temporary-login-days" value="<?php echo esc_attr($selectedDays); ?>" />
            <button type="button" class="wpstg-dropdown-trigger" data-dropdown-trigger>
                <span data-dropdown-value><?php echo esc_html($selectedDays) . ' days'; ?></span>
                <?php echo wp_kses($chevronSvg, $svgAllowed); ?>
            </button>
            <ul class="wpstg-dropdown-menu" data-dropdown-menu>
                <?php foreach ($days as $day) :
                    $isSelected = ($day == $selectedDays);
                    ?>
                    <li class="wpstg-dropdown-option" data-value="<?php echo esc_attr($day); ?>" <?php echo $isSelected ? 'data-selected' : ''; ?>>
                        <?php echo wp_kses($isSelected ? $checkSvg : '<span class="wpstg-dropdown-spacer"></span>', $svgAllowed); ?>
                        <?php echo esc_html($day) . ' days'; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="wpstg-dropdown" data-dropdown-select>
            <input type="hidden" name="wpstg-temporary-login-hours" id="wpstg-temporary-login-hours" value="<?php echo esc_attr($selectedHours); ?>" />
            <button type="button" class="wpstg-dropdown-trigger" data-dropdown-trigger>
                <span data-dropdown-value><?php echo esc_html($selectedHours) . ' hours'; ?></span>
                <?php echo wp_kses($chevronSvg, $svgAllowed); ?>
            </button>
            <ul class="wpstg-dropdown-menu" data-dropdown-menu>
                <?php foreach ($hours as $hour) :
                    $isSelected = ($hour == $selectedHours);
                    ?>
                    <li class="wpstg-dropdown-option" data-value="<?php echo esc_attr($hour); ?>" <?php echo $isSelected ? 'data-selected' : ''; ?>>
                        <?php echo wp_kses($isSelected ? $checkSvg : '<span class="wpstg-dropdown-spacer"></span>', $svgAllowed); ?>
                        <?php echo esc_html($hour) . ' hours'; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="wpstg-dropdown" data-dropdown-select>
            <input type="hidden" name="wpstg-temporary-login-minutes" id="wpstg-temporary-login-minutes" value="<?php echo esc_attr($selectedMinutes); ?>" />
            <button type="button" class="wpstg-dropdown-trigger" data-dropdown-trigger>
                <span data-dropdown-value><?php echo esc_html($selectedMinutes) . ' mins'; ?></span>
                <?php echo wp_kses($chevronSvg, $svgAllowed); ?>
            </button>
            <ul class="wpstg-dropdown-menu" data-dropdown-menu>
                <?php foreach ($minutes as $minute) :
                    $isSelected = ($minute == $selectedMinutes);
                    ?>
                    <li class="wpstg-dropdown-option" data-value="<?php echo esc_attr($minute); ?>" <?php echo $isSelected ? 'data-selected' : ''; ?>>
                        <?php echo wp_kses($isSelected ? $checkSvg : '<span class="wpstg-dropdown-spacer"></span>', $svgAllowed); ?>
                        <?php echo esc_html($minute) . ' mins'; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
