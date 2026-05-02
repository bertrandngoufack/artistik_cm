<?php

namespace WPStaging\Pro\Push\Tasks\Database\Adjustment;

use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Pro\Push\Tasks\OptionAdjustmentTask;

/**
 * This class is responsible for updating prefix in options table during staging site push.
 */
class UpdatePrefixInOptionsTableTask extends OptionAdjustmentTask
{
    public static function getTaskName(): string
    {
        return 'push_update_options_prefix';
    }

    public static function getTaskTitle(): string
    {
        return 'Update prefix in options table';
    }

    public function adjustOptionsTable(): TaskResponseDto
    {
        $this->logger->info("Updating {$this->tmpOptionsTable} table prefix to {$this->wpdb->prefix}.");
        $this->logger->debug("SQL - UPDATE {$this->tmpOptionsTable} SET option_name = replace(option_name, {$this->stagingPrefix}, {$this->wpdb->prefix}) WHERE option_name LIKE {$this->stagingPrefix}_%");

        $resultOptions = $this->wpdb->query(
            $this->wpdb->prepare(
                // db_version is a wp option_name and should be excluded in case staging db prefix is 'db_'
                "UPDATE IGNORE {$this->tmpOptionsTable} SET option_name= replace(option_name, %s, %s) WHERE option_name LIKE %s AND option_name <> 'db_version'",
                $this->stagingPrefix,
                $this->wpdb->prefix,
                $this->stagingPrefix . "_%"
            )
        );

        if ($resultOptions === false) {
            $this->logger->error("Failed to update {$this->tmpOptionsTable} with table prefixes. DB Error: {$this->wpdb->last_error}");
        }

        return $this->generateResponse();
    }
}
