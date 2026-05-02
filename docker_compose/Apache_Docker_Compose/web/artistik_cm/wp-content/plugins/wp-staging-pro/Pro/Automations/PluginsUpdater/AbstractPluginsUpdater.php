<?php

namespace WPStaging\Pro\Automations\PluginsUpdater;

use WPStaging\Backup\BackupScheduler;
use WPStaging\Framework\Adapter\DatabaseInterface;
use WPStaging\Framework\Mails\MailSender;
use WPStaging\Framework\SiteInfo;
use WPStaging\Framework\Traits\RestRequestTrait;
use WPStaging\Notifications\Notifications;

abstract class AbstractPluginsUpdater
{
    use RestRequestTrait;

    /**
     * @var Notifications
     */
    protected $notifications;

    /**
     * @var SiteInfo
     */
    protected $siteInfo;

    /**
     * @var MailSender
     */
    protected $mailSender;

    /**
     * @var DatabaseInterface
     */
    protected $database;

    /**
     * @param Notifications $notifications
     * @param SiteInfo $siteInfo
     * @param MailSender $mailSender
     * @param DatabaseInterface $database
     */
    public function __construct(Notifications $notifications, SiteInfo $siteInfo, MailSender $mailSender, DatabaseInterface $database)
    {
        $this->notifications = $notifications;
        $this->siteInfo      = $siteInfo;
        $this->mailSender    = $mailSender;
        $this->database      = $database;
    }

    /**
     * @return void
     */
    abstract public function registerRestRoutes();

    /**
     * @param string $url
     * @param string $pluginFile
     * @param bool $wpCheckUpdates
     * @param string $latestVersion
     * @param string $oldVersion
     * @param bool $isNetworkSite
     * @param string $accessToken
     * @return array|\WP_Error
     */
    protected function sendPluginsUpdateRequest(string $url, string $pluginFile, bool $wpCheckUpdates = false, string $latestVersion = '', string $oldVersion = '', bool $isNetworkSite = false, string $accessToken = '')
    {
        $body = [
            'plugin_file'      => $pluginFile,
            'wp_plugin_update' => $wpCheckUpdates,
            'latest_version'   => $latestVersion,
            'old_version'      => $oldVersion,
            'is_network_site'  => $isNetworkSite,
        ];
        return $this->sendRestRequest($url, 'wpstg_plugins_update', $body, $accessToken);
    }

    /**
     * @param string $url
     * @param string $accessToken
     * @return array|\WP_Error
     */
    protected function getOutdatedPluginsRequest(string $url, string $accessToken = '')
    {
        return $this->sendRestRequest($url, 'wpstg_get_outdated_plugins', [], $accessToken);
    }

    /**
     * @param string $url
     * @param string $pluginFile
     * @param string $accessToken
     * @return array|\WP_Error
     */
    protected function sendPluginActivationRequest(string $url, string $pluginFile, string $accessToken = '')
    {
        $body = [
            'plugin_file' => $pluginFile,
        ];
        return $this->sendRestRequest($url, 'wpstg_activate_updated_plugins', $body, $accessToken);
    }

    /**
     * @param string $title
     * @param string $message
     * @return bool|\WP_Error|null
     */
    protected function sendEmailNotification(string $title, string $message)
    {
        $reportEmail = get_option(Notifications::OPTION_BACKUP_SCHEDULE_REPORT_EMAIL);
        if (!filter_var($reportEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $this->mailSender->setAddFooter(true);
        return $this->mailSender->sendRequestForEmailNotification($title, $message);
    }

    /**
     * @param string $title
     * @param string $message
     * @return bool
     */
    protected function sendSlackNotification(string $title, string $message): bool
    {
        if (get_option(BackupScheduler::OPTION_BACKUP_SCHEDULE_SLACK_ERROR_REPORT) !== 'true') {
            return false;
        }

        $webhook = get_option(BackupScheduler::OPTION_BACKUP_SCHEDULE_REPORT_SLACK_WEBHOOK);
        if (!filter_var($webhook, FILTER_VALIDATE_URL)) {
            return false;
        }

        return $this->notifications->sendSlack($webhook, $title, $message);
    }

    /**
     * @param string $pluginFile
     * @return string
     */
    protected function getPluginVersion(string $pluginFile = ""): string
    {
        if (empty($pluginFile)) {
            return "";
        }

        $allPlugins = get_plugins();
        return empty($allPlugins[$pluginFile]['Version']) ? 'unknown' : $allPlugins[$pluginFile]['Version'];
    }

    /**
     * @param string $message
     * @param array $updatesReport
     * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    protected function sendResponse(string $message, array $updatesReport = [])
    {
        return rest_ensure_response([
            'status'  => 'success',
            'message' => $message,
            'result'  => $updatesReport,
        ]);
    }
}
