<?php
/*
Plugin Name: Formidable Coupons
Description: Add coupons to apply discounts to products and calculations in Formidable Forms.
Version: 1.0
Plugin URI: https://formidableforms.com/
Author URI: https://formidableforms.com/
Author: Strategy11
*/

/**
 * Register autoload for Formidable conversational forms.
 *
 * @param string $class_name
 *
 * @return void
 */
function frm_forms_coupons_autoloader( $class_name ) {
	// Only load Frm classes here
	if ( ! preg_match( '/^FrmCoupons.+$/', $class_name ) ) {
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
function load_formidable_coupons() {
	$is_free_installed = function_exists( 'load_formidable_forms' );
	$is_pro_installed  = function_exists( 'load_formidable_pro' );

	if ( ! $is_free_installed ) {
		add_action( 'admin_notices', 'frm_coupons_free_not_installed_notice' );
	} elseif ( ! $is_pro_installed ) {
		add_action( 'admin_notices', 'frm_coupons_pro_not_installed_notice' );
		$page = FrmAppHelper::get_param( 'page', '', 'get', 'sanitize_text_field' );
		if ( 'formidable' === $page ) {
			add_filter( 'frm_message_list', 'frm_coupons_pro_missing_add_message' );
		}
	} else {
		// Add the autoloader
		spl_autoload_register( 'frm_forms_coupons_autoloader' );

		FrmCouponsAppController::load_hooks();
	}
}

/**
 * @return void
 */
function frm_coupons_free_not_installed_notice() {
	?>
	<div class="error">
		<p>
			<?php esc_html_e( 'Formidable Coupons requires Formidable Forms to be installed.', 'formidable-coupons' ); ?>
			<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=formidable+forms&tab=search&type=term' ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Install Formidable Forms', 'formidable-coupons' ); ?>
			</a>
		</p>
	</div>
	<?php
}

/**
 * @return void
 */
function frm_coupons_pro_not_installed_notice() {
	?>
	<div class="error">
		<p><?php esc_html_e( 'Formidable Coupons requires Formidable Forms Pro to be installed.', 'formidable-coupons' ); ?></p>
	</div>
	<?php
}

/**
 * @param array $messages
 *
 * @return array
 */
function frm_coupons_pro_missing_add_message( $messages ) {
	$messages['coupons_pro_missing'] = 'Formidable Coupons requires Formidable Forms Pro to be installed.';
	return $messages;
}

add_action( 'plugins_loaded', 'load_formidable_coupons', 1 );

/**
 * @return void
 */
function frm_update_stylesheet_on_coupons_activation() {
	if ( ! function_exists( 'load_formidable_forms' ) ) {
		return;
	}

	load_formidable_coupons();

	$frm_style = new FrmStyle();
	$frm_style->update( 'default' );
}

register_activation_hook( __FILE__, 'frm_update_stylesheet_on_coupons_activation' );
