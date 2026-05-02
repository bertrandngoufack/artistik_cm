<?php
/**
 * Plugin Name: Formidable GetResponse
 * Description: Add users to GetResponse campaigns from your Formidable forms
 * Version: 1.07
 * Plugin URI: https://formidableforms.com/
 * Author URI: https://formidableforms.com/
 * Author: Strategy11
 *
 * @package formidable-getresponse
 */

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

function frm_getresponse_forms_autoloader( $class_name ) {
	$path = dirname( __FILE__ );

	// Only load Frm classes here.
	if ( ! preg_match( '/^FrmGetResponse.+$/', $class_name ) ) {
		return;
	}

	if ( preg_match( '/^.+Controller$/', $class_name ) ) {
		$path .= '/controllers/' . $class_name . '.php';
	} else {
		$path .= '/models/' . $class_name . '.php';
	}

	if ( file_exists( $path ) ) {
		include $path;
	}
}

// Add the autoloader.
spl_autoload_register( 'frm_getresponse_forms_autoloader' );

// Load hooks.
FrmGetResponseHooksController::load_hooks();
