<?php

namespace WPStaging\Pro\Backup\Dto\Job;

use WPStaging\Backup\Entity\BackupMetadata;
use WPStaging\Framework\Job\Dto\JobDataDto;

/**
 * Stores state for the backup extraction job (selected files and directories).
 */
class JobExtractDataDto extends JobDataDto
{
    /** @var string */
    protected $file;

    /** @var int[] */
    protected $offsets = [];

    /** @var string[] */
    protected $directories = [];

    /** @var bool */
    protected $overwrite = false;

    /** @var BackupMetadata|null */
    protected $backupMetadata;

    /** @var int */
    protected $extracted = 0;

    /** @var int */
    protected $skipped = 0;

    /** @var string[] */
    protected $errors = [];

    public function getFile(): string
    {
        return $this->file;
    }

    public function setFile(string $file)
    {
        $this->file = untrailingslashit(wp_normalize_path($file));
    }

    /**
     * @return int[]
     */
    public function getOffsets(): array
    {
        return $this->offsets;
    }

    /**
     * @param int[] $offsets
     * @return void
     */
    public function setOffsets(array $offsets)
    {
        $offsets = array_map('absint', $offsets);
        $offsets = array_values(array_filter($offsets));
        $this->offsets = $offsets;
    }

    /**
     * @return string[]
     */
    public function getDirectories(): array
    {
        return $this->directories;
    }

    /**
     * @param string[] $directories
     * @return void
     */
    public function setDirectories(array $directories)
    {
        $normalized = [];
        foreach ($directories as $directory) {
            $directory = trim((string)$directory);
            if ($directory === '') {
                continue;
            }

            $directory = trim(wp_normalize_path($directory), '/');
            if ($directory === '') {
                continue;
            }

            $normalized[] = $directory;
        }

        $this->directories = array_values(array_unique($normalized));
    }

    public function getOverwrite(): bool
    {
        return (bool)$this->overwrite;
    }

    public function setOverwrite(bool $overwrite)
    {
        $this->overwrite = $overwrite;
    }

    /**
     * @return BackupMetadata|null
     */
    public function getBackupMetadata()
    {
        return $this->backupMetadata;
    }

    /**
     * @param BackupMetadata|array|null $backupMetadata
     * @return void
     */
    public function setBackupMetadata($backupMetadata)
    {
        if ($backupMetadata instanceof BackupMetadata) {
            $this->backupMetadata = $backupMetadata;
            return;
        }

        if (is_array($backupMetadata)) {
            try {
                $this->backupMetadata = (new BackupMetadata())->hydrate($backupMetadata);
                return;
            } catch (\Exception $e) {
                $this->backupMetadata = null;
                return;
            }
        }

        $this->backupMetadata = null;
    }

    public function getExtracted(): int
    {
        return (int)$this->extracted;
    }

    public function setExtracted(int $extracted)
    {
        $this->extracted = max(0, $extracted);
    }

    public function incrementExtracted()
    {
        $this->extracted++;
    }

    public function getSkipped(): int
    {
        return (int)$this->skipped;
    }

    public function setSkipped(int $skipped)
    {
        $this->skipped = max(0, $skipped);
    }

    public function incrementSkipped()
    {
        $this->skipped++;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param string[] $errors
     * @return void
     */
    public function setErrors(array $errors)
    {
        $this->errors = array_values(array_filter(array_map('strval', $errors)));
    }

    public function addError(string $error)
    {
        $error = (string)$error;
        if ($error === '') {
            return;
        }

        $this->errors[] = $error;
    }
}
