<?php
/*
Plugin Name: Formidable Constant Contact
Description: Add contacts to Constant Contact account when a Formidable form is submitted
Version: 1.07
Plugin URI: https://formidableforms.com/
Author URI: https://formidableforms.com/
Author: Strategy11
Text Domain: formidable-ctct
*/

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Autoloader function for Formidable Constant Contact
 *
 * @param string $class_name
 * @return void
 */
function frm_ctct_forms_autoloader( $class_name ) {
	$path = dirname( __FILE__ );

	// Only load Frm classes here
	if ( ! preg_match( '/^FrmCtct.+$/', $class_name ) ) {
		return;
	}

	if ( preg_match( '/^.+Controller$/', $class_name ) ) {
		$path .= '/controllers';
	} else {
		$path .= '/models';
	}

	$path .= '/' . $class_name . '.php';

	if ( file_exists( $path ) ) {
		include $path;
	}
}

// Load after plugins have loaded.
// If this happens too early, it may lead to Uncaught Error: Class "FrmFormAction" not found fatal errors.
add_action(
	'plugins_loaded',
	function() {
		spl_autoload_register( 'frm_ctct_forms_autoloader' );
		FrmCtctHooksController::load_hooks();
	},
	1
);
