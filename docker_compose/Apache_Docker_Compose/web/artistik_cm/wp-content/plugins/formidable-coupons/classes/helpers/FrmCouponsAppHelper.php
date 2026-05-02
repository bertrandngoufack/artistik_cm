<?php

class FrmCouponsAppHelper {

	/**
	 * Store the raw coupon amount value when get_discount_for_coupon is called.
	 * In some cases, the coupon discount can be 0.00 for a valid coupon.
	 * This check allows us to determine if the coupon is valid or not.
	 *
	 * @var string|null
	 */
	private static $last_coupon_raw_amount;

	/**
	 * Store the total value when get_discount_for_coupon is called.
	 *
	 * @var string|null
	 */
	private static $last_calc_total_value;

	/**
	 * Store the coupon match when get_discount_for_coupon is called.
	 *
	 * @var WP_Post|null
	 */
	private static $last_coupon_match;

	/**
	 * @var string
	 */
	public static $plug_version = '1.0';

	/**
	 * @return string
	 */
	public static function plugin_version() {
		return self::$plug_version;
	}

	/**
	 * @return string
	 */
	public static function path() {
		return dirname( __DIR__, 2 );
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	public static function plugin_url( $path = '' ) {
		return plugins_url( $path, self::path() . '/formidable-coupons.php' );
	}

	/**
	 * Get a coupon by ID, and only if it is a coupon (and not a post of some other type).
	 *
	 * @since 1.0
	 *
	 * @param int|string $id
	 *
	 * @return false|WP_Post
	 */
	public static function get_coupon( $id ) {
		$coupon = get_post( $id );
		if ( ! $coupon || 'frm_coupon' !== $coupon->post_type ) {
			return false;
		}

		return $coupon;
	}

	/**
	 * Get a coupon by code.
	 *
	 * @since 1.0
	 *
	 * @param string $code
	 *
	 * @return int
	 */
	public static function get_coupon_id_by_code( $code ) {
		global $wpdb;
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_excerpt = %s AND post_type = "frm_coupon"',
				$code
			)
		);
	}

	/**
	 * @since 1.0
	 *
	 * @param int|string $coupon_id
	 *
	 * @return int
	 */
	public static function get_coupon_uses_by_id( $coupon_id ) {
		$coupon = self::get_coupon( $coupon_id );
		return $coupon ? self::get_coupon_uses_by_code( $coupon->post_excerpt ) : 0;
	}

	/**
	 * @since 1.0
	 *
	 * @param string     $coupon_code
	 * @param int|string $form_id
	 *
	 * @return int
	 */
	public static function get_coupon_uses_by_code( $coupon_code, $form_id = 0 ) {
		global $wpdb;

		$expected_meta_value = serialize( compact( 'coupon_code' ) );

		if ( $form_id ) {
			$table = $wpdb->prefix . 'frm_item_metas m JOIN ' . $wpdb->prefix . 'frm_items i ON m.item_id = i.id';
			$where = array(
				'i.form_id'    => $form_id,
				'm.field_id'   => 0,
				'm.meta_value' => $expected_meta_value,
			);
		} else {
			$table = 'frm_item_metas';
			$where = array(
				'field_id'   => 0,
				'meta_value' => $expected_meta_value,
			);
		}

		return FrmDb::get_count( $table, $where );
	}

	/**
	 * @since 1.0
	 *
	 * @param int $coupon_id
	 *
	 * @return string
	 */
	public static function get_coupon_status_by_id( $coupon_id ) {
		if ( ! $coupon_id ) {
			return 'draft';
		}

		$coupon = self::get_coupon( $coupon_id );
		if ( ! $coupon ) {
			return 'does_not_exist';
		}

		return self::check_coupon_object_for_status( $coupon );
	}

	/**
	 * @param object $coupon
	 * @param int    $entry_id
	 *
	 * @return string
	 */
	public static function check_coupon_object_for_status( $coupon, $entry_id = 0 ) {
		if ( '' === $coupon->post_title || '' === $coupon->post_excerpt ) {
			return 'draft';
		}

		$coupon_data = json_decode( $coupon->post_content );
		if ( ! is_object( $coupon_data ) ) {
			return 'invalid';
		}

		if ( empty( $coupon_data->amount ) || empty( $coupon_data->start ) ) {
			return 'draft';
		}

		$date = new DateTime( 'now', wp_timezone() );
		$now  = $date->format( 'Y-m-d H:i:s' );

		if ( $coupon_data->start > $now ) {
			return 'scheduled';
		}

		if ( ! empty( $coupon_data->end ) && $coupon_data->end < $now ) {
			return 'expired';
		}

		$coupons = FrmCouponsFilterHelper::check_for_coupon_limits( array( $coupon ), $entry_id );
		return $coupons ? 'active' : 'limit_reached';
	}

	/**
	 * @since 1.0
	 *
	 * @param string $status
	 *
	 * @return string
	 */
	public static function get_readable_coupon_status( $status ) {
		switch ( $status ) {
			case 'does_not_exist':
				return __( 'Does Not Exist', 'formidable-coupons' );
			case 'invalid':
				return __( 'Invalid', 'formidable-coupons' );
			case 'draft':
				return __( 'Draft', 'formidable-coupons' );
			case 'scheduled':
				return __( 'Scheduled', 'formidable-coupons' );
			case 'expired':
				return __( 'Expired', 'formidable-coupons' );
			case 'limit_reached':
				return __( 'Limit Reached', 'formidable-coupons' );
			case 'active':
				return __( 'Active', 'formidable-coupons' );
			default:
				return $status;
		}
	}

	/**
	 * Get discount for coupon.
	 *
	 * @since 1.0
	 *
	 * @param string $code
	 * @param int    $form_id
	 * @param string $total_value
	 * @param int    $entry_id
	 *
	 * @return string '0.00' if there is no valid match.
	 */
	public static function get_discount_for_coupon( $code, $form_id, $total_value = '0.00', $entry_id = 0 ) {
		global $wpdb;

		self::$last_coupon_raw_amount = '0.00';
		self::$last_calc_total_value  = $total_value;
		self::$last_coupon_match      = null;

		if ( '' === $code ) {
			return '0.00';
		}

		$coupons = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT ID, post_title, post_content, post_excerpt FROM ' . $wpdb->posts . ' WHERE post_excerpt = %s AND post_type = "frm_coupon"',
				$code
			)
		);
		if ( ! $coupons ) {
			return '0.00';
		}

		$coupons = array_filter(
			$coupons,
			function ( $coupon ) use ( $entry_id ) {
				return 'active' === self::check_coupon_object_for_status( $coupon, $entry_id );
			}
		);

		$coupons = FrmCouponsFilterHelper::filter_coupons_for_form( $coupons, $form_id );
		if ( ! $coupons ) {
			return '0.00';
		}

		$coupon = reset( $coupons );
		$data   = json_decode( $coupon->post_content );
		if ( ! is_object( $data ) ) {
			return '0.00';
		}

		$discount = $data->amount;

		self::$last_coupon_raw_amount = $data->amount;
		self::$last_coupon_match      = $coupon;

		if ( '0.00' === $total_value ) {
			return '0.00';
		}

		$minimum_order_value = $data->minimum_order_value;
		if ( $minimum_order_value && $minimum_order_value > $total_value ) {
			return '0.00';
		}

		$is_percent_discount = '%' === substr( $discount, -1 );
		if ( $is_percent_discount ) {
			$percent_discount = substr( $discount, 0, -1 );
			$discount         = self::do_percent_calculation( $total_value, $percent_discount );
		} elseif ( $discount > $total_value ) {
			$discount = $total_value;
		}

		return $discount;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $total_value
	 * @param string $percent_discount
	 *
	 * @return string
	 */
	public static function do_percent_calculation( $total_value, $percent_discount ) {
		$discount = floatval( $total_value ) * ( floatval( $percent_discount ) / 100 );

		if ( ! $discount ) {
			return '0.00';
		}

		$discount = round( $discount, 2 );
		return (string) $discount;
	}

	/**
	 * @since 1.0
	 *
	 * @return string|null
	 */
	public static function get_last_coupon_raw_amount() {
		return self::$last_coupon_raw_amount;
	}

	/**
	 * @since 1.0
	 *
	 * @return string|null
	 */
	public static function get_last_calc_total_value() {
		return self::$last_calc_total_value;
	}

	/**
	 * @since 1.0
	 *
	 * @return WP_Post|null
	 */
	public static function get_last_coupon_match() {
		return self::$last_coupon_match;
	}

	/**
	 * @param int|string $coupon_id
	 *
	 * @return array<int>
	 */
	public static function get_allowed_form_ids( $coupon_id ) {
		if ( ! $coupon_id ) {
			return array();
		}

		$coupon_id = intval( $coupon_id );

		$all_coupon_fields = FrmDb::get_results(
			'frm_fields',
			array(
				'type' => 'coupon',
			),
			'id, form_id, field_options'
		);
		$form_ids          = array();
		foreach ( $all_coupon_fields as $coupon_field ) {
			FrmAppHelper::unserialize_or_decode( $coupon_field->field_options );

			if ( empty( $coupon_field->field_options['allowed_coupons'] ) ) {
				continue;
			}

			$allowed_coupon_ids = array_map( 'intval', $coupon_field->field_options['allowed_coupons'] );
			if ( in_array( $coupon_id, $allowed_coupon_ids, true ) ) {
				$form_ids[] = (int) $coupon_field->form_id;
			}
		}

		return $form_ids;
	}

	/**
	 * @param int|string $coupon_id
	 * @param bool       $echo
	 *
	 * @return string|null
	 */
	public static function render_coupon_status( $coupon_id, $echo = true ) {
		$status          = self::get_coupon_status_by_id( $coupon_id );
		$readable_status = self::get_readable_coupon_status( $status );

		return FrmAppHelper::clip(
			function () use ( $status, $readable_status ) {
				$meta_tag_input_params = array(
					'class' => 'frm-meta-tag frm-coupon-status frm-coupon-status-' . esc_attr( str_replace( '_', '-', $status ) ),
				);
				?>
					<span <?php FrmAppHelper::array_to_html_params( $meta_tag_input_params, true ); ?>>
						<?php echo esc_html( $readable_status ); ?>
					</span>
				<?php
			},
			$echo
		);
	}

	/**
	 * @since 1.0
	 *
	 * @return array
	 */
	public static function get_allowed_forms_form_options() {
		$form_ids = FrmDb::get_col(
			'frm_fields',
			array(
				'type' => 'coupon',
			),
			'form_id'
		);
		if ( ! $form_ids ) {
			return array();
		}

		$forms = FrmDb::get_results(
			'frm_forms',
			array(
				'id' => $form_ids,
			),
			'id, name',
			array(
				'order_by' => 'name',
			)
		);
		return $forms;
	}

	/**
	 * @since 1.0
	 *
	 * @param array|stdClass $field
	 *
	 * @return string
	 */
	public static function get_apply_button_text( $field ) {
		$apply_button_text = FrmField::get_option( $field, 'apply_button_text' );
		if ( '' === $apply_button_text || ! is_string( $apply_button_text ) ) {
			$apply_button_text = __( 'Apply', 'formidable-coupons' );
		}

		return $apply_button_text;
	}

	/**
	 * @param string       $amount
	 * @param array|object $field
	 */
	public static function format_amount_as_currency_for_coupon_field( $amount, $field ) {
		if ( is_object( $field ) ) {
			$field->field_options['is_currency'] = true;
		}

		return FrmProCurrencyHelper::maybe_format_currency( $amount, $field, array() );
	}

	/**
	 * @return string
	 */
	public static function js_suffix() {
		return self::use_minified_js_file() ? '.min' : '';
	}

	/**
	 * @return bool
	 */
	private static function use_minified_js_file() {
		if ( self::debug_scripts_are_on() && self::has_unminified_js_file() ) {
			return false;
		}
		return self::has_minified_js_file();
	}

	/**
	 * @return bool
	 */
	private static function debug_scripts_are_on() {
		return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
	}

	/**
	 * @return bool
	 */
	private static function has_unminified_js_file() {
		return is_readable( self::path() . '/js/admin.js' ) && is_readable( self::path() . '/js/frontend.js' );
	}

	/**
	 * @return bool
	 */
	private static function has_minified_js_file() {
		return is_readable( self::path() . '/js/admin.min.js' ) && is_readable( self::path() . '/js/frontend.min.js' );
	}

	/**
	 * @since 1.0
	 *
	 * @param int $entry_id
	 *
	 * @return string
	 */
	public static function get_coupon_code_for_entry( $entry_id ) {
		$meta_value = FrmDb::get_var(
			'frm_item_metas',
			array(
				'item_id'          => $entry_id,
				'field_id'         => 0,
				'meta_value LIKE%' => 'a:1:{s:11:"coupon_code";',
			),
			'meta_value'
		);
		if ( ! $meta_value ) {
			return '';
		}

		FrmAppHelper::unserialize_or_decode( $meta_value );
		return is_array( $meta_value ) ? $meta_value['coupon_code'] : '';
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	public static function get_coupon_permission_required() {
		return 'frm_change_settings';
	}
}
