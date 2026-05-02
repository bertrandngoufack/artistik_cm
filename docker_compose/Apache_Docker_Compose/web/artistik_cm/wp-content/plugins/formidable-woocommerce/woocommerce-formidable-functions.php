<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Check and see if the form has a total field.
 *
 * @since  1.0
 * @param  int $form_id the attached form (if any)
 * @param  int $product_id the current product
 * @return bool
 */
function wc_fp_form_has_total_field( $form_id ) {
	_deprecated_function( __FUNCTION__, '1.10', 'WC_Formidable_App_Helper::form_has_total_field' );
	return WC_Formidable_App_Helper::form_has_total_field( $form_id );
}

function wc_fp_field_is_total( $field ) {
	_deprecated_function( __FUNCTION__, '1.10', 'WC_Formidable_App_Helper::field_is_total' );
	return WC_Formidable_App_Helper::field_is_total( $field );
}

function wc_fp_get_total_for_entry( $entry ) {
	_deprecated_function( __FUNCTION__, '1.10', 'WC_Formidable_App_Helper::get_total_for_entry' );
	return WC_Formidable_App_Helper::get_total_for_entry( $entry );
}
