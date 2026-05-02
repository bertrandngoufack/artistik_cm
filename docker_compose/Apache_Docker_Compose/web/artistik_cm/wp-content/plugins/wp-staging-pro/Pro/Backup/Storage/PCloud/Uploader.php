<?php

namespace WPStaging\Pro\Backup\Storage\PCloud;

use Exception;
use WPStaging\Backup\Dto\Interfaces\RemoteUploadDtoInterface;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Filesystem\FileObject;
use WPStaging\Framework\Utils\Strings;
use WPStaging\Framework\Traits\HttpRequestTrait;
use WPStaging\Backup\Dto\Job\JobBackupDataDto;
use WPStaging\Backup\Exceptions\StorageException;
use WPStaging\Backup\WithBackupIdentifier;
use WPStaging\Framework\Queue\FinishedQueueException;
use WPStaging\Pro\Backup\Dto\Job\JobCloudDownloadDataDto;
use WPStaging\Pro\Backup\Storage\RemoteUploaderInterface;
use WPStaging\Pro\Backup\Storage\PCloud\Auth;
use WPStaging\Pro\Backup\Task\Tasks\JobBackup\AbstractStorageTask;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

use function WPStaging\functions\debug_log;

class Uploader implements RemoteUploaderInterface
{
    use WithBackupIdentifier;
    use HttpRequestTrait;

    /** @var RemoteUploadDtoInterface|JobCloudDownloadDataDto */
    private $jobDataDto;

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $filePath;

    /** @var string */
    private $fileName;

    /**
     * Backup folder id
     * @var string
     */
    private $folderId;

    /** @var string */
    private $folderName;

    /** @var int */
    private $maxBackupsToKeep;

    /** @var FileObject */
    private $fileObject;

    /** @var int */
    private $chunkSize;

    /** @var Auth */
    private $auth;

    /** @var bool|string */
    private $error;

    /** @var array */
    protected $options;

    /** @var Strings */
    private $strings;

    /** @var Directory */
    private $dirAdapter;

    public function __construct(Auth $auth, Strings $strings, Directory $directory)
    {
        $this->auth = $auth;
        if (!$this->auth->testConnection() && !$this->auth->refreshToken()) {
            $this->error = esc_html__('Fail to refresh the access token, the process should resume automatically, if the error persists please reconnect to your remote storage account.', 'wp-staging');
            return;
        }

        if (!$this->auth->isAuthenticated()) {
            $this->error = esc_html__('Remote Storage is not authenticated. Backup is still available locally.', 'wp-staging');
            return;
        }

        $this->error            = false;
        $this->strings          = $strings;
        $this->dirAdapter       = $directory;
        $this->options          = $this->auth->getOptions();
        $this->folderName       = isset($this->options['folderName']) ? $this->options['folderName'] : Auth::FOLDER_NAME;
        $this->maxBackupsToKeep = isset($this->options['maxBackupsToKeep']) && $this->options['maxBackupsToKeep'] > 0 ? intval($this->options['maxBackupsToKeep']) : 15;
    }

    public function getProviderName()
    {
        return 'PCloud';
    }

    /**
     * @param LoggerInterface $logger
     * @param RemoteUploadDtoInterface $jobDataDto
     * @param int $chunkSize
     * @return void
     */
    public function setupUpload(LoggerInterface $logger, RemoteUploadDtoInterface $jobDataDto, $chunkSize = 1 * MB_IN_BYTES)
    {
        $this->logger     = $logger;
        $this->jobDataDto = $jobDataDto;
        $this->chunkSize  = $chunkSize;
    }

    /**
     * @param int $backupSize
     */
    public function checkDiskSize($backupSize)
    {
        // no-op
    }

    /**
     * @param  string $backupFilePath
     * @param  string $fileName
     * @return bool
     */
    public function setBackupFilePath($backupFilePath, $fileName): bool
    {
        $this->fileName   = $fileName;
        $this->filePath   = $backupFilePath;
        $this->fileObject = new FileObject($this->filePath, FileObject::MODE_READ);

        $this->doExceedDiskLimit($this->fileObject->getSize());

        return true;
    }

    /**
     * @throws FinishedQueueException
     * @throws Exception
     *
     * @return int Number of bytes uploaded
     */
    public function chunkUpload(): int
    {
        $this->options = $this->auth->getOptions();
        if (empty($this->options['accessToken'])) {
            throw new Exception("User is not authenticated");
        }

        if (isset($this->jobDataDto->getRemoteStorageMeta()[$this->fileName]) && isset($this->jobDataDto->getRemoteStorageMeta()[$this->fileName]['Offset'])) {
            $fileMetadata = $this->jobDataDto->getRemoteStorageMeta()[$this->fileName];
            $offset       = $fileMetadata['Offset'];
        } else {
            $offset = 0;
            unset($this->options['uploadId']);
            $this->auth->saveOptions($this->options);
        }

        $this->fileObject->fseek($offset);
        $chunk = $this->fileObject->fread($this->chunkSize);

        $chunkSize = strlen($chunk);
        $fileSize  = $this->fileObject->getSize();
        if ($offset === 0 && !isset($this->options['uploadId'])) {
            $chunkSize = $this->startUploadSession();
            // start uploading right away!
            if (isset($this->options['uploadId'])) {
                $chunkSize = $this->appendUploadSession((int)$offset, $chunk);
            }
        } elseif (isset($this->options['uploadId'])) {
            $chunkSize = $this->appendUploadSession((int)$offset, $chunk);
        } else {
            debug_log('WP STAGING should had stop upload: ' . $this->fileName);
            throw new FinishedQueueException();
        }

        // finish upload task if the file is fully uploaded and upload session is closed
        if (!isset($this->options['uploadId']) && ($chunkSize + $offset) >= $fileSize) {
            debug_log('WP STAGING stopping upload: ' . $this->fileName);
            throw new FinishedQueueException();
        }

        // close the upload session if the file is fully uploaded by the first chunk
        if (isset($this->options['uploadId']) && ($chunkSize + $offset) >= $fileSize) {
            $this->finishUploadSession();
            throw new FinishedQueueException();
        }

        return $chunkSize;
    }

    /**
     * @param string $filePath
     * @param string $remoteFileName
     *
     * @throws Exception
     * @return bool
     */
    public function uploadFile($filePath, $remoteFileName = ''): bool
    {
        $this->chunkSize  = AbstractStorageTask::CHUNK_SIZE * MB_IN_BYTES;
        $this->jobDataDto = WPStaging::make(JobBackupDataDto::class);
        $this->setBackupFilePath($filePath, $remoteFileName);

        try {
            $this->chunkUpload();
        } catch (FinishedQueueException $exception) {
            return true;
        } catch (Exception $ex) {
            throw new Exception($this->getProviderName() . " error in uploadFile: " . $ex->getMessage());
        }

        return true;
    }

    /**
     * @return void
     */
    public function stopUpload()
    {
        unset($this->options['uploadId']);
    }

    /** @return string */
    public function getError()
    {
        return $this->error;
    }

    /**
     * WPStaging\Pro\Backup\Storage\RemoteUploaderInterface::getBackups
     * @return array
     */
    public function getBackups()
    {
        return $this->auth->getBackups();
    }

    /**
     * @return bool
     */
    public function deleteOldestBackups(): bool
    {
        $retainedBackups = $this->auth->getRetainedBackups();
        if (empty($this->maxBackupsToKeep)) {
            $this->maxBackupsToKeep = 15; // should not happen!
            debug_log('Fail to find default maximum backup to keep!');
        }

        if (count($retainedBackups) < $this->maxBackupsToKeep) {
            return true;
        }

        $remoteBackupsFiles = $this->auth->getBackups();

        foreach ($retainedBackups as $retainedBackupId => $retainedBackup) {
            if (count($retainedBackups) < $this->maxBackupsToKeep) {
                break;
            }

            $this->deleteBackupFiles($retainedBackupId, $remoteBackupsFiles);

            $this->auth->unsetStorageFromRetainedBackups($retainedBackupId);
            unset($retainedBackups[$retainedBackupId]);
        }

        return true;
    }

    /**
     * @param  array $uploadsToVerify
     * @return bool
     */
    public function verifyUploads(array $uploadsToVerify): bool
    {
        $files            = $this->auth->getBackups();
        $uploadsConfirmed = [];
        foreach ($files as $file) {
            if (empty($file['name']) || empty($file['size'])) {
                continue;
            }

            $fileName = $file['name'];
            if (!array_key_exists($fileName, $uploadsToVerify)) {
                continue;
            }

            $fileSize = (int)$file['size'];
            $toVerify = $uploadsToVerify[$fileName];
            if ($toVerify['size'] !== $fileSize) {
                continue;
            }

            $checksums = $this->getRemoteFileChecksum($file['fileid']);
            if (empty($checksums['sha1']) || $checksums['sha1'] !== $toVerify['hash']) {
                $this->logger->warning('Cannot verify file checksum');
            }

            $uploadsConfirmed[] = $fileName;
        }

        $this->auth->saveStorageAccountInfo();

        if (count($uploadsConfirmed) !== count($uploadsToVerify)) {
            debug_log("Fail to confirm uploads. Uploads confirmed: " . print_r($uploadsConfirmed, true) . "; Uploads to verify: " . print_r($uploadsToVerify, true));
        }

        return count($uploadsConfirmed) === count($uploadsToVerify);
    }

    /**
     * @return int Number of bytes uploaded
     */
    protected function startUploadSession(int $retry = 0): int
    {
        $url = $this->auth->getPCloudHostname() . "/upload_create";

        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->options['accessToken'],
            ],
            'method'  => 'POST',
        ];

        try {
            $response = $this->getRequestBody($url, $args);
            if (!isset($response['uploadid'])) {
                debug_log($this->getProviderName() . ' upload id is missing. Should Retry...');
                return 0;
            }

            $this->setMetadata(0);
            $this->options['uploadId'] = $response['uploadid'];
            $this->auth->saveOptions($this->options);
        } catch (\Throwable $th) {
            if ($this->auth->isRetryableError($th) && $retry < 3) {
                debug_log($this->getProviderName() . ': retryable error in startUploadSession, attempt ' . ($retry + 1) . '. Waiting 1s. Error: ' . $th->getMessage());
                sleep(1);
                return $this->startUploadSession($retry + 1);
            }

            debug_log($this->getProviderName() . ' fail to start upload session. Error message: ' . $th->getMessage());
        }

        return 0;
    }

    /**
     * @param  int $offset
     * @param  string $chunk Chunk of data to upload
     * @param  int $retry
     *
     * @return int Number of bytes uploaded
     */
    protected function appendUploadSession(int $offset, string $chunk, int $retry = 0): int
    {
        $url = $this->auth->getPCloudHostname() . "/upload_write";
        $args = [
            'body'   => $chunk,
            'method' => 'PUT',
        ];
        $params = [
            'access_token' => $this->options['accessToken'],
            'uploadid'     => $this->options['uploadId'],
            'uploadoffset' => $offset,
        ];
        $url = $url . '?' . http_build_query($params);
        try {
            $response = $this->getRequestBody($url, $args);
            if (!isset($response['result'])) {
                debug_log($this->getProviderName() . ': append upload failing. Should Retry...');
                return 0;
            }
        } catch (\Throwable $th) {
            if ($this->auth->isRetryableError($th) && $retry < 3) {
                debug_log($this->getProviderName() . ': retryable error, attempt ' . ($retry + 1) . '. Waiting 1s. Error: ' . $th->getMessage());
                sleep(1);
                return $this->appendUploadSession((int)$offset, $chunk, $retry + 1);
            }

            debug_log("Fail to append upload. Error: " . $th->getMessage());
            return 0;
        }

        $this->setMetadata($offset + strlen($chunk));
        return strlen($chunk);
    }

    /**
     * @throws FinishedQueueException
     * @throws Exception
     * @throws StorageException
     *
     * @return int Returns 0 if upload failed so it can be retried.
     */
    protected function finishUploadSession(int $retry = 0): int
    {
        $url = $this->auth->getPCloudHostname() . "/upload_save";

        $args = [
            'method' => 'POST',
        ];
        $params = [
            'uploadid'     => $this->options['uploadId'],
            'name'         => $this->fileName,
            'folderid'     => $this->auth->getBackupsDestinationId(),
            'access_token' => $this->options['accessToken'],
        ];

        $url = $url . '?' . http_build_query($params);
        try {
            $response = $this->getRequestBody($url, $args);
            if (!isset($response['result'])) {
                throw new Exception("Error to finish upload process. No result from server!");
            }

            if ($response['result'] === 2008) {
                throw new StorageException("Insufficient Storage on your PCloud, please increase the disk space and try again. Backup is still available in local.");
            }

            if ($response['result'] !== 0) {
                return 0;
            }
        } catch (\Throwable $th) {
            if ($this->auth->isRetryableError($th) && $retry < 3) {
                debug_log($this->getProviderName() . ': retryable error in finishUploadSession, attempt ' . ($retry + 1) . '. Waiting 1s. Error: ' . $th->getMessage());
                sleep(1);
                return $this->finishUploadSession($retry + 1);
            }

            throw $th;
        }

        unset($this->options['uploadId']);
        $this->auth->saveOptions($this->options);

        $this->auth->saveStorageAccountInfo();
        throw new FinishedQueueException();
    }

    /**
     * @param  int $offset
     * @return void
     */
    protected function setMetadata($offset = 0)
    {
        $this->jobDataDto->setRemoteStorageMeta([
            $this->fileName => [
                'Offset' => $offset,
            ],
        ]);
    }

    /**
     * @param  string $fileid
     *
     * @return string|array
     */
    protected function getRemoteFileChecksum(string $fileid)
    {
        $url  = $this->auth->getPCloudHostname() . "/checksumfile";
        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->options['accessToken'],
            ],
            'method'  => 'GET',
        ];
        $params = [
            'fileid' => $fileid,
        ];

        $url = $url . '?' . http_build_query($params);
        try {
            return $this->getRequestBody($url, $args);
        } catch (\Throwable $th) {
            debug_log("Failed to delete old backups. Error message: " . $th->getMessage());
            return [];
        }
    }

    /**
     * @param int $backupSize
     * @return void
     */
    private function doExceedDiskLimit(int $backupSize)
    {
        $options    = $this->auth->getOptions();
        $totalQuota = $options['storageInfo']['allocation']['allocated'] ?? '';
        $usedQuota  = $options['storageInfo']['used'] ?? '';
        if (!is_numeric($totalQuota) || !is_numeric($usedQuota)) {
            if (!empty($this->logger)) {
                $this->logger->warning('Unable to get size of used or available storage space. Continuing with Upload!');
            }

            return;
        }

        $availableQuota = $totalQuota - $usedQuota;
        if (empty($availableQuota) || !is_numeric($availableQuota)) {
            return;
        }

        if ($backupSize > $availableQuota && !empty($this->logger)) {
            $availableQuota = max(0, $availableQuota); // to avoid negative cases, when the disk space is exceeded.
            $this->logger->warning(sprintf('The disk size might be exceeded and upload might fail. Increase pCloud space or delete old data! Backup Size: %s. Space Available: %s. Continuing with Upload!', size_format($backupSize, 2), size_format($availableQuota, 2)));
        }

        return;
    }

    /**
     * Deletes remote backup files that match a given retained backup ID.
     *
     * @param string $retainedBackupId The ID of the retained backup to match.
     * @param array $remoteBackupsFiles List of remote backup files to check.
     *
     * @return void
     */
    private function deleteBackupFiles(string $retainedBackupId, array $remoteBackupsFiles)
    {
        foreach ($remoteBackupsFiles as $file) {
            $fileName = $file['name'];
            if (strpos($fileName, $retainedBackupId) !== false) {
                $this->auth->deleteFileById($file['fileid']);
            }
        }
    }
}
