<?php
/**
 * Get the translated values into the forms and fields.
 */
class FrmWpmlAppController {

	//filter the form description and title before displaying
	public static function setup_frm_wpml_form( $form ) {

		$form_keys = FrmWpmlString::get_form_strings( $form );

		foreach ( $form_keys as $key ) {
			$string_name = $form->id . '_' . $key;
			if ( isset( $form->{$key} ) && $form->{$key} !== '' ) {
				$form->{$key} = self::get_single_translated_string( $string_name, $form->{$key} );
			} else if ( isset( $form->options[ $key ] ) && $form->options[ $key ] !== '' ) {
				$form->options[ $key ] = self::get_single_translated_string( $string_name, $form->options[ $key ] );
			}
		}

		return $form;
	}

	/**
	 * Retrieve a single translated (or untranslated) string
	 *
	 * @since 1.04
	 * @param string       $name
	 * @param string|array $value
	 * @return string|array
	 */
	private static function get_single_translated_string( $name, $value ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $k => $string ) {
				$value[ $k ] = apply_filters( 'wpml_translate_single_string', $string, 'formidable', $name . '-' . $k );
			}
		} else {
			$value = apply_filters( 'wpml_translate_single_string', $value, 'formidable', $name );
		}
		return stripslashes_deep( $value );
	}

	// filter form last, after button name may have been changed
	public static function setup_frm_wpml_form_vars( $values, $entry ) {
		$form = FrmForm::getOne( $entry->form_id );

		if ( isset( $form->options['edit_value'] ) && $values['edit_value'] == $form->options['edit_value'] ) {
			$values['edit_value'] = self::get_single_translated_string( $entry->form_id . '_edit_value', $values['edit_value'] );
		}

		return $values;
	}

	/*
	* If a term is excluded in the settings, exclude it for all languages
	*/
	public static function filter_taxonomies( $exclude, $field ) {
		if ( empty( $exclude ) ) {
			// don't continue if there is nothing to exclude
			return $exclude;
		}

		$default_language = FrmWpmlAppHelper::get_default_language();
		$current_lang = FrmWpmlAppHelper::get_current_language();

		if ( $current_lang == $default_language ) {
			// don't check if the excluded options are the correct ones to exclude
			return $exclude;
		}

		$post_type = FrmProFormsHelper::post_type( $field['form_id'] );
		$taxonomy = FrmProAppHelper::get_custom_taxonomy( $post_type, $field );

		global $sitepress;

		$excluded_ids = explode( ',', $exclude );
		foreach ( $excluded_ids as $id ) {
			$trid = $sitepress->get_element_trid( $id, 'tax_' . $taxonomy );
			$translations = $sitepress->get_element_translations( $trid, 'tax_' . $taxonomy );

			if ( isset( $translations[ $current_lang ] ) ) {
				$excluded_ids[] = $translations[ $current_lang ]->term_id;
			}
		}

		$exclude = implode( ',', $excluded_ids );

		return $exclude;
	}

	public static function captcha_lang( $lang ) {
		$current_lang = FrmWpmlAppHelper::get_current_language();
		$allowed = FrmAppHelper::locales( 'captcha' );

		if ( isset( $allowed[ $current_lang ] ) ) {
			$lang = $current_lang;
		}

		return $lang;
	}

	public static function submit_button_label( $submit, $form ) {
		global $frm_vars;

		//check if next button needs to be translated
		if ( ! isset( $frm_vars['next_page'][ $form->id ] ) || empty( $frm_vars['next_page'][ $form->id ] ) ) {
			return $submit;
		}

		$field = $frm_vars['next_page'][ $form->id ];

		if ( ! is_object( $field ) || $submit != $field->name ) {
			return $submit;
		}

		$submit = self::get_single_translated_string( $form->id . '_field-' . $field->id . '-name', $submit );

		return $submit;
	}

	/**
	 * Switch out the translated options/values in a field
	 *
	 * @param array $values
	 * @param object $field
	 * @return array
	 */
	public static function setup_translated_field( $values, $field ) {
		//don't interfere with the form builder page
		if ( self::is_builder_page() ) {
			return $values;
		}

		$prev_default = $values['default_value'];
		self::set_field_array_value( $field, $values );

		if ( $values['value'] == $prev_default ) {
			$values['value'] = $values['default_value'];
		}

		if ( 'lookup' === $values['type'] && 'select' === $values['data_type'] ) {
			reset( $values['options'] );
			$first_key = key( $values['options'] );
			$values['options'][ $first_key ] = $values['placeholder'];
		}

		if ( ! in_array( $values['type'], array( 'select', 'checkbox', 'radio', 'data', 'product', 'likert', 'lookup' ), true ) || $field->type === 'user_id' ) {
			return $values;
		}

		self::set_field_choices_array( $values );

		return $values;
	}

	/**
	 * Don't interfere with the form builder page
	 *
	 * @since 1.05
	 */
	private static function is_builder_page() {
		$action = FrmAppHelper::simple_get( 'frm_action', 'sanitize_title' );
		return FrmAppHelper::is_admin_page( 'formidable' ) && $action !== 'translate';
	}

	/**
	 * Use the field object to get field values. This is a more basic version of the
	 * setup_translated_field function.
	 *
	 * @since 1.05
	 * @param object $field
	 */
	public static function setup_translated_field_object( $field ) {
		$values = $field->field_options;
		self::set_field_array_value( $field, $values );
		$field->field_options = $values;
		return $field;
	}

	/**
	 * Translates the specified properties of a field object.
	 *
	 * @since 1.07
	 *
	 * @param object $field Field object.
	 * @param array $properties_to_translate Array of properties to translate.
	 *
	 * @return object The field object with the specified properties translated.
	 */
	public static function translate_field_properties( $field, $properties_to_translate = array( 'name', 'description' ) ) {
		foreach ( (array) $properties_to_translate as $property ) {
			if ( empty( $field->$property ) ) {
				continue;
			}

			$form_id          = self::get_form_id_from_field_object( $field );
			$key              = self::get_translation_key_for_field( $form_id, $field->id, $property );
			$field->$property = self::get_single_translated_string( $key, $field->$property );
		}

		return $field;
	}

	/**
	 * Returns the form id for a field.  If field is in a Repeater, the parent's form id is returned.
	 *
	 * @since 1.07
	 *
	 * @param object $field A field object.
	 *
	 * @return int Form id of field.
	 */
	private static function get_form_id_from_field_object( $field ) {
		$section_id = FrmField::get_option( $field, 'in_section' );
		if ( $section_id ) {
			$field = FrmField::getOne( (int) $section_id );
		}

		return isset( $field->form_id ) ? $field->form_id : 0;
	}

	/**
	 * @since 1.05
	 * @param object $field
	 * @param array  $values
	 */
	private static function set_field_array_value( $field, &$values ) {
		$form_id = self::form_id_for_field( $values, $field->form_id );
		$keys    = FrmWpmlString::field_translation_keys( $field );

		foreach ( $keys as $key ) {
			if ( ! FrmWpmlString::field_option_needs_translation( $values, $key ) ) {
				continue;
			}

			$name = self::get_translation_key_for_field( $form_id, $field->id, $key );
			$args = array(
				'name'    => $name,
				'key'     => $key,
				'field'   => $field,
				'sub_key' => '',
			);

			if ( isset( $values[ $key ] ) && is_array( $values[ $key ] ) ) {
				foreach ( $values[ $key ] as $k => $v ) {
					$args['sub_key'] = '-' . $k;
					self::add_single_translated_value( $args, $values );
				}
			} else {
				self::add_single_translated_value( $args, $values );
			}
		}
	}

	/**
	 * Generates the key used to store and retrieve a field's property translation in WPML.
	 *
	 * @since 1.07
	 *
	 * @param  int    $form_id Id of form.
	 * @param  int    $field_id Id of field.
	 * @param  string   $key Property of field to be translated.
	 *
	 * @return string Key used to store and retrieve a field's translation in WMPL
	 */
	private static function get_translation_key_for_field( $form_id, $field_id, $key ) {
		return $form_id . '_field-' . $field_id . '-' . $key;
	}

	/**
	 * @since 1.05
	 * @param array $args
	 * @param array $values
	 */
	private static function add_single_translated_value( $args, &$values ) {
		if ( ! icl_st_is_registered_string( 'formidable', $args['name'] . $args['sub_key'] ) ) {
			return;
		}

		$key            = $args['key'];
		$values[ $key ] = self::get_single_translated_string( $args['name'], $values[ $key ] );

		if ( class_exists( 'FrmProFieldsHelper' ) ) {
			$values[ $key ] = FrmProFieldsHelper::get_default_value( $values[ $key ], $args['field'], false, ( $key === 'default_value' ) );
		}
	}

	/**
	 * @since 1.05
	 * @param array $values
	 * @todo Should lookup values be included here?
	 */
	private static function set_field_choices_array( &$values ) {
		if ( ! isset( $values['form_id'] ) ) {
			return;
		}

		$form_id = $values['form_id'];
		$sep_val = isset( $values['separate_value'] ) ? $values['separate_value'] : 0;

		if ( is_array( $values['options'] ) && ! isset( $values['options']['label'] ) ) {
			foreach ( $values['options'] as $index => $choice ) {
				if ( is_array( $choice ) ) {
					$choice = isset( $choice['label'] ) ? $choice['label'] : reset( $choice );

					// limit to 160 chars
					$string_name = FrmWpmlAppHelper::get_safe_substring( $form_id . '_field-' . $values['id'] . '-choice-' . $choice );
					$values['options'][ $index ]['label'] = self::get_single_translated_string( $string_name, $choice );

					if ( ! $sep_val && isset( $values['options'][ $index ]['value'] ) ) {
						$values['options'][ $index ]['value'] = $choice;
					}
				} else {
					// limit to 160 chars
					$string_name = FrmWpmlAppHelper::get_safe_substring( $form_id . '_field-' . $values['id'] . '-choice-' . $choice );

					if ( ( isset( $values['use_key'] ) && $values['use_key'] ) || $sep_val || 'data' == $values['type'] || 'lookup' === $values['type'] ) {
						$values['options'][ $index ] = self::get_single_translated_string( $string_name, $choice );
					} else {
						$values['options'][ $index ] = array(
							'label' => self::get_single_translated_string( $string_name, $choice ),
							'value' => $choice,
						);

						$values['separate_value'] = true;
					}
				}
			}
		} else {
			if ( is_array( $values['options'] ) ) {
				$string_name = FrmWpmlAppHelper::get_safe_substring( $form_id . '_field-' . $values['id'] . '-choice-' . $values['options']['label'] );
				$values['options']['label'] = self::get_single_translated_string( $string_name, $values['options']['label'] );
			} else {
				$string_name = FrmWpmlAppHelper::get_safe_substring( $form_id . '_field-' . $values['id'] . '-choice-' . $values['options'] );
				$values['options'] = self::get_single_translated_string( $string_name, $values['options'] );
			}
		}
	}

	/**
	 * Use the parent form id for repeaters, but not for embedded forms.
	 *
	 * @since 1.05
	 * @param array $values
	 * @param int   $default The fallback form id
	 * @return int
	 */
	private static function form_id_for_field( $values, $default = 0 ) {
		if ( ! isset( $values['form_id'] ) ) {
			return $default;
		}

		$embedded = isset( $values['in_embed_form'] ) && ! empty( $values['in_embed_form'] );
		$form_id  = ( ! $embedded && isset( $values['parent_form_id'] ) && ! empty( $values['parent_form_id'] ) ) ? $values['parent_form_id'] : $values['form_id'];
		return $form_id;
	}

	/*
	* Filter out text values before main Formidable plugin does
	*
	* @return string of HTML
	*/
	public static function replace_form_shortcodes( $html, $form, $values = array() ) {
		preg_match_all( "/\[(if )?(back_label|draft_label)\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s", $html, $shortcodes, PREG_PATTERN_ORDER );

		if ( empty( $shortcodes[0] ) ) {
			return $html;
		}

		foreach ( $shortcodes[0] as $short_key => $tag ) {
			$replace_with = '';
			$translation  = '';
			$atts = shortcode_parse_atts( $shortcodes[3][ $short_key ] );

			if ( $shortcodes[2][ $short_key ] == 'back_label' ) {
				$translation = self::get_single_translated_string( $form->id . '_prev_value', '' );
				if ( empty( $translation ) ) {
					// For reverse compatibilty.
					$translation = self::get_single_translated_string( $form->id . '_' . $shortcodes[2][ $short_key ], '' );
				}
			} elseif ( $shortcodes[2][ $short_key ] == 'draft_label' ) {
				$translation = self::get_single_translated_string( $form->id . '_' . $shortcodes[2][ $short_key ], '' );
			}

			if ( ! empty( $translation ) ) {
				$html = str_replace( $shortcodes[0][ $short_key ], $translation, $html );
			}
		}

		return $html;
	}

	/**
	 * Returns true if an error message of given type.
	 *
	 * @since 1.13
	 *
	 * @param object $field
	 * @param array  $errors
	 * @param string $type
	 *
	 * @return bool
	 */
	private static function is_error_type( $field, $errors, $type ) {
		if ( ! isset( $field->field_options[ $type ] ) ) {
			return false;
		}
		$error_message = str_replace( '[field_name]', $field->name, $field->field_options[ $type ] );
		return $errors[ 'field' . $field->id ] === $error_message;
	}

	/**
	 * Translate validation strings.
	 *
	 * @param array  $errors Array of errors set.
	 * @param object $field The field we are checking error for.
	 *
	 * @return array $errors Array of errors, probably translated.
	 */
	public static function setup_frm_wpml_validation( $errors, $field ) {

		$field->field_options = maybe_unserialize( $field->field_options );

		self::maybe_add_required_field_error_message( $field, $errors );

		//there are no errors to translate
		if ( ! isset( $errors[ 'field' . $field->id ] ) ) {
			return $errors;
		}

		$key = false;
		if ( self::is_error_type( $field, $errors, 'blank' ) ) {
			$key = 'blank';
		} elseif ( self::is_error_type( $field, $errors, 'invalid' ) ) {
			$key = 'invalid';
		} elseif ( self::is_error_type( $field, $errors, 'unique_msg' ) ) {
			$key = 'unique_msg';
		}

		if ( $key ) {
			$string_name = $field->form_id . '_field-' . $field->id . '-' . $key;

			$error_message = self::get_single_translated_string( $string_name, $errors[ 'field' . $field->id ] );

			$errors[ 'field' . $field->id ] = str_replace( '[field_name]', $field->name, $error_message );

			$field_conf = 'fieldconf_' . $field->id;
			if ( isset( $errors[ $field_conf ] ) ) {
				$errors[ $field_conf ] = self::get_single_translated_string( $field->form_id . '_field-' . $field->id . '-conf_msg', $errors[ $field_conf ] );
			}
		}

		return $errors;
	}

	/**
	 * Add the required field error message if default value should not pass validation and default value is in field
	 *
	 * @since 1.04
	 * @param object $field
	 * @param array $errors
	 */
	private static function maybe_add_required_field_error_message( $field, &$errors ) {
		if ( isset( $errors[ 'field' . $field->id ] ) && $errors[ 'field' . $field->id ] == $field->field_options['blank'] ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		$has_value = isset( $_POST['item_meta'][ $field->id ] ) && $_POST['item_meta'][ $field->id ] != '';

		if ( $field->required && FrmField::is_option_true_in_object( $field, 'default_blank' ) &&
			$has_value && FrmWpmlString::default_value_needs_translation( $field )
		) {
			$string_name = $field->form_id . '_field-' . $field->id . '-default_value';
			$default_value = self::get_single_translated_string( $string_name, $field->default_value );

			if ( class_exists( 'FrmProFieldsHelper' ) ) {
				$default_value = FrmProFieldsHelper::get_default_value( $default_value, $field, false, true );
			}

			// phpcs:ignore WordPress.Security.NonceVerification
			if ( $_POST['item_meta'][ $field->id ] == $default_value && ! isset( $errors[ 'field' . $field->id ] ) ) {
				$errors[ 'field' . $field->id ] = $field->field_options['blank'];
			}
		}
	}

	/**
	 * Show the translated values in the WooCommerce cart
	 *
	 * @since 1.05
	 */
	public static function translate_woo_cart( $cart_values, $atts ) {
		$field = $atts['field'];
		$string_name = $field->form_id . '_field-' . $field->id . '-name';
		$field_name = self::get_single_translated_string( $string_name, $field->name );
		$cart_values['name'] = '<strong>' . $field_name . '</strong>';
		return $cart_values;
	}

	/**
	 * Show the translated value for fields with separated values.
	 * This also covers the WooCommerce cart.
	 *
	 * @since 1.05
	 */
	public static function translate_display_value( $value, $field, $atts = array() ) {
		$show_value  = ( isset( $atts['show'] ) && $atts['show'] == 'value' );
		$saved_value = ( isset( $atts['saved_value'] ) && $atts['saved_value'] );
		$has_separate_option = in_array( $field->type, array( 'radio', 'checkbox', 'select' ) ) && FrmField::is_option_true( $field, 'separate_value' );
		if ( ! $has_separate_option || $saved_value || $value === false || $show_value === true ) {
			return $value;
		}

		foreach ( (array) $value as $v_key => $val ) {

			foreach ( $field->options as $opt_key => $opt ) {
				if ( ! is_array( $opt ) ) {
					continue;
				}

				$label = isset( $opt['label'] ) ? $opt['label'] : reset( $opt );
				$saved_value = isset( $opt['value'] ) ? $opt['value'] : $label;

				if ( $val == $label || $val == $saved_value ) {
					$string_name = FrmWpmlAppHelper::get_safe_substring( $field->form_id . '_field-' . $field->id . '-choice-' . $label );
					$label = self::get_single_translated_string( $string_name, $label );
					if ( is_array( $value ) ) {
						$value[ $v_key ] = $label;
					} else {
						$value = $label;
					}
				}
			}

			unset( $v_key, $val );
		}

		return $value;
	}

	public static function views_to_wpml( $replace_with, $tag, $atts, $field ) {
		if ( ! in_array( $field->type, array( 'select', 'radio', 'checkbox' ) ) || ( isset( $atts['show'] ) && $atts['show'] == 'value' ) ) {
			return $replace_with;
		}

		if ( is_array( $replace_with ) ) {
			foreach ( $replace_with as $k => $v ) {
				$string_name = FrmWpmlAppHelper::get_safe_substring( $field->form_id . '_field-' . $field->id . '-choice-' . $v );
				$replace_with[ $k ] = self::get_single_translated_string( $string_name, $v );
			}
			unset( $k, $v );

		} else {
			$string_name = FrmWpmlAppHelper::get_safe_substring( $field->form_id . '_field-' . $field->id . '-choice-' . $replace_with );
			$replace_with = self::get_single_translated_string( $string_name, $replace_with );
		}

		return $replace_with;
	}

	/*
	* Translate the message after an entry is deleted
	* @return string The translated value
	*/
	public static function delete_message( $message, $entry ) {
		$translation = self::get_single_translated_string( $entry->form_id . '_delete_msg', '' );
		if ( ! empty( $translation ) ) {
			$message = $translation;
		}
		return $message;
	}

	/**
	 * Check if a global setting has a translated value.
	 *
	 * @since 1.05
	 * @param string $setting The current value of the string.
	 * @param string $string The name of the string to translate.
	 * @param object $settings FrmSettings.
	 * @return string The translated global setting.
	 */
	public static function translate_global_setting( $setting, $string, $settings ) {
		remove_filter( 'frm_invalid_error_message', 'FrmWpmlAppController::translate_invalid_error_message', 10 );

		$form_id = $settings->current_form;
		return self::get_single_translated_string( $form_id . '_' . $string, $setting );
	}

	/**
	 * Check if a global registration plugin setting has a translated value.
	 *
	 * @since 1.05
	 * @param array  $messages The array of global messages.
	 * @param FrmRegGlobalSettings $settings
	 */
	public static function translate_reg_settings( $messages, $settings ) {
		$form_id = $settings->get_current_form();
		foreach ( $messages as $key => $message ) {
			$name = 'reg_' . $key;
			if ( ! empty( $form_id ) ) {
				$name = $form_id . '_' . $name;
			}

			$messages[ $key ] = self::get_single_translated_string( $name, $messages[ $key ] );
		}

		return $messages;
	}

	public static function set_ajax_language( $url ) {
		global $sitepress;
		if ( is_object( $sitepress ) ) {
			$url = add_query_arg( array( 'lang' => $sitepress->get_current_language() ), $url );
		}
		return $url;
	}

	/**
	 * Unregister a single ICL string
	 *
	 * @since 1.04
	 * @deprecated 1.05
	 * @param string $name
	 */
	public static function unregister_single_wpml_string( $name ) {
		_deprecated_function( __METHOD__, '1.05', 'FrmWpmlRegister::unregister' );
		$register = new FrmWpmlRegister();
		$register->unregister( $name );
	}

	/**
	 * @deprecated 1.05
	 * @param object $string
	 * @param array  $atts
	 * @return object
	 */
	public static function maybe_register_string( $string, $atts ) {
		_deprecated_function( __METHOD__, '1.05', 'FrmWpmlRegister::maybe_register' );
		$register = new FrmWpmlRegister();
		$register->maybe_register( $string, $atts );
	}

	/**
	 * @deprecated 1.05
	 */
	public static function get_string_language() {
		_deprecated_function( __METHOD__, '1.05', 'FrmWpmlSettingsController::get_string_language' );
		return FrmWpmlSettingsController::get_string_language();
	}

	/**
	 * Translate the incorrect field message
	 *
	 * @since 1.04
	 * @deprecated 1.05
	 * @param string $message
	 * @param array $args
	 * @return string
	 */
	public static function translate_invalid_error_message( $message, $args ) {
		if ( isset( $args['form'] ) && is_object( $args['form'] ) ) {
			_deprecated_function( __FUNCTION__, '1.05', 'Please update Formidable Forms to the latest version.' );
			$message = self::get_single_translated_string( $args['form']->id . '_invalid_msg', $message );
		}

		return $message;
	}

	/**
	 * Update the saved ICL strings
	 *
	 * @since 1.04
	 * @deprecated 1.05
	 * @param int $form_id
	 * @param array $values
	 */
	public static function update_saved_wpml_strings( $form_id, $values ) {
		_deprecated_function( __METHOD__, '1.05', 'FrmWpmlSettingsController::update_saved_wpml_strings' );
		FrmWpmlSettingsController::update_saved_wpml_strings( $form_id, $values );
	}

	/**
	 * @deprecated 1.05
	 */
	public static function get_translatable_items( $items, $type, $filter ) {
		_deprecated_function( __METHOD__, '1.05', 'FrmWpmlSettingsController::get_translatable_items' );
		return FrmWpmlSettingsController::get_translatable_items();
	}

	/**
	 * @deprecated 1.05
	 */
	public static function get_translatable_item( $item, $id ) {
		_deprecated_function( __METHOD__, '1.05' );
		return $item;
	}

	/**
	 * @deprecated 1.05
	 */
	public static function load_lang() {
		_deprecated_function( __METHOD__, '1.05', 'FrmWpmlSettingsController::load_lang' );
		FrmWpmlSettingsController::load_lang();
	}

	/**
	 * @deprecated 1.05
	 */
	public static function get_translatable_types( $types ) {
		_deprecated_function( __METHOD__, '1.05', 'FrmWpmlSettingsController::get_translatable_types' );
		return FrmWpmlSettingsController::get_translatable_types( $types );
	}

	/**
	 * @deprecated 1.05
	 */
	public static function get_link( $item, $id, $anchor, $hide_empty ) {
		_deprecated_function( __METHOD__, '1.05' );
	}

	/**
	 * @deprecated 1.05
	 */
	public static function delete_frm_wpml( $id ) {
		_deprecated_function( __METHOD__, '1.05', 'FrmWpmlSettingsController::delete_frm_wpml' );
		FrmWpmlSettingsController::delete_frm_wpml( $id );
	}

	/**
	 * @deprecated 1.05
	 */
	public static function translated() {
		_deprecated_function( __METHOD__, '1.05', 'FrmWpmlSettingsController::translated' );
		return FrmWpmlSettingsController::translated();
	}

	/**
	 * @deprecated 1.05
	 */
	public static function add_translate_button( $values ) {
		_deprecated_function( __METHOD__, '1.05', 'FrmWpmlSettingsController::add_translate_button' );
		FrmWpmlSettingsController::add_translate_button( $values );
	}

	/**
	 * @deprecated 1.05
	 */
	public static function translate( $message = '' ) {
		_deprecated_function( __METHOD__, '1.05', 'FrmWpmlSettingsController::translate' );
		FrmWpmlSettingsController::translate( $message );
	}

	/**
	 * @deprecated 1.05
	 */
	public static function update_translate() {
		_deprecated_function( __METHOD__, '1.05', 'FrmWpmlSettingsController::update_translate' );
		FrmWpmlSettingsController::update_translate();
	}

	/**
	 * @deprecated 1.05
	 */
	public static function include_updater() {
		_deprecated_function( __METHOD__, '1.05', 'FrmWpmlSettingsController::include_updater' );
		FrmWpmlSettingsController::include_updater();
	}

	/**
	 * @deprecated 1.04
	 */
	public static function setup_frm_wpml( $values, $field ) {
		_deprecated_function( __FUNCTION__, '1.04', 'FrmWpmlAppController::setup_translated_field' );
		return self::setup_translated_field( $values, $field );
	}
}
