<?php

namespace WPStaging\Pro\Backup\Storage\GoogleDrive;

use WPStaging\Framework\Queue\FinishedQueueException;
use WPStaging\Pro\Backup\Storage\RemoteDownloaderInterface;
use WPStaging\Pro\Backup\Dto\Job\JobCloudDownloadDataDto;
use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Backup\Service\BackupsFinder;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Traits\HttpRequestTrait;

class Downloader implements RemoteDownloaderInterface
{
    use HttpRequestTrait;

    /** @var JobCloudDownloadDataDto */
    private $jobDataDto;

    /** @var int */
    private $chunkSize;

    /** @var Auth */
    private $auth;

    /** @var bool|string */
    private $error;

    /** @var Directory */
    private $dirAdapter;

    public function __construct(Auth $auth, Directory $directory)
    {
        $this->error = false;
        $this->auth = $auth;

        if (!$this->auth->isAuthenticated()) {
            $this->error = 'Google Drive is not authenticated. Backup is still available locally.';
            return;
        }

        $this->dirAdapter = $directory;
    }

    public function getProviderName(): string
    {
        return $this->auth->getLabel();
    }

    /**
     * @param LoggerInterface $logger
     * @param JobCloudDownloadDataDto $jobDataDto
     * @param int $chunkSize = MB_IN_BYTES
     * @return void
     */
    public function setupDownload(LoggerInterface $logger, JobCloudDownloadDataDto $jobDataDto, int $chunkSize = MB_IN_BYTES)
    {
        $this->jobDataDto = $jobDataDto;
        $this->chunkSize  = $chunkSize < 10 * MB_IN_BYTES ? 10 * MB_IN_BYTES : $chunkSize;
    }

    /** @return string */
    public function getError()
    {
        return $this->error;
    }

    public function getBackups(): array
    {
        return $this->auth->getBackups();
    }

    /**
     * Delete backup from google drive
     *
     * @param string $fileId
     * @return bool
     */
    public function deleteFile(string $fileId): bool
    {
        try {
            $this->auth->deleteRemoteFileById($fileId);

            // Update backup retention.
            $backupFileName = empty($_REQUEST['backupFileName']) ? "" : sanitize_text_field($_REQUEST['backupFileName']);
            $this->auth->unsetStorageFromRetainedBackups($backupFileName);

            return true;
        } catch (\Throwable $th) {
        }

        return false;
    }

    /**
     * Download backup file from google drive to server
     *
     * @param string $fileId
     * @param int $fileSize
     * @param int $chunkStart
     *
     * @throws FinishedQueueException
     *
     * @return int
     */
    public function chunkDownloadCloudFileToFolder(string $fileId, int $fileSize, int $chunkStart): int
    {
        $chunkSize           = $this->chunkSize;
        $downloadedChunkSize = 0;

        $tmpDirectory = $this->dirAdapter->getDownloadsDirectory();
        $fileName     = $this->jobDataDto->getCloudFileName();
        $filePath     = $tmpDirectory . $fileName;
        $fileHandle   = fopen($filePath, 'a+');

        $chunkStart = filesize($filePath);
        $chunkEnd   = $chunkStart + $chunkSize;

        if ($chunkStart < $fileSize) {
            try {
                $options = $this->auth->getOptions();
                $args = [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $options['accessToken'],
                        'Range'         => sprintf('bytes=%s-%s', (int)$chunkStart, $chunkEnd),
                    ],
                    'method'  => 'GET',
                ];
                $body = $this->getRequestBody(Auth::GOOGLEDRIVE_API_V3_BASE_URL . "/files/{$fileId}?alt=media", $args, false);

                fwrite($fileHandle, $body);
                $downloadedChunkSize = $chunkEnd;
            } catch (\Throwable $th) {
            }
        }

        if ($chunkStart >= $fileSize || $downloadedChunkSize >= $fileSize) {
            // close the file pointer
            fclose($fileHandle);
            $backupsDirectory = WPStaging::make(BackupsFinder::class)->getBackupsDirectory();
            $destination      = $backupsDirectory . $fileName;
            // move backup from tmp to backup folder
            rename($filePath, $destination);
            throw new FinishedQueueException($fileName);
        }

        return $downloadedChunkSize;
    }
}
