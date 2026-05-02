<?php

namespace WPStaging\Pro\RemoteSync\Tasks;

use WPStaging\Backup\Service\BackupsFinder;
use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Framework\Job\Task\AbstractTask;
use WPStaging\Framework\Facades\Hooks;
use WPStaging\Framework\Network\RemoteDownloader;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Traits\RestRequestTrait;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\RemoteSync\Dto\Job\PullInitiatorDataDto;
use WPStaging\Pro\RemoteSync\Dto\Task\DownloadPullDataTaskDto;
use WPStaging\Pro\RemoteSync\Service\DynamicChunkSizeCalculator;
use WPStaging\Pro\RemoteSync\SyncSession;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

class DownloadPullDataTask extends AbstractTask
{
    use RestRequestTrait;

    /**
     * @var PullInitiatorDataDto
     */
    protected $jobDataDto;

    /** @var int */
    const MAX_RETRIES = 5;

    /** @var int */
    const CURL_18_NO_PROGRESS_MAX = 5;

    /** @var DownloadPullDataTaskDto */
    protected $currentTaskDto;

    /** @var RemoteDownloader */
    private $downloader;

    /**
     * @var BackupsFinder
     */
    private $backupsFinder;

    /**
     * @var string
     */
    private $backupPath;

    /**
     * @var DynamicChunkSizeCalculator
     */
    private $chunkSizeCalculator;

    public function __construct(LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue, RemoteDownloader $downloader, BackupsFinder $backupsFinder, DynamicChunkSizeCalculator $chunkSizeCalculator)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
        $this->downloader    = $downloader;
        $this->backupsFinder = $backupsFinder;
        $this->chunkSizeCalculator = $chunkSizeCalculator;
    }

    /**
     * @return string
     */
    public static function getTaskName()
    {
        return 'remote_sync_pull_data';
    }

    /**
     * @return string
     */
    public static function getTaskTitle()
    {
        return 'Pulling data from remote site';
    }

    /**
     * @return TaskResponseDto
     */
    public function execute()
    {
        $this->setup();

        // Initialize dynamic chunk size
        $initialChunkSize = $this->chunkSizeCalculator->initializeChunkSize($this->currentTaskDto, $this->downloader->getRemoteFileSize());
        $this->downloader->setChunkSize($initialChunkSize);

        while (!$this->isThreshold() && !$this->downloader->getIsCompleted()) {
            $this->downloadChunk();
            $this->downloader->advanceStartByte();

            if (!$this->downloader->getIsSuccess()) {
                break;
            }
        }

        $this->downloader->closeFileHandle();
        $this->stepsDto->setCurrent($this->downloader->getStartByte());
        if (!$this->downloader->getIsCompleted()) {
            $this->currentTaskDto->retried = 0; // Reset retry count for the next attempt
            $this->setCurrentTaskDto($this->currentTaskDto);
            $this->logDownloadingInfo();
            return $this->generateResponse(false);
        }

        // Check if we have retried the maximum number of times
        if (!$this->downloader->getIsSuccess() && $this->currentTaskDto->retried >= self::MAX_RETRIES) {
            $this->setCurrentTaskDto($this->currentTaskDto);
            $this->logDownloadingInfo();
            $this->logger->error($this->downloader->getMessage());
            return $this->generateResponse();
        }

        if (!$this->downloader->getIsSuccess()) {
            $this->currentTaskDto->retried++;
            $this->setCurrentTaskDto($this->currentTaskDto);

            // Reset performance counters for retry
            $this->chunkSizeCalculator->resetCounters($this->currentTaskDto);

            $this->logDownloadingInfo();
            $this->logger->warning(sprintf("Download failed, retrying (%d/%d)...", $this->currentTaskDto->retried, self::MAX_RETRIES));
            return $this->generateResponse(false);
        }

        // Log final performance summary
        $performanceInfo = $this->currentTaskDto->getPerformanceInfo();
        $this->logger->info(sprintf("Pull data download completed. Final performance: [%s]", $this->getReadablePerformance($performanceInfo)));

        $this->stepsDto->finish();
        $this->jobDataDto->setFile($this->backupPath);
        $this->jobDataDto->setIsDataDownloaded(true);
        $this->triggerFinishDownloadEvent();
        $this->updateJob();

        return $this->generateResponse();
    }

    /**
     * @return void
     * @throws \RuntimeException
     */
    protected function setup()
    {
        $startByte     = 0;
        $fileSize      = 0;
        $remoteFileUrl = $this->jobDataDto->getDataUrl();

        if ($this->stepsDto->getTotal() > 0) {
            $startByte = $this->stepsDto->getCurrent();
            $fileSize  = $this->stepsDto->getTotal();
        }

        $this->downloader->setRemoteUrl($remoteFileUrl);
        $fileName = basename($remoteFileUrl);
        $this->downloader->setFileName($fileName);
        $this->downloader->setStartByte($startByte);

        $httpAuthUsername = $this->jobDataDto->getHttpAuthUsername();
        $httpAuthPassword = $this->jobDataDto->getHttpAuthPassword();
        if (!empty($httpAuthUsername) && !empty($httpAuthPassword)) {
            $this->downloader->setCustomHeaders([
                'Authorization' => 'Basic ' . base64_encode($httpAuthUsername . ':' . $httpAuthPassword),
            ]);
        }

        if ($fileSize === 0) {
            $fileSize = $this->downloader->fetchRemoteFileSize();
            $this->stepsDto->setTotal($fileSize);
        }

        if ($fileSize === 0) {
            $fileSize = $this->downloader->fetchRemoteFileSize(false);
            $this->stepsDto->setTotal($fileSize);
        }

        if ($fileSize === 0) {
            $fileSize = $this->downloader->fetchRemoteFileSizeByGet();
            $this->stepsDto->setTotal($fileSize);
        }

        if ($fileSize === 0) {
            $fileSize = $this->downloader->fetchRemoteFileSizeByGet(false);
            $this->stepsDto->setTotal($fileSize);
        }

        if ($fileSize === 0) {
            throw new \RuntimeException('Could not determine remote file size.');
        }

        $this->downloader->setRemoteFileSize($fileSize);
        $this->backupPath = $this->backupsFinder->getBackupsDirectory() . '/' . $this->downloader->getFileName();
        $this->downloader->setLocalPath($this->backupPath);
    }

    /**
     * @return void
     */
    protected function triggerFinishDownloadEvent()
    {
        $syncSession = new SyncSession();
        if (!$syncSession->isRunning() || (!$syncSession->isTwoWaySync() && !$syncSession->isInitiator())) {
            return;
        }

        $this->headers = $this->getAuthorizationHeader($syncSession->getToken());
        $this->setHttpAuth($this->jobDataDto->getHttpAuthUsername(), $this->jobDataDto->getHttpAuthPassword());
        $this->sendRestRequest($syncSession->getPushSiteUrl(), 'finish_download');
    }

    protected function formatSize(int $bytes): string
    {
        // Let show decimal places only if the size is greater than 1GB
        if ($bytes >= 1073741824) {
            return size_format($bytes, 3);
        }

        return size_format($bytes);
    }

    protected function downloadChunk()
    {
        $chunkStartTime = microtime(true);
        $this->currentTaskDto->lastChunkStartTime = $chunkStartTime;

        $this->downloader->downloadChunk();

        $this->updateCurl18NoProgressCounter();
        $this->setCurrentTaskDto($this->currentTaskDto);

        if ($this->currentTaskDto->consecutiveCurl18NoProgress >= self::CURL_18_NO_PROGRESS_MAX) {
            throw new \RuntimeException($this->getVarnishCurl18GuidanceMessage());
        }

        $chunkDownloadTime = $this->calculateSpeed($chunkStartTime);
        if (!$this->downloader->getIsSuccess()) {
            // Update chunk size on failure
            $newChunkSize = $this->chunkSizeCalculator->updateOnFailure($this->currentTaskDto);
            $this->downloader->setChunkSize($newChunkSize);

            $performanceInfo = $this->currentTaskDto->getPerformanceInfo();
            $this->logger->warning(sprintf(
                "Download chunk failed. Reduced chunk size to %s. Performance: [%s]",
                $this->formatSize($newChunkSize),
                $this->getReadablePerformance($performanceInfo)
            ));

            return;
        }

        // Update chunk size on success
        $newChunkSize = $this->chunkSizeCalculator->updateOnSuccess($this->currentTaskDto, $this->downloader->getLastDownloadedBytes(), $chunkDownloadTime);
        $performanceInfo = $this->currentTaskDto->getPerformanceInfo();
        if ($newChunkSize === $this->downloader->getChunkSize()) {
            $this->logger->debug(sprintf(
                "Download Performance: [%s]",
                $this->getReadablePerformance($performanceInfo)
            ));

            return;
        }

        $this->downloader->setChunkSize($newChunkSize);
        $this->logger->debug(sprintf(
            "Optimized chunk size to %s. Performance: %s",
            $this->formatSize($newChunkSize),
            $this->getReadablePerformance($performanceInfo)
        ));
    }

    private function updateCurl18NoProgressCounter()
    {
        if ($this->downloader->getIsSuccess()) {
            $this->currentTaskDto->consecutiveCurl18NoProgress = 0;
            return;
        }

        $downloadedBytes = $this->downloader->getLastDownloadedBytes();
        $message         = $this->downloader->getMessage();
        if ($downloadedBytes === 0 && $this->isCurl18Message($message)) {
            $this->currentTaskDto->consecutiveCurl18NoProgress++;
            return;
        }

        $this->currentTaskDto->consecutiveCurl18NoProgress = 0;
    }

    private function isCurl18Message(string $message): bool
    {
        return stripos($message, 'cURL error 18') !== false || stripos($message, 'curl error 18') !== false;
    }

    private function getVarnishCurl18GuidanceMessage(): string
    {
        $helpUrl = Hooks::applyFilters(
            'wpstg.remote_sync.curl18_varnish_help_url',
            'https://wp-staging.com/docs/category/troubleshooting/'
        );

        return sprintf(
            // translators: %1$s: help url, %2$s: original cURL error message.
            esc_html__(
                'Download failed: repeated "cURL error 18" with no progress. This is often caused by a cache/proxy (e.g. Varnish/CloudPanel) closing the transfer. Please disable caching for the remote sync download endpoint or temporarily disable Varnish on the remote site. See: %1$s. Original error: %2$s',
                'wp-staging'
            ),
            $helpUrl,
            $this->downloader->getMessage()
        );
    }

    /** @return string */
    protected function getCurrentTaskType(): string
    {
        return DownloadPullDataTaskDto::class;
    }

    private function calculateSpeed(float $chunkStartTime): float
    {
        $chunkEndTime      = microtime(true);
        $chunkDownloadTime = max(0.000001, $chunkEndTime - $chunkStartTime);
        $bytesDownloaded   = $this->downloader->getLastDownloadedBytes();
        $this->currentTaskDto->totalBytesDownloaded += $bytesDownloaded;
        $this->currentTaskDto->totalDownloadTime += $chunkDownloadTime;
        $this->currentTaskDto->lastSpeed = ($bytesDownloaded > 0) ? ($bytesDownloaded / $chunkDownloadTime) : 0.0;
        if ($this->currentTaskDto->totalDownloadTime > 0) {
            $this->currentTaskDto->averageSpeed = $this->currentTaskDto->totalBytesDownloaded / $this->currentTaskDto->totalDownloadTime;
        }

        return $chunkDownloadTime;
    }

    /**
     * @return void
     */
    private function logDownloadingInfo()
    {
        $downloadPercentage    = ($this->downloader->getRemoteFileSize() > 0) ? (int)number_format(($this->downloader->getStartByte() / $this->downloader->getRemoteFileSize()) * 100, 0, '.', '') : 0;
        $speedInBytesPerSecond = $this->currentTaskDto->lastSpeed;
        $downloadSpeedInMbps   = (float)number_format($speedInBytesPerSecond / 1000000.0, 1, '.', '');
        $this->logger->info(sprintf(
            "Downloading Pull Data %s/%s - %s ~ %s",
            $this->formatSize($this->downloader->getStartByte()),
            $this->formatSize($this->downloader->getRemoteFileSize()),
            $downloadPercentage . '%',
            $downloadSpeedInMbps . 'MB/s'
        ));
    }

    private function getReadablePerformance(array $performanceInfo): string
    {
        // Speeds are already provided in MB/s by the DTO to keep log output readable.
        $averageSpeedMbps = isset($performanceInfo['averageSpeed']) ? (float)$performanceInfo['averageSpeed'] : 0.0;
        $lastSpeedMbps    = isset($performanceInfo['lastSpeed']) ? (float)$performanceInfo['lastSpeed'] : 0.0;

        return sprintf(
            "Chunk Size: %s, Consecutive Successes: %s, Consecutive Failures: %s, Avg Speed: %s MB/s, Last Speed: %s MB/s, Total Downloaded: %s, Total Time: %ss",
            $this->formatSize((int)$performanceInfo['chunkSize']),
            $performanceInfo['consecutiveSuccesses'],
            $performanceInfo['consecutiveFailures'],
            number_format($averageSpeedMbps, 2),
            number_format($lastSpeedMbps, 2),
            $this->formatSize((int)$performanceInfo['totalDownloaded']),
            number_format((float)$performanceInfo['totalTime'], 2)
        );
    }
}
