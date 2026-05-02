<?php
/*
Plugin Name: Formidable HubSpot
Description: Add contacts to HubSpot from Formidable Forms
Version: 2.0.2
Plugin URI: https://formidableforms.com/
Author URI: https://formidableforms.com/
Author: Strategy11
Text Domain: formidable-hubspot
*/
// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

function frm_hubspot_forms_autoloader( $class_name ) {
	$path = dirname( __FILE__ );

	// Only load Frm classes here
	if ( ! preg_match( '/^FrmHubSpot.+$/', $class_name ) ) {
		return;
	}

	if ( preg_match( '/^.+Controller$/', $class_name ) ) {
		$path .= '/controllers/' . $class_name . '.php';
	} elseif ( preg_match( '/^.+Helper$/', $class_name ) ) {
		$path .= '/helpers/' . $class_name . '.php';
	} else {
		$path .= '/models/' . $class_name . '.php';
	}

	if ( file_exists( $path ) ) {
		include $path;
	}
}

/**
 * Load plugin whether it's safe to run.
 *
 * @since 2.0
 *
 * @return void
 */
function load_formidable_hubspot() {
	$is_free_installed = function_exists( 'load_formidable_forms' );

	if ( ! $is_free_installed ) {
		add_action( 'admin_notices', 'frm_hubspot_free_not_installed_notice' );
	} else {
		// Add the autoloader
		spl_autoload_register( 'frm_hubspot_forms_autoloader' );
		// Load hooks
		add_action( 'frm_load_controllers', array( 'FrmHubSpotHooksController', 'add_hooks_controller' ) );
	}
}

/**
 * Notice for having an active version of lite.
 *
 * @since 2.0
 *
 * @return void
 */
function frm_hubspot_free_not_installed_notice() {
	?>
	<div class="error">
		<p>
			<?php esc_html_e( 'Formidable HubSpot requires Formidable Forms to be installed.', 'formidable-hubspot' ); ?>
			<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=formidable+forms&tab=search&type=term' ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Install Formidable Forms', 'formidable-hubspot' ); ?>
			</a>
		</p>
	</div>
	<?php
}

add_action( 'plugins_loaded', 'load_formidable_hubspot', 1 );
