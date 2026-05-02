<?php
/*
Plugin Name: Formidable Test Mode
Description: Enable testing controls for Formidable Forms.
Version: 1.0
Plugin URI: https://formidableforms.com/
Author URI: https://formidableforms.com/
Author: Strategy11
*/

/**
 * Register autoload for Formidable conversational forms.
 *
 * @param string $class_name
 * @return void
 */
function frm_forms_test_mode_autoloader( $class_name ) {
	// Only load Frm classes here
	if ( ! preg_match( '/^FrmTestMode.+$/', $class_name ) ) {
		return;
	}

	$filepath = __DIR__ . '/classes/';
	if ( preg_match( '/^.+Helper$/', $class_name ) ) {
		$filepath .= 'helpers/';
	} elseif ( preg_match( '/^.+Controller$/', $class_name ) ) {
		$filepath .= 'controllers/';
	} else {
		$filepath .= 'models/';
	}

	$filepath .= $class_name . '.php';

	if ( file_exists( $filepath ) ) {
		include $filepath;
	}
}

/**
 * @return void
 */
function load_formidable_test_mode() {
	$is_free_installed = function_exists( 'load_formidable_forms' );
	$is_pro_installed  = function_exists( 'load_formidable_pro' );

	if ( ! $is_free_installed ) {
		add_action( 'admin_notices', 'frm_test_mode_free_not_installed_notice' );
	} elseif ( ! $is_pro_installed ) {
		add_action( 'admin_notices', 'frm_test_mode_pro_not_installed_notice' );
		$page = FrmAppHelper::get_param( 'page', '', 'get', 'sanitize_text_field' );
		if ( 'formidable' === $page ) {
			add_filter( 'frm_message_list', 'frm_test_mode_pro_missing_add_message' );
		}
	} else {
		// Add the autoloader
		spl_autoload_register( 'frm_forms_test_mode_autoloader' );

		FrmTestModeAppController::load_hooks();
	}
}

/**
 * @return void
 */
function frm_test_mode_free_not_installed_notice() {
	?>
	<div class="error">
		<p>
			<?php esc_html_e( 'Formidable Test Mode requires Formidable Forms to be installed.', 'formidable-test-mode' ); ?>
			<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=formidable+forms&tab=search&type=term' ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Install Formidable Forms', 'formidable-test-mode' ); ?>
			</a>
		</p>
	</div>
	<?php
}

/**
 * @return void
 */
function frm_test_mode_pro_not_installed_notice() {
	?>
	<div class="error">
		<p><?php esc_html_e( 'Formidable Test Mode requires Formidable Forms Pro to be installed.', 'formidable-test-mode' ); ?></p>
	</div>
	<?php
}

/**
 * @param array $messages
 * @return array
 */
function frm_test_mode_pro_missing_add_message( $messages ) {
	$messages['test_mode_pro_missing'] = 'Formidable Test Mode requires Formidable Forms Pro to be installed.';
	return $messages;
}

add_action( 'plugins_loaded', 'load_formidable_test_mode', 1 );
