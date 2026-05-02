<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmStrpApiHelper {

	/**
	 * @param string $mode
	 * @return bool
	 */
	public static function initialize_api( $mode = 'auto' ) {
		$secret_key = self::get_secret_key_for_mode( $mode );
		$success    = false;

		try {
			\Stripe\Stripe::setApiKey( $secret_key );
			$success = true;
		} catch ( Exception $e ) {
			FrmTransLog::log_message( 'Stripe API initialization failed.' );
		}

		if ( $success ) {
			self::set_app();
		}

		return $success;
	}

	/**
	 * Get the secret key from settings for specific mode.
	 *
	 * @since 3.0
	 *
	 * @param string $mode If 'auto' is passing, the active mode in settings will be used.
	 * @return string
	 */
	private static function get_secret_key_for_mode( $mode ) {
		if ( 'auto' === $mode ) {
			$mode = FrmStrpAppHelper::active_mode();
		}

		$settings     = FrmStrpAppHelper::get_settings();
		$setting_name = $mode . '_secret';
		$secret_key   = $settings->settings->$setting_name;

		return $secret_key;
	}

	private static function set_app() {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugin_info = get_plugin_data( FrmStrpAppHelper::plugin_path() . '/formidable-stripe.php' );
		try {
			\Stripe\Stripe::setAppInfo(
				'WordPress FormidableForms',
				$plugin_info['Version'],
				'https://formidableforms.com',
				'pp_partner_DKlIR46WMlkUQ2'
			);
		} catch ( Exception $e ) {
			FrmTransLog::log_message( 'setAppInfo failed.' );
		}
	}

	/**
	 * For compatibility with all api versions, we'll need to force it sometimes
	 *
	 * @since 1.15
	 * @param string $version
	 */
	public static function set_api_version( $version ) {
		try {
			\Stripe\Stripe::setApiVersion( $version );
		} catch ( Exception $e ) {
			FrmTransLog::log_message( 'Stripe API version could not be set.' );
		}
	}

	/**
	 * @param string $sub_id
	 * @return bool
	 */
	public static function cancel_subscription( $sub_id ) {
		if ( FrmStrpAppHelper::should_use_stripe_connect() ) {
			return FrmStrpConnectApiAdapter::cancel_subscription( $sub_id );
		}

		self::initialize_api();

		if ( current_user_can( 'administrator' ) ) {
			$cancel = self::cancel_subscription_now_or_later( $sub_id );
		} else {
			$customer = self::get_customer();
			$sub      = \Stripe\Subscription::retrieve( $sub_id );
			if ( is_object( $customer ) && $sub->customer === $customer->id ) {
				$cancel = self::cancel_subscription_now_or_later( $sub_id );
			} else {
				$cancel = false;
			}
		}

		return $cancel && ( $cancel->status === 'canceled' || $cancel->cancel_at_period_end == true );
	}

	/**
	 * Either cancel a subscription now or later.
	 * This is based on the result of the frm_stripe_cancel_subscription_at_period_end filter.
	 * By default, a subscription is canceled at period end.
	 * But if the filtered value of frm_stripe_cancel_subscription_at_period_end is false, it will
	 * cancel immediately.
	 *
	 * @since 3.1.4
	 *
	 * @param string $sub_id
	 * @return object|false Object on success, false on failure.
	 */
	private static function cancel_subscription_now_or_later( $sub_id ) {
		if ( FrmStrpSubscriptionHelper::should_cancel_at_period_end() ) {
			try {
				$stripe = self::get_stripe_client();
				$cancel = $stripe->subscriptions->update( $sub_id, array( 'cancel_at_period_end' => true ) );
			} catch ( Exception $e ) {
				FrmTransLog::log_message( $e->getMessage() );
				$cancel = false;
			}

			return $cancel;
		}

		try {
			$sub    = \Stripe\Subscription::retrieve( $sub_id );
			$cancel = $sub->cancel();
		} catch ( Exception $e ) {
			FrmTransLog::log_message( $e->getMessage() );
			$cancel = false;
		}

		return $cancel;
	}

	/**
	 * @param string $payment_id
	 * @return bool
	 */
	public static function refund_payment( $payment_id ) {
		if ( FrmStrpAppHelper::should_use_stripe_connect() ) {
			return FrmStrpConnectApiAdapter::refund_payment( $payment_id );
		}

		self::initialize_api();
		try {
			if ( strpos( $payment_id, 'pi_' ) === 0 ) {
				$param = 'payment_intent';
			} else {
				$param = 'charge';
			}
			\Stripe\Refund::create( array( $param => $payment_id ) );
			$refunded = true;
		} catch ( Exception $e ) {
			FrmTransLog::log_message( $e->getMessage() );
			$refunded = false;
		}
		return $refunded;
	}

	/**
	 * Get the payment intent from Stripe
	 *
	 * @since 2.0
	 * @param string $payment_id - The Stripe payment intent id.
	 * @return Stripe\PaymentIntent|false
	 */
	public static function get_intent( $payment_id ) {
		self::initialize_api();
		try {
			$payment = \Stripe\PaymentIntent::retrieve( $payment_id );
		} catch ( Exception $e ) {
			FrmTransLog::log_message( $e->getMessage() );
			$payment = false;
		}
		return $payment;
	}

	/**
	 * Get the payment from Stripe
	 *
	 * @since 1.15
	 * @param string $payment_id - The Stripe payment id.
	 * @return Stripe\PaymentIntent|Stripe\Charge|false
	 */
	public static function get_charge( $payment_id ) {
		self::initialize_api();
		if ( strpos( $payment_id, 'pi_' ) === 0 ) {
			return self::get_intent( $payment_id );
		}
		try {
			$payment = \Stripe\Charge::retrieve( $payment_id );
		} catch ( Exception $e ) {
			FrmTransLog::log_message( $e->getMessage() );
			$payment = false;
		}
		return $payment;
	}

	/**
	 * Check if a charge has already be authorized
	 *
	 * @since 1.15
	 * @param string $charge_id - The Stripe charge id.
	 * @return boolean
	 */
	public static function can_by_captured( $charge_id ) {
		$charge = self::get_charge( $charge_id );
		if ( empty( $charge ) || ! is_object( $charge ) ) {
			return false;
		}

		if ( $charge->object === 'payment_intent' ) {
			$capture = $charge->status === 'requires_capture';
		} else {
			$capture = ! $charge->captured && $charge->status === 'succeeded';
		}

		return $capture;
	}

	/**
	 * @since 1.15
	 * @param string $charge_id - The id of the Stripe charge.
	 * @return boolean
	 */
	public static function capture_charge( $charge_id ) {
		try {
			$payment = self::get_charge( $charge_id );
			$payment->capture();
			$charged = true;
		} catch ( Exception $e ) {
			FrmTransLog::log_message( $e->getMessage() );
			$charged = false;
		}
		return $charged;
	}

	/**
	 * @param string $intent_id
	 * @param array  $data
	 * @return bool
	 */
	public static function update_intent( $intent_id, $data ) {
		$intent = self::get_intent( $intent_id );
		if ( ! is_object( $intent ) ) {
			return $intent;
		}
		$updated = false;
		try {
			$intent->update( $intent->id, $data );
			$updated = true;
		} catch ( \Stripe\Error\Base $e ) {
			self::get_stripe_exception( $e );
		} catch ( Exception $e ) {
			self::get_generic_exception( $e );
		}
		return $updated;
	}

	/**
	 * @param string $intent_id
	 * @param array  $data
	 * @return mixed
	 */
	public static function confirm_intent( $intent_id, $data ) {
		$intent = self::get_intent( $intent_id );
		if ( ! is_object( $intent ) ) {
			return $intent;
		}
		try {
			return $intent->confirm( $data );
		} catch ( Exception $e ) {
			return self::get_generic_exception( $e );
		}
		return false;
	}

	/**
	 * @param string $intent_id
	 * @param array  $data
	 * @return object|string|false
	 */
	public static function capture_intent( $intent_id, $data ) {
		// As of 2022-11-15 the charges property has been removed from payment intents.
		self::set_api_version( '2022-08-01' );

		$intent = self::get_intent( $intent_id );
		if ( ! is_object( $intent ) ) {
			return $intent;
		}
		try {
			return $intent->capture( $data );
		} catch ( \Stripe\Error\Base $e ) {
			return self::get_stripe_exception( $e );
		} catch ( Exception $e ) {
			return self::get_generic_exception( $e );
		}
		return false;
	}

	public static function get_customer_subscriptions() {
		$customer = self::get_customer();
		if ( is_object( $customer ) ) {
			$subscriptions = \Stripe\Subscription::all( array( 'customer' => $customer->id ) );
		} else {
			$subscriptions = array();
		}

		return $subscriptions;
	}

	/**
	 * @param array $options
	 * @return mixed
	 */
	public static function get_customer( $options = array() ) {
		self::initialize_api();

		$customer_id = false;
		$user_id     = 0;
		if ( ! empty( $options['user_id'] ) ) {
			$user_id = $options['user_id'];
		} elseif ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
		}

		if ( isset( $options['user_id'] ) ) {
			unset( $options['user_id'] );
		}

		$meta_name = FrmStrpAppHelper::get_customer_id_meta_name();
		if ( $user_id ) {
			$customer_id = get_user_meta( $user_id, $meta_name, true );
			if ( ! isset( $options['email'] ) ) {
				$user_info = get_userdata( $user_id );
				if ( is_object( $user_info ) && ! empty( $user_info->user_email ) ) {
					$options['email'] = $user_info->user_email;
				}
			}
		}

		try {
			if ( $customer_id ) {
				$customer = \Stripe\Customer::retrieve( $customer_id );
				if ( is_object( $customer ) && isset( $options['source'] ) ) {
					$customer->source = $options['source'];
					$customer->save();
				}
			} else {
				$customer = \Stripe\Customer::create( $options );

				if ( is_object( $customer ) && isset( $user_id ) ) {
					update_user_meta( $user_id, $meta_name, $customer->id );
				}
			}
		} catch ( \Stripe\Error\Card $e ) {
			$customer = self::get_stripe_exception( $e );
		} catch ( \Stripe\Error\Base $e ) {
			$customer = self::get_stripe_exception( $e );
		} catch ( Exception $e ) {
			FrmTransLog::log_message( $e->getMessage() );
			$customer = $e->getMessage();
		}

		if ( $customer_id && is_string( $customer ) && strpos( $customer, 'No such customer' ) !== false ) {
			FrmTransLog::log_message( 'Reset customer id for user #' . $user_id );
			delete_user_meta( $user_id, $meta_name );
		}

		return $customer;
	}

	/**
	 * @param int $user_id
	 * @return mixed
	 */
	public static function get_customer_by_id( $user_id ) {
		self::initialize_api();
		$meta_name   = FrmStrpAppHelper::get_customer_id_meta_name();
		$customer_id = get_user_meta( $user_id, $meta_name, true );

		if ( $customer_id ) {
			try {
				$customer = \Stripe\Customer::retrieve( $customer_id );
				if ( isset( $customer->deleted ) && $customer->deleted ) {
					$customer = false;
					delete_user_meta( $user_id, $meta_name );
				}
			} catch ( Exception $e ) {
				$customer = false;
			}
		} else {
			$customer = false;
		}

		return $customer;
	}

	/**
	 * @param int $user_id
	 * @return array
	 */
	public static function get_cards( $user_id ) {
		$cards = array();

		if ( $user_id ) {
			$customer = self::get_customer_by_id( $user_id );

			if ( $customer ) {
				$saved_cards  = $customer->sources->all( array( 'object' => 'card' ) );
				$default_card = $customer->default_source;

				foreach ( $saved_cards->data as $card ) {
					$cards[ $card->id ] = array(
						'card'    => $card,
						'default' => ( $card->id === $default_card ),
					);
				}
			}
		}

		return $cards;
	}

	/**
	 * @param string $card_id
	 * @return array response
	 */
	public static function delete_card( $card_id ) {
		$response = array(
			'success' => false,
			'error'   => '',
		);
		$user_id  = get_current_user_id();

		if ( $user_id ) {
			$customer = self::get_customer_by_id( $user_id );
			if ( $customer ) {
				try {
					$stripe_response = $customer->sources->retrieve( $card_id )->delete();
					$response['success'] = $stripe_response->deleted;
				} catch ( \Stripe\Error\Base $e ) {
					self::get_stripe_exception( $e );
				} catch ( Exception $e ) {
					$response['error'] = $e->getMessage();
					FrmTransLog::log_message( $response['error'] );
				}
			}
		} else {
			$response['error'] = 'User is not logged in';
		}

		return $response;
	}

	/**
	 * @param string $id
	 * @return mixed
	 */
	public static function get_event( $id ) {
		$event = false;
		try {
			$event = \Stripe\Event::retrieve( $id );
		} catch ( \Stripe\Error\Base $e ) {
			self::get_stripe_exception( $e );
		} catch ( Exception $e ) {
			FrmTransLog::log_message( $e->getMessage() );
		}

		return $event;
	}

	/**
	 * @param array $plan
	 */
	public static function maybe_create_plan( $plan ) {
		$existing_plan = self::retrieve_plan( $plan['id'] );
		if ( $existing_plan ) {
			$plan_id = $existing_plan['id'];
		} else {
			$plan_id = self::create_plan( $plan );
		}
		return $plan_id;

	}

	/**
	 * @param array $plan
	 * @return mixed
	 */
	public static function create_plan( $plan ) {
		self::set_api_version( '2018-01-23' );
		$plan_id = 0;
		try {
			\Stripe\Plan::create( $plan );
			$plan_id = $plan['id'];
		} catch ( Exception $e ) {
			$error = self::get_generic_exception( $e );
			FrmTransLog::log_message( $error );
		}
		return $plan_id;
	}

	/**
	 * @param string $plan_id
	 * @return mixed
	 */
	public static function retrieve_plan( $plan_id ) {
		$plan = false;
		try {
			$plan = \Stripe\Plan::retrieve( $plan_id );
		} catch ( Exception $e ) {
			// plan doesn't exist
			$error = self::get_generic_exception( $e );
			FrmTransLog::log_message( $error );
		}
		return $plan;
	}

	/**
	 * @param array $new_charge
	 * @return Stripe\Subscription|string|false
	 */
	public static function create_subscription( $new_charge ) {
		self::initialize_api();
		try {
			return \Stripe\Subscription::create( $new_charge );
		} catch ( \Stripe\Error\Base $e ) {
			return self::get_stripe_exception( $e );
		} catch ( Exception $e ) {
			return self::get_generic_exception( $e );
		}
		return false;
	}

	/**
	 * @param array $new_charge
	 * @return Stripe\PaymentIntent|Stripe\Charge|string|false
	 */
	public static function run_new_charge( $new_charge ) {
		// Set the API version because of an error that happens in API version 2023-08-16.
		self::set_api_version( '2022-11-15' );

		try {
			if ( ! empty( $new_charge['confirm'] ) ) {
				return \Stripe\PaymentIntent::create( $new_charge );
			}
			return \Stripe\Charge::create( $new_charge );
		} catch ( \Stripe\Error\Base $e ) {
			return self::get_stripe_exception( $e );
		} catch ( Exception $e ) {
			return self::get_generic_exception( $e );
		}
		return false;
	}

	/**
	 * @param array $new_charge
	 * @return mixed
	 */
	public static function create_intent( $new_charge ) {
		try {
			return \Stripe\PaymentIntent::create( $new_charge );
		} catch ( \Stripe\Error\Base $e ) {
			return self::get_stripe_exception( $e );
		} catch ( Exception $e ) {
			return self::get_generic_exception( $e );
		}
		return false;
	}

	/**
	 * Create a setup intent for a Stripe link recurring payment.
	 * This is called when a form is loaded.
	 *
	 * @since 3.0
	 *
	 * @param string      $customer_id Customer ID beginning with cus_.
	 * @param array|false $payment_method_types If false the types will defaults to array( 'card', 'link' ).
	 * @return object|string|false
	 */
	public static function create_setup_intent( $customer_id, $payment_method_types = false ) {
		$stripe = self::get_stripe_client();

		$data = array( 'customer' => $customer_id );

		if ( $payment_method_types ) {
			$data['payment_method_types'] = $payment_method_types;
		} elseif ( false === $payment_method_types ) {
			$data['payment_method_types'] = array( 'card', 'link' );
		} else {
			$data['automatic_payment_methods'] = array( 'enabled' => true );
		}

		try {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			return $stripe->setupIntents->create( $data );
		} catch ( \Stripe\Error\Base $e ) {
			return self::get_stripe_exception( $e );
		} catch ( Exception $e ) {
			return self::get_generic_exception( $e );
		}
		return false;
	}

	/**
	 * Get a $stripe object. This is required to access the $stripe->setupIntents object for recurring Stripe link payments.
	 *
	 * @since 3.0
	 *
	 * @return \Stripe\StripeClient
	 */
	private static function get_stripe_client() {
		$secret_key = self::get_secret_key_for_mode( 'auto' );
		return new \Stripe\StripeClient( $secret_key );
	}

	/**
	 * Get a setup intent (used for Stripe link recurring payments).
	 *
	 * @since 3.0
	 *
	 * @param string $setup_id
	 * @return object|string|false
	 */
	public static function get_setup_intent( $setup_id ) {
		$stripe = self::get_stripe_client();
		try {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			return $stripe->setupIntents->retrieve( $setup_id, array( 'expand' => array( 'latest_attempt' ) ) );
		} catch ( \Stripe\Error\Base $e ) {
			return self::get_stripe_exception( $e );
		} catch ( Exception $e ) {
			return self::get_generic_exception( $e );
		}
		return false;
	}

	/**
	 * @param object $e exception.
	 * @return string
	 */
	private static function get_generic_exception( $e ) {
		FrmTransLog::log_message( $e->getMessage() );
		return $e->getMessage();
	}

	public static function get_stripe_exception( $e ) {
		$body  = $e->getJsonBody();
		$error = $body['error'];
		FrmTransLog::log_message( print_r( $error, 1 ) );
		$error['code'] = apply_filters( 'frm_stripe_error_code', $error['code'], $e );
		$message       = self::get_translated_error( $error['code'], isset( $error['message'] ) ? $error['message'] : '' );
		return esc_html( $message );
	}

	/**
	 * @param string $code
	 * @param string $message
	 * @return string
	 */
	private static function get_translated_error( $code, $message = '' ) {
		if ( $message !== '' ) {
			$locale = get_locale();
			if ( $locale === 'en_US' ) {
				return $message;
			}
		}

		$messages = array(
			'incorrect_number'      => __( 'The card number is incorrect.', 'formidable-stripe' ),
			'invalid_number'        => __( 'The card number is not a valid credit card number.', 'formidable-stripe' ),
			'invalid_expiry_month'  => __( 'The card\'s expiration month is invalid.', 'formidable-stripe' ),
			'invalid_expiry_year'   => __( 'The card\'s expiration year is invalid.', 'formidable-stripe' ),
			'invalid_cvc'           => __( 'The card\'s security code is invalid.', 'formidable-stripe' ),
			'expired_card'          => __( 'The card has expired.', 'formidable-stripe' ),
			'incorrect_cvc'         => __( 'The card\'s security code is incorrect.', 'formidable-stripe' ),
			'incorrect_zip'         => __( 'The card\'s zip code failed validation.', 'formidable-stripe' ),
			'card_declined'         => __( 'The card was declined.', 'formidable-stripe' ),
			'missing'               => __( 'There is no card on a customer that is being charged.', 'formidable-stripe' ),
			'processing_error'      => __( 'An error occurred while processing the card.', 'formidable-stripe' ),
			'rate_limit'            => __( 'An error occurred due to requests hitting the API too quickly. Please let us know if you\'re consistently running into this error.', 'formidable-stripe' ),
			'invalid_swipe_data'    => __( 'The card\'s swipe data is invalid.', 'formidable-stripe' ),
			'rate_limit_error'      => __( 'Too many requests hit the API too quickly.', 'formidable-stripe' ),
			'invalid_request_error' => __( 'Invalid request errors arise when your request has invalid parameters.', 'formidable-stripe' ),
			'authentication_error'  => __( 'Failed to properly authenticate in the request.', 'formidable-stripe' ),
			'api_connection_error'  => __( 'Failed to connect to Stripe\'s API. This usually means you have an issue with TLS 1.2 on your server.', 'formidable-stripe' ),
		);

		$messages = apply_filters( 'frm_stripe_error_messages', $messages );

		if ( isset( $messages[ $code ] ) ) {
			$message = $messages[ $code ];
		}

		return $message;
	}
}
