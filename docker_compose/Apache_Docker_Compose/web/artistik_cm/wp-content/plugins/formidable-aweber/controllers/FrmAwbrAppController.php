<?php

class FrmAwbrAppController {

	public static function load_hooks() {
		add_action( 'init', 'FrmAwbrAppController::load_lang' );
		add_action( 'admin_init', 'FrmAwbrAppController::include_updater', 1 );
		add_action( 'frm_trigger_aweber_action', 'FrmAwbrAppController::trigger_aweber', 10, 3 );
	}

	public static function path() {
		return dirname( __DIR__ );
	}

	public static function load_lang() {
		load_plugin_textdomain( 'formidable-aweber', false, basename( self::path() ) . '/languages/' );
	}

	public static function include_updater() {
		if ( class_exists( 'FrmAddon' ) ) {
			include_once self::path() . '/models/FrmAwbrUpdate.php';
			FrmAwbrUpdate::load_hooks();
		}
	}

	public static function trigger_aweber( $action, $entry, $form ) {
		$settings = $action->post_content;
		$info     = array(
			'action_id' => $action->ID,
		);
		self::send_to_aweber( $entry, $form, $settings, $info );
	}

	/**
	 * Add a subscription with the Aweber API.
	 *
	 * @param stdClass $entry
	 * @param stdClass $form
	 * @param array    $list_options
	 * @param array    $info
	 */
	public static function send_to_aweber( $entry, $form, $list_options, $info = array() ) {
		$list = FrmAwbrAppHelper::get_aweber_list( $list_options['list_id'] );
		if ( empty( $list ) ) {
			return;
		}

		$vars = array(
			'custom_fields' => array(),
		);

		$ip = FrmAppHelper::get_server_value( 'REMOTE_ADDR' );
		if ( ! self::ip_is_private( $ip ) ) {
			// Aweber blocks local IP addresses but it's an optional field, so omit it for IPs starting with 172.
			$vars['ip_address'] = $ip;
		}

		foreach ( $list_options['fields'] as $field_tag => $field_data ) {
			if ( in_array( $field_tag, array( 'ad_tracking', 'tags' ), true ) ) {
				$vars[ $field_tag ] = FrmFormsController::filter_content( $field_data, $form, $entry );

				if ( 'tags' === $field_tag ) {
					$vars[ $field_tag ] = self::reduce_tags_string_to_an_array( $vars[ $field_tag ] );
				}

				continue;
			}

			$field_id = $field_data;
			$val      = FrmAwbrAppHelper::get_entry_or_post_value( $entry, $field_id );
			$field    = FrmField::getOne( $field_id );

			if ( is_numeric( $val ) ) {
				if ( 'user_id' === $field->type ) {
					$user_data = get_userdata( $val );
					if ( $user_data ) {
						if ( 'email' === $field_tag ) {
							$val = $user_data->user_email;
						} elseif ( 'name' === $field_tag ) {
							$val = $user_data->first_name . ' ' . $user_data->last_name;
						} else {
							$val = $user_data->user_login;
						}
					}
				} else {
					$value_atts = array(
						'type'     => $field->type,
						'truncate' => false,
						'entry_id' => $entry->id,
					);
					if ( is_callable( 'FrmEntriesHelper::display_value' ) ) {
						$val = FrmEntriesHelper::display_value( $val, $field, $value_atts );
					} elseif ( is_callable( 'FrmProEntryMetaHelper::display_value' ) ) {
						$val = FrmProEntryMetaHelper::display_value( $val, $field, $value_atts );
					}
				}
			}

			if ( is_string( $val ) ) {
				// Aweber does not support accents, so switch the letters out for the non-accented versions.
				$val = remove_accents( $val );
			}

			if ( 'email' === $field_tag || ( 'name' === $field_tag && 'name' !== $field->type ) ) {
				$vars[ $field_tag ] = $val;
			} elseif ( 'name' === $field_tag ) {
				$vars[ $field_tag ] = $val['first'] . ' ' . $val['last'];
			} else {
				$vars['custom_fields'][ $field_tag ] = $val;
			}
		}

		$vars = apply_filters( 'frm_awbr_vars', $vars, $form );

		if ( ! isset( $vars['email'] ) ) {
			//no email address is mapped
			return;
		}

		if ( empty( $vars['custom_fields'] ) ) {
			unset( $vars['custom_fields'] );
		}

		try {
			$subscribers    = $list->subscribers;
			$new_subscriber = $subscribers->create( $vars );

			if ( class_exists( 'FrmLog' ) ) {
				$log = new FrmLog();
				$log->add(
					array(
						'title'   => 'AWeber Subscription',
						'content' => array(
							'uuid' => $new_subscriber->data['uuid'],
						),
						'fields'  => array(
							'entry'   => $entry->id,
							'action'  => isset( $info['action_id'] ) ? $info['action_id'] : 0,
							'code'    => 201,
							'message' => $vars,
						),
					)
				);
			}
		} catch ( FrmAWeberAPIException $exception ) {
			// Subscription failed.
			if ( class_exists( 'FrmLog' ) ) {
				$log = new FrmLog();
				$log->add(
					array(
						'title'   => 'AWeber Exception',
						'content' => $exception->message,
						'fields'  => array(
							'entry'   => $entry->id,
							'action'  => isset( $info['action_id'] ) ? $info['action_id'] : 0,
							'code'    => $exception->status,
							'message' => $vars,
						),
					)
				);
			}
		}
	}

	/**
	 * Check if an IP is in a private range (including IPs that fall between 172.16.0.0 – 172.31.255.255).
	 *
	 * @param string $ip
	 * @return bool
	 */
	private static function ip_is_private( $ip ) {
		$filtered_ip = filter_var(
			$ip,
			FILTER_VALIDATE_IP,
			FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
		);
		return $filtered_ip !== $ip;
	}

	private static function reduce_tags_string_to_an_array( $tags ) {
		return array_reduce(
			explode( ',', $tags ),
			function ( $total, $tag ) {
				$tag = trim( $tag );
				if ( is_array( $total ) && $tag && ! in_array( $tag, $total, true ) ) {
					$total[] = $tag;
				}
				return $total;
			},
			array()
		);
	}

	public static function hidden_form_fields() {
		_deprecated_function( __METHOD__, '2.02' );
	}

	public static function maybe_send_to_aweber() {
		_deprecated_function( __METHOD__, '2.02' );
	}
}
