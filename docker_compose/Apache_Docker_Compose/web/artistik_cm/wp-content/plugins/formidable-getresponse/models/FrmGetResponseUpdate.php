<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Handle the plugin updating.
 */
class FrmGetResponseUpdate extends FrmAddon {

	public $plugin_file;
	public $plugin_name = 'GetResponse';
	public $download_id = 20813244;
	public $version = '1.07';

	public function __construct() {
		$this->plugin_file = FrmGetResponseAppController::path() . '/formidable-getresponse.php';
		parent::__construct();
	}

	public static function load_hooks() {
		add_filter( 'frm_include_addon_page', '__return_true' );
		new FrmGetResponseUpdate();
	}
}
