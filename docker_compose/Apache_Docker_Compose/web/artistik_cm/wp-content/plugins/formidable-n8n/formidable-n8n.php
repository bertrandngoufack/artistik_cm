<?php
/**
 * Plugin Name: Formidable n8n integration
 * Description: Integrates your forms with n8n by sending submissions to any webhook.
 * Version: 0.1.0
 * Plugin URI: https://formidableforms.com/
 * Author URI: https://formidableforms.com/
 * Author: Strategy11
 * Text Domain: formidable-n8n
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package FrmN8N
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loads all the classes for this plugin.
 *
 * @param string $class_name The name of the class to load.
 *
 * @return void
 */
function frm_n8n_autoloader( $class_name ) {
	$path = __DIR__;

	// Only load Frm classes here.
	if ( ! preg_match( '/^FrmN8N.+$/', $class_name ) ) {
		return;
	}

	if ( is_callable( 'frm_class_autoloader' ) ) {
		frm_class_autoloader( $class_name, $path );
	}
}
spl_autoload_register( 'frm_n8n_autoloader' );

add_filter( 'frm_load_controllers', array( 'FrmN8NHooksController', 'add_hooks_controller' ) );
