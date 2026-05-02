<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to access this file directly.' );
}

/**
 * Private access token manager class.
 *
 * @since 2.0
 */
final class FrmHubSpotPAT extends FrmHubSpotAPI implements FrmHubSpotAuth {

	/**
	 * @var string $private_app_access_token
	 */
	protected $private_app_access_token;

	/**
	 * Get access token.
	 *
	 * @since 2.0
	 *
	 * @return string access token.
	 */
	public function get_access_token() {
		$setting       = FrmHubSpotAppHelper::get_settings();
		return $setting->private_app_access_token;
	}

	/**
	 * PAT authorization in action page.
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public static function authorization() {
		FrmAppHelper::permission_check( 'frm_change_settings' );
		check_ajax_referer( 'frm_hubspot_ajax', 'security' );

		$private_app_access_token = FrmAppHelper::get_post_param( 'auth_code', '' );
		$setting       = FrmHubSpotAppHelper::get_settings();
		$api         = ( new self() );
		// Clear the cache so next validation could work correctly.
		$api->clear_cache();

		// Sanitization will happen in the following method. Since there is an output on PAT check we need to turn it off so ob never gets used.
		$setting->update( array( 'frm_hubspot_private_app_access_token' => $private_app_access_token ) );
		$setting->store();

		if ( is_wp_error( $api->is_valid_private_app_access_token( $private_app_access_token ) ) ) {
			$json = array(
				'success' => false,
				'result' => /* translators: %1$s: HubSpot auth doc */
				sprintf(
					'Authentication credentials not found you can find more details at %1$s',
					esc_url( 'https://developers.hubspot.com/docs/methods/auth/oauth-overview' )
				),
			);
		} else {
			// No need to send result, in JS side we have already a message for it.
			$json = array(
				'success' => true,
			);
		}

		wp_send_json( $json );
	}
}
