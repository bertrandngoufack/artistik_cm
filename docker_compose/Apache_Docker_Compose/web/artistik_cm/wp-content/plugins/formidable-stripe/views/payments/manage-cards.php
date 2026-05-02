<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<table>
	<thead>
		<tr>
			<th><?php esc_html_e( 'Card ending in', 'formidable-stripe' ); ?></th>
			<th><?php esc_html_e( 'Expires', 'formidable-stripe' ); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ( $cards as $card ) { ?>
		<tr>
			<td><?php echo esc_html( $card['card']->last4 ); ?></td>
			<td><?php echo esc_html( $card['card']->exp_month . '/' . $card['card']->exp_year ); ?></td>
			<td><?php echo FrmStrpPaymentsController::get_delete_card_link( $card['card']->id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
