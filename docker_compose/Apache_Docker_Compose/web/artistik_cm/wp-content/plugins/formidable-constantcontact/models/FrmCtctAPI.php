<?php
class FrmCtctAPI {

	/**
	 * @var string $api_base_url the URL used for FrmCtctAPI::make_request function calls.
	 */
	protected $api_base_url = 'https://api.cc.email/v3';

	/**
	 * @var int $entry_id
	 */
	protected $entry_id = 0;

	/**
	 * @var string $api_key The active API key used for Constant Contact integration.
	 */
	protected $api_key = '';

	/**
	 * @since 1.04
	 *
	 * @var string $legacy_api_key the API key used for legacy Constant Contact integration.
	 */
	protected $legacy_api_key = '5ccb8551-6e77-4703-b8f7-1fc1d8d9627b';

	/**
	 * @deprecated 1.04 This secret is no longer used in new implementations.
	 *
	 * @var string $secret
	 */
	protected $secret = '';

	/**
	 * @var string $access_token
	 */
	protected $access_token = '';

	/**
	 * @var string $refresh_token
	 */
	protected $refresh_token = '';

	/**
	 * @var int|null $api_index
	 */
	private $api_index;

	/**
	 * @var int $api_version
	 */
	private $api_version = 1;

	/**
	 * @var WP_Post|null $action
	 */
	protected $action;

	public function __construct() {
		$ctct_settings       = FrmCtctSettingsController::get_settings();
		$settings            = $ctct_settings->settings;
		$this->access_token  = $settings->access_token;
		$this->refresh_token = $settings->refresh_token;
		$this->api_key       = $settings->api_key;
		$this->api_index     = $settings->api_index;
		$this->api_version   = $settings->api_version;
	}

	/**
	 * @return string
	 */
	public function auth_url() {
		return wp_nonce_url( admin_url( 'admin-ajax.php?action=frm_ctct_auth_url' ) );
	}

	/**
	 * Make a request to API server for an Authentication URL to redirect to.
	 *
	 * @return array $result {
	 *     @type bool        $success
	 *     @type string|null $url
	 *     @type string|null $error
	 * }
	 */
	public function call_api_for_auth_url() {
		$headers = self::build_headers_for_post();

		if ( false === $headers ) {
			return array(
				'success' => false,
				'error'   => 'Unable to build Authorization headers for Constant Contact',
			);
		}

		$api_url  = $this->get_api_url();
		$req_args = array(
			'method'  => 'POST',
			'headers' => $headers,
			'body'    => array(
				'frm_cc_request_type' => 'auth_url',
				'original_url'        => admin_url( 'admin.php?page=formidable-settings&t=constantcontact_settings' ),
			),
		);
		$result   = wp_remote_request( $api_url, $req_args );

		if ( is_wp_error( $result ) ) {
			$response = $result->get_error_message();
		} else {
			$response = json_decode( wp_remote_retrieve_body( $result ) );
			if ( is_object( $response ) && isset( $response->error ) ) {
				$response = $response->error_description;
			}
		}

		$error = false;

		if ( is_object( $response ) && ! isset( $response->url ) ) {
			$error = __( 'Failed to Authorize your request.', 'formidable-ctct' );
		} elseif ( is_string( $response ) ) {
			$error = $response;
		}

		if ( false !== $error ) {
			return array(
				'success' => false,
				'error'   => is_string( $response ) ? $response : '',
			);
		}

		if ( is_object( $response ) && isset( $response->api_key ) && isset( $response->index ) ) {
			$settings                      = FrmCtctSettingsController::get_settings();
			$settings->settings->api_key   = sanitize_text_field( $response->api_key );
			$settings->settings->api_index = absint( $response->index );
			$settings->store();

			if ( class_exists( 'FrmInbox' ) ) {
				$inbox = new FrmInbox();
				$inbox->dismiss( 'legacy_ctct_api' );
			}
		}

		return array(
			'success' => true,
			'url'     => is_object( $response ) ? $response->url : '',
		);
	}

	/**
	 * Get the Authorization header with pro license information to send to the API endpoint to verify the request.
	 *
	 * @return array|false
	 */
	private static function build_headers_for_post() {
		$pro_license = is_callable( 'FrmAddonsController::get_pro_license' ) ? FrmAddonsController::get_pro_license() : false;

		if ( ! $pro_license ) {
			return false;
		}

		$site_url = preg_replace( '#^https?://#', '', home_url() ); // remove protocol from url (our url cannot include the colon)
		$site_url = preg_replace( '/:[0-9]+/', '', $site_url );     // remove port from url (mostly helpful in development)

		// wpml might add a language to the url. don't send that to the server.
		$split_on_language = explode( '/?lang=', $site_url );
		if ( 2 === count( $split_on_language ) ) {
			$site_url = $split_on_language[0];
		}

		return array(
			'Authorization' => 'Basic ' . base64_encode( $site_url . ':' . $pro_license ),
		);
	}

	/**
	 * Make a request for an Access Token via Authorization Code.
	 *
	 * @param string $auth_code
	 * @return mixed
	 */
	public function get_access_token( $auth_code ) {
		$body = array(
			'code'       => $auth_code,
			'grant_type' => 'authorization_code',
		);
		return $this->token_request( $body );
	}

	/**
	 * Get the URL of the API endpoint used for authenticating with Constant Contact.
	 *
	 * @since 1.04
	 *
	 * @return string
	 */
	private function get_api_url() {
		$url = getenv( 'FRM_CTCT_APIURL' );
		if ( is_string( $url ) ) {
			return $url;
		}
		return 'https://api.strategy11.com';
	}

	/**
	 * Make a refresh token request if a non-token request was unauthorized and a refresh token is available.
	 *
	 * @since 1.0
	 *
	 * @param mixed $response
	 * @return bool true if tokens were refreshed
	 */
	private function maybe_refresh_access_token( $response ) {
		if ( ! $this->is_unauthorized( $response ) || empty( $this->refresh_token ) ) {
			return false;
		}

		$body     = array(
			'refresh_token' => $this->refresh_token,
			'grant_type'    => 'refresh_token',
		);
		$response = $this->using_legacy_api() ? $this->legacy_token_request( $body ) : $this->token_request( $body );

		if ( 'unknown, invalid, or expired refresh token' === $response ) {
			$this->clear_tokens();
			return false;
		}

		$refreshed = false;
		$this->save_tokens( $response );
		if ( is_object( $response ) && isset( $response->access_token ) ) {
			$refreshed = true;
		}

		return $refreshed;
	}

	/**
	 * Make a request to the API server for a new Access Token (either with an Authorization Code or with a Refresh Token).
	 *
	 * @param array $body {
	 *     @type string      $grant_type    Supported values include 'refresh_token' and 'authorization_code'.
	 *     @type string|null $refresh_token Used for refresh_token grant type.
	 *     @type string|null $code          Used for authorization_code grant type.
	 * }
	 * @return mixed
	 */
	private function token_request( $body ) {
		$headers = self::build_headers_for_post();

		if ( false === $headers ) {
			return 'Unable to build Authorization headers for Constant Contact';
		}

		$body['ctct_api_index'] = $this->get_api_index();
		$req_args               = array(
			'method'  => 'POST',
			'headers' => $headers,
			'body'    => $body,
		);
		$result                 = wp_remote_request( $this->get_api_url(), $req_args );

		$response = $this->handle_token_request_result( $result );

		// Set API version after the first successful token request.
		if ( is_object( $response ) ) {
			$settings                        = FrmCtctSettingsController::get_settings();
			$settings->settings->api_version = 2;
			$this->api_version               = 2;
		}

		return $response;
	}

	/**
	 * Check option for API index that gets sent with token requests.
	 *
	 * @return int
	 */
	private function get_api_index() {
		return $this->api_index;
	}

	/**
	 * Handle the result of a token request (Current or Legacy).
	 *
	 * @since 1.04
	 *
	 * @param mixed  $result
	 * @return mixed $response
	 */
	private function handle_token_request_result( $result ) {
		if ( is_wp_error( $result ) ) {
			$response = $result->get_error_message();
		} else {
			$response = json_decode( wp_remote_retrieve_body( $result ) );
			if ( is_object( $response ) && isset( $response->error ) ) {
				$response = $response->error_description;
			} else {
				$this->save_tokens( $response );
			}
		}
		return $response;
	}

	/**
	 * Check the credentials are still set to the legacy API.
	 *
	 * @since 1.04
	 *
	 * @return bool
	 */
	private function using_legacy_api() {
		return $this->api_version < 2;
	}

	/**
	 * Make legacy token requests if the new connection has not been established.
	 *
	 * @since 1.04
	 *
	 * @param array $body
	 * @return mixed
	 */
	private function legacy_token_request( $body ) {
		$url      = 'https://idfed.constantcontact.com/as/token.oauth2';
		$req_args = array(
			'method'  => 'POST',
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $this->legacy_api_key . ':EE0E28wDy7ssRUaqo04oRQ' ),
			),
			'body'    => $body,
		);
		$result   = wp_remote_request( $url, $req_args );
		return $this->handle_token_request_result( $result );
	}

	/**
	 * Get Lists
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function get_lists() {
		$cached_lists = get_transient( 'frm-ctct-lists' );
		if ( $cached_lists ) {
			return $cached_lists;
		}

		$response = $this->make_request( '/contact_lists/', 'GET' );

		if ( is_object( $response ) && isset( $response->lists ) ) {
			$lists = array();
			foreach ( $response->lists as $list_row ) {
				$lists[] = array(
					'label' => $list_row->name,
					'id'    => $list_row->list_id,
				);
			}

			set_transient( 'frm-ctct-lists', $lists, 60 * 60 * 60 );
			$this->set_last_checked();
			return $lists;
		}

		if ( $this->is_unauthorized( $response ) ) {
			return array(
				'error' => sprintf(
					/* translators: %1$s: Start link HTML, %2$s: end link HTML */
					esc_html__( 'The Constant Contact authorization has expired. Please return to the %1$sGlobal settings%2$s and reauthorize.', 'formidable-ctct' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=formidable-settings&t=constantcontact_settings' ) ) . '" target="_blank" rel="noopener">',
					'</a>'
				),
			);
		}

		$this->show_error( $response );
		return array();
	}

	/**
	 * Get custom fields
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function get_custom_fields() {
		$cached = get_transient( 'frm-ctct-fields' );
		if ( $cached ) {
			return $cached;
		}

		$fields   = array();
		$response = $this->make_request( '/contact_custom_fields/', 'GET' );

		if ( ! is_object( $response ) || ! isset( $response->custom_fields ) ) {
			$this->show_error( $response );
			return array();
		}

		$fields = array();
		foreach ( $response->custom_fields as $custom_field ) {
			if ( 'date' === $custom_field->type ) {
				$new_field = array(
					'name' => $custom_field->label,
					'type' => array( 'date' ),
				);
			} else {
				$new_field = $custom_field->label;
			}
			$fields[ $custom_field->custom_field_id ] = $new_field;
		}

		set_transient( 'frm-ctct-fields', $fields, 60 * 60 * 60 );
		$this->set_last_checked();

		return $fields;
	}

	/**
	 * @return void
	 */
	private function set_last_checked() {
		$settings                         = FrmCtctSettingsController::get_settings();
		$settings->settings->last_checked = time();
		$settings->store();
	}

	/**
	 * If the tokens are no longer valid, clear them out
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	private function clear_tokens() {
		$settings                          = FrmCtctSettingsController::get_settings();
		$settings->settings->refresh_token = '';
		$settings->settings->access_token  = '';
		$settings->settings->last_checked  = '';
		$this->refresh_token               = '';
		$this->access_token                = '';
		$settings->store();
	}

	/**
	 * After new tokens are generated, save them
	 *
	 * @since 1.0
	 *
	 * @param mixed $response
	 * @return void
	 */
	private function save_tokens( $response ) {
		$settings = FrmCtctSettingsController::get_settings();
		$this->set_tokens( $response, $settings );
		$settings->store();
	}

	/**
	 * Add tokens to the settings to be saved later
	 *
	 * @since 1.0
	 *
	 * @param mixed           $response
	 * @param FrmCtctSettings $settings
	 * @return void
	 */
	public function set_tokens( $response, &$settings ) {
		if ( ! $response || ! isset( $response->access_token ) ) {
			return;
		}

		$settings->settings->access_token  = $response->access_token;
		$settings->settings->refresh_token = $response->refresh_token;
		$settings->settings->last_checked  = time();
		$this->refresh_token               = $response->refresh_token;
		$this->access_token                = $response->access_token;
	}

	/**
	 * Add data to ConstantContact Object
	 *
	 * @since  1.0
	 *
	 * @param array $data
	 * @param array $atts
	 * @return void
	 */
	public function create_or_update_contact( $data, $atts = array() ) {
		$this->set_entry( $atts );
		if ( ! isset( $data['street_addresses'] ) && ! isset( $data['phone_numbers'] ) ) {
			// create or update contact subscription
			$this->sign_up_contact( $data );
			return;
		}

		$contact_exists = $this->make_request( '/contacts', 'GET', array( 'email' => $data['email_address'] ) );
		if ( is_object( $contact_exists ) && ! empty( $contact_exists->contacts ) ) {
			$contact = reset( $contact_exists->contacts );

			// update the contact to set multiple addresses
			$this->update_contact( $contact->contact_id, $data );
		} else {
			$response = $this->sign_up_contact( $data );

			if ( is_object( $response ) && isset( $response->contact_id ) ) {
				$this->update_contact( $response->contact_id, $data );
			}
		}
	}

	/**
	 * Creates a contact or updates existing one based on email address match.
	 * NOTE: This endpoint doesn't support updating multiple contact addresses as of the v3 api.
	 *
	 * @since 1.05
	 *
	 * @param array $data
	 *
	 * @return mixed
	 */
	private function sign_up_contact( $data ) {
		$data['create_source'] = 'Contact';
		return $this->make_request( '/contacts/sign_up_form', 'POST', null, $data );
	}

	/**
	 * Updates existing contact, used to update street_addresses when there are multiple address mappings.
	 *
	 * @since 1.05
	 *
	 * @param string $contact_id
	 * @param array $data
	 *
	 * @return object
	 */
	private function update_contact( $contact_id, $data ) {
		$data['update_source'] = 'Contact';
		$data['email_address'] = array( 'address' => $data['email_address'] );
		return $this->make_request( '/contacts/' . $contact_id, 'PUT', null, $data );
	}

	/**
	 * Check if contact already exists.
	 *
	 * @since 1.02
	 *
	 * @param array $data
	 * @return mixed
	 */
	private function get_existing_contact( $data ) {
		$query = array(
			'email'   => $data['email_address']['address'],
			'status'  => 'all', // Allow deleted contacts to be updated.
			'include' => 'list_memberships',
		);
		if ( ! empty( $data['custom_fields'] ) ) {
			// Include custom fields for merging.
			$query['include'] .= ',custom_fields';
		}
		return $this->make_request( '/contacts', 'GET', $query );
	}

	/**
	 * @param array $atts
	 * @return void
	 */
	private function set_entry( $atts ) {
		if ( ! isset( $atts['entry'] ) ) {
			return;
		}

		$this->entry_id = $atts['entry']->id;
		$this->action   = $atts['action'];
	}

	/**
	 * @param string     $uri    The url for the API request
	 * @param string     $method GET, POST, etc.
	 * @param array|null $params
	 * @param array      $data   Includes the entry and form action
	 * @return mixed
	 */
	private function make_request( $uri, $method, $params = array(), $data = array() ) {
		// setup query params
		$params['api_key'] = $this->using_legacy_api() ? $this->legacy_api_key : $this->api_key;
		$querystring       = urldecode( http_build_query( $params ) );
		$url               = $this->api_base_url . $uri . '?' . $querystring;

		$req_args = array(
			'method'  => $method,
			'headers' => array(
				'content-type'  => 'application/json',
				'Authorization' => 'Bearer ' . $this->access_token,
			),
		);

		if ( $data ) {
			$req_args['body'] = json_encode( $data );
		}

		$result = wp_remote_request( $url, $req_args );

		$this->log_results(
			array(
				'response' => $result,
				'headers'  => $req_args['headers'],
				'body'     => json_encode( $data ),
				'url'      => $url,
			)
		);

		// handle response
		if ( is_wp_error( $result ) ) {
			$response = $result->get_error_message();
		} else {
			//no error
			$response = json_decode( wp_remote_retrieve_body( $result ) );
		}

		$refreshed = $this->maybe_refresh_access_token( $response );
		if ( $refreshed ) {
			$response = $this->make_request( $uri, $method, $params, $data );
		}

		return $response;
	}

	/**
	 * @param mixed $response
	 * @return void
	 */
	private function show_error( $response ) {
		echo '<pre>';
		echo esc_html( print_r( $response, 1 ) );
		echo '</pre>';
	}

	/**
	 * @param mixed $response
	 * @return bool
	 */
	private function is_unauthorized( $response ) {
		return is_object( $response ) && isset( $response->error_key ) && 'unauthorized' === $response->error_key;
	}

	/**
	 * @param array $atts
	 * @return void
	 */
	public function log_results( $atts ) {
		if ( ! class_exists( 'FrmLog' ) || empty( $this->entry_id ) ) {
			return;
		}

		$body    = wp_remote_retrieve_body( $atts['response'] );
		$content = $this->process_response( $atts['response'], $body );
		$message = isset( $content['message'] ) ? $content['message'] : '';

		$headers = '';
		$this->array_to_list( $atts['headers'], $headers );

		$content = array(
			'title'   => __( 'Constant Contact:', 'formidable-ctct' ) . ' ' . $this->action->post_title,
			'content' => (array) $body,
			'fields'  => array(
				'entry'   => $this->entry_id,
				'action'  => $this->action->ID,
				'code'    => isset( $content['code'] ) ? $content['code'] : '',
				'url'     => $atts['url'],
				'message' => $message,
				'request' => $atts['body'],
			),
		);

		$log = new FrmLog();
		$log->add( $content );
	}

	/**
	 * @param mixed $response
	 * @param mixed $body
	 * @return array
	 */
	private function process_response( $response, $body ) {
		$processed = array(
			'message' => '',
			'code'    => 'FAIL',
		);

		if ( is_wp_error( $response ) ) {
			$processed['message'] = $response->get_error_message();
		} elseif ( 'error' === $body || is_wp_error( $body ) ) {
			$processed['message'] = __( 'You had an HTTP connection error', 'formidable-api' );
		} elseif ( isset( $response->error_key ) ) {
			$processed['message'] = $response->error_key . ':' . $response->error_message;
		} elseif ( isset( $response['response'] ) && isset( $response['response']['code'] ) ) {
			$processed['code']    = $response['response']['code'];
			$processed['message'] = $response['body'];
		}

		return $processed;
	}

	/**
	 * @param array  $array
	 * @param string $list
	 * @return void
	 */
	private function array_to_list( $array, &$list ) {
		foreach ( $array as $k => $v ) {
			$list .= "\r\n" . $k . ': ' . $v;
		}
	}
}
