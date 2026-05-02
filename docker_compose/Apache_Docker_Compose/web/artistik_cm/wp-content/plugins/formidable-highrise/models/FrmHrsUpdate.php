<?php

class FrmHrsUpdate extends FrmAddon {

	public $plugin_file;
	public $plugin_name = 'Highrise';
	public $download_id = 180495;
	public $version = '1.06';

	public function __construct() {
		$this->plugin_file = dirname( dirname( __FILE__ ) ) . '/formidable-highrise.php';
		parent::__construct();
	}

	public static function load_hooks() {
		add_filter( 'frm_include_addon_page', '__return_true' );
		new FrmHrsUpdate();
	}

}
