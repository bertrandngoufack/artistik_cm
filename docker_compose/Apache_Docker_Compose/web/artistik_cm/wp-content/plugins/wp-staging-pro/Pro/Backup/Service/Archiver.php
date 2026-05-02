<?php

namespace WPStaging\Pro\Backup\Service;

use RuntimeException;
use WPStaging\Backup\BackupFileIndex;
use WPStaging\Backup\BackupHeader;
use WPStaging\Backup\Dto\Job\JobBackupDataDto;
use WPStaging\Backup\Entity\BackupMetadata;
use WPStaging\Backup\Entity\MultipartMetadata;
use WPStaging\Backup\FileHeader;
use WPStaging\Backup\Service\Archiver as BaseArchiver;
use WPStaging\Backup\Service\Compression\CompressionInterface;
use WPStaging\Backup\Service\ZlibCompressor;
use WPStaging\Framework\Adapter\PhpAdapter;
use WPStaging\Framework\Filesystem\FileObject;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Framework\Filesystem\PathIdentifier;
use WPStaging\Framework\Job\Dto\JobDataDto;
use WPStaging\Framework\Job\Exception\DiskNotWritableException;
use WPStaging\Framework\Job\Exception\ThresholdException;
use WPStaging\Framework\Traits\ResourceTrait;
use WPStaging\Framework\Utils\Cache\BufferedCache;
use WPStaging\Pro\Backup\Dto\Service\ArchiverDto;

class Archiver extends BaseArchiver
{
    use ResourceTrait;

    /** @var int */
    const CHUNK_SIZE = 256 * 1024; // 256 KB

    /** @var ArchiverDto */
    protected $archiverDto;

    /**
     * Category can be: empty string|null|false, plugins, mu-plugins, themes, uploads, other, database
     * Where empty string|null|false is used for single file backup,
     * And other is for files from wp-content not including plugins, mu-plugins, themes, uploads
     * @var string
     */
    private $category = '';

    /**
     * The current index of category in which appending files
     * Not used in single file backup
     * @var int
     */
    private $categoryIndex = 0;

    /**
     * @var CompressionInterface
     */
    private $zlibService;

    /**
     * @var bool
     */
    private $isCompressBackup = false;

    public function __construct(
        BufferedCache $cacheIndex,
        BufferedCache $tempBackup,
        PathIdentifier $pathIdentifier,
        JobDataDto $jobDataDto,
        ArchiverDto $archiverDto,
        PhpAdapter $phpAdapter,
        BackupFileIndex $backupFileIndex,
        FileHeader $fileHeader,
        BackupHeader $backupHeader,
        Filesystem $filesystem,
        ZlibCompressor $zlibCompressor
    ) {
        parent::__construct(
            $cacheIndex,
            $tempBackup,
            $pathIdentifier,
            $jobDataDto,
            $archiverDto,
            $phpAdapter,
            $backupFileIndex,
            $fileHeader,
            $backupHeader,
            $filesystem
        );

        $this->isCompressBackup = $zlibCompressor->isCompressionEnabled();
        $this->zlibService      = $zlibCompressor->getService();
    }

    /**
     * @param bool $isCompressBackup
     * @return void
     */
    public function setShouldCompress(bool $isCompressBackup)
    {
        $this->isCompressBackup = $isCompressBackup;
    }

    /**
     * @param int $fileAppendTimeLimit
     * @return void
     */
    public function setFileAppendTimeLimit(int $fileAppendTimeLimit)
    {
        $this->tempBackup->setFileAppendTimeLimit($fileAppendTimeLimit);
        $this->tempBackupIndex->setFileAppendTimeLimit($fileAppendTimeLimit);
        self::$fileAppendMaxExecutionTimeInSeconds = $fileAppendTimeLimit;
    }

    /**
     * Setup temp backup file and temp files index file for the given job id,
     * If multipart backup category and category index are given, then they are used to create unique file names
     * @return void
     */
    public function setupTmpBackupFile()
    {
        $additionalInfo = empty($this->category) ? '' : $this->category . '_' . $this->categoryIndex . '_';
        $postFix = $additionalInfo . $this->jobDataDto->getId();

        $this->tempBackup->setFilename('temp_wpstg_backup_' . $postFix);
        $this->tempBackup->setLifetime(DAY_IN_SECONDS);

        $tempBackupIndexFilePrefix = 'temp_backup_index_';
        $this->tempBackupIndex->setFilename($tempBackupIndexFilePrefix . $postFix);
        $this->tempBackupIndex->setLifetime(DAY_IN_SECONDS);
    }

    /**
     * @param int $index
     * @param bool $isCreateBinaryHeader
     * @return void
     */
    public function setCategoryIndex(int $index, bool $isCreateBinaryHeader = true)
    {
        if (empty($index)) {
            $index = 0;
        }

        $this->categoryIndex = $index;
        $this->createArchiveFile($isCreateBinaryHeader);
    }

    /**
     * @param string $category
     * @param bool $isCreateBinaryHeader
     * @return void
     */
    public function setCategory(string $category = '', bool $isCreateBinaryHeader = false)
    {
        $this->category = $category;

        $this->createArchiveFile($isCreateBinaryHeader);
    }

    /**
     * @param int $fileSize
     * @param int $maxPartSize
     * @return bool
     */
    public function doExceedMaxPartSize(int $fileSize, int $maxPartSize): bool
    {
        $allowedSize     = $fileSize - $this->archiverDto->getWrittenBytesTotal();
        $sizeAfterAdding = $allowedSize + filesize($this->tempBackup->getFilePath());
        return $sizeAfterAdding >= $maxPartSize;
    }

    /**
     * @param int    $sizeBeforeAddingIndex
     * @param string $category
     * @param string $partName
     * @param int    $categoryIndex
     */
    public function generateBackupMetadataForBackupPart(int $sizeBeforeAddingIndex, string $category, string $partName, int $categoryIndex)
    {
        $this->category      = $category;
        $this->categoryIndex = $categoryIndex;
        $this->setupTmpBackupFile();
        $this->generateBackupMetadata($sizeBeforeAddingIndex, $partName);
    }

    /**
     * @return array
     */
    public function getFinalizeBackupInfo(): array
    {
        return [
            'category'              => $this->category,
            'index'                 => $this->categoryIndex,
            'filePath'              => $this->tempBackup->getFilePath(),
            'destination'           => $this->getDestinationPath(),
            'status'                => 'Pending',
            'sizeBeforeAddingIndex' => 0,
        ];
    }

    /**
     * @return string
     */
    public function getDestinationPath(): string
    {
        $extension = $this->isTempBackup ? self::TMP_BACKUP_EXTENSION : self::BACKUP_EXTENSION;
        if ($this->category !== '') {
            $index = ($this->categoryIndex + 1) . '.';
            $extension = $this->category . '.' . $index . $extension;
        }

        if ($this->isTempBackup) {
            return sprintf(
                '%s.%s',
                $this->jobDataDto->getId(),
                $extension
            );
        }

        return sprintf(
            '%s_%s_%s.%s',
            parse_url(get_home_url())['host'],
            current_time('Ymd-His'),
            $this->jobDataDto->getId(),
            $extension
        );
    }

    /**
     * @param BackupMetadata $backupMetadata
     * @param JobBackupDataDto $jobBackupDataDto
     * @return void
     */
    protected function setBackupMetadataCategoryInfo(BackupMetadata $backupMetadata, JobBackupDataDto $jobBackupDataDto)
    {
        $backupMetadata->setIndexPartSize($jobBackupDataDto->getCategorySizes());

        if (!$jobBackupDataDto->getIsMultipartBackup()) {
            return;
        }

        $splitMetadata = $backupMetadata->getMultipartMetadata();
        $splitMetadata = empty($splitMetadata) ? new MultipartMetadata() : $splitMetadata;
        $splitMetadata->setTotalFiles($jobBackupDataDto->getFilesInPart($this->category, $this->categoryIndex));
        $backupMetadata->setMultipartMetadata($splitMetadata);
    }

    protected function incrementFilesCount(JobBackupDataDto $jobBackupDataDto)
    {
        $jobBackupDataDto->setTotalFiles($jobBackupDataDto->getTotalFiles() + 1);
        if (!$jobBackupDataDto->getIsMultipartBackup()) {
            return;
        }

        $filesCount = $jobBackupDataDto->getFilesInPart($this->category, $this->categoryIndex);
        $jobBackupDataDto->setFilesInPart($this->category, $this->categoryIndex, $filesCount + 1);
    }

    /**
     * @return void
     */
    protected function setIndexPositionCreated()
    {
        $this->archiverDto->setIndexPositionCreated(true, $this->category, $this->categoryIndex);
    }

    /**
     * @return bool
     */
    protected function isIndexPositionCreated(): bool
    {
        return $this->archiverDto->isIndexPositionCreated($this->category, $this->categoryIndex);
    }

    /**
     * @param resource $resource
     * @param string $filePath
     *
     * @return int Bytes written
     * @throws DiskNotWritableException
     * @throws RuntimeException
     * @throws ThresholdException
     */
    protected function appendToArchiveFile($resource, string $filePath): int
    {
        if (!$this->isCompressBackup) {
            return parent::appendToArchiveFile($resource, $filePath);
        }

        try {
            $shouldCompress = $this->archiverDto->getIsCompressed();
        } catch (\Exception $e) {
            $shouldCompress = null;
        }

        if ($shouldCompress === null) {
            $shouldCompress = $this->shouldCompress($resource, $filePath);

            $this->archiverDto->setIsCompressed($shouldCompress);
        }

        if (!$shouldCompress) {
            return parent::appendToArchiveFile($resource, $filePath);
        }

        return $this->appendToArchiveFileWithCompression($resource, $filePath);
    }

    private function appendToArchiveFileWithCompression($resource, string $filePath): int
    {
        $newBytesRead         = 0;
        $totalBytesRead       = $this->archiverDto->getWrittenBytesTotal();
        $remainingBytesToRead = $this->archiverDto->getFileSize() - $totalBytesRead;

        if ($totalBytesRead === 0) {
            clearstatcache();
            $this->archiverDto->setStartOffset(filesize($this->tempBackup->getFilePath()) - $this->archiverDto->getFileHeaderSizeInBytes());
        }

        if ($remainingBytesToRead <= 0) {
            return 0;
        }

        fseek($resource, $totalBytesRead);
        do {
            $chunkToRead      = min(self::CHUNK_SIZE, $remainingBytesToRead);
            $unCompressedData = fread($resource, $chunkToRead);
            $compressedData   = $this->zlibService->compress($unCompressedData);
            if ($compressedData === false) {
                throw new RuntimeException('Failed to compress file: ' . $filePath);
            }

            $compressedBytes = strlen($compressedData);

            $newBytesWritten = $this->tempBackup->appendUnsafe(
                pack('N', $this->jobDataDto->getTotalChunks()) .
                pack('N', $compressedBytes) .
                $compressedData
            );

            $this->archiverDto->appendCompressedBytesTotal($newBytesWritten);

            $this->updateCompressedBackupBytesInfo($chunkToRead, $compressedBytes);

            $this->jobDataDto->incrementTotalChunks();

            $newBytesRead         += $chunkToRead;
            $remainingBytesToRead -= $chunkToRead;
        } while ($remainingBytesToRead > 0 && !$this->isFileAppendThreshold());

        // Append the end line when whole file is processed
        if ($remainingBytesToRead <= 0) {
            $this->tempBackup->appendUnsafe("\n");
            $this->incrementTotalFilesCompressed();
        }

        return $totalBytesRead + $newBytesRead;
    }

    protected function addNewFileHeaderToIndex(int $writtenBytes, int $startOffset): int
    {
        if (!$this->isCompressBackup) {
            $this->updateBackupBytesInfo($writtenBytes);
            // If compression is not enabled, use the parent method
            return parent::addNewFileHeaderToIndex($writtenBytes, $startOffset);
        }

        if (!$this->archiverDto->getIsCompressed()) {
            $this->updateBackupBytesInfo($writtenBytes);
            return parent::addNewFileHeaderToIndex($writtenBytes, $startOffset);
        }

        $this->fileHeader->setStartOffset($this->archiverDto->getStartOffset());
        $this->fileHeader->setIsCompressed(true);
        $this->fileHeader->setCompressedSize($this->archiverDto->getCompressedBytesTotal());
        $this->updateFileHeader($this->fileHeader, $this->tempBackup->getFilePath());

        $indexHeader = $this->fileHeader->getIndexHeader() . "\n";
        // Lets update the file header when file processing completes
        if (!$this->isIndexPositionCreated()) {
            return $this->tempBackupIndex->appendUnsafe($indexHeader);
        }

        $this->tempBackupIndex->deleteBottomBytes(strlen($indexHeader));
        $this->tempBackupIndex->appendUnsafe($indexHeader);

        return 0;
    }

    /**
     * Determines whether the file should be compressed based on its content and extension.
     *
     * @param resource $resource The file resource to check.
     * @param string $filePath The path of the file being processed.
     * @return bool True if the file should be compressed, false otherwise.
     */
    protected function shouldCompress($resource, string $filePath): bool
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        $doNotCompress = [

            /* ── Images ── */
            // Lossy raster
            'jpg',
            'jpeg',
            'jpe',
            'heic',
            'heif',
            'avif',
            'jxl',
            'webp',
            // Lossless-but-compressed raster
            'png',
            'gif',
            'tif',
            'tiff',

            /* ── Audio ── */
            // Lossy audio
            'mp3',
            'aac',
            'm4a',
            'ogg',
            'opus',
            'wma',
            // Lossless (still compressed) audio
            'flac',
            'alac',
            'ape',
            'wv',

            /* ── Video / multimedia containers ── */
            'mp4',
            'mkv',
            'mov',
            'webm',
            'flv',
            '3gp',
            'ts',
            'm2ts',
            'avi',

            /* ── Document & publishing ── */
            'docx',
            'xlsx',
            'pptx',
            'pdf',
            'epub',
            'chm',
            'svgz',

            /* ── Archives & package formats ── */
            'zip',
            'rar',
            '7z',
            'gz',
            'bz2',
            'xz',
            'lzma',
            'zst',
            'tar.gz',
            'tgz',
            'tar.bz2',
            'tar.xz',
            'dmg',
            'pkg',
            'jar',
            'war',
            'apk',
            'whl',
            'rpm',
            'deb',

            /* ── Executables / firmware blobs ── */
            'exe',
            'dll',
            'sys',
            'app',
            'wasm',
            'bin',
            'img',
            'fw',

            /* ── Fonts ── */
            'ttf',
            'otf',
            'woff',
            'woff2',

            /* ── Misc. binary data formats ── */
            'sqlite',
            'realm',
            'parquet',
            'orc',
            'mbtiles',
            'pbf',
            'blk',
            'hdf5',
            'nc',
            'fits',
            'data',
        ];

        // Early bail: Infer that it's binary from the extension.
        if (in_array($extension, $doNotCompress, true)) {
            return false;
        }

        $forceCompress = ['sql'];

        // Early bail: The given extension needs to be compressed.
        if (in_array($extension, $forceCompress, true)) {
            return true;
        }

        $chunkToRead = min(self::CHUNK_SIZE, $this->fileHeader->getUncompressedSize());
        if ($chunkToRead <= 0) {
            return false;
        }

        fseek($resource, 0);
        $data = fread($resource, $chunkToRead);

        // Early bail: Empty string.
        if (empty($data)) {
            return false;
        }

        /**
         * It should compress if the string complies with UTF-8,
         * which we infer it's a text file.
         */
        return preg_match('//u', $data) !== false;
    }

    /**
     * @param FileHeader $fileHeader
     * @param string $tempBackupPath
     * @return void
     */
    protected function updateFileHeader(FileHeader $fileHeader, string $tempBackupPath)
    {
        $compressedBackup = new FileObject($tempBackupPath, "r+");
        $compressedBackup->fseek($fileHeader->getStartOffset());
        $compressedBackup->fwrite($fileHeader->getFileHeader());
        $compressedBackup = null;
    }

    protected function updateBackupBytesInfo(int $uncompressedBytes)
    {
        if (!$this->jobDataDto instanceof JobBackupDataDto) {
            return;
        }

        $this->jobDataDto->addBackupSizeUncompressed($uncompressedBytes);
        $this->jobDataDto->addBackupSizeCompressed($uncompressedBytes);
    }

    protected function updateCompressedBackupBytesInfo(int $uncompressedBytes, int $compressedBytes)
    {
        if (!$this->jobDataDto instanceof JobBackupDataDto) {
            return;
        }

        $this->jobDataDto->setIsCompressed(true);
        $this->jobDataDto->addBackupSizeUncompressed($uncompressedBytes);
        $this->jobDataDto->addBackupSizeCompressed($compressedBytes);
    }

    protected function incrementTotalFilesCompressed()
    {
        if (!$this->jobDataDto instanceof JobBackupDataDto) {
            return;
        }

        $this->jobDataDto->incrementTotalFilesCompressed();
    }
}
