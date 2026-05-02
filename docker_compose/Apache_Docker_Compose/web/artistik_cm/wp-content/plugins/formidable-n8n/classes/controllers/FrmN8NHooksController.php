<?php
/**
 * Hooks controller
 *
 * @package FrmN8N
 */

/**
 * Class FrmN8NHooksController
 */
class FrmN8NHooksController {

	/**
	 * Adds this class to hook controllers list.
	 *
	 * @param array $controllers Hooks controllers.
	 *
	 * @return array
	 */
	public static function add_hooks_controller( $controllers ) {
		if ( ! FrmN8NAppHelper::is_compatible() ) {
			self::load_incompatible_hooks();
			return $controllers;
		}

		$controllers[] = self::class;
		return $controllers;
	}

	/**
	 * Loads hooks when this plugin isn't safe to run.
	 *
	 * @return void
	 */
	private static function load_incompatible_hooks() {
		self::load_translation();

		add_action( 'admin_notices', array( 'FrmN8NAppController', 'show_incompatible_notice' ) );
	}

	/**
	 * Loads translation.
	 *
	 * @return void
	 */
	private static function load_translation() {
		add_action( 'init', array( 'FrmN8NAppController', 'init_translation' ) );
	}

	/**
	 * Loads plugin hooks.
	 *
	 * @return void
	 */
	public static function load_hooks() {
		self::load_translation();

		add_filter( 'frm_registered_form_actions', array( 'FrmN8NFormActionController', 'register_actions' ) );
		add_action( 'frm_trigger_n8n_action', array( 'FrmN8NFormActionController', 'trigger_action' ), 10, 4 );
	}

	/**
	 * These hooks only load in the admin area.
	 *
	 * @return void
	 */
	public static function load_admin_hooks() {
		add_action( 'admin_init', array( 'FrmN8NAppController', 'include_updater' ) );
		add_action( 'admin_enqueue_scripts', array( 'FrmN8NAppController', 'admin_scripts' ) );
		add_filter( 'frm_before_save_n8n_action', array( 'FrmN8NFormActionController', 'filter_saved_value' ) );
	}
}
