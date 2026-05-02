<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmCouponsFilterHelper extends FrmListHelper {

	/**
	 * Remove any coupons that have reached their limit.
	 *
	 * @param array $coupons
	 * @param int   $entry_id
	 *
	 * @return array
	 */
	public static function check_for_coupon_limits( $coupons, $entry_id = 0 ) {
		return array_filter(
			$coupons,
			/**
			 * @param WP_Post $coupon
			 *
			 * @return bool
			 */
			function ( $coupon ) use ( $entry_id ) {
				$data = json_decode( $coupon->post_content );
				if ( ! is_object( $data ) ) {
					return false;
				}

				$limit = $data->limit;
				if ( ! $limit || ! is_numeric( $limit ) ) {
					return true;
				}

				$coupon_code = $coupon->post_excerpt;

				if ( $entry_id ) {
					$match = FrmDb::get_var(
						'frm_item_metas',
						array(
							'field_id'   => 0,
							'item_id'    => $entry_id,
							'meta_value' => serialize( compact( 'coupon_code' ) ),
						)
					);
					if ( $match ) {
						return true;
					}
				}

				$used = FrmCouponsAppHelper::get_coupon_uses_by_code( $coupon_code );

				return $used < $limit;
			}
		);
	}

	/**
	 * Check coupon field settings for allowed coupons, and remove any coupons that do not match.
	 *
	 * @since 1.0
	 *
	 * @param array $coupons
	 * @param int   $form_id
	 *
	 * @return array
	 */
	public static function filter_coupons_for_form( $coupons, $form_id ) {
		$coupon_field = FrmProFormsHelper::has_field( 'coupon', $form_id );
		if ( ! $coupon_field ) {
			return array();
		}

		if ( empty( $coupon_field->field_options['allowed_coupons'] ) || ! is_array( $coupon_field->field_options['allowed_coupons'] ) ) {
			return array();
		}

		$allowed_coupon_ids = array_map( 'absint', $coupon_field->field_options['allowed_coupons'] );

		return array_filter(
			$coupons,
			function ( $coupon ) use ( $allowed_coupon_ids ) {
				return in_array( (int) $coupon->ID, $allowed_coupon_ids, true );
			}
		);
	}
}
