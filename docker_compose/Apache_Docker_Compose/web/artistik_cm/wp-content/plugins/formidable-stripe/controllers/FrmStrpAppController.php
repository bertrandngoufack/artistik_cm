<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
class FrmStrpAppController {

	/**
	 * Flag to delete the previous pay entry.
	 *
	 * @since 2.08
	 *
	 * @var bool
	 */
	private static $delete_pay_entry = false;

	/**
	 * @return void
	 */
	public static function load_lang() {
		load_plugin_textdomain( 'formidable-stripe', false, FrmStrpAppHelper::plugin_folder() . '/languages/' );
		add_filter( 'load_textdomain_mofile', 'FrmStrpAppController::overload_payments_mofile', 10, 2 );
	}

	/**
	 * Use the formidable-stripe .mo file whenever the formidable-payments text domain is used.
	 * The formidable-payments strings are all included in the formidable-stripe.pot file.
	 *
	 * @since 3.1.4
	 *
	 * @param string $mofile
	 * @param string $domain
	 * @return string
	 */
	public static function overload_payments_mofile( $mofile, $domain ) {
		if ( 'formidable-payments' !== $domain ) {
			return $mofile;
		}
		return str_replace( 'formidable-payments', 'formidable-stripe', $mofile );
	}

	/**
	 * Include the updater and show the Stripe connect message.
	 *
	 * @return void
	 */
	public static function include_updater() {
		if ( class_exists( 'FrmAddon' ) ) {
			FrmStrpUpdate::load_hooks();

			if ( self::should_show_stripe_connect_message() ) {
				self::maybe_add_stripe_connect_inbox_message();
			}
		}
		self::install();
	}

	/**
	 * Install required tables.
	 *
	 * @param mixed $old_db_version
	 * @return void
	 */
	public static function install( $old_db_version = false ) {
		FrmTransAppController::install( $old_db_version );
	}

	/**
	 * Add a Stripe gateway for the payment action.
	 *
	 * @param array $gateways
	 * @return array
	 */
	public static function add_gateway( $gateways ) {
		$gateways['stripe'] = array(
			'label'      => 'Stripe',
			'user_label' => __( 'Payment', 'formidable' ),
			'class'      => 'Strp',
			'recurring'  => true,
			'include'    => array(
				'billing_first_name',
				'billing_last_name',
				'credit_card',
				'billing_address',
			),
		);
		return $gateways;
	}

	/**
	 * Add REST API routing for deleting credit cards.
	 */
	public static function add_api_routes() {
		register_rest_route(
			'frm-strp/v1',
			'/card/(?P<id>[a-z0-9 _]+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( 'FrmStrpPaymentsController', 'delete_card' ),
				'permission_callback' => 'is_user_logged_in',
			)
		);
	}

	/**
	 * Check if Stripe connect has been configured for live payments.
	 *
	 * @return bool
	 */
	private static function should_show_stripe_connect_message() {
		$is_setup = FrmStrpConnectHelper::stripe_connect_is_setup( 'live' );
		return ! $is_setup && self::a_deprecated_stripe_api_key_is_filled_in();
	}

	/**
	 * Check if at least one Stripe API key is filled in.
	 *
	 * @since 3.1.5
	 * @return bool True if any of the deprecated keys are filled.
	 */
	private static function a_deprecated_stripe_api_key_is_filled_in() {
		$settings        = FrmStrpAppHelper::get_settings();
		$deprecated_keys = array(
			'test_secret',
			'test_publish',
			'live_secret',
			'live_publish',
		);
		foreach ( $deprecated_keys as $key ) {
			if ( ! empty( $settings->settings->$key ) ) {
				return true;
			}
		}

		// None of the deprecated keys are filled, so return false.
		return false;
	}

	/**
	 * @return void
	 */
	private static function maybe_add_stripe_connect_inbox_message() {
		if ( ! class_exists( 'FrmInbox' ) ) {
			return;
		}

		$has_passed_cutoff_date = ! FrmStrpAppHelper::stripe_still_supports_api_keys();
		if ( $has_passed_cutoff_date ) {
			$subject = 'Stripe API keys are no longer supported';
			$message = 'This site is using Stripe API keys. Stripe no longer supports API keys. Support for API keys will be fully removed in the next release of Formidable Stripe.';
		} else {
			$subject = 'Stripe API keys will no longer be supported';
			$message = 'This site is using Stripe API keys. Stripe API keys are now deprecated. After June, Stripe will no longer allow keys.';
		}

		$url   = self::get_stripe_connect_settings_url();
		$inbox = new FrmInbox();
		$inbox->add_message(
			array(
				'key'     => 'deprecated_strp_api_keys',
				'force'   => $has_passed_cutoff_date,
				'subject' => $subject,
				'message' => $message,
				'icon'    => 'frm_report_problem_icon',
				'cta'     => '<a class="button-secondary frm-button-secondary" href="' . esc_url_raw( $url ) . '">Connect Securely Now</a>',
			)
		);
	}

	/**
	 * Get a URL to the Stripe section of the global settings page.
	 *
	 * @return string
	 */
	private static function get_stripe_connect_settings_url() {
		return admin_url( 'admin.php?page=formidable-settings&t=stripe_settings' );
	}

	/**
	 * @since 3.1.5
	 *
	 * @param bool  $is_for_user
	 * @param array $who
	 * @return bool
	 */
	public static function inbox_message_is_for_user( $is_for_user, $who ) {
		if ( in_array( 'stripe_keys', $who, true ) ) {
			$is_for_user = self::should_show_stripe_connect_message();
		}
		return $is_for_user;
	}

	/**
	 * Maybe add payment error to the form errors data.
	 *
	 * @since 2.06
	 *
	 * @param array $errors Errors data. Is empty array if no errors found.
	 * @param array $params Form params. See {@FrmForm::get_params()}.
	 * @return array
	 */
	public static function maybe_add_payment_error( $errors, $params ) {
		if ( intval( $params['posted_form_id'] ) !== intval( $params['form_id'] ) ) {
			// Form is not submitted.
			$errors = self::maybe_add_payment_error_on_redirect( $errors, (int) $params['form_id'] );
			return $errors;
		}

		if ( FrmStrpConnectHelper::$latest_error_from_stripe_connect ) {
			$error_message = FrmStrpConnectHelper::$latest_error_from_stripe_connect;
		} else {
			global $frm_vars;
			$error_message = ! empty( $frm_vars['frm_trans']['error'] ) ? $frm_vars['frm_trans']['error'] : '';
		}

		if ( ! $error_message ) {
			return $errors;
		}

		$cc_field_id = self::get_credit_card_field_id_from_paged_form( $params['form_id'] );
		if ( false === $cc_field_id ) {
			return $errors;
		}

		if ( ! isset( $errors[ 'field' . $cc_field_id ] ) ) {
			// Do not update error message if that field has error already.
			$errors[ 'field' . $cc_field_id ] = $error_message;
			self::setup_form_after_payment_error( (int) $params['form_id'], (int) $params['id'], $errors );
		}

		return $errors;
	}

	/**
	 * Handle 3D secure and Stripe Link redirect failures.
	 * When a payment fails, the entry is deleted, and the previous entry's values are loaded in the form.
	 *
	 * @param array $errors
	 * @param int   $form_id
	 * @return array
	 */
	private static function maybe_add_payment_error_on_redirect( $errors, $form_id ) {
		$details = FrmStrpUrlParamHelper::get_details_for_form( $form_id );
		if ( ! is_array( $details ) ) {
			return $errors;
		}

		$entry          = $details['entry'];
		$intent         = $details['intent'];
		$payment        = $details['payment'];
		$payment_failed = FrmStrpAuth::payment_failed( $payment, $intent );

		// Only add the payment error if the payment failed.
		if ( ! $payment_failed ) {
			return $errors;
		}

		$cc_field_id = FrmDb::get_var(
			'frm_fields',
			array(
				'type'    => 'credit_card',
				'form_id' => $entry->form_id,
			)
		);
		if ( ! $cc_field_id ) {
			return $errors;
		}

		$is_setup_intent = 0 === strpos( $intent->id, 'seti_' );
		if ( $is_setup_intent ) {
			$errors[ 'field' . $cc_field_id ] = is_object( $intent->last_setup_error ) ? $intent->last_setup_error->message : '';
		} else {
			$errors[ 'field' . $cc_field_id ] = is_object( $intent->last_payment_error ) ? $intent->last_payment_error->message : '';
		}

		if ( ! $errors[ 'field' . $cc_field_id ] ) {
			$errors[ 'field' . $cc_field_id ] = 'Payment was not successfully processed.';
		}

		global $frm_vars;
		$frm_vars['frm_trans']['pay_entry'] = $entry;

		self::setup_form_after_payment_error( (int) $entry->form_id, (int) $entry->id, $errors );

		add_filter( 'frm_setup_new_fields_vars', 'FrmTransActionsController::fill_entry_from_previous', 20, 2 );

		return $errors;
	}

	/**
	 * Reset a form after a payment fails.
	 *
	 * @since 2.07
	 *
	 * @param int                  $form_id
	 * @param int                  $entry_id
	 * @param array<string,string> $errors
	 * @return void
	 */
	private static function setup_form_after_payment_error( $form_id, $entry_id, $errors ) {
		$form       = FrmForm::getOne( $form_id );
		$save_draft = ! empty( $form->options['save_draft'] );

		global $frm_vars;
		$frm_vars['created_entries'][ $form_id ]['errors'] = $errors;

		$_POST[ 'frm_page_order_' . $form_id ] = true; // Set to true to get FrmProFieldsHelper::get_page_with_error() run

		if ( ! $save_draft ) {
			// If draft saving is not on, delete the entry.
			self::$delete_pay_entry = true;
			return;
		}

		// If draft saving is on, load the draft entry.
		$frm_vars['created_entries'][ $form_id ]['entry_id'] = $entry_id;
		add_action(
			'frm_filter_final_form',
			/**
			 * Set the entry back to draft status after error.
			 *
			 * @param string $html
			 * @param int    $entry_id
			 * @return string
			 */
			function( $html ) use ( $entry_id ) {
				global $wpdb;
				$wpdb->update( $wpdb->prefix . 'frm_items', array( 'is_draft' => 1 ), array( 'id' => $entry_id ) );
				return $html;
			}
		);

	}

	/**
	 * Gets Credit card field ID from a paged form.
	 *
	 * @since 2.06
	 *
	 * @param int $form_id Form ID.
	 * @return int|false Return `false` if form is not paged and there is no Credit card field.
	 */
	private static function get_credit_card_field_id_from_paged_form( $form_id ) {
		$fields      = FrmField::get_all_for_form( $form_id ); // This result is cached and used when showing fields.
		$cc_field_id = false;
		$has_break   = false;

		foreach ( $fields as $field ) {
			if ( 'credit_card' === $field->type ) {
				$cc_field_id = $field->id;
			} elseif ( 'break' === $field->type ) {
				$has_break = true;
			}

			unset( $field );
		}

		if ( ! $has_break ) {
			return false;
		}

		return $cc_field_id;
	}

	/**
	 * Maybe delete the previous pay entry when error occurs.
	 *
	 * @since 2.08
	 *
	 * @param array  $values Entry edit values.
	 * @param object $field  Field object.
	 * @return array
	 */
	public static function maybe_delete_pay_entry( $values, $field ) {
		if ( self::$delete_pay_entry ) {
			self::$delete_pay_entry = false;
			return FrmTransActionsController::fill_entry_from_previous( $values, $field );
		}
		return $values;
	}

	/**
	 * Remove Stripe database items after uninstall.
	 *
	 * @since 2.07
	 *
	 * @return void
	 */
	public static function uninstall() {
		if ( ! current_user_can( 'administrator' ) ) {
			$frm_settings = FrmAppHelper::get_settings();
			wp_die( esc_html( $frm_settings->admin_permission ) );
		}

		$options_to_delete = array(
			FrmStrpEventsController::$events_to_skip_option_name,
			'frm_strp_options',
		);

		$modes            = array( 'test', 'live' );
		$option_name_keys = array( 'account_id', 'client_password', 'server_password', 'details_submitted' );
		foreach ( $modes as $mode ) {
			foreach ( $option_name_keys as $key ) {
				$options_to_delete[] = 'frm_strp_connect_' . $key . '_' . $mode;
			}
		}

		foreach ( $options_to_delete as $option_name ) {
			delete_option( $option_name );
		}
	}
}
