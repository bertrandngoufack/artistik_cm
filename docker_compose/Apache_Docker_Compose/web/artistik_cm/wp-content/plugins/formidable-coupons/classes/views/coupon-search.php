<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<form method="get">
	<p class="frm-search">
		<label class="screen-reader-text" for="coupon-search-input">
			<?php esc_html_e( 'Search:', 'formidable-coupons' ); ?>
		</label>
		<?php FrmAppHelper::icon_by_class( 'frmfont frm_search_icon frm_svg20' ); ?>
		<input type="hidden" name="page" value="formidable-payments" />
		<input type="hidden" name="action" value="coupons" />
		<input type="search" id="coupon-search-input" name="s" placeholder="" class="frm-search-input" value="<?php echo esc_attr( $search_term ); ?>" />
		<input type="submit" id="search-submit" class="button" value="Search" />
	</p>
</form>
