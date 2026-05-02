<?php

namespace WPStaging\Pro\Backup\Storage\BaseS3;

use Exception;
use WPStaging\Backup\Dto\Interfaces\RemoteUploadDtoInterface;
use WPStaging\Framework\Queue\FinishedQueueException;
use WPStaging\Pro\Backup\Storage\RemoteDownloaderInterface;
use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Core\WPStaging;
use WPStaging\Backup\Service\BackupsFinder;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Pro\Backup\Dto\Job\JobCloudDownloadDataDto;

use function WPStaging\functions\debug_log;

abstract class S3Downloader implements RemoteDownloaderInterface
{
    /** @var string */
    private $bucketName;

    /** @var int */
    private $chunkSize;

    /** @var S3Auth */
    private $auth;

    /** @var S3Client */
    private $client;

    /** @var bool|string */
    private $error;

    /** @var Directory */
    private $dirAdapter;

    public function __construct(S3Auth $auth, Directory $directory)
    {
        $this->error = false;
        $this->auth  = $auth;

        if (!$this->auth->isAuthenticated()) {
            $this->error = $this->getProviderName() . ' service is not authenticated. Backup is still available locally.';
            return;
        }

        $this->dirAdapter = $directory;
        $this->client     = $auth->getClient();
        $location         = $this->auth->getLocation();
        $this->bucketName = $location[0];
    }

    /**
     * @param LoggerInterface $logger
     * @param RemoteUploadDtoInterface $jobDataDto
     * @param int $chunkSize = MB_IN_BYTES
     * @return void
     */
    public function setupDownload(LoggerInterface $logger, JobCloudDownloadDataDto $jobDataDto, int $chunkSize = MB_IN_BYTES)
    {
        $this->chunkSize = $chunkSize;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getProviderName(): string
    {
        return $this->auth->getLabel();
    }

    public function getBackups(): array
    {
        return $this->auth->getBackups();
    }

    /**
     * @param string $fileId
     * @param int $fileSize
     * @param int $chunkStart
     * @return int
     */
    public function chunkDownloadCloudFileToFolder(string $fileId, int $fileSize, int $chunkStart): int
    {
        $tmpDirectory = $this->dirAdapter->getDownloadsDirectory();
        $fileName     = basename($fileId);
        $filePath     = $tmpDirectory . $fileName;
        $fileHandle   = fopen($filePath, 'a+');
        if (!$fileHandle) {
            debug_log("S3 cannot create file! Error message: ");
            throw new Exception("S3 cannot create file! Error message: ");
        }

        $chunkSize           = $this->chunkSize;
        $chunkStart          = filesize($filePath);
        $chunkEnd            = $chunkStart + $chunkSize;
        $downloadedChunkSize = 0;

        if ($chunkStart < $fileSize) {
            $file = $this->client->getObject([
                'Bucket' => $this->bucketName,
                'Key'    => $fileId,
                'Range'  => sprintf('bytes=%s-%s', ($chunkStart === 0) ? 0 : $chunkStart, $chunkEnd),
            ]);
            $body = $file['Body'];
            fwrite($fileHandle, $body);
            $contentLength = strlen($body);
            $downloadedChunkSize = $contentLength > 0 ? $chunkEnd : $downloadedChunkSize;
        }

        if ($chunkStart >= $fileSize || $downloadedChunkSize >= $fileSize) {
            // close the file pointer
            fclose($fileHandle);
            $backupsDirectory = WPStaging::make(BackupsFinder::class)->getBackupsDirectory();
            $destination = $backupsDirectory . $fileName;
            // move backup from tmp to backup folder
            rename($filePath, $destination);
            throw new FinishedQueueException($fileName);
        }

        return $downloadedChunkSize;
    }

    /**
     * Delete backup from s3
     *
     * @param string $fileId
     * @return bool
     */
    public function deleteFile(string $fileId): bool
    {
        if ($this->client === false) {
            return false;
        }

        if ($this->client->deleteObject(['Bucket' => $this->bucketName, 'Key' => $fileId])) {
            $this->auth->unsetStorageFromRetainedBackups($fileId);
            return true;
        }

        return false;
    }
}
