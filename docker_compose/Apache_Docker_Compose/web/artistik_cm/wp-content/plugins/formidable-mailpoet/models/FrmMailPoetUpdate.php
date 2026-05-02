<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmMailPoetUpdate extends FrmAddon {

	public $plugin_file;
	public $plugin_name = 'MailPoet Newsletters';
	public $download_id = 20781560;
	public $version = '1.04';

	public function __construct() {
		$this->plugin_file = FrmMailPoetAppController::path() . '/formidable-mailpoet.php';
		parent::__construct();
	}

	public static function load_hooks() {
		add_filter( 'frm_include_addon_page', '__return_true' );
		new FrmMailPoetUpdate();
	}
}
