<?php

namespace WPStaging\Pro\Push\Service;

use WPStaging\Framework\Adapter\Database;
use WPStaging\Framework\Collection\Collection;
use WPStaging\Framework\Database\TableDto;
use WPStaging\Framework\Database\TableService;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Pro\Staging\Service\StagingSetup;

class TableScanner
{
    /** @var string */
    const FILTER_PUSH_EXCLUDED_TABLES = 'wpstg_push_excluded_tables';

    /**
     * @var TemplateEngine
     */
    protected $templateEngine;

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var TableService
     */
    protected $tableService;

    /**
     * @var StagingSetup
     */
    protected $stagingSetup;

    /**
     * @var string[]
     */
    protected $disabledTables = [];

    /**
     * @var string[]
     */
    protected $selectedTables = [];

    /**
     * @var TableDto[]
     */
    protected $currentTables = [];

    /** @var string[] */
    protected $tablesExcludedByFilter = [];

    public function __construct(TemplateEngine $templateEngine, Database $database, TableService $tableService)
    {
        $this->templateEngine = $templateEngine;
        $this->database       = $database;
        $this->tableService   = $tableService;
    }

    /**
     * @return void
     */
    public function setStagingSetup(StagingSetup $stagingSetup)
    {
        $this->stagingSetup = $stagingSetup;
    }

    public function renderTablesSelection()
    {
        $stagingSiteDto = $this->stagingSetup->getStagingSiteDto();
        $this->scanTables($stagingSiteDto->getPrefix());
        $result = $this->templateEngine->render('pro/push/_partials/tables-selection.php', [
            'stagingSetup'   => $this->stagingSetup,
            'stagingSiteDto' => $stagingSiteDto,
            'tables'         => $this->currentTables,
            'disabledTables' => $this->disabledTables,
            'selectedTables' => $this->selectedTables,
        ]);

        echo $result; // phpcs:ignore
    }

    /**
     * @return void
     */
    protected function scanTables(string $stagingSitePrefix)
    {
        /**
         * @var Collection|TableDto[] $tables
         */
        $tables = $this->tableService->findTableStatusStartsWith($stagingSitePrefix);

        // reset the excluded tables
        $this->disabledTables = [];
        $this->currentTables  = [];

        $this->tablesExcludedByFilter = $this->getTablesExcludedByFilter();

        foreach ($tables as $table) {
            // Is the table disabled via filter?
            if ($this->isTableDisabled($table->getName())) {
                $this->disabledTables[] = $table->getName();
            }

            if ($this->isTableSelected($table->getName())) {
                $this->selectedTables[] = $table->getName();
            }

            if (!$table->getIsView()) {
                $this->currentTables[] = $table;
            }
        }
    }

    protected function isTableDisabled(string $tableName): bool
    {
        return in_array($tableName, $this->tablesExcludedByFilter);
    }

    protected function isTableSelected(string $tableName): bool
    {
        $previousSelection = $this->stagingSetup->getStagingSiteDto()->getTablePushSelection();
        if (is_array($previousSelection)) {
            return in_array($tableName, $previousSelection);
        }

        return true;
    }

    private function getTablesExcludedByFilter(): array
    {
        $excludedTables = [];
        $excludedTables = apply_filters(self::FILTER_PUSH_EXCLUDED_TABLES, $excludedTables);

        $tables = [];
        $prefix = $this->stagingSetup->getStagingSiteDto()->getPrefix();
        foreach ($excludedTables as $key => $value) {
            $tables[] = $prefix . $value;
        }

        return $tables;
    }
}
