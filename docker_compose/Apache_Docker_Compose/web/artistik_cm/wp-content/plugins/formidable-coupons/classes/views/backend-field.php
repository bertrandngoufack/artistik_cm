<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<input id="field_<?php echo esc_attr( $field['field_key'] ); ?>" type="text" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" name="item_meta[<?php echo esc_attr( $field['id'] ); ?>]" />

<a href="#" class="frm-apply-coupon"><?php echo esc_html( FrmCouponsAppHelper::get_apply_button_text( $field ) ); ?></a>

<?php if ( empty( $field['allowed_coupons'] ) ) : ?>
	<span class="frm-coupon-warning-icon-wrapper"><?php FrmAppHelper::icon_by_class( 'frmfont frm_alert_icon' ); ?></span>
<?php endif; ?>
