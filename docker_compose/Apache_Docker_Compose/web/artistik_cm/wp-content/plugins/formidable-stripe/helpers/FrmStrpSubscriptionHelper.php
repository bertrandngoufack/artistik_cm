<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Handles shared Stripe subscription logic between FrmStrpActionsController and FrmStrpLinkController.
 *
 * @since 3.0
 */
class FrmStrpSubscriptionHelper {

	/**
	 * Prepare a charge object for a Stripe subscription.
	 *
	 * @since 3.0
	 *
	 * @param object $subscription A Stripe Subscription object.
	 * @param string $amount
	 * @return stdClass
	 */
	public static function prepare_charge_object_for_subscription( $subscription, $amount ) {
		$charge_object                       = new stdClass();
		$charge_object->id                   = null;
		$charge_object->paid                 = false; // This is only checked in FrmStrpActionsController::trigger_one_time_payment, it doesn't need to be set for Stripe Link.
		$charge_object->amount               = $amount;
		$charge_object->sub_id               = $subscription->id;
		$charge_object->current_period_start = $subscription->current_period_start;
		$charge_object->current_period_end   = $subscription->current_period_end;

		return $charge_object;
	}

	/**
	 * Create a Formidable subscription object with the nested payments submodule.
	 *
	 * @since 3.0 This was moved from FrmStrpActionsController.
	 *
	 * @param array $atts
	 * @return string|int $sub_id
	 */
	public static function create_new_subscription( $atts ) {
		$atts['charge'] = (object) $atts['charge'];

		$new_values = array(
			'amount'         => FrmStrpAppHelper::get_formatted_amount_for_currency( $atts['charge']->amount, $atts['action'] ),
			'paysys'         => 'stripe',
			'item_id'        => $atts['entry']->id,
			'action_id'      => $atts['action']->ID,
			'sub_id'         => isset( $atts['charge']->sub_id ) ? $atts['charge']->sub_id : '',
			'interval_count' => $atts['action']->post_content['interval_count'],
			'time_interval'  => $atts['action']->post_content['interval'],
			'status'         => 'active',
			'next_bill_date' => gmdate( 'Y-m-d' ),
			'test'           => 'test' === FrmStrpAppHelper::active_mode() ? 1 : 0,
		);

		if ( ! empty( $atts['action']->post_content['payment_limit'] ) ) {
			$end_count = self::prepare_payment_limit(
				$atts['action']->post_content['payment_limit'],
				(int) $atts['entry']->form_id,
				(int) $atts['entry']->id
			);
			if ( is_int( $end_count ) ) {
				$new_values['end_count'] = $end_count;
			}
		}

		$frm_sub = new FrmTransSubscription();
		$sub_id  = $frm_sub->create( $new_values );
		return $sub_id;
	}

	/**
	 * Get an end_count value to use for our subscription.
	 *
	 * @since 3.1.5
	 *
	 * @param string $payment_limit The raw payment value string. It is not empty.
	 * @param int    $form_id       Required for processing shortcodes.
	 * @param int    $entry_id      Required for processing shortcodes.
	 * @return WP_Error|int
	 */
	public static function prepare_payment_limit( $payment_limit, $form_id, $entry_id ) {
		if ( is_numeric( $payment_limit ) ) {
			return (int) $payment_limit;
		}

		if ( false === strpos( $payment_limit, '[' ) ) {
			return self::get_invalid_payment_limit_error( $payment_limit );
		}

		$payment_limit = FrmTransAppHelper::process_shortcodes(
			array(
				'value' => $payment_limit,
				'form'  => $form_id,
				'entry' => $entry_id,
			)
		);
		if ( ! is_numeric( $payment_limit ) ) {
			return self::get_invalid_payment_limit_error( $payment_limit );
		}

		return (int) $payment_limit;
	}

	/**
	 * @since 3.1.5
	 *
	 * @param string $payment_limit
	 * @return WP_Error
	 */
	private static function get_invalid_payment_limit_error( $payment_limit ) {
		return new WP_Error(
			'invalid_payment_limit',
			sprintf( __( 'Invalid payment limit value %s', 'formidable' ), $payment_limit )
		);
	}

	/**
	 * Get a plan for Stripe subscription.
	 *
	 * @since 3.0
	 *
	 * @param array $atts {
	 *    @type WP_Post $action
	 *    @type string  $amount
	 * }
	 * @return string Plan id.
	 */
	public static function get_plan_from_atts( $atts ) {
		$action                         = $atts['action'];
		$action->post_content['amount'] = $atts['amount'];
		return self::get_plan_for_action( $action );
	}

	/**
	 * @since 3.0 This was moved from FrmStrpActionsController.
	 *
	 * @param WP_Post $action
	 * @return string|false
	 */
	private static function get_plan_for_action( $action ) {
		$plan_id = $action->post_content['plan_id'];
		if ( ! $plan_id ) {
			// the amount has already been formatted, so add the decimal back in
			$amount                         = $action->post_content['amount'];
			$action->post_content['amount'] = number_format( ( $amount / 100 ), 2, '.', '' );
			$plan_opts                      = self::prepare_plan_options( $action->post_content );
			$plan_id                        = self::maybe_create_plan( $plan_opts );
		}
		return $plan_id;
	}

	/**
	 * @since 3.0 This was moved from FrmStrpActionsController.
	 *
	 * @param array $settings
	 * @return array
	 */
	public static function prepare_plan_options( $settings ) {
		$amount              = FrmStrpActionsController::prepare_amount( $settings['amount'], $settings );
		$default_description = number_format( ( $amount / 100 ), 2 ) . '/' . $settings['interval'];
		$plan_opts           = array(
			'amount'         => $amount,
			'interval'       => $settings['interval'],
			'interval_count' => $settings['interval_count'],
			'currency'       => $settings['currency'],
			'name'           => empty( $settings['description'] ) ? $default_description : $settings['description'],
		);

		if ( ! empty( $settings['trial_interval_count'] ) ) {
			$plan_opts['trial_period_days'] = self::get_trial_with_default( $settings['trial_interval_count'] );
		}

		$plan_opts['id'] = FrmStrpActionsController::create_plan_id( $settings );

		return $plan_opts;
	}

	/**
	 * @since 3.0 This was moved from FrmStrpActionsController.
	 *
	 * @param array $plan
	 * @return mixed
	 */
	public static function maybe_create_plan( $plan ) {
		FrmStrpAppHelper::call_stripe_helper_class( 'initialize_api' );
		return FrmStrpAppHelper::call_stripe_helper_class( 'maybe_create_plan', $plan );
	}

	/**
	 * Since the trial period can come from an entry, use a default value
	 * when creating the plan. This is overridden when the subscription
	 * is created.
	 *
	 * @since 1.16
	 * @since 3.0 This was moved from FrmStrpActionsController.
	 *
	 * @param mixed $trial
	 * @return int
	 */
	private static function get_trial_with_default( $trial ) {
		if ( ! is_numeric( $trial ) ) {
			// Use 0 as this is only ever overwritten when it is non-zero.
			$trial = 0;
		}
		return absint( $trial );
	}

	/**
	 * If a subscription fails because the plan does not exist, create the plan and try again.
	 *
	 * @since 3.1.3
	 *
	 * @param object|string|false $subscription
	 * @param array               $charge_data
	 * @param WP_Post             $action
	 * @param int                 $amount
	 * @return object|string|false
	 */
	public static function maybe_create_missing_plan_and_create_subscription( $subscription, $charge_data, $action, $amount ) {
		if ( ! is_string( $subscription ) || 0 !== strpos( $subscription, 'No such plan: ' ) ) {
			// Only retry when there is a No such plan string error.
			return $subscription;
		}

		// The full error message looks like "No such plan: '_399_1month_usd".
		$action->post_content['plan_id'] = '';
		$charge_data['plan']             = self::get_plan_from_atts( compact( 'action', 'amount' ) );
		$subscription                    = FrmStrpAppHelper::call_stripe_helper_class( 'create_subscription', $charge_data );
		return $subscription;
	}

	/**
	 * When this is filtered and returns false, the subscription will be canceled immediately instead.
	 *
	 * @since 3.1.4
	 *
	 * @return bool
	 */
	public static function should_cancel_at_period_end() {
		/**
		 * @param bool $cancel_at_period_end
		 */
		return (bool) apply_filters( 'frm_stripe_cancel_subscription_at_period_end', true );
	}
}
