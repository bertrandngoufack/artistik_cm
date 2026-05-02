<?php

namespace WPStaging\Pro\RemoteSync\Ajax;

use WPStaging\Framework\Facades\Sanitize;
use WPStaging\Framework\Security\Auth;
use WPStaging\Pro\RemoteSync\ConnectionKey;

class Settings
{
    /**
     * @var Auth
     */
    private $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Ajax: Regenerate connection key for remote sync
     * @return void
     */
    public function ajaxRegenerateConnectionKey()
    {
        if (!$this->auth->isAuthenticatedRequest()) {
            wp_send_json_error(esc_html__('You are not allowed to do this.', 'wp-staging'));
        }

        $connectionKey = new ConnectionKey();
        $connectionKey->regenerate();
        wp_send_json_success(
            [
                'connectionKey' => $connectionKey->getConnectionKey(),
                'message'       => esc_html__('Connection Key has been regenerated successfully.', 'wp-staging'),
            ]
        );
    }

    /**
     * Ajax: Set/Update password
     * @return void
     */
    public function ajaxProtect()
    {
        if (!$this->auth->isAuthenticatedRequest()) {
            wp_send_json_error(esc_html__('You are not allowed to do this.', 'wp-staging'));
        }

        $password = empty($_POST['password']) ? '' : Sanitize::sanitizePassword($_POST['password']);
        if (empty($password)) {
            wp_send_json_error(esc_html__('Password cannot be empty.', 'wp-staging'));
        }

        $connectionKey = new ConnectionKey();
        $connectionKey->protect($password);

        wp_send_json_success(esc_html__('Connection key password has been set successfully.', 'wp-staging'));
    }

    /**
     * Ajax: Toggle remote sync enabled/disabled state
     * @return void
     */
    public function ajaxToggleRemoteSyncEnabled()
    {
        if (!$this->auth->isAuthenticatedRequest()) {
            wp_send_json_error(esc_html__('You are not allowed to do this.', 'wp-staging'));
        }

        $enabled = !empty($_POST['enabled']);
        ConnectionKey::setEnabled($enabled);

        wp_send_json_success(
            [
                'enabled' => $enabled,
                'message' => $enabled
                    ? esc_html__('Remote Sync has been enabled.', 'wp-staging')
                    : esc_html__('Remote Sync has been disabled. Incoming connections will be rejected.', 'wp-staging'),
            ]
        );
    }

    /**
     * Ajax: Remove password
     * @return void
     */
    public function ajaxUnprotect()
    {
        if (!$this->auth->isAuthenticatedRequest()) {
            wp_send_json_error(esc_html__('You are not allowed to do this.', 'wp-staging'));
        }

        $connectionKey = new ConnectionKey();
        $connectionKey->unprotect();

        wp_send_json_success(esc_html__('Connection key password has been removed successfully.', 'wp-staging'));
    }
}
