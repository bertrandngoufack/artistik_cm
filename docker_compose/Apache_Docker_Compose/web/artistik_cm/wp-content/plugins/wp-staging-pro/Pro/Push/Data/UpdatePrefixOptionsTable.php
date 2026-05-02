<?php

namespace WPStaging\Pro\Push\Data;

use WPStaging\Core\Utils\Logger;
use WPStaging\Pro\Traits\PreservedOptionsTrait;

class UpdatePrefixOptionsTable extends OptionsTablePushService
{
    use PreservedOptionsTrait;

    /**
     * @inheritDoc
     */
    protected function processOptionsTable()
    {
        $this->log("Updating {$this->tmpOptionsTable} table prefix to {$this->productionDb->prefix}.");
        $this->debugLog("SQL - UPDATE {$this->tmpOptionsTable} SET option_name = replace(option_name, {$this->stagingPrefix}, {$this->productionDb->prefix}) WHERE option_name LIKE {$this->stagingPrefix}_%");

        $wpStagingProtectedOptions = $this->getPrefixProtectedOptions();

        $excludeConditions = [];
        foreach ($wpStagingProtectedOptions as $option) {
            $condition = $this->productionDb->prepare('option_name <> %s', $option);
            if (is_string($condition)) {
                $excludeConditions[] = $condition;
            }
        }

        $excludeClause = !empty($excludeConditions) ? implode(' AND ', $excludeConditions) : '1=1';
        $resultOptions = $this->productionDb->query(
            $this->productionDb->prepare(
                "UPDATE IGNORE {$this->tmpOptionsTable} SET option_name = replace(option_name, %s, %s) WHERE option_name LIKE %s AND {$excludeClause}",
                $this->stagingPrefix,
                $this->productionDb->prefix,
                $this->stagingPrefix . "_%"
            )
        );

        if ($resultOptions === false) {
            $this->log("Failed to update {$this->tmpOptionsTable} with table prefixes. DB Error: {$this->productionDb->last_error}", Logger::TYPE_ERROR);
            $this->returnException("Failed to update {$this->tmpOptionsTable} with table prefixes {$this->productionDb->prefix}. DB Error: {$this->productionDb->last_error}");
            return false;
        }

        return true;
    }
}
