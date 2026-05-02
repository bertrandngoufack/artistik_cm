<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobBackup;

use WPStaging\Backup\Task\Tasks\JobBackup\DatabaseBackupTask as BasicDatabaseBackupTask;
use wpdb;
use WPStaging\Framework\Filesystem\PartIdentifier;

class DatabaseBackupTask extends BasicDatabaseBackupTask
{
    /**
     * @param wpdb $wpdb
     * @return void
     */
    protected function setupMultipartDatabaseFilePathName(wpdb $wpdb)
    {
        $partFilename     = $this->getDatabaseFilename($wpdb, $this->jobDataDto->getMaxDbPartIndex(), $useCache = true);
        $cacheDirectory   = $this->directory->getCacheDirectory();
        $currentPartIndex = $this->jobDataDto->getMaxDbPartIndex();

        $databaseFileLocation = $cacheDirectory . $partFilename;

        // create database file with comments for parts
        if (!file_exists($databaseFileLocation) && $currentPartIndex !== 0) {
            $this->createDatabasePart($wpdb, $databaseFileLocation, $currentPartIndex);
        }

        $multipartFilesInfo = $this->jobDataDto->getMultipartFilesInfo();
        $destinationFiles = array_map(function ($backupFile) {
            return $backupFile['destination'];
        }, $multipartFilesInfo);

        if (in_array($partFilename, $destinationFiles)) {
            return;
        }

        $this->jobDataDto->setDatabaseFile($databaseFileLocation);

        $this->jobDataDto->addMultipartFileInfo([
            'category'              => PartIdentifier::DATABASE_PART_IDENTIFIER,
            'index'                 => $currentPartIndex,
            'filePath'              => $databaseFileLocation,
            'destination'           => $partFilename,
            'status'                => 'Pending',
            'sizeBeforeAddingIndex' => 0,
        ]);
    }

    /**
     * @param int $partIndex
     * @return string
     */
    protected function getCachedDatabaseFilenameForPart(int $partIndex): string
    {
        $multipartFilesInfo = $this->jobDataDto->getMultipartFilesInfo();
        foreach ($multipartFilesInfo as $multipartFileInfo) {
            if ($multipartFileInfo['index'] === $partIndex && $multipartFileInfo['category'] === PartIdentifier::DATABASE_PART_IDENTIFIER) {
                return $multipartFileInfo['destination'];
            }
        }

        return '';
    }

    /**
     * @param wpdb  $wpdb
     * @param string $databaseFileLocation
     * @param int    $partNo
     */
    private function createDatabasePart(wpdb $wpdb, string $databaseFileLocation, int $partNo)
    {
        $content = <<<SQL
-- WP Staging SQL Backup Dump
-- https://wp-staging.com/
--
-- Host: {$wpdb->dbhost}
-- Database: {$wpdb->dbname}
-- Part No: {$partNo}
-- Class: WPStaging\Backup\Service\Database\Exporter\RowsExporter
--
SQL;
        file_put_contents($databaseFileLocation, $content);
    }
}
