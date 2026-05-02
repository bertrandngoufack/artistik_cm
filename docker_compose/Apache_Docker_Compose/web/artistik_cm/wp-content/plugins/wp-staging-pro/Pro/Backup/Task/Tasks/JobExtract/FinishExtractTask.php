<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobExtract;

use WPStaging\Framework\Traits\EventLoggerTrait;
use WPStaging\Pro\Backup\Dto\Task\Extract\Response\ExtractFinishResponseDto;
use WPStaging\Backup\Entity\BackupMetadata;
use WPStaging\Pro\Backup\Task\ExtractTask;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Logger\SseEventCache;
use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

/**
 * Finishes the extraction job.
 */
class FinishExtractTask extends ExtractTask
{
    use EventLoggerTrait;

    /** @var Directory */
    private $directory;

    public function __construct(Directory $directory, LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue)
    {
        $this->directory = $directory;
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
    }

    public static function getTaskName()
    {
        return 'backup_finish_extract';
    }

    public static function getTaskTitle()
    {
        return 'Finishing Extraction';
    }

    public function execute()
    {
        if (!$this->stepsDto->getTotal()) {
            $this->stepsDto->setTotal(1);
        }

        $this->jobDataDto->setFinished(true);

        $this->logger->info('✓ Extraction completed successfully');

        $metadata    = $this->jobDataDto->getBackupMetadata();
        $extractRoot = '';
        if ($metadata instanceof BackupMetadata) {
            $absolutePath = trailingslashit($this->directory->getPluginWpContentDirectory()) . 'extract/' . $this->jobDataDto->getId() . '/';
            $extractRoot  = str_replace(trailingslashit(wp_normalize_path(ABSPATH)), '', wp_normalize_path($absolutePath));
        }

        $this->getJobTransientCache()->completeJob();
        $this->logger->pushSseEvent(SseEventCache::EVENT_TYPE_COMPLETE, [
            'status' => 'success',
            'data'   => [
                'message' => __('Extraction completed successfully.', 'wp-staging'),
                'type'    => 'extract',
            ],
        ]);

        $this->logBackupExtractionCompleted();
        /** @var ExtractFinishResponseDto $response */
        $response = $this->generateResponse();
        $response->setPath($extractRoot);
        $response->setExtracted($this->jobDataDto->getExtracted());
        $response->setSkipped($this->jobDataDto->getSkipped());
        $response->setErrors($this->jobDataDto->getErrors());

        return $response;
    }

    /**
     * @return ExtractFinishResponseDto
     */
    protected function getResponseDto()
    {
        return new ExtractFinishResponseDto();
    }
}
