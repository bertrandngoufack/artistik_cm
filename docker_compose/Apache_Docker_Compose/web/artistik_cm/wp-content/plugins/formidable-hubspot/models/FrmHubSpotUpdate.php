<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmHubSpotUpdate extends FrmAddon {

	public $plugin_file;
	public $plugin_name = 'HubSpot';
	public $download_id = 20811871;
	public $version;

	public function __construct() {
		$this->plugin_file = FrmHubSpotAppHelper::path() . '/formidable-hubspot.php';
		$this->version     = FrmHubSpotAppHelper::$plug_version;
		parent::__construct();
	}

	public static function load_hooks() {
		add_filter( 'frm_include_addon_page', '__return_true' );
		new FrmHubSpotUpdate();
	}
}
