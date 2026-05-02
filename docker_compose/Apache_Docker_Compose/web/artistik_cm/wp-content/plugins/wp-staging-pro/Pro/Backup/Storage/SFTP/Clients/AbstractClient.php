<?php

namespace WPStaging\Pro\Backup\Storage\SFTP\Clients;

use WPStaging\Backup\Exceptions\StorageException;

use function WPStaging\functions\debug_log;

/**
 * Abstract base class for FTP/SFTP clients with shared directory creation logic
 */
abstract class AbstractClient
{
    /**
     * Ensures that a remote directory exists.
     *
     * If the directory does not exist, it is created recursively along with any missing parent directories.
     */
    public function makeDirectory(string $directory, bool $recursive = true): bool
    {
        if (!$this->maybeLogin()) {
            return false;
        }

        $directory = rtrim($directory, '/');
        if (empty($directory)) {
            return true;
        }

        if ($this->directoryExists($directory)) {
            return true;
        }

        $parentDir          = dirname($directory);
        $shouldCreateParent = $recursive && $parentDir !== '.' && $parentDir !== '/' && $parentDir !== $directory;
        if ($shouldCreateParent && !$this->makeDirectory($parentDir)) {
            debug_log($this->getDirectoryCreationErrorMessage($directory));
            return false;
        }

        return $this->createDirectory($directory);
    }

    public function deleteDirectory(string $directory): bool
    {
        if (!$this->maybeLogin()) {
            return false;
        }

        $directory = rtrim($directory, '/');
        if (empty($directory)) {
            return false;
        }

        if (!$this->directoryExists($directory)) {
            return true;
        }

        return $this->removeDirectory($directory);
    }

    abstract public function login(int $retry = 3): bool;

    abstract public function getError(): string;

    /** @return void */
    abstract public function close();

    /** @return void */
    abstract public function setMode(int $mode);

    abstract public function getDefaultPath(): string;

    /** @return void */
    abstract public function setPath(string $path);

    abstract public function getFiles(string $path): array;

    abstract public function upload(string $remotePath, string $file, string $chunk, int $offset = 0): bool;

    abstract public function deleteFile(string $path): bool;

    abstract public function downloadAsChunks(string $backupPath, string $filePath, string $fileName, int $chunkStart, int $chunkSize): bool;

    abstract public function getIsSupportNonBlockingUpload(): bool;

    abstract public function nonBlockingUpload(string $remoteFile, string $localFile, int $offset = 0): int;

    abstract public function directoryExists(string $directory): bool;

    abstract protected function createDirectory(string $directory): bool;

    abstract protected function removeDirectory(string $directory): bool;

    abstract protected function getClientType(): string;

    /**
     * Ensures the client is authenticated and connected before performing directory operations
     */
    abstract protected function maybeLogin(): bool;

    protected function getDirectoryCreationErrorMessage(string $directory): string
    {
        $clientType = $this->getClientType();
        return sprintf(
            "%s: Failed to create parent directory for '%s'. " .
            "This may happen if the directory path does not exist or the %s user lacks sufficient permissions. " .
            "If the error persists, please create the full directory path beforehand and ensure it has correct read and write permissions.",
            $clientType,
            $directory,
            $clientType
        );
    }
}
