<?php

namespace WPStaging\Pro\Push\Tasks;

use WPStaging\Framework\Adapter\Database;
use WPStaging\Framework\Database\TableService;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Framework\Utils\Urls;
use WPStaging\Staging\Traits\WithStagingDatabase;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

abstract class DatabaseAdjustmentTask extends PushTask
{
    use WithStagingDatabase;

    /** @var Urls */
    protected $urls;

    /** @var TableService */
    protected $currentTableService;

    /** @var Database|null */
    protected $database = null;

    /** @var \wpdb|null */
    protected $wpdb = null;

    /** @var \WP_Site[] */
    protected $subsites = [];

    /** @var string */
    protected $currentSitePrefix = '';

    /** @var string */
    protected $stagingPrefix = '';

    /** @var string */
    protected $tmpPrefix = '';

    /**
     * @param LoggerInterface $logger
     * @param Cache $cache
     * @param StepsDto $stepsDto
     * @param SeekableQueueInterface $taskQueue
     * @param Urls $urls
     * @param Database $database
     * @param TableService $tableService
     */
    public function __construct(LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue, Urls $urls, Database $database, TableService $tableService)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
        $this->database            = $database;
        $this->urls                = $urls;
        $this->currentTableService = $tableService;
    }

    /**
     * @return void
     */
    public function setup()
    {
        $this->initStagingDatabase($this->getStagingSiteDto($this->jobDataDto->getCloneId()));
        if ($this->tableService === null) {
            $this->tableService = new TableService($this->stagingDb);
        }

        if ($this->wpdb === null) {
            $this->wpdb = $this->database->getWpdb();
        }

        $this->stagingPrefix     = $this->jobDataDto->getStagingSite()->getPrefix();
        $this->tmpPrefix         = $this->jobDataDto->getTmpPrefix();
        $this->currentSitePrefix = $this->database->getBasePrefix();
    }

    /**
     * Check if the table exists in the current database.
     * @param string $tableName
     * @return bool
     */
    protected function isTableExists(string $tableName): bool
    {
        return $this->currentTableService->tableExists($tableName);
    }

    /**
     * Check if the table exists in the staging database.
     * @param string $tableName
     * @return bool
     */
    protected function isStagingTableExists(string $tableName): bool
    {
        return $this->tableService->tableExists($tableName);
    }

    /**
     * Check if the table excluded.
     * @param string $tableNameWithoutPrefix
     * @return bool
     */
    protected function isStagingTableExcluded(string $tableNameWithoutPrefix): bool
    {
        $tableName = $this->getPrefixedStagingTableName($tableNameWithoutPrefix);

        if (!$this->isStagingTableExists($tableName)) {
            return true;
        }

        if (in_array($tableNameWithoutPrefix, $this->jobDataDto->getExcludedTables())) {
            return true;
        }

        return false;
    }

    protected function getPrefixedStagingTableName(string $tableName): string
    {
        return $this->jobDataDto->getDatabasePrefix() . $tableName;
    }

    protected function isOptionsTableExcluded(): bool
    {
        $optionsTable = $this->getOptionsTableName();
        if ($this->isStagingTableExcluded($optionsTable)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $optionName
     * @param string|null $optionValue
     * @param bool $autoload
     * @return bool
     */
    protected function insertOption(string $optionName, $optionValue, bool $autoload = false): bool
    {
        // Let delete the option regardless it exists or not
        $this->deleteOption($optionName);

        $optionTable = $this->getOptionsTableName();
        return $this->executeQuery(
            "INSERT INTO `{$optionTable}` (option_name, option_value, autoload) VALUES (%s, %s, %s)",
            $optionName,
            $optionValue,
            $autoload ? 'on' : 'off'
        );
    }

    /**
     * @param string $optionName
     * @param string $optionValue
     * @return bool
     */
    protected function updateOption(string $optionName, string $optionValue): bool
    {
        $optionTable = $this->getOptionsTableName();
        return $this->executeQuery(
            "UPDATE `{$optionTable}` SET `option_value` = %s WHERE `option_name` = %s;",
            $optionValue,
            $optionName
        );
    }

    /**
     * @param string $optionName
     * @return bool
     */
    protected function deleteOption(string $optionName): bool
    {
        $optionTable = $this->getOptionsTableName();
        return $this->executeQuery(
            "DELETE FROM `{$optionTable}` WHERE `option_name` = %s;",
            $optionName
        );
    }

    protected function getOptionsTableName(): string
    {
        return $this->getPrefixedStagingTableName('options');
    }

    /**
     * @param string $query
     * @param array $parameters
     * @return bool
     */
    protected function executeQuery(string $query, ...$parameters): bool
    {
        $result = $this->wpdb->query(
            $this->wpdb->prepare(
                $query,
                $parameters
            )
        );

        if ($result === false) {
            $this->logger->debug("Database adjustment failed. Query: {$query}. Task: " . self::getTaskTitle());
            return false;
        }

        return true;
    }

    protected function lastError(): string
    {
        return $this->wpdb->last_error;
    }

    /**
     * Get List of all Staging Site Blogs
     *
     * @return \WP_Site[]
     */
    protected function getStagingSubsites(): array
    {
        if (!$this->jobDataDto->getStagingSite()->getNetworkClone()) {
            return [];
        }

        if (empty($this->subsites)) {
            $this->subsites = $this->stagingDb->getWpdb()->get_results("SELECT * FROM {$this->stagingPrefix}blogs");
        }

        return $this->subsites;
    }
}
