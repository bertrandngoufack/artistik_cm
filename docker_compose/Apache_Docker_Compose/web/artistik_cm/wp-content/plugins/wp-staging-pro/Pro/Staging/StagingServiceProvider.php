<?php

namespace WPStaging\Pro\Staging;

use WPStaging\Backend\Modules\Jobs\Cloning;
use WPStaging\Backend\Pro\Modules\Jobs\CloningPro;
use WPStaging\Core\CloningJobProvider;
use WPStaging\Core\Cron\Cron;
use WPStaging\Framework\DI\ServiceProvider;
use WPStaging\Framework\Job\Dto\JobDataDto;
use WPStaging\Pro\Staging\Ajax\Create;
use WPStaging\Pro\Staging\Ajax\Create\PrepareCreate;
use WPStaging\Pro\Staging\Ajax\Edit;
use WPStaging\Pro\Staging\Ajax\ExternalDatabase;
use WPStaging\Pro\Staging\Ajax\MagicLoginLink;
use WPStaging\Pro\Staging\Ajax\OneTimeLogin;
use WPStaging\Pro\Staging\Ajax\Reset;
use WPStaging\Pro\Staging\Ajax\Reset\PrepareReset;
use WPStaging\Pro\Staging\Ajax\Update;
use WPStaging\Pro\Staging\Ajax\Update\PrepareUpdate;
use WPStaging\Pro\Staging\Ajax\UserAccountSynchronizer;
use WPStaging\Pro\Staging\EmailReminder;
use WPStaging\Pro\Staging\Jobs\StagingSiteCreate;
use WPStaging\Pro\Staging\Jobs\StagingSiteReset;
use WPStaging\Pro\Staging\Jobs\StagingSiteUpdate;
use WPStaging\Pro\Staging\Service\Database\TableCreateService as ProTableCreateService;
use WPStaging\Pro\Staging\Service\StagingSetup;
use WPStaging\Staging\Ajax\Setup;
use WPStaging\Staging\Dto\Job\StagingSiteJobsDataDto;
use WPStaging\Staging\Service\AbstractStagingSetup;
use WPStaging\Staging\Service\Database\TableCreateService;
use WPStaging\Staging\Tasks\StagingSite\Database\CreateDatabaseTablesTask;

/**
 * Used to register classes and hooks for the staging/cloning related services.
 */
class StagingServiceProvider extends ServiceProvider
{
    protected function registerClasses()
    {
        $this->container->make(UserAccountSynchronizer::class);
        $this->container->make(ExternalDatabase::class);

        $this->container->when(CloningJobProvider::class)
                        ->needs(Cloning::class)
                        ->give(CloningPro::class);

        $this->container->when(Setup::class)
                        ->needs(AbstractStagingSetup::class)
                        ->give(StagingSetup::class);

        $this->container->when(StagingSiteCreate::class)
                        ->needs(JobDataDto::class)
                        ->give(StagingSiteJobsDataDto::class);

        $this->container->when(StagingSiteUpdate::class)
                        ->needs(JobDataDto::class)
                        ->give(StagingSiteJobsDataDto::class);

        $this->container->when(StagingSiteReset::class)
                        ->needs(JobDataDto::class)
                        ->give(StagingSiteJobsDataDto::class);

        $this->container->when(CreateDatabaseTablesTask::class)
                        ->needs(TableCreateService::class)
                        ->give(ProTableCreateService::class);
    }

    protected function addHooks()
    {
        add_action("wp_ajax_wpstg_sync_account", $this->container->callback(UserAccountSynchronizer::class, "ajaxSyncAccount")); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action("wp_ajax_wpstg_database_connect", $this->container->callback(ExternalDatabase::class, "ajaxDatabaseConnect")); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action("wp_ajax_wpstg_database_verification", $this->container->callback(ExternalDatabase::class, "ajaxDatabaseVerification")); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action(Cron::ACTION_DAILY_EVENT, $this->container->callback(EmailReminder::class, "maybeSendEmailReminder"));
        add_action('admin_init', $this->container->callback(EmailReminder::class, "sendStagingEmailNotification"));
        add_action("admin_post_wpstg-disable-staging-reminder", $this->container->callback(EmailReminder::class, "disableRemindEmailPublicEndpoint")); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action("wp_ajax_wpstg_database_verify_grants", $this->container->callback(ExternalDatabase::class, "ajaxVerifyDatabaseGrants")); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        $this->enqueueStagingAjaxListeners();
    }

    protected function enqueueStagingAjaxListeners()
    {
        add_action('wp_ajax_wpstg--staging-site--edit-modal', $this->container->callback(Edit::class, 'ajaxModalContent')); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('wp_ajax_wpstg--staging-site--edit-save', $this->container->callback(Edit::class, 'ajaxSave')); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('wp_ajax_wpstg--staging-site--one-time-login_url', $this->container->callback(OneTimeLogin::class, 'ajaxGenerateStagingLoginUrl'), 10, 1); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action("wp_ajax_wpstg_render_login_link_user_interface", $this->container->callback(MagicLoginLink::class, 'ajaxLoginLinkUserInterface')); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action("wp_ajax_wpstg_save_generated_link_data", $this->container->callback(MagicLoginLink::class, 'ajaxSaveGeneratedLinkData')); // phpcs:ignore WPStaging.Security.AuthorizationChecked

        if (!defined('WPSTG_NEW_STAGING') || !WPSTG_NEW_STAGING) {
            return;
        }

        add_action('wp_ajax_wpstg--staging-site--prepare-create', $this->container->callback(PrepareCreate::class, 'ajaxPrepare')); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('wp_ajax_wpstg--staging-site--create', $this->container->callback(Create::class, 'render')); // phpcs:ignore WPStaging.Security.AuthorizationChecked

        add_action('wp_ajax_wpstg--staging-site--prepare-update', $this->container->callback(PrepareUpdate::class, 'ajaxPrepare')); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('wp_ajax_wpstg--staging-site--update', $this->container->callback(Update::class, 'render')); // phpcs:ignore WPStaging.Security.AuthorizationChecked

        add_action('wp_ajax_wpstg--staging-site--prepare-reset', $this->container->callback(PrepareReset::class, 'ajaxPrepare')); // phpcs:ignore WPStaging.Security.AuthorizationChecked
        add_action('wp_ajax_wpstg--staging-site--reset', $this->container->callback(Reset::class, 'render')); // phpcs:ignore WPStaging.Security.AuthorizationChecked
    }
}
