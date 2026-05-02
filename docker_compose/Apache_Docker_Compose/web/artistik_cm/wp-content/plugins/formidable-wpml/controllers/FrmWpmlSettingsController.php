<?php

class FrmWpmlSettingsController {

	/**
	 * The number of translation strings displayed per page. This variable is here for backwards compatibility.
	 *
	 * @deprecated 1.13
	 * @var int
	 */
	const PER_PAGE = 20;

	/**
	 * The number of translation strings displayed per page.
	 *
	 * @since 1.13
	 * @var int|null
	 */
	private static $per_page;

	public static function include_updater() {
		if ( class_exists( 'FrmAddon' ) ) {
			FrmWpmlUpdate::load_hooks();
		}
	}

	public static function load_lang() {
		$plugin_folder = FrmWpmlAppHelper::plugin_folder();
		load_plugin_textdomain( 'formidable-wpml', false, $plugin_folder . '/languages/' );
	}

	/**
	 * Tell WPML that we want formidable forms translated.
	 *
	 * @deprecated 1.11
	 */
	public static function get_translatable_types( $types ) {
		_deprecated_function( __METHOD__, '1.11' );
		$slug = 'formidable';
		$name = 'Formidable';

		if ( isset( $types[ $slug ] ) ) {
			return $types;
		}

		$type                = new stdClass();
		$type->name          = $slug;
		$type->label         = $name;
		$type->prefix        = 'package';
		$type->external_type = 1;

		$type->labels                = new stdClass();
		$type->labels->singular_name = $name;
		$type->labels->name          = $name;

		$types[ $slug ]              = $type;

		return $types;
	}

	public static function add_translate_button( $values ) {
		?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable&frm_action=translate&id=' . $values['id'] ) ); ?>" class="button-secondary frm-button-secondary">
			<?php esc_html_e( 'Translate Form', 'formidable-wpml' ); ?>
		</a>
		<?php
	}

	public static function translated() {
		//don't continue another action
		return true;
	}

	public static function update_translate() {
		$strings = FrmAppHelper::get_param( 'frm_wpml', '', 'post', 'wp_filter_post_kses' );
		if ( empty( $strings ) || ! isset( $_POST['update_translations'] ) ) {
			self::translate();
			return;
		}

		$nonce = FrmAppHelper::get_param( 'frm_translate_form', '', 'post', 'sanitize_text_field' );
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'frm_translate_form_nonce' ) ) {
			global $frm_settings;
			wp_die( esc_html( $frm_settings->admin_permission ) );
		}

		self::add_copied_strings( $strings );
		self::update_strings( $strings );

		$message = __( 'Translations Successfully Updated', 'formidable-wpml' );

		self::translate( $message );
	}

	/**
	 * Adds copied strings to the list of strings before they are saved.
	 *
	 * @since 1.11
	 *
	 * @param array $strings Strings.
	 */
	private static function add_copied_strings( &$strings ) {
		$copied_strings = FrmAppHelper::get_post_param( 'frm_wpml_copy' );
		if ( ! $copied_strings ) {
			return;
		}

		foreach ( $copied_strings as $source_key => $dest_keys ) {
			if ( ! isset( $strings[ $source_key ] ) ) {
				continue;
			}

			foreach ( $dest_keys as $dest_key ) {
				$strings[ $dest_key ] = $strings[ $source_key ];
			}
		}
	}

	/**
	 * Update any translations saved on the Translation page.
	 *
	 * @since 1.05
	 * @since 1.12 Just process strings with `{string_id}_{lang}` as key, `{translation_id}` as key is not supported.
	 *
	 * @param array $strings The values POSTed from the translation form.
	 */
	private static function update_strings( $strings ) {
		global $wpdb;

		foreach ( $strings as $tkey => $t ) {
			$st = array(
				'value'  => $t['value'],
				'status' => ( isset( $t['status'] ) ) ? $t['status'] : ICL_STRING_TRANSLATION_NOT_TRANSLATED,
			);

			if ( ! empty( $t['value'] ) ) {
				$info = explode( '_', $tkey, 2 ); // limit by 2 to preserve languages with underscores.
				if ( ! is_numeric( $info[0] ) ) {
					continue;
				}

				$id = $wpdb->get_var(
					$wpdb->prepare(
						'SELECT id FROM ' . $wpdb->prefix . 'icl_string_translations WHERE language=%s AND string_id=%d',
						$info[1],
						$info[0]
					)
				);

				$string_id = $info[0];
				$language  = $info[1];

				icl_add_string_translation( $string_id, $language, $st['value'], $st['status'], $id );
			}

			unset( $t, $tkey );
		}
	}

	public static function translate( $message = '' ) {
		global $sitepress;

		$id   = FrmAppHelper::get_param( 'id', false, 'get', 'absint' );
		$form = FrmForm::getOne( $id );

		list( $fields, $form_ids ) = self::get_field_translate_details( $id );

		$langs            = $sitepress->get_active_languages();
		$default_language = self::get_string_language();
		ksort( $langs );

		$col_order = array( $default_language );
		foreach ( $langs as $lang ) {
			if ( $lang['code'] == $default_language ) {
				continue;
			}

			$col_order[] = $lang['code'];
		}

		$lang_count = ( count( $langs ) - 1 );

		// Register any missing strings.
		self::get_translatable_items();

		$strings      = self::get_strings_for_form( $form_ids );
		$strings      = self::sort_strings( $strings, $fields );
		$translations = self::get_string_translations( $strings );
		$register     = new FrmWpmlRegister( compact( 'form' ) );

		FrmWpmlString::add_copy_data_to_strings( $strings );

		self::filter_strings( $strings, $register, $id, $fields );

		$list_table = self::get_list_table( $strings );

		$offset = ( $list_table->get_pagenum() - 1 ) * self::get_per_page();

		$page_strings = array_slice( $strings, $offset, self::get_per_page(), true );

		include( FrmWpmlAppHelper::plugin_path() . '/views/translate.php' );
	}

	/**
	 * Returns the number of strings to display per page.
	 *
	 * @since 1.13
	 *
	 * @return int
	 */
	private static function get_per_page() {
		$per_page = (int) get_user_option( 'frm_wpml_items_per_page' );
		if ( ! $per_page ) {
			$per_page = 20;
		}
		self::$per_page = $per_page;

		return self::$per_page;
	}

	/**
	 * Gets a list table object used for generating pagination controls.
	 *
	 * @since 1.13
	 * @param array $strings
	 * @return FrmWpmlListHelper
	 */
	private static function get_list_table( $strings ) {
		$list_table = new FrmWpmlListHelper( array() );

		/**
		 * @psalm-suppress UndefinedPropertyAssignment
		 */
		$list_table->items = array( '' ); // Just setting the items so that the pagination nav would be displayed since FrmListHelper::has_items would return true.

		$list_table->call_set_pagination_args(
			array(
				'total_items' => count( $strings ),
				'per_page'    => self::get_per_page(),
			)
		);

		return $list_table;
	}

	/**
	 * @since 1.13
	 * @param array  $strings
	 * @param FrmWpmlRegister $register
	 */
	private static function filter_strings( &$strings, $register, $id, $fields ) {
		foreach ( $strings as $index => $string ) {
			$skip_string = false;

			if ( ! empty( $string->is_copied_from ) ) {
				$skip_string = true;
			} else {
				$string->value = $register->maybe_register( $string, compact( 'id', 'fields' ) );
				if ( is_array( $string->value ) ) {
					$skip_string = true;
				} elseif ( $string->value == '' || $string->value == '*' ) {
					$register->unregister( $string->name );
					$skip_string = true;
				}
			}

			if ( $skip_string ) {
				unset( $strings[ $index ] );
				continue;
			}

			$strings[ $index ] = $string;
		}
	}

	/**
	 * @param int $form_id
	 * @return array<array>
	 */
	private static function get_field_translate_details( $form_id ) {
		$fields       = array();
		$form_ids     = array();
		$fields_array = FrmField::getAll( array( 'fi.form_id' => $form_id ), 'field_order' );
		foreach ( $fields_array as $field ) {
			$fields[ $field->id ]        = $field;
			$form_ids[ $field->form_id ] = absint( $field->form_id );
			if ( ! empty( $field->field_options['form_select'] ) ) {
				$form_ids[ $field->field_options['form_select'] ] = absint( $field->field_options['form_select'] );
			}
		}

		return array( $fields, $form_ids );
	}

	/**
	 * Get all registered strings for the current form.
	 *
	 * @since 1.05
	 *
	 * @param array $form_ids
	 * @return array
	 */
	private static function get_strings_for_form( $form_ids ) {
		global $wpdb;

		if ( ! $form_ids ) {
			// No fields found in form. Get form id from URL instead.
			$form_id = FrmAppHelper::simple_get( 'id', 'absint' );
			if ( ! $form_id ) {
				return array();
			}

			$form_ids = array( $form_id );
		}

		$current_id = FrmAppHelper::get_param( 'id', false, 'get', 'absint' );
		$query_args = array( 'formidable' );
		$like       = '';
		foreach ( $form_ids as $form_id ) {
			if ( ! empty( $like ) ) {
				$like .= ' OR ';
			}
			$like .= 'name LIKE %s';

			// If this is a child form, only get the field values.
			$query_args[] = $form_id . '\_' . ( $form_id == $current_id ? '' : 'field-' ) . '%';
		}

		$search_keyword = self::search_keyword();
		if ( $search_keyword ) {
			$like .= $wpdb->prepare( ' AND value LIKE %s', '%' . $wpdb->esc_like( $search_keyword ) . '%' );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL
		$query = $wpdb->prepare( "SELECT id, name, value, language FROM {$wpdb->prefix}icl_strings WHERE context=%s AND (" . $like . ') ORDER BY name DESC', $query_args );
		return $wpdb->get_results( $query, OBJECT_K ); // phpcs:ignore WordPress.DB.PreparedSQL
	}

	/**
	 * Returns the search word if search button is clicked.
	 *
	 * @since 1.13
	 *
	 * @return string
	 */
	private static function search_keyword() {
		if ( isset( $_POST['update_translations'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Updating translations, no need to bother about the search word.
			return '';
		}
		return FrmAppHelper::get_param( 's', '', 'get', 'sanitize_text_field' );
	}

	/**
	 * After a form is duplicated, this function copies all strings with translation in different languages to the new form and fields.
	 *
	 * @param int   $form_id
	 * @param array $new_values
	 * @param array $args
	 * @return void
	 */
	public static function after_duplicate_form( $form_id, $new_values, $args ) {
		global $frm_duplicate_ids;

		$old_form_id      = $args['old_id'];
		$translation_args = self::get_form_translation_data( $old_form_id );

		if ( ! $translation_args ) {
			return;
		}

		$old_strings                 = $translation_args['old_strings'];
		$old_trans_by_string_id_lang = $translation_args['old_trans_by_string_id_lang'];
		$old_string_id_by_name       = $translation_args['old_string_id_by_name'];

		$form         = FrmForm::getOne( $form_id );
		$form_strings = FrmForm::translatable_strings( $form );

		foreach ( $form_strings as $old_string_key ) {
			if ( ! isset( $old_strings[ $old_string_key ] ) ) {
				continue;
			}

			$full_old_key  = $old_form_id . '_' . $old_string_key;
			$old_string_id = self::should_duplicate_string( $old_string_id_by_name, $old_string_key, $old_strings, $old_trans_by_string_id_lang, $full_old_key );

			if ( ! $old_string_id ) {
				continue;
			}

			$new_string_name = $form_id . '_' . $old_string_key;
			self::duplicate_translation( $old_string_key, $new_string_name, $old_string_id, $old_strings, $old_trans_by_string_id_lang );
		}

		if ( is_array( $frm_duplicate_ids ) ) {
			foreach ( $frm_duplicate_ids as $old_field_id => $new_field_id ) {
				$old_field = FrmField::getOne( $old_field_id );
				self::maybe_duplicate_single_field_translations( $form_id, $new_field_id, $old_field, $translation_args, $old_form_id );
			}
		}
	}

	/**
	 * Return all form string names, values and their translations in different structures.
	 *
	 * @param int $form_id
	 *
	 * @return array|false
	 */
	private static function get_form_translation_data( $form_id ) {
		global $wpdb;

		$old_strings_for_form = self::get_strings_for_form( array( $form_id ) );

		if ( ! $old_strings_for_form ) {
			return false;
		}

		$old_string_ids  = array_keys( $old_strings_for_form );
		$old_string_data = FrmDb::get_results( 'icl_strings', array( 'id' => $old_string_ids ), 'id, name' );

		if ( ! $old_string_data ) {
			return false;
		}

		$old_translations            = self::get_string_translations( $old_strings_for_form );
		$old_trans_by_string_id_lang = array();

		foreach ( $old_translations as $old_translation ) {
			if ( ! isset( $old_trans_by_string_id_lang[ $old_translation->string_id ] ) ) {
				$old_trans_by_string_id_lang[ $old_translation->string_id ] = array();
			}
			$old_trans_by_string_id_lang[ $old_translation->string_id ][ $old_translation->language ] = $old_translation;
		}
		unset( $old_strings_for_form, $old_translations );

		$old_strings           = FrmWpmlString::form_strings( $form_id );
		$old_string_id_by_name = wp_list_pluck( $old_string_data, 'id', 'name' );

		return compact( 'old_strings', 'old_trans_by_string_id_lang', 'old_string_id_by_name' );
	}

	/**
	 * Checks if the old (copy) field has some strings and options translated and duplicates them to the new created field.
	 *
	 * @param int      $form_id
	 * @param int      $field_id
	 * @param stdClass $copy_field
	 * @param array    $translation_args
	 * @param int|null $old_form_id
	 */
	private static function maybe_duplicate_single_field_translations( $form_id, $field_id, $copy_field, $translation_args, $old_form_id = null ) {
		$old_strings                 = $translation_args['old_strings'];
		$old_trans_by_string_id_lang = $translation_args['old_trans_by_string_id_lang'];
		$old_string_id_by_name       = $translation_args['old_string_id_by_name'];

		$translations   = array();
		$old_field_obj  = FrmFieldFactory::get_field_object( $copy_field );
		$field_settings = is_callable( array( $old_field_obj, 'translatable_strings' ) ) ? $old_field_obj->translatable_strings() : array();

		foreach ( $field_settings as $option ) {
			if ( ! is_string( $option ) || is_numeric( $option ) ) {
				continue;
			}
			$translations[]  = array(
				'old_string_key'  => 'field-' . $copy_field->id . '-' . $option,
				'new_string_name' => $form_id . '_field-' . $field_id . '-' . $option,
			);
		}

		if ( is_array( $copy_field->options ) ) {
			foreach ( $copy_field->options as $option ) {
				$label = is_array( $option ) ? $option['label'] : $option;
				if ( ! is_string( $label ) || is_numeric( $label ) ) {
					continue;
				}

				$translations[]  = array(
					'old_string_key'  => 'field-' . $copy_field->id . '-choice-' . $label,
					'new_string_name' => $form_id . '_field-' . $field_id . '-choice-' . $label,
				);
			}
		}

		$field_form_id = $old_form_id ? $old_form_id : $form_id;
		foreach ( $translations as $translation ) {
			$old_string_key  = $translation['old_string_key'];
			$new_string_name = $translation['new_string_name'];
			$full_old_key    = $field_form_id . '_' . $old_string_key;

			$old_string_id = self::should_duplicate_string( $old_string_id_by_name, $old_string_key, $old_strings, $old_trans_by_string_id_lang, $full_old_key );
			if ( ! $old_string_id ) {
				continue;
			}

			self::duplicate_translation( $old_string_key, $new_string_name, $old_string_id, $old_strings, $old_trans_by_string_id_lang );
		}
	}

	/**
	 * Proceed to a function that would translate a single field strings or return early
	 * if there is no translation strings from the original field.
	 *
	 * @param array $args Contains different values used to create a new field.
	 */
	public static function after_duplicate_field( $args ) {

		$field_id   = $args['field_id'];
		$copy_field = $args['copy_field'];
		$form_id    = $args['form_id'];

		$translation_args = self::get_form_translation_data( $form_id );
		if ( ! $translation_args ) {
			return;
		}

		self::maybe_duplicate_single_field_translations( $form_id, $field_id, $copy_field, $translation_args );
	}

	/**
	 * Check whether a string has translation and return the string id if so, otherwise return null.
	 *
	 * @param array  $old_string_id_by_name The list of old form strings using string name as key values.
	 * @param string $old_string_key The old form string key.
	 * @param array  $old_strings The list of all form string values.
	 * @param array  $old_trans_by_string_id_lang Mapping between string id and all translations of it grouped by language.
	 * @param string $full_old_key Old form string key with form id prepended.
	 *
	 * @return string|null
	 */
	private static function should_duplicate_string( $old_string_id_by_name, $old_string_key, $old_strings, $old_trans_by_string_id_lang, $full_old_key ) {
		if ( ! isset( $old_string_id_by_name[ $full_old_key ] ) ) {
			return null;
		}
		$old_string_id = $old_string_id_by_name[ $full_old_key ];

		return ( isset( $old_trans_by_string_id_lang[ $old_string_id ] ) && isset( $old_strings[ $old_string_key ] ) ) ? $old_string_id : null;
	}

	/**
	 * Create a string and add translations in different languages.
	 *
	 * @param string $old_string_key The old form string key.
	 * @param string $new_string_name String name for the new form.
	 * @param int    $old_string_id Old string id
	 * @param array  $old_strings The list of all form string values.
	 * @param array  $old_trans_by_string_id_lang Mapping between string id and all translations of it grouped by language.
	 */
	private static function duplicate_translation( $old_string_key, $new_string_name, $old_string_id, $old_strings, $old_trans_by_string_id_lang ) {
		$new_string_value = $old_strings[ $old_string_key ];
		$new_string_id    = icl_register_string( 'formidable', $new_string_name, $new_string_value );

		foreach ( $old_trans_by_string_id_lang[ $old_string_id ] as $language => $old_translation ) {
			icl_add_string_translation( $new_string_id, $old_translation->language, $old_translation->value, $old_translation->status );
		}
	}

	/**
	 * @param array $strings
	 * @param array $fields ordered by field sort order. used as a reference as strings should be in the same order as fields.
	 * @return array sorted strings
	 */
	private static function sort_strings( $strings, $fields ) {
		$sorted_strings      = array();
		if ( ! $strings ) {
			return $sorted_strings;
		}
		$indexed_by_field_id = self::index_strings_by_field_id( $strings );
		foreach ( $fields as $field ) {
			if ( isset( $indexed_by_field_id[ $field->id ] ) ) {
				$for_field = $indexed_by_field_id[ $field->id ];
				foreach ( $for_field as $string ) {
					$sorted_strings[ $string->id ] = $string;
				}
			}
		}
		$non_field_strings = $strings;
		foreach ( $sorted_strings as $string ) {
			unset( $non_field_strings[ $string->id ] );
		}
		return $non_field_strings + $sorted_strings;
	}

	/**
	 * @param array $strings
	 * @return array strings indexed by field id.
	 */
	private static function index_strings_by_field_id( $strings ) {
		$indexed = array();
		foreach ( $strings as $string ) {
			$split = explode( '-', $string->name );
			$count = count( $split );

			if ( $count === 3 ) {
				$key = $split[2];
			} elseif ( $count === 4 ) {
				if ( 'choice' !== $split[2] ) {
					continue;
				}
				$key = $split[3];
			} else {
				continue;
			}

			$field_id = $split[1];
			if ( ! isset( $indexed[ $field_id ] ) ) {
				$indexed[ $field_id ] = array();
			}
			$indexed[ $field_id ][ $key ] = $string;
		}
		return $indexed;
	}

	/**
	 * Get all translations for the registered strings.
	 *
	 * @since 1.05
	 * @param array $strings
	 * @return array
	 */
	private static function get_string_translations( $strings ) {
		global $wpdb;

		if ( empty( $strings ) ) {
			return array();
		}

		$where = array( 'string_id' => array_keys( $strings ) );
		$args  = array( 'order_by' => 'language ASC' );

		return FrmDb::get_results( $wpdb->prefix . 'icl_string_translations', $where, 'id, string_id, value, status, language', $args );
	}

	/**
	 * Show the input box on the translation page for a single string.
	 *
	 * @since 1.05
	 * @param array $atts
	 */
	public static function include_single_input( $atts ) {
		$name = 'frm_wpml[' . $atts['input_id'] . ']';
		if ( $atts['value'] === '' || ! isset( $atts['complete'] ) ) {
			$atts['complete'] = 0;
		}
		include( FrmWpmlAppHelper::plugin_path() . '/views/single-input.php' );
	}

	/**
	 * Pick from available options for the datepicker locale.
	 *
	 * @since 1.05
	 * @param object $string - The string to be translated.
	 * @param array  $atts - Includes the form id and array of all fields in the form.
	 */
	public static function maybe_get_translation_options( $string, $atts ) {
		$options   = array();
		$is_locale = strpos( $string->name, '-locale' ) !== false;
		$is_field  = strpos( $string->name, $atts['id'] . '_field-' ) === 0;
		if ( ! $is_locale || ! $is_field ) {
			return $options;
		}

		// Get the field id from the string name.
		$fid      = explode( '-', str_replace( $atts['id'] . '_field-', '', $string->name ), 2 );
		$field_id = $fid[0];
		if ( ! isset( $atts['fields'][ $field_id ] ) ) {
			return $options;
		}

		$field_type = $atts['fields'][ $field_id ]->type;
		if ( $field_type === 'date' ) {
			$options = FrmAppHelper::locales( 'date' );
		}

		return $options;
	}

	/**
	 * Show the locale dropdown on the translation page.
	 *
	 * @since 1.05
	 * @param string $input_name
	 * @param array  $options
	 * @param string $selected
	 */
	public static function show_dropdown_options( $input_name, $options, $selected = '' ) {
		?>
		<select name="<?php echo esc_attr( $input_name ); ?>[value]">
			<option value=""> </option>
			<?php foreach ( $options as $code => $label ) { ?>
				<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $selected, $code ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php } ?>
		</select>
		<?php
	}

	public static function get_string_language() {
		global $sitepress;

		$string_version = defined( 'WPML_ST_VERSION' ) ? WPML_ST_VERSION : 1;
		if ( class_exists( 'WPML_Language_Of_Domain' ) ) {
			$lang_of_domain = new WPML_Language_Of_Domain( $sitepress );
			$default_language = $lang_of_domain->get_language( 'formidable' );
			if ( ! $default_language ) {
				$default_language = FrmWpmlAppHelper::get_default_language();
			}
		} elseif ( version_compare( $string_version, '2.2.5', '>' ) ) {
			$default_language = 'en';
		} else {
			global $sitepress_settings;
			$default_language = ! empty( $sitepress_settings['st']['strings_language'] ) ? $sitepress_settings['st']['strings_language'] : FrmWpmlAppHelper::get_default_language();
		}

		return $default_language;
	}

	/**
	 * Update the saved ICL strings
	 *
	 * @since 1.05
	 * @param int $form_id
	 * @param array $values
	 */
	public static function update_saved_wpml_strings( $form_id, $values ) {
		$register = new FrmWpmlRegister( array( 'form' => $form_id ) );
		$register->update_form_fields( $values );
	}

	public static function delete_frm_wpml( $id ) {
		global $wpdb;

		//delete strings before a field is deleted
		$strings = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT name FROM {$wpdb->prefix}icl_strings WHERE context=%s AND name LIKE %s",
				'formidable',
				"%_field-{$id}-%"
			)
		);

		if ( $strings ) {
			$register = new FrmWpmlRegister();
			$register->unregister( $strings );
		}
	}

	public static function get_translatable_items() {
		global $wpdb;

		$action  = FrmAppHelper::simple_get( 'frm_action', 'sanitize_title' );
		$form_id = FrmAppHelper::simple_get( 'id', 'absint' );

		if ( $action === 'translate' && ! empty( $form_id ) ) {
			$forms = FrmForm::getAll( $wpdb->prepare( 'parent_form_id=%d or id=%d', $form_id, $form_id ) );
		} else {
			$forms = FrmForm::getAll( "is_template=0 AND (status is NULL OR status = '' OR status = 'published')", ' ORDER BY name' );
		}

		foreach ( $forms as $form ) {

			$register = new FrmWpmlRegister( compact( 'form' ) );

			$strings = FrmWpmlString::form_strings( $form );

			// Register the strings with WPML.
			foreach ( $strings as $key => $value ) {
				$key = FrmWpmlAppHelper::prepend_form_id_and_get_safe_substring( $key, $form->id );
				if ( ! is_array( $value ) && ! icl_st_is_registered_string( 'formidable', $key ) ) {
					$register->register( $key, $value );
				}

				unset( $key, $value );
			}
		}
	}

	/**
	 * Fetches choices icl strings from db.
	 *
	 * @since 1.13
	 *
	 * @param array $icl_string_prefixes
	 * @return array
	 */
	private static function get_choice_icl_strings( $icl_string_prefixes ) {
		return FrmDb::get_col(
			'icl_strings',
			array(
				'context' => 'formidable',
				'name LIKE' => $icl_string_prefixes,
			),
			'name'
		);
	}

	/**
	 * Returns true if an icl string matches in the new field choices.
	 *
	 * @since 1.13
	 *
	 * @param array  $new_field_choices
	 * @param string $field_choice_icl_string
	 * @param int    $form_id
	 * @param int    $field_id
	 *
	 * @return bool
	 */
	private static function choice_icl_string_has_match( $new_field_choices, $field_choice_icl_string, $form_id, $field_id ) {
		foreach ( $new_field_choices as $choice ) {
			if ( is_array( $choice ) ) {
				$choice = isset( $choice['label'] ) ? $choice['label'] : reset( $choice );
			}
			$choice_icl_string = FrmWpmlAppHelper::get_safe_substring( $form_id . '_field-' . $field_id . '-choice-' . $choice );
			if ( $field_choice_icl_string === $choice_icl_string ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Unregisters obsolete ICL entries for fields choices when a form is updated.
	 *
	 * @since 1.13
	 * @param int    $form_id
	 * @param array  $values
	 *
	 * @return void
	 */
	public static function maybe_unregister_obsolete_icl_entries( $form_id, $values ) {
		$field_ids = FrmDb::get_col(
			'frm_fields',
			array(
				'form_id' => $form_id,
				'type'    => array( 'radio', 'checkbox', 'select' ),
			)
		);

		$icl_string_prefixes = array();

		$should_continue_to_process = false;

		foreach ( $field_ids as $field_id ) {
			array_push( $icl_string_prefixes, $form_id . '_field-' . $field_id . '-choice-' );
			if ( ! empty( $values['field_options'][ 'options_' . $field_id ] ) ) {
				$should_continue_to_process = true;
			}
		}

		if ( ! $should_continue_to_process ) {
			// Field choices are not updated, so exit early and save extra db trip.
			return;
		}

		$choice_strings = self::get_choice_icl_strings( $icl_string_prefixes );

		if ( empty( $choice_strings ) ) {
			return;
		}

		self::unregister_obsolete_icl_entries( $field_ids, $form_id, $values, $choice_strings );
	}

	/**
	 * Unregisters obsolete icl entries.
	 *
	 * @since 1.13
	 *
	 * @param array  $field_ids
	 * @param int    $form_id
	 * @param array  $values
	 * @param array  $choice_strings
	 *
	 * @return void
	 */
	private static function unregister_obsolete_icl_entries( $field_ids, $form_id, $values, $choice_strings ) {
		$register = new FrmWpmlRegister();

		foreach ( $field_ids as $field_id ) {
			if ( empty( $values['field_options'][ 'options_' . $field_id ] ) ) {
				// This field choices are not updated, so skip it.
				continue;
			}

			$new_field_choices = $values['field_options'][ 'options_' . $field_id ];

			$field_choice_icl_strings = array_filter(
				$choice_strings,
				function( $choice_string ) use ( $form_id, $field_id ) {
					return strpos( $choice_string, $form_id . '_field-' . $field_id . '-choice-' ) === 0;
				}
			);

			foreach ( $field_choice_icl_strings as $field_choice_icl_string ) {
				if ( ! self::choice_icl_string_has_match( $new_field_choices, $field_choice_icl_string, $form_id, $field_id ) ) {
					$register->unregister( $field_choice_icl_string );
				}
			}
		}
	}

	/**
	 * @since 1.13
	 *
	 * @param bool      $show
	 * @param WP_Screen $screen
	 * @return bool
	 */
	public static function add_screen_options_to_translate_page( $show, \WP_Screen $screen ) {
		$action = FrmAppHelper::get_param( 'frm_action' );
		if ( ! FrmAppHelper::is_admin_page( 'formidable' ) || ! in_array( $action, array( 'translate', 'update_translate' ), true ) ) {
			// Not the translate page. Exit early.
			return $show;
		}

		add_screen_option(
			'per_page',
			array(
				'label'   => __( 'Items per page', 'formidable-wpml' ),
				'default' => self::get_per_page(),
				'option'  => 'frm_wpml_items_per_page',
			)
		);

		return true;
	}

	/**
	 * Save the number of items per page for the translation page.
	 *
	 * @since 1.13
	 * @param mixed  $save
	 * @param string $option
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	public static function save_per_page( $save, $option, $value ) {
		if ( $option === 'frm_wpml_items_per_page' ) {
			$save = (int) $value;
		}

		return $save;
	}
}
