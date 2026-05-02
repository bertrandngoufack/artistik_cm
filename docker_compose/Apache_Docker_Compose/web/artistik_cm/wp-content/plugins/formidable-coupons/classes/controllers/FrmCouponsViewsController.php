<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmCouponsViewsController {

	/**
	 * @param string $html
	 * @param int    $form_id
	 *
	 * @return string
	 */
	public static function add_coupon_code_to_field_select_options( $html, $form_id ) {
		$coupon_field = FrmProFormsHelper::has_field( 'coupon', $form_id );
		if ( ! $coupon_field ) {
			return $html;
		}

		ob_start();
		$subfields = array(
			'code' => __( 'Code', 'formidable-coupons' ),
		);
		FrmViewsEditorController::render_subfield_sort_options( (int) $coupon_field->id, $coupon_field->name, $subfields, false );
		$additional_coupon_options_html = ob_get_clean();
		$coupon_option_html             = '<option value="' . esc_attr( $coupon_field->id ) . '" >' . esc_html( FrmAppHelper::truncate( $coupon_field->name, 50 ) ) . '</option>';
		$html                           = str_replace(
			$coupon_option_html,
			$coupon_option_html . $additional_coupon_options_html,
			$html
		);

		return $html;
	}

	/**
	 * @param bool   $is_field_sort_option
	 * @param string $option
	 *
	 * @return bool
	 */
	public static function is_field_sort_option( $is_field_sort_option, $option ) {
		if ( self::is_coupon_code_sort_option( $option ) ) {
			$is_field_sort_option = true;
		}

		return $is_field_sort_option;
	}

	/**
	 * When querying for coupon code we need to check field ID 0 instead of the coupon field ID.
	 *
	 * @param array $where
	 * @param array $args
	 *
	 * @return array
	 */
	public static function modify_where_filter( $where, $args ) {
		if ( ! self::should_modify_where_filter( $args['where_opt'], $args['where_val'] ) ) {
			return $where;
		}

		unset( $where['fi.id'] );
		$where['it.field_id'] = 0;

		return $where;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $where_opt
	 * @param string $where_val
	 *
	 * @return bool
	 */
	private static function should_modify_where_filter( $where_opt, $where_val ) {
		if ( is_numeric( $where_opt ) && 'coupon' === FrmField::get_type( $where_opt ) ) {
			return self::coupon_code_exists( $where_val );
		}

		return self::is_coupon_code_sort_option( $where_opt );
	}

	/**
	 * @since 1.0
	 *
	 * @param string $code
	 *
	 * @return bool
	 */
	private static function coupon_code_exists( $code ) {
		if ( '' === $code ) {
			return false;
		}

		global $wpdb;
		$coupon_match = FrmDb::get_var(
			$wpdb->posts,
			array(
				'post_type'    => 'frm_coupon',
				'post_excerpt' => $code,
			),
			'ID'
		);
		return (bool) $coupon_match;
	}

	/**
	 * @param string $option
	 *
	 * @return bool
	 */
	private static function is_coupon_code_sort_option( $option ) {
		$split = explode( '_', $option );
		return 2 === count( $split ) && is_numeric( $split[0] ) && 'code' === $split[1];
	}

	/**
	 * @since 1.0
	 *
	 * @param bool     $handled
	 * @param array    $query
	 * @param array    $args
	 * @param string   $o_key
	 * @param stdClass $o_field
	 * @param string   $order
	 *
	 * @return array|bool
	 */
	public static function order_by_field( $handled, $query, $args, $o_key, $o_field, $order ) {
		if ( 'coupon' !== $o_field->type ) {
			return $handled;
		}

		$order_by = $args['order_by_array'][ $o_key ];
		if ( ! self::is_coupon_code_sort_option( $order_by ) ) {
			return $handled;
		}

		global $wpdb;
		$query['select']  = str_replace(
			'FROM ' . $wpdb->prefix . 'frm_items it',
			', SUBSTRING_INDEX(
				SUBSTRING_INDEX(
					REPLACE(
						em' . $o_key . '.meta_value,
						SUBSTRING_INDEX( em' . $o_key . '.meta_value, \'"coupon_code";s\', 1 ),
						""
					),
					";",
					2
				),
				":",
				-1
			) as `CouponCode' . $o_key . '`
			FROM ' . $wpdb->prefix . 'frm_items it',
			$query['select']
		);
		$query['select'] .= ' JOIN ' . $wpdb->prefix . 'frm_item_metas em' . $o_key . ' ON em' . $o_key . '.item_id=it.id AND em' . $o_key . '.field_id=0 AND em' . $o_key . '.meta_value LIKE "%coupon_code%"';
		$query['order']  .= '`CouponCode' . $o_key . '` ' . $order . ', ';

		return $query;
	}

	/**
	 * @param bool   $use_where_opt_split_val
	 * @param string $where_opt
	 *
	 * @return bool
	 */
	public static function should_get_field_id_from_where_opt_split_val( $use_where_opt_split_val, $where_opt ) {
		if ( self::is_coupon_code_sort_option( $where_opt ) ) {
			return true;
		}

		return $use_where_opt_split_val;
	}

	/**
	 * @param string   $field_key
	 * @param stdClass $where_field
	 * @param array    $args
	 *
	 * @return string
	 */
	public static function field_key_for_field_query( $field_key, $where_field, $args ) {
		if ( 'coupon' !== $where_field->type || ! self::is_coupon_code_sort_option( $args['where_opt'] ) ) {
			return $field_key;
		}

		return 'TRIM( \'""\' FROM
			CONCAT(
				\'"\',
				SUBSTRING_INDEX(
					SUBSTRING_INDEX(
						REPLACE(
							meta_value' . ',
							SUBSTRING_INDEX( meta_value' . ', \'"coupon_code";s\', 1 ),
							""
						),
						";",
						2
					),
					":",
					-1
				),
				\'"\'
			)
		) ' . FrmDb::append_where_is( $args['temp_where_is'] );
	}

	/**
	 * @param bool   $should_add_and_continue
	 * @param string $where_field
	 *
	 * @return bool
	 */
	public static function should_add_where_to_frm_items_query_and_continue( $should_add_and_continue, $where_field ) {
		if ( self::is_coupon_code_sort_option( $where_field ) ) {
			$should_add_and_continue = false;
		}

		return $should_add_and_continue;
	}
}
