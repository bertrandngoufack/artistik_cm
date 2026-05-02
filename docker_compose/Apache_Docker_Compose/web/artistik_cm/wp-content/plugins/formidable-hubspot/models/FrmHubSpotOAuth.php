<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * OAuth2 manager class.
 *
 * @since 2.0
 */
final class FrmHubSpotOAuth extends FrmHubSpotAPI implements FrmHubSpotAuth {

	/**
	 * @var FrmHubSpotEncryptionHelper $encryption
	 */
	private $encryption;

	/**
	 * @var FrmHubSpotSettings $settings
	 */
	private $settings;

	/**
	 * @var FrmHubSpotRemoteRequest $remote_request
	 */
	private $remote_request;

	/**
	 * @var string $private_app_access_token
	 */
	protected $private_app_access_token;

	/**
	 * Constructor.
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->encryption     = new FrmHubSpotEncryptionHelper();
		$this->remote_request = new FrmHubSpotRemoteRequest( true, 20 );
		$this->settings       = FrmHubSpotAppHelper::get_settings();
		parent::__construct();
	}

	/**
	 * Get auth code after authorization or revoke the authorization depends on user request.
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public static function authorization() {
		FrmAppHelper::permission_check( 'frm_change_settings' );
		check_ajax_referer( 'frm_hubspot_ajax', 'security' );

		$task = FrmAppHelper::get_post_param( 'task', false );

		if ( 'code_exchange' === $task ) {
			$auth_code = FrmAppHelper::get_post_param( 'auth_code', 0 );
			$response  = ( new self() )->set_oauth2_token( 'authorization_code', $auth_code );
		} elseif ( 'revoke' === $task ) {
			$response = ( new self() )->revoke();
		} else {
			$response = new WP_Error( 'invalid_task', __( 'Invalid task for ajax endpoint', 'formidable-hubspot' ) );
		}

		$success = is_wp_error( $response ) ? false : true;
		wp_send_json(
			array(
				'success' => $success,
				'result'  => $response,
			)
		);
	}

	/**
	 * Request for access token.
	 *
	 * @since 2.0
	 *
	 * @param string $grant_type could be 'refresh_token' or 'authorization_code'.
	 * @param string $auth_code 'refresh_token' or new code captured from hubspot for first time.
	 *
	 * @return WP_Error|String
	 */
	private function set_oauth2_token( $grant_type, $auth_code ) {
		$url = add_query_arg(
			array(
				'grant_type' => $grant_type,
				'url'        => trailingslashit( FrmHubSpotAppHelper::get_redirect_url() ),
				'code'       => $auth_code,
				'license'    => FrmHubSpotAppHelper::prepare_license_to_validate(),
			),
			FrmHubSpotAppHelper::get_code_exchange_url()
		);

		try {
			$response = $this->remote_request->post( $url );
		} catch ( Exception $exception ) {
			/* translators: %1$s: the fetched URL, %2$s the error message that was returned */
			return new WP_Error( 'http_error', sprintf( __( 'Failed to fetch: %1$s (%2$s)', 'formidable-hubspot' ), $url, $exception->getMessage() ) );
		}

		$auth_obj = json_decode( wp_remote_retrieve_body( (array) $response ), true );

		// Return error if there is some unknown issue happened.
		if ( ! isset( $auth_obj['access_token'] ) && ! isset( $auth_obj['refresh_token'] ) ) {
			return new WP_Error( 'failed_access_token', __( 'There is an issue with your access token. Please try to reauthorize the HubSpot API from the Global settings.', 'formidable-hubspot' ) );
		}

		$auth_credentials = array(
			'expires_in'    => isset( $auth_obj['expires_in'] ) ? strtotime( '+' . $auth_obj['expires_in'] . ' seconds' ) : '',
			'access_token'  => isset( $auth_obj['access_token'] ) ? $this->encryption->encrypt( $auth_obj['access_token'] ) : '',
			'refresh_token' => isset( $auth_obj['refresh_token'] ) ? $this->encryption->encrypt( $auth_obj['refresh_token'] ) : '',
		);

		// Update auth into db.
		$setting_args = array( 'frm_hubspot_formidable_hubspot_oauth' => $auth_credentials );

		if ( 'authorization_code' === $grant_type ) {
			$setting_args['frm_hubspot_setting_toggle'] = true;
		}

		$this->settings->update( $setting_args );
		$this->settings->store();

		return $auth_obj['access_token'];
	}

	/**
	 * Get access token.
	 *
	 * @since 2.0
	 *
	 * @return WP_Error|string access token.
	 */
	public function get_access_token() {
		$auth_settings   = $this->settings->formidable_hubspot_oauth;
		$expiration_time = isset( $auth_settings['expires_in'] ) ? $auth_settings['expires_in'] : false;
		if ( ! $expiration_time ) {
			return new WP_Error( 'http_expiration', __( 'The saved code is missing an expiration. Please deauthorize and then authorize again.', 'formidable-hubspot' ) );
		}
		// Give the access token a 5 minute buffer (300 seconds).
		$expiration_time = $expiration_time - 300;
		if ( time() < $expiration_time ) {
			return $this->encryption->decrypt( $auth_settings['access_token'] );
		}
		// at this point we have an expiration time but it is in the past or will be very soon.
		$this->private_app_access_token = self::set_oauth2_token( 'refresh_token', $this->encryption->decrypt( $auth_settings['refresh_token'] ) );
		return $this->private_app_access_token;
	}

	/**
	 * Revoke OAuth.
	 *
	 * @since 2.0
	 *
	 * @return WP_Error|string
	 */
	private function revoke() {

		$refresh_token = $this->encryption->decrypt( $this->settings->formidable_hubspot_oauth['refresh_token'] );

		// Delete access token from DB no matter what response is, This will prevents conflict when client terminated app from HubSpot dashboard.
		ob_start();
		$this->settings->update( array( 'frm_hubspot_formidable_hubspot_oauth' => '' ) );
		$this->settings->store();
		ob_clean();

		// Clear cache.
		$this->clear_cache();

		// By any chance if refresh token value set to WP_Error we could simply exit earlier without a call to hubspot for revoke.
		if ( is_wp_error( $refresh_token ) ) {
			return new WP_Error( 'invalid_token', __( 'Your API has been disconnected.', 'formidable-hubspot' ) );
		}

		$url = 'https://api.hubapi.com/oauth/v1/refresh-tokens/' . $refresh_token;

		try {
			$this->remote_request->delete( $url );
		} catch ( Exception $exception ) {
			/* translators: %1$s: the fetched URL, %2$s the error message that was returned */
			return new WP_Error( 'http_error', sprintf( __( 'Your API has been disconnected from HubSpot and there is no further action needed however following issue has been occurred in disconnection process: %1$s (%2$s)', 'formidable-hubspot' ), $url, $exception->getMessage() ) );
		}

		return esc_html__( 'HubSpot API has been successfully disconnected', 'formidable-hubspot' );
	}

}
