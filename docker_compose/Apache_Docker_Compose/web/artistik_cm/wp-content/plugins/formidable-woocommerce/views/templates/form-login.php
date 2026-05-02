<?php
/**
 * @version 7.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

do_action( 'woocommerce_before_customer_login_form' ); ?>

<div class="u-columns col2-set" id="customer_login">
	<div class="u-column1 col-1">
		<h2><?php esc_html_e( 'Login', 'formidable-woocommerce' ); ?></h2>

		<?php
			$shortcode_atts = array(
				'label_username'     => __( 'Username or email address', 'formidable-woocommerce' ),
				'show_lost_password' => true,
			);

			echo FrmRegShortcodesController::do_login_form_shortcode( $shortcode_atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
	</div>

	<div class="u-column2 col-2">
		<h2><?php esc_html_e( 'Register', 'formidable-woocommerce' ); ?></h2>

		<?php echo FrmFormsController::show_form( WC_Formidable_Settings::get_setting( 'frm_registration_form' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
