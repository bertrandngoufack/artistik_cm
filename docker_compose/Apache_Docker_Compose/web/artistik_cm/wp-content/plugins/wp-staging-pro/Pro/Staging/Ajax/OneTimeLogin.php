<?php

namespace WPStaging\Pro\Staging\Ajax;

use WPStaging\Framework\Facades\Sanitize;
use WPStaging\Pro\Staging\AutoLogin\LoginLinkGenerator;
use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Pro\WPStagingPro;

class OneTimeLogin extends AbstractTemplateComponent
{
    /**
     * @var LoginLinkGenerator
     */
    private $loginUrlGenerator;

    /**
     * @param LoginLinkGenerator $loginUrlGenerator
     * @param TemplateEngine $templateEngine
     */
    public function __construct(LoginLinkGenerator $loginUrlGenerator, TemplateEngine $templateEngine)
    {
        parent::__construct($templateEngine);
        $this->loginUrlGenerator = $loginUrlGenerator;
    }

    /**
     * @return void
     */
    public function ajaxGenerateStagingLoginUrl()
    {
        if (!$this->canRenderAjax() || !current_user_can('manage_options') || !WPStagingPro::isValidLicense()) {
            wp_send_json_error(['message' => 'You are not allowed to perform this action.'], 403);
        }

        $cloneID = isset($_POST['clone']) ? Sanitize::sanitizeString($_POST['clone']) : null;
        if (empty($cloneID)) {
            wp_send_json_error(['message' => 'Missing staging site id'], 403);
        }

        $loginUrl = $this->loginUrlGenerator->generate($cloneID);
        if (is_wp_error($loginUrl)) {
            wp_send_json_error([
                'message' => $loginUrl->get_error_message(),
            ], 403);
        }

        wp_send_json_success(['stagingUrl' => $loginUrl]);
    }
}
