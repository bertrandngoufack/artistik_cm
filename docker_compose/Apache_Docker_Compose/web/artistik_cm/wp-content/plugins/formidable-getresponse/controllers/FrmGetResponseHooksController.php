<?php
/**
 * Load all the hooks to keep memory low.
 */
class FrmGetResponseHooksController {

	public static function load_hooks() {
		add_action( 'init', 'FrmGetResponseAppController::load_lang', 0 );

		add_action( 'frm_trigger_getresponse_action', 'FrmGetResponseAppController::trigger_getresponse', 10, 3 );
		add_action( 'frm_registered_form_actions', 'FrmGetResponseSettingsController::register_actions' );

		self::load_admin_hooks();
	}

	public static function load_admin_hooks() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_init', 'FrmGetResponseAppController::include_updater' );
		add_action( 'after_plugin_row_formidable-getresponse/formidable-getresponse.php', 'FrmGetResponseAppController::min_version_notice' );

		add_action( 'frm_add_settings_section', 'FrmGetResponseSettingsController::add_settings_section' );

	}

	private static function is_form_settings_page() {
		if ( ! self::is_formidable_compatible() ) {
			return;
		}

		$is_form_settings_page = false;
		$page = FrmAppHelper::simple_get( 'page', 'sanitize_title' );
		$action = FrmAppHelper::simple_get( 'frm_action', 'sanitize_title' );
		if ( 'formidable' === $page && 'settings' === $action ) {
			$is_form_settings_page = true;
		}
		return $is_form_settings_page;
	}

	/**
	 * Check if the current version of Formidable is compatible with this add-on
	 *
	 * @since 1.04
	 * @return bool
	 */
	private static function is_formidable_compatible() {
		$frm_version = is_callable( 'FrmAppHelper::plugin_version' ) ? FrmAppHelper::plugin_version() : 0;
		return version_compare( $frm_version, '4.0', '>=' );
	}
}
