<?php

class FrmHubSpotSettingsController {

	public static function add_settings_section( $sections ) {
		$sections['hubspot'] = array(
			'class'    => __CLASS__,
			'function' => 'display_form',
			'name'     => 'HubSpot',
			'icon'     => 'frmfont frm_hubspot_icon',
		);
		return $sections;
	}

	public static function register_actions( $actions ) {
		$actions['hubspot'] = 'FrmHubSpotAction';

		include_once FrmHubSpotAppHelper::path() . '/models/FrmHubSpotAction.php';

		return $actions;
	}

	/**
	 * Clear the API cache when the clear cache button is clicked.
	 *
	 * @since 1.06
	 */
	public static function maybe_clear_cache() {
		if ( ! FrmHubSpotAppController::is_form_settings_page() ) {
			return;
		}

		$clear = FrmAppHelper::simple_get( 'clear_cache', 'sanitize_text_field' );
		$nonce = FrmAppHelper::simple_get( '_wpnonce', 'sanitize_text_field' );
		if ( 'hubspot' === $clear && ! empty( $nonce ) && wp_verify_nonce( $nonce ) ) {
			$api = new FrmHubSpotAuthHelper( FrmHubSpotAppHelper::get_active_authorization_class() );
			$api->clear_cache();
		}
	}

	public static function display_form() {
		$frm_hubspot_settings = FrmHubSpotAppHelper::get_settings();

		$is_valid_pat_exists = ( new FrmHubSpotPAT() )->is_valid_private_app_access_token( $frm_hubspot_settings->private_app_access_token );
		$is_valid_connection_exists = ! empty( $frm_hubspot_settings->formidable_hubspot_oauth ) || ! is_wp_error( $is_valid_pat_exists );
		require_once FrmHubSpotAppHelper::path() . '/views/settings/form.php';
	}

	/**
	 * Save the API key via ajax from the form action.
	 *
	 * @since 1.07
	 */
	public static function process_ajax() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$settings = FrmHubSpotAppHelper::get_settings();
		$settings->update( $_POST );
		$settings->store();

		wp_die();
	}

	/**
	 * Update setting field according to the new params.
	 *
	 * @since 2.0
	 *
	 * @param array $params of updated form.
	 * @see action hook frm_update_settings
	 * @return void
	 */
	public static function update( $params ) {
		$settings = FrmHubSpotAppHelper::get_settings();
		$settings->update( $params );
	}

	/**
	 * Save updated field to the DB.
	 *
	 * @since 2.0
	 *
	 * @see action hook frm_store_settings
	 * @return void
	 */
	public static function store() {
		$settings = FrmHubSpotAppHelper::get_settings();
		$settings->store();
	}

	/**
	 * @deprecated 2.0
	 */
	public static function process_form() {
		_deprecated_function( __METHOD__, '2.0' );
	}

	/**
	 * @deprecated 2.0
	 */
	public static function route() {
		_deprecated_function( __METHOD__, '2.0' );
	}
}
