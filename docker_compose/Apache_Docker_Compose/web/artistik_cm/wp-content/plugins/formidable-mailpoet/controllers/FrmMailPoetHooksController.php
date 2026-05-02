<?php

class FrmMailPoetHooksController {

	public static function load_hooks() {
		add_action( 'plugins_loaded', 'FrmMailPoetAppController::load_lang' );

		add_action( 'frm_trigger_mailpoet_action', 'FrmMailPoetAppController::trigger_mailpoet', 10, 3 );
		add_action( 'frm_registered_form_actions', 'FrmMailPoetAppController::register_actions' );
		self::load_admin_hooks();
	}

	public static function load_admin_hooks() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_init', 'FrmMailPoetAppController::include_updater' );
		add_action( 'after_plugin_row_formidable-mailpoet/formidable-mailpoet.php', 'FrmMailPoetAppController::min_version_notice' );

	}

	/**
	 * Check if the current page is the form settings page
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
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
	 * @since 1.02
	 * @return bool
	 */
	private static function is_formidable_compatible() {
		$frm_version = is_callable( 'FrmAppHelper::plugin_version' ) ? FrmAppHelper::plugin_version() : 0;
		return version_compare( $frm_version, '2.0', '>=' );
	}

	/**
	 * @deprecated 1.02
	 */
	public static function add_scripts() {
		_deprecated_function( __METHOD__, '1.2' );
	}
}
