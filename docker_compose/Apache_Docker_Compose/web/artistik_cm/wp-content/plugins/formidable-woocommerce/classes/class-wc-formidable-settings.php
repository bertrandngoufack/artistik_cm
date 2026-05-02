<?php
/**
 * WooCommerce Formidable Forms Product Add-ons
 *
 * @package     WC-formidable/Classes
 * @author      Strategy11
 * @copyright   Copyright (c) 2015, Strategy11
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * This class handles all of the WooCommerce settings section implementation on the wp admin page
 */
class WC_Formidable_Settings {

	const OPTION_NAME = 'frm_wc_settings';
	private static $settings;

	/**
	 * Initialize the WooCommerce Formidable Forms Settings class
	 *
	 * @since 1.10
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'frm_update_settings', array( $this, 'update' ) );
			add_action( 'frm_registration_settings_form', array( $this, 'registration_settings' ) );
		}

		add_filter( 'wc_get_template', array( $this, 'get_template' ), 10, 2 );
	}

	/**
	 * Add a WooCommerce settings section on the Registration tab in the global settings.
	 *
	 * @param  object $frm_settings The saved global settings.
	 * @since  1.10
	 */
	public function registration_settings() {
		if ( empty( self::$settings ) ) {
			self::init_settings();
		}

		$forms = $this->get_registration_forms();
		$frm_wc_settings = self::$settings;
		require_once WC_Formidable_App_Helper::plugin_path() . '/views/settings/registration.php';
	}

	/**
	 * Show a tooltip icon with the message passed.
	 *
	 * @since 1.13
	 *
	 * @param string $message The message to be displayed in the tooltip.
	 * @return void
	 */
	private static function show_svg_tooltip( $message ) {
		if ( ! is_callable( 'FrmAppHelper::tooltip_icon' ) ) {
			return;
		}
		FrmAppHelper::tooltip_icon( $message );
	}

	/**
	 * Get only the forms that have a user registration form action.
	 *
	 * @since  1.10
	 * @return mixed
	 */
	private function get_registration_forms() {
		global $wpdb;

		$query_args = array(
			'f.status'       => 'published',
			'p.post_type'    => 'frm_form_actions',
			'p.post_excerpt' => 'register',
		);

		return FrmDb::get_results(
			$wpdb->prefix . 'frm_forms AS f INNER JOIN ' . $wpdb->prefix . 'posts AS p ON f.id = p.menu_order',
			$query_args,
			'f.id, f.name'
		);
	}

	/**
	 * Store/update the fields of the WooCommerce settings section.
	 *
	 * @since  1.10
	 */
	public function update() {
		$options = array(
			'frm_registration_form' => FrmAppHelper::get_param( 'frm_registration_form', '', 'post', 'sanitize_text_field' ),
			'frm_profile_form'      => FrmAppHelper::get_param( 'frm_profile_form', '', 'post', 'sanitize_text_field' ),
		);

		update_option( self::OPTION_NAME, $options, 'no' );

		//keeps the $settings variable updated
		self::$settings = $options;
	}

	/**
	 * Filter the form-login and form-edit-account templates path to use formidable forms templates instead of the theme's.
	 *
	 * @param  string $located
	 * @param  string  $template_name
	 *
	 * @since  1.10
	 * @return string
	 */
	public function get_template( $located, $template_name ) {
		$templates = array(
			'myaccount/form-login.php',
			'myaccount/form-edit-account.php',
		);

		if ( ! in_array( $template_name, $templates ) ) {
			return $located;
		}

		if ( $templates[0] === $template_name && self::setting_field_is_not_empty( 'frm_registration_form' ) && get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) {
			$located = WC_Formidable_App_Helper::plugin_path() . '/views/templates/form-login.php';
		}

		if ( $templates[1] === $template_name && self::setting_field_is_not_empty( 'frm_profile_form' ) ) {
			$located = WC_Formidable_App_Helper::plugin_path() . '/views/templates/form-edit-account.php';
		}

		return $located;
	}

	/**
	 * @since 1.10
	 */
	private static function init_settings() {
		self::$settings = get_option(
			self::OPTION_NAME,
			array(
				'frm_registration_form' => '',
				'frm_profile_form'      => '',
			)
		);
	}

	/**
	 * Determine if the setting field is not empty.
	 *
	 * @param  string $key The setting field being evaluated.
	 * @since  1.10
	 * @return boolean
	 */
	public static function setting_field_is_not_empty( $key ) {
		if ( empty( self::$settings ) ) {
			self::init_settings();
		}

		return is_array( self::$settings ) && ! empty( self::$settings[ $key ] );
	}

	public static function get_setting( $key ) {
		return self::setting_field_is_not_empty( $key ) ? self::$settings[ $key ] : '';
	}
}
