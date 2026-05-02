<?php

namespace WPStaging\Pro\Push\Tasks;

use WPStaging\Framework\Job\Dto\TaskResponseDto;

abstract class OptionAdjustmentTask extends DatabaseAdjustmentTask
{
    /**
     * Temporary Options Table
     *
     * @var string
     */
    protected $tmpOptionsTable;

    /**
     * Productions Site Options Table
     *
     * @var string
     */
    protected $prodOptionsTable;

    public function execute(): TaskResponseDto
    {
        $this->setup();

        if ($this->jobDataDto->getStagingSite()->getNetworkClone()) {
            return $this->updateAllOptionsTables();
        }

        return $this->updateOptionsTable();
    }

    /**
     * Update all options table for entire multisite clone
     */
    protected function updateAllOptionsTables(): TaskResponseDto
    {
        foreach ($this->getStagingSubsites() as $site) {
            $this->updateOptionsTable($this->getOptionTableWithoutBasePrefix($site->blog_id));
        }

        return $this->generateResponse();
    }

    /**
     * Update the given options table
     *
     * @param string $tableName
     * @return TaskResponseDto
     */
    protected function updateOptionsTable($tableName = 'options')
    {
        $stagingTable = $this->jobDataDto->getStagingSite()->getPrefix() . $tableName;
        // options table has been excluded from pushing process so exit here
        if ($this->isStagingTableExcluded($tableName)) {
            $this->logger->warning("$stagingTable excluded. Skipping this task: " . static::getTaskTitle());
            return $this->generateResponse(false);
        }

        $this->tmpOptionsTable = $this->jobDataDto->getTmpPrefix() . $tableName;
        if (!$this->isTableExists($this->tmpOptionsTable)) {
            $this->logger->error("{$this->tmpOptionsTable} not found! Task: " . static::getTaskTitle());
            return $this->generateResponse(false);
        }

        $this->prodOptionsTable = $this->database->getBasePrefix() . $tableName;

        return $this->adjustOptionsTable();
    }

    /**
     * Get Option Table Without Base Prefix
     */
    protected function getOptionTableWithoutBasePrefix(string $blogID): string
    {
        if ($blogID === '0' || $blogID === '1') {
            return 'options';
        }

        return $blogID . '_options';
    }

    abstract protected function adjustOptionsTable(): TaskResponseDto;

    /**
     * Execute sql batch queries
     * @param string $sqlbatch
     * @return void
     */
    protected function executeBulk(string $sqlbatch)
    {
        $queries = array_filter(explode(";\n", $sqlbatch));

        foreach ($queries as $query) {
            if ($this->wpdb->query($query) === false) {
                $this->logger->warning("DB Data Warning:  Can not execute query $query");
            }
        }
    }
}
