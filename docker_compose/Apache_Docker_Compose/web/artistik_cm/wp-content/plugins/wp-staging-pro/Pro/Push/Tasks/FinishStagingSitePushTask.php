<?php

namespace WPStaging\Pro\Push\Tasks;

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Facades\Hooks;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Push\Jobs\StagingSitePush;
use WPStaging\Staging\Dto\StagingSiteDto;
use WPStaging\Staging\Dto\Task\Response\FinishStagingSiteResponseDto;
use WPStaging\Staging\Sites;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

use function WPStaging\functions\debug_log;

class FinishStagingSitePushTask extends PushTask
{
    /** @var Sites */
    private $sites;

    /**
     * @param LoggerInterface $logger
     * @param Cache $cache
     * @param StepsDto $stepsDto
     * @param SeekableQueueInterface $taskQueue
     * @param Sites $sites
     */
    public function __construct(LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue, Sites $sites)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
        $this->sites = $sites;
    }

    public static function getTaskName(): string
    {
        return 'push_finish';
    }

    public static function getTaskTitle(): string
    {
        return 'Finishing Staging Site Push';
    }

    public function execute(): TaskResponseDto
    {
        $this->getJobTransientCache()->completeJob();
        $stagingSites = $this->sites->tryGettingStagingSites();
        $stagingSite  = $this->buildStagingSite();
        $stagingSites[$this->jobDataDto->getCloneId()] = $stagingSite->toArray();
        $this->sites->updateStagingSites($stagingSites);
        $this->logFinishHeader($stagingSite->getSiteName());
        $this->triggerOnStagingSitePushEvent($stagingSite);

        $this->logger->info("✓ Staging site successfully created");

        return $this->overrideGenerateResponse();
    }

    /**
     * @param string $stagingSiteName
     * @return void
     */
    protected function logFinishHeader(string $stagingSiteName)
    {
        $this->logger->info(sprintf(
            'Staging Site "%s" pushed.',
            $stagingSiteName
        ));
    }

    protected function buildStagingSite(): StagingSiteDto
    {
        $stagingSite = $this->jobDataDto->getStagingSite();
        $stagingSite->setDatetime(time());
        $stagingSite->setVersion(WPStaging::getVersion());
        $stagingSite->setOwnerId(get_current_user_id());
        $stagingSite->setStatus(StagingSiteDto::STATUS_FINISHED);

        return $stagingSite;
    }

    protected function triggerOnStagingSitePushEvent(StagingSiteDto $stagingSite)
    {
        Hooks::doAction(StagingSitePush::ACTION_PUSHING_COMPLETE, $stagingSite->toArray());
    }

    protected function getResponseDto()
    {
        return new FinishStagingSiteResponseDto();
    }

    /**
     * @return FinishStagingSiteResponseDto|TaskResponseDto
     */
    private function overrideGenerateResponse()
    {
        add_filter('wpstg.task.response', function ($response) {
            if ($response instanceof FinishStagingSiteResponseDto) {
                $response->setCloneId($this->jobDataDto->getCloneId());
                $response->setStagingSiteUrl($this->jobDataDto->getStagingSiteUrl());
            } else {
                debug_log('Fail to finalize response for staging site push process! Response content: ' . print_r($response, true));
            }

            return $response;
        });

        return $this->generateResponse();
    }
}
