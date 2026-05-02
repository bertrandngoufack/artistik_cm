<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<!-- WooCommerce Settings -->
<h3><?php esc_html_e( 'WooCommerce', 'formidable-woocommerce' ); ?></h3>

<p>
	<label for="frm_wc_option" class="frm_left_label"><?php esc_html_e( 'Account Creation', 'formidable-woocommerce' ); ?>
		<?php self::show_svg_tooltip( __( 'This setting must be enabled in order to show the selected registration form.', 'formidable-woocommerce' ) ); ?>
	</label>

	<input id="frm_wc_option" type="checkbox" disabled <?php echo ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) ? 'checked' : '';?> />
	<?php
	printf(
		'%1$s <a href="%2$s">%3$s</a>',
		esc_html__( 'Allow customers to create an account on the "My account" page', 'formidable-woocommerce' ),
		esc_attr( admin_url( 'admin.php?page=wc-settings&tab=account' ) ),
		esc_html__( '[Edit in WooCommerce]', 'formidable-woocommerce' )
	);
	?>
</p>

<p>
	<label for="frm_registration_form" class="frm_left_label"><?php esc_html_e( 'Select Registration Form', 'formidable-woocommerce' ); ?>
	<?php self::show_svg_tooltip( __( 'Select a registration form to replace the WooCommerce registration form. The login form will also be replaced.', 'formidable-woocommerce' ) ); ?>
	</label>
	<select name="frm_registration_form" id="frm_registration_form" class="frm-pages-dropdown">
		<option value=""><?php esc_html_e( "Don't replace the WooCommerce registration form", 'formidable-woocommerce' ); ?></option>
		<?php foreach ( $forms as $form ) : ?>
			<option <?php selected( $frm_wc_settings['frm_registration_form'], $form->id ); ?> value="<?php echo esc_attr( $form->id ); ?>"><?php echo esc_html( $form->name ); ?></option>
		<?php endforeach; ?>
	</select>
</p>

<p>
	<label for="frm_profile_form" class="frm_left_label"><?php esc_html_e( 'Select Profile Form', 'formidable-woocommerce' ); ?>
	<?php self::show_svg_tooltip( __( 'The selected form will replace the profile form on the WooCoomerce My Account page.', 'formidable-woocommerce' ) ); ?>
	</label>
	<select name="frm_profile_form" id="frm_profile_form" class="frm-pages-dropdown">
		<option value=""><?php esc_html_e( "Don't replace the WooCommerce profile form", 'formidable-woocommerce' ); ?></option>
		<?php foreach ( $forms as $form ) : ?>
			<option <?php selected( $frm_wc_settings['frm_profile_form'], $form->id ); ?> value="<?php echo esc_attr( $form->id ); ?>"><?php echo esc_html( $form->name ); ?></option>
		<?php endforeach; ?>
	</select>
</p>
