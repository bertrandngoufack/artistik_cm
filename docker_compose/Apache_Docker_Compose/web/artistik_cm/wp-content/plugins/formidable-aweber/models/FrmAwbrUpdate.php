<?php

class FrmAwbrUpdate extends FrmAddon {

	public $plugin_file;
	public $plugin_name = 'Aweber';
	public $download_id = 168468;
	public $version = '2.05';

	public function __construct() {
		$this->plugin_file = dirname( __DIR__ ) . '/formidable-aweber.php';
		parent::__construct();
	}

	public static function load_hooks() {
		add_filter( 'frm_include_addon_page', '__return_true' );
		new FrmAwbrUpdate();
	}
}
