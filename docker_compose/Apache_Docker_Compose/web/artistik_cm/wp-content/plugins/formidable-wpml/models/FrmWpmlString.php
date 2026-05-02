<?php
/**
 * Get all the strings that need to be translated.
 */
class FrmWpmlString {

	/**
	 * Key for copied strings data in $frm_vars;
	 *
	 * @since 1.11
	 *
	 * @var string
	 */
	private static $copied_strings_key = 'frm_wpml_copied_strings';

	/**
	 * Get the keys for all form options that can be translated.
	 *
	 * @since 1.05
	 * @param object $form
	 */
	public static function get_form_strings( $form ) {
		if ( is_callable( 'FrmForm::translatable_strings' ) ) {
			return FrmForm::translatable_strings( $form );
		}

		_deprecated_function( __FUNCTION__, '1.05', 'Please update Formidable Forms to the latest version.' );

		$form_keys = array(
			'name',
			'description',
			'submit_value',
			'submit_msg',
			'success_msg',
			'email_subject',
			'email_message',
			'ar_email_subject',
			'ar_email_message',
			'draft_msg',
		);

		// Add edit options.
		if ( $form->editable ) {
			$form_keys[] = 'edit_value';
			$form_keys[] = 'edit_msg';
		}

		return $form_keys;
	}

	/**
	 * The used keys for field options.
	 *
	 * @since 1.05
	 * @param object $field
	 */
	public static function field_translation_keys( $field ) {
		$keys = self::get_field_option_keys_for_translations( $field->type );
		self::remove_unused_keys( $field, $keys );
		return $keys;
	}

	/**
	 * Get the keys for all field options that can be translated
	 *
	 * @since 1.05
	 * @param string $field_type
	 * @return array
	 */
	public static function get_field_option_keys_for_translations( $field_type ) {
		$field_obj = FrmFieldFactory::get_field_type( $field_type );
		if ( is_callable( array( $field_obj, 'translatable_strings' ) ) ) {
			return $field_obj->translatable_strings();
		}

		_deprecated_function( __FUNCTION__, '1.05', 'Please update Formidable Forms to the latest version.' );

		$keys = array(
			'name',
			'description',
			'default_value',
			'required_indicator',
			'invalid',
			'blank',
			'unique_msg',
		);

		switch ( $field_type ) {
			case 'end_divider':
				$keys = array( 'add_label', 'remove_label' );
				break;
			case 'divider':
				$keys = array( 'name', 'description' );
				break;
			case 'break':
				$keys = array( 'name' );
				break;
			case 'date':
				$keys[] = 'locale';
				break;
			case 'email':
			case 'password':
				$keys[] = 'conf_desc';
				break;
			case 'address':
				$keys[] = 'line1_desc';
				$keys[] = 'line2_desc';
				$keys[] = 'city_desc';
				$keys[] = 'state_desc';
				$keys[] = 'zip_desc';
				$keys[] = 'country_desc';
				break;
			case 'toggle':
				$keys[] = 'toggle_on';
				$keys[] = 'toggle_off';
		}

		return $keys;
	}

	/**
	 * Don't include the unique message unless it is needed.
	 *
	 * @since 1.05
	 * @param object $field
	 * @param array  $keys
	 */
	public static function remove_unused_keys( $field, &$keys ) {
		$remove = array();

		$is_unique = ( isset( $field->field_options['unique'] ) && $field->field_options['unique'] );
		if ( ! $is_unique ) {
			$remove[] = array_search( 'unique_msg', $keys );
		}

		if ( ! $field->required ) {
			$remove[] = array_search( 'blank', $keys );
		}

		$remove_if = array(
			'required_indicator' => '*',
			'toggle_on'          => 1,
			'toggle_off'         => 0,
		);
		foreach ( $remove_if as $opt => $value ) {
			$is_default = ( isset( $field->field_options[ $opt ] ) && $field->field_options[ $opt ] == $value );
			if ( $is_default ) {
				$remove[] = array_search( $opt, $keys );
			}
		}

		foreach ( $remove as $remove_key ) {
			if ( $remove_key !== false ) {
				unset( $keys[ $remove_key ] );
			}
		}
	}

	/**
	 * Get the default value for translating
	 * Do not translate default value for certain field types
	 * Do not translate shortcodes
	 *
	 * @since 1.05
	 * @param object $field
	 * @return array|string
	 */
	public static function get_default_value_for_translation( $field ) {
		if ( self::default_value_needs_translation( $field ) ) {
			$default_value = self::get_default_value( $field );
		} else {
			$default_value = '';
		}

		return $default_value;
	}

	/**
	 * Get the default value for a field
	 *
	 * @since 1.05
	 * @param object $field
	 * @return mixed
	 */
	private static function get_default_value( $field ) {
		if ( FrmField::is_option_true_in_object( $field, 'dyn_default_value' ) ) {
			$default_value = $field->field_options['dyn_default_value'];
		} else if ( isset( $field->default_value ) && $field->default_value ) {
			$default_value = $field->default_value;
		} else {
			$default_value = '';
		}

		return $default_value;
	}

	/**
	 * Check if a specific field option needs translating
	 *
	 * @since 1.05
	 * @param array $values
	 * @param string $key
	 * @return bool
	 */
	public static function field_option_needs_translation( $values, $key ) {
		$needs_translation = true;

		if ( ! isset( $values[ $key ] ) ) {
			$needs_translation = false;
		} elseif ( is_array( $values[ $key ] ) && ! in_array( $key, array( 'default_value', 'placeholder' ), true ) ) {
			$needs_translation = false;
		} elseif ( in_array( $values[ $key ], array( '', '*' ) ) ) {
			$needs_translation = false;
		} elseif ( $key === 'default_value' && self::default_value_needs_translation( $values ) === false ) {
			$needs_translation = false;
		}

		return $needs_translation;
	}

	/**
	 * Check if a field needs it default value translated
	 *
	 * @since 1.05
	 * @param object|array $field
	 * @return bool
	 */
	public static function default_value_needs_translation( $field ) {
		if ( is_object( $field ) ) {
			$field_type = $field->type;
		} else {
			$field_type = isset( $field['original_type'] ) ? $field['original_type'] : $field['type'];
		}

		return ! in_array( $field_type, array( 'radio', 'checkbox', 'select', 'scale', 'data', 'product' ), true );
	}

	public static function form_strings( $form ) {
		global $wpdb;
		if ( ! is_object( $form ) ) {
			$form = FrmForm::getOne( $form );
		}

		$string_data = array();

		self::get_translatable_strings_from_form_settings( $form, $string_data );

		$forms    = FrmForm::getAll( $wpdb->prepare( 'parent_form_id=%d or id=%d', $form->id, $form->id ) );
		$form_ids = wp_list_pluck( $forms, 'id' );
		$fields   = FrmField::getAll( array( 'fi.form_id' => $form_ids ), 'field_order' );

		$is_repeating = false;

		foreach ( $fields as $field ) {

			if ( FrmField::is_repeating_field( $field ) ) {
				$is_repeating = true;
			}
			self::get_translatable_strings_for_single_field( $field, $form, $is_repeating, $string_data );

			if ( $field->type == 'end_divider' && $is_repeating ) {
				$is_repeating = false;
			}
		}

		return $string_data;
	}

	/**
	 * Get the strings that need translation from a form's Settings
	 *
	 * @since 1.05
	 * @param object $form
	 * @param array $string_data
	 */
	private static function get_translatable_strings_from_form_settings( $form, &$string_data ) {
		$form_keys = self::get_form_strings( $form );

		// Add delete options.
		if ( $form->editable ) {
			$string_data['delete_msg'] = __( 'Your entry was successfully deleted', 'formidable' );
		}

		foreach ( $form_keys as $key ) {
			if ( isset( $form->{$key} ) && $form->{$key} != '' ) {
				$string_data[ $key ] = $form->{$key};
			} else if ( isset( $form->options[ $key ] ) && $form->options[ $key ] != '' && $form->options[ $key ] !== '[default-message]' ) {
				if ( is_array( $form->options[ $key ] ) ) {
					foreach ( $form->options[ $key ] as $k => $value ) {
						$string_data[ $key . '-' . $k ] = $value;
					}
				} else {
					$string_data[ $key ] = $form->options[ $key ];
				}
			}
		}

		// Add draft translations
		if ( isset( $form->options['save_draft'] ) && $form->options['save_draft'] ) {
			$string_data['draft_label'] = __( 'Save Draft', 'formidable' );
		}

		// Always update global strings in case they have changed.
		$frm_settings = FrmAppHelper::get_settings();
		if ( is_callable( array( $frm_settings, 'translatable_strings' ) ) ) {
			$register = new FrmWpmlRegister( compact( 'form' ) );
			foreach ( $frm_settings->translatable_strings() as $global_string ) {
				$string_data[ $global_string ] = $frm_settings->{$global_string};
				$register->update_string( $form->id . '-' . $global_string, $string_data[ $global_string ] );
			}
		} else {
			_deprecated_function( __FUNCTION__, '1.05', 'Please update Formidable Forms to the latest version.' );
			$string_data['invalid_msg'] = $frm_settings->invalid_msg;
		}

		self::add_registration_strings( $form, $string_data );
	}

	/**
	 * Get translatabe strings from the Registration add-on.
	 *
	 * @since 1.05
	 * @param object $form
	 * @param array  $string_data
	 * @return array
	 */
	private static function add_registration_strings( $form, &$string_data ) {
		if ( ! class_exists( 'FrmRegGlobalSettings' ) ) {
			return;
		}

		$registration_settings = new FrmRegGlobalSettings();
		if ( ! method_exists( $registration_settings, 'get_translatable_strings' ) ) {
			return;
		}

		$settings = FrmRegActionHelper::get_registration_settings_for_form( $form );
		if ( empty( $settings ) ) {
			// Don't add strings unless the form has a registration form action.
			return;
		}

		$keys   = $registration_settings->get_translatable_strings();
		$values = $registration_settings->get_global_messages();

		$register = new FrmWpmlRegister( compact( 'form' ) );

		foreach ( $keys as $key ) {
			$string_data[ 'reg_' . $key ] = $values[ $key ];
			$register->update_string( $form->id . '-reg_' . $key, $values[ $key ] );
		}
	}

	/**
	 * Get the strings that need translation for a single field.
	 *
	 * @since 1.04
	 * @since 2.05 The second parameter could be the form object of form ID.
	 *
	 * @param object          $field        Field object.
	 * @param int|object|null $form_id      Form ID or form object.
	 * @param bool            $is_repeating Is repeating field?
	 * @param array           $string_data  String data.
	 */
	private static function get_translatable_strings_for_single_field( $field, $form_id, $is_repeating, &$string_data ) {
		if ( is_numeric( $form_id ) ) {
			$form = FrmForm::getOne( $form_id );
		} else {
			$form = $form_id;
			if ( is_object( $form ) ) {
				$form_id = $form->id;
			}
		}

		if ( ! $form ) {
			return;
		}

		if ( $is_repeating && $field->type == 'end_divider' ) {
			self::add_translations_for_end_divider( $field, $string_data );
			return;
		}

		$keys = array(
			'name',
			'description',
			'default_value',
			'required_indicator',
			'blank',
			'unique_msg',
			'prev_value',
			'conf_msg',
		);
		self::remove_unused_keys( $field, $keys );

		foreach ( $keys as $key ) {
			// Some strings are duplicated, they have a form ID prefix version that work. This version with repeater ID as prefix won't work.
			if ( ! empty( $form->parent_form_id ) ) {
				continue;
			}

			$string_name = 'field-' . $field->id . '-' . $key;

			if ( $key === 'default_value' ) {
				$default_value = self::get_default_value_for_translation( $field );
				if ( is_array( $default_value ) ) {
					foreach ( $default_value as $k => $v ) {
						if ( $v !== '' ) {
							$string_data[ $string_name . '-' . $k ] = $v;
						}
					}
				} elseif ( $default_value !== '' ) {
					$string_data[ $string_name ] = $default_value;
				}
			} else if ( isset( $field->{$key} ) && $field->{$key} != '' && ! is_array( $field->{$key} ) ) {
				$string_data[ $string_name ] = $field->{$key};
			} else if ( isset( $field->field_options[ $key ] ) && $field->field_options[ $key ] != '' && ! is_array( $field->field_options[ $key ] ) ) {
				$string_data[ $string_name ] = $field->field_options[ $key ];
			}
		}

		if ( ! empty( $field->field_options['placeholder'] ) && is_array( $field->field_options['placeholder'] ) ) {
			foreach ( $field->field_options['placeholder'] as $pk => $pv ) {
				$string_data[ 'field-' . $field->id . '-placeholder-' . $pk ] = $pv;
			}
		}

		switch ( $field->type ) {
			case 'date':
				if ( isset( $field->field_options['locale'] ) ) {
					if ( $field->field_options['locale'] == '' ) {
						$field->field_options['locale'] = 'en';
					}
					$string_data[ 'field-' . $field->id . '-locale' ] = $field->field_options['locale'];
				}
				break;
			case 'address':
				foreach ( array( 'line1_desc', 'line2_desc', 'city_desc', 'state_desc', 'zip_desc', 'country_desc' ) as $address_text ) {
					$default_value = isset( $field->field_options[ $address_text ] ) ? $field->field_options[ $address_text ] : '';
					$string_data[ 'field-' . $field->id . '-' . $address_text ] = $default_value;
				}
				break;
			case 'email':
			case 'password':
				if ( isset( $field->field_options['conf_field'] ) && $field->field_options['conf_field'] ) {
					$string_data[ 'field-' . $field->id . '-conf_desc' ] = $field->field_options['conf_desc'];
				}

				// no break
			case 'url':
			case 'website':
			case 'phone':
			case 'image':
			case 'number':
			case 'file':
				if ( isset( $field->field_options['invalid'] ) && $field->field_options['invalid'] != '' ) {
					$string_data[ 'field-' . $field->id . '-invalid' ] = $field->field_options['invalid'];
				}
				break;
			case 'select':
			case 'checkbox':
			case 'radio':
			case 'likert':
			case 'lookup':
				$is_likert_row = self::is_likert_row( $field );

				if ( 'lookup' === $field->type && $field->field_options['data_type'] !== 'text' ) {
					$values = array_merge( (array) $field, $field->field_options );
					FrmProLookupFieldsController::maybe_get_initial_lookup_field_options( $values );
					$field->options = $values['options'];
				}

				if ( is_array( $field->options ) && ! isset( $field->options['label'] ) ) {
					foreach ( $field->options as $index => $choice ) {
						if ( is_array( $choice ) ) {
							$choice = isset( $choice['label'] ) ? $choice['label'] : reset( $choice );
						}

						$key                 = 'field-' . $field->id . '-choice-' . $choice;
						$string_data[ $key ] = $choice;

						if ( $is_likert_row ) {
							$source_key = 'field-' . $is_likert_row . '-choice-' . $choice;
							$dest_key   = $key;
							self::build_copied_strings( $source_key, $dest_key, array( 'form_id' => $field->form_id ) );
						}
					}
				} else {
					if ( is_array( $field->options ) ) {
						$field->options = isset( $field->options['label'] ) ? $field->options['label'] : reset( $field->options );
					}

					$key                 = 'field-' . $field->id . '-choice-' . $field->options;
					$string_data[ $key ] = $field->options;

					if ( $is_likert_row ) {
						$source_key = 'field-' . $is_likert_row . '-choice-' . $field->options;
						$dest_key   = $key;
						self::build_copied_strings( $source_key, $dest_key, array( 'form_id' => $field->form_id ) );
					}
				}
				break;
			case 'product':
				if ( is_array( $field->options ) ) {
					foreach ( $field->options as $product ) {
						if ( ! empty( $product['label'] ) ) {
							$string_data[ 'field-' . $field->id . '-choice-' . $product['label'] ] = $product['label'];
						}
					}
				}
				break;
		}
	}

	/**
	 * Checks if Surveys is installed and the given field is a Likert row.
	 *
	 * @since 1.11
	 *
	 * @param array $field Field data. This field could be stripped some parts.
	 * @return false|int   Return the Likert ID if true.
	 */
	private static function is_likert_row( $field ) {
		if ( method_exists( '\FrmSurveys\controllers\LikertController', 'is_likert_row' ) ) {
			return \FrmSurveys\controllers\LikertController::is_likert_row( $field );
		}
		return false;
	}

	/**
	 * Builds the list of string keys that will be copied from the source keys.
	 *
	 * @since 1.11
	 *
	 * @param string $source Source string key.
	 * @param string $dest   Destination string key.
	 * @param array  $args   Includes `form_id`.
	 */
	private static function build_copied_strings( $source, $dest, $args ) {
		global $frm_vars;
		$global_key = self::$copied_strings_key;

		$source = FrmWpmlAppHelper::prepend_form_id_and_get_safe_substring( $source, $args['form_id'] );
		$dest   = FrmWpmlAppHelper::prepend_form_id_and_get_safe_substring( $dest, $args['form_id'] );

		if ( ! isset( $frm_vars[ $global_key ] ) ) {
			$frm_vars[ $global_key ] = array();
		}

		$frm_vars[ $global_key ][ $dest ] = $source;
	}

	/**
	 * Adds copy data to the strings to display on the Translation page.
	 *
	 * @since 1.11
	 *
	 * @param array $strings Strings.
	 */
	public static function add_copy_data_to_strings( &$strings ) {
		global $frm_vars;
		$global_key = self::$copied_strings_key;
		if ( empty( $frm_vars[ $global_key ] ) ) {
			return;
		}

		// Build the names and indexes mapping.
		$names_indexes = array();
		foreach ( $strings as $index => $string ) {
			$names_indexes[ $string->name ] = $index;
			unset( $string );
		}

		foreach ( $strings as $string ) {
			if ( ! strpos( $string->name, '-choice-' ) ) {
				continue;
			}

			if ( ! empty( $frm_vars[ $global_key ][ $string->name ] ) ) {
				// This string is copied from source string. Store the source string id.
				$string->is_copied_from = $strings[ $names_indexes[ $frm_vars[ $global_key ][ $string->name ] ] ]->id;
				continue;
			}

			// This string could be the source of other copied strings.
			$dests = array();
			foreach ( $frm_vars[ $global_key ] as $dest => $source ) {
				if ( $source === $string->name ) {
					$dests[] = $strings[ $names_indexes[ $dest ] ]->id;
				}
			}

			if ( ! empty( $dests ) ) {
				// Store strings that are copied from this string.
				$string->is_copied_to = $dests;
			}
		}
	}

	/**
	 * Maybe print copy data inputs on Translation page.
	 *
	 * @since 1.11
	 *
	 * @param string $source_key Key of source string, to be used in POST request.
	 * @param array  $args       {
	 *     The arguments.
	 *
	 *     @type object $string               Current string data.
	 *     @type array  $strings_translations The mapping of string id and translation id.
	 *     @type string $lang                 The language code.
	 * }
	 */
	public static function maybe_print_copy_data_inputs( $source_key, $args ) {
		if ( empty( $args['string'] ) || empty( $args['string']->is_copied_to ) ) {
			return;
		}

		foreach ( $args['string']->is_copied_to as $str_id ) {
			if ( ! empty( $args['strings_translations'][ $str_id ] ) ) {
				// Translation for destination string exists, use the translation id.
				$dest_key = $args['strings_translations'][ $str_id ];
			} elseif ( ! empty( $args['lang'] ) ) {
				// Use {string_id}_{language_code}.
				$dest_key = $str_id . '_' . $args['lang'];
			}

			if ( isset( $dest_key ) ) {
				printf(
					'<input type="hidden" name="frm_wpml_copy[%1$s][]" value="%2$s" />',
					esc_attr( $source_key ),
					esc_attr( $dest_key )
				);
				unset( $dest_key );
			}
		}
	}

	/**
	 * Gets the mapping of string id and translation id.
	 *
	 * @since 1.11
	 *
	 * @param array $translations List of translations.
	 * @return array
	 */
	public static function get_strings_translations_mapping( $translations ) {
		$mapping = array();

		foreach ( $translations as $translation ) {
			$mapping[ $translation->string_id ] = $translation->id;
		}

		return $mapping;
	}

	/**
	 * Add translations for an end divider field
	 *
	 * @since 1.05
	 * @param object $field
	 * @param array $string_data
	 */
	private static function add_translations_for_end_divider( $field, &$string_data ) {
		$keys = array( 'add_label', 'remove_label' );

		foreach ( $keys as $key ) {
			$string_name = 'field-' . $field->id . '-' . $key;
			$string_data[ $string_name ] = $field->field_options[ $key ];
		}
	}
}
