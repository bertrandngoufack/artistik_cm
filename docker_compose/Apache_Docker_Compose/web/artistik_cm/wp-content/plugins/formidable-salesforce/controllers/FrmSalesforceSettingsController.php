<?php
/**
 * Initialize and save the settings
 */
class FrmSalesforceSettingsController {

	/**
	 * Add a tab in the Global settings
	 *
	 * @param array $sections
	 * @return array
	 */
	public static function add_settings_section( $sections ) {
		$sections['salesforce'] = array(
			'class' => 'FrmSalesforceSettingsController',
			'function' => 'route',
			'icon'     => 'frm_icon_font frm_salesforce_icon',
		);
		return $sections;
	}

	/**
	 * Check if the clear cache button was clicked
	 *
	 * @since 2.04
	 */
	public static function maybe_clear_cache() {
		$clear = FrmAppHelper::get_param( 'clear_cache', '', 'get', 'sanitize_text_field' );
		if ( 'salesforce' === $clear && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) ) ) {
			$salesforce = new FrmSalesforceAPI();
			$salesforce->clear_cache();
		}
	}

	public static function match_fields() {
		check_ajax_referer( 'frmsalesforce_ajax', 'security', true );
		$form_id = isset( $_POST['form_id'] ) ? (int) $_POST['form_id'] : false;
		$object_id = isset( $_POST['object_id'] ) ? sanitize_text_field( wp_unslash( $_POST['object_id'] ) ) : false;
		if ( ! $form_id || ! $object_id ) {
			wp_die();
		}

		$form_fields = FrmField::getAll( 'fi.form_id=' . (int) $form_id . " and fi.type not in ('break', 'divider', 'html', 'captcha', 'form')", 'field_order' );

		if ( isset( $_POST['action_key'] ) ) {
			$action_control = FrmFormActionsController::get_form_actions( 'salesforce' );
			$action_control->_set( sanitize_text_field( wp_unslash( $_POST['action_key'] ) ) );
			$list_options = $action_control->get_defaults();

			$salesforce = new FrmSalesforceAPI();
			$object_fields = $salesforce->fetch_object_fields( $object_id );

			include FrmSalesforceAppController::path() . '/views/action-settings/_match_fields.php';
		}

		wp_die();
	}

	public static function register_actions( $actions ) {
		$actions['salesforce'] = 'FrmSalesforceAction';

		include_once FrmSalesforceAppController::path() . '/models/FrmSalesforceAction.php';

		return $actions;
	}

	/**
	 * Get the saved global settings
	 *
	 * @since 2.01
	 */
	public static function get_settings() {
		global $frm_salesforce_settings;
		if ( empty( $frm_salesforce_settings ) ) {
			$frm_salesforce_settings = new FrmSalesforceSettings();
		}
		return $frm_salesforce_settings;
	}

	public static function display_form() {
		$frm_salesforce_settings = self::get_settings();
		$frm_version = FrmAppHelper::plugin_version();

		require_once FrmSalesforceAppController::path() . '/views/settings/form.php';
	}

	/**
	 * Show the button to either Authorize or Deauthorize depending
	 * on if the current Auth token is valid.
	 *
	 * @since 2.01
	 * @param object $settings
	 */
	public static function include_authorize_button( $settings ) {
		if ( empty( $settings->client_id ) ) {
			return;
		}

		$authsettings = get_option( 'formidable_salesforce_auth', true );

		$btn_text = __( 'Authorize', 'formidable-salesforce' );
		$class    = 'formidable_salesforce_authorization';
		$auth_url = '';
		$oauth_url = self::get_oauth_url();

		if ( empty( $authsettings['refresh_token'] ) ) {
			$redirect_uri = self::get_auth_redirect_url();
			$auth_url = $oauth_url . 'authorize?response_type=code&client_id=' . $settings->client_id . '&redirect_uri=' . $redirect_uri;
		} else {
			$auth_url = $oauth_url . 'revoke';
			$btn_text = __( 'Deauthorize', 'formidable-salesforce' );
			$class    = 'formidable_salesforce_deauthorize';
		}

		if ( isset( $authsettings['issued_at'] ) ) {
			$seconds = (int) ( $authsettings['issued_at'] / 1000 );
			$format  = get_option( 'date_format' );
			echo '<p class="alignright howto" style="padding:0 5px;">' .
				/* translators: %s: the date string */
				esc_html( sprintf( __( 'Issued %s', 'formidable-salesforce' ), gmdate( $format, $seconds ) ) ) .
				'</p>';
		}

		echo '<a  class="' . esc_attr( $class ) . ' button-secondary" href="' . esc_url( $auth_url ) . '">' .
			esc_html( $btn_text ) .
			'</a>';
	}

	/**
	 * The page to get the Auth token
	 *
	 * @since 2.01
	 */
	public static function get_auth_redirect_url() {
		return self::get_oauth_url( 'success' );
	}

	/**
	 * The endpoint URL for authenticating
	 *
	 * @since 2.01
	 * @param string $append
	 */
	public static function get_oauth_url( $append = '' ) {
		return self::get_base_oauth_url() . '/services/oauth2/' . $append;
	}

	/**
	 * The base URL for API requests.
	 *
	 * @since 2.01
	 */
	public static function get_base_oauth_url() {
		$settings = self::get_settings();
		$environment = $settings->settings->environment;
		if ( 'sandbox' === $environment ) {
			$url = 'https://test.salesforce.com';
		} else {
			$url = 'https://login.salesforce.com';
		}

		return apply_filters( 'frm-salesforce-outh-url', $url );
	}

	/**
	 * When the global settings page is saved, check if the Auth token has changed.
	 *
	 * @since 2.01
	 * @param object $settings
	 */
	public static function maybe_set_token( $settings ) {
		$process_form = FrmAppHelper::get_post_param( 'process_form', '', 'sanitize_text_field' );
		if ( ! wp_verify_nonce( $process_form, 'process_form_nonce' ) ) {
			return;
		}

		if ( ! empty( $_POST['frm_salesforce_auth_code'] ) && ! empty( $settings->client_id ) && ! empty( $settings->client_secret ) ) {
			$auth_settings = get_option( 'formidable_salesforce_auth', true );
			$previous_auth_code = urldecode( $settings->auth_code );
			$auth_code = urldecode( sanitize_text_field( wp_unslash( $_POST['frm_salesforce_auth_code'] ) ) );
			$needs_refresh = empty( $auth_settings ) || empty( $auth_settings['instance_url'] );
			if ( $previous_auth_code != $auth_code || $needs_refresh ) {
				FrmSalesforceAuth::set_oauth2_token( $auth_code, 'auth_code' );
			}
		}
	}

	public static function process_form() {
		$frm_salesforce_settings = self::get_settings();

		$process_form = FrmAppHelper::get_post_param( 'process_form', '', 'sanitize_text_field' );
		if ( wp_verify_nonce( $process_form, 'process_form_nonce' ) ) {
			self::maybe_set_token( $frm_salesforce_settings->settings );
			$frm_salesforce_settings->update( $_POST );
			$frm_salesforce_settings->store();
		}

		require_once FrmSalesforceAppController::path() . '/views/settings/form.php';
	}

	public static function route() {
		$action = FrmAppHelper::get_param( 'action' );
		if ( 'process-form' === $action ) {
			return self::process_form();
		} else {
			return self::display_form();
		}
	}
}
