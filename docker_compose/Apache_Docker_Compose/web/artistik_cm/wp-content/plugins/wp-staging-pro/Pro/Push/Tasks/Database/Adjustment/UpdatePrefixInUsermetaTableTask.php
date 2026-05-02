<?php

namespace WPStaging\Pro\Push\Tasks\Database\Adjustment;

use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Pro\Push\Tasks\DatabaseAdjustmentTask;

/**
 * This class is responsible for updating the usermeta table prefix during staging site push.
 */
class UpdatePrefixInUsermetaTableTask extends DatabaseAdjustmentTask
{
    public static function getTaskName(): string
    {
        return 'push_update_usermeta_prefix';
    }

    public static function getTaskTitle(): string
    {
        return 'Update prefix in usermeta table';
    }

    public function execute(): TaskResponseDto
    {
        $this->setup();

        if ($this->stagingPrefix === $this->currentSitePrefix) {
            $this->logger->info("Prefix of production and live site is the same. Skip adjusting usermeta table prefix.");
            return $this->generateResponse();
        }

        // usermeta table has been excluded from pushing process so exit here
        if ($this->isStagingTableExcluded('usermeta')) {
            $this->logger->info("{$this->stagingPrefix}usermeta excluded. Skip adjusting usermeta table prefix");
            return $this->generateResponse();
        }

        $tmpUserMetaTable = $this->tmpPrefix . 'usermeta';

        if ($this->isTableExists($tmpUserMetaTable) === false) {
            $this->logger->error('Fatal Error ' . $tmpUserMetaTable . ' does not exist');
            return $this->generateResponse();
        }

        $this->logger->info("Updating {$tmpUserMetaTable} db prefix to {$this->currentSitePrefix}");

        $preparedQuery = $this->wpdb->prepare(
            "UPDATE {$tmpUserMetaTable} SET meta_key = replace(meta_key, %s, %s) WHERE meta_key LIKE %s",
            $this->stagingPrefix,
            $this->currentSitePrefix,
            $this->stagingPrefix . "_%"
        );

        $resultMetaKeys = $this->wpdb->query($preparedQuery);

        if ($resultMetaKeys === false) {
            $this->logger->error("Failed to update usermeta meta_key database table prefixes {$this->wpdb->last_error}. Query: " . $preparedQuery);
            return $this->generateResponse();
        }

        return $this->generateResponse();
    }
}
