<?php
/**
 * Communicate with ConvertKit
 */
class FrmConvertKitAPI {

	const GET = 'GET';
	const POST = 'POST';
	const PUT = 'PUT';
	const DELETE = 'DELETE';

	/**
	 * Track entry object.
	 *
	 * @var stdClass|null
	 */
	private $entry;

	/**
	 * Track action object.
	 *
	 * @var stdClass
	 */
	private $action;

	/**
	 * API URL.
	 *
	 * @var string
	 */
	private $api_url = 'https://api.convertkit.com/v3/';

	/**
	 * The contact id is saved in this entry meta.
	 *
	 * @var string $meta_name
	 */
	public $meta_name = 'convertkit_id';

	/**
	 * Get forms from ConvertKit.
	 *
	 * @return array|mixed Array of forms or API response.
	 */
	public function get_forms() {
		$response = $this->make_request(
			array(
				'store' => 'forms',
				'url'   => 'forms',
			)
		);

		if ( is_object( $response ) && isset( $response->forms ) ) {
			return $response->forms;
		}

		return $response;
	}

	/**
	 * Gets sequences from ConvertKit.
	 *
	 * @return array|mixed Array of sequences or API response.
	 */
	public function get_sequences() {
		$response = $this->make_request(
			array(
				'store' => 'sequences',
				'url'   => 'sequences',
			)
		);

		if ( is_object( $response ) && isset( $response->courses ) ) {
			return $response->courses;
		}

		return $response;
	}

	/**
	 * Gets ConvertKit subscriber.
	 *
	 * @param string $email Email address.
	 * @return object|mixed Subscriber data or API response.
	 */
	public function get_subscriber( $email ) {
		$response = $this->make_request(
			array(
				'url'        => 'subscribers',
				'url_params' => array(
					'email_address' => $email,
				),
			)
		);

		if ( is_object( $response ) && isset( $response->subscribers ) ) {
			return $response->subscribers[0];
		}

		return $response;
	}

	/**
	 * Gets ConvertKit tags.
	 *
	 * @return array|mixed Array of tags or API response.
	 */
	public function get_tags() {
		$response = $this->make_request(
			array(
				'url' => 'tags',
			)
		);

		if ( is_object( $response ) && isset( $response->tags ) ) {
			return $response->tags;
		}

		return $response;
	}

	/**
	 * Creates a tag.
	 *
	 * @param string $name Tag name.
	 * @return object|mixed Tag data or API response.
	 */
	public function create_tag( $name ) {
		return $this->make_request(
			array(
				'url'     => 'tags',
				'body'    => array(
					'tag' => compact( 'name' ),
				),
				'headers' => array(
					'Content-Type: application/json',
				),
			),
			self::POST
		);
	}

	/**
	 * Removes tag from subscriber.
	 *
	 * @param int    $tag_id Tag ID.
	 * @param string $email  Email address.
	 * @return void
	 */
	public function remove_tag_from_subscriber( $tag_id, $email ) {
		$this->make_request(
			array(
				'url'  => 'tags/' . $tag_id . '/unsubscribe',
				'body' => compact( 'email' ),
			),
			self::POST
		);
	}

	/**
	 * Adds subscriber to list (form or sequence).
	 *
	 * @param string $endpoint   API endpoint.
	 * @param int    $list_id    List ID.
	 * @param array  $subscriber Subscriber data.
	 * @param array  $args       {
	 *     Args.
	 *
	 *     @type object $entry  Entry object.
	 *     @type object $action Action object.
	 * }
	 *
	 * @return object|mixed Subscription object or API response if failed.
	 */
	public function add_subscriber_to_list( $endpoint, $list_id, $subscriber, $args ) {
		if ( empty( $subscriber['email'] ) || ! is_email( $subscriber['email'] ) ) {
			return __( 'Subscriber email is missing', 'frm-convertkit' );
		}

		$this->entry  = $args['entry'];
		$this->action = $args['action'];

		if ( ! empty( $subscriber['tags'] ) && is_array( $subscriber['tags'] ) ) {
			$subscriber['tags'] = wp_json_encode( $subscriber['tags'] );
		}

		$response = $this->make_request(
			array(
				'url'  => $endpoint . '/' . $list_id . '/subscribe',
				'body' => $subscriber,
			),
			self::POST
		);

		if ( is_object( $response ) && ! empty( $response->subscription ) ) {
			$this->save_subscriber_id( $response->subscription->subscriber->id );
		}

		return $response;
	}

	/**
	 * Gets ConvertKit custom fields.
	 *
	 * @return array|mixed Return array of custom fields, or API response if failed.
	 */
	public function get_custom_fields() {
		$response = $this->make_request(
			array(
				'store' => 'custom_fields',
				'url'   => 'custom_fields',
			)
		);

		if ( is_object( $response ) && isset( $response->custom_fields ) ) {
			return $response->custom_fields;
		}

		return $response;
	}

	/**
	 * Unsubscribes.
	 *
	 * @param string $email Email address.
	 * @return void
	 */
	public function unsubscribe( $email ) {
		$this->make_request(
			array(
				'url'  => 'unsubscribe',
				'body' => compact( 'email' ),
			),
			self::PUT
		);
	}

	/**
	 * Save the subscriber to the entry so we can use it for better sync.
	 *
	 * @param int $subscriber_id Subscriber ID.
	 * @return void
	 */
	private function save_subscriber_id( $subscriber_id ) {
		if ( ! $this->entry ) {
			return;
		}

		$existing_meta = FrmDb::get_row(
			'frm_item_metas',
			array(
				'item_id'         => $this->entry->id,
				'field_id'        => 0,
				'meta_value LIKE' => 'a:1:{s:' . strlen( $this->meta_name ) . ':"' . $this->meta_name . '";',
			)
		);

		if ( ! $existing_meta ) {
			FrmEntryMeta::add_entry_meta( $this->entry->id, 0, '', serialize( array( $this->meta_name => $subscriber_id ) ) );
			return;
		}

		$meta_value = FrmAppHelper::maybe_unserialize_array( $existing_meta->meta_value );
		if ( is_array( $meta_value ) && isset( $meta_value[ $this->meta_name ] ) && intval( $meta_value[ $this->meta_name ] ) === intval( $subscriber_id ) ) {
			// This subscriber ID is saved before, don't need to update.
			return;
		}

		global $wpdb;
		$wpdb->update(
			$wpdb->prefix . 'frm_item_metas',
			array(
				'meta_value' => serialize( array( $this->meta_name => intval( $subscriber_id ) ) ),
			),
			array(
				'id' => $existing_meta->id,
			)
		);
	}

	/**
	 * Send the API request now.
	 *
	 * @param array  $args {
	 *     Request args.
	 *
	 *     @type string $store      Transient name.
	 *     @type string $url        API action to append to URL.
	 *     @type array  $body       Request body.
	 *     @type array  $url_params URL params.
	 * }
	 * @param string $method Request method.
	 * @return mixed Return the data or error string.
	 */
	private function make_request( $args, $method = 'GET' ) {
		if ( isset( $args['store'] ) ) {
			$response = get_transient( 'frm-convertkit-' . $args['store'] );
			if ( ! empty( $response ) ) {
				return $response;
			}
		}

		if ( empty( $args['url'] ) ) {
			return __( 'The API URL is missing', 'frm-convertkit' );
		}

		$settings   = new FrmConvertKitSettings();
		$api_secret = isset( $settings->settings->api_secret ) ? $settings->settings->api_secret : '';

		if ( ! $api_secret ) {
			return __( 'The API secret is missing', 'frm-convertkit' );
		}

		// Build the URL.
		$url = $this->api_url . $args['url'];
		$url = add_query_arg( 'api_secret', $api_secret, $url );

		if ( ! empty( $args['url_params'] ) ) {
			$url = add_query_arg( $args['url_params'], $url );
		}

		// Build request body.
		$api_args = array(
			'method'  => $method,
			'headers' => isset( $args['headers'] ) ? $args['headers'] : array(),
		);

		if ( isset( $args['body'] ) ) {
			$api_args['body'] = $args['body'];
		}

		$result = wp_remote_request( $url, $api_args );

		$this->log_results(
			array(
				'response' => $result,
				'headers'  => $api_args['headers'],
				'body'     => json_encode( $api_args ),
				'url'      => $url,
			)
		);

		// Handle response.
		if ( is_wp_error( $result ) ) {
			return $result->get_error_message();
		}

		$response = json_decode( wp_remote_retrieve_body( $result ) );
		if ( is_null( $response ) ) {
			if ( is_array( $result ) && ! empty( $result['response']['message'] ) ) {
				$response = $result['response']['message'];
			}
		} elseif ( is_object( $response ) && isset( $response->error ) && isset( $response->message ) ) {
			return $response->error . ': ' . $response->message;
		} elseif ( isset( $args['store'] ) ) {
			set_transient( 'frm-convertkit-' . $args['store'], $response, 60 * 60 * 60 );
		}

		return $response;
	}

	/**
	 * Logs result.
	 *
	 * @param array $atts See {@see FrmConvertKitAPI::make_request()}.
	 * @return void
	 */
	private function log_results( $atts ) {
		if ( ! class_exists( 'FrmLog' ) || empty( $this->entry ) ) {
			return;
		}

		$entry = $this->entry;

		$body    = wp_remote_retrieve_body( $atts['response'] );
		$content = $this->process_response( $atts['response'], $body );
		$message = isset( $content['message'] ) ? $content['message'] : '';

		$headers = '';
		$this->array_to_list( $atts['headers'], $headers );

		$log = new FrmLog();
		$log->add(
			array(
				'title'   => __( 'ConvertKit:', 'frm-convertkit' ) . ' ' . $this->action->post_title,
				'content' => (array) $body,
				'fields'  => array(
					'entry'   => $entry->id,
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
	 * Processes response for logging.
	 *
	 * @param array|WP_Error        $response API response.
	 * @param array|string|WP_Error $body     Response body.
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
			$processed['message'] = __( 'You had an HTTP connection error', 'frm-convertkit' );
		} elseif ( isset( $response['response']['code'] ) ) {
			$processed['code']    = $response['response']['code'];
			$processed['message'] = $response['body'];
		}

		return $processed;
	}

	/**
	 * Converts array to logged string.
	 *
	 * @param array  $array Array.
	 * @param string $list  Logged string.
	 * @return void
	 */
	private function array_to_list( $array, &$list ) {
		foreach ( $array as $k => $v ) {
			$list .= "\r\n" . $k . ': ' . $v;
		}
	}

	/**
	 * Clears API cache.
	 *
	 * @return void
	 */
	public static function clear_cache() {
		delete_transient( 'frm-convertkit-custom_fields' );
		delete_transient( 'frm-convertkit-forms' );
		delete_transient( 'frm-convertkit-sequences' );
	}
}
