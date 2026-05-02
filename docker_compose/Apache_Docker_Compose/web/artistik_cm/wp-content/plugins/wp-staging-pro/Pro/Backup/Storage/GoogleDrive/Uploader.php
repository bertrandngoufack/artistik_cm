<?php

namespace WPStaging\Pro\Backup\Storage\GoogleDrive;

use Exception;
use WPStaging\Core\WPStaging;
use WPStaging\Backup\Dto\Interfaces\RemoteUploadDtoInterface;
use WPStaging\Framework\Filesystem\FileObject;
use WPStaging\Framework\Queue\FinishedQueueException;
use WPStaging\Framework\Utils\Strings;
use WPStaging\Framework\Job\Exception\DiskNotWritableException;
use WPStaging\Pro\Backup\Storage\RemoteUploaderInterface;
use WPStaging\Backup\WithBackupIdentifier;
use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Framework\Traits\HttpRequestTrait;
use WPStaging\Pro\Backup\Task\Tasks\JobBackup\AbstractStorageTask;
use WPStaging\Backup\Dto\Job\JobBackupDataDto;
use WPStaging\Framework\Facades\Hooks;

use function WPStaging\functions\debug_log;

class Uploader implements RemoteUploaderInterface
{
    use WithBackupIdentifier;
    use HttpRequestTrait;

    /** @var string */
    const FILTER_GOOGLE_DRIVE_BYPASS_DISK_SPACE = 'wpstg.googleDrive.bypassDiskSpace';

    /** @var RemoteUploadDtoInterface */
    private $jobDataDto;

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $filePath;

    /** @var string */
    private $fileName;

    /** @var string */
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

    /** @var string */
    private $driveType;

    /** @var string */
    private $sharedDriveId;

    public function __construct(Auth $auth)
    {
        $this->error = false;
        $this->auth  = $auth;

        if (!$this->auth->isAuthenticated()) {
            $this->error = __('Google Drive is not authenticated. Backup is still available locally.', 'wp-staging');
            return;
        }

        $options                = $this->auth->getOptions();
        $this->folderName       = isset($options['folderName']) ? $this->auth->sanitizeGoogleDriveLocation($options['folderName']) : Auth::FOLDER_NAME;
        $this->maxBackupsToKeep = isset($options['maxBackupsToKeep']) ? $options['maxBackupsToKeep'] : 15;
        $this->maxBackupsToKeep = intval($this->maxBackupsToKeep);
        $this->maxBackupsToKeep = $this->maxBackupsToKeep > 0 ? $this->maxBackupsToKeep : 15;
        $this->driveType        = isset($options['driveType']) ? $options['driveType'] : Auth::DRIVE_TYPE_PERSONAL;
        $this->sharedDriveId    = isset($options['sharedDriveId']) ? $options['sharedDriveId'] : '';
        $this->folderId         = $this->auth->getFolderIdByLocation($this->folderName, $this->driveType, $this->sharedDriveId);
    }

    public function getProviderName(): string
    {
        return 'Google Drive';
    }

    /**
     * @param LoggerInterface $logger
     * @param RemoteUploadDtoInterface $jobDataDto
     * @param int $chunkSize = MB_IN_BYTES
     * @return void
     */
    public function setupUpload(LoggerInterface $logger, RemoteUploadDtoInterface $jobDataDto, $chunkSize = MB_IN_BYTES)
    {
        $this->logger     = $logger;
        $this->jobDataDto = $jobDataDto;
        $this->chunkSize  = $chunkSize;

        $this->folderId = $this->createBackupsDestination();
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
    public function setBackupFilePath($backupFilePath, $fileName)
    {
        $this->fileName   = $fileName;
        $this->filePath   = $backupFilePath;
        $this->fileObject = new FileObject($this->filePath, FileObject::MODE_READ);

        $this->doExceedGoogleDiskLimit($this->fileObject->getSize());

        $uploadMetadata = (array)$this->jobDataDto->getRemoteStorageMeta();
        if (!array_key_exists($this->fileName, $uploadMetadata)) {
            $this->folderId = $this->createBackupsDestination();
            $resumeURI      = $this->getResumeUri();
            $this->setMetadata($resumeURI, 0);
            if (is_object($this->logger)) {
                $this->logger->info('Starting upload of file:' . $this->fileName . '.');
            }

            return true;
        }

        return true;
    }

    /**
     * @return int
     *
     * @throws FinishedQueueException
     */
    public function chunkUpload()
    {
        if (empty($this->jobDataDto->getRemoteStorageMeta()[$this->fileName])) {
            debug_log($this->getProviderName() . ' Fail to start upload!');
            return 0;
        }

        $chunkSize    = 0;
        $fileMetadata = $this->jobDataDto->getRemoteStorageMeta()[$this->fileName];
        $offset       = $fileMetadata['Offset'];

        $this->fileObject->fseek($offset);
        $chunk = $this->fileObject->fread($this->chunkSize);
        $chunkSize = $this->nextChunk($fileMetadata['ResumeURI'], $chunk, $offset);

        $offset += $chunkSize;
        $this->setMetadata($fileMetadata['ResumeURI'], $offset);
        return $chunkSize;
    }

    /**
     * @param string $filePath
     * @param string $remoteFileName
     *
     * @throws Exception
     *
     * @return bool
     */
    public function uploadFile($filePath, $remoteFileName = ''): bool
    {
        $this->chunkSize  = AbstractStorageTask::CHUNK_SIZE * MB_IN_BYTES;
        $this->fileName   = $remoteFileName;
        $this->fileObject = new FileObject($filePath, FileObject::MODE_READ);
        $this->jobDataDto = WPStaging::make(JobBackupDataDto::class);
        $this->setBackupFilePath($filePath, $remoteFileName);
        try {
            $this->chunkUpload();
        } catch (FinishedQueueException $exception) {
            return true;
        } catch (Exception $ex) {
            throw new Exception(" error in uploadFile: " . $ex->getMessage());
        }

        return true;
    }

    public function stopUpload()
    {
        // no-op
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

    public function deleteOldestBackups(): bool
    {
        $retainedBackups = $this->auth->getRetainedBackups();
        if (count($retainedBackups) < $this->maxBackupsToKeep) {
            return true;
        }

        $remoteBackupsFiles = $this->auth->getBackups();

        foreach ($retainedBackups as $retainedBackupId => $retainedBackup) {
            if (count($retainedBackups) < $this->maxBackupsToKeep) {
                break;
            }

            foreach ($remoteBackupsFiles as $file) {
                $fileName = $file->name;
                if (strpos($fileName, $retainedBackupId) !== false) {
                    try {
                        $this->auth->deleteRemoteFileById($file->id);
                    } catch (\Throwable $th) {
                    }
                }
            }

            $this->auth->unsetStorageFromRetainedBackups($retainedBackupId);
            unset($retainedBackups[$retainedBackupId]);
        }

        return true;
    }

    /**
     * @param array $uploadsToVerify
     * @return bool
     */
    public function verifyUploads(array $uploadsToVerify): bool
    {
        $files            = $this->auth->getBackups();
        $uploadsConfirmed = [];
        foreach ($files as $file) {
            if (empty($file->name) || empty($file->size)) {
                continue;
            }

            $fileName = $file->name;
            if (!array_key_exists($fileName, $uploadsToVerify)) {
                continue;
            }

            $fileSize = (int)$file->size;
            $toVerify = $uploadsToVerify[$fileName];
            if ($toVerify['size'] !== $fileSize) {
                continue;
            }

            $uploadsConfirmed[] = $fileName;
        }

        $this->auth->saveStorageAccountInfo();

        return count($uploadsConfirmed) === count($uploadsToVerify);
    }

    /**
     * @return string
     */
    protected function getResumeUri(): string
    {
        $options = $this->auth->getOptions();
        if (empty($options['accessToken'])) {
            return '';
        }

        $body = [
            'parents' => [$this->folderId],
            'name'    => $this->fileName,
        ];
        $args = [
            'headers' => [
                'Content-Type'            => 'application/json; charset=UTF-8',
                'Authorization'           => 'Bearer ' . $options['accessToken'],
                'X-Upload-Content-Type'   => 'application/octet-stream',
                'X-Upload-Content-Length' => $this->fileObject->getSize(),
            ],
            'body'    => json_encode($body),
            'method'  => 'POST',
        ];

        $url = Auth::GOOGLEDRIVE_API_V3_UPLOAD_URL . '/files?uploadType=resumable';
        $url = $this->auth->appendSharedDriveParams($url, $this->driveType, $this->sharedDriveId);
        try {
            /**
             * @see https://developers.google.com/workspace/drive/api/guides/manage-uploads#initial-request
             */
            $response        = $this->getRemoteRequest($url, $args);
            $responseHeaders = wp_remote_retrieve_headers($response);
            if (empty($responseHeaders['location'])) {
                debug_log('upload url is missing. Should Retry...');
                return '';
            }

            return $responseHeaders['location'];
        } catch (\Throwable $th) {
            if ($th->getCode() === 403) {
                throw new DiskNotWritableException("Insufficient Storage on your Google Drive, please increase the disk space and try again. Backup is still available in local.");
            }

            debug_log('Fail to get resume uri, error message: ' . $th->getMessage());
        }

        return '';
    }

    /**
     * @param  string $uploadUrl
     * @param  string $chunk
     * @param  int $offset
     *
     * @throws FinishedQueueException
     *
     * @return int
     */
    protected function nextChunk(string $uploadUrl, string $chunk, int $offset)
    {
        $options  = $this->auth->getOptions();
        $fileSize = $this->fileObject->getSize();

        $args = [
            'headers' => [
                'Authorization'  => 'Bearer ' . $options['accessToken'],
                'Content-Length' => strlen($chunk),
                'Content-Range'  => "bytes {$offset}-" . ($offset + strlen($chunk) - 1) . "/{$fileSize}",
            ],
            'body'    => $chunk,
            'method'  => 'PUT',
        ];

        $responseCode = 0;
        try {
            $response     = $this->getRemoteRequest($uploadUrl, $args);
            $responseCode = wp_remote_retrieve_response_code($response);
        } catch (\Throwable $th) {
            if ($th->getCode() === 403) {
                throw new DiskNotWritableException("Insufficient Storage on your Google Drive, please increase the disk space and try again. Backup is still available in local.");
            }

            debug_log('Fail to upload next chunk, error message: ' . $th->getMessage());
        }

        if ($responseCode === 308) {
            return strlen($chunk);
        } elseif (in_array($responseCode, [200, 201], true)) {
            // Upload complete.
            throw new FinishedQueueException();
        }

        return strlen('');
    }

    protected function setMetadata($resumeURI, $offset)
    {
        $this->jobDataDto->setRemoteStorageMeta([
            $this->fileName => [
                'ResumeURI' => $resumeURI,
                'Offset'    => $offset,
            ],
        ]);
    }

    /**
     * @param int $backupSize
     * @return void
     */
    private function doExceedGoogleDiskLimit(int $backupSize)
    {
        if (Hooks::applyFilters(self::FILTER_GOOGLE_DRIVE_BYPASS_DISK_SPACE, false)) {
            return;
        }

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
     * @return string
     */
    protected function createBackupsDestination(): string
    {
        $location       = $this->getBackupsLocation();
        $locationURI    = $this->auth->getFoldersFromLocation($location);
        $parentFolderId = ($this->driveType !== Auth::DRIVE_TYPE_PERSONAL && !empty($this->sharedDriveId)) ? $this->sharedDriveId : Auth::DRIVE_ROOT_FOLDER_ID;
        foreach ($locationURI as $folder) {
            $folderId = $this->auth->getFolderIdByName($folder, $parentFolderId, $this->driveType, $this->sharedDriveId);
            if (!empty($this->auth->getError())) {
                $this->logger->error($this->auth->getError() . '.');
            }

            if ($folderId === '' && !empty($parentFolderId)) {
                $folderId = $this->createFolder($folder, $parentFolderId);
            }

            if ($folderId === '') {
                return ''; // Early bail: fail to get or to create the current folder, no need to continue the loop. Something is wrong!
            }

            $parentFolderId = $folderId;
        }

        return $folderId;
    }

    /**
     * @param  string $path
     * @param  string $parentFolderId
     * @return string
     */
    private function createFolder(string $path, string $parentFolderId): string
    {
        if (empty($parentFolderId)) {
            return '';
        }

        // Check if folder already exists before creating
        $existingFolderId = $this->auth->getFolderIdByName($path, $parentFolderId, $this->driveType, $this->sharedDriveId);
        if (!empty($existingFolderId)) {
            return $existingFolderId;
        }

        $options = $this->auth->getOptions();
        $body = [
            'parents'  => [$parentFolderId],
            'mimeType' => 'application/vnd.google-apps.folder',
            'name'     => $path,
        ];
        $args = [
            'headers' => [
                'Content-Type'  => 'application/json; charset=UTF-8',
                'Authorization' => 'Bearer ' . $options['accessToken'],
            ],
            'body'    => json_encode($body),
            'method'  => 'POST',
        ];

        $url = Auth::GOOGLEDRIVE_API_V3_BASE_URL . '/files';
        $url = $this->auth->appendSharedDriveParams($url, $this->driveType, $this->sharedDriveId);
        try {
            $response = $this->getRequestBody($url, $args);
            if (!empty($response['id'])) {
                return $response['id'];
            }
        } catch (\Throwable $th) {
            debug_log("Fail to create folder. Error: " . $th->getMessage());
        }

        return '';
    }

    /**
     * @return string
     */
    protected function getBackupsLocation(): string
    {
        $options = $this->auth->getOptions();
        return !empty($options['folderName']) ? $options['folderName'] : Auth::FOLDER_NAME;
    }
}
