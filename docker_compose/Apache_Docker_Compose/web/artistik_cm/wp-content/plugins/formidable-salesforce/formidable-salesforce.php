<?php
/**
 * Plugin Name: Formidable Salesforce
 * Description: Add leads to your Salesforce account when a Formidable form is submitted
 * Version: 2.05
 * Plugin URI: https://formidableforms.com/
 * Author URI: https://formidableforms.com/
 * Author: Strategy11
 *
 * @package formidable-salesforce
 */

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Autoload plugin classes
 *
 * @param string $class_name
 */
function frm_salesforce_forms_autoloader( $class_name ) {
	$path = dirname( __FILE__ );

	// Only load Frm classes here.
	if ( ! preg_match( '/^FrmSalesforce.+$/', $class_name ) ) {
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

// Add the autoloader.
spl_autoload_register( 'frm_salesforce_forms_autoloader' );

FrmSalesforceHooksController::load_hooks();
