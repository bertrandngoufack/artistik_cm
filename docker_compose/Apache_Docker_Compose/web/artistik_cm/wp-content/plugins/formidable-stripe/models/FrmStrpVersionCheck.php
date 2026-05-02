<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmStrpVersionCheck extends FrmStrpUpdate {

	public function __construct() {
		// the constructor intentionally avoid the FrmStrpUpdate constructor
		// we're really just trying to expose the value of $version
	}

	public static function get_version() {
		$check = new FrmStrpVersionCheck();
		return $check->version;
	}

}
