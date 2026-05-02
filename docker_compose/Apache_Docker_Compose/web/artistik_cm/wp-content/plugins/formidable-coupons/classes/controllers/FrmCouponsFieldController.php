<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmCouponsFieldController {

	/**
	 * @param array $field_types
	 *
	 * @return array
	 */
	public static function remove_coupon_upgrade_icon( $field_types ) {
		if ( ! isset( $field_types['coupon'] ) ) {
			// Lite is not up to date, so Coupon field is not registered.
			return $field_types;
		}

		$field_types['coupon']['icon'] = str_replace( ' frm_show_upgrade', '', $field_types['coupon']['icon'] );
		return $field_types;
	}

	/**
	 * @param string $class
	 * @param string $field_type
	 *
	 * @return string
	 */
	public static function set_coupon_field_class( $class, $field_type ) {
		return 'coupon' === $field_type ? 'FrmCouponsFieldCoupon' : $class;
	}

	/**
	 * Load Coupon Field scripts.
	 *
	 * @since 1.0
	 *
	 * @param array $field The field object.
	 *
	 * @return void
	 */
	public static function load_coupon_field_scripts( $field ) {
		if ( wp_script_is( 'frm-coupon-field', 'enqueued' ) ) {
			return;
		}

		if ( ! self::field_requires_coupon_scripts( $field ) ) {
			return;
		}

		wp_enqueue_script(
			'frm-coupon-field',
			FrmCouponsAppHelper::plugin_url() . '/js/frontend' . FrmCouponsAppHelper::js_suffix() . '.js',
			array(),
			FrmCouponsAppHelper::plugin_version(),
			true
		);

		$coupon_code_error_messages = array(
			'couponCodeRequired' => __( 'Please enter a coupon code', 'formidable-coupons' ),
			'noCalculations'     => __( 'No total fields or calculations were found that match this coupon field', 'formidable-coupons' ),
			'invalidCoupon'      => __( 'This coupon code is invalid', 'formidable-coupons' ),
		);

		/**
		 * Ideally this would be part of the "Validation Messages" field settings.
		 * However this is global right now, and the validation message settings are not very flexible yet.
		 * So for now it's possible to modify the error messages using this filter.
		 *
		 * @since 1.0
		 *
		 * @param array $coupon_code_error_messages
		 */
		$coupon_code_error_messages = (array) apply_filters( 'frm_coupon_field_error_messages', $coupon_code_error_messages );

		wp_localize_script( 'frm-coupon-field', 'frmCouponFieldErrorMessages', $coupon_code_error_messages );
	}

	/**
	 * @since 1.0
	 *
	 * @param array $field
	 *
	 * @return bool
	 */
	private static function field_requires_coupon_scripts( $field ) {
		if ( 'coupon' === $field['type'] ) {
			return true;
		}

		$field_depends_on_coupons = 'total' === $field['type'] || ! empty( $field['calc'] );
		if ( $field_depends_on_coupons && self::form_includes_coupon_field( $field['form_id'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @since 1.0
	 *
	 * @param int $form_id
	 *
	 * @return bool
	 */
	private static function form_includes_coupon_field( $form_id ) {
		return (bool) FrmProFormsHelper::has_field( 'coupon', $form_id );
	}

	/**
	 * @since 1.0
	 *
	 * @param array $field
	 *
	 * @return void
	 */
	public static function add_currency_format_to_coupon_field( $field ) {
		if ( 'coupon' !== $field['type'] ) {
			return;
		}

		$field['format'] = 'currency';
		require FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/currency-format.php';
	}

	/**
	 * @param array $field_options
	 *
	 * @return array
	 */
	public static function default_field_options( $field_options ) {
		$field_options['allowed_coupons']   = '';
		$field_options['apply_button_text'] = '';
		return $field_options;
	}

	/**
	 * @param bool           $should_format_value_as_currency
	 * @param array|stdClass $field
	 * @param array          $atts
	 *
	 * @return bool
	 */
	public static function frm_should_format_value_as_currency_on_display( $should_format_value_as_currency, $field, $atts ) {
		if ( 'coupon' === FrmField::get_field_type( $field ) ) {
			// Currency formatting is handled in the add-on.
			// We don't want the Pro filter to run, as it may modify
			// the format of the coupon code if it includes numbers.
			return false;
		}

		return $should_format_value_as_currency;
	}

	/**
	 * Add a hidden coupon code field when we're posting a hidden coupon discount
	 * between pages.
	 *
	 * @since 1.0
	 *
	 * @param array $field
	 *
	 * @return void
	 */
	public static function maybe_insert_hidden_coupon_code_field( $field ) {
		if ( 'coupon' !== $field['original_type'] ) {
			return;
		}

		$coupon_code = FrmAppHelper::get_post_param( $field['id'] . '_code', '', 'sanitize_text_field' );

		$params      = array(
			'type'  => 'hidden',
			'name'  => $field['id'] . '_code',
			'value' => $coupon_code,
		);

		?>
		<input <?php FrmAppHelper::array_to_html_params( $params, true ); ?> />
		<?php
	}
}
