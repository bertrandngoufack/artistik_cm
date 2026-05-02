<?php

namespace WPStaging\Pro\Push\Dto;

use WPStaging\Framework\Job\Dto\JobDataDto;
use WPStaging\Framework\Job\Dto\Traits\FilesystemScannerDtoTrait;
use WPStaging\Framework\Job\Interfaces\FilesystemScannerDtoInterface;
use WPStaging\Staging\Interfaces\StagingDatabaseDtoInterface;
use WPStaging\Staging\Interfaces\StagingOperationDtoInterface;
use WPStaging\Staging\Interfaces\StagingSiteDtoInterface;
use WPStaging\Staging\Traits\StagingDatabaseDtoTrait;
use WPStaging\Staging\Traits\StagingOperationDtoTrait;
use WPStaging\Staging\Traits\WithStagingSiteDto;

/**
 * This is a common dto that is used for staging site jobs (create, update, reset)
 */
class StagingSitePushDataDto extends JobDataDto implements StagingDatabaseDtoInterface, StagingSiteDtoInterface, StagingOperationDtoInterface, FilesystemScannerDtoInterface
{
    use FilesystemScannerDtoTrait;

    use WithStagingSiteDto, StagingOperationDtoTrait, StagingDatabaseDtoTrait {
        StagingOperationDtoTrait::setExcludedTables insteadof StagingDatabaseDtoTrait;
        StagingOperationDtoTrait::getExcludedTables insteadof StagingDatabaseDtoTrait;
    }

    /** @var string */
    private $cloneId = '';

    /** @var bool */
    private $isCleanPluginsThemes = false;

    /** @var bool */
    private $isCleanUploads = false;

    /** @var bool */
    private $isBackupUploads = false;

    /** @var bool */
    private $isCreateDatabaseBackup = false;

    /** @var bool */
    private $isPluginsCleanupDone = false;

    /** @var bool */
    private $isThemesCleanupDone = false;

    /** @var bool */
    private $isUploadsCleanupDone = false;

    /** @var bool */
    private $isBackupCreated = false;

    /** @var string */
    private $tmpPrefix = '';

    /** @var array<string,string> */
    private $shortNamedTablesToRename = [];

    /** @var array<string,string> */
    private $shortNamedTablesToDrop = [];

    /**
     * @param string $cloneId
     * @return void
     */
    public function setCloneId(string $cloneId)
    {
        $this->cloneId = $cloneId;
    }

    /**
     * @return string
     */
    public function getCloneId(): string
    {
        return $this->cloneId;
    }

    /**
     * @param bool $cleanPluginsThemes
     * @return void
     */
    public function setIsCleanPluginsThemes(bool $cleanPluginsThemes)
    {
        $this->isCleanPluginsThemes = $cleanPluginsThemes;
    }

    /**
     * @return bool
     */
    public function getIsCleanPluginsThemes(): bool
    {
        return $this->isCleanPluginsThemes;
    }

    /**
     * @param bool $cleanUploads
     * @return void
     */
    public function setIsCleanUploads(bool $cleanUploads)
    {
        $this->isCleanUploads = $cleanUploads;
    }

    /**
     * @return bool
     */
    public function getIsCleanUploads(): bool
    {
        return $this->isCleanUploads;
    }

    /**
     * @param bool $backupUploads
     * @return void
     */
    public function setIsBackupUploads(bool $backupUploads)
    {
        $this->isBackupUploads = $backupUploads;
    }

    /**
     * @return bool
     */
    public function getIsBackupUploads(): bool
    {
        return $this->isBackupUploads;
    }

    /**
     * @param bool $createDatabaseBackup
     * @return void
     */
    public function setIsCreateDatabaseBackup(bool $createDatabaseBackup)
    {
        $this->isCreateDatabaseBackup = $createDatabaseBackup;
    }

    /**
     * @return bool
     */
    public function getIsCreateDatabaseBackup(): bool
    {
        return $this->isCreateDatabaseBackup;
    }

    /**
     * @param bool $isBackupCreated
     * @return void
     */
    public function setIsBackupCreated(bool $isBackupCreated)
    {
        $this->isBackupCreated = $isBackupCreated;
    }

    /**
     * @return bool
     */
    public function getIsBackupCreated(): bool
    {
        return $this->isBackupCreated;
    }

    /**
     * @param string $tmpPrefix
     * @return void
     */
    public function setTmpPrefix(string $tmpPrefix)
    {
        $this->tmpPrefix = $tmpPrefix;
    }

    /**
     * @return string
     */
    public function getTmpPrefix(): string
    {
        return $this->tmpPrefix;
    }

    /**
     * @param array<string,string> $shortNamedTablesToRename
     * @return void
     */
    public function setShortNamedTablesToRename(array $shortNamedTablesToRename)
    {
        $this->shortNamedTablesToRename = $shortNamedTablesToRename;
    }

    /**
     * @return array<string,string>
     */
    public function getShortNamedTablesToRename(): array
    {
        return $this->shortNamedTablesToRename;
    }

    /**
     * @param array<string,string> $shortNamedTablesToDrop
     * @return void
     */
    public function setShortNamedTablesToDrop(array $shortNamedTablesToDrop)
    {
        $this->shortNamedTablesToDrop = $shortNamedTablesToDrop;
    }

    /**
     * @return array<string,string>
     */
    public function getShortNamedTablesToDrop(): array
    {
        return $this->shortNamedTablesToDrop;
    }

    /**
     * @param bool $isPluginsCleanupDone
     * @return void
     */
    public function setIsPluginsCleanupDone(bool $isPluginsCleanupDone)
    {
        $this->isPluginsCleanupDone = $isPluginsCleanupDone;
    }

    /**
     * @return bool
     */
    public function getIsPluginsCleanupDone(): bool
    {
        return $this->isPluginsCleanupDone;
    }

    /**
     * @param bool $isThemesCleanupDone
     * @return void
     */
    public function setIsThemesCleanupDone(bool $isThemesCleanupDone)
    {
        $this->isThemesCleanupDone = $isThemesCleanupDone;
    }

    /**
     * @return bool
     */
    public function getIsThemesCleanupDone(): bool
    {
        return $this->isThemesCleanupDone;
    }

    /**
     * @param bool $isUploadsCleanupDone
     * @return void
     */
    public function setIsUploadsCleanupDone(bool $isUploadsCleanupDone)
    {
        $this->isUploadsCleanupDone = $isUploadsCleanupDone;
    }

    /**
     * @return bool
     */
    public function getIsUploadsCleanupDone(): bool
    {
        return $this->isUploadsCleanupDone;
    }
}
