<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// The icon file is required for the back icon.
FrmAppHelper::include_svg();
?>
<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-payments&action=coupons' ) ); ?>" class="frm-coupons-back-link">
	<span>
		<?php FrmAppHelper::icon_by_class( 'frmfont frm_arrow_left_icon' ); ?>
	</span>
	<?php esc_html_e( 'Back to all Coupons', 'formidable-coupons' ); ?>
</a>
