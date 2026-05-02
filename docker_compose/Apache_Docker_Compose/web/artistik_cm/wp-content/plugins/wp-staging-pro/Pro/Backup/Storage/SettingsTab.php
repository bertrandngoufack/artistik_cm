<?php

namespace WPStaging\Pro\Backup\Storage;

use WPStaging\Pro\License\Licensing;
use WPStaging\Pro\WPStagingPro;

class SettingsTab
{
    /**
     * @var Licensing
     */
    private $licensing;

    public function __construct(Licensing $licensing)
    {
        $this->licensing = $licensing;
    }

    /**
     * Add remote storage tab
     *
     * @filter wpstg_main_settings_tabs
     * @return array returns tabs in key and title format
     */
    public function addRemoteStoragesSettingsTab($tabs)
    {
        if (WPStagingPro::isValidLicense() && !$this->licensing->isPersonalLicense()) {
            $tabs['remote-storages'] = __("Storage Providers", "wp-staging");
        }

        return $tabs;
    }
}
