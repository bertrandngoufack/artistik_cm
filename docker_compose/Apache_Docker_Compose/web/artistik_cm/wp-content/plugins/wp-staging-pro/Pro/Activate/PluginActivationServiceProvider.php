<?php

namespace WPStaging\Pro\Activate;

use Exception;
use WPStaging\Framework\Adapter\WpAdapter;
use WPStaging\Framework\DI\ServiceProvider;
use WPStaging\Framework\Language\Language;
use WPStaging\Framework\Notices\Notices;
use WPStaging\Framework\Security\Auth;
use WPStaging\Framework\Traits\NoticesTrait;
use WPStaging\Vendor\lucatume\DI52\ContainerException;

class PluginActivationServiceProvider extends ServiceProvider
{
    use NoticesTrait;

    /**
     * @var string
     */
    const TRANSIENT_REDIRECT_AFTER_PRO_INSTALL = 'wpstg_pro_activation_redirect';

    /**
     * @var string
     */
    private $error = '';

    /**
     * Register classes in the container.
     *
     * @return void
     */
    protected function registerClasses()
    {
        // no-op
    }

    /**
     * @return void
     * @throws ContainerException
     */
    protected function addHooks()
    {
        if (!wpstgIsFreeVersionRequiredForPro()) {
            return;
        }

        static $isRegistered = false;
        if ($isRegistered) {
            return;
        }

        add_action('admin_menu', [$this, 'addInstallPage']);
        add_action('admin_init', $this->container->callback($this, 'activateOrDeactivateFreeAndProVersion'));
        add_action("wp_ajax_wpstg_install_free", [$this, "ajaxInstallAndActivateFreeVersion"]); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action("wp_ajax_wpstg_load_install_free_interface", [$this, "getInstallPage"]); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('upgrader_process_complete', [$this, 'activateFreeAfterUpload'], 10, 2);

        $isRegistered = true;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function activateOrDeactivateFreeAndProVersion()
    {
        if (wpstgIsFreeActiveInNetworkOrCurrentSite()) {
            $this->showProUpgradingNotice();
        }

        remove_all_filters(WpAdapter::FILTER_OPTION_ACTIVE_PLUGINS); // this is important to let WP know all active plugins before updating them (useful while pushing).

        // Bail if it is network admin page and free version is active on network subsites.
        if (is_network_admin() && wpstgIsFreeVersionActiveInNetwork()) {
            return;
        }

        // Bail if it is no network admin page and free version is active.
        if (!is_network_admin() && wpstgIsFreeVersionActive()) {
            delete_transient(self::TRANSIENT_REDIRECT_AFTER_PRO_INSTALL);
            return;
        }

        if ($this->activateFreeVersion()) {
            // Bail here to avoid the notice below.
            delete_transient(self::TRANSIENT_REDIRECT_AFTER_PRO_INSTALL);
            return;
        }

        if (empty($_GET['page']) && (empty(wpstgGetFreeVersionNumberIfInstalled()) || $this->isFreeVersionOutdated()) && get_transient(self::TRANSIENT_REDIRECT_AFTER_PRO_INSTALL) === false) {
            header('Location: ' . admin_url('admin.php?page=wpstg-install'));
            set_transient(self::TRANSIENT_REDIRECT_AFTER_PRO_INSTALL, 'true', 300);
        }

        $this->addNoticesHooks();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function ajaxInstallAndActivateFreeVersion()
    {
        /** @var Auth $auth */
        $auth = $this->container->get(Auth::class);
        if (!$auth->isAuthenticatedRequest('wpstg-enable-free-nonce', 'install_plugins')) {
            $this->error = esc_html__('Failed to activate the WP STAGING core plugin for security reasons. Current user does not have permission to install plugins. Make sure current user has the capability `install_plugins`', 'wp-staging');
            wp_send_json_error($this->error);
        }

        if (wpstgIsProPluginActiveInNetworK() && !current_user_can('manage_network_plugins')) {
            $this->error = esc_html__('Fail to activate the WP STAGING core plugin. You do not have permission to activate network plugins (manage_network_plugins)!', 'wp-staging');
            wp_send_json_error($this->error);
        }

        if ($this->downloadFreeVersion()) {
            $this->activateFreeVersion();
            set_site_transient('wpstgUpgradingFreeToPro', true, 1 * HOUR_IN_SECONDS);
            delete_transient(self::TRANSIENT_REDIRECT_AFTER_PRO_INSTALL);
            $queryArgs = [
                'activate' => true,
            ];
            wp_send_json_success([
                'url' => $this->getRedirectUrl($queryArgs),
            ]);
        }

        wp_send_json_error(esc_html($this->error));
    }

    /**
     * @return void
     */
    public function addNoticesHooks()
    {
        if (is_network_admin()) {
            add_action(Notices::ACTION_NETWORK_ADMIN_NOTICES, [$this, 'renderFreeVersionRequireNotice']);
        } else {
            add_action(Notices::ACTION_ADMIN_NOTICES, [$this, 'renderFreeVersionRequireNotice']);
        }
    }

    /**
     * @return void
     */
    public function renderFreeVersionRequireNotice()
    {
        ?>
        <div class="wpstg-require-free-non-install-page-main-container">
            <div class="wpstg-require-free-non-install-page">
                <p>
                <?php
                if (!empty($this->error)) {
                    ?>
                        <?php
                        echo wp_kses_post($this->error); ?>

                    <?php
                }
                ?>
                </p>
            </div>
            <p class="wpstg-install-message" style="display: none;"></p>
        </div>
        <?php
    }

    /**
     * @param string $appendNotice
     * @param string $buttonText
     * @return string
     */
    private function formatActivateFreeVersionNotice(string $appendNotice = '', $buttonText = 'Activate Now'): string
    {
        $nonce = wp_create_nonce('wpstg-enable-free-nonce');
        $returnNotice = sprintf(
            __('Please activate the free %s. This is required to activate %s %s', 'wp-staging'),
            '<a href="https://wordpress.org/plugins/wp-staging/" target="_blank">' . __('WP Staging core plugin', 'wp-staging') . '</a>',
            '<strong>WP Staging Pro</strong>',
            '<a href="#" class="button" id="wpstg-activate-free" data-nonce="' . esc_attr($nonce) . '"><span id="wpstg-plugin-activation-text">' . esc_html($buttonText) . '</span> <span id="wpstg-plugin-activation-loader"></span></a>'
        );


        if (!empty($appendNotice)) {
            $returnNotice .= '<br><br>' . $appendNotice;
        }

        return $returnNotice;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function activateFreeVersion(): bool
    {
        if (empty(wpstgGetFreeVersionNumberIfInstalled())) {
            $this->error = $this->formatActivateFreeVersionNotice('', __('Install & Activate Now', 'wp-staging'));
            return false;
        }

        if ($this->isFreeVersionOutdated()) {
            $message     = __('Please update the WP STAGING core plugin! The installed core plugin version is not up to date and it must be at least ', 'wp-staging') . esc_html(WPSTGPRO_MINIMUM_FREE_VERSION);
            $this->error = $this->formatActivateFreeVersionNotice($message, __('Update & Activate Now', 'wp-staging'));
            return false;
        }

        // important for not disabling activated plugins while activating our free version!
        remove_all_filters(WpAdapter::FILTER_OPTION_ACTIVE_PLUGINS);
        remove_all_filters(WpAdapter::FILTER_SITE_OPTION_ACTIVE_SITEWIDE_PLUGINS);

        $freeVersionPluginSlug = wpstgGetPluginSlug(WPSTG_FREE_VERSION_PLUGIN_FILE);
        if (wpstgIsProPluginActiveInNetworK()) {
            $isFreeVersionActivated = activate_plugin($freeVersionPluginSlug, '', true);
        } else {
            $isFreeVersionActivated = activate_plugin($freeVersionPluginSlug);
        }

        if (is_wp_error($isFreeVersionActivated)) {
            $message     = __("Error: Failed to activate the WP STAGING core plugin. Please contact support@wp-staging.com. Error message: ", 'wp-staging') . esc_html($isFreeVersionActivated->get_error_message());
            $this->error = $this->formatActivateFreeVersionNotice($message);
            return false;
        }

        return true;
    }

    /**
     * Activate free version after uploading and installing.
     *
     * @param \WP_Upgrader $upgrader
     * @param array       $hookExtra
     *
     * @return void
     */
    public function activateFreeAfterUpload($upgrader, array $hookExtra)
    {
        if (empty($upgrader->skin->type) || $upgrader->skin->type !== 'upload') {
            return;
        }

        // Check if the operation is for installing a plugin
        if (!isset($hookExtra['type']) || $hookExtra['type'] !== 'plugin') {
            return;
        }

        if (!isset($hookExtra['action']) || !in_array($hookExtra['action'], ['install'], true)) {
            return;
        }

        if (empty($upgrader->result['destination_name']) || strpos($upgrader->result['destination_name'], 'wp-staging') === false) {
            return;
        }

        $this->activateOrDeactivateFreeAndProVersion();
        // Redirect with a success notice
        $url = admin_url('plugins.php?activate=1');
        echo "<script>location.href = '" . esc_url($url) . "'</script>";
        exit;
    }

    /**
     * Show notice when user activates Pro with Free active
     *
     * @return void
     */
    private function showProUpgradingNotice()
    {
        if (!is_admin() || !get_site_transient('wpstgUpgradingFreeToPro') || !current_user_can('activate_plugins')) {
            return;
        }

        delete_site_transient('wpstgUpgradingFreeToPro');
        add_action(is_network_admin() ? Notices::ACTION_NETWORK_ADMIN_NOTICES : Notices::ACTION_ADMIN_NOTICES, function () { // phpcs:ignore WPStaging.Security.FirstArgNotAString
            echo '<div class="notice-success wpstg-welcome-notice notice">';
            echo '<p style="font-weight: bold;">' . esc_html__('Welcome to WP STAGING Pro!', 'wp-staging') . '</p>';
            $supportUrl = Language::localizeSupportUrl('https://wp-staging.com/support/');
            echo '<p>' . wp_kses_post(sprintf(__('All Pro features are now unlocked. Need help? %1$sContact our support team%2$s.', 'wp-staging'), '<a href="' . esc_url($supportUrl) . '" target="_blank">', '</a>')) . '</p>';
            echo '<p>' . wp_kses_post(
                sprintf(
                    /* translators: URL to enter license key, URL to wp-staging.com account page. */
                    __('To get started, please enter your license key <a href="%s">here</a>. You can find your license key in your <a href="%s" target="_blank">account page</a>.', 'wp-staging'),
                    esc_url(self_admin_url('admin.php?page=wpstg-license')),
                    esc_url('https://wp-staging.com/your-account/')
                )
            ) . '</p>';
            echo '</div>';
        });
    }

    /**
     * @return bool
     */
    private function isFreeVersionOutdated(): bool
    {
        if (wpstgIsFreeVersionCompatible()) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    private function downloadFreeVersion(): bool
    {
        require_once(trailingslashit(ABSPATH) . 'wp-admin/includes/class-wp-upgrader.php');

        $pluginZip = 'https://downloads.wordpress.org/plugin/wp-staging.latest-stable.zip';
        //$pluginZip = 'https://wp-staging.com/core-download/wp-staging3.1.0.zip';

        $skin      = new \WP_Ajax_Upgrader_Skin();
        $upgrader  = new \Plugin_Upgrader($skin);

        $args = [
            'overwrite_package' => true, // overwrite existing files.
        ];
        $installed = $upgrader->install($pluginZip, $args);

        if (is_wp_error($installed)) {
            $this->error = sprintf(
                __('Error: Failed to install the WP STAGING core plugin. Error message: %s', 'wp-staging'),
                esc_html($installed->get_error_message())
            );
            return false;
        }

        if (empty($installed)) {
            $this->error = __("Error: Failed to install the WP STAGING core plugin due to unknown reason. Please try again or contact support@wp-staging.com!", 'wp-staging');
            return false;
        }

        return true;
    }

    /**
     * @param  array $queryArgs
     * @return string
     */
    private function getRedirectUrl(array $queryArgs = []): string
    {
        $url = add_query_arg(
            $queryArgs,
            wpstgIsProPluginActiveInNetworK() ? network_admin_url('plugins.php') : self_admin_url('plugins.php')
        );
        return $url;
    }

    /**
     * @return void
     */
    public function addInstallPage()
    {
        if (!empty(wpstgGetFreeVersionNumberIfInstalled()) && !$this->isFreeVersionOutdated()) {
            return;
        }

        add_submenu_page('tools.php', 'WP Staging Install', __("WP Staging Install", "wp-staging"), "manage_options", "wpstg-install", [$this, "getInstallPage"]);
    }

    /**
     * @return void
     */
    public function getInstallPage()
    {
        require_once WPSTG_VIEWS_DIR . 'pro/wpstg-install.php';
    }
}
