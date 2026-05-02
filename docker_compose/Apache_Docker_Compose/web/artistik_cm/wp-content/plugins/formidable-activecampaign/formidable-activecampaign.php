<?php
/**
 * Plugin Name: Formidable ActiveCampaign
 * Description: Add users to your ActiveCampaign list from Formidable Forms
 * Version: 1.11
 * Plugin URI: https://formidableforms.com/
 * Author URI: https://formidableforms.com/
 * Author: Strategy11
 * Text Domain: frmactivecampaign
 *
 * @package formidable-activecampaign
 */

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

function frm_activecampaign_forms_autoloader( $class_name ) {
	$path = dirname( __FILE__ );

	// Only load Frm classes here.
	if ( ! preg_match( '/^FrmActiveCampaign.+$/', $class_name ) ) {
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

// Load after plugins have loaded.
// If this happens too early, it may lead to Uncaught Error: Class "FrmFormAction" not found fatal errors.
add_action(
	'plugins_loaded',
	function() {
		// Add the autoloader.
		spl_autoload_register( 'frm_activecampaign_forms_autoloader' );

		// Load hooks.
		FrmActiveCampaignHooksController::load_hooks();
	},
	1
);
