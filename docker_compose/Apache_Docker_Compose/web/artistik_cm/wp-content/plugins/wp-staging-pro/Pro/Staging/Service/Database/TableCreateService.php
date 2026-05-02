<?php

namespace WPStaging\Pro\Staging\Service\Database;

use RuntimeException;
use WPStaging\Framework\Adapter\Database;
use WPStaging\Framework\Database\TableService;
use WPStaging\Staging\Service\Database\TableCreateService as BaseTableCreateService;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

/**
 * This extends the base TableCreateService to provide external database support and push specific logic.
 */
class TableCreateService extends BaseTableCreateService
{
    /** @var string */
    protected $tempPrefix;

    /**
     * @var TableService|null
     */
    protected $destinationSiteTableService = null;

    /**
     * @param string $tempPrefix
     * @return void
     */
    public function setupPush(LoggerInterface $logger, Database $sourceDb, string $tempPrefix)
    {
        $this->logger            = $logger;
        $this->destinationDb     = $this->sourceDb;
        $this->sourceDb          = $sourceDb;
        $this->databaseName      = $this->sourceDb->getWpdba()->getClient()->__get('dbname');
        $this->sourcePrefix      = $this->sourceDb->getPrefix();
        $this->destinationPrefix = $tempPrefix;
        $this->tableService      = new TableService($this->sourceDb);
    }

    /**
     * @param string $destTableName
     * @return void
     */
    protected function dropDestinationTableIfExists(string $destTableName)
    {
        if ($this->destinationSiteTableService === null) {
            $this->destinationSiteTableService = new TableService($this->destinationDb);
        }

        if (!$this->destinationSiteTableService->tableExists($destTableName)) {
            return;
        }

        if (!$this->isResetExistingTables) {
            throw new RuntimeException("Create Table - Cannot clone table. Error: Destination table $destTableName already exists.");
        }

        $this->logger->warning(sprintf("Create Table - Table %s already exists, dropping it first", esc_html($destTableName)));
        if ($this->destinationSiteTableService->dropTable($destTableName)) {
            return;
        }

        throw new RuntimeException("Create Table - Cannot drop table $destTableName");
    }
}
