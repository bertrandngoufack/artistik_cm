<?php

namespace WPStaging\Pro\Notifications;

use WPStaging\Notifications\NotificationsProvider as BasicNotificationsProvider;
use WPStaging\Notifications\Transporter\EmailNotification;
use WPStaging\Pro\Notifications\Transporter\SlackNotification;

class NotificationsProvider extends BasicNotificationsProvider
{
    /**
     * @return array
     */
    public function getProviders(): array
    {
        return [
            EmailNotification::class,
            SlackNotification::class,
        ];
    }
}
