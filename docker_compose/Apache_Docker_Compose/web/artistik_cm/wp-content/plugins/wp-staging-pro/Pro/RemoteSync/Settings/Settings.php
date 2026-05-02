<?php

namespace WPStaging\Pro\RemoteSync\Settings;

class Settings
{
    /**
     * Add remote sync settings tab
     *
     * @filter wpstg.main_settings_tabs
     * @return array
     */
    public function addRemoteSyncSettingsTab($tabs)
    {
        $tabs['remote-sync-settings'] = esc_html__("Remote Sync", "wp-staging") . '<br>' . esc_html__("Connection Key", "wp-staging");

        return $tabs;
    }
}
