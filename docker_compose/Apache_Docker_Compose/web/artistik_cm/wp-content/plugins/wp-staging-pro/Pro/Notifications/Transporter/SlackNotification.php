<?php

namespace WPStaging\Pro\Notifications\Transporter;

use WPStaging\Core\WPStaging;
use WPStaging\Notifications\Interfaces\NotificationsInterface;

use function WPStaging\functions\debug_log;

class SlackNotification implements NotificationsInterface
{
    /**
     * @var string
     */
    private $webhook = '';

    /**
     * @var string
     */
    private $title = '';

    /**
     * @var bool
     */
    private $isAddFooterMessage = true;

    /**
     * @param string $title
     * @return self
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param string $webhook
     * @return self
     */
    public function setWebhook(string $webhook)
    {
        $this->webhook = $webhook;
        return $this;
    }

    /**
     * @param bool $isAddFooterMessage
     * @return self
     */
    public function setIsAddFooterMessage(bool $isAddFooterMessage = false)
    {
        $this->isAddFooterMessage = $isAddFooterMessage;
        return $this;
    }

    /**
     * @return void
     */
    public function reset()
    {
        $this->webhook            = '';
        $this->title              = '';
        $this->isAddFooterMessage = true;
    }

    /**
     * @param string $webhook
     * @return bool
     */
    private function isValidWebhook(string $webhook): bool
    {
        if (empty($webhook)) {
            return false;
        }

        $parts = wp_parse_url($webhook);
        if (empty($parts['host'])) {
            return false;
        }

        $hostname = $parts['host'];
        if (!strpos($hostname, '.slack.com') && !strpos($hostname, 'wp-staging.com') && substr($hostname, -6) !== '.local') {
            return false;
        }

        return true;
    }

    /**
     * @param string $message
     * @return string
     */
    private function addFooterMessage(string $message): string
    {
        if (empty($message) || !$this->isAddFooterMessage) {
            return $message;
        }

        $message .= "\r\n\r\n" . "--";
        $message .= "\r\n" . sprintf(esc_html__('This message was sent by the WP Staging plugin from the website *%s*.', 'wp-staging'), get_site_url());
        $message .= "\r\n" . esc_html__('For error details, you may download all log files at *System Info -> Download All Log Files*.', 'wp-staging');
        return $message;
    }

    /**
     * @param string $message
     * @return bool
     */
    public function send(string $message): bool
    {
        if (!WPStaging::isPro()) {
            return false;
        }

        if (!$this->isValidWebhook($this->webhook)) {
            debug_log(sprintf('[SlackNotification] Invalid Webhook: %s', $this->webhook), 'info', false);
            return false;
        }

        if (empty($message)) {
            return false;
        }

        $message = $this->addFooterMessage($message);

        $data = [
            'blocks' => [
                [
                    'type' => 'header',
                    'text' => [
                        'type' => 'plain_text',
                        'text' => esc_html($this->title),
                    ],
                ],
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => esc_html($message),
                    ],
                ],
            ],
        ];

        $args = [
            'headers'   => ['Content-Type' => 'application/json; charset=utf-8'],
            'body'      => json_encode($data),
            'sslverify' => false,
            'timeout'   => 10,
        ];

        $response = wp_remote_post($this->webhook, $args);
        if (is_wp_error($response)) {
            debug_log(sprintf('[SlackNotification] %s', $response->get_error_message()), 'info', false);
            return false;
        }

        $responseCode = wp_remote_retrieve_response_code($response);

        return (int)$responseCode === 200;
    }
}
