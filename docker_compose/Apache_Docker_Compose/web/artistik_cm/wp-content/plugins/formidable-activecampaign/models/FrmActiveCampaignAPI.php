<?php
/**
 * Communicate with Active Campaign
 */
class FrmActiveCampaignAPI {

	private $entry_id;
	private $action;

	/**
	 * The contact id is saved in this entry meta.
	 *
	 * @var string $meta_name
	 */
	public $meta_name = 'activecampaign_id';

	/**
	 * Get lists from ActiveCampaign
	 *
	 * @since  1.03
	 * @return array|false - list of campaigns
	 */
	public function get_lists() {
		return $this->make_request(
			array(
				'store' => 'lists',
				'url'   => 'list_list&ids=all',
			)
		);
	}

	/**
	 * Get User defined Custom fields from ActiveCampaign
	 *
	 * @since  1.03
	 * @return Custom Fields Array
	 */
	public function get_custom_fields() {
		return $this->make_request(
			array(
				'store' => 'custom-fields',
				'url'   => 'list_field_view&ids=all',
			)
		);
	}

	/**
	 * Get list of from ActiveCampaign
	 *
	 * @since  1.03
	 * @return Forms Array
	 */
	public function get_forms() {
		return $this->make_request(
			array(
				'store' => 'forms',
				'url'   => 'form_getforms',
			)
		);
	}

	/**
	 * Add user to ActiveCampaign
	 *
	 * @since  1.03
	 *
	 * @param array $subscriber
	 * @param array $atts
	 */
	public function subscribe( $subscriber, $atts ) {
		$this->set_entry( $atts );
		$atts['id'] = isset( $subscriber['id'] ) ? $subscriber['id'] : '';

		// Check if the contact exists.
		$contact = $this->make_request(
			array(
				'url'  => 'contact_view',
				'body' => $subscriber,
			),
			'POST'
		);

		// If exists, maybe keep the existing list status (the resubscribe checkbox will overwrite status).
		$this->filter_contact_lists( $contact, $subscriber );

		// Update or create contact.
		$response = $this->make_request(
			array(
				'url'  => 'contact_sync',
				'body' => $subscriber,
			),
			'POST'
		);

		// Save the subscriber id with the entry.
		$this->save_subscriber_id( $response, $atts );
	}

	private function set_entry( $atts ) {
		if ( isset( $atts['entry'] ) ) {
			$this->entry_id  = $atts['entry']->id;
			$this->action = $atts['action'];
		}
	}

	/**
	 * Prevent resubscribing if contact is updated.
	 * Don't lose other lists contact is subscribed to.
	 *
	 * @since 1.06
	 *
	 * @param mixed $contact The response from getting a contact.
	 * @param array $new_info
	 */
	private function filter_contact_lists( $contact, &$new_info ) {
		$resubscribe = ! empty( $this->action->post_content['resubscribe'] );

		if ( ! is_object( $contact ) ) {
			if ( $resubscribe ) {
				foreach ( $new_info as $key => $value ) {
					if ( 0 === strpos( $key, 'p[' ) ) {
						$status_key              = str_replace( 'p[', 'status[', $key );
						$new_info[ $status_key ] = 1;
					}
				}
			}
			return;
		}

		if ( empty( $contact->lists ) ) {
			return;
		}

		$no_resubscribe = ! $resubscribe;

		foreach ( $contact->lists as $list ) {
			if ( isset( $new_info[ 'p[' . $list->listid . ']' ] ) ) {
				if ( $no_resubscribe ) {
					// Keep the current list status if no resubscribing.
					$new_info[ 'status[' . $list->listid . ']' ] = $list->status;
				}
				continue;
			}

			// Include all lists contact is subscribed to so they won't get wiped.
			$new_info[ 'p[' . $list->listid . ']' ]      = $list->listid;
			$new_info[ 'status[' . $list->listid . ']' ] = $list->status;
		}
	}

	/**
	 * Save the subscriber to the contact so we can use it for better sync.
	 *
	 * @since 1.06
	 *
	 * @param mixed $response
	 * @param array $atts
	 */
	private function save_subscriber_id( $response, $atts ) {
		if ( ! is_object( $response ) || empty( $response->result_code ) ) {
			return;
		}

		if ( $atts['id'] === $response->subscriber_id ) {
			// The id has already been added.
			return;
		}

		// Save the subscriber id with the entry.
		$new_meta = array(
			$this->meta_name => $response->subscriber_id,
		);
		$new_meta = maybe_serialize( $new_meta );

		if ( empty( $atts['entry']->ac_meta_id ) ) {
			FrmEntryMeta::add_entry_meta( $this->entry_id, 0, '', $new_meta );
		} else {
			// Update existing id.
			global $wpdb;
			$where = array( 'id' => $atts['entry']->ac_meta_id );
			$wpdb->update( $wpdb->prefix . 'frm_item_metas', array( 'meta_value' => $new_meta ), $where );
		}
	}

	/**
	 * Send the API request now.
	 *
	 * @param array  $args
	 *               $args[body] (optional).
	 *               $args[store] transient name.
	 *               $args[url] API action to append to url.
	 * @param string $method POST or GET.
	 */
	private function make_request( $args, $method = 'GET' ) {
		if ( isset( $args['store'] ) ) {
			$response = get_transient( 'frm-activecampaign-' . $args['store'] );
			if ( ! empty( $response ) ) {
				return $response;
			}
		}

		$settings = new FrmActiveCampaignSettings();
		$api_key = $settings->settings->api_key;
		$api_url = $settings->settings->api_url;

		if ( empty( $api_key ) || empty( $api_url ) ) {
			return __( 'The API key or URL is missing', 'frmactivecampaign' );
		}

		$url = $api_url . '/admin/api.php?api_key=' . $api_key . '&api_output=json&api_action=' . $args['url'];

		// Make the request.
		$api_args = array(
			'method'    => $method,
			'headers'   => array(
				// 'content-type'  => 'Content-Type: application/x-www-form-urlencoded',
			),
		);

		if ( isset( $args['body'] ) && ! empty( $args['body'] ) ) {
			$api_args['body'] = http_build_query( $args['body'], '', '&' );
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
			$response = $result->get_error_message();
		} else {
			$response = json_decode( wp_remote_retrieve_body( $result ) );
			if ( is_null( $response ) ) {
				if ( is_array( $result ) && ! empty( $result['response'] ) && ! empty( $result['response']['message'] ) ) {
					$response = $result['response']['message'];
				}
			} elseif ( 0 == $response->result_code ) {
				$response = $response->result_message;
			} elseif ( isset( $args['store'] ) ) {
				set_transient( 'frm-activecampaign-' . $args['store'], $response, 60 * 60 * 60 );
			}
		}

		return $response;
	}

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
				'title'   => __( 'ActiveCampaign:', 'frmactivecampaign' ) . ' ' . $this->action->post_title,
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
			$processed['code']    = $response['response']['code'];
			$processed['message'] = $response['body'];
		}

		return $processed;
	}

	private function array_to_list( $array, &$list ) {
		foreach ( $array as $k => $v ) {
			$list .= "\r\n" . $k . ': ' . $v;
		}
	}
}
