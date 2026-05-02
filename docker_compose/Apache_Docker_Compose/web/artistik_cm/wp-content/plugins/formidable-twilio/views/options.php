<?php _deprecated_file( __FILE__, '1.09' ); ?>
<div class="twilio_notification">
<h4>Twilio</h4>
<p><label for="twilio_<?php echo esc_attr( $email_key ); ?>">
	<input type="checkbox" value="1" name="notification[<?php echo esc_attr( $email_key ); ?>][twilio]" id="twilio_<?php echo esc_attr( $email_key ); ?>" class="frm_twilio_notification" <?php checked( $notification['twilio'], 1 ); ?>/>
	<?php esc_html_e( 'Send this notification as an SMS message via Twilio', 'frmtwlo' ); ?>
</label></p>
<p class="frm_indent_opt hide_twilio" <?php echo $notification['twilio'] ? '' : 'style="display:none;"'; ?>>
	<label><?php esc_html_e( 'Sending Number', 'frmtwlo' ); ?></label>
	<input type="text" name="notification[<?php echo esc_attr( $email_key ); ?>][twfrom]" value="<?php esc_attr( $notification['twfrom'] ); ?>" />
	<span class="howto"><?php esc_html_e( 'Add your phone number field(s) to the email recipients box above', 'frmtwlo' ); ?></span>
</p>

</div>
