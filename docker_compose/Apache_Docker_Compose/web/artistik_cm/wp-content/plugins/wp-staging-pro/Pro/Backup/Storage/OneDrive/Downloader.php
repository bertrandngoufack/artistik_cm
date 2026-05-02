<?php

namespace WPStaging\Pro\Backup\Storage\OneDrive;

use WPStaging\Backup\Service\BackupsFinder;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Queue\FinishedQueueException;
use WPStaging\Pro\Backup\Dto\Job\JobCloudDownloadDataDto;
use WPStaging\Pro\Backup\Storage\RemoteDownloaderInterface;
use WPStaging\Pro\Backup\Storage\OneDrive\Auth;
use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Framework\Traits\HttpRequestTrait;

class Downloader implements RemoteDownloaderInterface
{
    use HttpRequestTrait;

    /** @var JobCloudDownloadDataDto */
    private $jobDataDto;

    /** @var LoggerInterface */
    private $logger;

    /** @var int */
    private $chunkSize;

    /** @var Auth */
    private $auth;

    /** @var string */
    private $error;

    /** @var array */
    protected $options;

    /** @var array */
    protected $currentBackup;

    /** @var Directory */
    private $dirAdapter;

    public function __construct(Auth $auth, Directory $directory)
    {
        $this->auth = $auth;
        try {
            if (!$this->auth->testConnection() && !$this->auth->refreshToken()) {
                $this->error = 'Fail to refresh the access token, the process should resume automatically, if the error persists please reconnect to your OneDrive account.';
                return;
            }
        } catch (\Throwable $th) {
            return;
        }

        if (!$this->auth->isAuthenticated()) {
            $this->error = 'Microsoft OneDrive is not authenticated. Backup is still available locally.';
            return;
        }

        $this->error      = '';
        $this->dirAdapter = $directory;
        $this->options    = $this->auth->getOptions();
    }

    /**
     * @param  LoggerInterface $logger
     * @param  JobCloudDownloadDataDto $jobDataDto
     * @param  int $chunkSize
     * @return void
     */
    public function setupDownload(LoggerInterface $logger, JobCloudDownloadDataDto $jobDataDto, int $chunkSize = MB_IN_BYTES)
    {
        $this->logger     = $logger;
        $this->jobDataDto = $jobDataDto;
        $this->chunkSize  = $chunkSize;
    }

    /**
     * @return string
     */
    public function getProviderName(): string
    {
        return $this->auth->getLabel();
    }

    /** @return string */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Download backup content from remote storage to server
     *
     * @param string $fileId
     * @param int $fileSize
     * @param int $chunkStart
     * @return int
     * @throws \Exception
     */
    public function chunkDownloadCloudFileToFolder(string $fileId, int $fileSize, int $chunkStart): int
    {
        $tmpDirectory = $this->dirAdapter->getDownloadsDirectory();
        $fileName     = $this->jobDataDto->getCloudFileName();
        $filePath     = trailingslashit($tmpDirectory) . $fileName;
        $fileHandle   = fopen($filePath, 'a+');

        $downloadedChunkSize = 0;
        $chunkStart          = filesize($filePath);
        $chunkSize           = $this->chunkSize;
        $chunkEnd            = $chunkStart + $chunkSize;

        if ($chunkStart < $fileSize) {
            $remoteFile = $this->currentBackup;
            if (empty($this->currentBackup)) {
                $remoteFile          = $this->auth->getBackup($fileId);
                $this->currentBackup = $remoteFile;
            }

            if (empty($remoteFile['@microsoft.graph.downloadUrl'])) {
                throw new \Exception("Error in OneDrive: Fail to get download url.");
            }

            $downloadUrl = $remoteFile['@microsoft.graph.downloadUrl'];
            $args = [
                'headers' => [
                    'Range' => sprintf('bytes=%s-%s', empty($chunkStart) ? 0 : $chunkStart, $chunkEnd),
                ],
                'method'  => 'GET',
            ];

            $response = $this->getRequestBody($downloadUrl, $args, false);
            if (empty($response)) {
                throw new \Exception("Error in OneDrive: Nothing downloaded.");
            }

            fwrite($fileHandle, $response);
            $contentLength       = strlen($response);
            $downloadedChunkSize = intval($contentLength) > 0 ? $chunkEnd : $downloadedChunkSize;
        }

        if ($chunkStart >= $fileSize || $downloadedChunkSize >= $fileSize) {
            // close the file pointer
            fclose($fileHandle);
            $backupsDirectory = WPStaging::make(BackupsFinder::class)->getBackupsDirectory();
            $destination      = $backupsDirectory . $fileName;
            // move backup from tmp to backup folder
            rename($filePath, $destination);
            $this->currentBackup = [];
            throw new FinishedQueueException($fileName);
        }

        return $downloadedChunkSize;
    }

    /**
     * @param string $fileId
     * @return bool
     */
    public function deleteFile(string $fileId): bool
    {
        if (!$this->auth->deleteFile($fileId)) {
            return false;
        }

        $backupFileName = empty($_REQUEST['backupFileName']) ? "" : sanitize_text_field($_REQUEST['backupFileName']);
        $this->auth->unsetStorageFromRetainedBackups($backupFileName);
        return true;
    }

    /**
     * @return array
     */
    public function getBackups(): array
    {
        return $this->auth->getBackups();
    }
}
