<?php
/**
 * @version 8.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

do_action( 'woocommerce_before_edit_account_form' ); ?>

<?php echo FrmFormsController::show_form( WC_Formidable_Settings::get_setting( 'frm_profile_form' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

<?php do_action( 'woocommerce_after_edit_account_form' );
