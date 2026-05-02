<?php

namespace WPStaging\Pro\Staging\Ajax;

use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\Facades\Sanitize;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Pro\Staging\AutoLogin\LoginLinkGenerator;
use WPStaging\Pro\WPStagingPro;
use WPStaging\Staging\Sites;

class MagicLoginLink extends AbstractTemplateComponent
{
    /** @var LoginLinkGenerator */
    private $loginLinkGenerator;

    public function __construct(LoginLinkGenerator $loginLinkGenerator, TemplateEngine $templateEngine)
    {
        parent::__construct($templateEngine);
        $this->loginLinkGenerator = $loginLinkGenerator;
    }

    /**
     * @return void
     */
    public function ajaxLoginLinkUserInterface()
    {
        if (!$this->canRenderAjax() || !current_user_can('manage_options') || !WPStagingPro::isValidLicense()) {
            wp_send_json_error(['message' => esc_html__('You are not allowed to perform this action.', 'wp-staging')]);
        }

        $cloneID        = empty($_POST["clone"]) ? '' : Sanitize::sanitizeString($_POST["clone"]);
        $existingClones = get_option(Sites::STAGING_SITES_OPTION, []);
        if (!empty($cloneID) && array_key_exists($cloneID, $existingClones)) {
            $clone      = $existingClones[$cloneID];
            $stagingUrl = $clone['url'] ?? '';
            $output = $this->renderTemplate('pro/generate-login-ui.php', [
                'urlAssets'        => trailingslashit(WPSTG_PLUGIN_URL) . 'assets/',
                'canUseMagicLogin' => $this->loginLinkGenerator->canUseMagicLogin($stagingUrl),
                'cloneId'          => $cloneID,
                'cloneName'        => empty($_POST["cloneName"]) ? '' : Sanitize::sanitizeString($_POST["cloneName"]),
                'isNetworkClone'   => empty($clone['networkClone']) ? false : $clone['networkClone'],
            ]);
            wp_send_json_success($output);
        }

        wp_send_json_error([
            'message' => esc_html__("Unknown error. Please reload the page and try again.", "wp-staging"),
        ]);
    }

    /**
     * @return void
     */
    public function ajaxSaveGeneratedLinkData()
    {
        if (!$this->canRenderAjax() || !current_user_can('manage_options') || !WPStagingPro::isValidLicense()) {
            wp_send_json_error(['message' => esc_html__('You are not allowed to perform this action.', 'wp-staging')]);
        }

        if (empty($_POST['cloneID']) || empty($_POST['role'])) {
            wp_send_json_error(['message' => esc_html__('Clone ID and role are required.', 'wp-staging')]);
        }

        $cloneID  = sanitize_text_field($_POST['cloneID']);
        $userRole = sanitize_text_field($_POST['role']);
        $minutes  = !empty($_POST['minutes']) ? sanitize_text_field($_POST['minutes']) : '0';
        $hours    = !empty($_POST['hours']) ? sanitize_text_field($_POST['hours']) : '0';
        $days     = !empty($_POST['days']) ? sanitize_text_field($_POST['days']) : '0';
        $expiry   = strtotime("+{$days} days +{$hours} hours +{$minutes} minutes");
        if ($expiry <= time()) {
            wp_send_json_error([
                'message' => esc_html__('The expiry time must be set in the future to generate a login link.', 'wp-staging'),
            ]);
        }

        $relativeExpiry = $expiry - time();
        $loginUrl       = $this->loginLinkGenerator->generateTempUserLoginUrl($cloneID, $userRole, $relativeExpiry);
        if (is_wp_error($loginUrl)) {
            wp_send_json_error(['message' => $loginUrl->get_error_message()]);
        }

        wp_send_json_success([
            'message'  => esc_html__('Login Link created successfully!', 'wp-staging'),
            'loginUrl' => $loginUrl,
        ]);
    }
}
