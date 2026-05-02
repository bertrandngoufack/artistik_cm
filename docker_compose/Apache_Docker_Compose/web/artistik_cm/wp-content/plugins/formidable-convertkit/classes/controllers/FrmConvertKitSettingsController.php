<?php
/**
 * Settings controller
 *
 * @package FrmConvertKit
 */

/**
 * Class FrmConvertKitSettingsController
 */
class FrmConvertKitSettingsController {

	/**
	 * Adds settings section.
	 *
	 * @param array $sections Sections.
	 * @return array
	 */
	public static function add_settings_section( $sections ) {
		$sections['convertkit'] = array(
			'class'    => 'FrmConvertKitSettingsController',
			'function' => 'route',
			'name'     => 'ConvertKit',
			'icon'     => 'frm_convertkit_icon frm_icon_font',
		);
		return $sections;
	}

	/**
	 * Registers action.
	 *
	 * @param array $actions Array of actions.
	 * @return array
	 */
	public static function register_actions( $actions ) {
		$actions['convertkit'] = 'FrmConvertKitAction';
		return $actions;
	}

	/**
	 * Displays settings form.
	 *
	 * @return void
	 */
	public static function display_form() {
		$settings = new FrmConvertKitSettings();
		$frm_version = FrmAppHelper::plugin_version();

		require_once FrmConvertKitAppHelper::plugin_path() . '/classes/views/settings/form.php';
	}

	/**
	 * Processes saving settings.
	 *
	 * @return void
	 */
	public static function process_form() {
		$settings = new FrmConvertKitSettings();
		$settings->update();
		$settings->store();

		require_once FrmConvertKitAppHelper::plugin_path() . '/classes/views/settings/form.php';
	}

	/**
	 * Processes request.
	 *
	 * @return void
	 */
	public static function route() {
		$action = FrmAppHelper::get_param( 'action' );
		if ( 'process-form' === $action ) {
			self::process_form();
		} else {
			self::display_form();
		}
	}

	/**
	 * Enqueues scripts.
	 *
	 * @return void
	 */
	public static function enqueue_scripts() {
		if ( FrmAppHelper::is_admin_page() && in_array( FrmAppHelper::get_param( 'frm_action' ), array( 'settings', 'update_settings' ), true ) ) {
			wp_enqueue_script( 'frm-convertkit-action', FrmConvertKitAppHelper::plugin_url() . '/js/action.js', array( 'formidable_dom' ), FrmConvertKitAppHelper::$plug_version, true );
			wp_enqueue_style( 'frm-convertkit-action', FrmConvertKitAppHelper::plugin_url() . '/css/action.css', array(), FrmConvertKitAppHelper::$plug_version );
		}

		if ( FrmAppHelper::is_admin_page( 'formidable-settings' ) ) {
			wp_enqueue_script( 'frm-convertkit-settings', FrmConvertKitAppHelper::plugin_url() . '/js/settings.js', array( 'formidable_dom' ), FrmConvertKitAppHelper::$plug_version, true );
		}
	}

	/**
	 * Fetches data via AJAX.
	 *
	 * @return void
	 */
	public static function ajax_fetch_data() {
		$error = FrmAppHelper::permission_nonce_error( 'frm_edit_forms', 'nonce', 'frm_ajax' );
		if ( $error ) {
			wp_send_json_error( $error );
		}

		$api    = new FrmConvertKitAPI();
		$method = FrmAppHelper::get_param( 'method' );
		$method = array( $api, $method );

		FrmConvertKitAPI::clear_cache();

		if ( is_callable( $method ) ) {
			wp_send_json_success( call_user_func( $method ) );
		}
		wp_send_json_error( __( 'No data', 'frm-convertkit' ) );
	}
}
