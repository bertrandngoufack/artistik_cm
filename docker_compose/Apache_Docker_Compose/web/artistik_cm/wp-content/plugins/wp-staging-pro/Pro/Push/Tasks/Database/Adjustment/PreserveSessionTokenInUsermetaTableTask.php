<?php

namespace WPStaging\Pro\Push\Tasks\Database\Adjustment;

use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Pro\Push\Tasks\DatabaseAdjustmentTask;

/**
 * This class is responsible for preserving the session token in the usermeta table during staging site push.
 */
class PreserveSessionTokenInUsermetaTableTask extends DatabaseAdjustmentTask
{
    public static function getTaskName(): string
    {
        return 'push_preserve_session_token_usermeta';
    }

    public static function getTaskTitle(): string
    {
        return 'Preserve session token in usermeta table';
    }

    public function execute(): TaskResponseDto
    {
        $this->setup();

        // usermeta table has been excluded from pushing process so exit here
        if ($this->isStagingTableExcluded('usermeta')) {
            $this->logger->warning("{$this->stagingPrefix}usermeta excluded. Skipping session token preservation step");
            return $this->generateResponse();
        }

        $tmpUserMetaTable = $this->tmpPrefix . 'usermeta';

        if ($this->isTableExists($tmpUserMetaTable) === false) {
            $this->logger->error("Fatal Error {$tmpUserMetaTable} does not exist.");
            return $this->generateResponse();
        }

        $this->logger->info("Updating $tmpUserMetaTable session tokens");

        $userId       = get_current_user_id();
        // Get session token for current user from live site usermeta table
        $sessionToken = $this->wpdb->get_var("SELECT meta_value FROM {$this->wpdb->base_prefix}usermeta WHERE meta_key = 'session_tokens' AND user_id = '$userId'");

        $sessionToken = unserialize($sessionToken);

        if (!$sessionToken) {
            $this->logger->warning("Can not get session token of current user from {$this->wpdb->base_prefix}usermeta");
            return $this->generateResponse();
        }

        // Update session_tokens
        $resultSessionToken = $this->wpdb->query(
            "UPDATE $tmpUserMetaTable SET meta_value = '" . serialize($sessionToken) . "' WHERE meta_key = 'session_tokens' AND user_id = $userId"
        );

        if ($resultSessionToken === false) {
            $this->logger->warning("Can not update row session_tokens in $tmpUserMetaTable");
        }

        return $this->generateResponse();
    }
}
