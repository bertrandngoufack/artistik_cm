<?php
/**
 * Load all the hooks to keep memory low.
 */
class FrmActiveCampaignHooksController {

	public static function load_hooks() {
		add_action( 'frm_trigger_activecampaign_action', 'FrmActiveCampaignAppController::trigger_activecampaign', 10, 3 );
		add_action( 'frm_registered_form_actions', 'FrmActiveCampaignSettingsController::register_actions' );

		add_action( 'init', __CLASS__ . '::load_lang', 0 );
		self::load_admin_hooks();
	}

	/**
	 * Add support for translating add-on strings.
	 *
	 * @since 1.09
	 *
	 * @return void
	 */
	public static function load_lang() {
		load_plugin_textdomain( 'frmactivecampaign', false, basename( FrmActiveCampaignAppController::path() ) . '/languages/' );
	}

	public static function load_admin_hooks() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_init', 'FrmActiveCampaignAppController::include_updater', 1 );
		add_action( 'admin_enqueue_scripts', 'FrmActiveCampaignHooksController::add_scripts' );
		add_action( 'after_plugin_row_formidable-activecampaign/formidable-activecampaign.php', 'FrmActiveCampaignAppController::min_version_notice' );

		add_action( 'frm_add_settings_section', 'FrmActiveCampaignSettingsController::add_settings_section' );

		// Clear Cache.
		add_action( 'wp_ajax_clear_activecampaign_fields_cache', 'FrmActiveCampaignAction::clear_cache' );
		add_filter( 'frm_autoresponder_allowed_actions', 'FrmActiveCampaignAppController::add_active_campaign_to_automation' );
	}

	public static function add_scripts() {
		if ( self::is_form_settings_page() ) {
			$url = FrmActiveCampaignAppController::plugin_url();
			wp_register_script( 'frmactivecampaign', $url . '/js/frmactivecampaign.js', array(), 1 );

			wp_localize_script(
				'frmactivecampaign',
				'frmactivecampaignGlobal',
				array(
					'nonce'        => wp_create_nonce( 'frmactivecampaign_ajax' ),
				)
			);
			wp_enqueue_script( 'frmactivecampaign' );
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
		return version_compare( $frm_version, '2.0', '>=' );
	}
}
