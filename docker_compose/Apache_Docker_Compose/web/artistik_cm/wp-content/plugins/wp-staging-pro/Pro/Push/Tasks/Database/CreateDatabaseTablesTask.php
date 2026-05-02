<?php

namespace WPStaging\Pro\Push\Tasks\Database;

use WPStaging\Backup\Service\Database\DatabaseImporter;
use WPStaging\Framework\Database\SelectedTables;
use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Push\Tasks\PushTask;
use WPStaging\Pro\Staging\Service\Database\TableCreateService;
use WPStaging\Staging\Traits\WithStagingDatabase;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

/**
 * This class is responsible for creating empty temporary tables on the current site.
 * This class is used during staging site push.
 */
class CreateDatabaseTablesTask extends PushTask
{
    use WithStagingDatabase;

    /** @var TableCreateService */
    protected $tableCreateService;

    /** @var array */
    protected $tables = [];

    public function __construct(LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue, TableCreateService $tableCreateService)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
        $this->tableCreateService = $tableCreateService;
    }

    public static function getTaskName(): string
    {
        return 'push_creating_tables';
    }

    public static function getTaskTitle(): string
    {
        return 'Creating Database Tables';
    }

    public function execute(): TaskResponseDto
    {
        $this->setup();

        while (!$this->stepsDto->isFinished() && !$this->isThreshold()) {
            $srcTable  = $this->tables[$this->stepsDto->getCurrent()];
            $destTable = $this->tableCreateService->getDestinationTable($srcTable);

            $this->tableCreateService->createDestinationTable($srcTable, $destTable);
            $this->jobDataDto->addStagingTable($srcTable, $destTable);

            $this->stepsDto->incrementCurrentStep();
        }

        if ($this->stepsDto->isFinished()) {
            $this->logger->info('All tables created on staging site...');
        }

        return $this->generateResponse(false);
    }

    /**
     * @return void
     */
    protected function setup()
    {
        $stagingSiteDto = $this->jobDataDto->getStagingSite();
        $this->initStagingDatabase($stagingSiteDto);
        $this->tables = $this->jobDataDto->getSelectedTables();
        $this->tableCreateService->setIsResetExistingTables(false);
        $this->tableCreateService->setupPush($this->logger, $this->stagingDb, DatabaseImporter::TMP_DATABASE_PREFIX);
        if (!$this->stepsDto->getTotal()) {
            $selectedTables = new SelectedTables();
            $this->setDatabaseInfo($selectedTables);
            $selectedTables->setIncludedTables($this->jobDataDto->getIncludedTables());
            $selectedTables->setExcludedTables($this->jobDataDto->getExcludedTables());
            $selectedTables->setSelectedTablesWithoutPrefix($this->jobDataDto->getNonSiteTables());
            $selectedTables->setAllTablesExcluded($this->jobDataDto->getAllTablesExcluded());
            $this->tables = $selectedTables->getSelectedTables($stagingSiteDto->getNetworkClone());
            $this->stepsDto->setTotal(count($this->tables));
            $this->jobDataDto->setSelectedTables($this->tables);
        }
    }

    /**
     * @param SelectedTables $selectedTables
     * @return void
     */
    protected function setDatabaseInfo(SelectedTables $selectedTables)
    {
        $stagingSiteDto = $this->jobDataDto->getStagingSite();
        if ($stagingSiteDto->getIsExternalDatabase()) {
            $selectedTables->setDatabaseInfo(
                $stagingSiteDto->getDatabaseServer(),
                $stagingSiteDto->getDatabaseUser(),
                $stagingSiteDto->getDatabasePassword(),
                $stagingSiteDto->getDatabaseDatabase(),
                $stagingSiteDto->getDatabasePrefix(),
                $stagingSiteDto->getDatabaseSsl()
            );

            return;
        }

        $selectedTables->setDatabaseInfo(
            DB_HOST,
            DB_USER,
            DB_PASSWORD,
            DB_NAME,
            $stagingSiteDto->getDatabasePrefix(),
            $stagingSiteDto->getDatabaseSsl()
        );
    }
}
