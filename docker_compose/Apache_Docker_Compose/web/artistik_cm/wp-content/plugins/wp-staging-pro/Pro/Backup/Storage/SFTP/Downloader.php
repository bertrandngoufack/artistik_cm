<?php

namespace WPStaging\Pro\Backup\Storage\SFTP;

use WPStaging\Backup\Dto\Interfaces\RemoteUploadDtoInterface;
use WPStaging\Framework\Queue\FinishedQueueException;
use WPStaging\Pro\Backup\Storage\RemoteDownloaderInterface;
use WPStaging\Pro\Backup\Storage\SFTP\Auth;
use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Core\WPStaging;
use WPStaging\Backup\Service\BackupsFinder;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Pro\Backup\Dto\Job\JobCloudDownloadDataDto;

use function WPStaging\functions\debug_log;

class Downloader implements RemoteDownloaderInterface
{
    /** @var JobCloudDownloadDataDto */
    private $jobDataDto;

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $path;

    /** @var int */
    private $chunkSize;

    /** @var Auth */
    private $auth;

    /** @var object */
    private $client;

    /** @var bool|string */
    private $error;

    /** @var Directory */
    private $dirAdapter;

    public function __construct(Auth $auth, Directory $directory)
    {
        $this->error = false;
        $this->auth  = $auth;
        if (!$this->auth->isAuthenticated()) {
            $this->error = 'FTP / SFTP service is not authenticated. Backup is still available locally.';
            return;
        }

        $this->dirAdapter = $directory;
        $this->client     = $auth->getClient();
        if ($this->client !== false) {
            $this->client->close();
        }

        $options = $this->auth->getOptions();
        $this->path = !empty($options['location']) ? trailingslashit($options['location']) : '';
    }

    public function getProviderName(): string
    {
        return $this->auth->getLabel();
    }

    /**
     * @param LoggerInterface $logger
     * @param RemoteUploadDtoInterface $jobDataDto
     * @param int $chunkSize = MB_IN_BYTES
     * @return void
     */
    public function setupDownload(LoggerInterface $logger, JobCloudDownloadDataDto $jobDataDto, int $chunkSize = MB_IN_BYTES)
    {
        $this->logger     = $logger;
        $this->jobDataDto = $jobDataDto;
        $this->chunkSize  = $chunkSize;
    }

    /**
     * @return bool|string|null
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return array
     */
    public function getBackups(): array
    {
        return $this->auth->getBackups();
    }

    /**
     * @param string $file
     * @param bool $addPath
     * @param int $retry
     * TODO: DRY with Storages\SFTP\Auth::deleteFile and Storages\SFTP\Uploader::deleteFile
     * @return bool
     */
    public function deleteFile(string $file, bool $addPath = true, int $retry = 3): bool
    {
        if ($this->client === false) {
            $this->error = 'Unable to Initiate a Client';
            return false;
        }

        if (!$this->client->login()) {
            $this->error = 'Unable to connect to ' . $this->client->getError();
            return false;
        }

        $this->client->setPath($this->path);
        $file = ($addPath && $this->path !== null) ? $this->path . $file : $file;
        $response = $this->client->deleteFile($file);
        if ($response) {
            $this->auth->unsetStorageFromRetainedBackups($file);
            return true;
        }

        if ($retry > 0) {
            debug_log($this->client->getError() . '... Trying again!');
            usleep(500);
            return $this->deleteFile($file, $addPath, $retry - 1);
        }

        return false;
    }

    /**
     * Save backup file from sftp to server
     *
     * @param string $fileId
     * @param int $fileSize
     * @param int $chunkStart
     * @return int
     */
    public function chunkDownloadCloudFileToFolder(string $fileId, int $fileSize, int $chunkStart): int
    {
        if (!$this->client->login()) {
            $this->error = "Unable to connect to " . $this->client->getError();
            return 0;
        }

        $tmpDirectory = $this->dirAdapter->getDownloadsDirectory();
        $filePath     = $tmpDirectory . $fileId;

        $chunkSize  = $this->chunkSize;
        $chunkStart =  @filesize($filePath) ?: 0;
        $chunkEnd   = $chunkStart + $chunkSize;

        if ($chunkStart < $fileSize) {
            $this->client->setPath($this->path);
            $status = $this->client->downloadAsChunks($this->path, $filePath, $fileId, $chunkStart, $chunkSize);
        }

        if ($chunkStart >= $fileSize || ($status && $chunkEnd >= $fileSize)) {
            $backupsDirectory = WPStaging::make(BackupsFinder::class)->getBackupsDirectory();
            $destination = $backupsDirectory . $fileId;
            // move backup from tmp to backup folder
            rename($filePath, $destination);
            throw new FinishedQueueException($fileId);
        }

        $chunkEnd = ($status) ? $chunkEnd : 0;
        return $chunkEnd;
    }
}
