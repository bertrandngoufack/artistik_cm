<?php

class FrmTwloAppController {

	public function __construct() {
		add_action( 'admin_init', array( __CLASS__, 'include_updater' ), 1 );
		add_action( 'wp_ajax_frm_twilio_vote', array( __CLASS__, 'process_notification' ) );
		add_action( 'wp_ajax_nopriv_frm_twilio_vote', array( __CLASS__, 'process_notification' ) );

		add_action( 'frm_trigger_twilio_action', array( __CLASS__, 'trigger_sms' ), 10, 3 );
	}

	public static function path() {
		return dirname( dirname( __FILE__ ) );
	}

	public static function include_updater() {
		if ( class_exists( 'FrmAddon' ) ) {
			include_once self::path() . '/models/FrmTwloUpdate.php';
			FrmTwloUpdate::load_hooks();
		}
	}

	/**
	 * Save a texted vote
	 * The URL example.com/wp-admin/admin-ajax.php?action=frm_twilio_vote&form=5
	 */
	public static function process_notification() {
		header( 'Content-type: text/xml' );
		echo '<?xml version="1.0" encoding="UTF-8" ?>';
		echo '<Response>';

		if ( class_exists( 'FrmAppHelper' ) ) {
			$form_id = FrmAppHelper::get_param( 'form', '', 'get', 'absint' );

			$phone_number = sanitize_text_field( $_REQUEST['From'] );
			$vote         = sanitize_text_field( $_REQUEST['Body'] );

			// If we've got good data, save the vote
			if ( ( strlen( $phone_number ) >= 10 ) && ! empty( $vote ) ) {
				// verify this came from Twilio
				if ( self::verify() ) {
					$response = self::save_vote( $phone_number, $vote, $form_id );
				} else {
					$response = __( 'Sorry, your vote doesn\'t appear to be authentic.', 'frmtwlo' );
				}
			} else {
				// Otherwise, give the user an example of how to vote
				$response = __( 'Sorry, I didn\'t understand that.', 'frmtwlo' );
			}
		} else {
			$response = __( 'Sorry, I wasn\'t able to save your vote.', 'frmtwlo' );
		}
		$response = apply_filters(
			'frmtwlo_sms_response',
			$response,
			array(
				'from'    => $phone_number,
				'form_id' => $form_id,
			)
		);

		// Send an SMS back to the person that voted letting them know that their vote was saved,
		// or that there was an error of some sort
		echo '<Message>' . $response . '</Message>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</Response>';

		wp_die();
	}

	public static function trigger_sms( $action, $entry, $form ) {
		$defaults = array( 'from', 'to', 'message', 'custom' );
		foreach ( $defaults as $default ) {
			$action->post_content[ $default ] = apply_filters( 'frm_content', $action->post_content[ $default ], $form, $entry );
			$action->post_content[ $default ] = do_shortcode( $action->post_content[ $default ] );
		}

		// Use a custom number just in case.
		if ( 'custom' === $action->post_content['from'] ) {
			$action->post_content['from'] = $action->post_content['custom'];
		}

		$to = preg_split( '/(,|;)/', $action->post_content['to'] );
		foreach ( (array) $to as $phone ) {
			if ( ! $phone ) {
				continue;
			}

			$message = self::prepare_message( $action->post_content['message'], $entry );
			self::send_message( $action->post_content['from'], trim( $phone ), $message );
		}
	}

	/**
	 * Run through shortcodes in the message.
	 *
	 * @since 1.09
	 */
	private static function prepare_message( $message, $entry ) {
		$pass_entry = clone $entry;
		$message    = FrmEntriesHelper::replace_default_message(
			$message,
			array(
				'id'         => $entry->id,
				'entry'      => $pass_entry,
				'plain_text' => true,
				'user_info'  => false,
			)
		);
		return $message;
	}

	private static function send_message( $from, $to, $message ) {
		if ( empty( $from ) || empty( $to ) ) {
			return;
		}

		$from = self::format_number( $from );
		$to = self::format_number( $to );

		$message = wp_specialchars_decode( strip_tags( $message ), ENT_QUOTES );

		// the message won't be sent by Twilio if it's too long
		if ( strlen( $message ) > 1600 ) {
			$message = substr( $message, 0, 1597 ) . '...';
		}

		$args = array(
			'To'   => $to,
			'From' => $from,
			'Body' => $message,
		);

		self::send_api_request( 'Messages', $args );
	}


	/**
	 * Get the phone numbers that are connected to user's Twilio account
	 *
	 * @return array associatve indexed like +18043169678 => (804) 316-9678
	 */
	public static function get_phone_numbers() {
		// Use an option since transients aren't always reliable and load on every page load.
		$option        = 'frmtwlo_numbers';
		$phone_numbers = get_option( $option );

		$is_cached = $phone_numbers && isset( $phone_numbers['saved'] ) && $phone_numbers['saved'] > strtotime( '-15 minutes' );
		if ( $is_cached ) {
			return $phone_numbers['numbers'];
		}

		$phone_numbers = self::send_api_request( 'IncomingPhoneNumbers.json?PageSize=1000' );
		if ( empty( $phone_numbers['incoming_phone_numbers'] ) ) {
			// Return an error message if applicable.
			$phone_numbers = is_array( $phone_numbers ) ? array() : $phone_numbers;
		} else {
			$phone_numbers = array_column( $phone_numbers['incoming_phone_numbers'], 'friendly_name', 'phone_number' );
		}

		// Save for caching.
		$numbers = array(
			'numbers' => $phone_numbers,
			'saved'   => time(),
		);
		update_option( $option, $numbers, 'no' );

		return $phone_numbers;
	}

	/**
	 * Fallback to save number that isn't included in the dropdown.
	 *
	 * @since 1.09
	 */
	public static function get_custom_number( $form_action, $use_custom = false ) {
		$custom_number = $form_action->post_content['custom'];
		$from_number   = $form_action->post_content['from'];
		if ( ! $custom_number && $use_custom && $from_number && 'custom' !== $from_number ) {
			$custom_number = $from_number;
		}
		return $custom_number;
	}

	/**
	 * Outputs the HTML selected attribute for phone numbers
	 *
	 * @param mixed $selected
	 * @param mixed $current
	 */
	public static function selected_phone_number( $selected, $current ) {
		$selected = self::format_number( $selected );
		$current  = self::format_number( $current );
		return selected( $selected, $current );
	}

	/**
	 * Prepare the number before it's sent to Twilio
	 * Formats the number as +15555555555
	 *
	 * @since 1.03
	 */
	private static function format_number( $num ) {
		$country_code_included = '+' === substr( $num, 0, 1 );
		$original_num          = $num;

		// remove all non-numeric cahracters
		$num = preg_replace( '/[^0-9]/', '', $num );

		// if this is a 10-digit number, assume it's US
		if ( ! $country_code_included && strpos( $num, '1' ) !== 0 && strlen( $num ) == 10 ) {
			$num = '1' . $num;
		}
		$num = '+' . $num;

		$num = apply_filters( 'frmtwlo_format_number', $num, array( 'original' => $original_num ) );
		return $num;
	}

	/**
	 * Send the API request and process the response
	 *
	 * @since 1.03
	 */
	private static function send_api_request( $endpoint, $args = array() ) {
		$frm_twlo_settings = new FrmTwloSettings();
		$sid               = $frm_twlo_settings->settings['account_sid'];
		$token             = $frm_twlo_settings->settings['auth_token'];

		if ( ! $sid || ! $token ) {
			return __( 'The Twilio API has not been set up in the Global Settings', 'frmtwlo' );
		}

		// use the json url so we will get a json response back
		$domain = 'https://api.twilio.com/2010-04-01/Accounts/' . urlencode( strip_tags( $sid ) );
		$uri    = $domain . '/' . $endpoint;

		if ( false === strpos( $uri, '.json' ) ) {
			$uri .= '.json';
		}

		$arg_array = array(
			'body'      => $args,
			'timeout'   => 20,
			'sslverify' => false,
			'headers'   => array(
				'Authorization' => 'Basic ' . base64_encode( sprintf( '%s:%s', $sid, $token ) ),
			),
		);

		if ( self::use_get_for_endpoint( $endpoint ) ) {
			$resp = wp_remote_get( $uri, $arg_array );
		} else {
			$resp = wp_remote_post( $uri, $arg_array );
		}

		$body    = wp_remote_retrieve_body( $resp );
		$message = __( 'There was a problem with your Twilio submission.', 'frmtwlo' );

		if ( is_wp_error( $resp ) ) {
			$message = __( 'You had an error communicating with Twilio\'s API.', 'frmtwlo' );
			if ( is_wp_error( $resp ) ) {
				$message .= ' ' . $resp->get_error_message();
			}
		} elseif ( 'error' === $body || is_wp_error( $body ) ) {
			$message = __( 'You had an HTTP error connecting to Twilio\'s API', 'frmtwlo' );
		} else {
			$json_res = json_decode( $body, true );
			if ( null !== $json_res ) {
				if ( is_array( $json_res ) && isset( $json_res['error_code'] ) && ! empty( $json_res['error_code'] ) ) {
					$message = $json_res['error'];
				} else {
					$message = $json_res;
				}
			} elseif ( isset( $resp['response'] ) && isset( $resp['response']['code'] ) ) {
				$message = sprintf( __( '%1$s: %2$s', 'frmtwlo' ), $resp['response']['code'], $resp['response']['message'] . ' ' . $resp['body'] );
			}
		}

		return $message;
	}

	private static function use_get_for_endpoint( $endpoint ) {
		return false !== strpos( $endpoint, 'IncomingPhoneNumbers.json?PageSize' );
	}

	/**
	 * Verify incoming text
	 *
	 * @return bool
	 */
	private static function verify() {
		$frm_twlo_settings = new FrmTwloSettings();
		if ( empty( $frm_twlo_settings->settings['account_sid'] ) ) {
			return true;
		}

		$account_sid = FrmAppHelper::get_param( 'AccountSid', '', 'get', 'sanitize_text_field' );
		return $frm_twlo_settings->settings['account_sid'] === $account_sid;
	}

	/**
	 * Save incoming text
	 *
	 * @param string $phone_number
	 * @param string $vote
	 * @param int $form_id
	 * @return string $message
	 */
	private static function save_vote( $phone_number, $vote, $form_id ) {
		// Just the digits, please
		$phone_number = preg_replace( '/\D/', '', $phone_number );
		$message      = __( 'Sorry, there was an error saving your vote.', 'frmtwlo' );

		$form = FrmForm::getOne( $form_id );
		if ( ! $form || $form->logged_in ) {
			return $message;
		}

		$allowed = true;

		global $wpdb;
		if ( ! empty( $form->options['single_entry'] ) ) {
			//if form is limited to one, check to see if person has already voted
			$prev_entry = FrmEntry::getAll( $wpdb->prepare( 'it.form_id=%d and it.ip = %s', $form_id, $phone_number ), '', ' LIMIT 1' );
			if ( $prev_entry ) {
				$allowed = false;
			}
		}

		if ( $allowed ) {
			$values                = array(
				'ip'      => $phone_number,
				'form_id' => $form_id,
			);
			$values['description'] = serialize(
				array(
					'browser'  => 'Phone',
					'referrer' => 'http://twilio.com',
				)
			);
			$values['item_meta']   = array();

			$fields               = FrmField::get_all_for_form( $form->id );
			$lowercase_vote_value = strtolower( $vote );
			foreach ( $fields as $field ) {
				if ( in_array( $field->type, array( 'select', 'radio', 'scale', 'checkbox' ), true ) && is_array( $field->options ) ) {
					foreach ( $field->options as $opt ) {
						if ( is_array( $opt ) ) {
							if ( isset( $opt['label'] ) && strtolower( $opt['label'] ) === $lowercase_vote_value ) {
								$values['item_meta'][ $field->id ] = $opt['label'];
							} elseif ( isset( $opt['value'] ) && strtolower( $opt['value'] ) === $lowercase_vote_value ) {
								$values['item_meta'][ $field->id ] = $opt['value'];
							}
						} elseif ( strtolower( $opt ) === $lowercase_vote_value ) {
							$values['item_meta'][ $field->id ] = $opt;
						}
					}

					if ( isset( $values['item_meta'][ $field->id ] ) ) {
						continue;
					}

					if ( is_numeric( $vote ) ) {
						$values['item_meta'][ $field->id ] = $field->options[ (int) $vote - 1 ];

						if ( is_array( $values['item_meta'][ $field->id ] ) && isset( $values['item_meta'][ $field->id ]['value'] ) ) {
							$values['item_meta'][ $field->id ] = $values['item_meta'][ $field->id ]['value'];
						}

						continue;
					}

					if ( ! empty( $field->field_options['other'] ) ) {
						$values['item_meta'][ $field->id ]          = 'Other';
						$values['item_meta']['other'][ $field->id ] = $vote;
					}
				} else {
					$values['item_meta'][ $field->id ] = $vote;
				}
				unset( $field );
			}

			$_POST  = $values;
			$errors = FrmEntryValidate::validate( $values );
			if ( $errors ) {
				$message = implode( ', ', $errors );
			} else {
				$id = FrmEntry::create( $_POST );
				if ( $id ) {
					$default_message = __( 'Thank you, your vote has been recorded.', 'frmtwlo' );
					$message         = isset( $form->options['success_msg'] ) ? $form->options['success_msg'] : $default_message;
					$message         = apply_filters( 'frm_content', $message, $form, $id );
					$wpdb->update( $wpdb->prefix . 'frm_items', array( 'ip' => $phone_number ), compact( 'id' ) );
				}
			}
		} else {
			$message = __( 'Sorry, you can only vote once.', 'frmtwlo' );
		}

		return $message;
	}

	/*
	* v2.0 fallback
	*/
	public static function send_sms( $atts ) {
		_deprecated_function( __FUNCTION__, '1.02', 'Update your Formidable version' );
	}
}
