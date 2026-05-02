<?php

namespace WPStaging\Pro\Staging\Ajax;

use wpdb;
use WPStaging\Backup\Service\Database\DatabaseImporter;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Adapter\Database\DatabaseException;
use WPStaging\Framework\Security\Auth;
use WPStaging\Framework\Database\DbInfo;
use WPStaging\Framework\Traits\TablePrefixValidator;
use WPStaging\Framework\Database\WpDbInfo;
use WPStaging\Framework\Utils\DBPermissions;
use WPStaging\Framework\Utils\Sanitize;
use WPStaging\Framework\Utils\Strings;
use WPStaging\Pro\Staging\Service\CompareExternalDatabase;

/**
 * @package WPStaging\Pro\Staging\Ajax
 */
class ExternalDatabase
{
    use TablePrefixValidator;

    /** @var Auth */
    private $auth;

    /** @var Sanitize */
    private $sanitize;

    /**
     * @var Strings
     */
    private $strHelper;

    /**
     * @var wpdb
     */
    private $productionDb;

    /**
     * @var WpDbInfo
     */
    private $productionDbInfo;

    /**
     * @var DbInfo
     */
    private $stagingDbInfo;


    public function __construct(Auth $auth, Sanitize $sanitize, Strings $strHelper)
    {
        $this->auth      = $auth;
        $this->sanitize  = $sanitize;
        $this->strHelper = $strHelper;
    }

    /**
     * Connect to external database for testing correct credentials
     * @return void
     */
    public function ajaxDatabaseConnect()
    {
        if (!$this->auth->isAuthenticatedRequest()) {
            return;
        }

        $args     = $_POST;
        $user     = !empty($args['databaseUser']) ? $this->sanitize->sanitizeString($args['databaseUser']) : '';
        $password = !empty($args['databasePassword']) ? $this->sanitize->sanitizePassword($args['databasePassword']) : '';
        $database = !empty($args['databaseDatabase']) ? $this->sanitize->sanitizeString($args['databaseDatabase']) : '';
        $server   = !empty($args['databaseServer']) ? $this->sanitize->sanitizeString($args['databaseServer']) : 'localhost';
        $prefix   = !empty($args['databasePrefix']) ? $this->sanitize->sanitizeString($args['databasePrefix']) : 'wp_';
        $useSsl   = !empty($args['databaseSsl']) && $this->sanitize->sanitizeBool($args['databaseSsl']);

        // make sure prefix doesn't contains any invalid character
        // same condition as in WordPress wpdb::set_prefix() method
        if (preg_match('|[^a-z0-9_]|i', $prefix)) {
            wp_send_json_error(['message' => __('Table prefix contains an invalid character.', 'wp-staging')]);
        }

        $tmpPrefixes = [
            DatabaseImporter::TMP_DATABASE_PREFIX,
            DatabaseImporter::TMP_DATABASE_PREFIX_TO_DROP,
        ];

        if (in_array($prefix, $tmpPrefixes)) {
            wp_send_json_error(['message' => 'Prefix wpstgtmp_ and wpstgbak_ are preserved by WP Staging and cannot be used for CLONING purpose! Please use another prefix.']);
        }

        if ($this->isWpStagingReservedPrefix($prefix)) {
            wp_send_json_error([
                'message' => sprintf(
                    __('The prefix "%s" is reserved by WP STAGING and cannot be used. Please use a different prefix like "wpstg0_", "wpstg1_", etc.', 'wp-staging'),
                    esc_html($prefix)
                ),
            ]);
        }

        // ensure tables with the given prefix exist, default false
        $ensurePrefixTableExist = !empty($args['databaseEnsurePrefixTableExist']) ? $this->sanitize->sanitizeBool($args['databaseEnsurePrefixTableExist']) : false;

        try {
            $this->stagingDbInfo = new DbInfo($server, $user, stripslashes($password), $database, $useSsl);
        } catch (DatabaseException $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }

        $wpdb = $this->stagingDbInfo->connect();

        // Check if any table with provided prefix already exist
        $existingTables = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->esc_like($prefix) . '%'));
        // used in new clone
        if ($existingTables !== null && !$ensurePrefixTableExist) {
            wp_send_json_error(['message' => sprintf(__("Tables with prefix '%s' already exist in database. Select another prefix.", 'wp-staging'), $prefix)]);
        }

        $this->productionDb     = WPStaging::make('wpdb');
        $this->productionDbInfo = new WpDbInfo($this->productionDb);

        if ($this->isAllowedDBPrefix($database, $server, $prefix) === false) {
            wp_send_json_error(['message' => sprintf(__("The prefix '%s' is not allowed as it begins with the production database prefix '%s'. Please choose a different prefix.", 'wp-staging'), $prefix, $this->productionDb->prefix)]);
        }

        // no need to check further for new clone
        if ($existingTables === null && !$ensurePrefixTableExist) {
            wp_send_json_success();
        }

        // used in edit and update of clone
        if ($existingTables === null && $ensurePrefixTableExist) {
            wp_send_json_error(['message' => sprintf(__("Tables with prefix '%s' do not exist in the database. Ensure that they do exist!", 'wp-staging'), $prefix)]);
        }

        $stagingSiteAddress     = $this->stagingDbInfo->getServerIp();
        $productionSiteAddress  = $this->productionDbInfo->getServerIp();

        if ($stagingSiteAddress === null || $productionSiteAddress === null) {
            wp_send_json_error(['message' => __('Unable to find database server hostname of the staging or the production site.', 'wp-staging')]);
        }

        $isSameAddress         = $productionSiteAddress === $stagingSiteAddress;
        $isSamePort            = $this->productionDbInfo->getServerPort() === $this->stagingDbInfo->getServerPort();
        $isSameServer          = ($isSameAddress && $isSamePort) || $server === DB_HOST;

        if ($database === DB_NAME && $prefix === $this->productionDb->prefix && $isSameServer) {
            wp_send_json_error(['message' => __('Cannot use production site database. Use another database.', 'wp-staging')]);
        }

        wp_send_json_success();
    }

    /**
     * Compare database and table properties of separate db with local db
     * @return void
     */
    public function ajaxDatabaseVerification()
    {
        if (!$this->auth->isAuthenticatedRequest()) {
            return;
        }

        $user     = !empty($_POST['databaseUser']) ? $this->sanitize->sanitizeString($_POST['databaseUser']) : '';
        $password = !empty($_POST['databasePassword']) ? $this->sanitize->sanitizePassword($_POST['databasePassword']) : '';
        $database = !empty($_POST['databaseDatabase']) ? $this->sanitize->sanitizeString($_POST['databaseDatabase']) : '';
        $server   = !empty($_POST['databaseServer']) ? $this->sanitize->sanitizeString($_POST['databaseServer']) : 'localhost';
        $useSsl   = !empty($_POST['databaseSsl']) && $this->sanitize->sanitizeBool($_POST['databaseSsl']);
        $prefix   = !empty($_POST['databasePrefix']) ? $this->sanitize->sanitizeString($_POST['databasePrefix']) : '';


        if ($this->isWpStagingReservedPrefix($prefix)) {
            wp_send_json_error([
                'message' => $this->getReservedPrefixErrorMessage($prefix),
            ]);
        }

        $comparison = null;
        try {
            $comparison = new CompareExternalDatabase($server, $user, stripslashes($password), $database, $useSsl);
        } catch (DatabaseException $ex) {
            wp_send_json_error(['error_type' => 'connection', 'message' => esc_html($ex->getMessage())]);
        }

        $results = $comparison->maybeGetComparison();
        wp_send_json($results);
    }

    /**
     * Verify if the user has the necessary privileges to access the database
     * @return void
     * @throws DatabaseException
     */
    public function ajaxVerifyDatabaseGrants()
    {
        if (!$this->auth->isAuthenticatedRequest()) {
            return;
        }

        $server   = !empty($_POST['databaseServer']) ? $this->sanitize->sanitizeString($_POST['databaseServer']) : 'localhost';
        $user     = !empty($_POST['databaseUser']) ? $this->sanitize->sanitizeString($_POST['databaseUser']) : '';
        $password = !empty($_POST['databasePassword']) ? $this->sanitize->sanitizePassword($_POST['databasePassword']) : '';
        $database = !empty($_POST['databaseDatabase']) ? $this->sanitize->sanitizeString($_POST['databaseDatabase']) : '';
        $useSsl   = !empty($_POST['databaseSsl']) && $this->sanitize->sanitizeBool($_POST['databaseSsl']);

        if (empty($user) || empty($password) || empty($database) || empty($server)) {
            wp_send_json_error(['message' => __('Database credentials are missing.', 'wp-staging')]);
        }

        try {
            $stagingDbInfo = new DbInfo($server, $user, stripslashes($password), $database, $useSsl);
            $wpdb          = $stagingDbInfo->connect();
            $dbPermissions = new DBPermissions($wpdb, $this->auth);
            $dbPermissions->ajaxCheckDBPermissions();
        } catch (\Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * @param $database
     * @param $server
     * @param $prefix
     * @return bool
     */
    private function isAllowedDBPrefix($database, $server, $prefix): bool
    {
        $isSameAddress = $this->productionDbInfo->getServerIp() === $this->stagingDbInfo->getServerIp();
        $isSamePort    = $this->productionDbInfo->getServerPort() === $this->stagingDbInfo->getServerPort();
        $isSameServer  = ($isSameAddress && $isSamePort) || $server === DB_HOST;

        if ($database === DB_NAME && $isSameServer && $this->strHelper->startsWith($prefix, $this->productionDb->prefix)) {
            return false;
        }

        return true;
    }
}
