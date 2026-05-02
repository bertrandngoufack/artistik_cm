<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmCouponsRouteController {

	/**
	 * Handle coupons routes.
	 *
	 * @param bool   $route_handled
	 * @param string $action
	 *
	 * @return bool
	 */
	public static function handle_route( $route_handled, $action ) {
		if ( ! in_array( $action, array( 'new-coupon', 'edit-coupon' ), true ) ) {
			return $route_handled;
		}

		switch ( $action ) {
			case 'new-coupon':
				self::handle_new_coupon();
				break;
			case 'edit-coupon':
				self::handle_edit_coupon();
				break;
		}

		return true;
	}

	/**
	 * Handle new coupon route.
	 *
	 * @return void
	 */
	private static function handle_new_coupon() {
		FrmAppHelper::permission_check( FrmCouponsAppHelper::get_coupon_permission_required() );

		$is_new = true;
		$start  = ( new DateTime( 'now', wp_timezone() ) )->format( 'Y-m-d H:i' );

		include FrmCouponsAppHelper::path() . '/classes/views/edit.php';
	}

	/**
	 * Handle edit coupon route.
	 *
	 * @return void
	 */
	private static function handle_edit_coupon() {
		FrmAppHelper::permission_check( FrmCouponsAppHelper::get_coupon_permission_required() );

		$coupon_id = FrmAppHelper::simple_get( 'id' );
		if ( ! $coupon_id ) {
			self::show_error_modal( __( 'Missing coupon ID', 'formidable-coupons' ) );
			return;
		}

		$coupon = FrmCouponsAppHelper::get_coupon( $coupon_id );
		if ( ! $coupon ) {
			self::show_error_modal( __( 'This coupon does not exist.', 'formidable-coupons' ) );
			return;
		}

		$is_new      = false;
		$coupon_data = json_decode( $coupon->post_content );

		if ( is_object( $coupon_data ) ) {
			$start  = $coupon_data->start ?? '';
		} else {
			$start = '';
		}

		include FrmCouponsAppHelper::path() . '/classes/views/edit.php';
	}

	/**
	 * @since 1.0
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	private static function show_error_modal( $message ) {
		FrmAppHelper::include_svg();
		$error_args   = array(
			'title'       => __( 'Invalid Request', 'formidable-coupons' ),
			'body'        => $message,
			'cancel_text' => __( 'Cancel', 'formidable-coupons' ),
		);
		FrmAppController::show_error_modal( $error_args );
	}

	/**
	 * Handle destroy coupon route.
	 *
	 * @return void
	 */
	public static function handle_destroy_coupon() {
		FrmAppHelper::permission_check( FrmCouponsAppHelper::get_coupon_permission_required() );

		if ( ! check_admin_referer() ) {
			wp_die( esc_html__( 'Invalid nonce', 'formidable-coupons' ) );
		}

		$id = FrmAppHelper::simple_get( 'id' );
		if ( ! $id ) {
			wp_die( esc_html__( 'Missing coupon ID', 'formidable-coupons' ) );
		}

		$coupon = FrmCouponsAppHelper::get_coupon( $id );
		if ( ! $coupon ) {
			wp_die( esc_html__( 'The specified coupon does not exist.', 'formidable-coupons' ) );
		}

		wp_delete_post( $id );
		wp_safe_redirect( admin_url( 'admin.php?page=formidable-payments&action=coupons&message=deleted' ) );
		exit;
	}

	/**
	 * Handle route for saving a coupon from the admin edit page.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public static function handle_save_coupon() {
		FrmAppHelper::permission_check( FrmCouponsAppHelper::get_coupon_permission_required() );

		if ( ! check_admin_referer( 'frm_ajax', 'nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$coupon_id = FrmAppHelper::get_post_param( 'id', 0, 'absint' );
		if ( $coupon_id ) {
			$coupon = FrmCouponsAppHelper::get_coupon( $coupon_id );
			if ( ! $coupon ) {
				wp_send_json_error( 'Invalid coupon ID' );
			}
		} else {
			// Creating a new coupon
			$coupon            = new stdClass();
			$coupon->post_type = 'frm_coupon';
		}

		$previous_title     = $coupon->post_title ?? '';
		$coupon->post_title = trim( FrmAppHelper::get_post_param( 'name', '', 'sanitize_text_field' ) );

		if ( '' === $coupon->post_title ) {
			wp_send_json_error( __( 'A Coupon cannot be saved without a name.', 'formidable-coupons' ) );
		}

		if ( $coupon->post_title !== $previous_title && self::coupon_name_is_taken( $coupon->post_title ) ) {
			wp_send_json_error( __( 'A Coupon cannot be saved with a name that already exists.', 'formidable-coupons' ) );
		}

		$locked = $coupon_id && FrmCouponsAppHelper::get_coupon_uses_by_code( $coupon->post_excerpt ) > 0;

		if ( ! $locked ) {
			$previous_code_value  = $coupon->post_excerpt ?? '';
			$coupon->post_excerpt = trim( FrmAppHelper::get_post_param( 'code', '', 'sanitize_text_field' ) );

			if ( '' === ( $coupon->post_excerpt ?? '' ) ) {
				wp_send_json_error( __( 'A Coupon cannot be saved without a code.', 'formidable-coupons' ) );
			}

			// The code must be unique.
			// Check that it doesn't already exist.
			if ( $previous_code_value !== $coupon->post_excerpt && self::coupon_code_is_taken( $coupon->post_excerpt ) ) {
				wp_send_json_error( __( 'A Coupon cannot be saved with a code that already exists.', 'formidable-coupons' ) );
			}
		}

		$coupon_data = $coupon_id ? json_decode( $coupon->post_content ) : false;
		if ( ! is_object( $coupon_data ) ) {
			$coupon_data = new stdClass();
		}

		if ( ! $locked ) {
			$coupon_data->amount = FrmAppHelper::get_post_param( 'amount', '', 'sanitize_text_field' );
			$coupon_data->amount = str_replace( '$', '', $coupon_data->amount );
		}

		$amount_error = self::validate_coupon_amount( $coupon_data->amount );
		if ( is_string( $amount_error ) ) {
			wp_send_json_error( $amount_error );
		}

		$coupon_data->limit = FrmAppHelper::get_post_param( 'limit', '', 'sanitize_text_field' );
		if ( $coupon_data->limit && $coupon_data->limit < 0 ) {
			wp_send_json_error( __( 'A Coupon cannot be saved with a limit that is less than 0.', 'formidable-coupons' ) );
		}

		$coupon_data->start = FrmAppHelper::get_post_param( 'start_date', '', 'sanitize_text_field' );
		$coupon_data->end   = FrmAppHelper::get_post_param( 'end_date', '', 'sanitize_text_field' );

		$minimum_order_value_enabled = FrmAppHelper::get_post_param( 'minimum_order_value_enabled', 0, 'absint' );
		if ( $minimum_order_value_enabled ) {
			$coupon_data->minimum_order_value = FrmAppHelper::get_post_param( 'minimum_order_value', '', 'sanitize_text_field' );

			if ( $coupon_data->minimum_order_value && $coupon_data->minimum_order_value < 0 ) {
				wp_send_json_error( __( 'A Coupon cannot be saved with a minimum order value that is less than 0.', 'formidable-coupons' ) );
			}
		} else {
			$coupon_data->minimum_order_value = '';
		}

		$coupon->post_status = 'publish';

		if ( empty( $coupon->post_date_gmt ) || '0000-00-00 00:00:00' === $coupon->post_date_gmt ) {
			$coupon->post_date_gmt = gmdate( 'Y-m-d H:i:s' );
		}

		$allowed_forms = FrmAppHelper::get_post_param( 'allowed_forms', '', 'sanitize_text_field' );

		self::sync_allowed_forms_setting( $coupon_id, $allowed_forms );

		$coupon->post_content = json_encode( $coupon_data );

		if ( $coupon_id ) {
			wp_update_post( $coupon );

			// translators: %s is the coupon name.
			$message = sprintf( __( '%s was updated successfully.', 'formidable-coupons' ), $coupon->post_title );
		} else {
			$coupon_id = wp_insert_post( (array) $coupon );

			if ( ! $coupon_id ) {
				wp_send_json_error( 'Failed to create coupon' );
			}

			// translators: %s is the coupon name.
			$message = sprintf( __( '%s was created successfully.', 'formidable-coupons' ), $coupon->post_title );
		}

		wp_send_json_success(
			array(
				'message'    => $message,
				'statusHtml' => FrmCouponsAppHelper::render_coupon_status( $coupon_id, false ),
				'id'         => $coupon_id,
			)
		);
	}

	/**
	 * Check if a coupon name already exists.
	 *
	 * @param string $coupon_name
	 *
	 * @return bool
	 */
	private static function coupon_name_is_taken( $coupon_name ) {
		global $wpdb;
		return (bool) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = %s AND post_type = "frm_coupon"',
				$coupon_name
			)
		);
	}

	/**
	 * Check if a coupon code already exists.
	 *
	 * @param string $coupon_code
	 *
	 * @return bool
	 */
	private static function coupon_code_is_taken( $coupon_code ) {
		return (bool) FrmCouponsAppHelper::get_coupon_id_by_code( $coupon_code );
	}

	/**
	 * Validate the coupon amount.
	 *
	 * @param string $amount
	 *
	 * @return string|true
	 */
	private static function validate_coupon_amount( $amount ) {
		if ( ! $amount || ! is_numeric( str_replace( '%', '', $amount ) ) ) {
			return __( 'A Coupon cannot be saved without a valid numeric amount.', 'formidable-coupons' );
		}

		if ( is_numeric( $amount ) && $amount < 0 ) {
			return __( 'A Coupon cannot be saved with a negative amount.', 'formidable-coupons' );
		}

		$is_percent_discount = '%' === substr( $amount, -1 );
		if ( ! $is_percent_discount ) {
			return true;
		}

		$percent_discount = floatval( substr( $amount, 0, -1 ) );
		if ( $percent_discount >= 1 && $percent_discount <= 100 ) {
			return true;
		}

		return __( 'A Coupon cannot be saved with a percentage discount less than 1 or greater than 100.', 'formidable-coupons' );
	}

	/**
	 * @param int          $coupon_id
	 * @param array|string $allowed_forms
	 *
	 * @return void
	 */
	private static function sync_allowed_forms_setting( $coupon_id, $allowed_forms ) {
		if ( ! is_array( $allowed_forms ) ) {
			$allowed_forms = array();
		}

		$previously_allowed_form_ids = FrmCouponsAppHelper::get_allowed_form_ids( $coupon_id );
		$allowed_form_ids            = array_keys( $allowed_forms );

		if ( $previously_allowed_form_ids === $allowed_form_ids ) {
			return;
		}

		$newly_assigned_form_ids = array_diff( $allowed_form_ids, $previously_allowed_form_ids );
		$removed_form_ids        = array_diff( $previously_allowed_form_ids, $allowed_form_ids );

		foreach ( $newly_assigned_form_ids as $form_id ) {
			self::assign_coupon_to_form( $coupon_id, $form_id );
		}

		foreach ( $removed_form_ids as $form_id ) {
			self::remove_coupon_from_form( $coupon_id, $form_id );
		}
	}

	/**
	 * @param int $coupon_id
	 * @param int $form_id
	 *
	 * @return void
	 */
	private static function assign_coupon_to_form( $coupon_id, $form_id ) {
		$coupon_field = FrmProFormsHelper::has_field( 'coupon', $form_id );
		if ( ! $coupon_field ) {
			return;
		}

		self::maybe_fix_coupon_field( $coupon_field );

		$coupon_field->field_options['allowed_coupons'][] = (string) $coupon_id;

		self::save_coupon_field_options( $coupon_field );
	}

	/**
	 * @param int $coupon_id
	 * @param int $form_id
	 *
	 * @return void
	 */
	private static function remove_coupon_from_form( $coupon_id, $form_id ) {
		$coupon_field = FrmProFormsHelper::has_field( 'coupon', $form_id );
		if ( ! $coupon_field ) {
			return;
		}

		self::maybe_fix_coupon_field( $coupon_field );

		$coupon_field->field_options['allowed_coupons'] = array_diff(
			$coupon_field->field_options['allowed_coupons'],
			array( (string) $coupon_id )
		);

		self::save_coupon_field_options( $coupon_field );
	}

	/**
	 * @param stdClass $coupon_field
	 *
	 * @return void
	 */
	private static function maybe_fix_coupon_field( $coupon_field ) {
		if ( ! is_array( $coupon_field->field_options ) ) {
			$coupon_field->field_options = array();
		}

		if ( ! is_array( $coupon_field->field_options['allowed_coupons'] ?? '' ) ) {
			$coupon_field->field_options['allowed_coupons'] = array();
		}
	}

	/**
	 * @since 1.0
	 *
	 * @param stdClass $coupon_field
	 *
	 * @return void
	 */
	private static function save_coupon_field_options( $coupon_field ) {
		global $wpdb;
		$wpdb->update(
			$wpdb->prefix . 'frm_fields',
			array(
				'field_options' => serialize( $coupon_field->field_options ),
			),
			array(
				'id' => $coupon_field->id,
			)
		);

		wp_cache_delete( $coupon_field->id, 'frm_field' );
		FrmField::delete_form_transient( $coupon_field->form_id );
	}

	/**
	 * @since 1.0
	 *
	 * @param bool $show
	 *
	 * @return bool
	 */
	public static function maybe_hide_floating_links( $show ) {
		$page   = FrmAppHelper::simple_get( 'page' );
		$action = FrmAppHelper::simple_get( 'action' );

		if ( 'formidable-payments' === $page && in_array( $action, array( 'edit-coupon', 'new-coupon' ), true ) ) {
			$show = false;
		}

		return $show;
	}
}
