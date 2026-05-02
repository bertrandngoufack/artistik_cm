<?php

namespace WPStaging\Pro\RemoteSync;

use WPStaging\Backend\Administrator;
use WPStaging\Backup\Task\Tasks\JobRestore\RenameDatabaseTask;
use WPStaging\Framework\BackgroundProcessing\Job\PrepareJob;
use WPStaging\Framework\DI\ServiceProvider;
use WPStaging\Framework\Facades\Hooks;
use WPStaging\Framework\Job\Ajax\PrepareCancel;
use WPStaging\Framework\Job\Dto\JobDataDto;
use WPStaging\Framework\Job\Task\AbstractTask;
use WPStaging\Framework\Rest\Rest;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Pro\RemoteSync\Ajax\RemoteSync as AjaxRemoteSync;
use WPStaging\Pro\RemoteSync\Ajax\Settings as AjaxSettings;
use WPStaging\Pro\RemoteSync\Ajax\Status as AjaxStatus;
use WPStaging\Pro\RemoteSync\Dto\Job\PullInitiatorDataDto;
use WPStaging\Pro\RemoteSync\Jobs\PullInitiator;
use WPStaging\Pro\RemoteSync\Settings\Settings;
use WPStaging\Pro\Template\ProTemplateIncluder;

/**
 * Used to register classes and hooks for the remote sync feature.
 */
class RemoteSyncServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function registerRestRoutes()
    {
        register_rest_route(Rest::WPSTG_ROUTE_NAMESPACE_V1, '/authenticate', [
            'methods'             => 'POST',
            'callback'            => $this->container->callback(RemoteSyncRestEndpoints::class, 'restAuthenticate'),
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(Rest::WPSTG_ROUTE_NAMESPACE_V1, '/two_way_sync', [
            'methods'             => 'POST',
            'callback'            => $this->container->callback(RemoteSyncRestEndpoints::class, 'restTwoWaySync'),
            'permission_callback' => $this->container->callback(RemoteSyncRestEndpoints::class, 'verifyRequest'),
        ]);

        register_rest_route(Rest::WPSTG_ROUTE_NAMESPACE_V1, '/sync_status', [
            'methods'             => 'POST',
            'callback'            => $this->container->callback(RemoteSyncRestEndpoints::class, 'restSyncStatus'),
            'permission_callback' => $this->container->callback(RemoteSyncRestEndpoints::class, 'verifyRequest'),
        ]);

        register_rest_route(Rest::WPSTG_ROUTE_NAMESPACE_V1, '/initiate_sync', [
            'methods'             => 'POST',
            'callback'            => $this->container->callback(RemoteSyncRestEndpoints::class, 'restInitiateSync'),
            'permission_callback' => $this->container->callback(RemoteSyncRestEndpoints::class, 'verifyRequest'),
        ]);

        register_rest_route(Rest::WPSTG_ROUTE_NAMESPACE_V1, '/prepare_pull', [
            'methods'             => 'POST',
            'callback'            => $this->container->callback(RemoteSyncRestEndpoints::class, 'restPreparePull'),
            'permission_callback' => $this->container->callback(RemoteSyncRestEndpoints::class, 'verifyRequest'),
        ]);

        register_rest_route(Rest::WPSTG_ROUTE_NAMESPACE_V1, '/remote_events', [
            'methods'             => 'POST',
            'callback'            => $this->container->callback(RemoteSyncRestEndpoints::class, 'restRemoteEvents'),
            'permission_callback' => $this->container->callback(RemoteSyncRestEndpoints::class, 'verifyRequest'),
        ]);

        register_rest_route(Rest::WPSTG_ROUTE_NAMESPACE_V1, '/start_pull', [
            'methods'             => 'POST',
            'callback'            => $this->container->callback(RemoteSyncRestEndpoints::class, 'restStartPull'),
            'permission_callback' => $this->container->callback(RemoteSyncRestEndpoints::class, 'verifyRequest'),
        ]);

        register_rest_route(Rest::WPSTG_ROUTE_NAMESPACE_V1, '/remote_sync_cancelled', [
            'methods'             => 'POST',
            'callback'            => $this->container->callback(RemoteSyncRestEndpoints::class, 'restSyncCancelled'),
            'permission_callback' => $this->container->callback(RemoteSyncRestEndpoints::class, 'verifyRequest'),
        ]);

        register_rest_route(Rest::WPSTG_ROUTE_NAMESPACE_V1, '/finish_download', [
            'methods'             => 'POST',
            'callback'            => $this->container->callback(RemoteSyncRestEndpoints::class, 'restFinishDownload'),
            'permission_callback' => $this->container->callback(RemoteSyncRestEndpoints::class, 'verifyRequest'),
        ]);

        register_rest_route(Rest::WPSTG_ROUTE_NAMESPACE_V1, '/finish_pull', [
            'methods'             => 'POST',
            'callback'            => $this->container->callback(RemoteSyncRestEndpoints::class, 'restFinishPull'),
            'permission_callback' => $this->container->callback(RemoteSyncRestEndpoints::class, 'verifyRequest'),
        ]);

        register_rest_route(Rest::WPSTG_ROUTE_NAMESPACE_V1, '/remote_sync_failed', [
            'methods'             => 'POST',
            'callback'            => $this->container->callback(RemoteSyncRestEndpoints::class, 'restSyncFailed'),
            'permission_callback' => $this->container->callback(RemoteSyncRestEndpoints::class, 'verifyRequest'),
        ]);
    }

    /**
     * @return void
     */
    protected function registerClasses()
    {
        $this->container->singleton(RemoteSyncRestEndpoints::class);
        $this->container->singleton(RemoteEvents::class);

        $this->container->when(PullInitiator::class)
                ->needs(JobDataDto::class)
                ->give(PullInitiatorDataDto::class);
    }

    /**
     * @return void
     */
    protected function addHooks()
    {
        add_action("wp_ajax_wpstg_regenerate_connection_key", $this->container->callback(AjaxSettings::class, "ajaxRegenerateConnectionKey")); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action("wp_ajax_wpstg_unprotect_remote_sync", $this->container->callback(AjaxSettings::class, "ajaxUnprotect")); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action("wp_ajax_wpstg_protect_remote_sync", $this->container->callback(AjaxSettings::class, "ajaxProtect")); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action("wp_ajax_wpstg_toggle_remote_sync_enabled", $this->container->callback(AjaxSettings::class, "ajaxToggleRemoteSyncEnabled")); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action("wp_ajax_wpstg_authenticate_sync", $this->container->callback(AjaxRemoteSync::class, "ajaxAuthenticate")); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action("wp_ajax_wpstg_is_two_way_sync", $this->container->callback(AjaxRemoteSync::class, "ajaxIsTwoWaySync")); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action("wp_ajax_wpstg_start_pull", $this->container->callback(AjaxRemoteSync::class, "ajaxStartPull")); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action("wp_ajax_wpstg_check_already_sync", $this->container->callback(AjaxRemoteSync::class, "ajaxCheckAlreadySync")); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('rest_api_init', [$this, 'registerRestRoutes']);
        add_action(TemplateEngine::HOOK_RENDER_PRO_TEMPLATES, $this->container->callback(ProTemplateIncluder::class, 'addRemoteSyncModalTemplates'), 10);
        add_filter(Administrator::FILTER_MAIN_SETTING_TABS, $this->container->callback(Settings::class, 'addRemoteSyncSettingsTab'), 10, 1);

        Hooks::registerInternalHook(AbstractTask::ACTION_TASK_RESPONSE, $this->container->callback(RemoteEvents::class, 'maybeSendEventsOnTaskResponse'));

        Hooks::registerInternalHook(PrepareJob::ACTION_JOB_FAILURE, $this->container->callback(RemoteEvents::class, 'maybeHandleFailure'));

        Hooks::registerInternalHook(PrepareCancel::ACTION_JOB_CANCEL, $this->container->callback(RemoteEvents::class, 'maybeHandleCancel'));

        Hooks::registerInternalHook(RenameDatabaseTask::HOOK_KEEP_OPTIONS, function ($optionsToKeep) {
            $optionsToKeep[] = [
                'name'     => ConnectionKey::OPTION_REMOTE_SYNC_API_TOKEN,
                'value'    => get_option(ConnectionKey::OPTION_REMOTE_SYNC_API_TOKEN),
                'autoload' => false,
            ];

            $optionsToKeep[] = [
                'name'     => ConnectionKey::OPTION_REMOTE_SYNC_PASSWORD,
                'value'    => get_option(ConnectionKey::OPTION_REMOTE_SYNC_PASSWORD),
                'autoload' => false,
            ];

            $optionsToKeep[] = [
                'name'     => ConnectionKey::OPTION_REMOTE_SYNC_ENABLED,
                'value'    => get_option(ConnectionKey::OPTION_REMOTE_SYNC_ENABLED, '0'),
                'autoload' => false,
            ];

            // We need to keep the transient session in the database, so we can correctly perform sync process last step.
            $optionsToKeep[] = [
                'name'     => '_transient_' . SyncSession::TRANSIENT_SESSION,
                'value'    => get_option('_transient_' . SyncSession::TRANSIENT_SESSION),
                'autoload' => false,
            ];

            $optionsToKeep[] = [
                'name'     => '_transient_timeout_' . SyncSession::TRANSIENT_SESSION,
                'value'    => get_option('_transient_timeout_' . SyncSession::TRANSIENT_SESSION),
                'autoload' => false,
            ];

            return $optionsToKeep;
        });
    }
}
