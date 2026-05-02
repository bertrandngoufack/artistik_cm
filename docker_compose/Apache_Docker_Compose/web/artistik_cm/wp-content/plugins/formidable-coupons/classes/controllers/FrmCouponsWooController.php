<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmCouponsWooController {

	/**
	 * Set the regular price of a cart item to the total price plus the discount.
	 *
	 * @since 1.0
	 *
	 * @param float    $total_price
	 * @param array    $cart_item
	 * @param stdClass $entry
	 *
	 * @return void
	 */
	public static function on_add_cart_item( $total_price, $cart_item, $entry ) {
		$coupon_field = FrmProFormsHelper::has_field( 'coupon', $entry->form_id );
		if ( ! $coupon_field ) {
			return;
		}

		$discount = self::get_discount_for_entry( $entry->id, $coupon_field->id );
		if ( false === $discount ) {
			return;
		}

		$regular_price = round( $total_price + floatval( $discount ), 2 );
		$cart_item['data']->set_regular_price( $regular_price );
	}

	/**
	 * @param int|string $entry_id
	 * @param int|string $coupon_field_id
	 *
	 * @return false|string
	 */
	private static function get_discount_for_entry( $entry_id, $coupon_field_id ) {
		$discount = FrmDb::get_var(
			'frm_item_metas',
			array(
				'item_id'  => $entry_id,
				'field_id' => $coupon_field_id,
			),
			'meta_value'
		);
		if ( ! $discount || ! is_string( $discount ) || ! is_numeric( $discount ) ) {
			return false;
		}

		return $discount;
	}

	/**
	 * Add the coupon code to the cart item.
	 *
	 * @since 1.0
	 *
	 * @param string $display
	 * @param array  $args
	 *
	 * @return string
	 */
	public static function on_addons_cart_option( $display, $args ) {
		$field = $args['field'];
		if ( 'coupon' !== FrmField::get_field_type( $field ) ) {
			return $display;
		}

		$entry       = $args['entry'];
		$coupon_code = FrmCouponsAppHelper::get_coupon_code_for_entry( $entry->id );
		if ( ! $coupon_code ) {
			return $display;
		}

		$field_object = new FrmCouponsFieldCoupon( $field );
		$field_value  = $field_object->format_coupon_code_and_discount_values( $coupon_code, $display, $entry->id );
		return $field_value;
	}
}
