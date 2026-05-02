<?php

namespace WPStaging\Pro\Staging\Tasks\StagingSite\DatabaseAdjustment;

use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Staging\Tasks\DatabaseAdjustmentTask;

/**
 * Replacement for WPStaging\Pro\Staging\Data\Steps\MultisiteAddNetworkAdministrators
 * This class is responsible for adding missing capabilities when for all super admin when cloning a subsite into single staging site
 */
class AdjustSubsiteStagingAdministratorsTask extends DatabaseAdjustmentTask
{
    /**
     * @return string
     */
    public static function getTaskName()
    {
        return 'staging_adjust_super_admin';
    }

    /**
     * @return string
     */
    public static function getTaskTitle()
    {
        return 'Adjust super administrators for the staging site';
    }

    /**
     * @return TaskResponseDto
     */
    public function execute()
    {
        $this->setup();
        $this->logger->info('Adding missing capabilities for super admins for the staging site.');
        if ($this->isTableExcluded('usermeta')) {
            $this->logger->warning('Skipping super admins capabilities adjustment as usermeta table is excluded.');
            return $this->generateResponse();
        }

        $productionDb  = $this->database->getWpdb();
        $stagingPrefix = $this->jobDataDto->getDatabasePrefix();
        $basePrefix    = $productionDb->base_prefix;

        foreach (get_super_admins() as $username) {
            // Get user id
            $userId = $productionDb->get_var("SELECT ID FROM {$basePrefix}users WHERE user_login = '{$username}';");

            // Check if user capability already exists
            $capabilityExists = $this->wpdb->get_var("SELECT user_id FROM {$stagingPrefix}usermeta WHERE user_id = '{$userId}' AND meta_key = '{$stagingPrefix}capabilities';");

            // Do nothing if already exists
            if (!empty($capabilityExists)) {
                continue;
            }

            // Add new capability
            $query = $this->wpdb->prepare(
                "INSERT INTO `{$stagingPrefix}usermeta` ( `umeta_id`, `user_id`, `meta_key`, `meta_value` ) VALUES ( NULL , %s, %s, %s );",
                $userId,
                $stagingPrefix . 'capabilities',
                serialize(
                    [
                        'administrator' => true,
                    ]
                )
            );

            if ($this->wpdb->query($query) === false) {
                $this->logger->warning("Could not execute query {$query}.");
            } else {
                $this->logger->info("Added missing capabilities for super admin {$username}.");
            }
        }

        return $this->generateResponse();
    }
}
