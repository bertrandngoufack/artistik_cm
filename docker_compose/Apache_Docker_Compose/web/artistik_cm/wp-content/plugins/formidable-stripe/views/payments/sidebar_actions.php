<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
if ( $payment->status === 'authorized' && FrmStrpAppHelper::call_stripe_helper_class( 'can_by_captured', $payment->receipt_id ) ) {
	FrmAppHelper::include_svg();
	?>
	<div class="misc-pub-section">
		<span><?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_credit_card_alt_icon', array( 'style' => 'color:var(--grey);vertical-align:bottom;width:22.5px;' ) ); ?></span>
		<?php esc_html_e( 'Authorized:', 'formidable-stripe' ); ?>
		<?php FrmStrpPaymentsController::capture_link( $payment ); ?>
	</div>
<?php } ?>
