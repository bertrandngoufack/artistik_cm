<?php

namespace WPStaging\Pro\Backup\Storage;

use WPStaging\Pro\Backup\Dto\Job\JobCloudDownloadDataDto;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

interface RemoteDownloaderInterface
{
    /** @return string */
    public function getProviderName(): string;

    /** @return array */
    public function getBackups(): array;

    /**
     * @param string $fileId
     * @return bool
     */
    public function deleteFile(string $fileId): bool;

    /**
     * @param  LoggerInterface $logger
     * @param  JobCloudDownloadDataDto $jobDataDto
     * @param  int $chunkSize
     * @return void
     */
    public function setupDownload(LoggerInterface $logger, JobCloudDownloadDataDto $jobDataDto, int $chunkSize = MB_IN_BYTES);

    /**
     * Save backup file from remote storage to server
     * @param string $fileId Usually this is same with the file name.
     * @param int $fileSize
     * @param int $chunkStart
     * @return int
     */
    public function chunkDownloadCloudFileToFolder(string $fileId, int $fileSize, int $chunkStart): int;
}
