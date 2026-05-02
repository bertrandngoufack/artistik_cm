<?php

class FrmMailPoetAppController {

	public static $min_version = '2.0';

	public static function min_version_notice() {
		$frm_version = is_callable( 'FrmAppHelper::plugin_version' ) ? FrmAppHelper::plugin_version() : 0;

		// check if Formidable meets minimum requirements
		if ( version_compare( $frm_version, self::$min_version, '>=' ) ) {
			return;
		}

		$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
		echo '<tr class="plugin-update-tr active"><th colspan="' . (int) $wp_list_table->get_column_count() . '" class="check-column plugin-update colspanchange"><div class="update-message">' .
			esc_html_e( 'You are running an outdated version of Formidable. This plugin needs Formidable v2.0 + to work correctly.', 'frmmailpoet' ) .
			'</div></td></tr>';
	}

	/**
	 * @since 1.03
	 *
	 * @return void
	 */
	public static function load_lang() {
		load_plugin_textdomain( 'frmmailpoet', false, basename( self::path() ) . '/languages/' );
	}

	public static function include_updater() {
		if ( class_exists( 'FrmAddon' ) ) {
			include self::path() . '/models/FrmMailPoetUpdate.php';
			FrmMailPoetUpdate::load_hooks();
		}
	}

	public static function path() {
		return dirname( dirname( __FILE__ ) );
	}

	public static function plugin_url() {
		return plugins_url() . '/' . basename( self::path() );
	}

	public static function trigger_mailpoet( $action, $entry, $form ) {
		$settings = $action->post_content;
		self::send_to_mailpoet( $entry, $form, $settings, $action );
	}

	public static function send_to_mailpoet( $entry, $form, $settings, $action = array() ) {
		$entry_id = $entry->id;
		$vars = array();

		foreach ( $settings['fields'] as $field_tag => $field_id ) {
			if ( empty( $field_id ) ) {
				// don't sent an empty value
				continue;
			}
			$vars[ $field_tag ] = self::get_entry_or_post_value( $entry, $field_id );

			self::add_field_value( compact( 'field_tag', 'field_id', 'entry_id' ), $vars );
		}

		if ( ! isset( $vars['email'] ) ) {
			// no email address is mapped
			return;
		}

		$lists = array();
		if ( ! empty( $settings['list_id'] ) ) {
			$lists[] = $settings['list_id'];
		}

		$options = array(
			'send_confirmation_email' => 'no' !== $settings['send_confirmation_email'],
			'schedule_welcome_email'  => 'no' !== $settings['schedule_welcome_email'],
		);

		if ( class_exists( 'FrmLog' ) ) {
			$log_args = array(
				'entry' => $entry,
				'hook'  => $action,
			);
		}

		try {
			$subscriber = \MailPoet\API\API::MP( 'v1' )->addSubscriber( $vars, $lists, $options );
			if ( isset( $log_args ) ) {
				$message  = 'Create a new subscriber and add it to List ' . implode( ',', $lists );
				$message .= '. Subscriber details: ID => ' . $subscriber['id'] . ', email => ' . $subscriber['email'];
				$log_args = self::fill_log_args( $log_args, '', $message );
				self::log_results( $log_args );
			}
		} catch ( Exception $exception ) {
			if ( $exception->getCode() === \MailPoet\API\MP\v1\APIException::SUBSCRIBER_EXISTS ) {
				// try to subscribe user to lists.
				try {
					$subscriber = \MailPoet\API\API::MP( 'v1' )->subscribeToLists( $vars['email'], $lists, $options );
					if ( isset( $log_args ) ) {
						$message  = 'Add an existing subscriber to List ' . implode( ',', $lists );
						$message .= '. Subscriber details: ID => ' . $subscriber['id'] . ', email => ' . $subscriber['email'];
						$log_args = self::fill_log_args( $log_args, '', $message );
						self::log_results( $log_args );
					}
				} catch ( Exception $exception ) {
					if ( isset( $log_args ) ) {
						$log_args = self::fill_log_args( $log_args, $exception );
						self::log_results( $log_args );
					}
				}
			} elseif ( isset( $log_args ) ) {
				$log_args = self::fill_log_args( $log_args, $exception );
				self::log_results( $log_args );
			}
		}
	}

	/**
	 * Fills the logging arguments array with properties from an exception object.
	 *
	 * @param array            $log_args An array of prefilled log arguments.
	 * @param Exception|string $exception The exception object or an empty string if no Exception is available.
	 * @param string           $message Message for successful operations.
	 *
	 * @return array $log_args Log args variable ready to be used by the logger function.
	 */
	public static function fill_log_args( $log_args, $exception, $message = null ) {
		if ( $exception ) {
			$unique_log_args = array(
				'response' => $exception->getTrace(),
				'processed' => array(
					'code'    => $exception->getCode(),
					'message' => $exception->getMessage(),
				),
			);
		} else {
			$unique_log_args = array(
				'response' => '',
				'processed' => array(
					'code'    => '',
					'message' => $message,
				),
			);
		}

		$log_args = array_merge( $log_args, $unique_log_args );

		return $log_args;
	}

	/**
	 * @param array $atts
	 * @return void
	 */
	public static function log_results( $atts ) {
		if ( ! class_exists( 'FrmLog' ) ) {
			return;
		}

		if ( empty( $atts['hook'] ) ) {
			$atts['hook'] = (object) array(
				'ID' => 0,
				'post_title' => '',
			);
		}

		$content = $atts['processed'];
		$message = isset( $content['message'] ) ? $content['message'] : '';
		$headers = '';

		$log = new FrmLog();
		$log->add(
			array(
				'title'   => __( 'Mailpoet:', 'frmmailpoet' ) . ' ' . $atts['hook']->post_title,
				'content' => (array) $atts['response'],
				'fields'  => array(
					'entry'   => $atts['entry']->id,
					'action'  => $atts['hook']->ID,
					'code'    => isset( $content['code'] ) ? $content['code'] : '',
					'message' => $message,
					'url'     => isset( $content['url'] ) ? $content['url'] : '',
					'request' => isset( $content['body'] ) ? $content['body'] : '',
					'headers' => $headers,
				),
			)
		);
	}

	/**
	 * @param array $args
	 * @param array $vars
	 * @return void
	 */
	private static function add_field_value( $args, &$vars ) {
		$field_tag = $args['field_tag'];
		$field_id  = $args['field_id'];
		$field     = FrmField::getOne( $field_id );

		if ( 'file' === $field->type ) {
			$vars[ $field_tag ] = FrmProEntriesController::get_field_value_shortcode(
				array(
					'field_id' => $field_id,
					'entry_id' => $args['entry_id'],
					'show'     => '1',
					'html'     => 0,
				)
			);
			return;
		}

		if ( is_numeric( $vars[ $field_tag ] ) ) {
			if ( 'user_id' === $field->type ) {
				$vars[ $field_tag ] = self::get_value_from_user_id( $vars[ $field_tag ], $field_tag );
			} else {
				$display_atts = array(
					'type'     => $field->type,
					'truncate' => false,
					'entry_id' => $args['entry_id'],
				);
				$vars[ $field_tag ] = FrmEntriesHelper::display_value( $vars[ $field_tag ], $field, $display_atts );
			}
		}

		if ( is_array( $vars[ $field_tag ] ) ) {
			$value = $vars[ $field_tag ];

			if ( 'first_name' === $field_tag && 'name' === $field->type ) {
				$value = isset( $value['first'] ) ? $value['first'] : '';
			} elseif ( 'last_name' === $field_tag && 'name' === $field->type ) {
				$value = isset( $value['last'] ) ? $value['last'] : '';
			} else {
				$value = implode( ', ', $value );
			}

			$vars[ $field_tag ] = $value;
		}
	}

	private static function get_value_from_user_id( $value, $field_tag ) {
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
		return $value;
	}

	public static function get_entry_or_post_value( $entry, $field_id ) {
		$value = '';
		if ( ! empty( $entry ) && isset( $entry->metas[ $field_id ] ) ) {
			$value = $entry->metas[ $field_id ];
		} elseif ( isset( $_POST['item_meta'][ $field_id ] ) ) { // WPCS: CSRF ok.
			$value = sanitize_text_field( wp_unslash( $_POST['item_meta'][ $field_id ] ) ); // WPCS: CSRF ok.
		}
		return $value;
	}

	public static function register_actions( $actions ) {
		$actions['mailpoet'] = 'FrmMailPoetAction';

		include_once self::path() . '/models/FrmMailPoetAction.php';

		return $actions;
	}

	/**
	 * @deprecated 1.03
	 *
	 * @param stdClass $form
	 * @param string   $form_action
	 */
	public static function hidden_form_fields( $form, $form_action ) {
		_deprecated_function( __METHOD__, '1.03' );
	}
}
