<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<p>
	<label>
		<?php esc_html_e( 'Allowed Coupons', 'formidable-coupons' ); ?>
	</label>
	<select id="allowed_coupons_<?php echo esc_attr( $field['id'] ); ?>" class="frm_multiselect" name="field_options[allowed_coupons_<?php echo esc_attr( $field['id'] ); ?>][]" multiple="multiple">
		<?php foreach ( $coupons as $coupon ) : ?>
			<option value="<?php echo esc_attr( $coupon->ID ); ?>" <?php selected( in_array( $coupon->ID, $selected_coupon_ids, true ) ); ?>>
				<?php echo esc_html( $coupon->post_title ); ?>
			</option>
		<?php endforeach; ?>
	</select>
</p>

<?php if ( ! $selected_coupon_ids ) : ?>
	<div class="frm_warning_style frm-no-selected-coupons-warning">
		<div><?php FrmAppHelper::icon_by_class( 'frmfont frm_alert_icon' ); ?></div>
		<div>
			<?php echo esc_html__( 'You haven\'t selected any coupons that can be used with this form. Please choose at least one coupon.', 'formidable-coupons' ); ?>
		</div>
	</div>
<?php endif; ?>
