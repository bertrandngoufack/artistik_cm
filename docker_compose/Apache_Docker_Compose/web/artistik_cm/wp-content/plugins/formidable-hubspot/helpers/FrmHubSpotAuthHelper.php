<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Helper class for getting correct auth method.
 *
 * @since 2.0
 */
class FrmHubSpotAuthHelper {

	/**
	 * @var FrmHubSpotAuth
	 */
	private $strategy;

	/**
	 * Objects which implements FrmHubSpotAuth interface.
	 *
	 * @param FrmHubSpotAuth $strategy method name.
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public function __construct( FrmHubSpotAuth $strategy ) {
		$this->strategy = $strategy;
	}

	/**
	 * Pass called method to the initiated object.
	 *
	 * @param string       $name method name.
	 * @param array<mixed> $arguments arguments.
	 *
	 * @since 2.0
	 *
	 * @return mixed
	 */
	public function __call( $name, $arguments ) {
		return call_user_func_array( array( $this->strategy, $name ), $arguments );
	}

}
