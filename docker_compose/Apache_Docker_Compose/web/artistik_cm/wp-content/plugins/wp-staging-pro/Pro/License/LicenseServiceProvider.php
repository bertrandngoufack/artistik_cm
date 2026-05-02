<?php

namespace WPStaging\Pro\License;

use WPStaging\Framework\DI\ServiceProvider;

class LicenseServiceProvider extends ServiceProvider
{
    protected function registerClasses()
    {
        $this->initializeLegacy();
    }

    /**
     * Initialize legacy classes
     *
     * Allow executing cron jobs by regular frontpage visitors
     */
    private function initializeLegacy()
    {
        $this->container->make(Licensing::class);
        $this->container->make(Version::class);
        if (defined('WPSTG_IS_DEV') && WPSTG_IS_DEV === true && class_exists(DevLicensing::class)) {
            $this->container->make(DevLicensing::class);
        }
    }

    /**
     * @return void
     */
    protected function addHooks()
    {
        add_action('wp_ajax_wpstg-refresh-license-status', $this->container->callback(AjaxLicenseHandler::class, 'ajaxRefreshLicenseStatus')); // phpcs:ignore WPStaging.Security.AuthorizationChecked
    }
}
