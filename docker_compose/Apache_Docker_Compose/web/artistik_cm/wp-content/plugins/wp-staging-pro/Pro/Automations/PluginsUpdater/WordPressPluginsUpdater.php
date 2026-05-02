<?php

namespace WPStaging\Pro\Automations\PluginsUpdater;

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Adapter\SourceDatabase;
use WPStaging\Framework\Rest\Rest;
use WPStaging\Notifications\Notifications;
use WPStaging\Pro\Automations\BackgroundProcessing\PreparePluginsAutoUpdater;
use WPStaging\Pro\Automations\PluginsUpdater\Traits\PluginsUpdaterTrait;
use WPStaging\Pro\WPStagingPro;
use WPStaging\Staging\Sites;

use function WPStaging\functions\debug_log;

class WordPressPluginsUpdater extends AbstractPluginsUpdater
{
    use PluginsUpdaterTrait;

    /**
     * @param array|object $options
     * @return void
     */
    public function autoUpdatePlugins($options)
    {
        // Support both array (refactored code) and object (legacy code) formats
        if (is_array($options)) {
            $options = $this->normalizeOptionsFromArray($options);
        }

        if (empty($options->isAutoUpdatePlugins) || empty($options->cloneDirectoryName)) {
            return;
        }

        $stagingUrl = trailingslashit(get_home_url()) . $options->cloneDirectoryName;
        if (!empty($options->cloneHostname)) {
            $stagingUrl = $options->cloneHostname;
        }

        $cloneId = empty($options->clone) ? '' : $options->clone;

        $data = [
            'name'                => 'wp_plugins_auto_updater',
            'stagingUrl'          => $stagingUrl,
            'isAutoUpdatePlugins' => $options->isAutoUpdatePlugins,
            'isWpCliRequest'      => true,
            'isNetworkClone'      => $this->isNetworkClone($options),
            'authToken'           => $this->generateAccessToken($options),
            'cloneId'             => $cloneId,
        ];

        $jobId = WPStaging::make(PreparePluginsAutoUpdater::class)->prepare($data);
        if ($jobId instanceof \WP_Error) {
            throw new \RuntimeException('Failed to trigger Plugins Updates in background: ' . $jobId->get_error_message());
        } else {
            debug_log('Plugins Updates triggered in background with job ID: ' . $jobId);
        }
    }

    /**
     * @return void
     */
    public function registerRestRoutes()
    {
        register_rest_route(Rest::WPSTG_ROUTE_NAMESPACE_V1, '/wpstg_plugins_update', [
            'methods'             => 'POST',
            'callback'            => [$this, 'mayBeUpdatePlugins'],
            'permission_callback' => [$this, 'verifyAuthToken'],
        ]);

        register_rest_route(Rest::WPSTG_ROUTE_NAMESPACE_V1, '/wpstg_get_outdated_plugins', [
            'methods'             => 'POST',
            'callback'            => [$this, 'getOutdatedPlugins'],
            'permission_callback' => [$this, 'verifyAuthToken'],
        ]);

        register_rest_route(Rest::WPSTG_ROUTE_NAMESPACE_V1, '/wpstg_activate_updated_plugins', [
            'methods'             => 'POST',
            'callback'            => [$this, 'activateUpdatedPlugin'],
            'permission_callback' => [$this, 'verifyAuthToken'],
        ]);
    }

    /**
     * Verify authentication token
     * @return bool|\WP_Error
     */
    public function verifyAuthToken()
    {
        $headers    = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return false;
        }

        $token  = $matches[1];
        $userId = $this->validateAuthToken($token);
        if (!$userId) {
            return false;
        }

        wp_set_current_user($userId);
        return true;
    }

    /**
     * @param \WP_REST_Request $request
     * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function activateUpdatedPlugin(\WP_REST_Request $request)
    {
        $params     = $request->get_json_params();
        $pluginFile = $params['plugin_file'] ?? '';
        if (empty($pluginFile)) {
            return $this->sendResponse("Missing plugin file");
        }

        if (!$this->siteInfo->isStagingSite()) {
            return $this->sendResponse("You are not authorized to perform this action on Production site.");
        }

        if (is_plugin_active($pluginFile)) {
            return $this->sendResponse("Plugin already active");
        }

        // string $pluginFile, string $redirect = '', bool $network_wide = false, bool $silent = true (no need to run activation hook)
        $result = activate_plugin($pluginFile, '', false, true);
        if (is_wp_error($result)) {
            return $this->sendResponse(sprintf("Error activating the plugin: %s", $result->get_error_message()));
        }

        return $this->sendResponse("Plugin activated successfully");
    }

    /**
     * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function mayBeUpdatePlugins(\WP_REST_Request $request)
    {
        $params         = $request->get_json_params();
        $pluginFile     = $params['plugin_file'] ?? '';
        $wpPluginUpdate = $params['wp_plugin_update'] ?? false;
        $isNetworkSite  = $params['is_network_site'] ?? false;
        $latestVersion  = $params['latest_version'] ?? '';
        $oldVersion     = $params['old_version'] ?? '';

        if ($wpPluginUpdate && !$isNetworkSite) {
            return $this->refreshPluginUpdates();
        }

        if ($wpPluginUpdate && $isNetworkSite) {
            $sites = get_sites();
            foreach ($sites as $site) {
                switch_to_blog($site->blog_id);
                if (version_compare($this->getPluginVersion($pluginFile), $latestVersion, '=')) {
                    continue;
                }

                return $this->refreshPluginUpdates();
            }
        }

        if (empty($pluginFile)) {
            return $this->sendResponse("Missing plugin file");
        }

        if (!$this->siteInfo->isStagingSite()) {
            return $this->sendResponse("You are not authorized to perform this action on Production site.");
        }

        if (is_multisite() && $isNetworkSite) {
            return $this->updatePluginsMultisite($pluginFile, $latestVersion, $oldVersion);
        }

        return $this->updatePlugins($pluginFile);
    }

    /**
     * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function getOutdatedPlugins()
    {
        if (!$this->siteInfo->isStagingSite()) {
            return $this->sendResponse("This action is only allowed on staging sites.");
        }

        if (class_exists('WP_Plugin_Dependencies')) {
            \WP_Plugin_Dependencies::initialize();
        }

        wp_update_plugins();
        $updatePlugins   = get_site_transient('update_plugins');
        $outdatedPlugins = [];

        foreach ($updatePlugins->response as $pluginFile => $pluginData) {
            $pluginSlug    = dirname($pluginFile);
            $pluginDetails = get_plugin_data(WP_PLUGIN_DIR . '/' . $pluginFile);

            $outdatedPlugins[$pluginSlug] = [
                'name'            => $pluginDetails['Name'],
                'file'            => $pluginFile,
                'current_version' => $pluginDetails['Version'],
                'new_version'     => $pluginData->new_version,
                'slug'            => $pluginData->slug,
                'is_active'       => is_plugin_active($pluginFile),
                'dependencies'    => $this->getPluginDependencies($pluginFile),
            ];
        }

        $outdatedPlugins = $this->sortPluginsByDependencies($outdatedPlugins);

        return $this->sendResponse("Outdated Plugins", $outdatedPlugins);
    }

    /**
     * @param string $url
     * @param string $accessToken
     * @return array
     */
    public function getOutdatedPluginsFromStagingSite(string $url = '', string $accessToken = ''): array
    {
        if (empty($url)) {
            return [];
        }

        $apiResponse =  $this->getOutdatedPluginsRequest($url, $accessToken);
        if (is_wp_error($apiResponse)) {
            return [];
        }

        $outdatedPlugins = json_decode(wp_remote_retrieve_body($apiResponse), true);
        if (empty($outdatedPlugins)) {
            return [];
        }

        return $outdatedPlugins['result'] ?? [];
    }

    /**
     * @param string $url
     * @param bool $wpCheckUpdates
     * @param string $accessToken
     * @return mixed|string
     */
    public function checkAvailableUpdates(string $url, bool $wpCheckUpdates, string $accessToken = '')
    {
        $apiResponse = $this->sendPluginsUpdateRequest($url, '', $wpCheckUpdates, '', '', false, $accessToken);
        if (is_wp_error($apiResponse)) {
            return sprintf("Something went wrong while checking for available updates. Error: %s", $apiResponse->get_error_message());
        }

        $responseBody = json_decode(wp_remote_retrieve_body($apiResponse), true);
        if (empty($responseBody)) {
            return "Something went wrong while checking for available updates.";
        }

        return $responseBody['message'] ?? "Something went wrong while checking for available updates.";
    }

    /**
     * @param string $url
     * @param string $pluginFile
     * @param string $latestVersion
     * @param string $oldVersion
     * @param bool $isNetworkSite
     * @param string $accessToken
     * @return array
     */
    public function sendUpdateRequest(string $url, string $pluginFile, string $latestVersion, string $oldVersion, bool $isNetworkSite = false, string $accessToken = ''): array
    {
        $apiResponse = $this->sendPluginsUpdateRequest($url, $pluginFile, false, $latestVersion, $oldVersion, $isNetworkSite, $accessToken);
        $errorMessage = esc_html__("Error updating plugin. Please try updating manually", "wp-staging");

        if (is_wp_error($apiResponse)) {
            $errorMessage = sprintf(esc_html__("Error updating plugin: %s", "wp-staging"), $apiResponse->get_error_message());
            return $this->formatResponse($pluginFile, '0', '0', 'error', $errorMessage);
        }

        $responseBody = json_decode(wp_remote_retrieve_body($apiResponse), true);
        if (empty($responseBody)) {
            return $this->formatResponse($pluginFile, '0', '0', 'error', $errorMessage);
        }

        return $responseBody['result'] ?? [];
    }

    /**
     * @param string $url
     * @param string $pluginFile
     * @param string $accessToken
     * @return string
     */
    public function activatePlugin(string $url, string $pluginFile, string $accessToken = ''): string
    {
        $apiResponse = $this->sendPluginActivationRequest($url, $pluginFile, $accessToken);
        if (is_wp_error($apiResponse)) {
            return sprintf("Error activating plugin: %s", $apiResponse->get_error_message());
        }

        $responseBody = json_decode(wp_remote_retrieve_body($apiResponse), true);
        if (empty($responseBody)) {
            return "Error activating plugin";
        }

        return $responseBody['message'] ?? '';
    }

    /**
     * @param array $response
     * @param string $stagingURL
     * @param array $outdatedPlugins
     * @return void
     */
    public function sendNotification(array $response, string $stagingURL, array $outdatedPlugins)
    {
        $subject = esc_html__("WP Staging Automation Update Report", "wp-staging");
        $message = $this->prepareNotificationBody($response, $stagingURL, $outdatedPlugins);
        if (get_option(Notifications::OPTION_SEND_EMAIL_AS_HTML, false) === 'true') {
            $htmlMessage = $this->prepareHTMLNotificationBody($response, $stagingURL, $outdatedPlugins);
            $this->sendEmailNotification($subject, $htmlMessage);
        } else {
            $this->sendEmailNotification($subject, $message);
        }

        if (!$this->sendSlackNotification($subject, $message)) {
            $this->sendSlackNotification($subject, $this->prepareNotificationBody($response, $stagingURL, $outdatedPlugins, true));
        }
    }

    /**
     * Clean up the existing users from the staging site
     * @return void
     */
    public function deleteAuthToken($cloneID)
    {
        $existingClones = get_option(Sites::STAGING_SITES_OPTION, []);
        if (empty($existingClones) || !is_array($existingClones)) {
            return;
        }

        if (!isset($existingClones[$cloneID])) {
            return;
        }

        $currentClone   = $existingClones[$cloneID];
        // Set up the database connection for the current clone
        /** @var SourceDatabase $sourceDatabase */
        $sourceDatabase = WPStaging::make(SourceDatabase::class);
        $sourceDatabase->setOptions((object)$currentClone);
        $cloneDB        = $sourceDatabase->getDatabase();
        $currentUser    = wp_get_current_user();
        $userId         = $currentUser->ID;
        $usersMetaTable = $currentClone['prefix'] . 'usermeta';

        $query = $cloneDB->prepare(
            "SELECT COUNT(*) FROM $usersMetaTable WHERE user_id = %d AND meta_key = %s",
            $userId,
            'wpstg_auth_token'
        );
        $tokenExists = $cloneDB->get_var($query);

        if ($tokenExists > 0) {
            $deleteQuery = $cloneDB->prepare(
                "DELETE FROM $usersMetaTable WHERE user_id = %d AND meta_key = %s",
                $userId,
                'wpstg_auth_token'
            );
            $cloneDB->query($deleteQuery);
        }
    }

    /**
     * Normalize array options to object with expected property names
     * Maps StagingSiteDto array keys to the property names expected by this class
     *
     * @param array $options
     * @return object
     */
    private function normalizeOptionsFromArray(array $options)
    {
        return (object)[
            'isAutoUpdatePlugins' => $options['isAutoUpdatePlugins'] ?? false,
            'cloneDirectoryName'  => empty($options['directoryName']) ? ($options['cloneName'] ?? '') : $options['directoryName'],
            'cloneHostname'       => $options['url'] ?? '',
            'clone'               => $options['cloneId'] ?? '',
            'networkClone'        => $options['networkClone'] ?? false,
        ];
    }

    /**
     * @param $options
     * @return bool
     */
    private function isNetworkClone($options)
    {
        if (!is_multisite() || empty($options->networkClone)) {
            return false;
        }

        return WPStagingPro::isValidLicense();
    }

    /**
     * Generate an access token for the user
     * @param $options
     * @return string
     */
    private function generateAccessToken($options): string
    {
        if (empty($options->clone)) {
            return '';
        }

        $existingClones = get_option(Sites::STAGING_SITES_OPTION, []);
        if (empty($existingClones) || !is_array($existingClones)) {
            return '';
        }

        if (!isset($existingClones[$options->clone])) {
            return '';
        }

        $currentClone = $existingClones[$options->clone];

        // Set up the database connection for the current clone
        /** @var SourceDatabase $sourceDatabase */
        $sourceDatabase = WPStaging::make(SourceDatabase::class);
        $sourceDatabase->setOptions((object)$currentClone);
        $cloneDB     = $sourceDatabase->getDatabase();
        $currentUser = wp_get_current_user();
        $userId      = $currentUser->ID;
        $token       = wp_generate_password(32, false);
        $expires     = time() + DAY_IN_SECONDS;
        $authToken   = json_encode(['token' => $token, 'expires' => $expires]);

        $usersMetaTable = $currentClone['prefix'] . 'usermeta';
        $query          = $cloneDB->prepare("SELECT COUNT(*) FROM $usersMetaTable WHERE user_id = %d AND meta_key = %s", $userId, 'wpstg_auth_token');
        $existingToken  = $cloneDB->get_var($query);

        if ($existingToken > 0) {
            $query = $cloneDB->prepare("UPDATE $usersMetaTable SET meta_value = %s WHERE user_id = %d AND meta_key = %s", $authToken, $userId, 'wpstg_auth_token');
        } else {
            $query = $cloneDB->prepare("INSERT INTO $usersMetaTable (user_id, meta_key, meta_value) VALUES (%d, %s, %s)", $userId, 'wpstg_auth_token', $authToken);
        }

        $cloneDB->query($query);

        return $token;
    }

    /**
     * Validate authentication token
     * @param string $token
     * @return int|false
     */
    private function validateAuthToken(string $token)
    {
        $usersMetaTable = $this->database->getPrefix() . 'usermeta';
        $query          = "SELECT user_id, meta_value FROM `$usersMetaTable` WHERE meta_key = 'wpstg_auth_token'";
        $result         = $this->database->getClient()->query($query);

        if (!$result || $result->num_rows === 0) {
            return false;
        }

        while ($user = $result->fetch_assoc()) {
            $tokenData = json_decode($user['meta_value'], true);

            if (!is_array($tokenData) || empty($tokenData['token']) || empty($tokenData['expires'])) {
                continue;
            }

            if (hash_equals($tokenData['token'], $token) && time() < (int)$tokenData['expires']) {
                return (int) $user['user_id'];
            }
        }

        return false;
    }
}
