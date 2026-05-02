<?php

class FrmAwbrSettingsController {

	public static function load_hooks() {
		add_action( 'frm_registered_form_actions', 'FrmAwbrSettingsController::register_actions' );
		add_action( 'frm_before_list_actions', 'FrmAwbrSettingsController::migrate_to_2' );

		add_action( 'frm_add_settings_section', 'FrmAwbrSettingsController::add_settings_section' );
		add_action( 'wp_ajax_frm_awbr_match_fields', 'FrmAwbrSettingsController::match_fields' );
	}

	public static function register_actions( $actions ) {
		$actions['aweber'] = 'FrmAwbrAction';
		include_once FrmAwbrAppController::path() . '/models/FrmAwbrAction.php';

		return $actions;
	}

	public static function add_settings_section( $sections ) {
		$sections['aweber'] = array(
			'class'    => 'FrmAwbrSettingsController',
			'function' => 'route',
			'icon'     => 'frm_icon_font frm_aweber_icon',
		);
		return $sections;
	}

	public static function match_fields() {
		$form_id = FrmAppHelper::get_param( 'form_id', 0, 'post', 'absint' );
		$list_id = FrmAppHelper::get_param( 'list_id', 0, 'post', 'sanitize_text_field' );
		if ( ! $form_id || ! $list_id ) {
			wp_die();
		}

		$list = FrmAwbrAppHelper::get_aweber_list( $list_id );
		if ( empty( $list ) ) {
			$error = FrmAwbrAppHelper::wrong_account_message();
		} else {
			$list_fields = $list->custom_fields->data['entries'];
		}

		$form_fields = FrmField::getAll( "fi.form_id='$form_id' and fi.type not in ('break', 'divider', 'html', 'captcha', 'form')" );

		$action_control = FrmFormActionsController::get_form_actions( 'aweber' );
		$action_control->_set( FrmAppHelper::get_param( 'action_key', 0, 'post', 'absint' ) );

		include FrmAwbrAppController::path() . '/views/action-settings/_match_fields.php';
		wp_die();
	}

	public static function display_form() {
		$frm_awbr_settings = new FrmAwbrSettings();

		$frm_version = FrmAppHelper::plugin_version();

		require_once FrmAwbrAppController::path() . '/views/settings/form.php';
	}

	public static function process_form() {
		$nonce = FrmAppHelper::get_post_param( 'process_form', '', 'sanitize_text_field' );
		if ( ! wp_verify_nonce( $nonce, 'process_form_nonce' ) ) {
			return;
		}

		$frm_awbr_settings = new FrmAwbrSettings();

		if ( ! class_exists( 'FrmAWeberAPI' ) ) {
			require_once FrmAwbrAppController::path() . '/aweber_api/aweber.php';
		}

		$aweber = new FrmAWeberAPI( $frm_awbr_settings->settings->consumer_key, $frm_awbr_settings->settings->consumer_secret );
		$message = '';
		$error = '';
		$old_key = $frm_awbr_settings->settings->access_key;

		$oauth_id = FrmAppHelper::get_param( 'frm_awbr_oauth_id', '', 'post', 'sanitize_text_field' );
		if ( ! empty( $oauth_id ) && ( empty( $frm_awbr_settings->settings->access_secret ) || $oauth_id != $frm_awbr_settings->settings->oauth_id ) ) {
			// Then they just saved a key and didn't remove anything
			// Check it's validity then save it for later use
			try {
				list( $consumer_key, $consumer_secret, $access_key, $access_secret ) = $aweber->getDataFromAweberID( $oauth_id );
			} catch ( FrmAWeberException $e ) {
				list( $consumer_key, $consumer_secret, $access_key, $access_secret ) = null;
			}
			if ( ! $access_secret ) {
				$error = __( 'There was a problem authenticating AWeber', 'formidable-aweber' );
			} else {
				$frm_awbr_settings->settings->consumer_key = $consumer_key;
				$_POST['frm_awbr_consumer_key'] = $consumer_key;
				$frm_awbr_settings->settings->consumer_secret = $consumer_secret;
				$_POST['frm_awbr_consumer_secret'] = $consumer_secret;
				$frm_awbr_settings->settings->access_key = $access_key;
				$_POST['frm_awbr_access_key'] = $access_key;
				$frm_awbr_settings->settings->access_secret = $access_secret;
				$_POST['frm_awbr_access_secret'] = $access_secret;

				$frm_awbr_settings->update( $_POST );
				$frm_awbr_settings->store();

				$aweber = new FrmAWeberAPI( $frm_awbr_settings->settings->consumer_key, $frm_awbr_settings->settings->consumer_secret );
			}
		}

		if ( $frm_awbr_settings->settings->access_key && $frm_awbr_settings->settings->access_key != $old_key ) {
			try {
				$account = $aweber->getAccount( $frm_awbr_settings->settings->access_key, $frm_awbr_settings->settings->access_secret );
			} catch ( FrmAWeberException $e ) {
				$account = null;
			}
			if ( ! $account ) {
				$frm_awbr_settings->update( array( 'frm_awbr_access_secret' => null ) );
				$error = __( 'AWeber Authorization failed', 'formidable-aweber' );
			} else {
				$message = __( 'AWeber successfully Authorized', 'formidable-aweber' );
				$authorize_success = true;
			}
		}

		$frm_version = FrmAppHelper::plugin_version();

		require_once FrmAwbrAppController::path() . '/views/settings/form.php';
	}

	public static function route() {
		$action = FrmAppHelper::get_param( 'action' );
		if ( 'process-form' == $action ) {
			return self::process_form();
		} else {
			return self::display_form();
		}
	}

	public static function migrate_to_2( $form ) {
		if ( ! isset( $form->options['aweber'] ) || ! $form->options['aweber'] || empty( $form->options['awbr_list'] ) ) {
			return;
		}

		if ( ! FrmAwbrAppHelper::is_formidable_v2() ) {
			return;
		}

		$action_control = FrmFormActionsController::get_form_actions( 'aweber' );
		$orginal_options = $form->options;

		$post_id = false;
		foreach ( (array) $form->options['awbr_list'] as $list_id => $list_options ) {
			$form->options['list_id'] = $list_id;
			$form->options = array_merge( $form->options, $list_options );

			$post_id = $action_control->migrate_to_2( $form, 'skip' );
			$form->options = $orginal_options;
		}

		if ( $post_id ) {
			global $wpdb;

			// update form options
			unset( $form->options['aweber'], $form->options['awbr_list'] );

			$wpdb->update( $wpdb->prefix . 'frm_forms', array( 'options' => maybe_serialize( $form->options ) ), array( 'id' => $form->id ) );
			wp_cache_delete( $form->id, 'frm_form' );
		}

		return $post_id;
	}

	public static function load_form_settings_hooks() {
		_deprecated_function( __METHOD__, '2.02' );
	}

	public static function add_aweber_options( $sections ) {
		_deprecated_function( __METHOD__, '2.02' );
		return $sections;
	}

	public static function add_list_ajax() {
		_deprecated_function( __METHOD__, '2.02' );
	}

	public static function add_list() {
		_deprecated_function( __METHOD__, '2.02' );
	}

	public static function add_logic_row() {
		_deprecated_function( __METHOD__, '2.02' );
	}

	public static function get_field_values() {
		_deprecated_function( __METHOD__, '2.02' );
	}

	public static function setup_new_vars( $values ) {
		_deprecated_function( __METHOD__, '2.02' );
		return $values;
	}

	public static function setup_edit_vars( $values ) {
		_deprecated_function( __METHOD__, '2.02' );
		return $values;
	}

	public static function update_options( $options ) {
		_deprecated_function( __METHOD__, '2.02' );
		return $options;
	}


	public static function aweber_options( $values ) {
		_deprecated_function( __METHOD__, '2.02' );
	}
}
