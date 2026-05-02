<?php

class FrmHubSpotHooksController {

	/**
	 * Adds this class to hook controllers list.
	 *
	 * @since 2.0
	 *
	 * @param array $controllers Hooks controllers.
	 * @return array
	 */
	public static function add_hooks_controller( $controllers ) {
		if ( ! self::is_compatible() ) {
			self::load_incompatible_hooks();

			return $controllers;
		}

		$controllers[] = __CLASS__;
		return $controllers;
	}

	public static function load_hooks() {
		self::load_translation();

		add_filter( 'frm_get_field_type_class', 'FrmHubSpotAppController::add_field_class', 10, 2 );
		add_action( 'frm_trigger_hubspot_action', 'FrmHubSpotAppController::trigger_hubspot', 10, 3 );
		add_action( 'frm_registered_form_actions', 'FrmHubSpotSettingsController::register_actions' );
		add_action( 'rest_api_init', array( new FrmHubSpotWebhooks(), 'register_routes' ) );
	}

	public static function load_admin_hooks() {
		add_filter( 'frm_pro_available_fields', 'FrmHubSpotAppController::add_field' );
		add_action( 'admin_enqueue_scripts', 'FrmHubSpotAppController::enqueue_admin_assets' );
		add_action( 'admin_init', 'FrmHubSpotAppController::admin_init', 1 );
		add_action( 'admin_init', 'FrmHubSpotSettingsController::maybe_clear_cache' );
		add_action( 'after_plugin_row_formidable-hubspot/formidable-hubspot.php', 'FrmHubSpotAppController::min_version_notice' );
		add_filter( 'frm_fields_in_form_builder', 'FrmHubSpotAppController::hide_builder_field' );
		add_filter( 'frm_action_logic_exclude_fields', 'FrmHubSpotAppController::hide_hubspot_from_condition_logic_row', 10, 1 );
		add_filter( 'frm_entry_values_exclude_fields', 'FrmHubSpotAppController::exclude_hubspot_field_from_entry_detail', 10, 2 );

		// Settings controller.
		add_filter( 'frm_add_settings_section', 'FrmHubSpotSettingsController::add_settings_section' );
		add_action( 'frm_update_settings', 'FrmHubSpotSettingsController::update' );
		add_action( 'frm_store_settings', 'FrmHubSpotSettingsController::store' );

		self::load_ajax_hooks();
	}

	/**
	 * These hooks only load during ajax request.
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public static function load_ajax_hooks() {
		if ( ! wp_doing_ajax() ) {
			return;
		}

		add_action( 'wp_ajax_frm_hubspot_authorization', 'FrmHubSpotOAuth::authorization' );
		add_action( 'wp_ajax_frm_hubspot_authorization_pat', 'FrmHubSpotPAT::authorization' );
		add_action( 'wp_ajax_frm_hbsp_save_key', 'FrmHubSpotSettingsController::process_ajax' );
	}

	/**
	 * Display an error to the user that the plugin could not get activated.
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public static function show_incompatible_notice() {
		echo '<div class="error">' .
			'<p>' . esc_html( self::incompatible_message() ) . '</p>' .
		'</div>';
	}

	/**
	 * Display an error to the user that the plugin could not get activated.
	 *
	 * @since 2.0
	 *
	 * @param array<string> $messages used for formidable.
	 * @return array<string> Array of messages.
	 */
	public static function add_incompatible_message( $messages ) {
		$messages[] = self::incompatible_message();
		return $messages;
	}

	/**
	 * Loads translation.
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	private static function load_translation() {
		add_action( 'init', array( 'FrmHubSpotAppController', 'init_translation' ) );
	}

	/**
	 * Get the error message to show if not compatible.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	private static function incompatible_message() {
		return __( 'Formidable HubSpot requires an active version of Formidable Pro.', 'formidable-hubspot' );
	}

	/**
	 * Loads hooks when this plugin isn't safe to run.
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	private static function load_incompatible_hooks() {
		self::load_translation();

		add_action( 'admin_notices', array( __CLASS__, 'show_incompatible_notice' ) );

		$page = FrmAppHelper::get_param( 'page', '', 'get', 'sanitize_text_field' );
		if ( 'formidable' === $page ) {
			add_filter( 'frm_message_list', array( __CLASS__, 'add_incompatible_message' ) );
		}
	}

	/**
	 * Checks if this plugin is safe to run.
	 *
	 * @since 2.0
	 *
	 * @return bool
	 */
	private static function is_compatible() {
		return true;
	}

	/**
	 * Clear cache.
	 *
	 * @deprecated 2.0
	 *
	 * @return void
	 */
	public static function add_scripts() {
		_deprecated_function( __METHOD__, '2.0', 'FrmHubSpotSettingsController::maybe_clear_cache()' );
		FrmHubSpotSettingsController::maybe_clear_cache();
	}

}
