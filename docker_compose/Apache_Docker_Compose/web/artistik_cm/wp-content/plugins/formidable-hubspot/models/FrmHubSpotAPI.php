<?php


class FrmHubSpotAPI {

	/**
	 * @var string $api_key
	 * @deprecated 2.0
	 */
	protected $api_key;

	/**
	 * @var string $private_app_access_token
	 */
	protected $private_app_access_token;

	/**
	 * @var int $entry_id
	 */
	protected $entry_id = 0;

	/**
	 * @var WP_Post|null $action
	 */
	protected $action;

	/**
	 * @var string $list_cache
	 */
	protected $list_cache = 'frm_hubspot_lists';

	/**
	 * @var string $property_cache
	 */
	protected $property_cache = 'frm_hubspot_properties';

	/**
	 * @var string $is_pat_valid_cache
	 */
	protected $is_pat_valid_cache = 'frm_hubspot_is_pat_valid';

	/**
	 * @var FrmHubSpotRemoteRequest $remote_request
	 */
	private $remote_request;

	/**
	 * Constructor.
	 *
	 * @since 2.0
	 *
	 * return void
	 */
	public function __construct() {
		$this->remote_request = new FrmHubSpotRemoteRequest();
		$this->private_app_access_token = $this->get_access_token();
	}

	/**
	 * Get access token, This should be overrides with child methods.
	 *
	 * @since 2.0
	 *
	 * @return WP_Error|string
	 */
	protected function get_access_token() {
		return '';
	}

	/**
	 * Clear the API cache.
	 *
	 * @since 1.06
	 */
	public function clear_cache() {
		delete_transient( $this->list_cache );
		delete_transient( $this->property_cache );
		delete_transient( $this->is_pat_valid_cache );
	}

	/**
	 * Check if the API key is valid.
	 *
	 * @since 1.07
	 *
	 * @param string $key The new api key to check.
	 * @return WP_Error|bool True if valid key.
	 */
	public function is_valid_api_key( $key ) {
		_deprecated_function( __METHOD__, '2.0' );
		$this->api_key = $key;
		return $this->is_valid();
	}

	/**
	 * @since 1.10
	 *
	 * @param string $token
	 * @return WP_Error|bool
	 */
	public function is_valid_private_app_access_token( $token ) {
		$this->private_app_access_token = $token;
		return $this->is_valid();
	}

	/**
	 * @since 1.10
	 *
	 * @return WP_Error|bool
	 */
	private function is_valid() {
		// Exit early when PAT is empty.
		if ( empty( $this->private_app_access_token ) ) {
			return new WP_Error( 'empty_value', __( 'Please enter your HubSpot Private App Access Token.', 'formidable-hubspot' ) );
		}

		// Check whether PAT is authenticated before. We normally clear the transient whenever a new PAT is updated within form action or form settings so no extra call needed.
		$is_pat_valid_cache = get_transient( $this->is_pat_valid_cache );
		if ( $is_pat_valid_cache ) {
			return true;
		}

		$token_key = json_encode( array( 'tokenKey' => $this->private_app_access_token ) );

		try {
			$this->remote_request->post(
				'https://api.hubapi.com/oauth/v2/private-apps/get/access-token-info',
				array(
					'headers' => $this->get_private_app_headers(),
					'body'    => $token_key,
				)
			);
		} catch ( Exception $exception ) {
			/* translators: %1$s the error message that was returned */
			return new WP_Error( 'http_error', sprintf( __( 'Invalid HubSpot Private App Access Token.', 'formidable-hubspot' ), $exception->getMessage() ) );
		}

		set_transient( $this->is_pat_valid_cache, true );

		return true;

	}

	/**
	 * Get Lists from HubSpot.
	 *
	 * @since  1.0
	 * @since  2.0 Removed is_private_app()
	 * @return object|false Campaigns.
	 */
	public function fetch_lists() {
		$lists = get_transient( $this->list_cache );

		if ( is_object( $lists ) || ! $this->has_credentials() ) {
			return $lists;
		}

		$args  = array( 'headers' => $this->get_private_app_headers() );
		$lists = $this->fetch_all_static_contact_lists( $args );

		if ( ! is_object( $lists ) || is_wp_error( $lists ) ) {
			if ( empty( $lists ) ) {
				$lists = esc_html__( 'Your private access token could not be connected. Please check if you have valid PAT or you have an active version of cURL installed on your server.', 'formidable-hubspot' );
			}
			$this->show_error( $lists );
			return false;
		}

		// TODO: Support companies soon. Right now there are issues adding to a company list.
		// $this->add_companies_to_lists( $lists, $args );

		if ( empty( $lists->lists ) && empty( $lists->companies ) ) {
			$this->show_error( $lists );
		}

		set_transient( $this->list_cache, $lists, DAY_IN_SECONDS );

		return $lists;
	}

	/**
	 * Possibly make multiple requests to HubSpot if there are multiple pages.
	 *
	 * @since 2.0.1
	 *
	 * @param array $args
	 * @return mixed
	 */
	private function fetch_all_static_contact_lists( $args ) {
		$lists     = false;
		$offset    = 0;
		$page_size = 250; // 250 is the HubSpot limit.

		do {
			$url = $this->hubspot_url( '/lists/static?count=' . $page_size );
			if ( $offset ) {
				$url .= '&offset=' . $offset;
			}

			$response       = wp_remote_retrieve_body( wp_remote_get( $url, $args ) );
			$decoded_result = json_decode( $response );

			if ( is_wp_error( $decoded_result ) || ! is_object( $decoded_result ) ) {
				return $decoded_result;
			}

			if ( false === $lists ) {
				$lists = $decoded_result;
			} elseif ( ! empty( $decoded_result->lists ) ) {
				foreach ( $decoded_result->lists as $list ) {
					$lists->lists[] = $list;
				}
			}

			$offset += $page_size;
		} while ( ! empty( $decoded_result->{'has-more'} ) );

		return $lists;
	}

	/**
	 * Do a second query to the companies endpoint and append company data to the $lists object.
	 *
	 * @since x.x
	 *
	 * @param stdClass $lists
	 * @param array    $args
	 * @return void
	 */
	private function add_companies_to_lists( $lists, $args ) {
		$url           = 'https://api.hubapi.com/crm/v3/objects/companies';
		$response      = wp_remote_retrieve_body( wp_remote_get( $url, $args ) );
		$company_lists = json_decode( $response );

		$lists->companies = array();
		if ( ! empty( $company_lists->results ) && is_array( $company_lists->results ) ) {
			foreach ( $company_lists->results as $company ) {
				$list               = new stdClass();
				$list->listId       = $company->id; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$list->name         = $company->properties->name;
				$list->dynamic      = false;
				$lists->companies[] = $list;
			}
		}
	}

	/**
	 * @since 1.10
	 *
	 * @return array
	 */
	private function get_private_app_headers() {
		return array(
			'Authorization' => 'Bearer ' . $this->private_app_access_token,
			'Content-Type'  => 'application/json',
		);
	}

	/**
	 * Check if a valid set of credentials is set, whether it's a public App API key or a Private App Access Token.
	 *
	 * @since 1.10
	 * @since 2.0 Removed API key.
	 * @since 2.0 Change access to public.
	 *
	 * @return bool|string
	 */
	public function has_credentials() {
		return ! is_wp_error( $this->private_app_access_token ) ? $this->private_app_access_token : false;
	}

	/**
	 * Alphabetize the list of options in the form action
	 *
	 * @since 1.06
	 * @return array|object|false
	 */
	public function fetch_ordered_properties() {
		$properties = $this->fetch_properties();
		if ( is_wp_error( $properties ) || ! is_array( $properties ) ) {
			return $properties;
		}

		foreach ( $properties as $k => $group ) {
			$order = array();
			foreach ( $group->properties as $k2 => $property ) {
				if ( ! $property->readOnlyValue ) { // phpcs:ignore WordPress.NamingConventions
					if ( $property->favorited ) {
						// phpcs:ignore WordPress.NamingConventions
						$display_order = $property->favoritedOrder > -1 ? $property->favoritedOrder : 100;
					} else {
						// phpcs:ignore WordPress.NamingConventions
						$display_order = ( $property->displayOrder > -1 ? $property->displayOrder : 100 ) + 100;
					}
					$order[ $k2 ] = $display_order;
				}
			}
			asort( $order );

			$ordered = array();
			foreach ( $order as $k2 => $label ) {
				$ordered[ $k2 ] = $properties[ $k ]->properties[ $k2 ];
			}
			$properties[ $k ]->properties = $ordered;
			if ( empty( $properties[ $k ]->properties ) ) {
				// Prevent empty headings.
				unset( $properties[ $k ]->properties );
			}
		}

		return $properties;
	}

	/**
	 * Get User defined Custom fields from HubSpot
	 *
	 * @since  1.0
	 * @since  2.0 Removed is_private_app() method.
	 *
	 * @return array|false Custom Fields.
	 */
	public function fetch_properties() {
		$properties = get_transient( $this->property_cache );
		if ( $properties || ! $this->has_credentials() ) {
			return $properties;
		}

		$url  = $this->hubspot_url( '/groups?includeProperties=true', 'v2' );
		$args = array( 'headers' => $this->get_private_app_headers() );

		$response   = wp_remote_retrieve_body( wp_remote_get( $url, $args ) );
		$properties = json_decode( $response );

		if ( ! is_wp_error( $properties ) && is_array( $properties ) ) {
			set_transient( $this->property_cache, $properties, DAY_IN_SECONDS );
		} else {
			if ( empty( $properties ) ) {
				$properties = esc_html__( 'Please check you have an active version of cURL installed on your server.', 'formidable-hubspot' );
			}
			$this->show_error( $properties );
			$properties = false;
		}

		return $properties;
	}

	/**
	 * Add user to Hubspot
	 *
	 * @since  1.0
	 *
	 * @param array      $subscriber
	 * @param string|int $list_id
	 * @param mixed      $atts
	 * @return void
	 */
	public function subscribe_to_list( $subscriber, $list_id, $atts = array() ) {
		if ( ! array( $atts ) ) {
			// For reverse compatibility.
			$atts = array(
				'email_id' => $atts,
			);
		}
		$this->set_entry( $atts );
		$body = json_encode( $subscriber );

		$contact_id = $this->create_contact( $body, $atts['email_id'] );

		$this->add_contact_to_list( $contact_id, $list_id );
	}

	/**
	 * Initialize the class variables.
	 *
	 * @param array $atts - The action and entry object passed to the class.
	 * @since 1.07
	 */
	private function set_entry( $atts ) {
		if ( isset( $atts['entry'] ) ) {
			$this->entry_id = $atts['entry']->id;
		}

		if ( isset( $atts['action'] ) ) {
			$this->action = $atts['action'];
		}
	}

	/**
	 * Create a contact or update if it already exists.
	 *
	 * @param string $body - The JSON data to send.
	 * @param string $email_field - The subscriber email to check.
	 */
	private function create_contact( $body, $email_field ) {
		$contact = $this->remote_request( '/contact/createOrUpdate/email/' . $email_field, compact( 'body' ) );
		$error   = is_object( $contact ) && isset( $contact->status ) && 'error' !== $contact->status;

		if ( ! is_wp_error( $contact ) && is_object( $contact ) && ! $error ) {
			$contact_id = isset( $contact->vid ) ? $contact->vid : false;
			$this->set_hubspot_object_to_item_meta( $contact_id );
		} else {
			$contact_id = false;
		}

		return $contact_id;
	}

	/**
	 * Add entry meta with contact id of hubSpot.
	 *
	 * @since 2.0
	 *
	 * @param string|bool $contact_id hubspot contact id.
	 * @return void
	 */
	private function set_hubspot_object_to_item_meta( $contact_id ) {
		// This is strict check if $this->action is not being set by set_entry.
		if ( ! ( $this->action instanceof WP_Post ) ) {
			return;
		}

		$post_content = maybe_unserialize( $this->action->post_content );

		// Check existence of field id.
		if ( empty( $post_content['hubspot_hidden_field'] ) ) {
			return;
		}

		FrmEntryMeta::add_entry_meta( $this->entry_id, $post_content['hubspot_hidden_field'], '', $contact_id );
	}

	private function add_contact_to_list( $contact_id, $list_id ) {
		if ( ! empty( $contact_id ) && ! empty( $list_id ) ) {
			$contact = array(
				'vids' => array( $contact_id ),
			);
			$body    = json_encode( $contact );

			$this->remote_request( '/lists/' . $list_id . '/add', compact( 'body' ) );
		}
	}

	private function remote_request( $endpoint, $args = array() ) {
		$request = $this->prepare_request( $args );
		$url     = $this->hubspot_url( $endpoint );
		$result  = wp_remote_request( $url, $request );

		$this->log_results(
			array(
				'response' => $result,
				'headers'  => $request['headers'],
				'body'     => json_encode( $request ),
				'url'      => $url,
			)
		);

		// Handle response.
		if ( is_wp_error( $result ) ) {
			$response = $result->get_error_message();
		} else {
			$response = json_decode( wp_remote_retrieve_body( $result ) );
		}

		return $response;
	}

	private function prepare_request( $args ) {
		$request = array(
			'method'  => isset( $args['method'] ) ? $args['method'] : 'POST',
			'headers' => array(
				'content-type' => 'application/json',
			),
		);

		$request['headers']['Authorization'] = 'Bearer ' . $this->private_app_access_token;

		if ( isset( $args['body'] ) ) {
			$request['body'] = $args['body'];
		}

		return $request;
	}

	private function hubspot_url( $endpoint, $version = 'v1' ) {
		$url = 'https://api.hubapi.com/contacts/' . $version . $endpoint;

		return $url;
	}

	/**
	 * Print the error respone on the page.
	 *
	 * @param mixed $response
	 *
	 * @since 1.07
	 */
	private function show_error( $response ) {
		if ( is_array( $response ) && isset( $response['success'] ) && 1 === $response['success'] ) {
			return;
		}

		$message = ( is_object( $response ) && isset( $response->message ) ) ? $response->message : $response;
		if ( ! is_string( $message ) ) {
			if ( isset( $response->lists ) && ! $response->lists ) {
				$message = __( 'No HubSpot lists found.', 'formidable-hubspot' );
			} else {
				$message = print_r( $message, true );
			}
		}

		if ( is_string( $message ) ) {
			echo '<div class="frm_error frm_error_style">' . esc_html( $message ) . '</div>';
		}
	}

	/**
	 * Send the API request and response to the Formidable Logs plugin.
	 *
	 * @param array $atts
	 *
	 * @since 1.07
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

		$log = new FrmLog();
		$log->add(
			array(
				'title'   => __( 'HubSpot:', 'formidable-hubspot' ) . ' ' . $this->action->post_title,
				'content' => (array) $body,
				'fields'  => array(
					'entry'   => $this->entry_id,
					'action'  => $this->action->ID,
					'code'    => isset( $content['code'] ) ? $content['code'] : '',
					'url'     => $atts['url'],
					'message' => $message,
					'request' => $atts['body'],
				),
			)
		);
	}

	/**
	 * After the API response is received, determine if it's the response
	 * needed and expected.
	 *
	 * @param mixed $response
	 * @param mixed $body
	 *
	 * @since 1.07
	 */
	private function process_response( $response, $body ) {
		$processed = array(
			'message' => '',
			'code'    => 'FAIL',
		);

		if ( is_wp_error( $response ) ) {
			$processed['message'] = $response->get_error_message();
		} elseif ( 'error' === $body || is_wp_error( $body ) ) {
			$processed['message'] = __( 'You had an HTTP connection error', 'formidable-hubspot' );
		} elseif ( isset( $response['response'] ) && isset( $response['response']['code'] ) ) {
			$processed['code']    = $response['response']['code'];
			$processed['message'] = $response['body'];
		}

		return $processed;
	}

	/**
	 * Convert an array to a labeled list for display.
	 *
	 * @param array  $array
	 * @param string $list
	 *
	 * @since 1.07
	 */
	private function array_to_list( $array, &$list ) {
		foreach ( $array as $k => $v ) {
			$list .= "\r\n" . $k . ': ' . $v;
		}
	}
}
