<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
class FrmStrpHooksController {

	/**
	 * @return void
	 */
	public static function queue_load() {
		add_action( 'plugins_loaded', 'FrmStrpHooksController::load_hooks' );
	}

	/**
	 * @return void
	 */
	public static function load_hooks() {
		$is_free_installed = function_exists( 'load_formidable_forms' );
		if ( ! $is_free_installed ) {
			return;
		}

		if ( is_admin() ) {
			self::load_admin_hooks();
			if ( defined( 'DOING_AJAX' ) ) {
				self::load_ajax_hooks();
			}
		}

		add_action( 'init', 'FrmStrpAppController::load_lang', 0 );
		register_activation_hook( dirname( dirname( __FILE__ ) ) . '/formidable-stripe.php', 'FrmStrpAppController::install' );

		add_filter( 'frm_payment_gateways', 'FrmStrpAppController::add_gateway' );
		add_action( 'rest_api_init', 'FrmStrpAppController::add_api_routes' );

		add_filter( 'frm_filter_final_form', 'FrmStrpAuth::maybe_show_message' );
		add_action( 'frm_entry_form', 'FrmStrpAuth::add_hidden_token_field' );
		add_filter( 'frm_pro_show_card_callback', 'FrmStrpActionsController::show_card_callback' );
		add_filter( 'frm_validate_credit_card_field_entry', 'FrmStrpActionsController::remove_cc_validation', 20, 3 );
		add_action( 'frm_enqueue_form_scripts', 'FrmStrpActionsController::maybe_load_scripts' );
		add_action( 'frm_enqueue_stripe_scripts', 'FrmStrpActionsController::load_scripts' );
		add_filter( 'frm_setup_edit_fields_vars', 'FrmStrpSettingsController::prepare_field_desc', 30, 2 );
		add_filter( 'frm_setup_new_fields_vars', 'FrmStrpSettingsController::prepare_field_desc', 30, 2 );

		add_shortcode( 'frm-stripe-cards', 'FrmStrpPaymentsController::manage_cards' );

		add_filter( 'frm_include_credit_card', '__return_true' );

		add_action( 'init', 'FrmStrpConnectHelper::check_for_stripe_connect_webhooks' );

		add_filter( 'frm_saved_errors', 'FrmStrpAppController::maybe_add_payment_error', 10, 2 );
		add_filter( 'frm_setup_edit_entry_vars', 'FrmStrpAppController::maybe_delete_pay_entry', 20, 2 );

		// Stripe link (and other payment types).
		add_filter( 'frm_form_object', 'FrmStrpLinkController::force_ajax_submit_for_stripe_link' );
		add_filter( 'frm_form_classes', 'FrmStrpLinkController::add_form_classes' );
		add_filter( 'frm_email_action_options', 'FrmStrpLinkController::add_trigger_to_action' );

		add_filter( 'frm_trans_settings_for_js', 'FrmStrpActionsController::update_payment_action_js_vars', 10, 2 );
	}

	/**
	 * @since 3.1
	 *
	 * @return void
	 */
	private static function load_admin_hooks() {
		add_action( 'admin_init', 'FrmStrpAppController::include_updater', 1 );
		add_action( 'frm_after_uninstall', 'FrmStrpAppController::uninstall' );

		add_filter( 'frm_pay_action_defaults', 'FrmStrpActionsController::add_action_defaults' );
		add_action( 'frm_pay_show_stripe_options', 'FrmStrpActionsController::add_action_options' );
		add_filter( 'frm_before_save_payment_action', 'FrmStrpActionsController::before_save_settings' );

		add_filter( 'frm_pay_stripe_receipt', 'FrmStrpPaymentsController::get_receipt_link' );
		add_filter( 'frm_sub_stripe_receipt', 'FrmStrpPaymentsController::get_receipt_link' );

		add_action( 'frm_add_settings_section', 'FrmStrpSettingsController::add_settings_section' );
		add_action( 'frm_pay_stripe_sidebar', 'FrmStrpPaymentsController::show_capture_link' );
		add_action( 'admin_footer', 'FrmStrpSettingsController::hide_cc_settings' );

		add_action( 'frm_update_settings', 'FrmStrpSettingsController::process_form' );

		add_filter( 'frm_inbox_message_is_for_user', 'FrmStrpAppController::inbox_message_is_for_user', 10, 2 );
	}

	/**
	 * @since 3.1
	 *
	 * @return void
	 */
	private static function load_ajax_hooks() {
		// Event processing.
		$frm_strp_events_controller = new FrmStrpEventsController();
		add_action( 'wp_ajax_nopriv_frm_strp_event', array( &$frm_strp_events_controller, 'process_event' ) );
		add_action( 'wp_ajax_frm_strp_event', array( &$frm_strp_events_controller, 'process_event' ) );
		add_action( 'wp_ajax_nopriv_frm_strp_process_events', array( &$frm_strp_events_controller, 'process_connect_events' ) );
		add_action( 'wp_ajax_frm_strp_process_events', array( &$frm_strp_events_controller, 'process_connect_events' ) );

		add_action( 'wp_ajax_frm_trans_capture', 'FrmStrpPaymentsController::capture_charge' );
		add_action( 'wp_ajax_nopriv_frm_strp_amount', 'FrmStrpAuth::update_intent_ajax' );
		add_action( 'wp_ajax_frm_strp_amount', 'FrmStrpAuth::update_intent_ajax' );

		// Stripe Link (and other payment methods).
		add_action( 'wp_ajax_nopriv_frmstrplinkreturn', 'FrmStrpLinkController::handle_return_url' );
		add_action( 'wp_ajax_frmstrplinkreturn', 'FrmStrpLinkController::handle_return_url' );

		// 3D Secure.
		add_action( 'wp_ajax_nopriv_frmstrp3dsecurereturn', 'FrmStrpAuth::handle_3d_secure_return_url' );
		add_action( 'wp_ajax_frmstrp3dsecurereturn', 'FrmStrpAuth::handle_3d_secure_return_url' );
	}
}
