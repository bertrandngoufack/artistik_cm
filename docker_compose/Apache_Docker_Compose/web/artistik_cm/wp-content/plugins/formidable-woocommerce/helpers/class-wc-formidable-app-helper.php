<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class WC_Formidable_App_Helper {

	/**
	 * @since 1.10
	 */
	private static $active_plugins;

	/**
	 * @since 1.10
	 */
	public static function plugin_path() {
		return dirname( dirname( __FILE__ ) );
	}

	/**
	 * @since 1.10
	 */
	public static function is_woocommerce_active() {
		if ( ! isset( self::$active_plugins ) ) {
			self::set_active_plugins();
		}

		return in_array( 'woocommerce/woocommerce.php', self::$active_plugins, true ) || array_key_exists( 'woocommerce/woocommerce.php', self::$active_plugins );
	}

	/**
	 * @since 1.10
	 */
	private static function set_active_plugins() {
		self::$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}
	}

	/**
	 * Check and see if the form has a total field.
	 *
	 * @since  1.10
	 * @param  int $form_id the attached form (if any)
	 * @param  int $product_id the current product
	 * @return false|object
	 */
	public static function form_has_total_field( $form_id ) {

		// get the form
		$fields = FrmField::get_all_for_form( $form_id );

		// reverse the fields so we get the last total
		$fields = array_reverse( $fields );

		// check for the total field
		$found = false;
		foreach ( $fields as $field ) {
			if ( self::field_is_total( $field ) ) {
				$found = $field;
				break;
			}
		}

		return $found;

	}

	/**
	 * @since 1.10
	 */
	public static function field_is_total( $field ) {
		$in_repeater = false;
		$has_calc    = ! empty( $field->field_options['calc'] );
		$calc_off    = isset( $field->field_options['use_calc'] ) && 1 != $field->field_options['use_calc'];
		$is_calc     = ( $has_calc && ! $calc_off ) || $field->type === 'total';

		if ( $is_calc ) {
			$in_repeater = FrmDb::get_var( 'frm_forms', array( 'id' => $field->form_id ), 'parent_form_id' );
			$in_repeater = ! empty( $in_repeater );
		}

		return $is_calc && ! $in_repeater;
	}

	/**
	 * @since 1.10
	 */
	public static function get_total_for_entry( $entry ) {
		$total_field = self::form_has_total_field( $entry->form_id );
		$total = FrmProEntryMetaHelper::get_post_or_meta_value( $entry, $total_field );
		return $total;
	}
}
