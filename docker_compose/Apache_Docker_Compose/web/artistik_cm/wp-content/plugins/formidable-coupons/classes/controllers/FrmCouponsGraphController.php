<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 1.0
 */
class FrmCouponsGraphController {

	/**
	 * @since 1.0
	 *
	 * @param array $data
	 * @param array $atts
	 *
	 * @return array
	 */
	public static function graph_data( $data, $atts ) {
		if ( ! is_array( $atts['fields'] ) || 1 !== count( $atts['fields'] ) ) {
			return $data;
		}

		$field = reset( $atts['fields'] );
		if ( 'coupon' !== $field->type ) {
			return $data;
		}

		global $wpdb;
		$coupon_code_metas = FrmDb::get_col(
			$wpdb->prefix . 'frm_item_metas m JOIN ' . $wpdb->prefix . 'frm_items i ON m.item_id = i.id',
			array(
				'm.field_id'        => 0,
				'm.meta_value LIKE' => 'coupon_code',
				'i.form_id'         => $field->form_id,
			),
			'm.meta_value'
		);

		$coupon_code_counts = array();
		foreach ( $coupon_code_metas as $coupon_code_meta ) {
			FrmAppHelper::unserialize_or_decode( $coupon_code_meta );

			if ( ! is_array( $coupon_code_meta ) || ! array_key_exists( 'coupon_code', $coupon_code_meta ) ) {
				continue;
			}

			$code = $coupon_code_meta['coupon_code'];
			if ( ! isset( $coupon_code_counts[ $code ] ) ) {
				$coupon_code_counts[ $code ] = 0;
			}

			$coupon_code_counts[ $code ]++;
		}

		$should_add_colors = 3 === count( $data[0] );
		$color             = $data[1][2] ?? '';
		$entry_count       = (int) FrmEntry::getRecordCount( $field->form_id );
		$no_coupon_count   = $entry_count - count( $coupon_code_metas );
		$new_data          = array( $data[0] );

		foreach ( $coupon_code_counts as $code => $count ) {
			$new_row = array( $code, $count );
			if ( $should_add_colors ) {
				$new_row[] = $color;
			}

			$new_data[] = $new_row;
		}

		if ( $no_coupon_count ) {
			$new_row = array( __( 'No coupon used', 'formidable-coupons' ), $no_coupon_count );
			if ( $should_add_colors ) {
				$new_row[] = $color;
			}

			$new_data[] = $new_row;
		}

		return $new_data;
	}
}
