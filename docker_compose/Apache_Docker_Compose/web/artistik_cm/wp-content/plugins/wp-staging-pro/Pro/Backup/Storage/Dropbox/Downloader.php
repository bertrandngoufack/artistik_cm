<?php

namespace WPStaging\Pro\Backup\Storage\Dropbox;

use WPStaging\Backup\Service\BackupsFinder;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Backup\Exceptions\StorageException;
use WPStaging\Framework\Queue\FinishedQueueException;
use WPStaging\Pro\Backup\Dto\Job\JobCloudDownloadDataDto;
use WPStaging\Pro\Backup\Storage\RemoteDownloaderInterface;
use WPStaging\Pro\Backup\Storage\Dropbox\Auth;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

use function WPStaging\functions\debug_log;

class Downloader implements RemoteDownloaderInterface
{
    /** @var string */
    const DROPBOX_API_CONTENT_URL = 'https://content.dropboxapi.com/2/files';

    /** @var string */
    const DROPBOX_API_FILE_DOWNLOAD_URL = self::DROPBOX_API_CONTENT_URL . '/download';

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
            $this->error = 'Fail to refresh the access token, the process should resume automatically, if the error persists please reconnect to your dropbox account.';
            return;
        }

        if (!$this->auth->isAuthenticated()) {
            $this->error = 'Dropbox is not authenticated. Backup is still available locally.';
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
     * Download backup content from dropbox to server
     *
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
        $chunkSize           = $this->chunkSize;
        $chunkEnd            = $chunkStart + $chunkSize;

        if ($chunkStart < $fileSize) {
            $dropboxArgs = [
                'path' => $fileId,
            ];

            $args = [
                'headers' => [
                    'Content-Type'    => 'application/octet-stream',
                    'Authorization'   => 'Bearer ' . $this->options['accessToken'],
                    'Dropbox-API-Arg' => json_encode($dropboxArgs),
                    'Range'           => sprintf('bytes=%s-%s', empty($chunkStart) ? 0 : $chunkStart, $chunkEnd),
                ],
            ];

            $response = $this->runRemoteRequest(self::DROPBOX_API_FILE_DOWNLOAD_URL, $args);
            if (!$response) {
                return 0;
            }

            fwrite($fileHandle, $response);
            $contentLength = strlen($response);
            $downloadedChunkSize = intval($contentLength) > 0 ? $chunkEnd : $downloadedChunkSize;
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

    /**
     * @todo check if WPStaging\Pro\Backup\Storage\Dropbox\Auth::deleteFile can be used
     * @param string $fileId
     * @return bool
     */
    public function deleteFile(string $fileId): bool
    {
        $args = [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $this->options['accessToken'],
            ],
            'body'    => json_encode([
                'path' => $fileId,
            ]),
        ];

        $response = $this->runRemoteRequest(Auth::DROPBOX_API_V2_URL . '/files/delete_v2', $args);
        $response = json_decode($response, true);
        if (!$response) {
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

    /**
     * @param  string $url
     * @param  array $args
     * @throws StorageException
     * @return string
     */
    protected function runRemoteRequest(string $url, array $args = []): string
    {
        $defaults = [
            'timeout'     => 120,
            'httpversion' => '1.0',
            'sslverify'   => true,
        ];
        $args     = wp_parse_args($args, $defaults);
        $response = wp_remote_post($url, $args);

        if (is_wp_error($response) || (wp_remote_retrieve_response_code($response) !== 200 && wp_remote_retrieve_response_code($response) !== 206)) {
            $errorMessage = is_wp_error($response) ? $response->get_error_message() : wp_remote_retrieve_body($response);
            $this->error = $errorMessage;

            $responseBody = json_decode(wp_remote_retrieve_body($response), true);

            debug_log("WP STAGING Dropbox error in downloader. url: $url; Error Message: $errorMessage");
            throw new StorageException('Dropbox error in runRemoteRequest: ' . $errorMessage);
        }

        $responseBody = wp_remote_retrieve_body($response);
        return $responseBody;
    }
}
