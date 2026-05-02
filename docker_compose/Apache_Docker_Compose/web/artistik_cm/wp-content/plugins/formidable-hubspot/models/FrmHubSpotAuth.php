<?php
/**
 * FrmHubSpotRequest Interface.
 *
 * @since 2.0
 */
interface FrmHubSpotAuth {
	/**
	 * Get access token.
	 *
	 * @since 2.0
	 *
	 * @return WP_Error|string access token.
	 */
	public function get_access_token();
}
