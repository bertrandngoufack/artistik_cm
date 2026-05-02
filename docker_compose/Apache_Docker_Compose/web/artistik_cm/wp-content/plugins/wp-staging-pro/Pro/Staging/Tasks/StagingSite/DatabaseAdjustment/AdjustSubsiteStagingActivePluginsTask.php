<?php

namespace WPStaging\Pro\Staging\Tasks\StagingSite\DatabaseAdjustment;

use WPStaging\Framework\Facades\Hooks;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Staging\Service\StagingSetup;
use WPStaging\Staging\Tasks\DatabaseAdjustmentTask;
use WPStaging\Staging\Tasks\StagingSite\DatabaseAdjustment\UpdateOptionsInOptionsTableTask;

/**
 * Replacement for WPStaging\Pro\Staging\Data\Steps\MultisiteUpdateActivePlugins
 * This class is responsible for adding network active plugins to the staging site when cloning a subsite into single staging site
 */
class AdjustSubsiteStagingActivePluginsTask extends DatabaseAdjustmentTask
{
    /**
     * @return string
     */
    public static function getTaskName()
    {
        return 'staging_network_active_plugins';
    }

    /**
     * @return string
     */
    public static function getTaskTitle()
    {
        return 'Activating network active plugins for the staging site';
    }

    /**
     * @return TaskResponseDto
     */
    public function execute()
    {
        $this->setup();
        $this->logger->info('Activating network active plugins for the staging site.');

        if ($this->isOptionsTableExcluded()) {
            $this->logger->warning('Skipping active plugins adjustment as options table is excluded.');
            return $this->generateResponse();
        }

        $productionDb = $this->database->getWpdb();

        // Get active_plugins value from sub site options table
        $activePlugins = $productionDb->get_var("SELECT option_value FROM {$productionDb->prefix}options WHERE option_name = 'active_plugins' ");
        if (!$activePlugins) {
            $this->logger->warning("Option name 'active_plugins' is empty for the production site.");
            $activePlugins = serialize([]);
        }

        // Get active_sitewide_plugins value from main multisite wp_sitemeta table
        $activeSitewidePlugins = $productionDb->get_var("SELECT meta_value FROM {$productionDb->base_prefix}sitemeta WHERE meta_key = 'active_sitewide_plugins' ");
        if (!$activeSitewidePlugins) {
            $this->logger->warning("Site meta 'active_sitewide_plugins' is empty for the production site.");
            $activeSitewidePlugins = serialize([]);
        }

        // Let merge active plugins from the subsite and network active plugins
        $activeSitewidePlugins = unserialize($activeSitewidePlugins);
        $activePlugins = unserialize($activePlugins);
        $allPlugins = array_merge($activePlugins, array_keys($activeSitewidePlugins));
        sort($allPlugins);

        if ($this->jobDataDto->getJobType() === StagingSetup::JOB_NEW_STAGING_SITE) {
            $activePlugins = Hooks::applyFilters(UpdateOptionsInOptionsTableTask::FILTER_CLONING_UPDATE_ACTIVE_PLUGINS, $allPlugins);
            if (is_array($activePlugins)) {
                $allPlugins = $activePlugins;
            }
        }

        if ($this->updateOption('active_plugins', serialize($allPlugins)) === false) {
            $this->logger->error("Can not update option active_plugins in when adjusting staging site active plugins according to network active plugins.");
        }

        return $this->generateResponse();
    }
}
