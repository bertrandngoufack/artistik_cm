<?php

class FrmCtctAppController {

	/**
	 * @since 1.04
	 *
	 * @var string $plug_version
	 */
	public static $plug_version = '1.07';

	/**
	 * @var string $min_version
	 */
	public static $min_version = '2.0';

	/**
	 * The mapping of emails and lists.
	 *
	 * @since 1.04
	 *
	 * @var array
	 */
	private static $emails_lists = array();

	public static function min_version_notice() {
		$frm_version = is_callable( 'FrmAppHelper::plugin_version' ) ? FrmAppHelper::plugin_version() : 0;

		// check if Formidable meets minimum requirements
		if ( version_compare( $frm_version, self::$min_version, '>=' ) ) {
			return;
		}

		$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
		echo '<tr class="plugin-update-tr active"><th colspan="' . absint( $wp_list_table->get_column_count() ) . '" class="check-column plugin-update colspanchange"><div class="update-message">' .
		esc_html_e( 'You are running an outdated version of Formidable. This plugin needs Formidable v2.0 + to work correctly.', 'formidable-ctct' ) .
			'</div></td></tr>';
	}

	/**
	 * @since 1.04
	 *
	 * @return void
	 */
	public static function admin_init() {
		self::include_updater();

		$ctct_settings = FrmCtctSettingsController::get_settings();
		if ( $ctct_settings->using_legacy_api() ) {
			self::add_legacy_api_inbox_message();
		}
	}

	/**
	 * @return void
	 */
	private static function add_legacy_api_inbox_message() {
		if ( ! class_exists( 'FrmInbox' ) ) {
			return;
		}

		ob_start();
		FrmCtctSettingsController::print_deprecated_api_warning_body();
		$message = ob_get_clean();

		$ctct_api = new FrmCtctAPI();
		$inbox    = new FrmInbox();

		$inbox->add_message(
			array(
				'key'     => 'legacy_ctct_api',
				'message' => $message,
				'subject' => 'You must connect to the new Constant Contact Authorization Service',
				'cta'     => '<a href="' . esc_url( $ctct_api->auth_url() ) . '"></a>',
				'type'    => 'frm_report_problem_icon',
			)
		);
	}

	/**
	 * @return void
	 */
	public static function include_updater() {
		if ( class_exists( 'FrmAddon' ) ) {
			include self::path() . '/models/FrmCtctUpdate.php';
			FrmCtctUpdate::load_hooks();
		}
	}

	/**
	 *
	 * @since 1.0
	 */
	public static function load_lang() {
		load_plugin_textdomain( 'formidable-ctct', false, basename( self::path() ) . '/languages/' );
	}

	public static function path() {
		return dirname( dirname( __FILE__ ) );
	}

	/**
	 *
	 * @since 1.0
	 */
	public static function plugin_url() {
		return plugins_url() . '/' . basename( self::path() );
	}

	public static function hidden_form_fields( $form, $form_action ) {
		_deprecated_function( __METHOD__, '1.03' );
	}

	public static function trigger_constantcontact( $action, $entry, $form ) {
		$entry_id = $entry->id;
		$settings = $action->post_content;
		$vars     = array();

		foreach ( $settings['fields'] as $field_tag => $field_id ) {
			if ( empty( $field_id ) ) {
				// don't send an empty value
				continue;
			}
			$field = FrmField::getOne( $field_id );

			if ( 'home_address' === $field_tag || 'work_address' === $field_tag ) {
				//convert address field to constant contact accepted address values
				$address = self::get_entry_or_post_value( $entry, $field_id );
				self::get_formatted_address( $field_tag, $address, $vars );
			} else {
				$value = self::get_field_value( $entry, $field, $field_id, $field_tag );
				if ( '' !== $value ) {
					$vars[ $field_tag ] = $value;
					self::maybe_format_phone( $field, $field_tag, $vars );
					self::maybe_format_birthday( $field, $field_tag, $vars );
				}
			}
		}

		$list_id = $settings['list_id'];
		if ( ! empty( $list_id ) && ! empty( $vars['email'] ) ) {

			$vars['email_address'] = $vars['email'];

			self::set_list_memberships( $vars, $list_id );
			self::set_custom_fields( $vars );

			// unset vars not required
			unset( $vars['email'] );

			$ctct_api = new FrmCtctAPI();
			$ctct_api->create_or_update_contact( $vars, compact( 'action', 'entry' ) );
		}
	}

	/**
	 * Sets list_memberships to API data.
	 *
	 * @since 1.04
	 *
	 * @param array  $vars    API data.
	 * @param string $list_id The List ID from form action settings.
	 */
	private static function set_list_memberships( &$vars, $list_id ) {
		if ( ! isset( self::$emails_lists[ $vars['email_address'] ] ) ) {
			self::$emails_lists[ $vars['email_address'] ] = array();
		}
		self::$emails_lists[ $vars['email_address'] ][] = $list_id;

		$vars['list_memberships'] = self::$emails_lists[ $vars['email_address'] ];
	}

	private static function get_field_value( $entry, $field, $field_id, $field_tag ) {
		$value = self::get_entry_or_post_value( $entry, $field_id );

		if ( is_numeric( $value ) ) {

			if ( 'user_id' === $field->type ) {
				$user_data = get_userdata( (int) $value );
				if ( 'email' === $field_tag ) {
					$value = $user_data->user_email;
				} elseif ( 'first_name' === $field_tag ) {
					$value = $user_data->first_name;
				} elseif ( 'last_name' === $field_tag ) {
					$value = $user_data->last_name;
				} else {
					$value = $user_data->user_login;
				}
			} else {
				$value = FrmEntriesHelper::display_value(
					$value,
					$field,
					array(
						'type'     => $field->type,
						'truncate' => false,
						'entry_id' => $entry->id,
					)
				);
			}
		} elseif ( is_array( $value ) ) {
			if ( 'first_name' === $field_tag && 'name' === $field->type ) {
				$value = isset( $value['first'] ) ? $value['first'] : '';
			} elseif ( 'last_name' === $field_tag && 'name' === $field->type ) {
				$value = isset( $value['last'] ) ? $value['last'] : '';
			} else {
				$value = implode( ', ', $value );
			}
		}

		return $value;
	}

	private static function get_formatted_address( $type, $address, &$vars ) {
		if ( empty( $address ) || ! isset( $address['line1'] ) ) {
			return;
		}

		if ( ! isset( $vars['street_addresses'] ) ) {
			$vars['street_addresses'] = array();
		}

		if ( isset( $address['line2'] ) ) {
			$street = $address['line1'] . ' ' . $address['line2'];
		} else {
			$street = $address['line1'];
		}

		$vars['street_addresses'][] = array(
			'kind'        => str_replace( '_address', '', $type ),
			'street'      => trim( $street ),
			'city'        => $address['city'],
			'state'       => $address['state'],
			'country'     => $address['country'],
			'postal_code' => $address['zip'],
		);
	}

	private static function maybe_format_phone( $field, $field_tag, &$vars ) {
		if ( 'work_phone' !== $field_tag && 'home_phone' !== $field_tag ) {
			return;
		}

		if ( ! isset( $vars['phone_numbers'] ) ) {
			$vars['phone_numbers'] = array();
		}

		$vars['phone_numbers'][] = array(
			'phone_number' => $vars[ $field_tag ],
			'kind'         => str_replace( '_phone', '', $field_tag ),
		);
		unset( $vars[ $field_tag ] );
	}

	private static function maybe_format_birthday( $field, $field_tag, &$vars ) {
		if ( 'birthday' !== $field_tag ) {
			return;
		}

		$date = $vars[ $field_tag ];
		$vars['birthday_month'] = gmdate( 'm', strtotime( $date ) );
		$vars['birthday_day']   = gmdate( 'd', strtotime( $date ) );
		unset( $vars[ $field_tag ] );
	}

	private static function set_custom_fields( &$vars ) {
		$custom_fields = array();
		$fields = $vars;
		foreach ( $fields as $key => $value ) {
			if ( strpos( $key, 'custom_field_' ) === 0 ) {
				$custom_fields[] = array(
					'custom_field_id' => str_replace( 'custom_field_', '', $key ),
					'value'           => $value,
				);
				unset( $vars[ $key ] );
			}
		}
		$vars['custom_fields'] = $custom_fields;
	}

	private static function get_entry_or_post_value( $entry, $field_id ) {
		$value = '';
		if ( ! empty( $entry ) && isset( $entry->metas[ $field_id ] ) ) {
			$value = $entry->metas[ $field_id ];
		} else if ( isset( $_POST['item_meta'][ $field_id ] ) ) { // WPCS: CSRF ok.
			$value = sanitize_text_field( wp_unslash( $_POST['item_meta'][ $field_id ] ) ); // WPCS: CSRF ok.
		}
		return $value;
	}
}
