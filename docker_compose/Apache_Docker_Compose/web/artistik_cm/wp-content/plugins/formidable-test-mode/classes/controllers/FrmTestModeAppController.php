<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTestModeAppController {

	/**
	 * @var int
	 */
	private static $ai_autofill_retry_count = 0;

	/**
	 * @return void
	 */
	public static function load_hooks() {
		if ( is_admin() ) {
			self::load_admin_hooks();
		}
	}

	/**
	 * @return void
	 */
	private static function load_admin_hooks() {
		add_action(
			'frm_test_mode_init',
			/**
			 * @return void
			 */
			function () {
				self::maybe_show_all_hidden_fields();
				FrmProFormState::set_initial_value( 'testmode', 1 );
			}
		);

		/**
		 * @return void
		 */
		$new_repeater_row_callback = function () {
			if ( ! FrmProFormState::get_from_request( 'testmode', false ) ) {
				return;
			}

			self::maybe_show_all_hidden_fields();
		};
		add_action( 'wp_ajax_frm_add_form_row', $new_repeater_row_callback, 1 );
		add_action( 'wp_ajax_nopriv_frm_add_form_row', $new_repeater_row_callback, 1 );

		/**
		 * @return void
		 */
		$load_form_callback = function () {
			if ( FrmProFormState::get_from_request( 'testmode', false ) ) {
				add_filter( 'frm_filter_final_form', 'FrmTestModeController::maybe_add_test_mode_container', 99 );
			}
		};
		add_action( 'wp_ajax_frm_load_form', $load_form_callback, 0 );
		add_action( 'wp_ajax_nopriv_frm_load_form', $load_form_callback, 0 );

		// Carry test mode controls when submitting with AJAX.
		add_action(
			'wp_loaded',
			/**
			 * @return void
			 */
			function () {
				if ( ! FrmAppHelper::doing_ajax() || ! isset( $_POST['form_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
					return;
				}

				if ( FrmProFormState::get_from_request( 'testmode', false ) ) {
					add_filter( 'frm_filter_final_form', 'FrmTestModeController::maybe_add_test_mode_container', 99 );
				}
			},
			4
		);

		add_action(
			'frm_test_mode_container',
			/**
			 * @return void
			 */
			function () {
				self::on_test_mode_container();
			}
		);

		add_filter(
			'frm_validate_field_entry',
			/**
			 * @param array        $errors
			 * @param array|object $posted_field
			 * @return array
			 */
			function ( $errors, $posted_field ) {
				if ( self::is_test_mode_disabled_required_validation() ) {
					return self::remove_required_error_messages( $errors, $posted_field );
				}
				return $errors;
			},
			11,
			2
		);

		add_filter(
			'frm_test_mode_disable_required_fields_toggle_args',
			/**
			 * @param array $args
			 * @return array
			 */
			function ( $args ) {
				return self::modify_disable_required_fields_toggle_args( $args );
			}
		);

		add_filter(
			'frm_test_mode_show_all_hidden_fields_toggle_args',
			/**
			 * @param array $args
			 * @return array
			 */
			function ( $args ) {
				return self::modify_show_all_hidden_fields_toggle_args( $args );
			}
		);

		add_filter(
			'frm_field_visible_to_user',
			/**
			 * @param bool         $visible
			 * @param array|object $field
			 * @return bool
			 */
			function ( $visible, $field ) {
				return self::is_field_visible_to_user( $visible, $field );
			},
			10,
			2
		);

		add_filter(
			'frm_test_mode',
			/**
			 * @param bool $test_mode
			 * @return bool
			 */
			function ( $test_mode ) {
				if ( ! $test_mode ) {
					return FrmProFormState::get_from_request( 'testmode', false );
				}
				return $test_mode;
			}
		);

		add_filter(
			'frm_form_is_visible',
			/**
			 * @param bool   $visible
			 * @param object $form
			 * @return bool
			 */
			function ( $visible, $form ) {
				return self::is_form_visible_to_user( $visible, $form );
			},
			10,
			2
		);

		add_filter(
			'frm_denylist_data',
			/**
			 * @param array $denylist
			 * @return array
			 */
			function ( $denylist ) {
				return self::maybe_update_denylist( $denylist );
			},
			0
		);

		add_action(
			'wp_ajax_frm_testmode_fill_in_empty_form_fields',
			/**
			 * @return void
			 */
			function () {
				self::handle_fill_in_empty_form_fields();
			}
		);

		add_filter(
			'frm_skip_form_action',
			/**
			 * @param bool  $skip
			 * @param array $args
			 * @return bool
			 */
			function ( $skip, $args ) {
				if ( $skip ) {
					return $skip;
				}

				if ( self::is_in_test_mode() ) {
					$skip = ! in_array( $args['action']->ID, self::get_enabled_form_action_ids(), true );
				}

				return $skip;
			},
			10,
			2
		);

		add_filter(
			'frm_get_met_on_submit_actions',
			/**
			 * @param array $actions
			 * @return array
			 */
			function ( $actions ) {
				if ( ! self::is_in_test_mode() ) {
					return $actions;
				}

				$enabled_form_action_ids = self::get_enabled_form_action_ids();

				return array_filter(
					$actions,
					/**
					 * @param WP_Post $action
					 * @return bool
					 */
					function ( $action ) use ( $enabled_form_action_ids ) {
						return in_array( $action->ID, $enabled_form_action_ids, true );
					}
				);
			}
		);

		add_filter(
			'frm_quizzes_scored_quiz_action',
			/**
			 * @param WP_Post $action
			 * @return WP_Post|null
			 */
			function ( $action ) {
				if ( ! self::is_in_test_mode() ) {
					return $action;
				}

				$enabled_form_action_ids = self::get_enabled_form_action_ids();
				if ( ! in_array( $action->ID, $enabled_form_action_ids, true ) ) {
					return null;
				}

				return $action;
			}
		);

		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );

		add_filter(
			'frm_field_is_conditionally_shown',
			/**
			 * @param bool $visible
			 * @return bool
			 */
			function ( $visible ) {
				return $visible || self::should_show_hidden_fields();
			}
		);

		add_filter(
			'frm_quiz_outcome_is_disabled',
			/**
			 * @param bool    $disabled
			 * @param WP_Post $outcome
			 * @return bool
			 */
			function ( $disabled, $outcome ) {
				if ( ! self::is_in_test_mode() ) {
					return $disabled;
				}

				return ! in_array( $outcome->ID, self::get_enabled_form_action_ids(), true );
			},
			10,
			2
		);

		add_action(
			'frm_after_create_entry',
			/**
			 * @param int   $entry_id
			 * @param int   $form_id
			 * @param array $args
			 * @return void
			 */
			function ( $entry_id, $form_id, $args ) {
				if ( self::is_in_test_mode() && empty( $args['is_child'] ) ) {
					FrmEntryMeta::add_entry_meta( $entry_id, 0, '', array( 'testmode' => 1 ) );
				}
			},
			10,
			3
		);

		add_filter(
			'frm_additional_timestamp_text',
			/**
			 * @param string   $text
			 * @param stdClass $entry
			 * @return string
			 */
			function ( $text, $entry ) {
				if ( ! self::entry_was_submitted_in_test_mode( $entry ) ) {
					return $text;
				}
				$text .= ' (' . __( 'Test Mode', 'formidable' ) . ')';
				return $text;
			},
			10,
			2
		);

		add_filter(
			'frm_test_mode_enabled_form_action_ids',
			/**
			 * @param array $action_ids
			 * @return array
			 */
			function ( $action_ids ) {
				if ( ! isset( $_GET['frm_testmode_enabled_form_actions'] ) && empty( $_POST ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
					return $action_ids;
				}

				if ( 'frm_load_form' === FrmAppHelper::get_post_param( 'action', '', 'sanitize_text_field' ) ) {
					// If we are starting over, select every form action again.
					return $action_ids;
				}

				return self::get_enabled_form_action_ids();
			}
		);

		add_filter(
			'frm_test_mode_selected_role',
			/**
			 * @param string $selected_role
			 * @return string
			 */
			function ( $selected_role ) {
				if ( ! empty( $_POST['frm_testmode'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
					// phpcs:ignore WordPress.Security.NonceVerification.Missing
					$selected_role = sanitize_text_field( wp_unslash( $_POST['frm_testmode']['preview_role'] ?? '' ) );
				} else {
					$selected_role = FrmAppHelper::simple_get( 'frm_testmode_role' );
				}
				return $selected_role;
			}
		);
	}

	/**
	 * @param stdClass $entry
	 * @return bool
	 */
	private static function entry_was_submitted_in_test_mode( $entry ) {
		$metas = FrmDb::get_col(
			'frm_item_metas',
			array(
				'field_id' => 0,
				'item_id' => $entry->id,
			),
			'meta_value'
		);
		return (bool) array_filter(
			$metas,
			/**
			 * @param string $meta
			 * @return bool
			 */
			function ( $meta ) {
				$meta = maybe_unserialize( $meta );
				return ! empty( $meta['testmode'] );
			}
		);
	}

	/**
	 * "Show all hidden fields".
	 *
	 * Show:
	 * - Conditionally hidden fields.
	 * - Fields hidden using the visibility setting.
	 * - The "Hidden" field type.
	 * - A field hidden using the frm_hidden layout class.
	 *
	 * Do not show:
	 * - Honeypot fields.
	 * - Fields on other pages.
	 * - Inactive conversational fields.
	 * - Unexpected fields: Gateway fields, hidden file ID fields.
	 * - Fields like form key that are hidden in every form.
	 */
	private static function maybe_show_all_hidden_fields() {
		if ( ! self::should_show_hidden_fields() ) {
			return;
		}

		self::show_hidden_field_types();

		// Overwrite field visibility settings.
		add_filter( 'frm_field_visible_to_user', '__return_true', 99 );

		add_action(
			'frm_pro_before_footer_js',
			/**
			 * @return void
			 */
			function () {
				global $frm_vars;

				// Disable conditional logic.
				$frm_vars['rules'] = array();
			}
		);

		add_filter(
			'frm_field_div_classes',
			/**
			 * @param string $classes
			 * @return string
			 */
			function ( $classes ) {
				$classes_array = explode( ' ', $classes );
				return implode( ' ', array_diff( $classes_array, array( 'frm_hidden' ) ) );
			}
		);
	}

	/**
	 * Show fields with the "Hidden" field type.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	private static function show_hidden_field_types() {
		if ( ! is_callable( 'FrmProFieldsHelper::field_is_hidden_on_page' ) ) {
			// This function will only exist if people have updated to the version of Pro
			// that supports test mode.
			return;
		}

		/**
		 * @param array $vars
		 * @param bool  $is_new
		 * @return array
		 */
		$field_vars_callback = function ( $vars, $is_new = false ) {
			if ( 'hidden' !== $vars['type'] ) {
				return $vars;
			}

			if ( FrmProFieldsHelper::field_is_hidden_on_page( $vars['id'] ) ) {
				// Only show hidden field on the current page.
				// And avoid field types that were changed to hidden because they were on another page.
				return $vars;
			}

			$vars['type']        = 'text';
			$vars['custom_html'] = FrmFieldsHelper::get_default_html( 'text' );

			// If creating a new entry, make the input readonly.
			if ( $is_new ) {
				$vars['custom_html'] = str_replace( '[input]', '[input readonly=1]', $vars['custom_html'] );
			}

			return $vars;
		};

		add_filter(
			'frm_setup_new_fields_vars',
			/**
			 * @param array $vars
			 * @return array
			 */
			function ( $vars ) use ( $field_vars_callback ) {
				return $field_vars_callback( $vars, true );
			}
		);
		add_filter( 'frm_setup_edit_fields_vars', $field_vars_callback );
	}

	/**
	 * @return bool
	 */
	private static function should_show_hidden_fields() {
		if ( ! self::is_in_test_mode() ) {
			return false;
		}

		$has_get_param  = (bool) FrmAppHelper::simple_get( 'frm_testmode_show_all_hidden_fields' );
		$has_post_param = ! empty( $_POST['frm_testmode']['show_all_hidden_fields'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		return $has_get_param || $has_post_param;
	}

	/**
	 * Handle AJAX request to make an AI request for form fill data.
	 */
	private static function handle_fill_in_empty_form_fields() {
		if ( ! current_user_can( 'frm_edit_forms' ) ) {
			wp_send_json_error( 'You do not have permission to fill in empty form fields' );
		}

		$nonce = FrmAppHelper::get_post_param( 'nonce', '', 'sanitize_text_field' );
		if ( ! wp_verify_nonce( $nonce, 'frm_ajax' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$pending_field_data = FrmAppHelper::get_post_param( 'pendingFillData', '', 'sanitize_text_field' );
		if ( ! $pending_field_data ) {
			wp_send_json_error( 'No pending field data' );
		}

		$pending_fields = json_decode( $pending_field_data, true );
		if ( ! $pending_fields || ! is_array( $pending_fields ) ) {
			wp_send_json_error( 'Pending field data is not valid' );
		}

		$index = 0;
		$pending_field_data = json_encode(
			array_reduce(
				$pending_fields,
				/**
				 * @param array $carry
				 * @param array $item
				 * @return array
				 */
				function ( $carry, $item ) use ( &$index ) {
					$item['key'] = $index++;
					$carry[]     = $item;
					return $carry;
				},
				array()
			)
		);

		if ( ! class_exists( 'FrmTestModeAutofill' ) ) {
			wp_send_json_error( 'AI add-on is not active' );
		}

		$response = FrmTestModeAutofill::get_response(
			array(
				'question' => $pending_field_data,
			)
		);
		if ( isset( $response['error'] ) ) {
			wp_send_json_error( $response['error'] );
		}

		$json                = $response['success'];
		$decoded_ai_response = json_decode( $json, true );

		if ( ! is_array( $decoded_ai_response ) ) {
			wp_send_json_error( 'API response was invalid ' . $json );
		}

		if ( count( $pending_fields ) !== count( $decoded_ai_response ) ) {
			// Retry once if there are fewer than 100 fields to autofill.
			if ( ! self::$ai_autofill_retry_count && count( $pending_fields ) < 100 ) {
				self::$ai_autofill_retry_count++;
				self::handle_fill_in_empty_form_fields();
			} else {
				wp_send_json_error( 'The AI failed to generate a proper response. This may happen when there are too many fields to autofill.' );
			}
		}

		$decoded_ai_response = self::maybe_fix_phone_numbers( $decoded_ai_response, $pending_fields );

		wp_send_json_success( $decoded_ai_response );
	}

	/**
	 * @param array $ai_response
	 * @param array $pending_fields
	 * @return array
	 */
	private static function maybe_fix_phone_numbers( $ai_response, $pending_fields ) {
		foreach ( $pending_fields as $index => $pending_field ) {
			$is_phone_field = 0 === strpos( $pending_field['context'], 'Phone' );
			if ( ! $is_phone_field ) {
				continue;
			}

			$none_pattern_substring = '(Regex Pattern: ' . substr( self::get_pattern_for_phone_field_none_format(), 1 );
			$is_none_format         = false !== strpos( $pending_field['context'], $none_pattern_substring );

			if ( ! $is_none_format ) {
				continue;
			}

			$ai_response[ $index ]['value'] = str_replace( ') ', ')', $ai_response[ $index ]['value'] );
		}
		return $ai_response;
	}

	/**
	 * Copied from FrmEntryValidate::default_phone_format because the function is private.
	 *
	 * @return string
	 */
	private static function get_pattern_for_phone_field_none_format() {
		return '^((\+\d{1,3}(-|.| )?\(?\d\)?(-| |.)?\d{1,5})|(\(?\d{2,6}\)?))(-|.| )?(\d{3,4})(-|.| )?(\d{4})(( x| ext)\d{1,5}){0,1}$';
	}

	/**
	 * @param array $denylist
	 *
	 * @return array
	 */
	private static function maybe_update_denylist( $denylist ) {
		if ( ! self::is_in_test_mode() ) {
			return $denylist;
		}

		$role = self::check_for_test_mode_role();
		if ( ! $role ) {
			return $denylist;
		}

		$role = self::get_role( $role );
		if ( ! $role ) {
			return $denylist;
		}

		$path = FrmAppHelper::plugin_path() . '/denylist/splorp-wp-comment.txt';
		foreach ( $denylist as $key => $value ) {
			if ( ! isset( $value['file'] ) || $path !== $value['file'] ) {
				continue;
			}

			$denylist[ $key ]['skip'] = ! empty( $role->capabilities['frm_create_entries'] );
			break;
		}

		return $denylist;
	}

	/**
	 * @param string $role_name
	 * @return WP_Role|null
	 */
	private static function get_role( $role_name ) {
		global $wp_roles;
		return isset( $wp_roles->roles[ $role_name ] ) ? $wp_roles->get_role( $role_name ) : null;
	}

	/**
	 * @param bool   $visible
	 * @param object $form
	 * @return bool
	 */
	private static function is_form_visible_to_user( $visible, $form ) {
		if ( ! self::is_in_test_mode() ) {
			return $visible;
		}

		$role = self::check_for_test_mode_role();
		if ( ! $role ) {
			return $visible;
		}

		if ( 'loggedout' === $role && $form->logged_in ) {
			self::add_test_container_to_login_msg_global();
			return false;
		}

		$visibility = $form->options['logged_in_role'] ?? false;
		if ( ! $visibility || in_array( '', $visibility, true ) ) {
			// If no role is defined, return true.
			return true;
		}

		$visibility = (array) $visibility;
		if ( in_array( 'loggedin', $visibility, true ) && 'loggedout' !== $role ) {
			return true;
		}

		if ( ! in_array( $role, $visibility, true ) ) {
			self::add_test_container_to_login_msg_global();
			return false;
		}

		return true;
	}

	/**
	 * @return void
	 */
	private static function add_test_container_to_login_msg_global() {
		global $frm_settings;
		$frm_settings->login_msg = FrmTestModeController::maybe_add_test_mode_container( $frm_settings->login_msg );
	}

	/**
	 * @param bool         $visible
	 * @param array|object $field
	 * @return bool
	 */
	private static function is_field_visible_to_user( $visible, $field ) {
		if ( ! self::is_in_test_mode() ) {
			return $visible;
		}

		$role = self::check_for_test_mode_role();
		if ( ! $role ) {
			return $visible;
		}

		$visibility = FrmField::get_option( $field, 'admin_only' );
		if ( ! $visibility ) {
			return $visible;
		}

		if ( ! is_array( $visibility ) ) {
			$visibility = (array) $visibility;
		}

		if ( in_array( '', $visibility, true ) ) {
			return $visible;
		}

		if ( in_array( 'loggedin', $visibility, true ) && 'loggedout' !== $role ) {
			return true;
		}

		return in_array( $role, $visibility, true );
	}

	/**
	 * @param array        $errors
	 * @param array|object $field
	 * @return array
	 */
	private static function remove_required_error_messages( $errors, $field ) {
		$blank_message = FrmFieldsHelper::get_error_msg( $field, 'blank' );
		return array_filter(
			$errors,
			/**
			 * @param string $error
			 * @return bool
			 */
			function ( $error ) use ( $blank_message ) {
				// Required name fields add an empty string, so remove those as well.
				return '' !== $error && $error !== $blank_message;
			}
		);
	}

	/**
	 * @param array $args
	 * @return array
	 */
	private static function modify_disable_required_fields_toggle_args( $args ) {
		$args['disabled'] = false;

		if ( ! empty( $_POST['frm_testmode'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$args['checked']  = ! empty( $_POST['frm_testmode']['disable_required_fields'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		} else {
			$args['checked'] = (bool) FrmAppHelper::simple_get( 'frm_testmode_disable_required_fields' );
		}

		return $args;
	}

	/**
	 * @param array $args
	 * @return array
	 */
	private static function modify_show_all_hidden_fields_toggle_args( $args ) {
		$args['disabled'] = false;
		$args['checked']  = ! empty( $_POST['frm_testmode']['show_all_hidden_fields'] ) || ! empty( $_GET['frm_testmode_show_all_hidden_fields'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return $args;
	}

	/**
	 * @since 1.0
	 *
	 * @return bool
	 */
	private static function is_test_mode_disabled_required_validation() {
		return current_user_can( 'frm_edit_forms' ) && FrmProFormState::get_from_request( 'testmode', false ) && ! empty( $_POST['frm_testmode']['disable_required_fields'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	/**
	 * @return void
	 */
	private static function on_test_mode_container() {
		wp_enqueue_script( 'frm_testing_mode_addon', FrmTestModeAppHelper::plugin_url() . '/js/testmode.js', array(), FrmTestModeAppHelper::plugin_version(), true );

		wp_localize_script(
			'frm_testing_mode_addon',
			'frmTestModeVars',
			array(
				'hasRequiredFields' => self::active_form_has_required_fields(),
				'hasPostedData'     => ! empty( $_POST ), // phpcs:ignore WordPress.Security.NonceVerification.Missing
			)
		);

		add_filter(
			'frm_test_mode_pagination_buttons',
			/**
			 * @return Closure
			 */
			function () {
				/**
				 * @return void
				 */
				return function () {
					$form_key = self::get_form_key_from_request();
					$form     = $form_key ? FrmForm::getOne( $form_key ) : null;
					if ( $form ) {
						self::add_pagination( $form );
					}
				};
			}
		);

		add_filter(
			'frm_testmode_start_over_button_attrs',
			/**
			 * @param array $atts
			 * @return array
			 */
			function ( $atts ) {
				$atts['class'] = str_replace( 'frm_noallow', 'frm_start_over', $atts['class'] );
				return $atts;
			}
		);
	}

	/**
	 * @return string|false
	 */
	private static function get_form_key_from_request() {
		$form_key = FrmAppHelper::simple_get( 'form' );
		if ( $form_key ) {
			return $form_key;
		}

		$form_key = FrmAppHelper::get_post_param( 'form', '', 'sanitize_text_field' );
		if ( $form_key ) {
			return $form_key;
		}

		$form_id = FrmAppHelper::get_post_param( 'form_id', '', 'sanitize_text_field' );
		if ( $form_id && is_numeric( $form_id ) ) {
			return FrmForm::get_key_by_id( $form_id );
		}

		return self::maybe_get_form_key_for_repeater_row();
	}

	private static function active_form_has_required_fields() {
		$form_key = self::get_form_key_from_request();
		$form     = $form_key ? FrmForm::getOne( $form_key ) : null;
		if ( ! $form ) {
			return false;
		}

		$fields = FrmField::get_all_for_form( $form->id, '', 'include', 'include' );
		foreach ( $fields as $field ) {
			if ( $field->required ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return false|string
	 */
	private static function maybe_get_form_key_for_repeater_row() {
		if ( ! in_array( current_action(), array( 'wp_ajax_frm_add_form_row', 'wp_ajax_nopriv_frm_add_form_row' ), true ) ) {
			return false;
		}

		$field_id = FrmAppHelper::get_post_param( 'field_id', '', 'absint' );
		if ( ! $field_id ) {
			return false;
		}

		$field = FrmField::getOne( $field_id );
		if ( ! $field ) {
			return false;
		}

		$form = FrmForm::getOne( $field->form_id );
		return $form->form_key;
	}

	/**
	 * @param object $form
	 * @return void
	 */
	private static function add_pagination( $form ) {
		$pages = self::get_data_for_pages( $form );

		include FrmTestModeAppHelper::path() . '/classes/views/pagination.php';
	}

	/**
	 * @since 1.0
	 *
	 * @param object $form
	 * @return array
	 */
	private static function get_data_for_pages( $form ) {
		$page_data = FrmProPageField::get_form_pages( $form );

		if ( array_key_exists( 'page_array', $page_data ) ) {
			return $page_data['page_array'];
		}

		// Always show at least one page.
		return array(
			1 => array(
				'class'         => '',
				'aria-disabled' => true,
				'data-page'     => 0,
				'data-field'    => 0,
			),
		);
	}

	/**
	 * @since 1.0.01
	 *
	 * @return void
	 */
	public static function admin_init() {
		self::include_updater();
	}

	/**
	 * @return void
	 */
	public static function include_updater() {
		if ( class_exists( 'FrmAddon' ) ) {
			include FrmTestModeAppHelper::path() . '/classes/models/FrmTestModeUpdate.php';
			FrmTestModeUpdate::load_hooks();
		}
	}

	/**
	 * Check from the page or from the state if we are in test mode.
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	private static function is_in_test_mode() {
		if ( ! current_user_can( 'frm_edit_forms' ) ) {
			return false;
		}

		if ( ! self::current_preview_form_supports_test_mode() ) {
			return false;
		}

		$is_preview_test_mode = 'frm_forms_preview' === FrmAppHelper::simple_get( 'action' ) && FrmAppHelper::simple_get( 'testmode' );
		if ( $is_preview_test_mode ) {
			return true;
		}

		return (bool) FrmProFormState::get_from_request( 'testmode', false );
	}

	/**
	 * Check if the form being previewed exists and is not conversational.
	 *
	 * @since 1.0
	 *
	 * @return bool True if the form being previewed exists and is not conversational.
	 */
	private static function current_preview_form_supports_test_mode() {
		$form_key = self::get_form_key_from_request();
		if ( ! $form_key ) {
			return false;
		}

		$form = FrmForm::getOne( $form_key );
		if ( ! $form ) {
			return false;
		}

		return empty( $form->options['chat'] );
	}

	/**
	 * @return string|false
	 */
	private static function check_for_test_mode_role() {
		if ( ! empty( $_POST['frm_testmode'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$role = sanitize_text_field( wp_unslash( $_POST['frm_testmode']['preview_role'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		} else {
			$role = FrmAppHelper::simple_get( 'frm_testmode_role' );
		}
		if ( $role && ! self::role_is_editable( $role ) ) {
			$role = false;
		}
		return $role ? $role : false;
	}

	/**
	 * @param string $role
	 * @return bool
	 */
	private static function role_is_editable( $role ) {
		if ( in_array( $role, array( 'loggedin', 'loggedout' ), true ) ) {
			return true;
		}

		if ( ! function_exists( 'get_editable_roles' ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}

		$roles = get_editable_roles();
		return isset( $roles[ $role ] );
	}

	/**
	 * @return array
	 */
	private static function get_enabled_form_action_ids() {
		if ( ! empty( $_POST['frm_testmode'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return self::check_post_data_for_enabled_form_action_ids();
		}
		return self::check_get_data_for_enabled_form_action_ids();
	}

	/**
	 * @return array
	 */
	private static function check_post_data_for_enabled_form_action_ids() {
		if ( empty( $_POST['frm_testmode']['enabled_form_actions'] ) || ! is_array( $_POST['frm_testmode']['enabled_form_actions'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing, SlevomatCodingStandard.Files.LineLength.LineTooLong
			return array();
		}

		return array_map( 'absint', $_POST['frm_testmode']['enabled_form_actions'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	/**
	 * @return array
	 */
	private static function check_get_data_for_enabled_form_action_ids() {
		// Check GET as the action IDs may be passed in the URL.
		$enabled_form_action_ids_csv = FrmAppHelper::simple_get( 'frm_testmode_enabled_form_actions' );
		if ( ! $enabled_form_action_ids_csv || '-1' === $enabled_form_action_ids_csv ) {
			return array();
		}

		return array_map( 'absint', explode( ',', $enabled_form_action_ids_csv ) );
	}
}
