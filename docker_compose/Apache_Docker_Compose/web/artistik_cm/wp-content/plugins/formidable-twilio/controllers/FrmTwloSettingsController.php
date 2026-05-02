<?php

class FrmTwloSettingsController {

	public function __construct() {
		add_action( 'frm_add_settings_section', 'FrmTwloSettingsController::add_settings_section' );

		add_action( 'frm_registered_form_actions', 'FrmTwloSettingsController::register_actions' );
		add_action( 'frm_create_twilio_action', 'FrmTwloSettingsController::migrate_to_2', 10, 2 );
	}

	/* Global Form settings */
	public static function add_settings_section( $sections ) {
		$sections['twilio'] = array(
			'class'    => __CLASS__,
			'function' => 'route',
			'icon'     => 'frm_twilio_icon frm_icon_font',
		);
		return $sections;
	}

	private static function display_form( $errors = array(), $message = '' ) {
		$frm_twlo_settings = new FrmTwloSettings();
		require FrmTwloAppController::path() . '/views/form.php';
	}

	private static function process_form() {
		$frm_twlo_settings = new FrmTwloSettings();
		$frm_twlo_settings->update( $_POST );
		$frm_twlo_settings->store();
		$message = __( 'Settings Saved', 'frmtwlo' );
		self::display_form( array(), $message );
	}

	public static function route() {
		$action = FrmAppHelper::get_param( 'action', '', 'get', 'sanitize_title' );
		if ( 'process-form' === $action ) {
			return self::process_form();
		} else {
			return self::display_form();
		}
	}
	/* End Global form settings */

	public static function register_actions( $actions ) {
		$actions['twilio'] = 'FrmTwloAction';
		include_once FrmTwloAppController::path() . '/models/FrmTwloAction.php';
		return $actions;
	}

	/**
	 * Migrate old settings into 2.0 form actions
	 */
	public static function migrate_to_2( $atts, $notification ) {
		$settings = array(
			'to'      => $atts['email_to'],
			'from'    => $notification['twfrom'],
			'message' => isset( $notification['email_message'] ) ? $notification['email_message'] : '',
			'event'   => $atts['event'],
		);

		$new_action = array(
			'post_type'    => FrmFormActionsController::$action_post_type,
			'post_excerpt' => 'twilio',
			'post_name'    => $atts['form_id'] . '_twilio_' . $atts['email_key'],
			'post_title'   => __( 'Send Twilio SMS', 'frmtwlo' ),
			'menu_order'   => $atts['form_id'],
			'post_status'  => 'publish',
			'post_content' => $settings,
		);

		// Switch field IDs and keys, if needed
		$new_action['post_content'] = FrmFieldsHelper::switch_field_ids( $new_action['post_content'] );
		$new_action['post_content'] = FrmAppHelper::prepare_and_encode( $new_action['post_content'] );

		$exists = get_posts(
			array(
				'name'        => $new_action['post_name'],
				'post_type'   => $new_action['post_type'],
				'post_status' => $new_action['post_status'],
				'numberposts' => 1,
			)
		);

		if ( ! $exists ) {
			wp_insert_post( $new_action );
		}
	}

	/* Start v2.0 fallback */
	public static function load_form_settings_hooks() {
		_deprecated_function( __FUNCTION__, '1.09', 'Update your Formidable version' );
	}

	public static function options_js() {
		_deprecated_function( __FUNCTION__, '1.02', 'Update your Formidable version' );
	}

	public static function options( $values, $atts ) {
		_deprecated_function( __FUNCTION__, '1.02', 'Update your Formidable version' );
	}
	/* End v2.0 fallback */

}
