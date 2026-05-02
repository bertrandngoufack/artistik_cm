<?php

namespace WPStaging\Pro\RemoteSync\Dto\Task;

use WPStaging\Framework\Job\Dto\AbstractTaskDto;

class DownloadPullDataTaskDto extends AbstractTaskDto
{
    /** @var int */
    public $retried = 0;

    /** @var int Current chunk size in bytes */
    public $chunkSize = 0;

    /** @var int Number of consecutive successful downloads */
    public $consecutiveSuccesses = 0;

    /** @var int Number of consecutive failed downloads */
    public $consecutiveFailures = 0;

    /** @var float Average download speed in bytes per second */
    public $averageSpeed = 0.0;

    /** @var float Last download speed in bytes per second */
    public $lastSpeed = 0.0;

    /** @var int Total bytes downloaded */
    public $totalBytesDownloaded = 0;

    /** @var float Total download time in seconds */
    public $totalDownloadTime = 0.0;

    /** @var float Timestamp when the last chunk download started */
    public $lastChunkStartTime = 0;

    /** @var bool Whether the chunk size has been initialized */
    public $isChunkSizeInitialized = false;

    /**
     * Number of consecutive "cURL error 18" failures where no bytes were downloaded.
     * This is commonly caused by proxy/cache layers (e.g. Varnish/CloudPanel) closing the connection.
     *
     * @var int
     */
    public $consecutiveCurl18NoProgress = 0;

    /**
     * Get performance summary for logging and telemetry.
     *
     * @return array{
     *   chunkSize:int,
     *   consecutiveSuccesses:int,
     *   consecutiveFailures:int,
     *   averageSpeed:float,
     *   lastSpeed:float,
     *   totalDownloaded:int,
     *   totalTime:float
     * }
     */
    public function getPerformanceInfo(): array
    {
        return [
            'chunkSize'            => $this->chunkSize,
            'consecutiveSuccesses' => $this->consecutiveSuccesses,
            'consecutiveFailures'  => $this->consecutiveFailures,
            // Speeds in MB/s to keep logs readable
            'averageSpeed'         => round(($this->averageSpeed) / (1024 * 1024), 2),
            'lastSpeed'            => round(($this->lastSpeed) / (1024 * 1024), 2),
            'totalDownloaded'      => $this->totalBytesDownloaded,
            'totalTime'            => round($this->totalDownloadTime, 2),
        ];
    }
}
