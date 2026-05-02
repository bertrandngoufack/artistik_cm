<?php

class FrmWpmlHooksController {

	public static function load_hooks() {
		// Checking for icl_t is not enough because Polylang includes this function.
		if ( ! function_exists( 'icl_t' ) || ! class_exists( 'SitePress' ) ) {
			return;
		}

		add_action( 'init', 'FrmWpmlSettingsController::load_lang', 0 );

		add_filter( 'frm_ajax_url', 'FrmWpmlAppController::set_ajax_language' );

		add_filter( 'frm_pre_display_form', 'FrmWpmlAppController::setup_frm_wpml_form' );
		add_filter( 'frm_setup_edit_entry_vars', 'FrmWpmlAppController::setup_frm_wpml_form_vars', 20, 2 );
		add_filter( 'frm_setup_new_fields_vars', 'FrmWpmlAppController::setup_translated_field', 20, 2 );
		add_filter( 'frm_setup_edit_fields_vars', 'FrmWpmlAppController::setup_translated_field', 20, 2 );
		add_filter( 'frm_field', 'FrmWpmlAppController::setup_translated_field_object' );
		add_filter( 'frm_field_value_object', 'FrmWpmlAppController::translate_field_properties', 10, 1 );
		add_filter( 'frm_field_object_for_shortcode', 'FrmWpmlAppController::translate_field_properties', 10, 1 );
		add_filter( 'frm_exclude_cats', 'FrmWpmlAppController::filter_taxonomies', 10, 2 );
		add_filter( 'frm_form_replace_shortcodes', 'FrmWpmlAppController::replace_form_shortcodes', 9, 3 );
		add_filter( 'frm_recaptcha_lang', 'FrmWpmlAppController::captcha_lang' );
		add_filter( 'frm_submit_button', 'FrmWpmlAppController::submit_button_label', 20, 2 );
		add_filter( 'frm_validate_field_entry', 'FrmWpmlAppController::setup_frm_wpml_validation', 30, 2 );

		add_filter( 'frm_delete_message', 'FrmWpmlAppController::delete_message', 10, 2 );
		add_filter( 'frm_invalid_error_message', 'FrmWpmlAppController::translate_invalid_error_message', 10, 2 );
		add_filter( 'frm_global_setting', 'FrmWpmlAppController::translate_global_setting', 10, 3 );

		add_filter( 'wc_fp_cart_item_data', 'FrmWpmlAppController::translate_woo_cart', 10, 2 );
		add_filter( 'frm_display_value_custom', 'FrmWpmlAppController::translate_display_value', 0, 3 );
		add_filter( 'frmpro_fields_replace_shortcodes', 'FrmWpmlAppController::views_to_wpml', 11, 4 );

		add_action( 'frm_after_duplicate_form', 'FrmWpmlSettingsController::after_duplicate_form', 10, 3 );
		add_action( 'frm_after_duplicate_field', 'FrmWpmlSettingsController::after_duplicate_field', 10, 4 );

		self::load_admin_hooks();
	}

	public static function load_admin_hooks() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_init', 'FrmWpmlSettingsController::include_updater', 1 );

		add_action( 'frm_settings_buttons', 'FrmWpmlSettingsController::add_translate_button' );

		add_action( 'frm_form_action_translate', 'FrmWpmlSettingsController::translate' );
		add_action( 'frm_form_action_update_translate', 'FrmWpmlSettingsController::update_translate' );
		add_action( 'frm_update_form', 'FrmWpmlSettingsController::update_saved_wpml_strings', 10, 2 );

		add_filter( 'frm_form_stop_action_translate', 'FrmWpmlSettingsController::translated' );
		add_filter( 'frm_form_stop_action_update_translate', 'FrmWpmlSettingsController::translated' );

		add_action( 'frm_before_destroy_field', 'FrmWpmlSettingsController::delete_frm_wpml' );

		add_action( 'frm_update_form', 'FrmWpmlSettingsController::maybe_unregister_obsolete_icl_entries', 10, 2 );

		add_filter( 'screen_options_show_screen', 'FrmWpmlSettingsController::add_screen_options_to_translate_page', 10, 2 );
		add_filter( 'set-screen-option', 'FrmWpmlSettingsController::save_per_page', 10, 3 );
	}
}
