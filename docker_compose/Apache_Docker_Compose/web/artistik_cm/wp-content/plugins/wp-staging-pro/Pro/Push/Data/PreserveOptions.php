<?php

namespace WPStaging\Pro\Push\Data;

use WPStaging\Framework\SiteInfo;
use WPStaging\Staging\CloneOptions;
use WPStaging\Framework\Database\OptionPreservationHandler;
use WPStaging\Framework\ThirdParty\FreemiusScript;
use WPStaging\Framework\Facades\Hooks;
use WPStaging\Pro\Traits\PreservedOptionsTrait;
use WPStaging\Pro\Push\Tasks\Database\Adjustment\PreserveOptionsTask;

class PreserveOptions extends OptionsTablePushService
{
    use PreservedOptionsTrait;

    private $optionPreservationHandler;
    private $freemiusHelper;
    private $siteInfo;

    /**
     * @param FreemiusScript $freemiusScript
     * @param SiteInfo $siteInfo
     * @param OptionPreservationHandler $optionPreservationHandler
     */
    public function __construct(FreemiusScript $freemiusScript, SiteInfo $siteInfo, OptionPreservationHandler $optionPreservationHandler)
    {
        $this->freemiusHelper            = $freemiusScript;
        $this->siteInfo                  = $siteInfo;
        $this->optionPreservationHandler = $optionPreservationHandler;
    }

    /**
     * @inheritDoc
     */
    protected function processOptionsTable(): bool
    {
        $this->log("Preserve Data in " . $this->prodOptionsTable);

        if (!$this->tableExists($this->prodOptionsTable)) {
            return true;
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

        $optionsToPreserve = Hooks::applyFilters(PreserveOptionsTask::FILTER_PRESERVED_OPTIONS, $optionsToPreserve);
        $this->optionPreservationHandler->setProductionDb($this->productionDb);
        $likeStatement     = $this->optionPreservationHandler->getLikeStatement($optionsToPreserve);
        $productionOptions = $this->optionPreservationHandler->getOptionsDataToPreserve($likeStatement, $this->prodOptionsTable);

        if (empty($productionOptions)) {
            return true;
        }

        // Delete any preserve data from pushed wpstgtmp_options table that already exist to insert the actually "preserved data" in the next step and not get any conflicts
        $this->optionPreservationHandler->deleteFromTable($likeStatement, $this->tmpOptionsTable);

        // Create insert preserved data queries for wpstgtmp_options tables
        $sql = $this->optionPreservationHandler->createInsertQuery($productionOptions, $this->tmpOptionsTable);

        $this->debugLog("Preserve values " . json_encode($productionOptions));

        $this->executeSql($sql);

        return true;
    }
}
