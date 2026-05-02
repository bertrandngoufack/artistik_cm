<?php

/**
 * Prepares a Pull Request to be executed using Background Processing.
 *
 * @package WPStaging\Pro\RemoteSync\BackgroundProcessing
 */

namespace WPStaging\Pro\RemoteSync\BackgroundProcessing;

use WPStaging\Core\WPStaging;
use WPStaging\Framework\BackgroundProcessing\Job\PrepareJob;
use WPStaging\Framework\BackgroundProcessing\Queue;
use WPStaging\Framework\Job\ProcessLock;
use WPStaging\Framework\Traits\MemoryExhaustTrait;
use WPStaging\Framework\Utils\Times;
use WPStaging\Pro\RemoteSync\Ajax\PreparePull as AjaxPreparePull;
use WPStaging\Pro\RemoteSync\Dto\Job\PullInitiatorDataDto;
use WPStaging\Pro\RemoteSync\Jobs\PullInitiator;

/**
 * Class PreparePull
 *
 * @package WPStaging\Pro\RemoteSync\BackgroundProcessing
 */
class PreparePull extends PrepareJob
{
    use MemoryExhaustTrait;

    /**
     * @var string
     */
    const WPSTG_REQUEST = 'wpstg_remote_sync_pull';

    /**
     * PreparePull constructor.
     *
     * @param AjaxPreparePull   $ajaxPreparePull   A reference to the object currently handling
     *                                             Pull preparation requests.
     * @param Queue             $queue             A reference to the instance of the Queue manager the class
     *                                             should use for processing.
     * @param ProcessLock       $processLock       A reference to the Process Lock manager the class should use
     *                                             to prevent concurrent processing of the job requests.
     * @param Times             $times             A reference to the Times utility class.
     */
    public function __construct(AjaxPreparePull $ajaxPreparePull, Queue $queue, ProcessLock $processLock, Times $times)
    {
        parent::__construct($ajaxPreparePull, $queue, $processLock, $times);
    }

    /**
     * Returns the default data configuration that will be used to prepare Pull using
     * default settings.
     */
    public function getDefaultDataConfiguration(): array
    {
        return [
            'isInit' => true,
        ];
    }

    /**
     * Prepares the Pull request.
     *
     * @param array $args The arguments to prepare the Pull request with.
     *
     * @return void
     */
    protected function maybeInitJob(array $args)
    {
        if ($args['isInit']) {
            $prepareJob = WPStaging::make(AjaxPreparePull::class);
            $prepareJob->prepare($args);
            $this->job = $prepareJob->getJob();
        } else {
            // Explicitly create PullInitiatorDataDto to ensure the correct DTO type is used.
            // The DI contextual binding may fail to provide the correct type after database restore,
            // so we manually inject the correct DTO to guarantee proper job state restoration.
            $jobDataDto = WPStaging::make(PullInitiatorDataDto::class);
            $this->job = WPStaging::make(PullInitiator::class);
            $this->job->setJobDataDto($jobDataDto);
        }

        $this->job->setMemoryExhaustErrorTmpFile($this->getMemoryExhaustErrorTmpFile(self::WPSTG_REQUEST));
    }

    protected function getIsBackupJob(): bool
    {
        return false;
    }

    protected function getJobDefaultName(): string
    {
        return 'Pull Data';
    }
}
