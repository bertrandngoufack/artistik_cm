<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<input type="hidden" name="<?php echo esc_attr( $action_control->get_field_name( 'plan_id' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['plan_id'] ); ?>" />

<?php
// v5.5.3 is required for the new setting to work. Otherwise the form will just submit and the confirmPayment call won't ever get triggered.
$show_toggle = is_callable( 'FrmProHtmlHelper::toggle' ) && version_compare( FrmAppHelper::plugin_version(), '5.5.3', '>=' );

if ( $show_toggle ) {
	$toggle_method = method_exists( 'FrmProHtmlHelper', 'admin_toggle' ) ? 'admin_toggle' : 'toggle';
	$toggle_id     = $action_control->get_field_id( 'stripe_link' );
	$toggle_name   = $action_control->get_field_name( 'stripe_link' );

	$div_classes = 'show_stripe';
	if ( ! in_array( 'stripe', (array) $form_action->post_content['gateway'], true ) ) {
		$div_classes .= ' frm_hidden';
	}
	?>
	<div class="<?php echo esc_attr( $div_classes ); ?>">
		<?php
		call_user_func(
			array( 'FrmProHtmlHelper', $toggle_method ),
			$toggle_id,
			$toggle_name,
			array(
				'div_class' => 'with_frm_style frm_toggle',
				'checked'   => ! empty( $form_action->post_content['stripe_link'] ),
				'echo'      => true,
			)
		);
		?>
		<label for="<?php echo esc_attr( $toggle_id ); ?>" id="<?php echo esc_attr( $toggle_id ); ?>_label">
			<?php
			$stripe_link_documentation_url = 'https://stripe.com/docs/payments/link/accept-a-payment';
			printf(
				// translators: %1$s: Anchor open tag, %2$s: Anchor close tag.
				esc_html__( 'Enable %1$sStripe link%2$s and additional payment methods.', 'formidable-stripe' ),
				'<a href="' . esc_url( $stripe_link_documentation_url ) . '" target="_blank">',
				'</a>'
			);
			?>
		</label>
		<div class="frm_grid_container">
			<p class="frm6 <?php echo esc_attr( empty( $form_action->post_content['stripe_link'] ) ? 'frm_hidden' : '' ); ?>">
				<label for="<?php echo esc_attr( $action_control->get_field_id( 'layout' ) ); ?>">
					<?php esc_html_e( 'Layout', 'formidable' ); ?>
				</label>
				<select name="<?php echo esc_attr( $action_control->get_field_name( 'layout' ) ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'layout' ) ); ?>">
					<option value="">
						<?php esc_html_e( 'Tabs', 'formidable' ); ?>
					</option>
					<option value="accordion" <?php isset( $form_action->post_content['layout'] ) ? selected( $form_action->post_content['layout'], 'accordion' ) : ''; ?>>
						<?php esc_html_e( 'Accordion', 'formidable' ); ?>
					</option>
				</select>
			</p>
		</div>
	</div>
	<?php
}
?>
<script>
document.addEventListener( 'click', function( event ) {
	if ( event.target.id.startsWith( 'stripe_link_' ) ) {
		const actionID = event.target.id.replace( 'stripe_link_', '' );
		document.getElementById( `layout_${ actionID }` ).closest( 'p' ).classList.toggle( 'frm_hidden' );
	}
});
</script>
