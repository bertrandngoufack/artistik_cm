<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! empty( $coupon_id ) && '' !== ( $coupon->post_excerpt ?? '' ) ) {
	$locked = FrmCouponsAppHelper::get_coupon_uses_by_code( $coupon->post_excerpt ) > 0;
} else {
	$locked = false;
}

$minimum_order_value = $coupon_data->minimum_order_value ?? '';
?>

<?php // Load the front end CSS so the flatpickr looks like the rest of the site. ?>
<link href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=frmpro_css' ) ); ?>" type="text/css" rel="Stylesheet" class="frm-custom-theme" />

<div class="frm_wrap">
	<?php
	FrmAppHelper::get_admin_header(
		array(
			'label'   => __( 'Payments', 'formidable' ),
			'form'    => FrmAppHelper::simple_get( 'form', 'absint', 0 ),
			'publish' => array( 'FrmCouponsAppController::save_button', array() ),
		)
	);
	?>

	<div class="frm-coupon-settings frm-fields">
		<div class="frm-coupon-settings-card">
			<?php include FrmCouponsAppHelper::path() . '/classes/views/back-link.php'; ?>

			<hr />

			<?php
			if ( ! empty( $coupon_id ) ) {
				?>
				<h2>
					<?php
					echo esc_html( $coupon->post_title );
					FrmCouponsAppHelper::render_coupon_status( $coupon_id );
					?>
				</h2>
				<?php
			} else {
				?>
				<h2 style="margin-bottom: 0">
					<?php
					esc_html_e( 'Add New Coupon', 'formidable-coupons' );
					FrmCouponsAppHelper::render_coupon_status( 0 );
					?>
				</h2>
				<span class="frm-coupon-setting-description">
					<?php esc_html_e( 'Create a coupon that can be used to receive a discount on your payment forms.', 'formidable-coupons' ); ?>
				</span>
				<?php
			}
			?>

			<form id="frm_coupons_form">
				<div class="frm_grid_container">
					<p class="frm3 frm_form_field">
						<label for="frm_coupon_name"><?php esc_html_e( 'Name', 'formidable-coupons' ); ?><span class="frm_required">*</span></label>
					</p>
					<p class="frm9 frm_form_field">
						<input type="text" id="frm_coupon_name" name="name" value="<?php echo esc_attr( $coupon->post_title ?? '' ); ?>" />
						<?php if ( empty( $coupon_id ) ) : ?>
							<span class="frm-coupon-setting-description">
								<?php esc_html_e( 'Give your coupon a name so you can easily identify it. This is not displayed to customers.', 'formidable-coupons' ); ?>
							</span>
						<?php endif; ?>
					</p>

					<p class="frm3 frm_form_field">
						<label for="frm_coupon_code"><?php esc_html_e( 'Code', 'formidable-coupons' ); ?><span class="frm_required">*</span></label>
					</p>
					<p class="frm9 frm_form_field">
						<span class="frm-coupon-code-field-wrapper">
							<span>
								<?php
								$code_input_params = array(
									'id'    => 'frm_coupon_code',
									'type'  => 'text',
									'name'  => 'code',
									'value' => $coupon->post_excerpt ?? '',
								);
								if ( $locked ) {
									$code_input_params['disabled'] = 'disabled';
								}
								?>
								<input <?php FrmAppHelper::array_to_html_params( $code_input_params, true ); ?> />
							</span>
							<span>
								<?php
								$generate_code_button_classes = array(
									'button',
									'button-secondary',
									'frm-button-secondary',
									'frm-button-small',
									'frm-generate-coupon-code-button',
								);
								if ( $locked ) {
									$generate_code_button_classes[] = 'frm-disabled';
								}
								$generate_code_button_params = array(
									'class' => implode( ' ', $generate_code_button_classes ),
									'href'  => '#',
								);
								?>
								<a <?php FrmAppHelper::array_to_html_params( $generate_code_button_params, true ); ?>>
									<?php esc_html_e( 'Generate Code', 'formidable-coupons' ); ?>
								</a>
								<?php if ( $locked ) : ?>
									<span class="frm-coupon-lock-icon-wrapper"><?php FrmAppHelper::icon_by_class( 'frmfont frm_lock_icon' ); ?></span>
								<?php endif; ?>
							</span>
						</span>
						<?php if ( empty( $coupon_id ) ) : ?>
							<span class="frm-coupon-setting-description">
								<?php
								printf(
									// translators: %1$s and %2$s are HTML tags for bold text.
									esc_html__( 'The code customers will enter to receive a discount. %1$sCannot be changed once used.%2$s', 'formidable-coupons' ),
									'<strong>',
									'</strong>'
								);
								?>
							</span>
						<?php endif; ?>
					</p>

					<p class="frm3 frm_form_field">
						<label for="frm_coupon_amount"><?php esc_html_e( 'Amount', 'formidable-coupons' ); ?><span class="frm_required">*</span></label>
					</p>
					<p class="frm9 frm_form_field">
						<?php
						$input_number_attrs = array();
						if ( $locked ) {
							$input_number_attrs['disabled'] = 'disabled';
						}
						FrmHtmlHelper::echo_unit_input(
							array(
								'value'              => $coupon_data->amount ?? '',
								'field_attrs'        => array(
									'id'   => 'frm_coupon_amount',
									'name' => 'amount',
								),
								'units'              => array( '$', '%' ),
								'default_unit'       => '$',
								'input_number_attrs' => $input_number_attrs,
							)
						);
						if ( $locked ) {
							?>
							<span class="frm-coupon-lock-icon-wrapper"><?php FrmAppHelper::icon_by_class( 'frmfont frm_lock_icon' ); ?></span>
							<?php
						}
						?>
						<?php if ( empty( $coupon_id ) ) : ?>
							<span class="frm-coupon-setting-description">
								<?php
								printf(
									// translators: %1$s and %2$s are HTML tags for bold text.
									esc_html__( 'The amount of the discount as a percentage (%%) or fixed amount ($). %1$sCannot be changed once used.%2$s', 'formidable-coupons' ),
									'<strong>',
									'</strong>'
								);
								?>
							</span>
						<?php endif; ?>
					</p>

					<p class="frm3 frm_form_field">
						<label for="frm_coupon_limit"><?php esc_html_e( 'Max Uses', 'formidable-coupons' ); ?></label>
					</p>
					<p class="frm9 frm_form_field">
						<input type="number" id="frm_coupon_limit" name="limit" value="<?php echo esc_attr( $coupon_data->limit ?? '' ); ?>" step="1" min="0" />
						<span class="frm-coupon-setting-description">
							<?php
							if ( ! empty( $coupon_id ) ) {
								$uses  = FrmCouponsAppHelper::get_coupon_uses_by_code( $coupon->post_excerpt );
								$limit = '' === $coupon_data->limit ? esc_html__( 'Unlimited', 'formidable-coupons' ) : $coupon_data->limit;

								// translators: %1$s and %2$s are HTML tags for bold text.
								printf( esc_html__( '%1$s/%2$s coupon uses', 'formidable-coupons' ), absint( $uses ), '<span class="frm-coupon-limit">' . esc_html( $limit ) . '</span>' );
							} else {
								echo esc_html__( 'The total number of times this coupon can be used.', 'formidable-coupons' );
							}
							?>
						</span>
					</p>

					<p class="frm3 frm_form_field">
						<label for="frm_coupon_start_date"><?php esc_html_e( 'Start Date / Time', 'formidable-coupons' ); ?></label>
					</p>
					<p class="frm9 frm_form_field">
						<?php
						$start_date_input_params = array(
							'id'    => 'frm_coupon_start_date',
							'type'  => 'text',
							'class' => 'frm_date',
							'name'  => 'start_date',
							'value' => $start,
						);
						if ( '' === $start ) {
							$start_date_input_params['class'] .= ' frm-no-start-date';
						}
						?>
						<input <?php FrmAppHelper::array_to_html_params( $start_date_input_params, true ); ?> />
						<span class="frm-coupon-setting-description">
							<?php if ( empty( $coupon_id ) ) : ?>							
								<?php esc_html_e( 'Set the date and time this discount will start on.', 'formidable-coupons' ); ?>
								<br>
							<?php endif; ?>
							<span class="frm-coupon-settings-no-start-date-warning">
								<?php esc_html_e( 'A coupon with no start date will be marked as draft.', 'formidable-coupons' ); ?>
							</span>
						</span>
					</p>

					<p class="frm3 frm_form_field">
						<label for="frm_coupon_end_date"><?php esc_html_e( 'End Date / Time', 'formidable-coupons' ); ?></label>
					</p>
					<p class="frm9 frm_form_field">
						<input type="text" id="frm_coupon_end_date" name="end_date" class="frm_date" value="<?php echo esc_attr( $coupon_data->end ?? '' ); ?>" />
						<?php if ( empty( $coupon_id ) ) : ?>
							<span class="frm-coupon-setting-description">
								<?php esc_html_e( 'Set the date and time this discount will end on. Leave blank for no end date.', 'formidable-coupons' ); ?>
							</span>
						<?php endif; ?>
					</p>

					<p class="frm3 frm_form_field">
						<label for="frm_coupon_minimum_order_value"><?php esc_html_e( 'Minimum order value', 'formidable-coupons' ); ?></label>
					</p>
					<p class="frm9 frm_form_field">
						<label for="frm_coupon_minimum_order_value_enabled">
							<?php
							$minimum_order_checkbox_params = array(
								'id'           => 'frm_coupon_minimum_order_value_enabled',
								'type'         => 'checkbox',
								'name'         => 'minimum_order_value_enabled',
								'value'        => '1',
								'data-frmshow' => '#frm_coupon_minimum_order_value',
							);
							if ( '' !== $minimum_order_value ) {
								$minimum_order_checkbox_params['checked'] = 'checked';
							}
							?>
							<input <?php FrmAppHelper::array_to_html_params( $minimum_order_checkbox_params, true ); ?> />
							<?php esc_html_e( 'Require minimum order value', 'formidable-coupons' ); ?>
							<span>
								<?php FrmAppHelper::tooltip_icon( __( 'The total value must be greater than the minimum order value, or any applied coupons will have no value.', 'formidable-coupons' ), array( 'class' => 'frm-flex' ) ); ?>
							</span>
						</label>

						<?php
						$number_input_params = array(
							'id'    => 'frm_coupon_minimum_order_value',
							'type'  => 'number',
							'name'  => 'minimum_order_value',
							'value' => $minimum_order_value,
							'step'  => '0.01',
							'min'   => 0,
						);
						if ( '' === $minimum_order_value ) {
							$number_input_params['class'] = 'frm_hidden';
						}
						?>
						<input <?php FrmAppHelper::array_to_html_params( $number_input_params, true ); ?> />
					</p>
				</div>
				<hr />
				<?php include FrmCouponsAppHelper::path() . '/classes/views/allowed-forms.php'; ?>
			</form>
		</div>
	</div>
</div>
