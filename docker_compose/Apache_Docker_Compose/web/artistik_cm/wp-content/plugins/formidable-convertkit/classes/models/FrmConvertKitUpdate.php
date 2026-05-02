<?php
/**
 * Addon update class
 *
 * @package FrmConvertKit
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmConvertKitUpdate
 */
class FrmConvertKitUpdate extends FrmAddon {

	/**
	 * Plugin file path.
	 *
	 * @var string
	 */
	public $plugin_file;

	/**
	 * Plugin name.
	 *
	 * @var string
	 */
	public $plugin_name = 'ConvertKit';

	/**
	 * Download ID.
	 *
	 * @var int
	 */
	public $download_id = 28286367;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public $version;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->plugin_file = FrmConvertKitAppHelper::plugin_file();
		$this->version     = FrmConvertKitAppHelper::$plug_version;
		parent::__construct();
	}
}
