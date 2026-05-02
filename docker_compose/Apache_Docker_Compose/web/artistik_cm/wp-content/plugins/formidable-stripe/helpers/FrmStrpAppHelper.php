<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmStrpAppHelper {

	/**
	 * @var FrmStrpSettings|null
	 */
	private static $settings;

	public static function plugin_path() {
		return dirname( dirname( __FILE__ ) );
	}

	public static function plugin_folder() {
		return basename( self::plugin_path() );
	}

	public static function plugin_url() {
		return plugins_url( '', self::plugin_path() . '/formidable-stripe.php' );
	}

	public static function is_debug() {
		return defined( 'WP_DEBUG' ) && WP_DEBUG;
	}

	/**
	 * @param string $function
	 * @param array  ...$params
	 * @return mixed
	 */
	public static function call_stripe_helper_class( $function, ...$params ) {
		if ( self::should_use_stripe_connect() ) {
			if ( is_callable( "FrmStrpConnectApiAdapter::$function" ) ) {
				return FrmStrpConnectApiAdapter::$function( ...$params );
			}
		} elseif ( is_callable( "FrmStrpApiHelper::$function" ) ) {
			return FrmStrpApiHelper::$function( ...$params );
		}
		return false;
	}

	/**
	 * @return bool true if we're using connect (versus the legacy integration).
	 */
	public static function should_use_stripe_connect() {
		if ( ! class_exists( 'FrmStrpConnectApiAdapter' ) ) {
			require dirname( __FILE__ ) . '/FrmStrpConnectApiAdapter.php';
		}
		return FrmStrpConnectApiAdapter::initialize_api();
	}

	/**
	 * @return bool true if either connect or the legacy integration is set up.
	 */
	public static function stripe_is_configured() {
		return self::call_stripe_helper_class( 'initialize_api' );
	}

	/**
	 * If test mode is running, save the id somewhere else
	 *
	 * @return string
	 */
	public static function get_customer_id_meta_name() {
		$meta_name = '_frmstrp_customer_id';
		if ( 'test' === self::active_mode() ) {
			$meta_name .= '_test';
		}
		return $meta_name;
	}

	/**
	 * @return FrmStrpSettings
	 */
	public static function get_settings() {
		if ( ! isset( self::$settings ) ) {
			self::$settings = new FrmStrpSettings();
		}
		return self::$settings;
	}

	public static function active_mode() {
		$settings = self::get_settings();
		return $settings->settings->test_mode ? 'test' : 'live';
	}

	/**
	 * If the Authorize.Net plugin is also active, an older version of FrmTransAppHelper
	 * may be loaded instead. If the wrong version of FrmTransAppHelper is available, there
	 * will be a fatal error if get_formatted_amount_for_currency does not exist.
	 * If the Stripe Lite equivalent function is available in Stripe Lite, we'll call it instead.
	 * This way there is no fatal error when Authorize.Net is active, as long as Lite is v6.5 or higher.
	 * We also continue to call FrmTransAppHelper::get_formatted_amount_for_currency in case the active
	 * version of Lite is older than v6.5. In the future, we can remove that function.
	 *
	 * @since 3.1.4
	 *
	 * @param string|int $amount
	 * @param WP_Post    $action
	 *
	 * @return string
	 */
	public static function get_formatted_amount_for_currency( $amount, $action ) {
		if ( is_callable( 'FrmTransLiteAppHelper::get_formatted_amount_for_currency' ) ) {
			return FrmTransLiteAppHelper::get_formatted_amount_for_currency( $amount, $action );
		}
		return FrmTransAppHelper::get_formatted_amount_for_currency( $amount, $action );
	}

	/**
	 * @since 3.1.5
	 *
	 * @return bool True if we are not past the June 2024 cutoff date.
	 */
	public static function stripe_still_supports_api_keys() {
		return gmdate( 'Y-m-d' ) < '2024-06-01';
	}
}
