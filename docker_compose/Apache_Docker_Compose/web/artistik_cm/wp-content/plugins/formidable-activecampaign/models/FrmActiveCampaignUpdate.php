<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Handle the plugin updating.
 */
class FrmActiveCampaignUpdate extends FrmAddon {

	public $plugin_file;
	public $plugin_name = 'Active Campaign';
	public $download_id = 20790298;
	public $version = '1.11';

	public function __construct() {
		$this->plugin_file = FrmActiveCampaignAppController::path() . '/formidable-activecampaign.php';
		parent::__construct();
	}

	public static function load_hooks() {
		add_filter( 'frm_include_addon_page', '__return_true' );
		new FrmActiveCampaignUpdate();
	}
}
