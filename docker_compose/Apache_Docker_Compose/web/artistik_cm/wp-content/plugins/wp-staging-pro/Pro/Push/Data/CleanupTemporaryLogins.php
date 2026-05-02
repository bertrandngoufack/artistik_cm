<?php

namespace WPStaging\Pro\Push\Data;

use WPStaging\Pro\Auth\TemporaryLogins;

class CleanupTemporaryLogins extends DBPushService
{
    /**
     * @inheritDoc
     */
    protected function internalExecute()
    {
        if ($this->stagingPrefix === $this->productionDb->prefix) {
            $this->log("Skipping. Prefix of production and live site is the same: {$this->productionDb->prefix}.");
            return true;
        }

        if ($this->isTableExcluded($this->stagingPrefix . 'users') || $this->isTableExcluded($this->stagingPrefix . 'usermeta')) {
            $this->log("Users or usermeta table excluded. Skipping temporary users removal.");
            return true;
        }

        $tmpUsersTable    = $this->getTmpPrefix() . 'users';
        $tmpUserMetaTable = $this->getTmpPrefix() . 'usermeta';

        if ($this->isTable($tmpUsersTable) === false || $this->isTable($tmpUserMetaTable) === false) {
            $this->log("Fatal Error: {$tmpUsersTable} or {$tmpUserMetaTable} does not exist.");
            $this->returnException("Fatal Error: {$tmpUsersTable} or {$tmpUserMetaTable} does not exist.");
            return false;
        }

        $this->log("Delete temporary users and their meta from {$tmpUsersTable} and {$tmpUserMetaTable}.");
        // Clean both types of temporary users:
        // 1. wpstgtmpuser* - Direct staging temporary users
        // 2. wpstg_* - Magic login link users
        $escapedPrefix   = $this->productionDb->esc_like(TemporaryLogins::LOGIN_LINK_PREFIX); // wpstgtmpuser
        $loginLinkPrefix = 'wpstg_'; // magic login users

        $prepare = $this->productionDb->prepare(
            "DELETE t1, t2 FROM {$tmpUsersTable} as t1 LEFT JOIN {$tmpUserMetaTable} as t2 ON t1.ID = t2.user_id WHERE t1.user_login LIKE %s OR t1.user_login LIKE %s",
            $escapedPrefix . '%',
            $loginLinkPrefix . '%'
        );

        $result = $this->productionDb->query($prepare);
        if ($result === false) {
            $this->log("SQL - " . $prepare);
            $this->log("Failed to delete temporary users {$this->productionDb->last_error}");
            $this->returnException("Failed to delete temporary users {$this->productionDb->last_error}");
            return false;
        }

        if ($result > 0) {
            $this->log("Successfully deleted temporary users and their metadata.");
        } else {
            $this->log("No temporary users found in temporary tables.");
        }

        return true;
    }
}
