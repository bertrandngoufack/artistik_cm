<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Handle the automatic plugin updates
 */
class FrmSalesforceUpdate extends FrmAddon {

	public $plugin_file;
	public $plugin_name = 'Salesforce';
	public $download_id = 20266559;
	public $version = '2.05';

	public function __construct() {
		$this->plugin_file = FrmSalesforceAppController::path() . '/formidable-salesforce.php';
		parent::__construct();
	}

	public static function load_hooks() {
		add_filter( 'frm_include_addon_page', '__return_true' );
		new FrmSalesforceUpdate();
	}
}
