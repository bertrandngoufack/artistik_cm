<?php

/**
 * Handles the API connections to Salesforce.
 *
 * @since 2.01
 */
class FrmSalesforceAPI {

	/**
	 * The endpoint to get Salesforce objects.
	 *
	 * @var string $services_url
	 */
	protected $services_url = '/services/data/v35.0/sobjects/';

	/**
	 * The endpoint to create or update a new item.
	 *
	 * @var string $query_url
	 */
	protected $query_url = '/services/data/v35.0/query/';

	protected $entry_id = 0;

	protected $action;

	protected $object_cache = 'frm-salesforce-objects';
	protected $field_cache  = 'frm-salesforce-object-fields';

	/**
	 * Add data to Salesforce Object
	 *
	 * @param array $data
	 * @param array $atts
	 * @since  2.01
	 * @author Aman Saini
	 */
	public function create_or_update_record( $data, $atts = array() ) {
		$settings = get_option( 'frm_salesforce_options' );
		$this->set_entry( $atts );

		if ( isset( $atts['record_id'] ) && ! empty( $atts['record_id'] ) ) {
			$response = $this->update_record( $atts, $data );
		} else {
			$response = $this->create_new_record( $atts['object_id'], $data );
		}
	}

	/**
	 * Initialize the class variables.
	 *
	 * @param array $atts - The action and entry object passed to the class.
	 */
	private function set_entry( $atts ) {
		if ( isset( $atts['entry'] ) ) {
			$this->entry_id  = $atts['entry']->id;
			$this->action = $atts['action'];
		}
	}

	/**
	 * Add data to Salesforce Object
	 *
	 * @param int   $object_id
	 * @param array $data
	 * @param array $atts
	 * @since  2.03
	 * @return mixed
	 */
	public function create_new_record( $object_id, $data, $atts = array() ) {
		if ( ! empty( $atts ) ) {
			// For reverse compatibility.
			$this->set_entry( $atts );
		}
		return $this->get_response( $this->services_url . $object_id . '/', 'POST', $data );
	}

	/**
	 * Update Salesforce Object
	 *
	 * @param array $atts
	 * @param array $data
	 * @since  2.03
	 * @return mixed
	 */
	private function update_record( $atts, $data ) {
		return $this->get_response( $this->services_url . $atts['object_id'] . '/' . $atts['record_id'], 'PATCH', $data );
	}

	/**
	 * Get the lead/item id from Salesforce in order to update it.
	 *
	 * @param int   $object_id
	 * @param array $atts
	 * @since  2.03
	 * @return bool|string
	 */
	public function get_record_id_to_update( $object_id, $atts ) {
		$where = $atts['field_id'] . "='" . $atts['field_value'] . "'";
		$query = "SELECT+Id+from+$object_id+where+$where";
		$response = $this->get_response( $this->query_url, 'GET', array( 'q' => $query ) );
		if ( is_object( $response ) && $response->totalSize > 0 ) {  // phpcs:ignore WordPress.NamingConventions
			return $response->records[0]->Id;
		}
		return false;
	}

	/**
	 * Get User defined Custom fields from Salesforce
	 *
	 * @param int $object_id - The id of the Salesforce object.
	 * @since  2.01
	 * @author Aman Saini
	 * @return array Custom Fields Array.
	 */
	public function fetch_object_fields( $object_id ) {
		$cached_fields = get_transient( $this->field_cache );
		if ( false === $cached_fields ) {
			$cached_fields = array();
		}

		if ( ! empty( $cached_fields[ $object_id ] ) ) {
			return $cached_fields[ $object_id ];
		}

		$fields = array();
		$response = $this->get_response( $this->services_url . $object_id . '/describe/', 'GET' );

		if ( is_object( $response ) ) {
			foreach ( $response->fields as $fieldobj ) {
				if ( $fieldobj->updateable ) {
					$picklist = '';
					if ( is_array( $fieldobj->picklistValues ) ) { // phpcs:ignore WordPress.NamingConventions
						$picklist = implode( ', ', wp_list_pluck( $fieldobj->picklistValues, 'value' ) ); // phpcs:ignore WordPress.NamingConventions
					}
					$fields[] = array(
						'label' => $fieldobj->label,
						'name' => $fieldobj->name,
						'type' => $fieldobj->type,
						'picklistValues' => $picklist,
						'required' => ! $fieldobj->nillable,
					);

				}
			}

			$cached_fields[ $object_id ] = $fields;
			set_transient( $this->field_cache, $cached_fields, DAY_IN_SECONDS );
		} else {
			$this->show_error( $response );
		}

		return $fields;
	}

	/**
	 * Clear the cached API requests
	 *
	 * @since 2.04
	 */
	public function clear_cache() {
		delete_transient( $this->object_cache );
		delete_transient( $this->field_cache );
	}

	/**
	 * Get User defined Custom fields from Salesforce.
	 *
	 * @since  2.01
	 * @author Aman Saini
	 * @return array Custom Fields Array.
	 */
	public function fetch_custom_objects() {
		$cached_objects = get_transient( $this->object_cache );
		if ( ! empty( $cached_objects ) ) {
			$cached_objects = array_filter( $cached_objects );
			if ( ! empty( $cached_objects ) ) {
				return $cached_objects;
			}
		}

		$objects = array();
		$response = $this->get_response( $this->services_url, 'GET' );

		if ( is_object( $response ) ) {
			foreach ( $response->sobjects as $sobjects ) {
				if ( $sobjects->updateable ) {
					$objects[] = array(
						'label' => $sobjects->label,
						'name' => $sobjects->name,
					);
				}
			}
			set_transient( $this->object_cache, $objects, DAY_IN_SECONDS );
		} else {
			$this->show_error( $response );
		}

		return $objects;
	}

	private function get_response( $url, $method, $params = array() ) {
		$response = $this->make_request( false, $method, $url, $params );
		if ( is_array( $response ) && isset( $response[0]->errorCode ) && 'INVALID_SESSION_ID' == $response[0]->errorCode ) {
			$response = $this->make_request( true, $method, $url, $params );
		}
		return $response;
	}

	/**
	 * Send the API request to Salesforce.
	 *
	 * @param string $newtoken - The authentication token.
	 * @param string $method - GET, POST, ...
	 * @param string $uri - The url for the API request.
	 * @param array  $params - Extra parameters to include in the request URL.
	 */
	private function make_request( $newtoken, $method, $uri, $params ) {
		$settings     = get_option( 'formidable_salesforce_auth' );
		$instance_url = empty( $settings['instance_url'] ) ? home_url() : $settings['instance_url'];

		$url = $instance_url . $uri;

		if ( 'GET' === $method ) {
			$querystring = urldecode( http_build_query( $params ) );
			// Append a query string to the url.
			$url = $url . '?' . $querystring;

			// unset params on GET.
			$params = array();
		}

		$token = FrmSalesforceAuth::get_access_token( $newtoken );
		if ( '' === $token ) {
			return 'Unable to call Salesforce with an access token. Please set up Salesforce in Global Settings.';
		}

		// Make the request.
		$req_args = array(
			'method'    => $method,
			'headers'   => array(
				'content-type'  => 'application/json',
				'Authorization' => 'Bearer ' . $token,
			),
		);

		if ( ! empty( $params ) ) {
			$req_args['body'] = json_encode( $params );
		}

		$result = wp_remote_request( $url, $req_args );

		$this->log_results(
			array(
				'response' => $result,
				'headers'  => $req_args['headers'],
				'body'     => json_encode( $params ),
				'url'      => $url,
			)
		);

		// Handle response.
		if ( is_wp_error( $result ) ) {
			$response = $result->get_error_message();
		} else {
			// No error.
			$response = json_decode( wp_remote_retrieve_body( $result ) );
		}

		return $response;
	}

	/**
	 * Print the error respone on the page.
	 *
	 * @param mixed $response
	 */
	private function show_error( $response ) {
		if ( is_array( $response ) && isset( $response['success'] ) && 1 === $response['success'] ) {
			return;
		}

		echo '<pre>';
		echo esc_html( print_r( $response, 1 ) );
		echo '</pre>';
	}

	/**
	 * Send the API request and response to the Formidable Logs plugin.
	 *
	 * @param array $atts
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
				'title'   => __( 'Salesforce:', 'formidable-salesforce' ) . ' ' . $this->action->post_title,
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
		} elseif ( isset( $response['response'] ) && isset( $response['response']['code'] ) ) {
			$processed['code'] = $response['response']['code'];
			$processed['message'] = $response['body'];
		}

		return $processed;
	}

	/**
	 * Convert an array to a labeled list for display.
	 *
	 * @param array  $array
	 * @param string $list
	 */
	private function array_to_list( $array, &$list ) {
		foreach ( $array as $k => $v ) {
			$list .= "\r\n" . $k . ': ' . $v;
		}
	}
}
