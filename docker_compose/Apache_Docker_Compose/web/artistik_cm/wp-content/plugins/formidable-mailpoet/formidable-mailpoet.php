<?php
/*
Plugin Name: Formidable MailPoet Newsletters
Description: Add users to your MailPoet newsletter lists from Formidable forms
Version: 1.04
Plugin URI: https://formidableforms.com/
Author URI: https://formidableforms.com/
Author: Strategy11
Text Domain: frmmailpoet
*/

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

function frm_mailpoet_forms_autoloader( $class_name ) {
	$path = dirname( __FILE__ );

	// Only load Frm classes here
	if ( ! preg_match( '/^FrmMailPoet.+$/', $class_name ) ) {
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

// Add the autoloader
spl_autoload_register( 'frm_mailpoet_forms_autoloader' );

// Load hooks
FrmMailPoetHooksController::load_hooks();
