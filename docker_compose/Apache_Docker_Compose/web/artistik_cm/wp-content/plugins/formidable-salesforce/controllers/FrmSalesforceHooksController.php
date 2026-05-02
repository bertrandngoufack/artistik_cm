<?php
/**
 * Load all the hooks in one place to keep the baseline memory lower.
 */
class FrmSalesforceHooksController {

	public static function load_hooks() {
		add_action( 'frm_trigger_salesforce_action', 'FrmSalesforceAppController::trigger_salesforce', 10, 3 );
		add_action( 'frm_registered_form_actions', 'FrmSalesforceSettingsController::register_actions' );

		add_action( 'plugins_loaded', 'FrmSalesforceAppController::load_lang' );

		self::load_admin_hooks();
	}

	public static function load_admin_hooks() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_init', 'FrmSalesforceAppController::include_updater' );
		add_action( 'admin_enqueue_scripts', 'FrmSalesforceHooksController::add_scripts' );
		add_action( 'after_plugin_row_formidable-salesforce/formidable-salesforce.php', 'FrmSalesforceAppController::min_version_notice' );

		add_action( 'frm_add_settings_section', 'FrmSalesforceSettingsController::add_settings_section' );
		add_action( 'wp_ajax_frm_salesforce_match_fields', 'FrmSalesforceSettingsController::match_fields' );

		// Save auth token.
		add_action( 'wp_ajax_formidable_salesforce_finish_code_exchange', 'FrmSalesforceAuth::formidable_finish_code_exchange' );

		add_action( 'wp_ajax_formidable_salesforce_revoke', 'FrmSalesforceAuth::revoke' );
	}

	public static function add_scripts() {
		if ( self::is_form_settings_page() ) {
			FrmSalesforceSettingsController::maybe_clear_cache();

			$url = FrmSalesforceAppController::plugin_url();
			wp_enqueue_style( 'frmsalesforce', $url . '/css/frmsalesforce.css', array(), '2.03' );
			wp_register_script( 'frmsalesforce', $url . '/js/frmsalesforce.js', array(), '2.03' );

			wp_localize_script(
				'frmsalesforce',
				'frmsalesforceGlobal',
				array(
					'nonce' => wp_create_nonce( 'frmsalesforce_ajax' ),
				)
			);
			wp_enqueue_script( 'frmsalesforce' );
		}
	}

	/**
	 * Check if the current page is the form settings page
	 *
	 * @since 2.01
	 *
	 * @return bool
	 */
	private static function is_form_settings_page() {
		if ( ! self::is_formidable_compatible() ) {
			return false;
		}

		$page = FrmAppHelper::simple_get( 'page', 'sanitize_title' );
		$action = FrmAppHelper::simple_get( 'frm_action', 'sanitize_title' );
		$is_form_settings_page = ( 'formidable' === $page && 'settings' === $action );

		return $is_form_settings_page;
	}

	/**
	 * Check if the current version of Formidable is compatible with this add-on
	 *
	 * @since 2.04
	 * @return bool
	 */
	private static function is_formidable_compatible() {
		$frm_version = is_callable( 'FrmAppHelper::plugin_version' ) ? FrmAppHelper::plugin_version() : 0;
		return version_compare( $frm_version, '2.0', '>=' );
	}
}
