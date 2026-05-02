<?php
/**
 * WC Dependency Checker
 *
 * Checks if WooCommerce is enabled
 */
class WC_Dependencies {

	private static $active_plugins;

	public static function init() {
		_deprecated_function( __METHOD__, '1.10', 'WC_Formidable_App_Helper::set_active_plugins' );

		self::$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}
	}

	public static function woocommerce_active_check() {
		_deprecated_function( __METHOD__, '1.10', 'WC_Formidable_App_Helper::is_woocommerce_active' );
		return WC_Formidable_App_Helper::is_woocommerce_active();
	}

}


