<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FrmAIChatGPT {

	/**
	 * License key.
	 *
	 * @var string
	 */
	private static $license = '';

	/**
	 * Format the response as json.
	 *
	 * @param array $data The unsanitized data sent from the field js.
	 * @return void
	 */
	public static function get_json_response( $data ) {
		$response = self::get_response( $data );
		if ( ! empty( $response['error'] ) ) {
			wp_send_json_error( $response['error'] );
		} else {
			wp_send_json_success( $response['success'] );
		}
	}

	/**
	 * Send the API request off and return the response.
	 *
	 * @param array $data The unsanitized data sent from the field js.
	 * @return array
	 */
	public static function get_response( $data ) {
		self::set_license();
		$data = self::prepare_request_data( $data );

		if ( empty( self::$license ) || empty( $data ) ) {
			return array(
				'error' => 'Invalid request.',
			);
		}

		$response = self::send_request( $data );

		// Check if the request was successful.
		if ( ! is_array( $response ) && is_wp_error( $response ) ) {
			// Handle error.
			return array(
				'error' => $response->get_error_message(),
			);
		}

		// Retrieve the answer from the response.
		$answer = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! is_object( $answer ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			if ( is_object( $answer ) && ! empty( $answer->message ) ) {
				$message = $answer->message;
			} else {
				$message = wp_remote_retrieve_response_message( $response );
			}

			return array(
				'error' => sanitize_text_field( $message ),
			);
		}

		return self::interpret_answer( $answer );
	}

	/**
	 * Prepare the data to be sent in the request body.
	 *
	 * @param array $params The unsanitized values sent from the field js.
	 * @return array
	 */
	private static function prepare_request_data( $params ) {
		// Retrieve the value of the fields to watch.
		if ( empty( $params ) || empty( $params['question'] ) ) {
			return array();
		}

		$question = sanitize_text_field( $params['question'] );
		if ( empty( $question ) ) {
			return array();
		}

		$data = array(
			'prompt'      => sanitize_text_field( $params['prompt'] ),
			'question'    => $question,
			'temperature' => 0.5,
		);

		/**
		 * Filter the data sent to the API.
		 *
		 * @since 1.0
		 */
		return (array) apply_filters( 'frm_ai_data', $data );
	}

	/**
	 * Send the API request off and return the response.
	 *
	 * @param array $data The data to send to the API.
	 * @return array|WP_Error
	 */
	private static function send_request( $data ) {
		$api_url  = 'https://api.strategy11.com';
		$endpoint = $api_url . '/wp-json/s11connect/v1/chatgpt/';
		$endpoint .= '?l=' . urlencode( base64_encode( self::$license ) );

		// Send the request to the API endpoint.
		return wp_remote_post(
			$endpoint,
			array(
				'headers'    => array(
					'Content-Type'  => 'application/json',
				),
				'body'       => json_encode( $data ),
				'timeout'    => 450,
				'user-agent' => self::agent() . '; ' . get_bloginfo( 'url' ),
			)
		);
	}

	/**
	 * Get the answer into a readable format.
	 *
	 * @param object $answer The answer from the API.
	 * @return array
	 */
	private static function interpret_answer( $answer ) {
		if ( empty( $answer->message ) ) {
			return array(
				'error' => 'No answer found.',
			);
		}

		$ai_answer = sanitize_textarea_field( $answer->message );
		$ai_answer = array_filter( explode( "\n", $ai_answer ) );
		$ai_answer = array_values( $ai_answer ); // Reset array keys.

		return array(
			'success' => $ai_answer,
		);
	}

	/**
	 * Get the license key from Formidable.
	 *
	 * @return void
	 */
	private static function set_license() {
		if ( FrmAppHelper::pro_is_installed() && empty( self::$license ) ) {
			$updater = FrmProAppHelper::get_updater();
			self::$license = $updater->license;
		}
	}

	/**
	 * Get the user agent for the request.
	 *
	 * @return string
	 */
	private static function agent() {
		$agent = 'formidable/' . FrmAppHelper::plugin_version();
		if ( class_exists( 'FrmProDb' ) ) {
			$agent = 'formidable-pro/' . FrmProDb::$plug_version;
		}
		return $agent;
	}
}
