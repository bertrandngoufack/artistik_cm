<?php
/**
 * Interface for abstracting remote requests.
 *
 * @package formidable-hubspot
 */

/**
 * FrmHubSpotRequest Interface.
 *
 * @since 1.0
 */
interface FrmHubSpotRequest {

	/**
	 * Do a get request to retrieve the contents of a remote URL.
	 *
	 * @since 1.0
	 *
	 * @param string $url     URL.
	 * @param array  $args Optional.
	 *
	 * @return void|object|array Response for the executed request.
	 * @throws Exception Throwable exception.
	 */
	public function get( $url, $args = array());

	/**
	 * Do a remote request to retrieve the contents of a remote URL.
	 *
	 * @since 1.0
	 *
	 * @param string $url     URL.
	 * @param array  $args Optional.
	 *
	 * @return void|object|array Response for the executed request.
	 * @throws Exception Throwable exception.
	 */
	public function post( $url, $args = array());
}
