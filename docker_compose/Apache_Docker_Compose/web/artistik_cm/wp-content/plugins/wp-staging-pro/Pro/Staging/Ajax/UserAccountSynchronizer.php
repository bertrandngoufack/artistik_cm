<?php

namespace WPStaging\Pro\Staging\Ajax;

use WP_Error;
use wpdb;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Staging\Sites;
use WPStaging\Framework\Adapter\SourceDatabase;

/**
 * Synchronizes the current user account from the production site to a staging site
 */
class UserAccountSynchronizer extends AbstractTemplateComponent
{
    /** @var mixed */
    private $currentClone;

    /** @var wpdb */
    private $stagingDb;

    /** @var wpdb */
    private $productionDB;

    /**
     * @return void
     */
    public function ajaxSyncAccount()
    {
        if (!$this->canRenderAjax()) {
            wp_send_json_error(['message' => 'Invalid Request'], 403);
        }

        $result = $this->syncCurrentUser();
        if (is_wp_error($result)) {
            wp_send_json_error([
                'message' => sprintf(
                    esc_html__('Failed to synchronize user account: %s', 'wp-staging'),
                    $result->get_error_message()
                ),
            ]);
        }

        wp_send_json_success(['message' => esc_html__('The current user was successfully synchronized with the staging site.', 'wp-staging')]);
    }

    /**
     * @return int|WP_Error
     */
    public function syncCurrentUser()
    {
        $cloneID        = isset($_POST['clone']) ? sanitize_text_field($_POST['clone']) : null;
        $existingClones = get_option(Sites::STAGING_SITES_OPTION, []);
        if (!isset($existingClones[$cloneID])) {
            return new WP_Error('staging_site_not_found', esc_html__('Staging site not found.', 'wp-staging'));
        }

        $this->currentClone = $existingClones[$cloneID];

        /** @var SourceDatabase */
        $sourceDatabase = WPStaging::make(SourceDatabase::class);
        $sourceDatabase->setOptions((object)$this->currentClone);
        $this->stagingDb    = $sourceDatabase->getDatabase();
        $this->productionDB = WPStaging::make('wpdb');

        return $this->runSync();
    }

    /**
     * Run the synchronization
     *
     * @return int|WP_Error Number of rows affected on success, or WP_Error on failure.
     */
    protected function runSync()
    {
        $currentUserId           = get_current_user_id();
        $currentUserData         = (array)(wp_get_current_user()->data);
        $currentUserCapabilities = get_user_meta($currentUserId, $this->productionDB->prefix . 'capabilities', true);

        if (
            empty($this->currentClone['prefix']) ||
            empty($currentUserCapabilities) ||
            empty($currentUserData)
        ) {
            return new WP_Error('missing_user_data', esc_html__('Could not retrieve current user data or staging site database prefix.', 'wp-staging'));
        }

        $usersTable           = $this->currentClone['prefix'] . 'users';
        $usermetaTable        = $this->currentClone['prefix'] . 'usermeta';
        $cloneCapabilitiesKey = $this->currentClone['prefix'] . 'capabilities';

        $query        = $this->stagingDb->prepare("SELECT `ID` FROM `{$usersTable}` WHERE `ID` = %d", $currentUserId);
        $isUserExists = $this->stagingDb->query($query);

        $defaultColumns = $this->getTableColumns($usersTable);
        if (empty($defaultColumns)) {
            return new WP_Error('staging_users_table_missing', esc_html__('Could not read the staging site users table. It may not exist yet.', 'wp-staging'));
        }

        foreach ($currentUserData as $key => $value) {
            if (!in_array($key, $defaultColumns)) {
                unset($currentUserData[$key]);
            }
        }

        if (empty($isUserExists)) {
            return $this->insertUser($usersTable, $usermetaTable, $cloneCapabilitiesKey, $currentUserId, $currentUserData, $currentUserCapabilities);
        }

        return $this->updateUser($usersTable, $usermetaTable, $cloneCapabilitiesKey, $currentUserId, $currentUserData, $currentUserCapabilities);
    }

    /**
     * Insert a new user into the staging site database
     *
     * @param string $usersTable
     * @param string $usermetaTable
     * @param string $cloneCapabilitiesKey
     * @param int    $currentUserId
     * @param array  $currentUserData
     * @param mixed  $currentUserCapabilities
     * @return int|WP_Error
     */
    private function insertUser(string $usersTable, string $usermetaTable, string $cloneCapabilitiesKey, int $currentUserId, array $currentUserData, $currentUserCapabilities)
    {
        $existingUser = $this->stagingDb->query(
            $this->stagingDb->prepare(
                "SELECT `ID` FROM `{$usersTable}` WHERE `user_login` = %s OR `user_email` = %s",
                $currentUserData['user_login'],
                $currentUserData['user_email']
            )
        );

        if ($existingUser === false) {
            return new WP_Error('db_query_failed', esc_html__('Failed to query user data on the staging site database.', 'wp-staging'));
        }

        if (!empty($existingUser)) {
            return new WP_Error(
                'user_already_exists',
                esc_html__('A user with the same login or email already exists on the staging site with a different user ID.', 'wp-staging')
            );
        }

        $result = $this->stagingDb->insert($usersTable, $currentUserData);
        if ($result === false) {
            return new WP_Error('db_insert_failed', esc_html__('Failed to create user account in the staging site database.', 'wp-staging'));
        }

        $result = $this->stagingDb->insert($usermetaTable, [
            'user_id'    => $currentUserId,
            'meta_key'   => $cloneCapabilitiesKey,
            'meta_value' => serialize($currentUserCapabilities),
        ]);

        if ($result === false) {
            return new WP_Error('db_insert_meta_failed', esc_html__('User was created but failed to set user capabilities on the staging site.', 'wp-staging'));
        }

        return $result;
    }

    /**
     * Update an existing user in the staging site database
     *
     * @param string $usersTable
     * @param string $usermetaTable
     * @param string $cloneCapabilitiesKey
     * @param int    $currentUserId
     * @param array  $currentUserData
     * @param mixed  $currentUserCapabilities
     * @return int|WP_Error
     */
    private function updateUser(string $usersTable, string $usermetaTable, string $cloneCapabilitiesKey, int $currentUserId, array $currentUserData, $currentUserCapabilities)
    {
        $result = $this->stagingDb->update($usersTable, $currentUserData, ['ID' => $currentUserId]);
        if ($result === false) {
            return new WP_Error('db_update_failed', esc_html__('Failed to update user account on the staging site database.', 'wp-staging'));
        }

        $result = $this->stagingDb->update(
            $usermetaTable,
            [
                'user_id'    => $currentUserId,
                'meta_key'   => $cloneCapabilitiesKey,
                'meta_value' => serialize($currentUserCapabilities),
            ],
            [
                'user_id'  => $currentUserId,
                'meta_key' => $cloneCapabilitiesKey,
            ]
        );

        if ($result === false) {
            return new WP_Error('db_update_meta_failed', esc_html__('User was updated but failed to update capabilities on the staging site.', 'wp-staging'));
        }

        return $result;
    }

    /**
     * @param string $tableName Table name
     *
     * @return array
     */
    private function getTableColumns(string $tableName)
    {
        $columns = [];

        $result = $this->stagingDb->get_results("SHOW COLUMNS FROM `{$tableName}`", ARRAY_A);
        if (empty($result)) {
            return $columns;
        }

        foreach ($result as $row) {
            if (!isset($row['Field'])) {
                continue;
            }

            $columns[$row['Field']] = $row['Field'];
        }

        return $columns;
    }
}
