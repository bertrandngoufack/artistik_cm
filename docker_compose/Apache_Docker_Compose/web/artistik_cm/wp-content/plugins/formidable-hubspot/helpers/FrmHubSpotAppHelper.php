<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmHubSpotAppHelper {

	/**
	 * Plugin version.
	 *
	 * @var string plug_version.
	 */
	public static $plug_version = '2.0.2';

	/**
	 * Settings holder.
	 *
	 * @since 2.0
	 *
	 * @var FrmHubSpotSettings|null $settings
	 */
	private static $settings;

	/**
	 * Get plugin version.
	 *
	 * @return string
	 */
	public static function plugin_version() {
		return self::$plug_version;
	}

	/**
	 * @return string
	 */
	public static function path() {
		return dirname( dirname( __FILE__ ) );
	}

	/**
	 * @return string
	 */
	public static function url() {
		// Previously FRM_URL constant.
		return plugins_url( '', self::path() . '/formidable-hubspot.php' );
	}

	/**
	 * The URL which will be used for authorization redirects refers to the current website URL.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public static function get_redirect_url() {
		return esc_url( home_url() );
	}

	/**
	 * Get the sheet settings.
	 *
	 * @since 2.0
	 *
	 * @return FrmHubSpotSettings
	 */
	public static function get_settings() {
		if ( ! isset( self::$settings ) ) {
			self::$settings = new FrmHubSpotSettings();
		}
		return self::$settings;
	}

	/**
	 * OAuth2 installation URL for HubSpot authorization.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public static function get_install_url() {
		return esc_url( add_query_arg( array( 'state' => self::get_redirect_url() ), self::get_base_api_url() . '/hsproxy/install/' ) );
	}

	/**
	 * Middleware endpoint to exchange the code with OAuth2 credentials.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public static function get_code_exchange_url() {
		return esc_url( self::get_base_api_url() . '/wp-json/frm/hsapi/v1/exchange' );
	}

	/**
	 * Get middleware rely endpoint to verify the coming payloads.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public static function get_rely_endpoint() {
		return esc_url( self::get_base_api_url() . '/wp-json/frm/hsapi/v1/webhooks/rely' );
	}

	/**
	 * Get base middleware URL for OAuth2.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	private static function get_base_api_url() {
		return 'https://api.strategy11.com';
	}

	/**
	 * Make a decision for choosing between the methods from the setting form.
	 *
	 * @since 2.0
	 *
	 * @return FrmHubSpotOAuth|FrmHubSpotPAT
	 */
	public static function get_active_authorization_class() {
		$settings                 = self::get_settings();
		$has_private_access_token = ! empty( $settings->private_app_access_token );
		$has_oauth                = ! empty( $settings->formidable_hubspot_oauth );
		$method_selected          = $settings->auth_toggle; // True OAuth.

		if ( $has_oauth && $method_selected || ! $has_private_access_token ) {
			return new FrmHubSpotOAuth();
		}

		// On a fresh install we need to pass a class for consistency.
		return new FrmHubSpotPAT();
	}

	/**
	 * Prepare license key and site url for sending to the server.
	 *
	 * @since 2.0
	 *
	 * @return string|bool
	 */
	public static function prepare_license_to_validate() {
		$pro_license = is_callable( 'FrmAddonsController::get_pro_license' ) ? FrmAddonsController::get_pro_license() : false;

		if ( ! $pro_license ) {
			return false;
		}

		return base64_encode( $pro_license );
	}

}
