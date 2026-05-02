<?php

namespace WPStaging\Pro\Push\Tasks\Database\Adjustment;

use WPStaging\Framework\Facades\Hooks;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Pro\Push\Tasks\OptionAdjustmentTask;

/**
 * This class is responsible for updating active plugins in options table during staging site push.
 */
class UpdateActivePluginsTask extends OptionAdjustmentTask
{
    /** @var string */
    const FILTER_PUSHING_UPDATE_ACTIVE_PLUGINS = 'wpstg.pushing.update_active_plugins';

    public static function getTaskName(): string
    {
        return 'push_update_active_plugins';
    }

    public static function getTaskTitle(): string
    {
        return 'Update active plugins in options table';
    }

    public function adjustOptionsTable(): TaskResponseDto
    {
        $this->logger->info("Updating {$this->tmpOptionsTable} active_plugins");

        // Get active_plugins from tmp tables
        $activePlugins = $this->wpdb->get_var("SELECT option_value FROM {$this->tmpOptionsTable} WHERE option_name = 'active_plugins' ");
        if (empty($activePlugins)) {
            $activePlugins = [];
        } else {
            $activePlugins = unserialize($activePlugins);
        }

        // Get active_plugins from production site
        $activePluginsProd = [];
        if ($this->isTableExists($this->prodOptionsTable)) {
            $activePluginsProd = $this->wpdb->get_var("SELECT option_value FROM {$this->prodOptionsTable} WHERE option_name = 'active_plugins' ");
            $activePluginsProd = unserialize($activePluginsProd);
        }

        if (!$activePlugins) {
            $this->logger->warning("Can not get list of active plugins from from {$this->tmpOptionsTable} - DB Error {$this->wpdb->last_error}");
        }

        $activePlugins = Hooks::applyFilters(self::FILTER_PUSHING_UPDATE_ACTIVE_PLUGINS, $activePlugins);

        $plugin = plugin_basename(trim(WPSTG_PLUGIN_FILE));

        $activePlugins = array_filter($activePlugins, function ($pluginSlug) {
            return strpos($pluginSlug, 'wp-staging') === false;
        });

        // Only activate that WP Staging plugin which is used during PUSH
        $activePlugins[] = $plugin;

        // Activate WP STAGING Hooks Plugin if it is activated on production site
        if (array_search('wp-staging-hooks/wp-staging-hooks.php', $activePluginsProd) !== false) {
            $activePlugins[] = 'wp-staging-hooks/wp-staging-hooks.php';
        }

        // Update active_plugins
        $resultActivePlugins = $this->wpdb->query(
            "UPDATE {$this->tmpOptionsTable} SET option_value = '" . serialize($activePlugins) . "' WHERE option_name = 'active_plugins' "
        );

        if ($resultActivePlugins === false) {
            $this->logger->error("Can not update table active_plugins in {$this->tmpOptionsTable}");
        }

        return $this->generateResponse();
    }
}
