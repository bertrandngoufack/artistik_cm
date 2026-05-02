<?php
/**
 * Communicate with GetResponse.
 */
class FrmGetResponseAPI {

	protected $url = 'https://api.getresponse.com/v3';
	protected $api_key;
	protected $log_success = false;

	public function __construct() {
		$settings      = new FrmGetResponseSettings();
		$this->api_key = $settings->settings->api_key;
	}

	/**
	 * Get Campaigns from GetResponse
	 *
	 * @since  1.0
	 * @return string|array Error string or Campaigns array
	 */
	public function fetch_campaigns() {
		$this->log_success = false;
		return $this->remote_request( '/campaigns?fields=campaignId,name', array( 'method' => 'GET' ) );
	}

	/**
	 * Get User defined Custom fields from GetResponse
	 *
	 * @since  1.0
	 * @return string|array Error string or Custom Fields array
	 */
	public function fetch_custom_fields() {
		$this->log_success = false;
		return $this->remote_request( '/custom-fields?fields=customFieldId,name', array( 'method' => 'GET' ) );
	}

	/**
	 * Add user to getResponse
	 *
	 * @since  1.0
	 *
	 * @param array $subscriber - The information about when and what to subscribe.
	 * @return string Error string or Status Code
	 */
	public function subscribe_to_campaign( $subscriber ) {
		$this->log_success = true;
		$body = json_encode( $subscriber );
		return $this->remote_request( '/contacts', compact( 'body' ) );
	}

	private function remote_request( $endpoint, $args = array() ) {
		$request  = $this->prepare_request( $args );
		$url      = $this->url . $endpoint;
		$result   = wp_remote_request( $url, $request );

		$this->log_results(
			array(
				'response' => $result,
				'headers'  => $request['headers'],
				'body'     => isset( $request['body'] ) ? json_encode( $request['body'] ) : '',
				'url'      => $url,
			)
		);

		// Handle response.
		if ( is_wp_error( $result ) ) {
			$response = $result->get_error_message();
		} else {
			$response = json_decode( wp_remote_retrieve_body( $result ) );
			if ( isset( $response->code ) && isset( $response->message ) ) {
				$response = $response->message;
			}
		}

		return $response;
	}

	private function prepare_request( $args ) {
		$request = array(
			'method'  => isset( $args['method'] ) ? $args['method'] : 'POST',
			'headers' => array(
				'content-type' => 'application/json',
				'X-Auth-Token' => 'api-key ' . $this->api_key,
			),
		);
		if ( isset( $args['body'] ) ) {
			$request['body'] = $args['body'];
		}

		return $request;
	}

	/**
	 * Send the API request and response to the Formidable Logs plugin.
	 *
	 * @since 1.04
	 *
	 * @param array $atts - The request and response for logging.
	 */
	private function log_results( $atts ) {
		if ( ! class_exists( 'FrmLog' ) ) {
			return;
		}

		$body    = wp_remote_retrieve_body( $atts['response'] );
		$content = $this->process_response( $atts['response'], $body );
		$message = isset( $content['message'] ) ? $content['message'] : '';
		$code    = isset( $content['code'] ) ? $content['code'] : '';

		if ( ! $this->log_success && $code == 200 ) {
			return;
		}

		$headers = '';
		$this->array_to_list( $atts['headers'], $headers );

		$log = new FrmLog();
		$log->add(
			array(
				'title'   => 'GetResponse',
				'content' => (array) $body,
				'fields'  => array(
					'entry'   => '',
					'action'  => '',
					'code'    => $code,
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
	 * @since 1.04
	 *
	 * @param mixed $response - The response from the API request.
	 * @param mixed $body - The body of the response.
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
	 * @param array  $array - The array of values to convert to a string.
	 * @param string $list - The string to add the array to.
	 */
	private function array_to_list( $array, &$list ) {
		foreach ( $array as $k => $v ) {
			$list .= "\r\n" . $k . ': ' . $v;
		}
	}
}
