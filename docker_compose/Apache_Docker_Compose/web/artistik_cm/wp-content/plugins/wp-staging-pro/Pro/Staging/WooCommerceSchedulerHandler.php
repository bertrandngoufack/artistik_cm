<?php

namespace WPStaging\Pro\Staging;

use WPStaging\Staging\CloneOptions;
use WPStaging\Staging\FirstRun;

class WooCommerceSchedulerHandler
{
    /**
     * @var CloneOptions
     */
    protected $cloneOptions;

    public function __construct(CloneOptions $cloneOptions)
    {
        $this->cloneOptions = $cloneOptions;
    }

    public function disableActionScheduler()
    {
        $isWooSchedulerEnabled = (bool)$this->cloneOptions->get(FirstRun::WOO_SCHEDULER_ENABLED_KEY);
        if (!empty($isWooSchedulerEnabled)) {
            return;
        }

        if (class_exists('ActionScheduler')) {
            remove_action('action_scheduler_run_queue', [\ActionScheduler::runner(), 'run']);
        }
    }
}
