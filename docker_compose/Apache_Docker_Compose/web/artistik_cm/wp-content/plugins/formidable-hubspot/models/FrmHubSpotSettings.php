<?php

class FrmHubSpotSettings extends FrmSettings {

	/**
	 * Option name.
	 *
	 * @since 2.0
	 *
	 * @var string $option_name
	 */
	public $option_name = 'frm_hubspot_options';

	/**
	 * API key.
	 *
	 * @since 2.0
	 *
	 * @var string $api_key
	 */
	public $api_key;

	/**
	 * HubSpot private app access token.
	 *
	 * @since 2.0
	 *
	 * @var string $private_app_access_token
	 */
	public $private_app_access_token;

	/**
	 * OAuth2 credentials, tokens etc.
	 *
	 * @since 2.0
	 *
	 * @var array<mixed> $formidable_hubspot_oauth
	 */
	public $formidable_hubspot_oauth;

	/**
	 * Save the toggle value on DB, to recognize which methods are selected by the user.
	 *
	 * @since 2.0
	 *
	 * @var bool $auth_toggle
	 */
	public $auth_toggle;

	/**
	 * Encrypt key used for OAuth credentials.
	 *
	 * @since 2.0
	 *
	 * @var string $encrypt_key
	 */
	public $encrypt_key;

	/**
	 * Encrypt salt used for OAuth credentials.
	 *
	 * @since 2.0
	 *
	 * @var string $encrypt_salt
	 */
	public $encrypt_salt;

	/**
	 * Update values based on changes or initial result.
	 *
	 * @since 1.0
	 *
	 * @param array<mixed> $params post value.
	 * @return void
	 */
	public function update( $params ) {
		if ( isset( $params['frm_hubspot_private_app_access_token'] ) ) {
			if ( $params['frm_hubspot_private_app_access_token'] !== $this->private_app_access_token ) {
				add_filter( 'frm_message_list', array( $this, 'check_credentials' ) );
			}
			$this->private_app_access_token = sanitize_text_field( (string) $params['frm_hubspot_private_app_access_token'] );
		}
		if ( isset( $params['frm_hubspot_formidable_hubspot_oauth'] ) ) {
			$this->formidable_hubspot_oauth = map_deep( $params['frm_hubspot_formidable_hubspot_oauth'], 'sanitize_text_field' );
		}
		if ( isset( $params['frm_hubspot_encrypt_key'] ) ) {
			$this->encrypt_key = sanitize_text_field( $params['frm_hubspot_encrypt_key'] );
		}
		if ( isset( $params['frm_hubspot_encrypt_salt'] ) ) {
			$this->encrypt_salt = sanitize_text_field( $params['frm_hubspot_encrypt_salt'] );
		}

		$this->auth_toggle = ! empty( $params['frm_hubspot_setting_toggle'] );
	}

	/**
	 * Store options to db.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function store() {
		update_option( $this->option_name, $this, 'no' );
		set_transient( $this->option_name, $this );
	}

	/**
	 * If the Private App access token has changed, clear the cache and check the new key.
	 *
	 * @since 1.07
	 *
	 * @return void
	 */
	public function check_credentials() {
		$api = new FrmHubSpotAuthHelper( FrmHubSpotAppHelper::get_active_authorization_class() );
		$api->clear_cache();

		if ( ! is_wp_error( $api->is_valid_private_app_access_token( $this->private_app_access_token ) ) ) {
			$class = 'frm_updated_message';
			$message = __( 'HubSpot Private App Access Token accepted.', 'formidable-hubspot' );
		} else {
			$class = 'frm_error frm_error_style';
			$message = __( 'Invalid HubSpot Private App Access Token.', 'formidable-hubspot' );
		}

		/* translators: %1$s: PAT div class, %2$s Message */
		printf(
			'<div class="%s">%s</div>',
			esc_attr( $class ),
			esc_html( $message )
		);
	}

	public function get_options() {
		_deprecated_function( __METHOD__, '2.0' );
		return get_option( 'frm_hubspot_options' );
	}
}
