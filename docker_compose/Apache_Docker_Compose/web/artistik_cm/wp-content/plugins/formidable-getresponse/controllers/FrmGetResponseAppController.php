<?php
/**
 * This class is the main controller to hook into Formidable.
 */
class FrmGetResponseAppController {
	public static $min_version = '2.0';

	public static function min_version_notice() {
		$frm_version = is_callable( 'FrmAppHelper::plugin_version' ) ? FrmAppHelper::plugin_version() : 0;

		// Check if Formidable meets minimum requirements.
		if ( version_compare( $frm_version, self::$min_version, '>=' ) ) {
			return;
		}

		$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
		echo '<tr class="plugin-update-tr active"><th colspan="' . (int) $wp_list_table->get_column_count() . '" class="check-column plugin-update colspanchange"><div class="update-message">' .
			esc_html__( 'You are running an outdated version of Formidable. This plugin needs Formidable v2.0 + to work correctly.', 'formidable' ) .
			'</div></td></tr>';
	}

	public static function include_updater() {
		if ( class_exists( 'FrmAddon' ) ) {
			include self::path() . '/models/FrmGetResponseUpdate.php';
			FrmGetResponseUpdate::load_hooks();
		}
	}

	/**
	 * Add support for translating add-on strings.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function load_lang() {
		load_plugin_textdomain( 'formidable-getresponse', false, basename( self::path() ) . '/languages/' );
	}

	public static function plugin_url() {
		return plugins_url() . '/' . basename( self::path() );
	}

	public static function path() {
		return dirname( dirname( __FILE__ ) );
	}

	public static function hidden_form_fields( $form, $form_action ) {
		_deprecated_function( __METHOD__, '1.05' );
	}

	public static function trigger_getresponse( $action, $entry, $form ) {
		$settings = $action->post_content;
		$api      = new FrmGetResponseAPI();
		$entry_id = $entry->id;
		$vars     = array();

		foreach ( $settings['fields'] as $field_tag => $field_id ) {
			if ( empty( $field_id ) ) {
				// Don't sent an empty value.
				continue;
			}

			$vars[ $field_tag ] = self::get_entry_or_post_value( $entry, $field_id );
			$field              = FrmField::getOne( $field_id );

			if ( is_numeric( $vars[ $field_tag ] ) ) {
				if ( 'user_id' === $field->type ) {
					$user_data = get_userdata( $vars[ $field_tag ] );
					if ( 'email' === $field_tag ) {
						$vars[ $field_tag ] = $user_data->user_email;
					} elseif ( 'first_name' === $field_tag ) {
						$vars[ $field_tag ] = $user_data->first_name;
					} elseif ( 'last_name' === $field_tag ) {
						$vars[ $field_tag ] = $user_data->last_name;
					} else {
						$vars[ $field_tag ] = $user_data->user_login;
					}
				} else {
					if ( 'file' === $field->type ) {
						// Get file url.
						$vars[ $field_tag ] = FrmProEntriesController::get_field_value_shortcode(
							array(
								'field_id' => $field_id,
								'entry_id' => $entry_id,
								'show'     => '1',
								'html'     => 0,
							)
						);
					} else {
						$vars[ $field_tag ] = FrmEntriesHelper::display_value(
							$vars[ $field_tag ],
							$field,
							array(
								'type'     => $field->type,
								'truncate' => false,
								'entry_id' => $entry_id,
							)
						);
					}
				}
			}

			if ( is_array( $vars[ $field_tag ] ) ) {
				if ( 'file' === $field->type ) {
					$vars[ $field_tag ] = FrmProEntriesController::get_field_value_shortcode(
						array(
							'field_id' => $field_id,
							'entry_id' => $entry_id,
							'show' => '1',
							'html' => 0,
						)
					) . ',';
				} elseif ( 'first_name' === $field_tag && 'name' === $field->type ) {
					$vars[ $field_tag ] = isset( $vars[ $field_tag ]['first'] ) ? $vars[ $field_tag ]['first'] : '';
				} elseif ( 'last_name' === $field_tag && 'name' === $field->type ) {
					$vars[ $field_tag ] = isset( $vars[ $field_tag ]['last'] ) ? $vars[ $field_tag ]['last'] : '';
				} else {
					$vars[ $field_tag ] = implode( ', ', $vars[ $field_tag ] );
				}
			}
		}

		if ( ! isset( $vars['email'] ) ) {
			// No email address is mapped.
			return;
		}

		$subscriber = array(
			'email' => $vars['email'],
			'name'  => $vars['first_name'] . ( empty( $vars['last_name'] ) ? '' : ' ' . $vars['last_name'] ),
		);

		if ( is_callable( 'FrmAppHelper::ips_saved' ) && FrmAppHelper::ips_saved() ) {
			$subscriber['ipAddress'] = $entry->ip;
		}

		// Cycle day can be set to 0.
		if ( $settings['cycle_day'] !== '' ) {
			$subscriber['dayOfCycle'] = $settings['cycle_day'];
		}

		if ( ! empty( $settings['list_id'] ) ) {
			$subscriber['campaign'] = array(
				'campaignId' => $settings['list_id'],
			);
		}

		self::add_custom_fields( $vars, $subscriber );

		$api->subscribe_to_campaign( $subscriber );
	}

	private static function add_custom_fields( $vars, &$subscriber ) {
		$skip = array( 'email', 'first_name', 'last_name' );
		foreach ( $vars as $tag => $value ) {
			if ( in_array( $tag, $skip ) ) {
				continue;
			}
			if ( ! isset( $subscriber['customFieldValues'] ) ) {
				$subscriber['customFieldValues'] = array();
			}
			$subscriber['customFieldValues'][] = array(
				'customFieldId' => $tag,
				'value'         => is_array( $value ) ? $value : array( $value ),
			);
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

}
