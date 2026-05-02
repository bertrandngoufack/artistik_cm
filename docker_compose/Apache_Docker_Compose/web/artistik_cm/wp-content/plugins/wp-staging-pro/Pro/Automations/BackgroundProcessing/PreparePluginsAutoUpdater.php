<?php

namespace WPStaging\Pro\Automations\BackgroundProcessing;

use WPStaging\Core\WPStaging;
use WPStaging\Framework\BackgroundProcessing\Job\PrepareJob;
use WPStaging\Framework\BackgroundProcessing\Queue;
use WPStaging\Framework\Job\ProcessLock;
use WPStaging\Framework\Utils\Times;
use WPStaging\Pro\Automations\PluginsUpdater\Job\JobPluginsUpdater;
use WPStaging\Pro\Automations\PluginsUpdater\PreparePluginsUpdater;

use function WPStaging\functions\debug_log;

class PreparePluginsAutoUpdater extends PrepareJob
{
    /**
     * @param PreparePluginsUpdater $preparePluginsUpdater
     * @param Queue $queue
     * @param ProcessLock $processLock
     * @param Times $times
     */
    public function __construct(PreparePluginsUpdater $preparePluginsUpdater, Queue $queue, ProcessLock $processLock, Times $times)
    {
        parent::__construct($preparePluginsUpdater, $queue, $processLock, $times);
    }

    /**
     * Returns the default data configuration that will be used to prepare a WP Plugins Updater using
     * default settings.
     *
     * @return array The WP Plugins Updater preparation default settings.
     */
    public function getDefaultDataConfiguration(): array
    {
        return [
            'isAutoUpdatePlugins' => false,
            'stagingUrl'          => false,
            'isInit'              => true,
            'outdatedPlugins'     => [],
            'isWpCliRequest'      => true,
            'isNetworkClone'      => false,
            'name'                => 'wp_plugins_auto_updater',
            'authToken'           => '',
            'cloneId'             => '',
        ];
    }

    /**
     * @param array $args
     * @return void
     */
    protected function maybeInitJob(array $args)
    {
        if ($args['isInit']) {
            debug_log('[Schedule] Configuring JOB DATA DTO', 'info', false);
            $preparePluginsUpdater = WPStaging::make(PreparePluginsUpdater::class);
            $preparePluginsUpdater->prepare($args);
            $this->job = $preparePluginsUpdater->getJob();
        } else {
            $this->job = WPStaging::make(JobPluginsUpdater::class);
        }
    }
}
