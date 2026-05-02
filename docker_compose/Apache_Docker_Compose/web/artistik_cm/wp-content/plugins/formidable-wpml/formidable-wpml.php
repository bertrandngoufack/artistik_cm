<?php
/*
Plugin Name: Formidable Multilingual
Description: Add multilingual support for Formidable
Plugin URI: https://formidableforms.com/
Author: Strategy11
Author URI: https://formidableforms.com/
Version: 1.13
Test Domain: formidable-wpml
*/

/**
 * @param string $class_name
 * @return void
 */
function frm_wpml_autoloader( $class_name ) {
	$path = dirname( __FILE__ );

	// Only load Frm classes here
	if ( $class_name !== 'FormidableWPML' && ! preg_match( '/^FrmWpml.+$/', $class_name ) ) {
		return;
	}

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
spl_autoload_register( 'frm_wpml_autoloader' );

// Load hooks
add_action( 'plugins_loaded', 'FrmWpmlHooksController::load_hooks', 10 );
add_filter( 'frmreg_global_messages', 'FrmWpmlAppController::translate_reg_settings', 10, 2 );
