<?php

namespace WPStaging\Pro\Automations\PluginsUpdater\Traits;

trait PluginsUpdaterTrait
{
    /**
     * @param string $pluginFile
     * @param string $oldVersion
     * @param string $newVersion
     * @param string $status
     * @param string $message
     * @param bool $networkUptoDate
     * @return array
     */
    private function formatResponse(string $pluginFile, string $oldVersion, string $newVersion, string $status, string $message, bool $networkUptoDate = false): array
    {
        return [
            'plugin'            => $pluginFile,
            'old_version'       => $oldVersion,
            'new_version'       => $newVersion,
            'status'            => $status,
            'message'           => $message,
            'network_upto_date' => $networkUptoDate,
        ];
    }

    /**
     * @return void
     */
    private function loadWpFiles()
    {
        try {
            require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            require_once(ABSPATH . 'wp-admin/includes/update.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/misc.php');
        } catch (\Throwable $exception) {
            $this->sendResponse(sprintf('Failed to load WP files while updating plugins. Error: %s', $exception->getMessage()));
        }
    }

    /**
     * @param array $response
     * @param string $stagingURL
     * @param array $outdatedPlugins
     * @param bool $summarize
     * @return string
     */
    private function prepareNotificationBody(array $response, string $stagingURL, array $outdatedPlugins = [], bool $summarize = false): string
    {
        $footer = "\n" . sprintf(
            esc_html__("You can manually check the staging site at this link: %s", "wp-staging"),
            esc_url($stagingURL)
        ) . "\n";

        if (count($outdatedPlugins) === 0) {
            $header = esc_html__("Summary: All Plugins are up to date.", "wp-staging") . "\n\n";
            return $header . $footer;
        }

        $total   = count($response);
        $updated = 0;
        $message = esc_html__("Detailed Report:", "wp-staging") . "\n\n";
        foreach ($response as $plugin) {
            if (empty($plugin['plugin'])) {
                continue;
            }

            if (!empty($plugin['status']) && $plugin['status'] === 'success' && version_compare($plugin['new_version'], $plugin['old_version'], '>')) {
                $updated++;
            }

            $message .= "- {$plugin['plugin']} \n";
            if ($plugin['new_version'] === $plugin['old_version']) {
                $message .= "  " . esc_html__("Status: Plugin auto update failed.", "wp-staging") . "\n\n"; // could be related to slow internet connection
                continue;
            }

            $message .= "  " . sprintf(
                esc_html__("Status: %s.", "wp-staging"),
                $plugin['message']
            ) . "\n\n";
        }

        $header = sprintf(
            esc_html__("Summary: %s of %s plugins were updated successfully on the staging site %s", "wp-staging"),
            $updated,
            $total,
            esc_url($stagingURL)
        ) . "\n\n";

        if ($summarize) {
            return sprintf(
                esc_html__("Successfully updated %s of %s plugins.", "wp-staging"),
                $updated,
                $total
            ) . "\n\n" . $footer;
        }

        $message .= $footer;
        return $header . $message;
    }

    /**
     * Get plugin dependencies
     * @param string $pluginFile
     * @return array
     */
    private function getPluginDependencies(string $pluginFile): array
    {
        if (class_exists('WP_Plugin_Dependencies') && \WP_Plugin_Dependencies::has_dependencies($pluginFile)) {
            return \WP_Plugin_Dependencies::get_dependencies($pluginFile);
        }

        return [];
    }

    /**
     * Get plugin dependents
     * @param string $pluginFile
     * @return array
     */
    private function getPluginDependents(string $pluginFile): array
    {
        if (class_exists('WP_Plugin_Dependencies') && \WP_Plugin_Dependencies::has_dependents($pluginFile)) {
            $pluginSlug = dirname($pluginFile);
            return \WP_Plugin_Dependencies::get_dependents($pluginSlug);
        }

        return [];
    }

    /**
     * Check if plugin has unmet dependencies
     * @param string $pluginFile
     * @return bool
     */
    private function hasUnmetDependencies(string $pluginFile): bool
    {
        if (class_exists('WP_Plugin_Dependencies')) {
            return \WP_Plugin_Dependencies::has_unmet_dependencies($pluginFile);
        }

        return false;
    }

    /**
     * Check if plugin has circular dependencies
     * @param string $pluginFile
     * @return bool
     */
    private function hasCircularDependency(string $pluginFile): bool
    {
        if (class_exists('WP_Plugin_Dependencies')) {
            return \WP_Plugin_Dependencies::has_circular_dependency($pluginFile);
        }

        return false;
    }

    /**
     * @param array $plugins
     * @return mixed
     */
    private function sortPluginsByDependencies(array $plugins): array
    {
        usort($plugins, function ($first, $second) {
            $firstHasDependencies = !empty($first['dependencies']);
            $secondHasDependencies = !empty($second['dependencies']);

            if ($firstHasDependencies && !$secondHasDependencies) {
                return -1;
            } elseif (!$firstHasDependencies && $secondHasDependencies) {
                return 1;
            }

            return 0;
        });

        return $plugins;
    }

    /**
     * @param string $pluginFile
     * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    private function updatePlugins(string $pluginFile)
    {
        $this->loadWpFiles();
        global $wp_filesystem;

        try {
            if (empty($wp_filesystem)) {
                WP_Filesystem();
            }

            $updatesReport = $this->processPluginUpdate($pluginFile);
        } catch (\Throwable $error) {
            return $this->sendResponse(sprintf('Unable to update plugin. Error: %s', $error->getMessage()));
        }

        return $this->sendResponse('Plugin updates completed.', $updatesReport);
    }

    /**
     * @param \Plugin_Upgrader $upgrader
     * @param string $pluginFile
     * @return array
     */
    private function processPluginUpdate(string $pluginFile): array
    {
        $pluginData = get_plugin_data(WP_PLUGIN_DIR . '/' . $pluginFile);
        $oldVersion = $pluginData['Version'];
        try {
            $upgrader   = new \Plugin_Upgrader(new \WP_Ajax_Upgrader_Skin());
            $result     = $upgrader->upgrade($pluginFile);
            if (is_wp_error($result) || !$result) {
                $errorMessage = is_wp_error($result) ? $result->get_error_message() : esc_html__("Failed to update plugin.", 'wp-staging');
                return $this->formatResponse($pluginData['Name'], $oldVersion, 'unknown', 'error', $errorMessage);
            }
        } catch (\Throwable $error) {
            return $this->formatResponse($pluginData['Name'], $oldVersion, 'unknown', 'error', $error->getMessage());
        }

        $newVersion = $this->getPluginVersion($pluginFile);
        return $this->formatResponse(
            $pluginData['Name'],
            $oldVersion,
            $newVersion,
            'success',
            sprintf(
                __("Updated successfully from version %s to %s", "wp-staging"),
                $oldVersion,
                $newVersion
            )
        );
    }

    /**
     * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    private function refreshPluginUpdates()
    {
        try {
            wp_update_plugins();
            return $this->sendResponse("Refreshed available updates of plugins.");
        } catch (\Throwable $throwable) {
            return $this->sendResponse(sprintf("Failed to refresh available updates of plugins. Error: %s", $throwable->getMessage()));
        }
    }

    /**
     * @param string $pluginFile
     * @param string $latestVersion
     * @param string $oldVersion
     * @return array|\WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    private function updatePluginsMultisite(string $pluginFile, string $latestVersion, string $oldVersion)
    {
        $sites  = get_sites();
        $result = [];
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);
            if (version_compare($this->getPluginVersion($pluginFile), $latestVersion, '=')) {
                continue;
            }

            $result = $this->updatePlugins($pluginFile);
            restore_current_blog();
            break;
        }

        if (empty($result)) {
            $pluginData    = get_plugin_data(WP_PLUGIN_DIR . '/' . $pluginFile);
            $updatesReport = $this->formatResponse(
                $pluginData['Name'],
                $oldVersion,
                $latestVersion,
                'success',
                sprintf(
                    __("Updated successfully on all network sites from version %s to %s", "wp-staging"),
                    $oldVersion,
                    $latestVersion
                ),
                true
            );
            return $this->sendResponse('Plugin updates completed.', $updatesReport);
        }

        return $result;
    }

    /**
     * Prepares the HTML content for the email notification.
     *
     * This method should be called after the multipart email PR is merged
     * (https://github.com/wp-staging/wp-staging-pro/pull/4017) and HTML emails are enabled.
     *  use esc_html() to escape the HTML content while printing
     *
     * @param array  $response
     * @param string $stagingURL
     * @param array  $outdatedPlugins
     * @param bool   $summarize
     * @return string
     */

    private function prepareHTMLNotificationBody(array $response, string $stagingURL, array $outdatedPlugins = [], bool $summarize = false): string
    {
        $user     = wp_get_current_user()->display_name;
        if (empty($user)) {
            $user = 'Admin';
        }

        $initial  = "<p>" . sprintf(__("Dear %s,", "wp-staging"), esc_html($user)) . "</p><p>" . sprintf(__("WP Staging plugin has successfully updated all plugins on your staging site: %s", "wp-staging"), esc_html($stagingURL)) . "</p>";
        $footer   = '<p>' . sprintf(__("You can manually check the staging site at this link: %s", "wp-staging"), esc_url($stagingURL)) . '</p>';
        $footer  .= '<hr>';
        if (empty($outdatedPlugins)) {
            return $initial . "<h3>" . __("All Plugins are up to date.", "wp-staging") . "</h3>" . $footer;
        }

        $updated = 0;
        $total   = count($response);
        $message = "<h4>" . __("Detailed Report:", "wp-staging") . "</h4><ul>";

        foreach ($response as $plugin) {
            if (empty($plugin['plugin'])) {
                continue;
            }

            $status = __("Plugin auto update failed.", "wp-staging");
            if (!empty($plugin['status']) && $plugin['status'] === 'success' && version_compare($plugin['new_version'], $plugin['old_version'], '>')) {
                $status = sprintf(
                    __("Updated successfully from version %s to %s.", "wp-staging"),
                    esc_html($plugin['old_version']),
                    esc_html($plugin['new_version'])
                );
                $updated++;
            }

            $message .= sprintf(
                "<li><strong>%s</strong><br><em>%s</em></li>",
                esc_html($plugin['plugin']),
                $status
            );
        }

        $message .= "<hr>";
        $message .= "</ul>";

        $summary = sprintf(
            "<strong><span>✅</span>&nbsp;&nbsp;<span>%s</span></strong>",
            sprintf(__("%s of %s plugins were updated successfully.", "wp-staging"), $updated, $total)
        );

        if ($summarize) {
            return sprintf(
                "<p>%s</p>%s",
                sprintf(__("Successfully updated %s of %s plugins.", "wp-staging"), $updated, $total),
                $footer
            );
        }

        $initialMessage = ($updated === $total) ? __("all", "wp-staging") : sprintf(__("%s of %s", "wp-staging"), $updated, $total);
        $initial        = "<p>" . sprintf(__("Dear %s,", "wp-staging"), esc_html($user)) . "</p><p>" . sprintf(__("WP Staging plugin has successfully updated %s plugins on your staging site: %s", "wp-staging"), esc_html($initialMessage), esc_html($stagingURL)) . "</p>";
        $initial       .= "<h4>" . __("Summary", "wp-staging") . "</h4>";

        return "<div>{$initial}{$summary}{$message}{$footer}</div>";
    }
}
