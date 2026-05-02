<p>
	<label for="frm_twlo_account_sid" class="frm_left_label">
		<?php esc_html_e( 'Account SID', 'frmtwlo' ); ?>
	</label>
	<input type="text" name="frm_twlo_account_sid" id="frm_twlo_account_sid" value="<?php echo esc_attr( $frm_twlo_settings->settings['account_sid'] ); ?>" class="frm_with_left_label" />
</p>
<p>
	<label for="frm_twlo_auth_token" class="frm_left_label">
		<?php esc_html_e( 'Auth Token', 'frmtwlo' ); ?>
	</label>
	<input type="text" name="frm_twlo_auth_token" id="frm_twlo_auth_token" value="<?php echo esc_attr( $frm_twlo_settings->settings['auth_token'] ); ?>" class="frm_with_left_label" />
</p>

<p>
	<a href="https://www.twilio.com/console/project/settings" target="_blank" rel="noopener">
		<?php esc_html_e( 'Get API Credentials from Twilio.com', 'frmtwlo' ); ?>
	</a>
</p>
