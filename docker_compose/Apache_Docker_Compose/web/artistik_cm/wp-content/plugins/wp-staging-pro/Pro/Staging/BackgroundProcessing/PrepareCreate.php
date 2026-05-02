<?php

namespace WPStaging\Pro\Staging\BackgroundProcessing;

use WPStaging\Core\WPStaging;
use WPStaging\Framework\BackgroundProcessing\Job\PrepareJob;
use WPStaging\Framework\BackgroundProcessing\Queue;
use WPStaging\Framework\Job\ProcessLock;
use WPStaging\Framework\Utils\Times;
use WPStaging\Pro\Staging\Jobs\StagingSiteCreate;
use WPStaging\Staging\Ajax\Create\PrepareCreate as AjaxPrepareCreate;

use function WPStaging\functions\debug_log;

/**
 * Class PrepareCreate
 * Prepares a Staging Site Create Job to be executed using Background Processing.
 *
 * @package WPStaging\Staging\BackgroundProcessing
 */
class PrepareCreate extends PrepareJob
{
    /**
     * PrepareCreate constructor.
     *
     * @param AjaxPrepareCreate $ajaxPrepareCreate A reference to the object currently handling
     *                                             AJAX Delete preparation requests.
     * @param Queue             $queue             A reference to the instance of the Queue manager the class
     *                                             should use for processing.
     * @param ProcessLock       $processLock       A reference to the Process Lock manager the class should use
     *                                             to prevent concurrent processing of the job requests.
     * @param Times             $times             A reference to the Times utility class.
     */
    public function __construct(AjaxPrepareCreate $ajaxPrepareCreate, Queue $queue, ProcessLock $processLock, Times $times)
    {
        parent::__construct($ajaxPrepareCreate, $queue, $processLock, $times);
    }

    /**
     * Returns the default data configuration that will be used to prepare a Staging Site Create using
     * default settings.
     */
    public function getDefaultDataConfiguration(): array
    {
        return [
            'cloneId'             => time(),
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
            debug_log('[Schedule] Configuring JOB DATA DTO', 'info', false);
            $prepareCreate = WPStaging::make(AjaxPrepareCreate::class);
            $prepareCreate->prepare($args);
            $this->job = $prepareCreate->getJob();
        } else {
            $this->job = WPStaging::make(StagingSiteCreate::class);
        }
    }

    protected function getJobDefaultName(): string
    {
        return 'Staging Site Create';
    }
}
