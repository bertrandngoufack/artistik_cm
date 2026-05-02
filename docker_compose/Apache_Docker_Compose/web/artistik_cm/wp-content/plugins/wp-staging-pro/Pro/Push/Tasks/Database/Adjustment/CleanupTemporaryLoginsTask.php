<?php

namespace WPStaging\Pro\Push\Tasks\Database\Adjustment;

use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Pro\Auth\TemporaryLogins;
use WPStaging\Pro\Push\Tasks\DatabaseAdjustmentTask;

/**
 * This class is responsible for cleaning up temporary logins during staging site push.
 */
class CleanupTemporaryLoginsTask extends DatabaseAdjustmentTask
{
    public static function getTaskName(): string
    {
        return 'push_cleanup_temporary_logins';
    }

    public static function getTaskTitle(): string
    {
        return 'Cleanup temporary logins';
    }

    public function execute(): TaskResponseDto
    {
        $this->setup();

        if ($this->isStagingTableExcluded('users') || $this->isStagingTableExcluded('usermeta')) {
            $this->logger->warning("Users or usermeta table excluded. Skipping temporary users removal.");
            return $this->generateResponse();
        }

        $tmpUsersTable    = $this->tmpPrefix . 'users';
        $tmpUserMetaTable = $this->tmpPrefix . 'usermeta';
        if (!$this->isTableExists($tmpUsersTable) || !$this->isTableExists($tmpUserMetaTable)) {
            $this->logger->error("Fatal Error: {$tmpUsersTable} or {$tmpUserMetaTable} does not exist.");
            return $this->generateResponse();
        }

        $this->logger->info("Delete temporary users and their meta from {$tmpUsersTable} and {$tmpUserMetaTable}.");
        // Clean both types of temporary users:
        // 1. wpstgtmpuser* - Direct staging temporary users
        // 2. wpstg_* - Magic login link users
        $escapedPrefix   = $this->wpdb->esc_like(TemporaryLogins::LOGIN_LINK_PREFIX); // wpstgtmpuser
        $loginLinkPrefix = $this->wpdb->esc_like('wpstg_'); // magic login users

        $prepare = $this->wpdb->prepare(
            "DELETE t1, t2 FROM {$tmpUsersTable} as t1 LEFT JOIN {$tmpUserMetaTable} as t2 ON t1.ID = t2.user_id WHERE t1.user_login LIKE %s OR t1.user_login LIKE %s",
            $escapedPrefix . '%',
            $loginLinkPrefix . '%'
        );

        $result = $this->wpdb->query($prepare);
        if ($result === false) {
            $this->logger->error("Failed to delete temporary users {$this->wpdb->last_error}. Query: " . $prepare);
            return $this->generateResponse();
        }

        if ($result > 0) {
            $this->logger->info("Successfully deleted temporary users and their metadata.");
        } else {
            $this->logger->info("No temporary users found in temporary tables.");
        }

        return $this->generateResponse();
    }
}
