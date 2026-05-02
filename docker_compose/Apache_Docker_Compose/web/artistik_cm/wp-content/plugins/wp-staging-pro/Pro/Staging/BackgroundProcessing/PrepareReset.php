<?php

namespace WPStaging\Pro\Staging\BackgroundProcessing;

use WPStaging\Core\WPStaging;
use WPStaging\Framework\BackgroundProcessing\Job\PrepareJob;
use WPStaging\Framework\BackgroundProcessing\Queue;
use WPStaging\Framework\Job\ProcessLock;
use WPStaging\Framework\Utils\Times;
use WPStaging\Pro\Staging\Jobs\StagingSiteReset;
use WPStaging\Pro\Staging\Ajax\Reset\PrepareReset as AjaxPrepareReset;

use function WPStaging\functions\debug_log;

/**
 * Prepares a Staging Site Reset Job to be executed using Background Processing.
 */
class PrepareReset extends PrepareJob
{
    /**
     * @param AjaxPrepareReset $ajaxPrepareReset A reference to the object currently handling
     *                                            AJAX reset preparation requests.
     * @param Queue            $queue            A reference to the instance of the Queue manager the class
     *                                            should use for processing.
     * @param ProcessLock      $processLock      A reference to the Process Lock manager the class should use
     *                                            to prevent concurrent processing of the job requests.
     * @param Times            $times            A reference to the Times utility class.
     */
    public function __construct(AjaxPrepareReset $ajaxPrepareReset, Queue $queue, ProcessLock $processLock, Times $times)
    {
        parent::__construct($ajaxPrepareReset, $queue, $processLock, $times);
    }

    /**
     * Returns the default data configuration that will be used to prepare a Staging Site Reset using
     * default settings.
     */
    public function getDefaultDataConfiguration(): array
    {
        return [
            'cloneId'             => '',
            'isInit'              => true,
            'allTablesExcluded'   => false,
            'excludedTables'      => [],
            'includedTables'      => [],
            'nonSiteTables'       => [],
            'excludedDirectories' => [],
            'extraDirectories'    => [],
            'excludeGlobRules'    => [],
        ];
    }

    protected function maybeInitJob(array $args)
    {
        if ($args['isInit']) {
            debug_log('[Schedule] Configuring JOB DATA DTO for Reset', 'info', false);
            $prepareReset = WPStaging::make(AjaxPrepareReset::class);
            $prepareReset->prepare($args);
            $this->job = $prepareReset->getJob();
        } else {
            $this->job = WPStaging::make(StagingSiteReset::class);
        }
    }

    protected function getJobDefaultName(): string
    {
        return 'Staging Site Reset';
    }
}
