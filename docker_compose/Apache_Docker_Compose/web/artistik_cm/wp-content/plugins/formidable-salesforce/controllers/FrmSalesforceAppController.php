<?php
/**
 * Initialize the plugin and handle top-level routing
 */
class FrmSalesforceAppController {
	public static $min_version = '2.0';

	public static function min_version_notice() {
		$frm_version = is_callable( 'FrmAppHelper::plugin_version' ) ? FrmAppHelper::plugin_version() : 0;

		// Check if Formidable meets minimum requirements.
		if ( version_compare( $frm_version, self::$min_version, '>=' ) ) {
			return;
		}

		$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
		echo '<tr class="plugin-update-tr active"><th colspan="' . esc_attr( $wp_list_table->get_column_count() ) . '" class="check-column plugin-update colspanchange"><div class="update-message">' .
		esc_html_e( 'You are running an outdated version of Formidable. This plugin needs Formidable v2.0 + to work correctly.', 'formidable-salesforce' ) .
			'</div></td></tr>';
	}

	public static function include_updater() {
		if ( class_exists( 'FrmAddon' ) ) {
			include self::path() . '/models/FrmSalesforceUpdate.php';
			FrmSalesforceUpdate::load_hooks();
		}
	}

	/**
	 * Initialize the translations for this plugin
	 *
	 * @since 2.01
	 */
	public static function load_lang() {
		load_plugin_textdomain( 'formidable-salesforce', false, basename( self::path() ) . '/languages/' );
	}

	public static function path() {
		return dirname( dirname( __FILE__ ) );
	}

	/**
	 * The base url for files within this plugin
	 *
	 * @since 2.01
	 */
	public static function plugin_url() {
		return plugins_url() . '/' . basename( self::path() );
	}

	public static function hidden_form_fields( $form, $form_action ) {
		_deprecated_function( __METHOD__, '2.05' );
	}

	public static function trigger_salesforce( $action, $entry, $form ) {
		$settings = $action->post_content;
		$vars     = array();

		$salesforce = new FrmSalesforceAPI();
		$object_fields = $salesforce->fetch_object_fields( $settings['object_id'] );

		foreach ( $settings['fields'] as $field_tag => $field_id ) {
			if ( empty( $field_id ) ) {
				// Don't sent an empty value.
				continue;
			}

			$sf_field = self::get_sf_field_by_name( $field_tag, $object_fields );
			$vars[ $field_tag ] = self::get_field_value( $entry, compact( 'field_id', 'field_tag', 'sf_field' ) );
		}

		$record_id = self::get_record_id( $settings, $vars );
		$object_id = $settings['object_id'];

		$salesforce->create_or_update_record( $vars, compact( 'action', 'entry', 'record_id', 'object_id' ) );
	}

	private static function get_sf_field_by_name( $field_name, $fields ) {
		foreach ( $fields as $field ) {
			if ( $field['name'] === $field_name ) {
				return $field;
			}
		}

		return false;
	}

	private static function get_field_value( $entry, $args ) {
		$field_id  = $args['field_id'];
		$value = self::get_entry_or_post_value( $entry, $field_id );
		$field = FrmField::getOne( $field_id );

		if ( $args['sf_field'] && 'multipicklist' === $args['sf_field']['type'] ) {
			$sep = ';';
		} else {
			$sep = ', ';
		}

		self::convert_date_format( $field, $value );

		if ( is_numeric( $value ) ) {
			if ( 'user_id' == $field->type ) {
				self::get_value_from_user_id( $args['field_tag'], $value );
			} else {
				$value = FrmEntriesHelper::display_value(
					$value,
					$field,
					array(
						'type'     => $field->type,
						'truncate' => false,
						'entry_id' => $entry->id,
						'sep'      => $sep,
					)
				);
			}
		}

		if ( is_array( $value ) ) {
			$value = implode( $sep, $value );
		}

		self::set_boolean( $args, $value );

		return $value;
	}

	/**
	 * Convert date to yyyy-mm-dd
	 *
	 * @param object $field
	 * @param string $value
	 */
	private static function convert_date_format( $field, &$value ) {
		if ( 'date' == $field->type ) {
			if ( ! empty( $value ) ) {
				$value = FrmProAppHelper::maybe_convert_to_db_date( $value, 'Y-m-d' );
			} else {
				$value = null;
			}
		}
	}

	private static function get_value_from_user_id( $field_tag, &$value ) {
		$user_data = get_userdata( $value );
		if ( 'email' === $field_tag ) {
			$value = $user_data->user_email;
		} elseif ( 'first_name' === $field_tag ) {
			$value = $user_data->first_name;
		} elseif ( 'last_name' === $field_tag ) {
			$value = $user_data->last_name;
		} else {
			$value = $user_data->user_login;
		}
	}

	/**
	 * Boolean fields require either true or false value.
	 *
	 * @param array  $args
	 * @param string $value
	 */
	private static function set_boolean( $args, &$value ) {
		if ( $args['sf_field'] && 'boolean' === $args['sf_field']['type'] ) {
			$value = ( ! empty( $value ) );
		}
	}

	public static function get_entry_or_post_value( $entry, $field_id ) {
		$value = '';
		if ( ! empty( $entry ) && isset( $entry->metas[ $field_id ] ) ) {
			$value = $entry->metas[ $field_id ];
		} else if ( isset( $_POST['item_meta'][ $field_id ] ) ) { // WPCS: CSRF ok.
			$value = sanitize_text_field( wp_unslash( $_POST['item_meta'][ $field_id ] ) ); // WPCS: CSRF ok.
		}
		return $value;
	}

	/**
	 * GEt the ID of the Salesforce object to update.
	 *
	 * @since 2.04
	 *
	 * @param array $settings
	 * @param array $vars
	 * @return bool|string
	 */
	private static function get_record_id( $settings, $vars ) {
		$field_id = $settings['update_field'];

		if ( empty( $field_id ) || ! isset( $vars[ $field_id ] ) ) {
			return false;
		}

		$salesforce = new FrmSalesforceAPI();
		$record_id = $salesforce->get_record_id_to_update(
			$settings['object_id'],
			array(
				'field_id'    => $field_id,
				'field_value' => $vars[ $field_id ],
			)
		);

		return $record_id;
	}
}
