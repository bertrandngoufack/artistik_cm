<?php

namespace WPStaging\Pro\Backup\Service\Database\Importer;

use WPStaging\Backup\Dto\Service\DatabaseImporterDto;
use WPStaging\Backup\Entity\BackupMetadata;
use WPStaging\Backup\Service\Database\Importer\SubsiteManagerInterface;
use WPStaging\Framework\Traits\DebugLogTrait;

class SubsiteManager implements SubsiteManagerInterface
{
    use DebugLogTrait;

    /** @var DatabaseImporterDto */
    private $databaseImporterDto;

    /** @var int|null */
    private $lastSubsiteId = null;

    /** @var string */
    private $tmpBasePrefix;

    /** @var bool */
    private $isEntireNetworkBackup = false;

    /**
     * @param DatabaseImporterDto $databaseImporterDto
     * @return void
     */
    public function initialize(DatabaseImporterDto $databaseImporterDto)
    {
        $this->databaseImporterDto   = $databaseImporterDto;
        $this->tmpBasePrefix         = $this->databaseImporterDto->getTmpPrefix();
        $this->isEntireNetworkBackup = $this->databaseImporterDto->getBackupType() === BackupMetadata::BACKUP_TYPE_MULTISITE;
        $this->lastSubsiteId         = $this->databaseImporterDto->getSubsiteId();
    }

    /** @return void */
    public function updateSubsiteId()
    {
        $this->databaseImporterDto->setSubsiteId($this->lastSubsiteId);
    }

    /** @return bool */
    public function isTableFromDifferentSubsite(string $query): bool
    {
        if (!$this->isEntireNetworkBackup) {
            return false;
        }

        $currentSubsiteId = null;
        try {
            $currentSubsiteId = $this->extractSubsiteIdFromQuery($query);
        } catch (\OutOfBoundsException $e) {
            return false;
        }

        if ($this->lastSubsiteId === null) {
            $this->lastSubsiteId = $currentSubsiteId;
            return false;
        }

        if ($currentSubsiteId === $this->lastSubsiteId) {
            return false;
        }

        $this->lastSubsiteId = $currentSubsiteId;

        return true;
    }

    /**
     * @param string $query
     * @return int
     * @throws \OutOfBoundsException
     */
    protected function extractSubsiteIdFromQuery(string $query): int
    {
        preg_match('#^INSERT INTO `(.+?(?=`))` VALUES (\(.+\));$#', $query, $insertIntoExploded);

        if (count($insertIntoExploded) !== 3) {
            $this->debugLog('Unable to extract ID. Maybe not an insert query? Query: ' . $query, 'info', false);
            throw new \OutOfBoundsException('Unable to extract ID. The query was logged....');
        }

        $tableName = $insertIntoExploded[1];
        if (strpos($tableName, $this->tmpBasePrefix) !== 0) {
            $this->debugLog('Unable to extract ID. Wrong Prefix. Maybe custom table? Query: ' . $query, 'info', false);
            throw new \OutOfBoundsException('Unable to extract ID. The query was logged....');
        }

        $tableName = substr($tableName, strlen($this->tmpBasePrefix));

        if (strpos($tableName, '_') === false) {
            return 1;
        }

        $subsiteId = explode('_', $tableName)[0];

        if (!is_numeric($subsiteId)) {
            return 1;
        }

        return (int)$subsiteId;
    }
}
