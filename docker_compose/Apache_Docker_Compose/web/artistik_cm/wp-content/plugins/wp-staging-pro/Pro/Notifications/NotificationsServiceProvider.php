<?php

namespace WPStaging\Pro\Notifications;

use WPStaging\Framework\DI\ServiceProvider;
use WPStaging\Notifications\Notifications;
use WPStaging\Notifications\NotificationsProvider;
use WPStaging\Pro\Notifications\NotificationsProvider as ProNotificationsProvider;

class NotificationsServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    protected function registerClasses()
    {
        $container = $this->container;
        $this->container->when(Notifications::class)
            ->needs(NotificationsProvider::class)
            ->give(function () use (&$container) {
                return $container->make(ProNotificationsProvider::class);
            });
    }
}
