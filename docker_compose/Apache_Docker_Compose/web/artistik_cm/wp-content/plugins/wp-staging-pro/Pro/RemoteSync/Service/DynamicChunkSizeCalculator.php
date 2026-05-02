<?php

namespace WPStaging\Pro\RemoteSync\Service;

use WPStaging\Pro\RemoteSync\Dto\Task\DownloadPullDataTaskDto;

/**
 * Class DynamicChunkSizeCalculator
 *
 * Handles dynamic adjustment of download chunk sizes based on success/failure rates
 * and network performance to optimize download speed and reliability.
 */
class DynamicChunkSizeCalculator
{
    /** @var int Minimum chunk size in bytes (1MB) */
    const MIN_CHUNK_SIZE = 1 * 1024 * 1024;

    /** @var int Maximum chunk size in bytes (10MB) */
    const MAX_CHUNK_SIZE = 10 * 1024 * 1024;

    /** @var int Default initial chunk size in bytes (5MB) */
    const DEFAULT_CHUNK_SIZE = 5 * 1024 * 1024;

    /** @var float Decrease factor on failure (25%) */
    const FAILURE_DECREASE_FACTOR = 0.75;

    /** @var int Number of failures that trigger immediate decrease */
    const FAILURES_FOR_DECREASE = 1;

    /** @var int Consecutive successes required for aggressive increase */
    const SUCCESSES_FOR_AGGRESSIVE_INCREASE = 3;

    /** @var int Consecutive successes required for moderate increase */
    const SUCCESSES_FOR_MODERATE_INCREASE = 5;

    /** @var int Speed threshold for aggressive increase (1 MB/s) */
    const SPEED_THRESHOLD_AGGRESSIVE = 1048576;

    /** @var int Speed threshold for moderate increase (512 KB/s) */
    const SPEED_THRESHOLD_MODERATE = 524288;

    /** @var float Aggressive increase factor (50%) */
    const AGGRESSIVE_INCREASE_FACTOR = 1.5;

    /** @var float Moderate increase factor (25%) */
    const MODERATE_INCREASE_FACTOR = 1.25;

    /**
     * Initialize chunk size for a new task
     *
     * @param DownloadPullDataTaskDto $taskDto
     * @param int $fileSize
     * @return int The initialized chunk size
     */
    public function initializeChunkSize(DownloadPullDataTaskDto $taskDto, int $fileSize): int
    {
        if ($taskDto->isChunkSizeInitialized) {
            return $taskDto->chunkSize;
        }

        // Start with default size but adjust based on file size
        $chunkSize = self::DEFAULT_CHUNK_SIZE;

        // For small files, use smaller chunks to avoid over-chunking
        if ($fileSize < 10 * 1024 * 1024) { // Files smaller than 10MB
            $chunkSize = min($chunkSize, max(self::MIN_CHUNK_SIZE, $fileSize / 4));
        }

        // For very large files, start with larger chunks
        if ($fileSize > 500 * 1024 * 1024) { // Files larger than 500MB
            $chunkSize = min(self::MAX_CHUNK_SIZE, $chunkSize * 2);
        }

        $taskDto->chunkSize = $this->roundToMegabytes($this->boundChunkSize($chunkSize));
        $taskDto->isChunkSizeInitialized = true;

        return $taskDto->chunkSize;
    }

    /**
     * Update chunk size on successful download
     *
     * @param DownloadPullDataTaskDto $dto
     * @param int $bytesDownloaded
     * @param float $downloadTime
     * @return int New chunk size
     */
    public function updateOnSuccess(DownloadPullDataTaskDto $dto, int $bytesDownloaded, float $downloadTime): int
    {
        if ($bytesDownloaded <= 0 || $downloadTime <= 0) {
            // Invalid metrics, no update
            return $dto->chunkSize;
        }

        $speed = $bytesDownloaded / $downloadTime; // bytes per second

        $dto->consecutiveSuccesses++;
        $dto->consecutiveFailures = 0;

        // Aggressive increase after multiple successes with good speed
        if ($dto->consecutiveSuccesses >= self::SUCCESSES_FOR_AGGRESSIVE_INCREASE && $speed > self::SPEED_THRESHOLD_AGGRESSIVE) {
            $newSize = (int)($dto->chunkSize * self::AGGRESSIVE_INCREASE_FACTOR);
        } elseif ($dto->consecutiveSuccesses >= self::SUCCESSES_FOR_MODERATE_INCREASE && $speed > self::SPEED_THRESHOLD_MODERATE) {
            $newSize = (int)($dto->chunkSize * self::MODERATE_INCREASE_FACTOR);
        } else {
            $newSize = $dto->chunkSize;
        }

        $newSize = $this->roundToMegabytes($this->boundChunkSize($newSize));

        $dto->chunkSize = $newSize;
        return $newSize;
    }

    /**
     * Update chunk size based on download failure
     *
     * @param DownloadPullDataTaskDto $taskDto
     * @return int The new chunk size
     */
    public function updateOnFailure(DownloadPullDataTaskDto $taskDto): int
    {
        // Reset success counter and increment failure counter
        $taskDto->consecutiveSuccesses = 0;
        $taskDto->consecutiveFailures++;

        // Immediately decrease chunk size on any failure
        if ($taskDto->consecutiveFailures >= self::FAILURES_FOR_DECREASE) {
            $newChunkSize = intval($taskDto->chunkSize * self::FAILURE_DECREASE_FACTOR);
            $taskDto->chunkSize = $this->roundToMegabytes($this->boundChunkSize($newChunkSize));
            $taskDto->consecutiveFailures = 0; // Reset to avoid continuous decreases
        }

        return $taskDto->chunkSize;
    }

    /**
     * Ensure chunk size is within acceptable bounds
     *
     * @param int $chunkSize
     * @return int
     */
    private function boundChunkSize(int $chunkSize): int
    {
        return max(self::MIN_CHUNK_SIZE, min(self::MAX_CHUNK_SIZE, $chunkSize));
    }

    /**
     * Round chunk size to the nearest megabyte
     *
     * @param int $chunkSize
     * @return int
     */
    private function roundToMegabytes(int $chunkSize): int
    {
        $megabyte = 1024 * 1024;
        $roundedMB = round($chunkSize / $megabyte);

        // Ensure we don't round to 0
        if ($roundedMB < 1) {
            $roundedMB = 1;
        }

        return intval($roundedMB * $megabyte);
    }

    /**
     * Reset performance counters (useful for retries)
     *
     * @param DownloadPullDataTaskDto $taskDto
     * @return void
     */
    public function resetCounters(DownloadPullDataTaskDto $taskDto)
    {
        $taskDto->consecutiveSuccesses = 0;
        $taskDto->consecutiveFailures = 0;
    }
}
