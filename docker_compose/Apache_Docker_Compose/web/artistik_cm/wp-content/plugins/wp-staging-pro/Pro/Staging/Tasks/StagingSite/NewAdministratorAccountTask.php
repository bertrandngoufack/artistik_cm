<?php

namespace WPStaging\Pro\Staging\Tasks\StagingSite;

use Exception;
use WPStaging\Framework\Database\TableService;
use WPStaging\Framework\Job\Dto\JobDataDto;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Staging\Interfaces\AdvanceStagingOptionsInterface;
use WPStaging\Staging\Interfaces\StagingDatabaseDtoInterface;
use WPStaging\Staging\Interfaces\StagingNetworkDtoInterface;
use WPStaging\Staging\Interfaces\StagingOperationDtoInterface;
use WPStaging\Staging\Interfaces\StagingSiteDtoInterface;
use WPStaging\Staging\Traits\WithStagingDatabase;
use WPStaging\Staging\Tasks\StagingTask;

/**
 * Replacement for WPStaging\Pro\Staging\Data\Steps\NewAdminAccount
 */
class NewAdministratorAccountTask extends StagingTask
{
    use WithStagingDatabase;

    /** @var JobDataDto|StagingOperationDtoInterface|StagingNetworkDtoInterface|StagingDatabaseDtoInterface|StagingSiteDtoInterface|AdvanceStagingOptionsInterface $jobDataDto */
    protected $jobDataDto; // @phpstan-ignore-line

    protected $wpdb = null;

    /**
     * @return string
     */
    public static function getTaskName()
    {
        return 'staging_new_administrator_account';
    }

    /**
     * @return string
     */
    public static function getTaskTitle()
    {
        return 'Adding new administrator account';
    }

    /**
     * @return TaskResponseDto
     * @throws Exception
     */
    public function execute()
    {
        $this->initStagingDatabase($this->jobDataDto->getStagingSite());
        $this->tableService = new TableService($this->stagingDb);

        $this->logger->info("Adding new administrator account");
        if ($this->isTableExcluded('users') || $this->isTableExcluded('usermeta')) {
            $this->logger->warning("Skip adding administrator account, user table(s) skipped.");
            return $this->generateResponse();
        }

        if (empty($this->jobDataDto->getAdminEmail()) || empty($this->jobDataDto->getAdminPassword())) {
            $this->logger->warning("Could not create new administrator user, missing email or password.");
            return $this->generateResponse();
        }

        $this->wpdb    = $this->stagingDb->getWpdb();
        $usersTable    = $this->jobDataDto->getDatabasePrefix() . 'users';
        $adminEmail    = $this->jobDataDto->getAdminEmail();
        $adminPassword = $this->jobDataDto->getAdminPassword();

        $query = "SELECT COUNT(*) as count FROM `{$usersTable}` WHERE `user_email` = %s;";
        $query = $this->wpdb->prepare($query, [
            $adminEmail
        ]);

        $count = $this->getCount($query);
        if ($count > 0) {
            $this->updateAdministratorPassword($usersTable, $adminEmail, $adminPassword);
            return $this->generateResponse();
        }

        $name     = explode('@', $adminEmail)[0];
        $username = $this->getAvailableUsername($name, $usersTable);
        if ($this->insertUser($name, $username, $adminEmail, $adminPassword, $usersTable) === false) {
            $this->logger->warning("Could not create new admin user {$query}.");
            return $this->generateResponse();
        }

        $userId    = (int)$this->wpdb->insert_id;
        $metaTable = $this->jobDataDto->getDatabasePrefix() . 'usermeta';

        $this->clearExistingMetas($userId, $metaTable);

        if (!$this->addAdminCapabilities($userId, $this->jobDataDto->getDatabasePrefix(), $metaTable)) {
            $this->logger->warning("Could not add admin capabilities to user {$query}.");
            return $this->generateResponse();
        }

        // Bail: No need to add support for super admin for single site and subsite staging
        if (!is_multisite() || !$this->jobDataDto->getIsStagingNetwork()) {
            $this->logger->info("New admininistrator account added with username: " . $username . ".");
            return $this->generateResponse();
        }

        if ($this->isTableExcluded("sitemeta")) {
            $this->logger->warning(sprintf("Sitemeta table excluded! New administrator account is added with username `%s` but couldn't be made super admin!", $username));
            return $this->generateResponse();
        }

        $siteMetaTable = $this->jobDataDto->getDatabasePrefix() . 'sitemeta';
        $this->addUserToSuperAdmins($username, $siteMetaTable);

        return $this->generateResponse();
    }

    /**
     * Check if the table excluded.
     * @param string $tableNameWithoutPrefix
     * @return bool
     */
    protected function isTableExcluded(string $tableNameWithoutPrefix): bool
    {
        $tableName = $this->jobDataDto->getDatabasePrefix() . $tableNameWithoutPrefix;
        if (!$this->tableService->tableExists($tableName)) {
            return true;
        }

        if (in_array($tableNameWithoutPrefix, $this->jobDataDto->getExcludedTables())) {
            return true;
        }

        return false;
    }

    protected function updateAdministratorPassword(string $usersTable, string $adminEmail, string $adminPassword)
    {
        $hashedPassword = wp_hash_password($adminPassword);

        $query = "UPDATE `{$usersTable}` SET `user_pass` = %s WHERE `user_email` = %s;";
        $query = $this->wpdb->prepare($query, [
            $hashedPassword,
            $adminEmail,
        ]);

        $this->wpdb->query($query);

        $this->logger->warning(sprintf("Administrator user '%s' password is updated, as the email already exists.", $adminEmail));
    }

    /**
     * @param string $name
     * @param string $usersTable
     * @return string
     */
    protected function getAvailableUsername(string $name, string $usersTable): string
    {
        $username = $name . '_' . substr(md5((string)time()), 0, 4);

        $query = "SELECT COUNT(*) as count FROM `{$usersTable}` WHERE `user_login` = %s;";
        $query = $this->wpdb->prepare($query, [
            $username
        ]);

        $count = $this->getCount($query);
        if ($count === 0) {
            return $username;
        }

        return $this->getAvailableUsername($name, $usersTable);
    }

    protected function getCount(string $query): int
    {
        $result = $this->wpdb->get_results($query);
        if (empty($result)) {
            return 0;
        }

        return (int)$result[0]->count;
    }

    protected function insertUser(string $name, string $username, string $email, string $password, string $usersTable): bool
    {
        $hashedPassword = wp_hash_password($password);

        $query = "INSERT INTO `{$usersTable}` ( `user_login`, `user_pass`, `user_nicename`, `display_name`, `user_email`, `user_status` ) VALUES ( %s, %s, %s, %s, %s, %s );";
        $query = $this->wpdb->prepare($query, [
            $username,
            $hashedPassword,
            $name,
            $name,
            $email,
            0,
        ]);

        return $this->wpdb->query($query) !== false;
    }

    protected function clearExistingMetas(int $userId, string $metaTable)
    {
        $query = "DELETE FROM `{$metaTable}` WHERE `user_id` = %s;";
        $query = $this->wpdb->prepare($query, [
            $userId,
        ]);

        $this->wpdb->query($query);
    }

    protected function addAdminCapabilities(int $userId, string $stagingPrefix, string $metaTable): bool
    {
        $query = "INSERT INTO `{$metaTable}` ( `umeta_id`, `user_id`, `meta_key`, `meta_value` ) VALUES ( NULL , %s, %s, %s );";
        $query = $this->wpdb->prepare($query, [
            $userId,
            $stagingPrefix . 'capabilities',
            serialize([
                'administrator' => true,
            ]),
        ]);

        if ($this->wpdb->query($query) === false) {
            return false;
        }

        $query = "INSERT INTO `{$metaTable}` ( `umeta_id`, `user_id`, `meta_key`, `meta_value` ) VALUES ( NULL , %s, %s, %s );";
        $query = $this->wpdb->prepare($query, [
            $userId,
            $stagingPrefix . 'user_level',
            10,
        ]);

        return $this->wpdb->query($query) !== false;
    }

    /**
     * @param string $username
     * @param string $siteMetaTable
     */
    protected function addUserToSuperAdmins(string $username, string $siteMetaTable)
    {
        $query  = "SELECT * FROM `{$siteMetaTable}` WHERE `meta_key` = 'site_admins';";
        $result = $this->wpdb->get_results($query);
        if (empty($result)) {
            $this->logger->warning(sprintf("Query failed for getting super admins! New administrator account added with username `%s` but couldn't be made super admin!", $username));
            return;
        }

        $this->logger->info(sprintf("New administrator account added with username `%s`. Adding to super administrator.", $username));
        foreach ($result as $row) {
            $admins = unserialize($row->meta_value);
            if (!is_array($admins)) {
                $admins = [];
            }

            $admins[] = $username;
            $admins   = array_unique($admins);

            $query = "UPDATE `{$siteMetaTable}` SET `meta_value` = %s WHERE `meta_id` = %s AND `site_id` = %s;";
            $query = $this->wpdb->prepare($query, [
                serialize($admins),
                $row->meta_id,
                $row->site_id,
            ]);

            if ($this->wpdb->query($query) === false) {
                $this->logger->warning(sprintf("Could not update site admins for site %s!", $row->site_id));
            } else {
                $this->logger->info(sprintf("Admin account made site admin for site %s!", $row->site_id));
            }
        }
    }
}
