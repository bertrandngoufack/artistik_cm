<?php
/**
 * This class is the main controller to hook into formidable.
 */
class FrmActiveCampaignAppController {
	public static $min_version = '2.0';

	public static function min_version_notice() {
		$frm_version = is_callable( 'FrmAppHelper::plugin_version' ) ? FrmAppHelper::plugin_version() : 0;

		// Check if Formidable meets minimum requirements.
		if ( version_compare( $frm_version, self::$min_version, '>=' ) ) {
			return;
		}

		$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
		echo '<tr class="plugin-update-tr active"><th colspan="' . (int) $wp_list_table->get_column_count() . '" class="check-column plugin-update colspanchange"><div class="update-message">' .
			esc_html_e( 'You are running an outdated version of Formidable. This plugin needs Formidable v2.0 + to work correctly.', 'frmactivecampaign' ) .
			'</div></td></tr>';
	}

	public static function include_updater() {
		if ( class_exists( 'FrmAddon' ) ) {
			include self::path() . '/models/FrmActiveCampaignUpdate.php';
			FrmActiveCampaignUpdate::load_hooks();
		}
	}

	public static function path() {
		return dirname( dirname( __FILE__ ) );
	}

	/**
	 * Allow Active Campaign to be triggered by the automation.
	 *
	 * @since x.x
	 *
	 * @param array $actions Actions supporting automation.
	 * @return array
	 */
	public static function add_active_campaign_to_automation( $actions ) {
		$actions[] = 'activecampaign';
		return $actions;
	}

	/**
	 * Get the url to this plugin files.
	 *
	 * @since 2.01
	 */
	public static function plugin_url() {
		return plugins_url() . '/' . basename( self::path() );
	}

	public static function trigger_activecampaign( $action, $entry, $form ) {
		$settings = $action->post_content;
		if ( empty( $settings['list_id'] ) ) {
			// No list is mapped.
			return;
		}

		$vars = self::prepare_mapped_values( $settings, $entry );

		$api = new FrmActiveCampaignAPI();

		if ( $entry->created_at !== $entry->updated_at ) {
			// Check for a contact id to update.
			$query = array(
				'item_id'  => $entry->id,
				'field_id' => 0,
			);
			$metas = FrmEntryMeta::getAll( $query, ' ORDER BY it.created_at ASC', '', true );
			if ( $metas ) {
				foreach ( $metas as $meta ) {
					if ( isset( $meta->meta_value[ $api->meta_name ] ) ) {
						$entry->ac_meta_id = $meta->id;
						$vars['id']        = $meta->meta_value[ $api->meta_name ];
					}
				}
			}
		}

		if ( ! isset( $vars['email'] ) ) {
			// No email address is mapped.
			return;
		}

		$subscriber = self::prepare_subscriber( $settings, $entry, $vars );

		$api->subscribe(
			$subscriber,
			array(
				'entry'  => $entry,
				'action' => $action,
			)
		);
	}

	/**
	 * Map entry values to vars before sending to API.
	 *
	 * @param array  $settings
	 * @param object $entry
	 * @return array
	 */
	private static function prepare_mapped_values( $settings, $entry ) {
		$vars = array();

		foreach ( $settings['fields'] as $field_tag => $field_id ) {

			if ( empty( $field_id ) ) {
				// Don't sent an empty value.
				continue;
			}

			$vars[ $field_tag ] = self::get_entry_or_post_value( $entry, $field_id );

			$field = FrmField::getOne( $field_id );
			if ( is_numeric( $vars[ $field_tag ] ) ) {
				if ( 'user_id' === $field->type && ! empty( $vars[ $field_tag ] ) ) {
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
				} elseif ( 'file' === $field->type && ! empty( $vars[ $field_tag ] ) ) {
					$vars[ $field_tag ] = wp_get_attachment_url( $vars[ $field_tag ] );
					if ( false === $vars[ $field_tag ] ) {
						unset( $vars[ $field_tag ] );
					}
				} else {
					$args               = array(
						'type'     => $field->type,
						'truncate' => false,
						'entry_id' => $entry->id,
					);
					$vars[ $field_tag ] = FrmEntriesHelper::display_value( $vars[ $field_tag ], $field, $args );
				}
			} elseif ( is_array( $vars[ $field_tag ] ) && 'file' === $field->type ) {
				$urls = array();
				foreach ( $vars[ $field_tag ] as $file_id ) {
					$url = wp_get_attachment_url( $file_id );
					if ( false !== $url ) {
						$urls[] = $url;
					}
				}
				$vars[ $field_tag ] = $urls;
			} elseif ( is_array( $vars[ $field_tag ] ) && 'name' === $field->type ) {
				if ( 'first_name' === $field_tag ) {
					$vars[ $field_tag ] = isset( $vars[ $field_tag ]['first'] ) ? $vars[ $field_tag ]['first'] : '';
				} elseif ( 'last_name' === $field_tag ) {
					$vars[ $field_tag ] = isset( $vars[ $field_tag ]['last'] ) ? $vars[ $field_tag ]['last'] : '';
				}
			}

			if ( is_array( $vars[ $field_tag ] ) && ! self::custom_field_is_checkbox_type( $field_tag ) ) {
				$vars[ $field_tag ] = implode( ', ', $vars[ $field_tag ] );
			}
		}

		return $vars;
	}

	/**
	 * Check if an Active Campaign custom field is a checkbox type. If it is, data should be sent as an array.
	 *
	 * @param int $field_tag
	 * @return bool
	 */
	private static function custom_field_is_checkbox_type( $field_tag ) {
		$custom_field = self::get_custom_field( $field_tag );
		return false !== $custom_field && 'checkbox' === $custom_field->element;
	}

	/**
	 * Check Active Campaign API for a custom field.
	 *
	 * @param int $field_tag
	 * @return object|false
	 */
	private static function get_custom_field( $field_tag ) {
		$api         = new FrmActiveCampaignAPI();
		$list_fields = $api->get_custom_fields();
		foreach ( $list_fields as $field ) {
			if ( ! is_object( $field ) ) {
				continue;
			}
			if ( $field_tag == $field->id ) {
				return $field;
			}
		}
		return false;
	}

	private static function prepare_subscriber( $settings, $entry, $vars ) {
		$subscriber = self::setup_subscriber( $settings, $entry );

		$subscriber['email']      = $vars['email'];
		$subscriber['first_name'] = ! empty( $vars['first_name'] ) ? ' ' . $vars['first_name'] : '';
		$subscriber['last_name']  = ! empty( $vars['last_name'] ) ? ' ' . $vars['last_name'] : '';
		$subscriber['phone']      = ! empty( $vars['phone'] ) ? ' ' . $vars['phone'] : '';
		$subscriber['tags']       = ! empty( $vars['tags'] ) ? ' ' . $vars['tags'] : '';

		if ( isset( $vars['id'] ) && is_numeric( $vars['id'] ) ) {
			$subscriber['id'] = $vars['id'];
		}

		$default_fields = array_keys( $subscriber );
		// Custom fields.
		foreach ( $vars as $custom_field_id => $value ) {
			if ( ! in_array( $custom_field_id, $default_fields ) ) {
				$subscriber[ 'field[' . $custom_field_id . ',0]' ] = $value;
			}
		}

		return array_filter( $subscriber, 'self::filter_subscriber_value' );
	}

	/**
	 * Determine if a value should be passed to Active Campaign API or not.
	 *
	 * @param mixed $value
	 * @return bool true if the value should be passed.
	 */
	private static function filter_subscriber_value( $value ) {
		if ( is_string( $value ) ) {
			return '' !== $value;
		}
		if ( is_numeric( $value ) ) {
			return true;
		}
		return (bool) $value;
	}

	private static function setup_subscriber( $settings, $entry ) {
		$subscriber = array();

		if ( 'yes' === $settings['send_ip_address'] ) {
			$subscriber['ip4'] = $entry->ip;
		}

		if ( empty( $settings['list_id'] ) ) {
			return $subscriber;
		}

		if ( 'yes' === $settings['instant_autoresponsder'] ) {
			$subscriber[ 'instantresponders[' . $settings['list_id'] . ']' ] = 1;
		}

		$subscriber[ 'p[' . $settings['list_id'] . ']' ] = $settings['list_id'];

		// If a form is selected, we can use the double opt-in.
		if ( ! empty( $settings['ac_form'] ) ) {
			// 0 is unconfirmed status.
			$subscriber[ 'status[' . $settings['list_id'] . ']' ] = 0;
			// empty is single opt-in, and auto is not linked to a form.
			$subscriber['form'] = $settings['ac_form'];
		}

		return $subscriber;
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
