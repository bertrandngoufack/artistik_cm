<?php

namespace WPStaging\Pro\Backup\Storage\SFTP\Clients;

use Exception;
use RuntimeException;
use WPStaging\Backup\Exceptions\StorageException;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Queue\FinishedQueueException;
use WPStaging\Framework\Traits\ResourceTrait;
use WPStaging\Pro\Backup\Storage\SFTP\Auth;

use function WPStaging\functions\debug_log;

class FtpClient extends AbstractClient
{
    use ResourceTrait;

    /** @var resource|false|null */
    protected $ftp;

    /** @var string */
    protected $hostname;

    /** @var string */
    protected $username;

    /** @var string */
    protected $password;

    /** @var int */
    protected $port;

    /** @var bool */
    protected $passive;

    /** @var bool */
    protected $ssl;

    /** @var string|false */
    protected $error;

    /** @var bool */
    protected $isLogin;

    /** @var string */
    protected $path;

    /** @var string */
    protected $defaultPath = '';

    /** @var string */
    protected $tmpDirectory;

    /** @var int */
    protected $mode;

    /**
     * @throws FtpException
     */
    public function __construct(string $hostname, string $username, string $password, bool $ssl, bool $passive, int $port)
    {
        if (!extension_loaded('ftp')) {
            throw new FtpException("PHP FTP extension not loaded");
        }

        $this->hostname = $hostname;
        $this->port     = $port;
        $this->ssl      = $ssl;
        $this->username = $username;
        $this->password = $password;
        $this->passive  = $passive;
        $this->mode     = Auth::FTP_UPLOAD_MODE_PUT;

        /** @var Directory */
        $directory          = WPStaging::make(Directory::class);
        $this->tmpDirectory = $directory->getTmpDirectory();
    }

    /**
     * @return void
     */
    public function setPath(string $path)
    {
        $this->path = $path;
    }

    public function getDefaultPath(): string
    {
        return $this->defaultPath;
    }

    /**
     * What command to use internally for appending? REST(using ftp_fput) or APPEND(using ftp_append)?
     * @return void
     *
     * @throws RuntimeException
     */
    public function setMode(int $mode)
    {
        $supportedModes = [
            Auth::FTP_UPLOAD_MODE_PUT,
            Auth::FTP_UPLOAD_MODE_APPEND,
            Auth::FTP_UPLOAD_MODE_NON_BLOCKING,
        ];

        if (!in_array($mode, $supportedModes)) {
            throw new RuntimeException(sprintf('Given upload mode (%s) not supported using ftp extension.', $mode));
        }

        $this->mode = $mode;
    }

    public function login(int $retry = 3): bool
    {
        if ($this->isLogin) {
            return true;
        }

        if (is_resource($this->ftp)) {
            $systype = $this->executeFtpCall(function () {
                return ftp_systype($this->ftp);
            });
            if ($systype !== false) {
                return true;
            }
        }

        try {
            if ($this->ssl) {
                $this->ftp = $this->executeFtpCall(function () {
                    return ftp_ssl_connect($this->hostname, $this->port, 30);
                });
            } else {
                $this->ftp = $this->executeFtpCall(function () {
                    return ftp_connect($this->hostname, $this->port, 30);
                });
            }
        } catch (Exception $ex) {
            debug_log(sprintf('Unable to connect to FTP server at %s:%d, Error: %s', $this->hostname, $this->port, $ex->getMessage()));
            $this->ftp = false;
        }

        if ($this->ftp === false && $retry > 0) {
            return $this->login($retry - 1);
        }

        if ($this->ftp === false) {
            $this->isLogin = false;
            $this->error = "Unable to connect to FTP server at {$this->hostname}:{$this->port}. Host might be unreachable.";
            debug_log(sprintf('Unable to connect to FTP server at %s:%d. Host might be unreachable.', $this->hostname, $this->port));
            return false;
        }

        $result = $this->executeFtpCall(function () {
            return ftp_login($this->ftp, $this->username, $this->password);
        });

        if ($result === false) {
            $this->isLogin = false;
            $this->error = "Unable to login to FTP server. Invalid username or password.";
            debug_log("Unable to login to FTP server. Invalid username or password.");
            return false;
        }

        $currentPath = $this->executeFtpCall(function () {
            return ftp_pwd($this->ftp);
        });
        $this->defaultPath = ($this->ftp && $currentPath !== false) ? $currentPath : '';

        $this->isLogin = true;
        $this->executeFtpCall(function () {
            return ftp_pasv($this->ftp, $this->passive);
        });
        $this->executeFtpCall(function () {
            return ftp_set_option($this->ftp, FTP_AUTOSEEK, false);
        });

        return true;
    }

    public function upload(string $remotePath, string $file, string $chunk, int $offset = 0): bool
    {
        if (!$this->login()) {
            return false;
        }

        if ($remotePath !== '') {
            $remotePath = trailingslashit($remotePath);
        }

        if ($offset === 0) {
            $this->makeDirectory($remotePath);
        }

        $remoteFile = $remotePath . $file;

        if ($this->mode === Auth::FTP_UPLOAD_MODE_PUT || $offset === 0) {
            return $this->uploadUsingPut($remoteFile, $chunk, $offset);
        }

        return $this->uploadUsingAppend($remoteFile, $file, $chunk, $offset);
    }

    public function nonBlockingUpload(string $remoteFile, string $localFile, int $offset = 0): int
    {
        if (!$this->login()) {
            throw new StorageException('FTP login failed');
        }

        $localFileSize  = filesize($localFile);
        $remoteFileSize = $this->executeFtpCall(function () use ($remoteFile) {
            return ftp_size($this->ftp, $remoteFile);
        });
        $resume = true;

        if ($remoteFileSize <= 0) {
            $remoteFileSize = 0;
            $resume         = false;
        }

        if ($remoteFileSize >= $localFileSize) {
            throw new FinishedQueueException('Remote file size is greater than or equal to local file size');
        }

        $handle = fopen($localFile, 'rb');
        if (!$handle) {
            throw new StorageException('Failed to open local file');
        }

        if ($resume) {
            fseek($handle, $remoteFileSize);
        }

        $response = $this->executeFtpCall(function () use ($remoteFile, $handle, $remoteFileSize) {
            return ftp_nb_fput($this->ftp, $remoteFile, $handle, FTP_BINARY, $remoteFileSize);
        });

        while ($response === FTP_MOREDATA && !$this->isThreshold()) {
            $response = $this->executeFtpCall(function () {
                return ftp_nb_continue($this->ftp);
            });
        }

        $newUploadSize = $this->executeFtpCall(function () use ($remoteFile) {
            return ftp_size($this->ftp, $remoteFile);
        });
        if ($newUploadSize <= 0) {
            $newUploadSize = ftell($handle);
        }

        fclose($handle);

        if ($response === FTP_FINISHED) {
            throw new FinishedQueueException('FTP upload finished');
        }

        return $newUploadSize - $remoteFileSize;
    }

    /**
     * @return void
     */
    public function close()
    {
        if ($this->ftp === null || $this->ftp === false) {
            $this->isLogin = false;
            $this->ftp     = null;
            return;
        }

        $this->isLogin = false;
        try {
            $this->executeFtpCall(function () {
                return ftp_close($this->ftp);
            });
        } catch (\Throwable $e) {
            // Silently handle SSL shutdown errors
            // The connection is being closed anyway
        }

        $this->ftp = null;
    }

    public function getError(): string
    {
        return $this->error ?: '';
    }

    /**
     * @throws StorageException
     */
    public function getFiles(string $path): array
    {
        $this->login();

        if ($this->ftp === false) {
            return [];
        }

        // Let check if path is already set, then no need to change directory
        // @todo Improve this condition in a separate PR if required
        $currentPath = $this->executeFtpCall(function () {
            return ftp_pwd($this->ftp);
        });
        if ($path !== '' && $path !== $currentPath) {
            $this->executeFtpCall(function () use ($path) {
                return ftp_chdir($this->ftp, $path);
            });
        }

        $items = [];
        try {
            $items = $this->executeFtpCall(function () {
                return ftp_rawlist($this->ftp, '-tr');
            });
        } catch (Exception $ex) {
            $this->error = $ex->getMessage();
            debug_log($this->error);
            $this->close();
            throw new StorageException($this->error);
        }

        // If somehow ftp_rawlist fails, then try ftp_mlsd, mlsd is only available in PHP 7.2+
        if ($items === false && function_exists('ftp_mlsd')) {
            return $this->getFilesUsingMlsd();
        }

        $files = [];
        if (!is_array($items)) {
            $this->close();
            throw new StorageException('Wrong Output');
        }

        foreach ($items as $item) {
            if (empty($item)) {
                continue;
            }

            $metas = preg_split('/\s+/', trim($item));

            if (substr($metas[0], 0, 1) === 'd') {
                continue;
            }

            $fileName  = empty($metas[count($metas) - 1]) ? '' : $metas[count($metas) - 1];
            $timestamp = null;
            preg_match('/_(\d{8})-(\d{6})_/', $fileName, $matches);
            if (isset($matches[1]) && isset($matches[2])) {
                $dateTime  = $matches[1] . $matches[2];
                $timestamp = (new \DateTime($dateTime))->getTimestamp();
            }

            $files[] = [
                'time' => $timestamp,
                'name' => $fileName,
                'size' => isset($metas[4]) ? (int)$metas[4] : null,
            ];
        }

        $this->close();
        return $files;
    }

    public function deleteFile(string $path): bool
    {
        $this->login();
        if ($this->ftp === false) {
            return false;
        }

        try {
            $result = $this->executeFtpCall(function () use ($path) {
                return ftp_delete($this->ftp, $path);
            });
            $this->close();
            return $result;
        } catch (Exception $ex) {
            debug_log($ex->getMessage());
            return false;
        }
    }

    public function downloadAsChunks(string $backupPath, string $filePath, string $fileName, int $chunkStart, int $chunkSize): bool
    {
        try {
            $this->login();
            if ($this->ftp === false) {
                return false;
            }

            $this->executeFtpCall(function () {
                return ftp_set_option($this->ftp, FTP_BINARY, true);
            });
            if ($this->ftp === false) {
                return false;
            }

            $fileHandle = fopen($filePath, 'a+');
            $remotePath = $backupPath . $fileName;
            $response   = $this->executeFtpCall(function () use ($fileHandle, $remotePath, $chunkStart) {
                return ftp_nb_fget($this->ftp, $fileHandle, $remotePath, FTP_BINARY, $chunkStart);
            });
            while ($response === FTP_MOREDATA) {
                $response = $this->executeFtpCall(function () {
                    return ftp_nb_continue($this->ftp);
                });
            }

            fclose($fileHandle);
            return true;
        } catch (Exception $ex) {
            $this->error = $ex->getMessage();
            return false;
        }
    }

    public function getIsSupportNonBlockingUpload(): bool
    {
        return true;
    }

    protected function maybeLogin(): bool
    {
        if (!$this->login()) {
            debug_log("FTP: Login failed for directory creation");
            return false;
        }

        return true;
    }

    protected function uploadUsingPut(string $remoteFile, string $chunk, int $offset): bool
    {
        $handle = fopen('php://temp', 'wb+');
        if (!$handle) {
            return false;
        }

        if ($fileSize = fwrite($handle, $chunk)) {
            rewind($handle);
        }

        // For SSL connections with offset > 0, we need to re-establish the connection
        // because PHP's ftp_fput with SSL doesn't properly handle the REST command
        if ($this->ssl && $offset > 0) {
            $this->close();
            if (!$this->login()) {
                debug_log("FTP uploadUsingPut: Failed to re-establish connection for SSL resume");
                fclose($handle);
                return false;
            }
        }

        $result = false;
        try {
            $result = $this->executeFtpCall(function () use ($remoteFile, $handle, $offset) {
                return ftp_fput($this->ftp, $remoteFile, $handle, FTP_BINARY, $offset);
            });
        } catch (Exception $e) {
            debug_log(sprintf("Ftp Extension: Offset - %s, Error:  %s", $offset, $e->getMessage()));
        }

        fclose($handle);
        return $result;
    }

    protected function uploadUsingAppend(string $remoteFile, string $fileName, string $chunk, int $offset): bool
    {
        if (!function_exists('ftp_append')) {
            return false;
        }

        $localFile = $this->tmpDirectory . $fileName;
        $handle    = fopen($localFile, 'wb+');
        if (!$handle) {
            return false;
        }

        if ($fileSize = fwrite($handle, $chunk)) {
            rewind($handle);
        }

        fclose($handle);
        try {
            return $this->executeFtpCall(function () use ($remoteFile, $localFile) {
                return ftp_append($this->ftp, $remoteFile, $localFile, FTP_BINARY); // phpcs:ignore
            });
        } catch (Exception $e) {
            debug_log(sprintf("Ftp Extension: Offset - %s, Error:  %s", $offset, $e->getMessage()));
        }

        return false;
    }

    /**
     * @throws StorageException
     */
    protected function getFilesUsingMlsd(): array
    {
        $items = [];
        try {
            $items = $this->executeFtpCall(function () {
                return ftp_mlsd($this->ftp, '.');
            });
        } catch (Exception $ex) {
            $this->error = $ex->getMessage();
            debug_log($this->error);
            throw new StorageException($this->error);
        }

        if (!is_array($items)) {
            $this->close();
            throw new StorageException('MLSD: Wrong Output');
        }

        $files = [];
        foreach ($items as $item) {
            $item = array_change_key_case($item, CASE_LOWER);
            if (empty($item['type'])) {
                continue;
            }

            if ($item['type'] !== 'file') {
                continue;
            }

            $files[] = [
                'time' => isset($item['modify']) ? strtotime($item['modify']) : null,
                'name' => $item['name'],
                'size' => isset($item['size']) ? (int)$item['size'] : null,
            ];
        }

        $this->close();
        return $files;
    }

    public function directoryExists(string $directory): bool
    {
        if (!$this->maybeLogin()) {
            return false;
        }

        if ($this->ftp === false || $this->ftp === null) {
            return false;
        }

        $currentDir = $this->executeFtpCall(function () {
            return ftp_pwd($this->ftp);
        });
        $exists = $this->executeFtpCall(function () use ($directory) {
            return ftp_chdir($this->ftp, $directory);
        });
        // Restore original directory
        if ($currentDir !== false) {
            $this->executeFtpCall(function () use ($currentDir) {
                return ftp_chdir($this->ftp, $currentDir);
            });
        }

        return $exists;
    }

    protected function createDirectory(string $directory): bool
    {
        try {
            $result = $this->executeFtpCall(function () use ($directory) {
                return ftp_mkdir($this->ftp, $directory);
            });
            if ($result !== false) {
                return true;
            }

            if ($this->directoryExists($directory)) {
                return true;
            }

            $this->error = "Error creating FTP directory: {$directory}";
            debug_log($this->error);
            return false;
        } catch (Exception $e) {
            $this->error = "Error creating FTP directory {$directory}: " . $e->getMessage();
            debug_log($this->error);
            return false;
        }
    }

    protected function getClientType(): string
    {
        return 'FTP';
    }

    protected function removeDirectory(string $directory): bool
    {
        try {
            $result = $this->executeFtpCall(function () use ($directory) {
                return ftp_rmdir($this->ftp, $directory);
            });
            return $result !== false;
        } catch (Exception $e) {
            debug_log("Error removing FTP directory {$directory}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Execute an FTP function call while capturing warnings
     *
     * @param callable $callback The FTP function to execute
     * @return mixed The result of the callback
     */
    protected function executeFtpCall(callable $callback)
    {
        $lastWarning = null;
        set_error_handler(function ($severity, $message, $file, $line) use (&$lastWarning) {
            $lastWarning = $message;
            return true;
        });

        try {
            $result = $callback();
        } finally {
            restore_error_handler();
        }

        if ($lastWarning !== null) {
            debug_log(sprintf('FTP Warning: %s', $lastWarning));
        }

        return $result;
    }
}
