<?php
/*
Plugin Name: Formidable AWeber
Description: Send Posted results to AWeber
Version: 2.05
Plugin URI: http://formidablepro.com/
Author URI: http://strategy11.com
Author: Strategy11
Text Domain: formidable-aweber
*/

function frm_awbr_forms_autoloader( $class_name ) {
	// Only load Frm classes here
	if ( ! preg_match( '/^FrmAwbr.+$/', $class_name ) ) {
		return;
	}

	$path = __DIR__;
	if ( preg_match( '/^.+Helper$/', $class_name ) ) {
		$path .= '/helpers/' . $class_name . '.php';
	} elseif ( preg_match( '/^.+Controller$/', $class_name ) ) {
		$path .= '/controllers/' . $class_name . '.php';
	} else {
		$path .= '/models/' . $class_name . '.php';
	}

	if ( file_exists( $path ) ) {
		include $path;
	}
}

// Add the autoloader
spl_autoload_register( 'frm_awbr_forms_autoloader' );

FrmAwbrAppController::load_hooks();
FrmAwbrSettingsController::load_hooks();
