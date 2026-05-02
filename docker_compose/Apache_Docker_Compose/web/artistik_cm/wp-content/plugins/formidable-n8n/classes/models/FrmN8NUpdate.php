<?php
/**
 * Addon update class
 *
 * @package FrmN8N
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmN8NUpdate
 */
class FrmN8NUpdate extends FrmAddon {

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
	public $plugin_name = 'Formidable n8n';

	/**
	 * Download ID.
	 *
	 * @var int
	 */
	public $download_id = 28339372;

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
		$this->plugin_file = FrmN8NAppHelper::plugin_file();
		$this->version     = FrmN8NAppHelper::$plug_version;
		parent::__construct();
	}
}
