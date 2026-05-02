<?php
/**
 * Add and Save Global settings.
 */
class FrmGetResponseSettingsController {

	public static function add_settings_section( $sections ) {
		$sections['getresponse'] = array(
			'class'    => 'FrmGetResponseSettingsController',
			'function' => 'route',
			'name'     => 'GetResponse',
			'icon'     => 'frm_getresponse_icon frm_icon_font',
		);
		return $sections;
	}

	public static function register_actions( $actions ) {
		$actions['getresponse'] = 'FrmGetResponseAction';

		include_once FrmGetResponseAppController::path() . '/models/FrmGetResponseAction.php';

		return $actions;
	}

	public static function display_form() {
		$frm_getresponse_settings = new FrmGetResponseSettings();
		$frm_version = FrmAppHelper::plugin_version();

		require_once FrmGetResponseAppController::path() . '/views/settings/form.php';
	}

	public static function process_form() {
		$frm_getresponse_settings = new FrmGetResponseSettings();

		$process_form = FrmAppHelper::get_post_param( 'process_form', '', 'sanitize_text_field' );
		if ( wp_verify_nonce( $process_form, 'process_form_nonce' ) ) {
			$frm_getresponse_settings->update( $_POST );

			$frm_getresponse_settings->store();
			$message = __( 'Settings Saved', 'formidable-getresponse' );
		}

		require_once FrmGetResponseAppController::path() . '/views/settings/form.php';
	}

	public static function route() {
		$action = FrmAppHelper::get_param( 'action' );
		if ( 'process-form' == $action ) {
			return self::process_form();
		} else {
			return self::display_form();
		}
	}
}
