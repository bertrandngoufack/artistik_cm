<?php

namespace WPStaging\Pro\Staging;

use WPStaging\Backend\Administrator;
use WPStaging\Framework\DI\ServiceProvider;
use WPStaging\Framework\Facades\Hooks;
use WPStaging\Pro\Staging\WooCommerceSchedulerHandler;

/**
 * Used to register classes and hooks for the staging site.
 */
class StagingSiteServiceProvider extends ServiceProvider
{
    /** @var string */
    const FILTER_NOTICES_DISABLE_PLUGIN_UPDATE_NOTICE = 'wpstg.notices.disable.plugin-update-notice';

    protected function registerClasses()
    {
        $this->container->singleton(SettingsTabs::class);
    }

    protected function addHooks()
    {
        if (Hooks::applyFilters(self::FILTER_NOTICES_DISABLE_PLUGIN_UPDATE_NOTICE, false) === true) {
            add_filter('site_transient_update_plugins', $this->container->callback(PluginUpdates::class, 'disablePluginUpdateChecksOnStagingSite'), 10, 1);
        }

        add_filter(Administrator::FILTER_MAIN_SETTING_TABS, $this->container->callback(SettingsTabs::class, 'addMailSettingsTabOnStagingSite'), 10, 1);
        add_action("wp_ajax_wpstg_update_staging_mail_settings", $this->container->callback(SettingsTabs::class, 'ajaxUpdateStagingMailSettings')); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action("init", $this->container->callback(WooCommerceSchedulerHandler::class, "disableActionScheduler")); // phpcs:ignore WPStaging.Security.AuthorizationChecked
    }
}
