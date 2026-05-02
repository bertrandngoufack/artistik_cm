<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
/**
 * @since 3.0
 */
class FrmStrpLinkController {

	/**
	 * Process the form input and call handle_one_time_stripe_link_return_url if all of the required data is being submitted.
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public static function handle_return_url() {
		$intent_id     = FrmAppHelper::simple_get( 'payment_intent' );
		$client_secret = FrmAppHelper::simple_get( 'payment_intent_client_secret' );
		$status        = FrmAppHelper::simple_get( 'redirect_status' );

		/**
		 * "succeeded" is used for cards and Link.
		 * "pending" happens for bank redirect types (iDEAL, Bancontact, SOFORT).
		 * No redirect status is valid as well (for Affirm payments).
		 */
		if ( $intent_id && $client_secret && in_array( $status, array( 'pending', 'succeeded', 'failed', '' ), true ) ) {
			self::handle_one_time_stripe_link_return_url( $intent_id, $client_secret );
			die();
		}

		$setup_id      = FrmAppHelper::simple_get( 'setup_intent' );
		$client_secret = FrmAppHelper::simple_get( 'setup_intent_client_secret' );

		if ( $setup_id && $client_secret && in_array( $status, array( 'succeeded', 'failed' ), true ) ) {
			self::handle_recurring_stripe_link_return_url( $setup_id, $client_secret );
			die();
		}

		wp_die();
	}

	/**
	 * Redirect the user after they return to the return URL based on the status of the payment intent information passed with the request.
	 * This will redirect and get handled by FrmStrpAuth::maybe_show_message or possibly by redirected if there is a success URL set.
	 * If the setup intent is completed, the payment will be changed from pending as well.
	 *
	 * @since 3.0
	 *
	 * @param string $intent_id
	 * @param string $client_secret
	 * @return void
	 */
	private static function handle_one_time_stripe_link_return_url( $intent_id, $client_secret ) {
		$redirect_helper = new FrmStrpLinkRedirectHelper( $intent_id, $client_secret );
		$frm_payment     = new FrmTransPayment();

		$payment = $frm_payment->get_one_by( $intent_id, 'receipt_id' );
		if ( ! $payment ) {
			$redirect_helper->handle_error( 'no_payment_record' );
			die();
		}

		$intent = FrmStrpAppHelper::call_stripe_helper_class( 'get_intent', $intent_id );
		if ( ! is_object( $intent ) ) {
			$redirect_helper->handle_error( 'intent_does_not_exist' );
			die();
		}

		if ( $client_secret !== $intent->client_secret ) {
			// Do an extra check against the client secret so the request isn't as easy to spoof.
			$redirect_helper->handle_error( 'unable_to_verify' );
			die();
		}

		$entry = FrmEntry::getOne( $payment->item_id );

		if ( ! $entry ) {
			$redirect_helper->handle_error( 'no_entry_found' );
			die();
		}

		$redirect_helper->set_entry_id( $entry->id );

		$action = FrmStrpActionsController::get_stripe_link_action( $entry->form_id );
		if ( ! $action ) {
			$redirect_helper->handle_error( 'no_stripe_link_action' );
			die();
		}

		$currency        = FrmTransLiteAppHelper::get_action_setting( 'currency', array( 'payment' => $payment ) );
		$currency        = FrmCurrencyHelper::get_currency( $currency );
		$actual_amount   = intval( $intent->amount );
		$expected_amount = round( floatval( $payment->amount ), 2 );

		if ( 0 !== $currency['decimals'] ) {
			// Convert 10 to 1000 for example for Stripe.
			// But avoid this for a 0-decimal currency like JPY.
			$expected_amount *= 100;
		}

		$expected_amount = intval( round( $expected_amount ) );

		if ( $expected_amount !== $actual_amount ) {
			$redirect_helper->handle_error( 'amount_mismatch' );
			die();
		}

		if ( ! in_array( $intent->status, array( 'requires_capture', 'succeeded' ), true ) ) {
			if ( 'processing' === $intent->status ) {
				FrmTransPaymentsController::change_payment_status( $payment, 'processing' );
				$redirect_helper->handle_success( $entry, '' );
				die();
			}

			FrmTransPaymentsController::change_payment_status( $payment, 'failed' );

			$payment_failed = 'requires_payment_method' === $intent->status && 'payment_intent_authentication_failure' === $intent->last_payment_error->code;
			$error_type     = $payment_failed ? 'payment_failed' : 'did_not_complete';

			$redirect_helper->handle_error( $error_type );
			die();
		}

		$status                       = 'succeeded' === $intent->status ? 'complete' : 'authorized';
		$new_payment_values           = (array) $payment;
		$new_payment_values['status'] = $status;

		if ( 'complete' === $status ) {
			$charge                           = reset( $intent->charges->data );
			$new_payment_values['receipt_id'] = $charge->id;
		}

		self::maybe_update_intent( $intent, $action, $entry );

		if ( $status !== $payment->status ) {
			$payment_status_still_needs_to_update = self::payment_status_still_needs_to_update( $payment->id, $status );
		} else {
			$payment_status_still_needs_to_update = false;
		}

		$updated = $frm_payment->update( $payment->id, $new_payment_values );

		if ( $status !== $payment->status && $updated && $payment_status_still_needs_to_update ) {
			FrmTransActionsController::trigger_payment_status_change( compact( 'status', 'payment' ) );
		}

		$redirect_helper->handle_success( $entry, isset( $charge ) ? $charge->id : '' );
		die();
	}

	/**
	 * @since 3.1.6
	 *
	 * @param int    $payment_id
	 * @param string $status
	 * @return bool
	 */
	private static function payment_status_still_needs_to_update( $payment_id, $status ) {
		$frm_payment = new FrmTransPayment();
		$payment     = $frm_payment->get_one( $payment_id );
		return $payment->status !== $status;
	}

	/**
	 * Try to add the description to a Stripe link payment after it was confirmed.
	 *
	 * @param object           $intent
	 * @param WP_Post|stdClass $action
	 * @param stdClass         $entry
	 * @return void
	 */
	private static function maybe_update_intent( $intent, $action, $entry ) {
		if ( empty( $action->post_content['description'] ) ) {
			return;
		}

		$shortcode_atts = array(
			'entry' => $entry,
			'form'  => $entry->form_id,
			'value' => $action->post_content['description'],
		);
		$new_values = array( 'description' => FrmTransAppHelper::process_shortcodes( $shortcode_atts ) );
		FrmStrpAppHelper::call_stripe_helper_class( 'update_intent', $intent->id, $new_values );
	}

	/**
	 * Handle return URL for a stripe link recurring payment which uses setup intents.
	 * This will redirect and get handled by FrmStrpAuth::maybe_show_message or possibly by redirected if there is a success URL set.
	 * If the setup intent is completed, the subscription will be created as well.
	 *
	 * @since 3.0
	 *
	 * @param string $setup_id
	 * @param string $client_secret
	 * @return void
	 */
	private static function handle_recurring_stripe_link_return_url( $setup_id, $client_secret ) {
		$redirect_helper = new FrmStrpLinkRedirectHelper( $setup_id, $client_secret );
		$frm_payment     = new FrmTransPayment();
		$payment         = $frm_payment->get_one_by( $setup_id, 'receipt_id' );

		if ( ! is_object( $payment ) ) {
			$redirect_helper->handle_error( 'no_payment_record' );
			die();
		}

		// Verify the setup intent.
		$setup_intent = FrmStrpAppHelper::call_stripe_helper_class( 'get_setup_intent', $setup_id );
		if ( ! is_object( $setup_intent ) ) {
			$redirect_helper->handle_error( 'intent_does_not_exist' );
			die();
		}

		// Verify the client secret.
		if ( $setup_intent->client_secret !== $client_secret ) {
			$redirect_helper->handle_error( 'unable_to_verify' );
			die();
		}

		// Verify the entry.
		$entry = FrmEntry::getOne( $payment->item_id );
		if ( ! is_object( $entry ) ) {
			$redirect_helper->handle_error( 'no_entry_found' );
			die();
		}

		$redirect_helper->set_entry_id( $entry->id );

		// Verify it's an action with Stripe link enabled.
		$action = FrmStrpActionsController::get_stripe_link_action( $entry->form_id );
		if ( ! is_object( $action ) ) {
			$redirect_helper->handle_error( 'no_stripe_link_action' );
			die();
		}

		$customer_id       = $setup_intent->customer;
		$payment_method_id = self::get_link_payment_method( $setup_intent );

		if ( ! $payment_method_id ) {
			FrmTransPaymentsController::change_payment_status( $payment, 'failed' );
			$redirect_helper->handle_error( 'did_not_complete' );
			die();
		}

		$amount     = $payment->amount * 100;
		$new_charge = array(
			'customer'               => $customer_id,
			'default_payment_method' => $payment_method_id,
			'expand'                 => array( 'latest_invoice.charge' ),
			'plan'                   => FrmStrpSubscriptionHelper::get_plan_from_atts( compact( 'action', 'amount' ) ),
		);

		if ( ! FrmStrpPaymentTypeHandler::should_use_automatic_payment_methods( $action ) ) {
			$new_charge['payment_settings'] = array(
				'payment_method_types' => FrmStrpPaymentTypeHandler::get_payment_method_types( $action ),
			);
		}

		$atts = array(
			'action' => $action,
			'entry'  => $entry,
		);

		$trial_end = FrmStrpActionsController::get_trial_end_time( $atts );
		if ( $trial_end ) {
			$new_charge['trial_end'] = $trial_end;
		}

		$subscription = FrmStrpAppHelper::call_stripe_helper_class( 'create_subscription', $new_charge );
		$subscription = FrmStrpSubscriptionHelper::maybe_create_missing_plan_and_create_subscription( $subscription, $new_charge, $action, $amount );

		if ( ! is_object( $subscription ) ) {
			$redirect_helper->handle_error( 'create_subscription_failed' );
			die();
		}

		if ( 'succeeded' !== $setup_intent->status ) {
			FrmTransPaymentsController::change_payment_status( $payment, 'failed' );
			$redirect_helper->handle_error( 'payment_failed' );
			die();
		}

		$customer_has_been_charged = ! empty( $subscription->latest_invoice->charge );
		$atts['charge']            = FrmStrpSubscriptionHelper::prepare_charge_object_for_subscription( $subscription, $amount );
		$new_payment_values        = (array) $payment;

		if ( $customer_has_been_charged ) {
			$charge = $subscription->latest_invoice->charge;
			$new_payment_values['receipt_id'] = $charge->id;

			if ( 'failed' === $charge->status ) {
				FrmTransPaymentsController::change_payment_status( $payment, 'failed' );

				$new_payment_values['receipt_id'] = $charge->id;
				$frm_payment->update( $payment->id, $new_payment_values );

				$redirect_helper->handle_error( 'payment_failed', $charge->id );
			}

			$new_payment_values['status'] = 'pending' === $charge->status ? 'processing' : 'complete';

			$new_payment_values['expire_date'] = '0000-00-00';
			foreach ( $subscription->latest_invoice->lines->data as $line ) {
				$new_payment_values['expire_date'] = gmdate( 'Y-m-d', $line->period->end );
			}
		} elseif ( $trial_end ) {
			$new_payment_values['amount']      = 0;
			$new_payment_values['begin_date']  = gmdate( 'Y-m-d', time() );
			$new_payment_values['expire_date'] = gmdate( 'Y-m-d', $trial_end );
		}

		$new_payment_values['sub_id'] = FrmStrpSubscriptionHelper::create_new_subscription( $atts );

		$frm_payment->update( $payment->id, $new_payment_values );

		if ( $customer_has_been_charged ) {
			// Set the payment to complete.
			$status = 'complete';
			FrmTransActionsController::trigger_payment_status_change( compact( 'status', 'payment' ) );

			// Update the next billing date.
			$next_bill_date = gmdate( 'Y-m-d' );
			foreach ( $subscription->latest_invoice->lines->data as $line ) {
				$next_bill_date = gmdate( 'Y-m-d', $line->period->end );
			}

			$frm_sub = new FrmTransSubscription();
			$frm_sub->update(
				$new_payment_values['sub_id'],
				array(
					'next_bill_date' => $next_bill_date,
					'test'           => 'test' === FrmStrpAppHelper::active_mode() ? 1 : 0,
				)
			);
		}

		$redirect_helper->handle_success( $entry, isset( $charge ) ? $charge->id : '' );
		die();
	}

	/**
	 * Check for a link payment method for a Stripe link recurring payment/subscription.
	 * This gets created on Stripe's end after confirmSetup is called client-side in the Stripe add on.
	 * This is required in order to associate a payment method with the subscription that gets created.
	 *
	 * @since 3.0
	 *
	 * @param object $setup_intent
	 * @return string|false
	 */
	private static function get_link_payment_method( $setup_intent ) {
		if ( is_object( $setup_intent->latest_attempt ) && ! empty( $setup_intent->latest_attempt->payment_method_details ) ) {
			$payment_method_details = $setup_intent->latest_attempt->payment_method_details;
			foreach ( array( 'ideal', 'sofort', 'bancontact' ) as $payment_method_type ) {
				if ( ! empty( $payment_method_details->$payment_method_type ) ) {
					return $payment_method_details->$payment_method_type->generated_sepa_debit;
				}
			}
		}

		if ( ! empty( $setup_intent->payment_method ) ) {
			return $setup_intent->payment_method;
		}

		return false;
	}

	/**
	 * Create a pending Stripe link payment on entry creation.
	 * Stripe link uses confirmPayment with a return URL which gets called after this.
	 * The payment is then updated from pending status later in another request, either when the return URL is loaded or with a webhook.
	 *
	 * @since 3.0
	 *
	 * @param array $atts {
	 *     @type stdClass $form
	 *     @type stdClass $entry
	 *     @type WP_Post  $action
	 *     @type string   $amount
	 *     @type object   $customer
	 * }
	 * @return bool True on success, false on failure.
	 */
	public static function create_pending_stripe_link_payment( $atts ) {
		if ( empty( $atts['form'] ) || empty( $atts['entry'] ) || empty( $atts['action'] ) || ! isset( $atts['amount'] ) || empty( $atts['customer'] ) ) {
			return false;
		}

		$form      = $atts['form'];
		$action    = $atts['action'];
		$intent_id = self::verify_intent( $form->id, $action );

		if ( ! $intent_id ) {
			return false;
		}

		$is_setup_intent = 0 === strpos( $intent_id, 'seti_' );
		$entry           = $atts['entry'];
		$amount          = $atts['amount'];
		$customer        = $atts['customer'];

		if ( ! $is_setup_intent ) {
			$new_values      = array(
				'amount'   => $amount,
				'customer' => $customer->id,
			);
			$updated = FrmStrpAppHelper::call_stripe_helper_class( 'update_intent', $intent_id, $new_values );
			if ( ! $updated ) {
				return false;
			}
		}

		FrmStrpAuth::add_temporary_referer_meta( (int) $entry->id );

		$frm_payment = new FrmTransPayment();
		$payment_id  = $frm_payment->create(
			array(
				'paysys'     => 'stripe',
				'amount'     => FrmStrpAppHelper::get_formatted_amount_for_currency( $amount, $action ),
				'status'     => 'pending',
				'item_id'    => $entry->id,
				'action_id'  => $action->ID,
				'receipt_id' => $intent_id,
				'sub_id'     => '',
				'test'       => 'test' === FrmStrpAppHelper::active_mode() ? 1 : 0,
			)
		);
		return (bool) $payment_id;
	}

	/**
	 * Verify a payment intent or setup intent client secret is in the POST data and is valid.
	 *
	 * @since 3.0
	 *
	 * @param string|int $form_id
	 * @param WP_Post    $action
	 *
	 * @return string|false String intent id on success, False if intent is missing or cannot be verified.
	 */
	private static function verify_intent( $form_id, $action ) {
		$client_secrets = FrmAppHelper::get_post_param( 'frmintent' . $form_id, array(), 'sanitize_text_field' );
		if ( ! $client_secrets ) {
			return false;
		}

		$client_secret              = reset( $client_secrets );
		list( $prefix, $intent_id ) = explode( '_', $client_secret );
		$intent_id                  = $prefix . '_' . $intent_id;

		$is_setup_intent = 0 === strpos( $intent_id, 'seti_' );

		$function_name = $is_setup_intent ? 'get_setup_intent' : 'get_intent';
		$intent        = FrmStrpAppHelper::call_stripe_helper_class( $function_name, $intent_id );

		if ( ! $intent || $intent->client_secret !== $client_secret || ! self::intent_matches_form_action( $intent, $action ) ) {
			return false;
		}

		if ( isset( $intent->charges ) && is_object( $intent->charges ) && ! empty( $intent->charges->data ) ) {
			// The intent should not have any charges yet.
			// If it does, the intent is invalid.
			return false;
		}

		$frm_payment = new FrmTransLitePayment();
		$payment     = $frm_payment->get_one_by( $intent_id, 'receipt_id' );

		if ( $payment ) {
			// A duplicate payment should not exist.
			return false;
		}

		return $intent_id;
	}

	/**
	 * Check if an intent matches a form action.
	 *
	 * @since 3.1.9
	 *
	 * @param object  $intent
	 * @param WP_Post $action
	 *
	 * @return bool
	 */
	private static function intent_matches_form_action( $intent, $action ) {
		if ( ! isset( $intent->metadata ) || ! is_object( $intent->metadata ) || empty( $intent->metadata->action ) ) {
			// Avoid false positive if the intent is missing metadata.
			return true;
		}

		return (int) $intent->metadata->action === $action->ID;
	}

	/**
	 * Flag a form with the frm_stripe_link_form class so it is identifiable when initializing in JavaScript.
	 *
	 * @since 3.0
	 *
	 * @param stdClass $form
	 * @return void
	 */
	public static function add_form_classes( $form ) {
		if ( false === FrmStrpActionsController::get_stripe_link_action( $form->id ) ) {
			return;
		}

		echo ' frm_stripe_link_form ';
	}

	/**
	 * We need to force AJAX submit with Stripe link to avoid the page reloading before confirmPayment is called after entry creation.
	 *
	 * @since 3.0
	 *
	 * @param mixed $form
	 * @return mixed
	 */
	public static function force_ajax_submit_for_stripe_link( $form ) {
		if ( ! is_object( $form ) ) {
			return $form;
		}

		if ( ! empty( $form->options['ajax_submit'] ) ) {
			// AJAX is already on so we can exit early.
			return $form;
		}

		if ( false !== FrmStrpActionsController::get_stripe_link_action( $form->id ) ) {
			$form->options['ajax_submit'] = '1';
		}

		return $form;
	}

	/**
	 * Adds 'payment-processing' trigger to the action options.
	 *
	 * This function accepts an array of options, adds 'payment-processing' to
	 * the 'event' key of the array, and returns the modified array.
	 *
	 * @param array $options The original options array.
	 *
	 * @return array The modified options array with the added 'payment-processing' event.
	 */
	public static function add_trigger_to_action( $options ) {
		$options['event'][] = 'payment-processing';
		return $options;
	}
}
