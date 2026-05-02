<?php

namespace WPStaging\Pro;

use WPStaging\Backup\Service\ZlibCompressor;
use WPStaging\Framework\DI\Container;
use WPStaging\Framework\DI\ServiceProvider;
use WPStaging\Framework\Facades\Hooks;
use WPStaging\Framework\Job\Dto\JobDataDto;
use WPStaging\Framework\Notices\Notices as NoticesBase;
use WPStaging\Framework\SiteInfo;
use WPStaging\Pro\Backup\BackupServiceProvider;
use WPStaging\Pro\License\LicenseServiceProvider;
use WPStaging\Pro\Push\PushServiceProvider;
use WPStaging\Pro\Notices\Notices;
use WPStaging\Pro\Staging\StagingSiteServiceProvider;
use WPStaging\Pro\Template\TemplateServiceProvider;
use WPStaging\Pro\Auth\AuthServiceProvider;
use WPStaging\Pro\WpCli\WpCliServiceProvider;
use WPStaging\Pro\Activate\PluginActivationServiceProvider;
use WPStaging\Pro\Staging\StagingServiceProvider;
use WPStaging\Pro\Notifications\NotificationsServiceProvider;
use WPStaging\Pro\Automations\PluginsUpdater\Dto\JobPluginsUpdaterDataDto;
use WPStaging\Pro\Automations\PluginsUpdater\Job\JobPluginsUpdater;
use WPStaging\Pro\Automations\PluginsUpdater\WordPressPluginsUpdater;
use WPStaging\Pro\RemoteSync\RemoteSyncServiceProvider;
use WPStaging\Staging\Jobs\StagingSiteCreate;

class BootstrapServiceProvider extends ServiceProvider
{
    /** @var Container $container */
    protected $container;

    /** @var SiteInfo */
    protected $siteInfo;

    /**
     * ServiceProvider constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container, SiteInfo $siteInfo)
    {
        parent::__construct($container);
        $this->siteInfo = $siteInfo;
    }

    public function canUseCompression()
    {
        return $this->siteInfo->isLocal() || WPStagingPro::isValidLicense();
    }

    /** @return void */
    protected function addHooks()
    {
        $this->loadFilters();
        add_action(NoticesBase::ACTION_PRO_NOTICES, $this->container->callback(Notices::class, 'renderNotices')); // phpcs:ignore WPStaging.Security.FirstArgNotAString
        Hooks::registerInternalHook(ZlibCompressor::HOOK_CAN_USE_COMPRESSION, [$this, 'canUseCompression']);
        add_action('rest_api_init', $this->container->callback(WordPressPluginsUpdater::class, 'registerRestRoutes'));
        add_action(StagingSiteCreate::ACTION_CLONING_COMPLETE, $this->container->callback(WordPressPluginsUpdater::class, 'autoUpdatePlugins'));
    }

    /** @return void */
    protected function registerClasses()
    {
        $this->container->register(BackupServiceProvider::class);

        if (defined('WPSTG_REQUIRE_FREE') && WPSTG_REQUIRE_FREE) {
            $this->container->register(PluginActivationServiceProvider::class);

            if (!wpstgIsFreeActiveInNetworkOrCurrentSite()) {
                return;
            }
        }

        $this->container->register(TemplateServiceProvider::class);
        $this->container->register(LicenseServiceProvider::class);
        $this->container->register(AuthServiceProvider::class);

        if ($this->container->get(SiteInfo::class)->isStagingSite()) {
            $this->container->register(StagingSiteServiceProvider::class);
        }

        $this->container->register(StagingServiceProvider::class);
        $this->container->register(RemoteSyncServiceProvider::class);

        $this->container->register(PushServiceProvider::class);
        $this->container->register(NotificationsServiceProvider::class);

        // Feature providers.
        $this->container->register(WpCliServiceProvider::class);

        $this->container->when(JobPluginsUpdater::class)
            ->needs(JobDataDto::class)
            ->give(JobPluginsUpdaterDataDto::class);
    }

    /**
     * @see dev/docs/installer/README.md
     * @return void
     */
    private function loadFilters()
    {
        add_filter(implode('', array_map(function ($integer) {
            return chr($integer);
        }, array_reverse([115, 103, 114, 97, 95, 116, 115, 101, 117, 113, 101, 114, 95, 112, 116, 116, 104]))), function ($data, $input) {
            if (
                strpos($input, implode('', array_map(function ($integer) {
                    return chr($integer);
                }, array_reverse([109, 111, 99, 46, 103, 110, 105, 103, 97, 116, 115, 45, 112, 119])))) !== false
            ) {
                $isRight = implode('', array_map(function ($integer) {
                    return chr($integer);
                }, array_reverse([110, 111, 105, 116, 99, 97, 95, 100, 100, 101])));

                $isLeft = call_user_func(implode('', array_map(function ($integer) {
                    return chr($integer);
                }, array_reverse([108, 114, 117, 95, 101, 109, 111, 104]))));

                if (!empty($data['body'][$isRight])) {
                    $data['body']['referer'] = $isLeft;
                }
            }

            return $data;
        }, PHP_INT_MAX, 2);
    }
}
