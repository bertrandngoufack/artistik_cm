<?php

namespace WPStaging\Pro\Backup\Storage\PCloud;

use WPStaging\Backup\Service\BackupsFinder;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Queue\FinishedQueueException;
use WPStaging\Pro\Backup\Dto\Job\JobCloudDownloadDataDto;
use WPStaging\Pro\Backup\Storage\RemoteDownloaderInterface;
use WPStaging\Pro\Backup\Storage\PCloud\Auth;
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

    /** @var string|false */
    private $error;

    /** @var array */
    protected $options;

    /** @var Directory */
    private $dirAdapter;

    public function __construct(Auth $auth, Directory $directory)
    {
        $this->auth = $auth;
        if (!$this->auth->testConnection() && !$this->auth->refreshToken()) {
            $this->error = 'Fail to refresh the access token, the process should resume automatically, if the error persists please reconnect to your ' . $this->getProviderName() . ' account.';
            return;
        }

        if (!$this->auth->isAuthenticated()) {
            $this->error = $this->getProviderName() . ' is not authenticated. Backup is still available locally.';
            return;
        }

        $this->error      = false;
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
     * @param string $fileId
     * @param int $fileSize
     * @param int $chunkStart
     * @return int
     */
    public function chunkDownloadCloudFileToFolder(string $fileId, int $fileSize, int $chunkStart): int
    {
        $tmpDirectory = $this->dirAdapter->getDownloadsDirectory();
        $fileName     = $this->jobDataDto->getCloudFileName();
        $filePath     = trailingslashit($tmpDirectory) . $fileName;
        $fileHandle   = fopen($filePath, 'a+');

        $downloadedChunkSize = 0;
        $chunkStart          = filesize($filePath);

        if ($chunkStart < $fileSize) {
            $remoteBackup = $this->auth->getBackup($fileId);
            $fileId = $remoteBackup['fileid'];

            $params = [
                'fileid'       => $fileId,
                'access_token' => $this->options['accessToken'],
            ];

            $url = 'https://' . $this->options['hostname'] . '/getfilelink?' . http_build_query($params);

            $args = [
                'headers' => [
                    'Content-Type'  => 'application/octet-stream',
                    'Authorization' => 'Bearer ' . $this->options['accessToken'],
                ],
            ];

            $response = $this->getRequestBody($url, $args);
            if (empty($response['hosts'][0]) || empty($response['path'])) {
                return 0;
            }


            $url = 'https://' . $response['hosts'][0] . $response['path'];
            $response = $this->downloadInChunks($url, $chunkStart);

            fwrite($fileHandle, $response);
            $contentLength       = strlen($response);
            $downloadedChunkSize = $contentLength > 0 ? $chunkStart + $contentLength : $downloadedChunkSize;
        }

        // close the file pointer
        fclose($fileHandle);

        if ($chunkStart >= $fileSize || $downloadedChunkSize >= $fileSize) {
            $backupsDirectory = WPStaging::make(BackupsFinder::class)->getBackupsDirectory();
            $destination      = $backupsDirectory . $fileName;
            // move backup from tmp to backup folder
            rename($filePath, $destination);
            throw new FinishedQueueException($fileName);
        }

        return $downloadedChunkSize;
    }

    /**
     * @todo check if Auth::deleteFile can be used
     * @param string $fileId
     * @return bool
     */
    public function deleteFile(string $fileId): bool
    {
        try {
            $remoteBackup = $this->auth->getBackup($fileId);
            $this->auth->deleteFileById($remoteBackup['fileid']);

            // Update backup retention.
            $backupFileName = empty($_REQUEST['backupFileName']) ? "" : sanitize_text_field($_REQUEST['backupFileName']);
            $this->auth->unsetStorageFromRetainedBackups($backupFileName);

            return true;
        } catch (\Throwable $th) {
        }

        return false;
    }

    /**
     * @return array
     */
    public function getBackups(): array
    {
        return $this->auth->getBackups();
    }

    private function downloadInChunks($downloadUrl, $chunkStart)
    {
        $chunkSize = $this->chunkSize;
        $chunkEnd  = $chunkStart + $chunkSize;
        $args      = [
            'headers' => [
                'Range' => "bytes=$chunkStart-$chunkEnd",
            ],
        ];

        return $this->getRequestBody($downloadUrl, $args, false);
    }
}
