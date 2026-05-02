<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<p>
	<label>
		<?php esc_html_e( 'Button Text', 'formidable-coupons' ); ?>
	</label>
	<input type="text" name="field_options[apply_button_text_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['field_options']['apply_button_text'] ?? '' ); ?>" id="apply_button_text_<?php echo esc_attr( $field['id'] ); ?>" />
</p>
