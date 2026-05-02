<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 1.0
 */
class FrmCouponsFieldCoupon extends FrmFieldType {

	/**
	 * @var string
	 */
	protected $type = 'coupon';

	/**
	 * @var bool
	 */
	protected $array_allowed = false;

	/**
	 * Define which field settings to show for a Coupon field.
	 *
	 * @return array
	 */
	protected function field_settings_for_type() {
		$settings = parent::field_settings_for_type();
		FrmProFieldsHelper::fill_default_field_display( $settings );

		// It doesn't really make sense to make a Coupon required, so remove the option.
		$settings['required'] = false;

		// Support placeholders for the coupon code input.
		$settings['clear_on_focus'] = true;

		// Coupon fields support currency settings.
		// The format dropdown is hidden, and always set to currency.
		$settings['format'] = true;

		return $settings;
	}

	/**
	 * @return string
	 */
	protected function html5_input_type() {
		return 'text';
	}

	/**
	 * @param array $args
	 * @param array $shortcode_atts
	 *
	 * @return string
	 */
	public function front_field_input( $args, $shortcode_atts ) {
		$input                      = parent::front_field_input( $args, $shortcode_atts );
		$coupon_wrapper_html_params = array(
			'class' => 'frm-coupon-wrapper',
		);
		$apply_button_html_params   = array(
			'class'    => 'frm-apply-coupon',
			'role'     => 'button',
			'tabindex' => 0,
		);

		$form_id = $this->get_form_id();
		$value   = $this->prepare_esc_value();

		if ( $this->is_editing_entry() ) {
			$entry_id    = $this->get_editing_entry_id();
			$coupon_code = FrmCouponsAppHelper::get_coupon_code_for_entry( $entry_id );
			$discount    = $value;

			if ( $coupon_code ) {
				// Call get_discount_for_coupon so the coupon is validated and the raw amount is set.
				FrmCouponsAppHelper::get_discount_for_coupon( $coupon_code, $form_id, '0.00', $entry_id );

				$has_valid_applied_discount = false !== $discount && '0.00' !== $discount && '0' !== $discount;
			} else {
				$has_valid_applied_discount = false;
			}
		} else {
			if ( $this->posting_data() ) {
				$coupon_code = $this->check_post_data_for_coupon_code();
				$total_value = $this->determine_total_value_for_coupon_validation( $value );
			} else {
				$coupon_code = $value;
				$total_value = '0.00';
			}

			$discount                   = FrmCouponsAppHelper::get_discount_for_coupon( $coupon_code, $form_id, $total_value );
			$has_valid_applied_discount = '0.00' !== FrmCouponsAppHelper::get_last_coupon_raw_amount();
		}

		$discount_html = $has_valid_applied_discount ? $this->get_discount_html( $discount, $coupon_code ) : '';
		if ( $has_valid_applied_discount ) {
			$apply_button_html_params['class'] .= ' frm-disabled';
		}

		// Stripe away front end number formatting.
		// We really just want currency settings for displaying discounts.
		$input = str_replace( 'frm-has-number-format', '', $input );

		// Open wrapper.
		$html = '<div ' . FrmAppHelper::array_to_html_params( $coupon_wrapper_html_params ) . '>';

		// This field is used for calculations. It is a hidden number, set when a coupon is applied.
		$html .= $this->prepare_hidden_coupon_discount_input( $input );

		// Place the hidden coupon code input after the discount input so querySelector() checks
		// for hidden inputs matches the first input. This stores the coupon code that is being applied.
		$html .= $this->prepare_hidden_coupon_code_input( $input, $args );

		// Place the visible code input. This is where the user enters a coupon code.
		// When a coupon code is added, this input is cleared, so it does not include the code on submit.
		$html .= $this->prepare_code_input( $input, $args, $has_valid_applied_discount );

		$html .= '<a ' . FrmAppHelper::array_to_html_params( $apply_button_html_params ) . '>';
		$html .= esc_html( $this->get_apply_button_text() );
		$html .= '</a>';

		// Close wrapper.
		$html .= '</div>';

		if ( '' === $value ) {
			return $html;
		}

		$html .= $discount_html;

		return $html;
	}

	/**
	 * @since 1.0
	 *
	 * @return bool
	 */
	private function posting_data() {
		return 'POST' === FrmAppHelper::get_server_value( 'REQUEST_METHOD' );
	}

	/**
	 * @param string $discount
	 * @param string $coupon_code
	 *
	 * @return string
	 */
	public function get_discount_html( $discount, $coupon_code ) {
		if ( '' === $coupon_code ) {
			return '';
		}

		$raw_discount = FrmCouponsAppHelper::get_last_coupon_raw_amount();
		if ( is_null( $raw_discount ) ) {
			return '';
		}

		$discount_text = '<strong>[code]</strong> &mdash; [detailed_discount]';

		/**
		 * @since 1.0
		 *
		 * @param string $discount_text
		 * @param array  $args
		 */
		$filtered_discount_text = apply_filters(
			'frm_coupon_discount_text',
			$discount_text,
			array(
				'coupon_code'  => $coupon_code,
				'discount'     => $discount,
				'raw_discount' => $raw_discount,
				'field'        => $this->field,
			)
		);

		if ( is_string( $filtered_discount_text ) ) {
			$discount_text = $filtered_discount_text;
		} else {
			_doing_it_wrong( __METHOD__, 'The frm_coupon_discount_text filter must return a string.', '1.0' );
		}

		$discount_text = str_replace( '[code]', esc_html( $coupon_code ), $discount_text );

		if ( false !== strpos( $discount_text, '[detailed_discount]' ) ) {
			$discount_text = str_replace( '[detailed_discount]', $this->get_detailed_discount_html( $discount, $raw_discount ), $discount_text );
		}

		$html  = '<div class="frm-applied-coupon">';
		$html .= wp_kses_post( $discount_text );
		$html .= $this->get_dismiss_icon_html();
		$html .= '</div>';

		return $html;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $discount
	 * @param string $raw_discount
	 *
	 * @return string
	 */
	private function get_detailed_discount_html( $discount, $raw_discount ) {
		$is_percent_discount = '%' === substr( $raw_discount, -1 );

		if ( $is_percent_discount ) {
			// translators: %s is the discount amount.
			$html = sprintf( esc_html__( '%s Off', 'formidable-coupons' ), esc_html( $raw_discount ) );

			if ( '0.00' !== $discount ) {
				$html .= ' (' . esc_html( $this->prepare_formatted_discount_amount( $discount ) ) . ')';
			}

			return $html;
		}

		if ( '0.00' === $discount ) {
			return '';
		}

		return esc_html( $this->prepare_formatted_discount_amount( $discount ) );
	}

	/**
	 * Get a note to display as an inline field error if the minimum order value is not met.
	 *
	 * @since 1.0
	 *
	 * @param string $discount
	 *
	 * @return string
	 */
	public function get_minimum_order_value_note( $discount ) {
		if ( '0.00' !== $discount ) {
			return '';
		}

		$total_value = FrmCouponsAppHelper::get_last_calc_total_value();
		if ( '0.00' === $total_value ) {
			return '';
		}

		$coupon = FrmCouponsAppHelper::get_last_coupon_match();
		if ( ! $coupon ) {
			return '';
		}

		$data = json_decode( $coupon->post_content );
		if ( ! is_object( $data ) ) {
			return '';
		}

		if ( empty( $data->minimum_order_value ) ) {
			return '';
		}

		if ( floatval( $total_value ) >= floatval( $data->minimum_order_value ) ) {
			return '';
		}

		$formatted_minimum_order_value = $this->prepare_formatted_discount_amount( $data->minimum_order_value );
		$note                          = sprintf(
			// translators: %s is the formatted minimum order value.
			esc_html__( 'Discount not applied. %s minimum not met.', 'formidable-coupons' ),
			esc_html( $formatted_minimum_order_value )
		);

		/**
		 * @since 1.0
		 *
		 * @param string $note
		 * @param string $formatted_minimum_order_value
		 * @param array  $args
		 */
		$filtered_note = apply_filters(
			'frm_coupon_minimum_order_value_note',
			$note,
			$formatted_minimum_order_value,
			array(
				'coupon'      => $coupon,
				'total_value' => $total_value,
				'field'       => $this->field,
			)
		);

		if ( ! is_string( $filtered_note ) ) {
			_doing_it_wrong( __METHOD__, 'The frm_coupon_minimum_order_value_note filter must return a string.', '1.0' );
			return $note;
		}

		return $filtered_note;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $discount
	 *
	 * @return string
	 */
	private function prepare_formatted_discount_amount( $discount ) {
		return FrmCouponsAppHelper::format_amount_as_currency_for_coupon_field( $discount, $this->field );
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	private function get_dismiss_icon_html() {
		return '<svg class="frm-dismiss-discount" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 16 16"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m11.5 4.5-7 7M4.5 4.5l7 7"/></svg>';
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	private function get_apply_button_text() {
		return FrmCouponsAppHelper::get_apply_button_text( $this->field );
	}

	/**
	 * Prepare the visible input where users input their coupon code.
	 *
	 * @since 1.0
	 *
	 * @param string $input
	 * @param array  $args
	 * @param bool   $has_valid_applied_discount
	 *
	 * @return string
	 */
	private function prepare_code_input( $input, $args, $has_valid_applied_discount ) {
		$value = $this->prepare_esc_value();

		// Remove the item meta from the code input.
		// This only stores temporary data, so we do not care what sends on POST.
		$code_input = str_replace( 'name="item_meta[' . $args['field_id'] . ']"', '', $input );

		// Set a unique ID for the code input.
		$code_input = str_replace( 'id="' . $args['html_id'] . '"', 'id="' . $args['html_id'] . '_code"', $code_input );

		// The visible code input should always be empty on load.
		$code_input = str_replace( 'value="' . $value . '"', 'value=""', $code_input );

		if ( $has_valid_applied_discount ) {
			$code_input = str_replace( 'value="', 'disabled="disabled" value="', $code_input );
		}

		return $code_input;
	}

	/**
	 * @param string $input
	 * @param array  $args
	 *
	 * @return string
	 */
	private function prepare_hidden_coupon_code_input( $input, $args ) {
		$hidden_code_input = $this->prepare_hidden_input( $input );

		// We cannot use item_meta[] because it seems to overwrite the value of the hidden input.
		$hidden_code_input = str_replace( 'name="item_meta[' . $args['field_id'] . ']"', 'name="' . $args['field_id'] . '_code"', $hidden_code_input );

		// Set a unique ID for the hidden code input.
		$hidden_code_input = str_replace( 'id="' . $args['html_id'] . '"', 'id="' . $args['html_id'] . '_applied_code"', $hidden_code_input );

		// The value needs to be the discount code.
		if ( $this->is_editing_entry() ) {
			$coupon_code = FrmCouponsAppHelper::get_coupon_code_for_entry( $this->get_editing_entry_id() );
		} elseif ( $this->posting_data() ) {
				$coupon_code = $this->check_post_data_for_coupon_code();
		} else {
			$value = $this->prepare_esc_value();
			if ( '' !== $value ) {
				$coupon_code = $value;
			}
		}

		if ( ! isset( $coupon_code ) || ! is_string( $coupon_code ) ) {
			$coupon_code = '';
		}

		$hidden_code_input = str_replace( 'value="' . $this->prepare_esc_value() . '"', 'value="' . $coupon_code . '"', $hidden_code_input );

		return $hidden_code_input;
	}

	/**
	 * This function only returns properly when called while rendering the input.
	 * If we're validating a field, this will return false.
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	private function is_editing_entry() {
		return is_array( $this->field ) && ! empty( $this->field['entry_id'] );
	}

	/**
	 * @since 1.0
	 *
	 * @return int
	 */
	private function get_editing_entry_id() {
		return $this->field['entry_id'];
	}

	/**
	 * @param string $input
	 *
	 * @return string
	 */
	private function prepare_hidden_coupon_discount_input( $input ) {
		$discount_input = $this->prepare_hidden_input( $input );
		$value          = $this->prepare_esc_value();
		if ( '' === $value ) {
			return $discount_input;
		}

		if ( $this->is_editing_entry() || $this->posting_data() ) {
			$discount_input = str_replace(
				'value="',
				'data-frmprice="' . esc_attr( $value ) . '" data-frmdiscount="true" value="',
				$discount_input
			);
			return $discount_input;
		}

		$coupon_code = $value;
		$discount    = FrmCouponsAppHelper::get_discount_for_coupon( $coupon_code, $this->get_form_id() );

		if ( '0.00' !== $discount ) {
			$new_value      = $discount;
			$discount_input = str_replace(
				'value="',
				'data-frmprice="' . esc_attr( $new_value ) . '" data-frmdiscount="true" value="',
				$discount_input
			);
		} else {
			$new_value = '';
		}

		$discount_input = str_replace(
			'value="' . $value . '"',
			'value="' . esc_attr( $new_value ) . '"',
			$discount_input
		);

		return $discount_input;
	}

	/**
	 * @param string $input
	 *
	 * @return string
	 */
	private function prepare_hidden_input( $input ) {
		$input = str_replace( 'type="text"', 'type="hidden"', $input );

		// Remove the placeholders from the hidden inputs.
		$placeholder = FrmField::get_option( $this->field, 'placeholder' );
		$input       = str_replace( 'placeholder="' . esc_attr( $placeholder ) . '"', '', $input );

		// Remove aria-invalid from the hidden inputs.
		$input = str_replace( 'aria-invalid="true"', '', $input );
		$input = str_replace( 'aria-invalid="false"', '', $input );

		// Remove data-invmsg from the hidden inputs.
		$invalid_message = FrmFieldsHelper::get_error_msg( $this->field, 'invalid' );
		$input           = str_replace( 'data-invmsg="' . esc_attr( $invalid_message ) . '"', '', $input );

		return $input;
	}

	/**
	 * @since 1.0
	 *
	 * @param array $args Includes 'field', 'display', and 'values'.
	 *
	 * @return void
	 */
	public function show_primary_options( $args ) {
		$field   = $args['field'];
		$coupons = get_posts(
			array(
				'post_type'   => 'frm_coupon',
				'numberposts' => -1,
			)
		);

		$selected_coupon_ids = $field['field_options']['allowed_coupons'] ?? array();
		if ( is_array( $selected_coupon_ids ) ) {
			$selected_coupon_ids = array_map( 'intval', $selected_coupon_ids );
		} else {
			$selected_coupon_ids = array();
		}

		include FrmCouponsAppHelper::path() . '/classes/views/allowed-coupons.php';

		parent::show_primary_options( $args );
	}

	/**
	 * This is called right after the default value settings.
	 *
	 * @since 1.0
	 *
	 * @param array $args - Includes 'field', 'display'.
	 *
	 * @return void
	 */
	public function show_after_default( $args ) {
		$field = $args['field'];
		include FrmCouponsAppHelper::path() . '/classes/views/button-text.php';
	}

	/**
	 * Make sure that when using separated values, labels are shown by default.
	 *
	 * @since 1.0
	 *
	 * @param array|string $value The value before display.
	 * @param array        $atts  Display attributes.
	 *
	 * @return array|string
	 */
	protected function prepare_display_value( $value, $atts ) {
		if ( is_array( $value ) ) {
			return $value;
		}

		if ( ! empty( $atts['format'] ) && 'number' === $atts['format'] ) {
			return $value;
		}

		$entry_id    = $atts['entry']->id ?? $atts['entry_id'] ?? 0;
		$coupon_code = $entry_id ? FrmCouponsAppHelper::get_coupon_code_for_entry( $entry_id ) : $this->check_post_data_for_coupon_code();

		if ( isset( $atts['show'] ) && 'code' === $atts['show'] ) {
			return $coupon_code;
		}

		if ( '' === $coupon_code ) {
			return $value;
		}

		return $this->format_coupon_code_and_discount_values( $coupon_code, $value, $entry_id );
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	private function check_post_data_for_coupon_code() {
		return FrmAppHelper::get_post_param( $this->get_field_id() . '_code', '', 'sanitize_text_field' );
	}

	/**
	 * @since 1.0
	 *
	 * @param string $coupon_code
	 * @param string $discount
	 * @param int    $entry_id
	 *
	 * @return string
	 */
	public function format_coupon_code_and_discount_values( $coupon_code, $discount, $entry_id = 0 ) {
		$formatted_value = FrmCouponsAppHelper::format_amount_as_currency_for_coupon_field( $discount, $this->field );
		$display_value   = '<strong>' . esc_html( $coupon_code ) . '</strong>';

		// translators: %s is the formatted discount amount.
		$display_value .= ' &mdash; ' . sprintf( esc_html__( '%s Off', 'formidable-coupons' ), esc_html( $formatted_value ) );

		$raw_discount = $this->get_raw_discount_for_coupon_code( $coupon_code, $entry_id );
		if ( is_null( $raw_discount ) ) {
			return $display_value;
		}

		$is_percent_discount = '%' === substr( $raw_discount, -1 );
		if ( $is_percent_discount ) {
			$display_value .= ' (' . esc_html( $raw_discount ) . ')';
		}

		return $display_value;
	}

	/**
	 * @param string $coupon_code
	 * @param int    $entry_id
	 *
	 * @return string|null
	 */
	private function get_raw_discount_for_coupon_code( $coupon_code, $entry_id ) {
		FrmCouponsAppHelper::get_discount_for_coupon( $coupon_code, $this->get_form_id(), '0.00', $entry_id );
		return FrmCouponsAppHelper::get_last_coupon_raw_amount();
	}

	/**
	 * Change the for attribute to include _code after the end, so the label matches the visible input.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	protected function for_label_html() {
		return 'for="field_[key]_code"';
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	protected function include_form_builder_file() {
		return FrmCouponsAppHelper::path() . '/classes/views/backend-field.php';
	}

	/**
	 * Make sure posted data for a coupon field is valid.
	 *
	 * @since 1.0
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function validate( $args ) {
		$errors          = array();
		$discount_amount = $args['value'];
		$coupon_code     = $this->check_post_data_for_coupon_code();

		if ( '' === $coupon_code ) {
			// When coupon code is NOT posted, make sure there is no discount amount posted.
			if ( $discount_amount ) {
				$errors[ 'field' . $args['id'] ] = esc_html__( 'An invalid discount amount was submitted. No coupon codes were detected.', 'formidable-coupons' );
			}

			return $errors;
		}

		$total_value = $this->determine_total_value_for_coupon_validation( $discount_amount );
		$entry_id    = FrmAppHelper::get_post_param( 'id', 0, 'absint' );

		// When coupon code IS posted, make sure the posted discount amount is valid.
		$expected_discount_amount = FrmCouponsAppHelper::get_discount_for_coupon( $coupon_code, $this->get_form_id(), $total_value, $entry_id );
		$raw_discount_amount      = FrmCouponsAppHelper::get_last_coupon_raw_amount();

		if ( '0.00' === $raw_discount_amount ) {
			$errors[ 'field' . $args['id'] ] = esc_html__( 'The coupon code submitted is invalid.', 'formidable-coupons' );
			return $errors;
		}

		if ( floatval( $discount_amount ) !== floatval( $expected_discount_amount ) ) {
			$errors[ 'field' . $args['id'] ] = esc_html__( 'The discount amount submitted does not match the discount amount for the coupon.', 'formidable-coupons' );
		}

		return $errors;
	}

	/**
	 * Check the undiscounted total field or calculation field value,
	 * to determine that the posted coupon discount amount is valid.
	 *
	 * @since 1.0
	 *
	 * @param string $posted_discount_amount
	 *
	 * @return string
	 */
	private function determine_total_value_for_coupon_validation( $posted_discount_amount ) {
		$form_id       = $this->get_form_id();
		$this_field_id = $this->get_field_id();
		$total_field   = FrmProFormsHelper::has_field( 'total', $form_id );
		if ( $total_field ) {
			if ( ! empty( $_POST['item_meta'][ $total_field->id ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				// Total fields include the discount, so we need to add the total
				// and the discount to determine the real total.
				$undiscounted_total = floatval( $_POST['item_meta'][ $total_field->id ] ) + floatval( $posted_discount_amount ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				return (string) $undiscounted_total;
			}

			return '0.00';
		}

		$target_calc_shortcode       = '[' . $this_field_id . ']';
		$fields_with_shortcode_match = FrmDb::get_results(
			'frm_fields',
			array(
				'form_id'             => $form_id,
				'field_options LIKE'  => $target_calc_shortcode,
				'field_options LIKE ' => '"calc"',
			),
			'id, field_options'
		);
		if ( ! $fields_with_shortcode_match ) {
			return '0.00';
		}

		foreach ( $fields_with_shortcode_match as $field ) {
			FrmAppHelper::unserialize_or_decode( $field->field_options );
			if ( ! is_array( $field->field_options ) || empty( $field->field_options['calc'] ) ) {
				continue;
			}

			$shortcodes = $this->get_shortcodes_from_string( $field->field_options['calc'] );
			if ( ! $shortcodes ) {
				continue;
			}

			$calc = $field->field_options['calc'];

			foreach ( $shortcodes as $shortcode ) {
				if ( is_numeric( $shortcode ) ) {
					$field_id = (int) $shortcode;
				} else {
					$field_id = (int) FrmField::get_id_by_key( $shortcode );
				}

				if ( ! $field_id ) {
					continue;
				}

				if ( $field_id === $this_field_id ) {
					// Treat the discount as 0 as we're trying to determine the undiscounted total value.
					$shortcode_value = '0';
				} elseif ( isset( $_POST['item_meta'][ $field_id ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
					if ( is_array( $_POST['item_meta'][ $field_id ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
						$shortcode_value   = 0;
						$posted_array_data = wp_unslash( $_POST['item_meta'][ $field_id ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						foreach ( $posted_array_data as $value ) {
							$shortcode_value += floatval( wp_unslash( $value ) );
						}
						unset( $posted_array_data );
						$shortcode_value = (string) $shortcode_value;
					} else {
						$shortcode_value = sanitize_text_field( wp_unslash( $_POST['item_meta'][ $field_id ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
						$shortcode_value = (string) $this->maybe_normalize_posted_value( $shortcode_value, $field_id );
					}
				} else {
					$shortcode_value = $this->check_for_repeater_field_value( $field_id );
				}

				$calc = str_replace( '[' . $shortcode . ']', $shortcode_value, $calc );
			}

			return FrmProMathController::math_shortcode(
				array(
					'thousands_sep' => '',
					'decimal'       => 2,
				),
				$calc
			);
		}

		return '0.00';
	}

	/**
	 * @since 1.0
	 *
	 * @param string $value
	 * @param int    $field_id
	 *
	 * @return float
	 */
	private function maybe_normalize_posted_value( $value, $field_id ) {
		$field  = FrmField::getOne( $field_id );
		$format = $field->field_options['format'];

		if ( FrmCurrencyHelper::is_currency_format( $format ) ) {
			$value = FrmProCurrencyHelper::normalize_formatted_numbers( $field, $value );
		}

		return floatval( $value );
	}

	/**
	 * Check posted repeater data for a field ID match.
	 *
	 * @since 1.0
	 *
	 * @param int $field_id
	 *
	 * @return string
	 */
	private function check_for_repeater_field_value( $field_id ) {
		if ( empty( $_POST ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return '0';
		}

		$field = FrmField::getOne( $field_id );
		if ( empty( $field->field_options['in_section'] ) ) {
			return '0';
		}

		$repeater = FrmField::getOne( $field->field_options['in_section'] );
		if ( ! $repeater || empty( $repeater->field_options['repeat'] ) ) {
			return '0';
		}

		if ( ! isset( $_POST['item_meta'][ $repeater->id ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return '0';
		}

		$repeater_meta = wp_unslash( $_POST['item_meta'][ $repeater->id ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$row_ids       = $repeater_meta['row_ids'] ?? array();

		$value = 0;
		foreach ( $row_ids as $row_id ) {
			if ( ! isset( $repeater_meta[ $row_id ] ) ) {
				continue;
			}

			$row_meta = $repeater_meta[ $row_id ];
			if ( ! isset( $row_meta[ $field_id ] ) ) {
				continue;
			}

			$value += $this->maybe_normalize_posted_value( $row_meta[ $field_id ], $field_id );
		}

		return (string) $value;
	}

	/**
	 * Get shortcodes from string.
	 *
	 * @since 1.0
	 *
	 * @param string $string
	 *
	 * @return array
	 */
	private function get_shortcodes_from_string( $string ) {
		preg_match_all( '/\[(\d+|[\w\s]+)\]/', $string, $matches );
		if ( empty( $matches[1] ) ) {
			return array();
		}
		return array_unique( $matches[1] );
	}

	/**
	 * @since 1.0
	 *
	 * @return int
	 */
	private function get_field_id() {
		return is_array( $this->field ) ? (int) $this->field['id'] : (int) $this->field->id;
	}

	/**
	 * @return int
	 */
	private function get_form_id() {
		return is_array( $this->field ) ? (int) $this->field['form_id'] : (int) $this->field->form_id;
	}
}
