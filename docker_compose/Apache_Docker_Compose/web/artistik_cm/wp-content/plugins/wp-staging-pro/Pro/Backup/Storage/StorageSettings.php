<?php

namespace WPStaging\Pro\Backup\Storage;

use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\Utils\Sanitize;
use WPStaging\Framework\Security\Auth;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Pro\WPStagingPro;

class StorageSettings extends AbstractTemplateComponent
{
    /** @var Auth */
    private $auth;

    /** @var Sanitize */
    private $sanitizer;

    /**
     * @param Auth $auth
     * @param TemplateEngine $templateEngine
     * @param Sanitize $sanitizer
     */
    public function __construct(Auth $auth, TemplateEngine $templateEngine, Sanitize $sanitizer)
    {
        parent::__construct($templateEngine);
        $this->auth      = $auth;
        $this->sanitizer = $sanitizer;
    }

    /**
     * @return void
     */
    public function ajaxRenderStorageSettings()
    {
        if (!$this->auth->isAuthenticatedRequest()) {
            wp_send_json_error(esc_html__('Invalid Request! User is not authenticated.', 'wp-staging'));
        }

        if (!WPStagingPro::isValidLicense()) {
            wp_send_json_error(esc_html__('Invalid License! Your license key of WP Staging Pro is invalid or deactivated.', 'wp-staging'));
        }

        $providerId = empty($_POST['provider']) ? null : $this->sanitizer->sanitizeString($_POST['provider']);
        if (empty($providerId)) {
            wp_send_json_error(esc_html__('Invalid Storage Provider! Please contact support@wp-staging.com.', 'wp-staging'));
        }

        $provider = strtolower($providerId);
        $result   = $this->templateEngine->render(
            "pro/settings/tabs/storages/" . $provider . "-settings.php",
            [
                "providerId" => $providerId,
                "redirectTo" => 'wpstg_backup',
            ]
        );
        wp_send_json_success($result);
    }
}
