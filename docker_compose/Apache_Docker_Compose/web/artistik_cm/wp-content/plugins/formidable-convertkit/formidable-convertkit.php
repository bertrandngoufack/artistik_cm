<?php
/**
 * Plugin Name: Formidable ConvertKit
 * Description: Add new ConvertKit subscribers from your Formidable forms
 * Version: 1.0
 * Plugin URI: https://formidableforms.com/
 * Author URI: https://formidableforms.com/
 * Author: Strategy11
 * Text Domain: frm-convertkit
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package FrmConvertKit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loads all the classes for this plugin.
 *
 * @param string $class_name The name of the class to load.
 * @return void
 */
function frm_convertkit_autoloader( $class_name ) {
	// Only load Frm classes here.
	if ( ! preg_match( '/^FrmConvertKit.+$/', $class_name ) ) {
		return;
	}

	if ( is_callable( 'frm_class_autoloader' ) ) {
		frm_class_autoloader( $class_name, __DIR__ );
	}
}
spl_autoload_register( 'frm_convertkit_autoloader' );

add_filter( 'frm_load_controllers', array( 'FrmConvertKitHooksController', 'add_hooks_controller' ) );
