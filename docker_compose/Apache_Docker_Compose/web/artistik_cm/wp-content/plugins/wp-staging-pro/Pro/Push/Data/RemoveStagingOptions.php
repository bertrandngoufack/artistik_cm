<?php

namespace WPStaging\Pro\Push\Data;

use WPStaging\Staging\Sites;

class RemoveStagingOptions extends OptionsTablePushService
{
    /**
     * @inheritDoc
     */
    protected function processOptionsTable()
    {
        $this->log("Remove staging site specific options from $this->tmpOptionsTable");

        $sql = $this->productionDb->prepare(
            "DELETE FROM `$this->tmpOptionsTable` WHERE `option_name` = %s;\n",
            'wpstg_connection'
        );

        $sql .= $this->productionDb->prepare(
            "DELETE FROM `$this->tmpOptionsTable` WHERE `option_name` = %s;\n",
            'wpstg_emails_disabled'
        );

        $sql .= $this->productionDb->prepare(
            "DELETE FROM `$this->tmpOptionsTable` WHERE `option_name` = %s;\n",
            'wpstg_entire_network_clone_notice'
        );

        $sql .= $this->productionDb->prepare(
            "DELETE FROM `$this->tmpOptionsTable` WHERE `option_name` = %s;\n",
            'wpstg_resave_permalinks_executed'
        );

        /*
         * Prevent Staging Site created before WPSTAGING Pro 4.0.5
         * from re-inserting the old staging sites option on Push.
         */
        $sql .= $this->productionDb->prepare(
            "DELETE FROM `$this->tmpOptionsTable` WHERE `option_name` = %s;\n",
            'wpstg_existing_clones_beta'
        );

        $sql .= $this->productionDb->prepare(
            "DELETE FROM `$this->tmpOptionsTable` WHERE `option_name` = %s;\n",
            Sites::STAGING_LOGIN_LINK_SETTINGS
        );

        $this->executeSql($sql);

        return true;
    }
}
