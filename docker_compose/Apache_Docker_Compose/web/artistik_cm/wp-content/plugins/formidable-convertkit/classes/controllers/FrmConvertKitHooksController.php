<?php
/**
 * Hooks controller
 *
 * @package FrmConvertKit
 */

/**
 * Class FrmConvertKitHooksController
 */
class FrmConvertKitHooksController {

	/**
	 * Adds this class to hook controllers list.
	 *
	 * @param array $controllers Hooks controllers.
	 * @return array
	 */
	public static function add_hooks_controller( $controllers ) {
		if ( ! FrmConvertKitAppHelper::is_compatible() ) {
			self::load_incompatible_hooks();
			return $controllers;
		}

		$controllers[] = __CLASS__;
		return $controllers;
	}

	/**
	 * Loads hooks when this plugin isn't safe to run.
	 *
	 * @return void
	 */
	private static function load_incompatible_hooks() {
		self::load_translation();

		add_action( 'admin_notices', array( 'FrmConvertKitAppController', 'show_incompatible_notice' ) );
	}

	/**
	 * Loads translation.
	 *
	 * @return void
	 */
	private static function load_translation() {
		add_action( 'init', array( 'FrmConvertKitAppController', 'init_translation' ) );
	}

	/**
	 * Loads plugin hooks.
	 *
	 * @return void
	 */
	public static function load_hooks() {
		add_action( 'frm_trigger_convertkit_action', 'FrmConvertKitActionController::trigger_action', 10, 2 );
		add_action( 'frm_registered_form_actions', 'FrmConvertKitSettingsController::register_actions' );
		self::load_translation();
	}

	/**
	 * These hooks are only needed for front-end forms.
	 *
	 * @return void
	 */
	public static function load_form_hooks() {
	}

	/**
	 * These hooks only load during ajax request.
	 *
	 * @return void
	 */
	public static function load_ajax_hooks() {
		add_action( 'wp_ajax_frm_cvk_fetch', 'FrmConvertKitSettingsController::ajax_fetch_data' );
	}

	/**
	 * These hooks only load in the admin area.
	 *
	 * @return void
	 */
	public static function load_admin_hooks() {
		add_action( 'admin_init', 'FrmConvertKitAppController::include_updater' );
		add_action( 'admin_enqueue_scripts', 'FrmConvertKitSettingsController::enqueue_scripts' );
		add_filter( 'frm_add_settings_section', 'FrmConvertKitSettingsController::add_settings_section' );
	}

	/**
	 * Add view hooks here.
	 * This can be removed later. It's required for earlier Formidable versions.
	 *
	 * @return void
	 */
	public static function load_view_hooks() {
	}
}
