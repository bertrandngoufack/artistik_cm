<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmStrpAuth {

	/**
	 * All of the form IDs with payment details in the URL params will be included in this array.
	 *
	 * @var array
	 */
	private static $form_ids = array();

	/**
	 * If returning from Stripe to authorize a payment, show the message.
	 * This is used for 3D secure and for Stripe link.
	 *
	 * @since 2.0
	 *
	 * @param string $html Form HTML that gets filtered through frm_filter_final_form.
	 * @return string
	 */
	public static function maybe_show_message( $html ) {
		$link_error = FrmAppHelper::simple_get( 'frm_link_error' );
		if ( $link_error ) {
			$message = '<div class="frm_error_style">' . self::get_message_for_stripe_link_code( $link_error ) . '</div>';
			self::insert_error_message( $message, $html );
			return $html;
		}

		$form_id = self::check_html_for_form_id_match( $html );
		if ( false === $form_id ) {
			return $html;
		}

		$details = FrmStrpUrlParamHelper::get_details_for_form( $form_id );
		if ( ! is_array( $details ) ) {
			return $html;
		}

		$atts = array(
			'fields' => FrmFieldsHelper::get_form_fields( $form_id ),
			'entry'  => $details['entry'],
		);
		self::prepare_success_atts( $atts );

		$intent  = $details['intent'];
		$payment = $details['payment'];

		if ( self::intent_has_failed_status( $intent ) ) {
			$message = '<div class="frm_error_style">' . $intent->last_payment_error->message . '</div>';
			self::insert_error_message( $message, $html );
			return $html;
		}

		$intent_is_processing = 'processing' === $intent->status;
		if ( $intent_is_processing ) {
			// Append an additional processing message to the end of the success message.
			$filter = function( $message ) {
				$stripe_settings = FrmStrpAppHelper::get_settings();
				$message        .= '<p>' . esc_html( $stripe_settings->settings->processing_message ) . '</p>';
				return $message;
			};
			add_filter( 'frm_content', $filter );
		}

		ob_start();
		FrmFormsController::run_on_submit_actions( $atts );
		$message = ob_get_contents();
		ob_end_clean();

		// Clean up the filter we added above so no other success messages get altered if there are multiple forms.
		if ( $intent_is_processing && isset( $filter ) ) {
			remove_filter( 'frm_content', $filter );
		}

		return $message;
	}

	/**
	 * Check if a payment failed.
	 *
	 * @since 3.1.4
	 *
	 * @param object $payment
	 * @param object $intent
	 * @return bool
	 */
	public static function payment_failed( $payment, $intent ) {
		if ( self::intent_has_failed_status( $intent ) ) {
			return true;
		}

		// The $intent will be "succeeded" with a failed payment when testing with the 4000000000000341 credit card.
		if ( 'payment_failed' === FrmAppHelper::simple_get( 'frm_link_error' ) && 'failed' === $payment->status ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if a payment or setup intent has failed.
	 *
	 * @param object $intent
	 * @return bool
	 */
	private static function intent_has_failed_status( $intent ) {
		return in_array( $intent->status, array( 'requires_source', 'requires_payment_method', 'canceled' ), true );
	}

	/**
	 * The frm_filter_final_form filter only passes form HTML as a string.
	 * To determine which form is being filtered, this function checks for the
	 * hidden form_id input. If there is a match, it returns the matching form id.
	 *
	 * @since 3.1.1
	 *
	 * @param string $html
	 * @return int|false Matching form id or false if there is no match.
	 */
	private static function check_html_for_form_id_match( $html ) {
		foreach ( self::$form_ids as $form_id ) {
			$substring = '<input type="hidden" name="form_id" value="' . $form_id . '"';
			if ( strpos( $html, $substring ) ) {
				return $form_id;
			}
		}

		return false;
	}

	/**
	 * @param string|int $form_id
	 * @return array|false
	 */
	private static function check_request_params( $form_id ) {
		if ( ! FrmStrpAppHelper::stripe_is_configured() ) {
			return false;
		}

		$details = FrmStrpUrlParamHelper::get_details_for_form( $form_id );
		if ( ! is_array( $details ) ) {
			return false;
		}

		$entry   = $details['entry'];
		$intent  = $details['intent'];
		$payment = $details['payment'];

		self::$form_ids[] = $form_id;

		return $details;
	}

	/**
	 * Translate an error code into a readable message for the front end.
	 * FrmStrpLinkRedirectHelper uses these codes to redirect errors that are then handled in self::maybe_show_message.
	 *
	 * @since 3.0
	 *
	 * @param string $code
	 * @return string
	 */
	private static function get_message_for_stripe_link_code( $code ) {
		switch ( $code ) {
			case 'intent_does_not_exist':
				return __( 'Payment intent does not exist.', 'formidable' );
			case 'unable_to_verify':
				return __( 'Unable to verify payment intent.', 'formidable' );
			case 'did_not_complete':
				return __( 'Payment did not complete.', 'formidable' );
			case 'no_payment_record':
				return __( 'Unable to find record of payment.', 'formidable' );
			case 'no_entry_found':
				return __( 'This form submission does not exist.', 'formidable' );
			case 'no_stripe_link_action':
				return __( 'This form is not configured for Stripe link payments.', 'formidable' );
			case 'create_subscription_failed':
				return __( 'Something went wrong when trying to create a subscription.', 'formidable' );
			case 'payment_failed':
				return __( 'Payment was not successfully processed.', 'formidable' );
			case 'amount_mismatch':
				return __( 'The payment amount does not match the expected amount.', 'formidable' );
		}
		return '';
	}

	/**
	 * Maybe update a pending 3D Secure payment after redirect.
	 *
	 * @since 2.07
	 *
	 * @param stdClass $payment
	 * @param string   $status
	 * @return void
	 */
	private static function maybe_update_3dsecure_payment( $payment, $status ) {
		if ( ! self::payment_is_one_time_and_pending( $payment ) ) {
			return;
		}

		$frm_payment = new FrmTransPayment();
		$frm_payment->update( $payment->id, array( 'status' => $status ) );
		FrmTransActionsController::trigger_payment_status_change( compact( 'status', 'payment' ) );
	}

	/**
	 * Check that a payment's status should change after 3D secure redirect.
	 * If it isn't pending, the status has already been updated, so there's no need to change anything.
	 * Subscriptions get updated with webhooks so no need to update here.
	 *
	 * @since 2.07
	 *
	 * @param stdClass $payment
	 * @return bool
	 */
	private static function payment_is_one_time_and_pending( $payment ) {
		return 'pending' === $payment->status && empty( $payment->sub_id );
	}

	/**
	 * Add the parameters the receiving functions are expecting.
	 *
	 * @since 2.0
	 *
	 * @param array $atts
	 * @return void
	 */
	private static function prepare_success_atts( &$atts ) {
		$atts['form']     = FrmForm::getOne( $atts['entry']->form_id );
		$atts['entry_id'] = $atts['entry']->id;

		$opt = 'success_action';
		$atts['conf_method'] = ! empty( $atts['form']->options[ $opt ] ) ? $atts['form']->options[ $opt ] : 'message';

		$actions = FrmFormsController::get_met_on_submit_actions( $atts, 'create' );
		if ( $actions ) {
			$action = reset( $actions );
			if ( ! empty( $action->post_content['success_action'] ) && 'message' === $action->post_content['success_action'] ) {
				$atts['conf_method'] = $action->post_content['success_action'];
			}
		}
	}

	/**
	 * Insert a message/error where the form styling will be applied.
	 *
	 * @since 2.0
	 */
	private static function insert_error_message( $message, &$form ) {
		$add_after = '<fieldset>';
		$pos = strpos( $form, $add_after );
		if ( $pos !== false ) {
			$form = substr_replace( $form, $add_after . $message, $pos, strlen( $add_after ) );
		}
	}

	/**
	 * Include the token if going between pages.
	 *
	 * @param object $form The form being submitted.
	 * @return void
	 */
	public static function add_hidden_token_field( $form ) {
		$posted_form = FrmAppHelper::get_param( 'form_id', 0, 'post', 'absint' );
		if ( $posted_form != $form->id || FrmFormsController::just_created_entry( $form->id ) ) {
			// Check to make sure the correct form was submitted.
			// Was an entry already created and the form should be loaded fresh?

			$intents = self::maybe_create_intents( $form->id );
			self::include_intents_in_form( $intents, $form );

			return;
		}

		if ( isset( $_POST['stripeToken'] ) ) {
			echo '<input type="hidden" name="stripeToken" value="' . esc_attr( wp_unslash( $_POST['stripeToken'] ) ) . '"/>';
			return;
		}

		if ( isset( $_POST['stripeMethod'] ) ) {
			echo '<input type="hidden" name="stripeMethod" value="' . esc_attr( wp_unslash( $_POST['stripeMethod'] ) ) . '"/>';
			return;
		}

		$auths = self::get_payment_intents( 'frmauth' . $form->id );
		foreach ( $auths as $auth ) {
			echo '<input type="hidden" name="frmauth' . esc_attr( $form->id ) . '[]" value="' . esc_attr( $auth ) . '" />';
		}

		$intents = self::get_payment_intents( 'frmintent' . $form->id );
		if ( ! empty( $intents ) ) {
			self::update_intent_pricing( $form->id, $intents, $_POST );
		} elseif ( empty( $auths ) ) {
			$intents = self::maybe_create_intents( $form->id );
		}

		self::include_intents_in_form( $intents, $form );
	}

	/**
	 * Include hidden fields with payment intent IDs in the form.
	 *
	 * @since 2.02
	 *
	 * @param array    $intents
	 * @param stdClass $form
	 * @return void
	 */
	private static function include_intents_in_form( $intents, $form ) {
		foreach ( $intents as $intent ) {
			if ( is_array( $intent ) ) {
				$id     = $intent['id'];
				$action = $intent['action'];
			} else {
				$id     = $intent;
				$action = '';
			}

			echo '<input type="hidden" name="frmintent' . esc_attr( $form->id ) . '[]" value="' . esc_attr( $id ) . '" data-action="' . esc_attr( $action ) . '" />';
		}
	}

	/**
	 * Check POST data for payment intents.
	 *
	 * @since 2.0
	 *
	 * @param string $name
	 * @return mixed
	 */
	public static function get_payment_intents( $name ) {
		if ( ! isset( $_POST[ $name ] ) ) {
			return array();
		}
		$intents = $_POST[ $name ];
		FrmAppHelper::sanitize_value( 'sanitize_text_field', $intents );
		return $intents;
	}

	/**
	 * Update pricing before authorizing.
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public static function update_intent_ajax() {
		check_ajax_referer( 'frm_strp_ajax', 'nonce' );
		$form = json_decode( stripslashes( $_POST['form'] ), true );
		self::format_form_data( $form );

		$form_id = absint( $form['form_id'] );
		$intents = isset( $form[ 'frmintent' . $form_id ] ) ? $form[ 'frmintent' . $form_id ] : array();

		if ( empty( $intents ) ) {
			wp_die();
		}

		if ( ! is_array( $intents ) ) {
			$intents = array( $intents );
		} else {
			foreach ( $intents as $k => $intent ) {
				if ( is_array( $intent ) && isset( $intent[ $k ] ) ) {
					$intents[ $k ] = $intent[ $k ];
				}
			}
		}

		self::update_intent_pricing( $form_id, $intents, $form );

		wp_die();
	}

	/**
	 * Update pricing on page turn and non-ajax validation.
	 *
	 * @since 2.0
	 *
	 * @param int   $form_id
	 * @param array $intents
	 * @param array $form_data
	 *
	 * @return void
	 */
	private static function update_intent_pricing( $form_id, &$intents, $form_data ) {
		if ( ! isset( $form_data['form_id'] ) || absint( $form_data['form_id'] ) !== (int) $form_id ) {
			return;
		}

		$actions = FrmStrpActionsController::get_actions_before_submit( $form_id );
		if ( empty( $actions ) || empty( $intents ) ) {
			return;
		}

		$form = FrmForm::getOne( $form_id );

		try {
			if ( ! FrmStrpAppHelper::call_stripe_helper_class( 'initialize_api' ) ) {
				return;
			}
		} catch ( Exception $e ) {
			// Intent was not created.
			return;
		}

		foreach ( $intents as $k => $intent ) {
			$intent_id       = explode( '_secret_', $intent )[0];
			$is_setup_intent = 0 === strpos( $intent_id, 'seti_' );
			if ( $is_setup_intent ) {
				continue;
			}

			$saved = FrmStrpAppHelper::call_stripe_helper_class( 'get_intent', $intent_id );
			if ( empty( $saved->metadata->action ) ) {
				continue;
			}
			foreach ( $actions as $action ) {
				if ( $saved->metadata->action != $action->ID ) {
					continue;
				}
				$intents[ $k ] = array(
					'id'     => $intent,
					'action' => $action->ID,
				);

				$amount = $action->post_content['amount'];
				if ( strpos( $amount, '[' ) === false ) {
					// The amount is static, so it doesn't need an update.
					continue;
				}

				// Update amount based on field shortcodes.
				$entry  = self::generate_false_entry( $form_data );
				$amount = FrmStrpActionsController::prepare_amount( $amount, compact( 'form', 'entry', 'action' ) );
				if ( $saved->amount == $amount || $amount == '000' ) {
					continue;
				}

				FrmStrpAppHelper::call_stripe_helper_class( 'update_intent', $intent_id, array( 'amount' => $amount ) );
			}
		}
	}

	/**
	 * Create an entry object with posted values.
	 *
	 * @since 2.0
	 *
	 * @param array $form_data
	 *
	 * @return stdClass
	 */
	private static function generate_false_entry( $form_data ) {
		$entry           = new stdClass();
		$entry->post_id  = 0;
		$entry->id       = 0;
		$entry->item_key = '';
		$entry->metas    = array();
		foreach ( $form_data as $k => $v ) {
			$k = sanitize_text_field( stripslashes( $k ) );
			$v = wp_unslash( $v );

			if ( $k === 'item_meta' ) {
				foreach ( $v as $f => $value ) {
					FrmAppHelper::sanitize_value( 'wp_kses_post', $value );
					$entry->metas[ absint( $f ) ] = $value;
				}
			} else {
				FrmAppHelper::sanitize_value( 'wp_kses_post', $v );
				$entry->{$k} = $v;
			}
		}
		return $entry;
	}

	/**
	 * Reformat the form data in name => value array.
	 *
	 * @since 2.0
	 *
	 * @param array $form
	 * @return void
	 */
	private static function format_form_data( &$form ) {
		$formatted = array();

		foreach ( $form as $input ) {
			$key = $input['name'];
			if ( isset( $formatted[ $key ] ) ) {
				if ( is_array( $formatted[ $key ] ) ) {
					$formatted[ $key ][] = $input['value'];
				} else {
					$formatted[ $key ] = array( $formatted[ $key ], $input['value'] );
				}
			} else {
				$formatted[ $key ] = $input['value'];
			}
		}

		parse_str( http_build_query( $formatted ), $form );
	}

	/**
	 * Create intents on form load when required.
	 * This only happens in two cases: For stripe link, and when processing a one-time payment before the entry is created.
	 *
	 * @since 2.0
	 *
	 * @param string|int $form_id
	 * @return array
	 */
	private static function maybe_create_intents( $form_id ) {
		$intents = array();
		$details = self::check_request_params( $form_id );

		if ( is_array( $details ) ) {
			$payment        = $details['payment'];
			$intent         = $details['intent'];
			$payment_failed = self::payment_failed( $payment, $intent );

			// Exit early if the request params are set.
			// This way an extra payment intent isn't created for Stripe Link.
			if ( ! $payment_failed ) {
				return $intents;
			}
		}

		if ( ! FrmStrpAppHelper::call_stripe_helper_class( 'initialize_api' ) ) {
			// Stripe is not configured, so don't create intents.
			return $intents;
		}

		$actions = FrmStrpActionsController::get_actions_before_submit( $form_id );
		self::add_amount_to_actions( $form_id, $actions );

		foreach ( $actions as $action ) {
			if ( ! self::requires_payment_intent_on_load( $action ) ) {
				continue;
			}

			if ( is_array( $details ) && self::intent_has_failed_status( $details['intent'] ) ) {
				$intents[] = array(
					'id'     => $details['intent']->client_secret,
					'action' => $action->ID,
				);
				continue;
			}

			$intent = self::create_intent( $action );
			if ( ! is_object( $intent ) ) {
				// A non-object is a string error message.
				// The error gets logged to results.log so we can just skip it.
				// Reasons it could fail is because a payment method type was specified that will not work.
				// A payment method type may not work because of a currency conflict, or because it isn't enabled.
				// Or the payment method type could be an incorrect value.
				// When using Stripe Connect, the error will just say "Unable to create intent".
				// In this case, you can find the full error message in the Stripe dashboard.
				continue;
			}

			$intents[] = array(
				'id'     => $intent->client_secret,
				'action' => $action->ID,
			);
		}

		return $intents;
	}

	/**
	 * Create a payment intent for Stripe link or when processing a payment before the entry is created.
	 *
	 * @since 3.0 This code was moved out of self::maybe_create_intents into a new function.
	 *
	 * @param WP_Post $action
	 * @return mixed
	 */
	private static function create_intent( $action ) {
		$amount   = $action->post_content['amount'];
		$currency = $action->post_content['currency'];
		if ( $amount == '000' ) {
			$amount = in_array( strtolower( $currency ), array( 'aud', 'cad', 'eur', 'gbp', 'usd' ), true ) ? 100 : 1000; // Create the intent when the form loads.
		}

		$use_stripe_link = self::uses_stripe_link( $action );
		if ( $use_stripe_link && 'recurring' === $action->post_content['type'] ) {
			$payment_method_types = FrmStrpPaymentTypeHandler::get_payment_method_types( $action );
			return self::create_setup_intent( $payment_method_types );
		}

		$new_charge = array(
			'amount'   => $amount,
			'currency' => $currency,
			'metadata' => array( 'action' => $action->ID ),
		);

		if ( FrmStrpPaymentTypeHandler::should_use_automatic_payment_methods( $action ) ) {
			$new_charge['automatic_payment_methods'] = array( 'enabled' => true );
		} else {
			$payment_method_types               = FrmStrpPaymentTypeHandler::get_payment_method_types( $action );
			$new_charge['payment_method_types'] = $payment_method_types;
		}

		$use_manual_capture = ( isset( $action->post_content['capture'] ) && 'authorize' === $action->post_content['capture'] ) || ! $use_stripe_link;
		if ( $use_manual_capture ) {
			$new_charge['capture_method'] = 'manual'; // Authorize only and capture after submit.
		}

		return FrmStrpAppHelper::call_stripe_helper_class( 'create_intent', $new_charge );
	}

	/**
	 * Create a customer and an associated setup intent for a recurring Stripe link payment.
	 *
	 * @since 3.0
	 *
	 * @param array $payment_method_types
	 * @return object|false
	 */
	private static function create_setup_intent( $payment_method_types ) {
		$payment_info = array(
			'user_id' => FrmTransAppHelper::get_user_id_for_current_payment(),
		);

		// We need to add a customer to support subscriptions with link.
		$customer = FrmStrpAppHelper::call_stripe_helper_class( 'get_customer', $payment_info );
		if ( ! is_object( $customer ) ) {
			return false;
		}

		return FrmStrpAppHelper::call_stripe_helper_class( 'create_setup_intent', $customer->id, $payment_method_types );
	}

	/**
	 * @since 2.0
	 *
	 * @param string|int $form_id
	 * @param array      $actions
	 * @return void
	 */
	private static function add_amount_to_actions( $form_id, &$actions ) {
		if ( empty( $actions ) ) {
			return;
		}
		$form = FrmForm::getOne( $form_id );

		foreach ( $actions as $k => $action ) {
			$amount = self::get_amount_before_submit( compact( 'action', 'form' ) );
			$actions[ $k ]->post_content['amount'] = $amount;
		}
	}

	/**
	 * @since 2.0
	 *
	 * @param array $atts
	 * @return string
	 */
	private static function get_amount_before_submit( $atts ) {
		$amount = $atts['action']->post_content['amount'];
		return FrmStrpActionsController::prepare_amount( $atts['action']->post_content['amount'], $atts );
	}

	/**
	 * Should the payment be processed before or after submit?
	 *
	 * @since 2.0
	 *
	 * @return bool
	 */
	public static function process_payment_before() {
		$settings = FrmStrpAppHelper::get_settings();
		return $settings->settings->process === 'before';
	}

	/**
	 * Returns whether or not a specific action needs to create a payment intent when the form loads.
	 * This is only true when using stripe link or when processing a one-time payment before the entry is created.
	 *
	 * @since 3.0
	 *
	 * @param WP_Post $action
	 * @return bool
	 */
	private static function requires_payment_intent_on_load( $action ) {
		if ( self::uses_stripe_link( $action ) ) {
			// Stripe links always require a payment intent on load.
			return true;
		}

		// Other than for Stripe links, one-time payments that process before the entry is created need to create the intent on load.
		return self::process_payment_before() && $action->post_content['type'] !== 'recurring';
	}

	/**
	 * Check if an action is set to use a Stripe link. This is based on the "Use previously saved card" toggle in Stripe payment actions.
	 *
	 * @since 3.0
	 *
	 * @param WP_Post $action
	 * @return bool
	 */
	private static function uses_stripe_link( $action ) {
		return ! empty( $action->post_content['stripe_link'] );
	}

	/**
	 * @since 2.0
	 *
	 * @param array $atts Includes 'customer', 'entry', 'action', 'amount'.
	 * @param int   $intent_id
	 */
	public static function redirect_auth( $atts, $intent_id ) {
		$data = array(
			'return_url' => self::return_url_for_3d_secure( (int) $atts['entry']->id ),
		);
		$a    = FrmStrpAppHelper::call_stripe_helper_class( 'confirm_intent', $intent_id, $data );
		global $frm_strp_redirect_url;
		$confirm_url           = esc_url_raw( $a->next_action->redirect_to_url->url );
		$frm_strp_redirect_url = $confirm_url;

		self::add_temporary_referer_meta( (int) $atts['entry']->id );

		add_filter( 'frm_redirect_url', 'FrmStrpAuth::set_redirect_url' );
		add_filter( 'frm_success_filter', 'FrmStrpAuth::trigger_redirect' );

		return $confirm_url;
	}

	/**
	 * Triggered by the frm_redirect_url hook.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public static function set_redirect_url( $url ) {
		global $frm_strp_redirect_url;
		if ( $frm_strp_redirect_url ) {
			$url = $frm_strp_redirect_url;
		}
		return $url;
	}

	/**
	 * Triggered by the frm_success_filter hook.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public static function trigger_redirect() {
		return 'redirect';
	}

	/**
	 * Get the URL to return to after a payment is complete.
	 * This may either use the success URL on redirect, or the message on success.
	 * It shouldn't be confused for the Stripe link return URL. It isn't used for that. That uses the frmstrplinkreturn AJAX action instead.
	 *
	 * @since 2.0
	 *
	 * @param array $atts
	 * @return string
	 */
	public static function return_url( $atts ) {
		$atts = array(
			'entry' => $atts['entry'],
		);
		self::prepare_success_atts( $atts );

		if ( $atts['conf_method'] === 'redirect' ) {
			$redirect = self::get_redirect_url( $atts );
		} else {
			$redirect = self::get_message_url( $atts );
		}

		return $redirect;
	}

	/**
	 * Get the URL to return to after the 3D secure redirect.
	 * This URL processes the result and updates the payment status before handling the final redirect.
	 *
	 * @param int $entry_id
	 * @return string
	 */
	private static function return_url_for_3d_secure( $entry_id ) {
		$redirect = admin_url( 'admin-ajax.php', is_ssl() ? 'admin' : 'http' );
		$redirect = apply_filters( 'frm_ajax_url', $redirect );
		$redirect = add_query_arg( 'action', 'frmstrp3dsecurereturn', $redirect );
		$redirect = add_query_arg( 'frmstrp', $entry_id, $redirect );
		return $redirect;
	}

	/**
	 * If the form should redirect, get the url to redirect to.
	 *
	 * @since 2.0
	 *
	 * @param array $atts {
	 *     @type stdClass $form
	 *     @type stdClass $entry
	 * }
	 * @return string
	 */
	private static function get_redirect_url( $atts ) {
		if ( is_callable( 'FrmFormsController::get_met_on_submit_actions' ) ) {
			$actions = FrmFormsController::get_met_on_submit_actions(
				$atts
			);
			if ( $actions ) {
				$success_url = reset( $actions )->post_content['success_url'];
			}
		}

		if ( empty( $success_url ) ) {
			$success_url = $atts['form']->options['success_url'];
		}

		$success_url = trim( $success_url );
		$success_url = apply_filters( 'frm_content', $success_url, $atts['form'], $atts['entry'] );
		$success_url = do_shortcode( $success_url );
		$atts['id']  = $atts['entry']->id;

		add_filter( 'frm_redirect_url', 'FrmEntriesController::prepare_redirect_url' );
		return apply_filters( 'frm_redirect_url', $success_url, $atts['form'], $atts );
	}

	/**
	 * If the form should should a message, append it to the success url.
	 *
	 * @since 2.0
	 *
	 * @param array $atts
	 * @return string
	 */
	private static function get_message_url( $atts ) {
		$url = self::get_referer_url( $atts['entry_id'], false );
		if ( false === $url ) {
			$url = FrmAppHelper::get_server_value( 'HTTP_REFERER' );
		}
		return add_query_arg( array( 'frmstrp' => $atts['entry_id'] ), $url );
	}

	/**
	 * @since 3.1.1
	 *
	 * @param string|int $entry_id
	 * @param bool       $delete_meta
	 * @return string|false
	 */
	public static function get_referer_url( $entry_id, $delete_meta = true ) {
		$row = FrmDb::get_row(
			'frm_item_metas',
			array(
				'field_id'        => 0,
				'item_id'         => $entry_id,
				'meta_value LIKE' => '{"referer":',
			),
			'id, meta_value'
		);
		if ( ! $row ) {
			return false;
		}

		$meta = $row->meta_value;
		$meta = json_decode( $meta, true );

		if ( ! is_array( $meta ) || empty( $meta['referer'] ) ) {
			return false;
		}

		if ( $delete_meta ) {
			self::delete_temporary_referer_meta( (int) $row->id );
		}

		return $meta['referer'];
	}

	/**
	 * Delete the referer meta as we'll no longer need it.
	 *
	 * @param int $row_id
	 * @return void
	 */
	private static function delete_temporary_referer_meta( $row_id ) {
		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'frm_item_metas', array( 'id' => $row_id ) );
	}

	/**
	 * Update 3D Secure payment data on redirect, then handle the confirmation action afterward.
	 *
	 * @since 3.1.4
	 */
	public static function handle_3d_secure_return_url() {
		$entry_id = FrmAppHelper::simple_get( 'frmstrp' );
		if ( ! $entry_id ) {
			wp_die( 0 );
		}

		$entry = FrmEntry::getOne( $entry_id );
		if ( ! $entry ) {
			wp_die( 0 );
		}

		$form_id  = $entry->form_id;
		$details  = FrmStrpUrlParamHelper::get_details_for_form( $form_id );
		if ( ! is_array( $details ) ) {
			wp_die( 0 );
		}

		$entry   = $details['entry'];
		$intent  = $details['intent'];
		$payment = $details['payment'];

		$failed = self::intent_has_failed_status( $intent );
		if ( $failed ) {
			self::maybe_update_3dsecure_payment( $payment, 'failed' );
		} else {
			$new_payment_status = 'requires_capture' === $intent->status ? 'authorized' : 'complete';
			self::maybe_update_3dsecure_payment( $payment, $new_payment_status );
		}

		// Get a fresh entry object so [if] shortcodes work as expected.
		$entry = FrmEntry::getOne( $entry->id, true );

		$atts = compact( 'entry' );
		self::prepare_success_atts( $atts );

		if ( $failed ) {
			// Redirect back to the form with error messages on failure.
			$redirect = self::get_message_url( $atts );
			$redirect = add_query_arg( 'payment_intent', $intent->id, $redirect );
			$redirect = add_query_arg( 'payment_intent_client_secret', $intent->client_secret, $redirect );
			wp_redirect( $redirect );
			die();
		}

		if ( $atts['conf_method'] === 'redirect' ) {
			$redirect = self::get_redirect_url( $atts );
		} else {
			$redirect = self::get_message_url( $atts );
			$redirect = add_query_arg( 'payment_intent', $intent->id, $redirect );
			$redirect = add_query_arg( 'payment_intent_client_secret', $intent->client_secret, $redirect );
		}

		// Delete the temporary referer meta as it is no longer needed.
		self::get_referer_url( $entry->id );

		wp_redirect( $redirect );
		die();
	}

	/**
	 * Set the referer URL as field ID 0 in entry meta.
	 * This is required for iDEAL, sofort, and other payment methods that include an additional redirect step.
	 * It is used for the redirect in FrmStrpLinkRedirectHelper.
	 * It is deleted after the redirect happens.
	 *
	 * @since 3.1
	 * @since 3.1.4 This was moved from FrmStrpLinkController to FrmStrpAuth.php and made public.
	 *
	 * @param int $entry_id
	 * @return void
	 */
	public static function add_temporary_referer_meta( $entry_id ) {
		$referer                          = FrmAppHelper::get_server_value( 'HTTP_REFERER' );
		$query_args_to_strip_from_referer = array(
			'frm_link_error',
			'payment_intent',
			'payment_intent_client_secret',
			'setup_intent',
			'setup_intent_client_secret',
		);
		foreach ( $query_args_to_strip_from_referer as $arg ) {
			$referer = remove_query_arg( $arg, $referer );
		}

		$meta_value = json_encode( compact( 'referer' ) );
		FrmEntryMeta::add_entry_meta( $entry_id, 0, '', $meta_value );
	}
}
