<?php

namespace WPStaging\Pro\Push\Tasks\Database\Adjustment;

use WPStaging\Framework\Adapter\Database;
use WPStaging\Framework\Database\OptionPreservationHandler;
use WPStaging\Framework\Database\TableService;
use WPStaging\Framework\Facades\Hooks;
use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\SiteInfo;
use WPStaging\Framework\ThirdParty\FreemiusScript;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Framework\Utils\Urls;
use WPStaging\Pro\Traits\PreservedOptionsTrait;
use WPStaging\Pro\Push\Tasks\OptionAdjustmentTask;
use WPStaging\Staging\CloneOptions;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

/**
 * This class is responsible for preserving options during staging site push.
 */
class PreserveOptionsTask extends OptionAdjustmentTask
{
    use PreservedOptionsTrait;

    /** @var string */
    const FILTER_PRESERVED_OPTIONS = 'wpstg_preserved_options';

    /** @var SiteInfo */
    protected $siteInfo;

    /** @var OptionPreservationHandler */
    protected $optionPreservationHandler;

    /** @var FreemiusScript */
    protected $freemiusHelper;

    /**
     * @param LoggerInterface $logger
     * @param Cache $cache
     * @param StepsDto $stepsDto
     * @param SeekableQueueInterface $taskQueue
     * @param Urls $urls
     * @param Database $database
     * @param TableService $tableService
     * @param SiteInfo $siteInfo
     * @param FreemiusScript $freemiusScript
     * @param OptionPreservationHandler $optionPreservationHandler
     */
    public function __construct(LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue, Urls $urls, Database $database, TableService $tableService, SiteInfo $siteInfo, FreemiusScript $freemiusScript, OptionPreservationHandler $optionPreservationHandler)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue, $urls, $database, $tableService);
        $this->siteInfo                  = $siteInfo;
        $this->freemiusHelper            = $freemiusScript;
        $this->optionPreservationHandler = $optionPreservationHandler;
    }

    public static function getTaskName(): string
    {
        return 'push_preserve_options';
    }

    public static function getTaskTitle(): string
    {
        return 'Preserve options';
    }

    public function adjustOptionsTable(): TaskResponseDto
    {
        $this->logger->info("Preserve Data in " . $this->prodOptionsTable);

        if (!$this->isTableExists($this->prodOptionsTable)) {
            return $this->generateResponse();
        }

        $optionsToPreserve = $this->getPreservedOptions();

        // Preserve CloneOptions if current site is staging site
        if ($this->siteInfo->isStagingSite()) {
            $optionsToPreserve[] = CloneOptions::WPSTG_CLONE_SETTINGS_KEY;
        }

        // Preserve freemius options on the production site if present.
        if ($this->freemiusHelper->hasFreemiusOptions()) {
            $optionsToPreserve = array_merge($optionsToPreserve, $this->freemiusHelper->getFreemiusOptions());
        }

        $optionsToPreserve = Hooks::applyFilters(self::FILTER_PRESERVED_OPTIONS, $optionsToPreserve);
        $this->optionPreservationHandler->setProductionDb($this->wpdb);
        $likeStatement     = $this->optionPreservationHandler->getLikeStatement($optionsToPreserve);
        $productionOptions = $this->optionPreservationHandler->getOptionsDataToPreserve($likeStatement, $this->prodOptionsTable);

        if (empty($productionOptions)) {
            return $this->generateResponse();
        }

        // Delete any preserve data from pushed wpstgtmp_options table that already exist to insert the actually "preserved data" in the next step and not get any conflicts
        $this->optionPreservationHandler->deleteFromTable($likeStatement, $this->tmpOptionsTable);

        // Create insert preserved data queries for wpstgtmp_options tables
        $sql = $this->optionPreservationHandler->createInsertQuery($productionOptions, $this->tmpOptionsTable);

        $this->logger->debug("Preserve values " . json_encode($productionOptions));

        $this->executeBulk($sql);

        return $this->generateResponse();
    }
}
