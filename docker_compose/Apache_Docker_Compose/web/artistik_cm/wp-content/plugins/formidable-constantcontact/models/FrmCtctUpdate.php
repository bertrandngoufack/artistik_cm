<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmCtctUpdate extends FrmAddon {

	/**
	 * @var string|null $plugin_file
	 */
	public $plugin_file;

	/**
	 * @var string $plugin_name
	 */
	public $plugin_name = 'Constant Contact';

	/**
	 * @var int $download_id
	 */
	public $download_id = 20826884;

	/**
	 * @var string|null $version
	 */
	public $version;

	public function __construct() {
		$this->version     = FrmCtctAppController::$plug_version;
		$this->plugin_file = FrmCtctAppController::path() . '/formidable-constantcontact.php';
		parent::__construct();
	}

	public static function load_hooks() {
		add_filter( 'frm_include_addon_page', '__return_true' );
		new FrmCtctUpdate();
	}
}
