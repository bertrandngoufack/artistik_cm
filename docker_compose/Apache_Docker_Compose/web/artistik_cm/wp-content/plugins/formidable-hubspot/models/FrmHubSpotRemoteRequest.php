<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to access this file directly.' );
}

use \WP_Http as WP_Http;
use \WP_Error as WP_Error;
use \Traversable as Traversable;

/**
 * Remote requests.
 *
 * @since 2.0
 */
final class FrmHubSpotRemoteRequest implements FrmHubSpotRequest {

	/**
	 * Default timeout value to use in seconds.
	 *
	 * @since 2.0
	 *
	 * @var int
	 */
	const DEFAULT_TIMEOUT = 5;

	/**
	 * Default number of retry attempts to do.
	 *
	 * @since 2.0
	 *
	 * @var int
	 */
	const DEFAULT_RETRIES = 2;

	/**
	 * List of HTTP status codes that are worth retrying for.
	 *
	 * @since 2.0
	 *
	 * @var int[]
	 */
	const RETRYABLE_STATUS_CODES = array(
		WP_Http::REQUEST_TIMEOUT,
		WP_Http::LOCKED,
		WP_Http::TOO_MANY_REQUESTS,
		WP_Http::INTERNAL_SERVER_ERROR,
		WP_Http::SERVICE_UNAVAILABLE,
		WP_Http::GATEWAY_TIMEOUT,
	);

	/**
	 * Whether to verify SSL certificates or not.
	 *
	 * @since 2.0
	 *
	 * @var boolean
	 */
	private $ssl_verify;

	/**
	 * Timeout value to use in seconds.
	 *
	 * @since 2.0
	 *
	 * @var int
	 */
	private $timeout;

	/**
	 * Number of retry attempts to do for an error that is worth retrying.
	 *
	 * @since 2.0
	 *
	 * @var int
	 */
	private $retries;

	/**
	 * Instantiate a FrmHubSpotRemoteRequest object.
	 *
	 * @since 2.0
	 *
	 * @param bool $ssl_verify Whether to verify SSL certificates. Defaults to true.
	 * @param int  $timeout    Timeout value to use in seconds. Defaults to 10.
	 * @param int  $retries    Number of retry attempts to do if a status code was thrown that is worth.
	 *
	 * @return void
	 */
	public function __construct( $ssl_verify = true, $timeout = self::DEFAULT_TIMEOUT, $retries = self::DEFAULT_RETRIES ) {
		if ( ! is_int( $timeout ) || $timeout < 0 ) {
			$timeout = self::DEFAULT_TIMEOUT;
		}

		if ( ! is_int( $retries ) || $retries < 0 ) {
			$retries = self::DEFAULT_RETRIES;
		}

		$this->ssl_verify = $ssl_verify;
		$this->timeout    = $timeout;
		$this->retries    = $retries;
	}

	/**
	 * Do a get request to retrieve the contents of a remote URL.
	 *
	 * @since 2.0
	 *
	 * @param string $url     URL.
	 * @param array  $args Optional.
	 * @return void|object|array Response for the executed request.
	 * @throws Exception Throwable exception.
	 */
	public function get( $url, $args = array() ) {
		$defaults = array(
			'method'    => 'GET',
			'timeout'   => $this->timeout,
			'sslverify' => $this->ssl_verify,
			'headers'   => self::get_default_headers(),
		);

		$args = wp_parse_args( $args, $defaults );

		return $this->request( $url, $args );
	}

	/**
	 * Do a post requests to post the contents to a remote API.
	 *
	 * @since 2.0
	 *
	 * @param string $url     URL.
	 * @param array  $args Optional.
	 * @return void|object|array Response for the executed request.
	 * @throws Exception Throwable exception.
	 */
	public function post( $url, $args = array() ) {
		$defaults = array(
			'method'    => 'POST',
			'timeout'   => $this->timeout,
			'sslverify' => $this->ssl_verify,
			'headers'   => self::get_default_headers(),
		);

		$args = wp_parse_args( $args, $defaults );

		return $this->request( $url, $args );
	}

	/**
	 * Do a post requests to post the contents to a remote API.
	 *
	 * @since 2.0
	 *
	 * @param string $url     URL.
	 * @param array  $args Optional.
	 * @return void|object|array Response for the executed request.
	 * @throws Exception Throwable exception.
	 */
	public function delete( $url, $args = array() ) {
		$defaults = array(
			'method'    => 'DELETE',
			'timeout'   => $this->timeout,
			'sslverify' => $this->ssl_verify,
			'headers'   => self::get_default_headers(),
		);

		$args = wp_parse_args( $args, $defaults );

		return $this->request( $url, $args );
	}

	/**
	 * Do a remote request to retrieve the contents of a remote URL.
	 *
	 * @since 2.0
	 *
	 * @param string $url     URL.
	 * @param array  $args Optional.
	 * @return void|object|array Response for the executed request.
	 * @throws Exception Throwable exception.
	 */
	private function request( $url, $args = array() ) {
		$retries_left = $this->retries;

		self::try_to_extend_server_timeout();

		do {
			$response = wp_remote_request( $url, $args );

			$status = wp_remote_retrieve_response_code( $response );

			if ( $response instanceof WP_Error ) {
				throw new Exception( $response->get_error_message() );
			}

			if ( ! isset( $response['response']['code'] ) ) {
				$message = isset( $response['response']['message'] ) ? $response['response']['message'] : esc_html__( 'Unknown error', 'formidable-hubspot' );

				/* translators: %1$s: response message */
				throw new Exception( sprintf( esc_html__( 'Failed to fetch the contents: %1$s as it returned HTTP status 500.', 'formidable-hubspot' ), $message ) );
			}

			if ( $status < 200 || $status >= 300 ) {
				if ( ! $retries_left || in_array( $status, self::RETRYABLE_STATUS_CODES, true ) === false ) {
					/* translators: %1$s: url, %2$s: status code */
					$message = sprintf( esc_html__( 'Failed to fetch the contents from the URL %1$s as it returned HTTP status %2$s.', 'formidable-hubspot' ), $url, $status );

					throw new Exception( $message );
				}

				continue;
			}

			$headers = $response['headers'];
			if ( $headers instanceof Traversable ) {
				$headers = iterator_to_array( $headers );
			}

			if ( ! is_array( $headers ) ) {
				$headers = array();
			}

			return $response;
		} while ( $retries_left-- );
	}

	/**
	 * Get the default headers that all requests to HubSpot API's usually need.
	 *
	 * @return array An Associate array of HTTP headers to send with all HubSpot Api requests.
	 */
	private static function get_default_headers() {
		return array(
			'Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8',
		);
	}

	/**
	 * Try to make sure the server time limit exceeds the request time limit.
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	private function try_to_extend_server_timeout() {
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( $this->timeout + 10 );
		}
	}
}
