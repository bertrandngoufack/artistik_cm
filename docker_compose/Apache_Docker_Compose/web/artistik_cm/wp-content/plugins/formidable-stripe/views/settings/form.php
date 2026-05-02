<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<table class="form-table">
	<tr class="form-field" valign="top">
		<td style="width:200px">
			<label><?php esc_html_e( 'Process One-time Payments', 'formidable-stripe' ); ?></label>
		</td>
		<td>
			<label for="frm_strp_process_before">
				<input type="radio" name="frm_strp_process" id="frm_strp_process_before" value="before" <?php checked( $settings->settings->process, 'before' ); ?> />
				<?php esc_html_e( 'Before entry is created.', 'formidable-stripe' ); ?>
			</label>
			<label for="frm_strp_process_after">
				<input type="radio" name="frm_strp_process" id="frm_strp_process_after" value="after" <?php checked( $settings->settings->process, 'after' ); ?> />
				<?php esc_html_e( 'After entry is created.', 'formidable-stripe' ); ?>
				<em><?php esc_html_e( 'Select if your site is using conditional logic on the payment form action or PHP customizations to set the price. If a form has multiple payment form actions, choose this option.', 'formidable-stripe' ); ?></em>
			</label>
		</td>
	</tr>
	<tr class="form-field" valign="top">
		<td>
			<label><?php esc_html_e( 'Test Mode', 'formidable' ); ?></label>
		</td>
		<td>
			<label for="frm_strp_test_mode">
				<input type="checkbox" name="frm_strp_test_mode" id="frm_strp_test_mode" value="1" <?php checked( $settings->settings->test_mode, 1 ); ?> />
				<?php esc_html_e( 'Use the Stripe test mode', 'formidable' ); ?>
			</label>
			<?php if ( ! is_ssl() ) { ?>
				<br/><em><?php esc_html_e( 'Your site is not using SSL. Before using Stripe to collect live payments, you will need to install an SSL certificate on your site.', 'formidable' ); ?></em>
			<?php } ?>
		</td>
	</tr>

	<?php FrmStrpConnectHelper::render_stripe_connect_settings_container(); ?>

	<?php if ( $keys ) { ?>
		<tr>
			<td colspan="2">
				<div class="frm_note_style">
					<?php
					FrmAppHelper::icon_by_class( 'frm_icon_font frm_alert_icon', array( 'style' => 'width: 14px; position: relative; bottom: 1px; margin-right: var(--gap-xs);' ) );

					if ( FrmStrpAppHelper::stripe_still_supports_api_keys() ) {
						esc_html_e( 'After June, Stripe will no longer allow keys.', 'formidable-stripe' );
					} else {
						esc_html_e( 'Stripe no longer supports API keys. Support for API keys will be fully removed in the next release of Formidable Stripe.', 'formidable-stripe' );
					}
					?>
				</div>
			</td>
		</tr>
		<?php foreach ( $keys as $key => $label ) { ?>
			<tr class="form-field" valign="top">
				<td>
					<label for="frm_strp_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
				</td>
				<td>
					<input type="text" name="frm_strp_<?php echo esc_attr( $key ); ?>" id="frm_strp_<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $settings->settings->{$key} ); ?>" class="frm_long_input" />
				</td>
			</tr>
		<?php } ?>
		<tr id="frm_strp_automatic_processing_settings_container">
			<td>
				<?php esc_html_e( 'Automatic Processing', 'formidable-stripe' ); ?>
			</td>
			<td>
				<?php esc_html_e( 'Stripe notifies your site of any recurring payments, refunds issued, and failed payments. In order to receive these notifications, you must add a new Webhook URL for your site in your Stripe Dashboard > Settings > Webhooks. The URL should be set to:', 'formidable-stripe' ); ?>
				<pre style="white-space:normal;">
					<?php echo esc_url_raw( admin_url( 'admin-ajax.php?action=frm_strp_event' ) ); ?>
				</pre>
			</td>
		</tr>
	<?php } ?>
</table>
