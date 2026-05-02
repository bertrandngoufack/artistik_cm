<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTestModeUpdate extends FrmAddon {

	/**
	 * @var string $plugin_file
	 */
	public $plugin_file;

	/**
	 * @var string $plugin_name
	 */
	public $plugin_name = 'Formidable Test Mode';

	/**
	 * @var int $download_id
	 */
	public $download_id = 28337572;

	/**
	 * @var string $version
	 */
	public $version;

	public function __construct() {
		$this->version     = FrmTestModeAppHelper::plugin_version();
		$this->plugin_file = FrmTestModeAppHelper::path() . '/formidable-test-mode.php';
		parent::__construct();
	}

	/**
	 * @return void
	 */
	public static function load_hooks() {
		add_filter( 'frm_include_addon_page', '__return_true' );
		new FrmTestModeUpdate();
	}
}
