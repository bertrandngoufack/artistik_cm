<?php

class FrmHubSpotAppController {
	public static $min_version = '6.0';

	public static function min_version_notice() {
		$frm_version = is_callable( 'FrmAppHelper::plugin_version' ) ? FrmAppHelper::plugin_version() : 0;

		// check if Formidable meets minimum requirements.
		if ( version_compare( (string) $frm_version, self::$min_version, '>=' ) ) {
			return;
		}

		$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
		// We could not find the list table class instance so we don't know where to append this message then early exit.
		if ( ! $wp_list_table ) {
			return;
		}

		printf(
			'<tr class="plugin-update-tr active"><th colspan="%1$s" class="check-column plugin-update colspanchange"><div class="update-message">%2$s</div></td></tr>',
			absint( $wp_list_table->get_column_count() ),
			sprintf(
				/* translators: %1$s: Minimum version HubSpot needs to work */
				esc_html__( 'You are running an outdated version of Formidable. This plugin needs Formidable v%1$s+ to work correctly.', 'formidable-hubspot' ),
				esc_html( self::$min_version )
			)
		);
	}

	/**
	 * @since 1.10
	 *
	 * @return void
	 */
	public static function admin_init() {
		self::include_updater();
		FrmHubSpotMigrate::init();
		self::maybe_enqueue_pro_styles();

		// Manipulate the entries list when it's necessary to exclude HubSpot field.
		if ( FrmAppHelper::is_admin_page( 'formidable-entries' ) ) {
			$frm_settings = FrmAppHelper::get_settings();
			add_filter( 'manage_' . sanitize_title( $frm_settings->menu ) . '_page_formidable-entries_columns', 'FrmHubSpotAppController::manage_columns', 25 );
		}
	}

	/**
	 * @return void
	 */
	public static function include_updater() {
		if ( class_exists( 'FrmAddon' ) ) {
			include FrmHubSpotAppHelper::path() . '/models/FrmHubSpotUpdate.php';
			FrmHubSpotUpdate::load_hooks();
		}
	}

	/**
	 * @since 1.10
	 *
	 * @return void
	 */
	private static function maybe_enqueue_pro_styles() {
		if ( ! class_exists( 'FrmProDb' ) ) {
			return;
		}

		wp_register_style( 'formidable-pro-fields', admin_url( 'admin-ajax.php?action=pro_fields_css' ), array(), FrmProDb::$plug_version );
		if ( is_admin() && 'formidable-settings' === FrmAppHelper::get_param( 'page', '', 'sanitize_key' ) ) {
			wp_enqueue_style( 'formidable-pro-fields' );
		}
	}

	/**
	 * Enqueue assets for settings form and builder page.
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public static function enqueue_admin_assets() {
		if ( ! self::is_form_settings_page() ) {
			return;
		}

		// Enqueue styles.
		wp_enqueue_style( 'formidable-hubspot-admin', FrmHubSpotAppHelper::url() . '/css/admin.css', array(), FrmHubSpotAppHelper::plugin_version() );

		// Enqueue scripts.
		wp_register_script( 'frm_hubspot_admin', FrmHubSpotAppHelper::url() . '/js/admin.min.js', array( 'formidable_dom' ), FrmHubSpotAppHelper::plugin_version(), true );
		wp_localize_script(
			'frm_hubspot_admin',
			'frmHubSpotGlobal',
			array(
				'nonce'   => wp_create_nonce( 'frm_hubspot_ajax' ),
				'homeURL' => esc_url( home_url() ),
				'authURL' => FrmHubSpotAppHelper::get_install_url(),
			)
		);

		wp_enqueue_script( 'frm_hubspot_admin' );
	}

	/**
	 * Initializes plugin translation.
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public static function init_translation() {
		load_plugin_textdomain( 'formidable-hubspot', false, basename( FrmHubSpotAppHelper::path() ) . '/languages/' );
	}

	public static function trigger_hubspot( $action, $entry, $form ) {
		// Check for active connection before proceeding further.
		$api = new FrmHubSpotAuthHelper( FrmHubSpotAppHelper::get_active_authorization_class() );

		if ( ! $api->has_credentials() ) {
			return;
		}

		$settings = $action->post_content;
		$email_id = ''; // Email id is optional to create contact in hubspot but needed to update existing contact
		$vars     = self::prepare_mapped_values( $settings, $entry );

		$subscriber = self::prepare_subscriber( $settings, $entry, $vars );
		if ( ! empty( $subscriber['email'] ) ) {
			$email_id = $subscriber['email'];
			unset( $subscriber['email'] );
		}

		$list_id = ! empty( $settings['list_id'] ) ? $settings['list_id'] : '';

		$pass = array(
			'action'   => $action,
			'entry'    => $entry,
			'email_id' => $email_id,
		);
		$api->subscribe_to_list( $subscriber, $list_id, $pass );
	}

	private static function prepare_mapped_values( $settings, $entry ) {
		$vars = array();
		foreach ( $settings['fields'] as $field_tag => $field_id ) {
			if ( empty( $field_id ) ) {
				// don't sent an empty value
				continue;
			}

			if ( ! is_numeric( $field_id ) ) {
				// If the value is set directly in the settings.
				$vars[ $field_tag ] = $field_id;
				continue;
			}

			$vars[ $field_tag ] = self::get_entry_or_post_value( $entry, $field_id );
			$field              = FrmField::getOne( $field_id );
			if ( is_numeric( $vars[ $field_tag ] ) ) {
				if ( 'user_id' === $field->type ) {
					$user_data = get_userdata( (int) $vars[ $field_tag ] );
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
						// get file url
						$vars[ $field_tag ] = FrmProEntriesController::get_field_value_shortcode(
							array(
								'field_id' => $field_id,
								'entry_id' => $entry->id,
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
								'entry_id' => $entry->id,
							)
						);
					}
				}
			} elseif ( is_array( $vars[ $field_tag ] ) ) {
				if ( 'firstname' === $field_tag && 'name' === $field->type ) {
					$vars[ $field_tag ] = isset( $vars[ $field_tag ]['first'] ) ? $vars[ $field_tag ]['first'] : '';
				} elseif ( 'lastname' === $field_tag && 'name' === $field->type ) {
					$vars[ $field_tag ] = isset( $vars[ $field_tag ]['last'] ) ? $vars[ $field_tag ]['last'] : '';
				} else {
					$vars[ $field_tag ] = implode( ';', $vars[ $field_tag ] );
				}
			}
		}

		return $vars;
	}

	private static function prepare_subscriber( $settings, $entry, $vars ) {
		$subscriber = array();

		// Contact fields
		foreach ( $vars as $custom_field_id => $value ) {
			if ( 'blog_default_hubspot_blog_subscription' !== $custom_field_id ) {
				$subscriber['properties'][] = array(
					'property' => $custom_field_id,
					'value'    => $value,
				);

				if ( 'email' == $custom_field_id ) {
					$subscriber['email'] = $value;
				}
			}
		}

		if ( ! empty( $settings['fields']['blog_default_hubspot_blog_subscription'] ) ) {
			$subscriber['properties'][] = array(
				'property' => 'blog_default_hubspot_blog_subscription',
				'value'    => $settings['fields']['blog_default_hubspot_blog_subscription'],
			);
		}

		return $subscriber;
	}

	public static function get_entry_or_post_value( $entry, $field_id ) {
		$value = '';
		if ( ! empty( $entry ) && isset( $entry->metas[ $field_id ] ) ) {
			$value = $entry->metas[ $field_id ];
		} elseif ( isset( $_POST['item_meta'][ $field_id ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$value = sanitize_text_field( wp_unslash( $_POST['item_meta'][ $field_id ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
		return $value;
	}

	/**
	 * Add field for hubspot.
	 *
	 * @since 2.0
	 *
	 * @param array $fields
	 * @return array
	 */
	public static function add_field( $fields ) {
		$fields['hubspot'] = array(
			'name' => __( 'HubSpot', 'formidable-hubspot' ),
			'icon' => '', // This field is hidden so the icon does not matter. It just needs to be set.
		);
		return $fields;
	}

	/**
	 * Add field for hubspot.
	 *
	 * @since 2.0
	 *
	 * @param string $class class name.
	 * @param string $field_type field type.
	 * @return string
	 */
	public static function add_field_class( $class, $field_type ) {
		if ( 'hubspot' === $field_type ) {
			$class = 'FrmHubSpotField';
		}
		return $class;
	}

	/**
	 * Hide HubSpot field from builder.
	 * Since we may have multiple actions we need to find Multiple HubSpot fields there is no need for break; in loop.
	 *
	 * @since 2.0
	 *
	 * @param object[] $fields Array of fields.
	 *
	 * @return array
	 */
	public static function hide_builder_field( $fields ) {
		foreach ( $fields as $k => $field ) {
			if ( isset( $field->type ) && 'hubspot' === $field->type ) {
				unset( $fields[ $k ] );
			}
		}
		return $fields;
	}

	/**
	 * Prevent HubSpot field from appearing in conditional logic options for all actions.
	 *
	 * @since 2.0
	 *
	 * @param array<string> $exclude_fields
	 *
	 * @return array<string>
	 */
	public static function hide_hubspot_from_condition_logic_row( $exclude_fields ) {
		$exclude_fields[] = 'hubspot';
		return $exclude_fields;
	}

	/**
	 * Exclude HubSpot field from entry detail page.
	 * No need for a break since there could be more HubSpot fields since we may have multiple actions.
	 *
	 * @since 2.0
	 *
	 * @param array $field_ids The list of field IDs.
	 * @param array $atts      The arguments. See {@see FrmEntriesController::show_entry_shortcode()}.
	 *
	 * @return array
	 */
	public static function exclude_hubspot_field_from_entry_detail( $field_ids, $atts ) {
		if ( empty( $atts['fields'] ) ) {
			return $field_ids;
		}

		foreach ( $atts['fields'] as $k => $field ) {
			if ( 'hubspot' === $field->type ) {
				$field_ids[] = $field->id;
			}
		}
		return $field_ids;
	}

	/**
	 * Exclude HubSpot field from entries list.
	 * No need for a break since there could be more HubSpot field for a entry.
	 *
	 * @since 2.0
	 *
	 * @param array<string> $columns table columns.
	 *
	 * @return array<string>
	 */
	public static function manage_columns( $columns ) {
		global $frm_vars;
		$form_id = FrmForm::get_current_form_id();
		$form_fields = FrmField::get_all_for_form( $form_id );

		foreach ( $form_fields as $key => $value ) {
			if ( 'hubspot' === $value->type ) {
				unset( $columns[ $form_id . '_' . $value->field_key ] );
			}
		}

		$frm_vars['cols'] = $columns;

		return $columns;
	}

	/**
	 * Check if the current page is the form settings page or formidable builder page.
	 *
	 * @since 2.0
	 *
	 * @return bool
	 */
	public static function is_form_settings_page() {
		$page   = FrmAppHelper::simple_get( 'page', 'sanitize_title' );
		$action = FrmAppHelper::simple_get( 'frm_action', 'sanitize_title' );
		return ( 'formidable-settings' === $page || 'formidable' === $page || 'settings' === $action );
	}

	/**
	 * @deprecated 2.0
	 */
	public static function path() {
		_deprecated_function( __METHOD__, '2.0', 'FrmHubSpotAppHelper::path()' );
		return FrmHubSpotAppHelper::path();
	}

	/**
	 * @deprecated 2.0
	 */
	public static function plugin_url() {
		_deprecated_function( __METHOD__, '2.0', 'FrmHubSpotAppHelper::url()' );
		return FrmHubSpotAppHelper::url();
	}

	public static function hidden_form_fields( $form, $form_action ) {
		_deprecated_function( __METHOD__, '1.08' );
	}
}
