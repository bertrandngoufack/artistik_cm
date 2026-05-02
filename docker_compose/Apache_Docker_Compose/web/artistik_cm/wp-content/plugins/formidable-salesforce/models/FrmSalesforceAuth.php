<?php
/**
 * Save, refresh, and revoke the Outh token.
 */
class FrmSalesforceAuth {

	public static function formidable_finish_code_exchange( $die = true ) {
		check_ajax_referer( 'salesforce-auth-ajax-nonce', 'security' );
		if ( isset( $_POST['auth_code'] ) ) {
			$auth_code = sanitize_text_field( wp_unslash( $_POST['auth_code'] ) );
			$token = self::set_oauth2_token( $auth_code, 'auth_code' );
			if ( $die ) {
				echo esc_html( $token );
				wp_die();
			}
		}
	}

	/**
	 * Get access or refresh token.
	 *
	 * @param string $grant_code
	 * @param string $grant_type
	 * @return string - The access token or an empty string on failure.
	 */
	public static function set_oauth2_token( $grant_code, $grant_type ) {
		$settings     = get_option( 'frm_salesforce_options', true );
		$authsettings = get_option( 'formidable_salesforce_auth', true );
		// $url = !empty( $settings['sandbox_mode'] ) ? WP_SALESFORCE_OAUTH_SANDBOX_URL : WP_SALESFORCE_OAUTH_LIVE_URL;
		$success = true;
		$oauth2token_url = FrmSalesforceSettingsController::get_oauth_url( 'token' );
		$clienttoken_post = array(
			'client_id'     => $settings->client_id,
			'client_secret' => $settings->client_secret,
		);

		$redirect_uri = FrmSalesforceSettingsController::get_auth_redirect_url();

		if ( 'auth_code' === $grant_type ) {
			// The "auth_code" grant type is to do the initial code exchange.
			$clienttoken_post['code'] = $grant_code;
			$clienttoken_post['redirect_uri'] = $redirect_uri;
			$clienttoken_post['grant_type'] = 'authorization_code';
		} elseif ( 'refresh_token' === $grant_type ) {
			// The "refresh token" grant type is to use a refresh token to get a new access token.
			if ( empty( $authsettings['refresh_token'] ) ) {
				echo 'The token is missing. Please resave the <a href="' . esc_attr( admin_url( 'admin.php?page=formidable-settings&t=salesforce_settings' ) ) . '">authentication settings</a>';
				return '';
			}
			$clienttoken_post['refresh_token'] = $authsettings['refresh_token'];
			$clienttoken_post['grant_type'] = 'refresh_token';
		}
		$postargs = array(
			'body' => $clienttoken_post,
		);

		$response = wp_remote_post( $oauth2token_url, $postargs );
		$auth_obj = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( is_wp_error( $response ) || ( isset( $auth_obj['error'] ) && ! empty( $auth_obj['error'] ) ) ) {
			self::show_error( $response, $auth_obj );
			return '';
		}

		if ( ! empty( $auth_obj['access_token'] ) ) {
			if ( ! is_array( $authsettings ) ) {
				$authsettings = array();
			}

			if ( isset( $auth_obj['refresh_token'] ) ) {
				$authsettings['refresh_token'] = $auth_obj['refresh_token'];
			}
			if ( isset( $auth_obj['access_token'] ) ) {
				$authsettings['access_token'] = $auth_obj['access_token'];
				$access_token = $auth_obj['access_token'];
			}
			if ( isset( $auth_obj['issued_at'] ) ) {
				$authsettings['issued_at'] = $auth_obj['issued_at'];
			}

			if ( isset( $auth_obj['instance_url'] ) ) {
				$authsettings['instance_url'] = $auth_obj['instance_url'];
				// $authsettings['access_token_expires'] = strtotime( "+" . $auth_obj['expires_in'] . " seconds" ) ;
			}

			$success = update_option( 'formidable_salesforce_auth', $authsettings, false );
		} else {
			$success = false;
		}

		if ( ! isset( $access_token ) || empty( $access_token ) ) {
			$access_token = '';
		}

		return $access_token;
	}

	private static function show_error( $response, $body ) {
		$page = FrmAppHelper::get_param( 'page', '', 'get', 'sanitize_text_field' );
		$tab  = FrmAppHelper::get_param( 't', '', 'get', 'sanitize_text_field' );
		$is_global_settings = 'formidable-settings' === $page && 'salesforce_settings' === $tab;

		if ( ! $is_global_settings ) {
			return;
		}

		$has_error = ( isset( $body['error'] ) && ! empty( $body['error'] ) );
		if ( $has_error ) {
			$message = $body['error'] . ': ' . $body['error_description'];
		} else {
			$message = print_r( $response, 1 );
		}
		echo '<div class="frm_error_style">' . esc_html( $message ) . '</div>';
	}

	/**
	 * Get an access token to use for Salesforce authentication.
	 *
	 * @param bool $new
	 * @return string
	 */
	public static function get_access_token( $new = false ) {
		$token = '';
		if ( $new ) {
			$token = self::set_oauth2_token( null, 'refresh_token' );
		} else {
			$authsettings = get_option( 'formidable_salesforce_auth', true );
			if ( is_array( $authsettings ) ) {
				$token = $authsettings['access_token'];
			}
		}
		return $token;
	}

	public static function revoke() {
		$authsettings = get_option( 'formidable_salesforce_auth', true );

		if ( ! empty( $authsettings['refresh_token'] ) ) {
			$revoke_url = FrmSalesforceSettingsController::get_oauth_url( 'revoke' );
			$revoke_url = esc_url_raw( $revoke_url . '?token=' . $authsettings['refresh_token'] );
			$data = wp_remote_get( $revoke_url );
			$response = wp_remote_retrieve_body( $data );
			delete_option( 'formidable_salesforce_auth' );
		}

		wp_die();
	}
}
