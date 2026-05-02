<?php

class FrmCtctHooksController {

	/**
	 * @return void
	 */
	public static function load_hooks() {
		add_action( 'frm_trigger_constantcontact_action', 'FrmCtctAppController::trigger_constantcontact', 10, 3 );
		add_action( 'frm_registered_form_actions', 'FrmCtctSettingsController::register_actions' );

		add_action( 'plugins_loaded', 'FrmCtctAppController::load_lang' );

		self::load_admin_hooks();
	}

	/**
	 * @return void
	 */
	public static function load_admin_hooks() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_init', 'FrmCtctAppController::admin_init' );
		add_action( 'admin_enqueue_scripts', 'FrmCtctHooksController::add_scripts' );
		add_action( 'after_plugin_row_formidable-constantcontact/formidable-constantcontact.php', 'FrmCtctAppController::min_version_notice' );

		add_action( 'frm_add_settings_section', 'FrmCtctSettingsController::add_settings_section' );
		add_action( 'wp_ajax_frm_ctct_auth_url', 'FrmCtctSettingsController::auth_url' );

		add_action( 'wp_ajax_clear_ctct_lists_cache', 'FrmCtctAction::clear_cache' );
	}

	/**
	 * Add script file used on Form Settings and Global Settings pages (if on one of those applicable pages).
	 *
	 * @return void
	 */
	public static function add_scripts() {
		if ( ! self::is_form_settings_page() ) {
			return;
		}

		wp_register_script( 'frmctct', FrmCtctAppController::plugin_url() . '/js/frmctct.js', array(), FrmCtctAppController::$plug_version, true );
		wp_localize_script(
			'frmctct',
			'frmctctGlobal',
			array(
				'nonce' => wp_create_nonce( 'frmctct_ajax' ),
			)
		);
		wp_enqueue_script( 'frmctct' );
	}

	/**
	 * Check if the current page is the form settings page
	 *
	 * @since 2.01
	 *
	 * @return bool
	 */
	private static function is_form_settings_page() {
		if ( ! method_exists( 'FrmAppHelper', 'simple_get' ) ) {
			return false;
		}

		$page   = FrmAppHelper::simple_get( 'page', 'sanitize_title' );
		$action = FrmAppHelper::simple_get( 'frm_action', 'sanitize_title' );
		return ( 'formidable' === $page && 'settings' === $action ) || 'formidable-settings' === $page;
	}
}
