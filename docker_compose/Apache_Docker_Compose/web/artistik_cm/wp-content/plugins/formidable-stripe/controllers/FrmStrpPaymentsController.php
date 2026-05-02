<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
class FrmStrpPaymentsController {

	/**
	 * Get the receipt link for a Stripe payment.
	 *
	 * @param string $receipt
	 * @return string
	 */
	public static function get_receipt_link( $receipt ) {
		$url = 'https://dashboard.stripe.com/';

		if ( 0 === strpos( $receipt, 'sub_' ) ) {
			$url .= 'subscriptions/';
		} elseif ( 0 === strpos( $receipt, 'seti_' ) ) {
			$url .= 'setup_intents/';
		} else {
			$url .= 'payments/';
		}

		$url .= $receipt;

		$link  = '<a href="' . esc_attr( $url ) . '" target="_blank">';
		$link .= esc_html( $receipt );
		$link .= '</a>';
		return $link;
	}

	/**
	 * Get the link for deleting a Stripe credit card.
	 *
	 * @param string $card_id
	 * @return string
	 */
	public static function get_delete_card_link( $card_id ) {
		$link  = '<button class="frm-stripe-delete-card" data-cid="' . esc_attr( $card_id ) . '">';
		$link .= esc_html__( 'Delete card', 'formidable-stripe' );
		$link .= '</button>';
		return $link;
	}

	/**
	 * Call Stripe to delete a credit card.
	 *
	 * @param array $args
	 * @return mixed
	 */
	public static function delete_card( $args ) {
		return FrmStrpAppHelper::call_stripe_helper_class( 'delete_card', $args['id'] );
	}

	/**
	 * Get the HTML for managing credit cards.
	 *
	 * @return string
	 */
	public static function manage_cards() {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return '';
		}

		FrmStrpActionsController::load_scripts( array() );

		$cards = FrmStrpAppHelper::call_stripe_helper_class( 'get_cards', $user_id );

		ob_start();
		include FrmStrpAppHelper::plugin_path() . '/views/payments/manage-cards.php';
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	 * If the payment has been authorized, include a link to capture.
	 *
	 * @since 1.15
	 *
	 * @param object $payment
	 * @return void
	 */
	public static function show_capture_link( $payment ) {
		include FrmStrpAppHelper::plugin_path() . '/views/payments/sidebar_actions.php';
	}

	/**
	 * Echo the ajax link to capture a payment.
	 *
	 * @since 1.15
	 *
	 * @param object $payment
	 * @return void
	 */
	public static function capture_link( $payment ) {
		$link  = admin_url( 'admin-ajax.php?action=frm_trans_capture&payment_id=' . $payment->id . '&nonce=' . wp_create_nonce( 'frm_trans_ajax' ) );
		$link  = '<a href="' . esc_url( $link ) . '" class="frm_trans_ajax_link">';
		$link .= esc_html__( 'Capture now', 'formidable-stripe' );
		$link .= '</a>';
		echo FrmAppHelper::kses( $link, array( 'a' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Process the ajax request to capture a charge.
	 *
	 * @since 1.15
	 *
	 * @return void
	 */
	public static function capture_charge() {
		FrmAppHelper::permission_check( 'frm_edit_entries' );
		check_ajax_referer( 'frm_trans_ajax', 'nonce' );

		$payment_id = FrmAppHelper::get_param( 'payment_id', '', 'get', 'absint' );
		if ( $payment_id ) {
			$frm_payment = new FrmTransPayment();
			$payment     = $frm_payment->get_one( $payment_id );

			$captured = FrmStrpAppHelper::call_stripe_helper_class( 'capture_charge', $payment->receipt_id );
			if ( $captured ) {
				FrmTransPaymentsController::change_payment_status( $payment, 'complete' );
				$message = __( 'Captured', 'formidable-stripe' );
			} else {
				$message = __( 'Failed', 'formidable' );
			}
		} else {
			$message = __( 'Oops! No payment was selected for the charge.', 'formidable-stripe' );
		}

		echo esc_html( $message );
		wp_die();
	}
}
