<?php

namespace WPStaging\Pro\Push;

use WPStaging\Framework\DI\ServiceProvider;
use WPStaging\Framework\Job\Dto\JobDataDto;
use WPStaging\Framework\Permalinks\PermalinksPurge;
use WPStaging\Pro\Push\Ajax\CancelPush;
use WPStaging\Pro\Push\Ajax\PreparePush;
use WPStaging\Pro\Push\Ajax\Push;
use WPStaging\Pro\Push\Ajax\Setup;
use WPStaging\Pro\Push\Dto\StagingSitePushDataDto;
use WPStaging\Pro\Push\Jobs\StagingSitePush;

class PushServiceProvider extends ServiceProvider
{
    protected function registerClasses()
    {
        $this->container->when(StagingSitePush::class)
                        ->needs(JobDataDto::class)
                        ->give(StagingSitePushDataDto::class);
    }

    protected function addHooks()
    {
        add_action(StagingSitePush::ACTION_PUSHING_COMPLETE, $this->container->callback(PermalinksPurge::class, "executeAfterPushing")); // phpcs:ignore WPStaging.Security.FirstArgNotAString

        add_action("wp_ajax_wpstg_cancel_push_processing", $this->container->callback(CancelPush::class, "ajaxCancelPush")); // phpcs:ignore WPStaging.Security.AuthorizationChecked

        if (!defined('WPSTG_NEW_STAGING') || !WPSTG_NEW_STAGING) {
            return;
        }

        add_action('wp_ajax_wpstg--staging-site--push-setup', $this->container->callback(Setup::class, 'ajaxSetup')); // phpcs:ignore WPStaging.Security.AuthorizationChecked

        add_action('wp_ajax_wpstg--staging-site--prepare-push', $this->container->callback(PreparePush::class, 'ajaxPrepare')); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('wp_ajax_wpstg--staging-site--push', $this->container->callback(Push::class, 'render')); // phpcs:ignore WPStaging.Security.AuthorizationChecked
    }
}
