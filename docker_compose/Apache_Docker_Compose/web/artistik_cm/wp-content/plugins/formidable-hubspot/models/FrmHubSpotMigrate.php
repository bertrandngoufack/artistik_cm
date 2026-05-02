<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
/**
 * Migrations.
 *
 * @since 2.0
 */
class FrmHubSpotMigrate {

	/**
	 * Db version.
	 *
	 * @var int|float
	 */
	private $new_db_version = 2.0;

	/**
	 * Current DB version.
	 *
	 * @var int|float
	 */
	private $current_db_version = 1.10;

	/**
	 * Option name.
	 *
	 * @var string
	 */
	private $option_name = 'frm_hubspot_db';

	/**
	 * Migrations.
	 *
	 * @var array
	 */
	private $migrations = array( 1 );

	/**
	 * FrmHubSpotMigrate constructor
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	private function __construct() {
		// Check if the database version needs updating or initializing.
		if ( $this->needs_migration() ) {
			$this->update_db_version();
		}
	}

	/**
	 * Run the migration on admin init hook.
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public static function init() {
		new self();
	}

	/**
	 * Save the db version to the database.
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	private function update_db_version() {
		update_option( $this->option_name, $this->new_db_version );
	}

	/**
	 * Get current db version.
	 *
	 * @since 2.0
	 *
	 * @return int|float
	 */
	private function get_current_db_version() {
		$this->current_db_version = (int) get_option( $this->option_name, $this->current_db_version );
		return $this->current_db_version;
	}

	/**
	 * Go through all necessary migrations in order to migrate db to the current version.
	 *
	 * @since 2.0
	 * @return bool
	 */
	private function needs_migration() {
		$eligible_to_update = ( $this->get_current_db_version() < $this->new_db_version );

		if ( ! $eligible_to_update ) {
			return false;
		}

		foreach ( $this->migrations as $migrate_to_version ) {
			$function_name = 'migrate_to_' . $migrate_to_version;
			$this->$function_name();
		}

		return true;
	}

	/**
	 * Convert saved values to new format.
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	private function migrate_to_1() {
		$old_settings = get_option( 'frm_hubspot_options' );

		// Since there wasn't any migration class before so we can not know whether it's a fresh install or not so we are checking it at this stage.
		if ( ! isset( $old_settings->api_key ) || ! isset( $old_settings->private_app_access_token ) ) {
			return;
		}

		$settings                           = new FrmHubSpotSettings();
		$settings->api_key                  = isset( $old_settings->api_key ) ? $old_settings->api_key : $settings->api_key;
		$settings->private_app_access_token = isset( $old_settings->private_app_access_token ) ? $old_settings->private_app_access_token : $settings->private_app_access_token;

		$settings->store();

		$api = new FrmHubSpotAuthHelper( FrmHubSpotAppHelper::get_active_authorization_class() );
		$api->clear_cache();
	}

}
