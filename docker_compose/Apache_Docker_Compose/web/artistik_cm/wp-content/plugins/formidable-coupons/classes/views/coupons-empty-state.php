<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$image_folder_url = FrmCouponsAppHelper::plugin_url() . '/images/';
?>

<div class="frm-coupons-empty-state">
	<div class="frm-coupons-empty-state-top-image-wrapper">
		<img src="<?php echo esc_url( $image_folder_url . 'empty-state-top.svg' ); ?>" alt="<?php esc_attr_e( 'Coupon field layout.', 'formidable-coupons' ); ?>" />
	</div>

	<h2><?php esc_html_e( 'You have not created any coupons yet', 'formidable-coupons' ); ?></h2>

	<p>
		<?php esc_html_e( 'It looks like you haven\'t created any coupons yet. Allow your customers to enter a custom coupon code and receive discounts on payments forms.', 'formidable-coupons' ); ?>
	</p>

	<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-payments&action=new-coupon' ) ); ?>" class="button button-primary frm-button-primary">
		<?php esc_html_e( 'Create a Coupon', 'formidable-coupons' ); ?>
	</a>

	<div class="frm_grid_container frm-secondary-empty-state-images">
		<div class="frm4">
			<div class="frm-coupons-empty-state-card">
				<img src="<?php echo esc_url( $image_folder_url . 'empty-state-left.png' ); ?>" alt="<?php esc_attr_e( 'Coupon code being entered', 'formidable-coupons' ); ?>" />
			</div>
			<h3><?php esc_html_e( 'Create', 'formidable-coupons' ); ?></h3>
			<p><?php esc_html_e( 'Set up a custom coupon code or let the system generate one for you. It\'s quick and flexible.', 'formidable-coupons' ); ?></p>
		</div>
		<div class="frm4">
			<div class="frm-coupons-empty-state-card">
				<img src="<?php echo esc_url( $image_folder_url . 'empty-state-middle.png' ); ?>" alt="<?php esc_attr_e( 'Copy to clipboard example', 'formidable-coupons' ); ?>" />
			</div>
			<h3><?php esc_html_e( 'Share', 'formidable-coupons' ); ?></h3>
			<p><?php esc_html_e( 'Grab your coupon code and share it anywhere emails, social posts, or directly on your site.', 'formidable-coupons' ); ?></p>
		</div>
		<div class="frm4">
			<div class="frm-coupons-empty-state-card">
				<img src="<?php echo esc_url( $image_folder_url . 'empty-state-right.svg' ); ?>" alt="<?php esc_attr_e( 'Coupon uses count', 'formidable-coupons' ); ?>" />
			</div>
			<h3><?php esc_html_e( 'Track', 'formidable-coupons' ); ?></h3>
			<p><?php esc_html_e( 'See how many times your coupon\'s been used and keep an eye on your campaign\'s performance.', 'formidable-coupons' ); ?></p>
		</div>
	</div>
</div>
