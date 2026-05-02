<?php

namespace WPStaging\Pro\Push\Tasks\Database;

use Throwable;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Job\Dto\Task\RowsExporterTaskDto;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Framework\Utils\Times;
use WPStaging\Pro\Push\Service\RowsExporter;
use WPStaging\Pro\Push\Tasks\PushTask;
use WPStaging\Staging\Traits\WithStagingDatabase;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

/**
 * This class is responsible for creating database dump so it can be imported on the temporary tables in current site database.
 * @see ImportDatabaseRowsTask for importing the dump.
 */
class PrepareDatabaseRowsTask extends PushTask
{
    use WithStagingDatabase;

    /** @var RowsExporter */
    protected $rowsExporter;

    /** @var RowsExporterTaskDto */
    protected $currentTaskDto;

    /** @var Directory */
    protected $directory;

    public function __construct(Directory $directory, LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue, RowsExporter $rowsExporter)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
        $this->rowsExporter = $rowsExporter;
        $this->directory    = $directory;
    }

    public static function getTaskName(): string
    {
        return 'push_prepare_database_rows';
    }

    public static function getTaskTitle(): string
    {
        return 'Prepare Database Records';
    }

    public function execute(): TaskResponseDto
    {
        $this->setup();

        do {
            $this->rowsExporter->setTableIndex($this->stepsDto->getCurrent());
            if (!$this->rowsExporter->initiate()) {
                $this->stepsDto->incrementCurrentStep();
                $this->currentTaskDto->reset();
                $this->persistStepsDto();
                $this->setCurrentTaskDto($this->currentTaskDto);
                continue;
            }

            try {
                $this->rowsExporter->export();
            } catch (Throwable $exception) {
                $this->rowsExporter->unlockTables();
            }

            $exporterDto = $this->rowsExporter->getRowsExporterDto();
            $this->currentTaskDto->fromRowExporterDto($exporterDto);
            $this->setCurrentTaskDto($this->currentTaskDto);

            $srcTable = $this->rowsExporter->getTableBeingExported();
            $this->logger->info(sprintf(
                'Preparing table %s: %s of %s records.',
                $srcTable,
                number_format_i18n($this->currentTaskDto->rowsOffset),
                number_format_i18n($this->currentTaskDto->totalRows)
            ));

            $this->logger->debug(sprintf(
                'Preparing table %s: Query time: %s. Batch Size: %s. Last query json: %s.',
                $srcTable,
                Times::formatQueryTime($this->jobDataDto->getDbRequestTime()),
                $this->jobDataDto->getBatchSize(),
                $this->jobDataDto->getLastQueryInfoJSON()
            ));

            if ($exporterDto->isFinished()) {
                $this->stepsDto->incrementCurrentStep();
                $this->currentTaskDto->reset();
                $this->jobDataDto->setTableAverageRowLength(0);
                $this->setCurrentTaskDto($this->currentTaskDto);
                $this->persistStepsDto();
            }
        } while (!$this->stepsDto->isFinished() && !$this->isThreshold());

        return $this->generateResponse(false);
    }

    /** @return string */
    protected function getCurrentTaskType(): string
    {
        return RowsExporterTaskDto::class;
    }

    /**
     * @return void
     */
    protected function setup()
    {
        $this->initStagingDatabase($this->jobDataDto->getStagingSite());
        $tables = $this->jobDataDto->getStagingTables();
        $this->rowsExporter->setupDatabase($this->stagingDb);
        $this->rowsExporter->setStagingPrefix($this->jobDataDto->getDatabasePrefix());
        $this->rowsExporter->inject($this->logger, $this->jobDataDto, $this->currentTaskDto->toRowsExporterDto());
        $this->rowsExporter->setFileName($this->directory->getCacheDirectory() . $this->jobDataDto->getId() . '.wpstgdbtmp.sql');
        $this->rowsExporter->setTables($tables);

        // Merge the tables that were completely excluded as well as the tables whose data needs to be excluded
        $tablesToExclude = array_merge(
            $this->jobDataDto->getExcludedTables(),
            apply_filters(RowsExporter::FILTER_EXCLUDE_TABLES_DATA, RowsExporter::TABLES_EXCLUDED_FROM_DATA_COPYING)
        );

        $this->rowsExporter->setTablesToExclude($tablesToExclude);
        $this->rowsExporter->prefixSpecialFields();
        if (!$this->stepsDto->getTotal()) {
            $this->stepsDto->setCurrent(0);
            $this->stepsDto->setTotal(count($tables));
        }
    }
}
