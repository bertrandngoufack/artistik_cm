<?php
/**
 * Register and update strings.
 */
class FrmWpmlRegister {

	private $form;

	private $field;

	public function __construct( $args = array() ) {
		if ( isset( $args['form'] ) ) {
			$this->set_form( $args['form'] );
		}

		if ( isset( $args['field'] ) ) {
			$this->set_field( $args['field'] );
		}
	}

	/**
	 * @since 1.05
	 */
	public function set_form( $form ) {
		$this->form = $form;
		FrmForm::maybe_get_form( $this->form );
	}

	/**
	 * @since 1.05
	 */
	public function set_field( $field ) {
		$this->field = $field;
	}

	/**
	 * Unregister a single ICL string
	 *
	 * @since 1.05
	 * @param string|array $name
	 */
	public function unregister( $name ) {
		foreach ( (array) $name as $string ) {
			icl_unregister_string( 'formidable', $string );
			unset( $string );
		}
	}

	/**
	 * Register a single ICL string
	 *
	 * @since 1.05
	 * @param string $name
	 * @param string $value
	 */
	public function register( $name, $value ) {
		$name    = FrmWpmlAppHelper::get_safe_substring( $name );
		$default_language = apply_filters( 'wpml_default_language', '' );
		do_action( 'wpml_register_single_string', 'formidable', $name, $value, false, $default_language );
	}

	/**
	 * Update the saved ICL strings
	 *
	 * @since 1.05
	 * @param array $values
	 */
	public function update_form_fields( $values ) {
		if ( isset( $values['field_options'] ) && ! empty( $values['field_options'] ) ) {
			self::update_fields();
		}

		if ( isset( $values['options'] ) && ! empty( $values['options'] ) ) {
			self::update_form( $values['options'] );
		}
	}

	/**
	 * Update the ICL strings saved for all fields in a form
	 *
	 * @since 1.05
	 */
	private function update_fields() {
		$fields = FrmField::get_all_for_form( $this->form->id );

		foreach ( $fields as $field ) {
			self::update_field( $field );
		}

	}

	/**
	 * Update the ICL strings for a single field
	 *
	 * @since 1.04
	 * @param object $field
	 */
	private function update_field( $field ) {
		$keys = FrmWpmlString::get_field_option_keys_for_translations( $field->type );
		FrmWpmlString::remove_unused_keys( $field, $keys );

		foreach ( $keys as $key ) {

			$name = $this->form->id . '_field-' . $field->id . '-' . $key;

			if ( in_array( $key, array( 'name', 'description' ), true ) ) {
				$value = $field->{$key};
			} elseif ( $key === 'default_value' ) {
				$value = FrmWpmlString::get_default_value_for_translation( $field );
			} else {
				$value = isset( $field->field_options[ $key ] ) ? $field->field_options[ $key ] : '';
			}

			self::update_string( $name, $value );
		}
	}

	/**
	 * Update the ICL strings saved for a form's Settings
	 *
	 * @since 1.05
	 * @param array $posted_form_options
	 */
	private function update_form( $posted_form_options ) {
		$form_option_keys = FrmWpmlString::get_form_strings( $this->form );

		foreach ( $form_option_keys as $option_key ) {
			if ( isset( $posted_form_options[ $option_key ] ) ) {
				$value = $posted_form_options[ $option_key ];
				$name  = $this->form->id . '_' . $option_key;
				if ( is_array( $value ) ) {
					foreach ( $value as $k => $string ) {
						self::update_string( $name . '-' . $k, $string );
					}
				} else {
					self::update_string( $name, $value );
				}
			}
		}
	}

	/**
	 * Update a single ICL string
	 *
	 * @since 1.05
	 * @param string $name
	 * @param mixed $value
	 */
	public function update_string( $name, $value ) {
		if ( is_array( $value ) || $value === '' || $value === '*' ) {
			self::unregister( $name );
		} else {
			self::register( $name, $value );
		}
	}

	/**
	 * @param object $string
	 * @param array  $atts
	 * @return object
	 */
	public function maybe_register( $string, $atts ) {
		if ( is_array( $string->value ) ) {
			return $string->value;
		}

		if ( strpos( $string->name, $atts['id'] . '_field-' ) === 0 ) {
			self::maybe_register_field_string( $atts, $string );
		} else {
			self::maybe_register_form_string( $atts, $string );
		}

		$is_new = isset( $string->is_new ) && $string->is_new;
		if ( $is_new && $string->value != '' && ! is_array( $string->value ) ) {
			$str_name = FrmWpmlAppHelper::get_safe_substring( $string->name );
			self::register( $str_name, $string->value );
		}

		return $string->value;
	}

	/**
	 * @since 1.05
	 * @param object $string
	 * @param array  $atts
	 */
	private function maybe_register_field_string( $atts, &$string ) {
		$fid      = explode( '-', str_replace( $atts['id'] . '_field-', '', $string->name ), 2 );
		$field_id = $fid[0];
		$option   = $fid[1];
		$fields   = $atts['fields'];

		if ( isset( $fields[ $field_id ] ) ) {
			self::maybe_register_string_in_object( (array) $fields[ $field_id ], $option, $string );
			self::maybe_register_string_in_object( $fields[ $field_id ]->field_options, $option, $string );
		}
	}

	/**
	 * @since 1.05
	 * @param object $string
	 * @param array  $atts
	 */
	private function maybe_register_form_string( $atts, &$string ) {
		$form_option_name = str_replace( $atts['id'] . '_', '', $string->name );

		self::maybe_register_string_in_object( (array) $this->form, $form_option_name, $string );
		self::maybe_register_string_in_object( $this->form->options, $form_option_name, $string );
	}

	/**
	 * @since 1.05
	 * @param array  $item - The form or field object, or settings array.
	 * @param string $name - The name of the value in the object.
	 * @param object $string
	 */
	private function maybe_register_string_in_object( $item, $name, &$string ) {
		if ( isset( $item[ $name ] ) && $string->value != $item[ $name ] ) {
			$string->value  = $item[ $name ];
			$string->is_new = true;
		}
	}
}
