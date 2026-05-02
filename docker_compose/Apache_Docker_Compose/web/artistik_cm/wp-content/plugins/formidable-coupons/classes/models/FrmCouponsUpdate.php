<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmCouponsUpdate extends FrmAddon {

	/**
	 * @var string
	 */
	public $plugin_file;

	/**
	 * @var string
	 */
	public $plugin_name = 'Formidable Coupons';

	/**
	 * @var int
	 */
	public $download_id = 28340061;

	/**
	 * @var string
	 */
	public $version;

	/**
	 * Set up a new Coupons plugin updater.
	 */
	public function __construct() {
		$this->version     = FrmCouponsAppHelper::plugin_version();
		$this->plugin_file = FrmCouponsAppHelper::path() . '/formidable-coupons.php';
		parent::__construct();
	}

	/**
	 * @return void
	 */
	public static function load_hooks() {
		add_filter( 'frm_include_addon_page', '__return_true' );
		new FrmCouponsUpdate();
	}
}
