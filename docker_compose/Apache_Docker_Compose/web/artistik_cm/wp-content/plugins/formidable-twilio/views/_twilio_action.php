<?php
if ( empty( $frm_twlo_settings->settings['account_sid'] ) || empty( $frm_twlo_settings->settings['auth_token'] ) ) {
	// Save the API info if not set yet. Include the settings just in case they have already been set up.
	?>
	<div class="frmcenter">
		<p class="frm_error_style">
			<?php esc_html_e( 'The Twilio API has not been set up yet.', 'frmtwlo' ); ?>
		</p>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-settings&t=twilio_settings' ) ); ?>" class="button button-primary frm-button-primary" target="_blank">
			<?php esc_html_e( 'Set up Twilo API', 'frmtwlo' ); ?>
		</a>
	</div>
	<input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'to' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['to'] ); ?>" />
	<input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'from' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['from'] ); ?>" />
	<input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'message' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['message'] ); ?>" />
	<?php
	return;
}
?>

<p class="frm_has_shortcodes">
	<label for="<?php echo esc_attr( $this->get_field_id( 'to' ) ); ?>">
		<?php esc_html_e( 'To', 'frmtwlo' ); ?>
	</label>
	<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'to' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['to'] ); ?>" class="frm_not_email_to frm_email_blur large-text" id="<?php echo esc_attr( $this->get_field_id( 'to' ) ); ?>" />
</p>
<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'from' ) ); ?>">
		<?php esc_html_e( 'From', 'frmtwlo' ); ?>
	</label>
	<?php if ( is_array( $phone_numbers ) ) { ?>
		<select name="<?php echo esc_attr( $this->get_field_name( 'from' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'from' ) ); ?>" onchange="frm_show_div('<?php echo esc_attr( $this->get_field_id( 'custom' ) ); ?>_container',this.value,'custom','#')">
			<option value=""></option>
			<?php foreach ( $phone_numbers as $phone_number => $friendly_phone_number ) { ?>
				<option value="<?php echo esc_attr( $phone_number ); ?>" <?php $selected = FrmTwloAppController::selected_phone_number( $form_action->post_content['from'], $phone_number ); ?>>
					<?php echo esc_html( $friendly_phone_number ); ?>
				</option>
				<?php $is_selected = $is_selected || $selected; ?>
			<?php } ?>
			<option value="custom" <?php selected( ( $has_from && ! $is_selected ) || $use_custom, true ); ?>>
				<?php esc_html_e( 'Use a different Twilio number', 'frmtwlo' ); ?>
			</option>
		</select>
	<?php } else { ?>
		<span class="frm_error_style">
			<?php echo esc_html( $phone_numbers ); ?>
		</span>
		<input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'from' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'from' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['from'] ); ?>" />
	<?php } ?>
</p>
<p id="<?php echo esc_attr( $this->get_field_id( 'custom' ) ); ?>_container" class="<?php echo ( ( $has_from && ! $is_selected ) || $use_custom ) ? '' : 'frm_hidden'; ?>">
	<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'custom' ) ); ?>" value="<?php echo esc_attr( FrmTwloAppController::get_custom_number( $form_action, ! $is_selected ) ); ?>" class="frm_not_email_to frm_email_blur large-text" id="<?php echo esc_attr( $this->get_field_id( 'custom' ) ); ?>" />
	<span class="howto">
		<?php esc_html_e( 'Warning: only add a custom number if you know what you are doing. Only Twilio numbers will send messages.', 'frmtwlo' ); ?>
	</span>
</p>
<p class="frm_has_shortcodes">
	<label for="<?php echo esc_attr( $this->get_field_id( 'message' ) ); ?>">
		<?php esc_html_e( 'Message', 'frmtwlo' ); ?>
	</label>
	<textarea name="<?php echo esc_attr( $this->get_field_name( 'message' ) ); ?>" class="frm_not_email_message frm_long_input" id="<?php echo esc_attr( $this->get_field_id( 'message' ) ); ?>" cols="50" rows="5"><?php echo FrmAppHelper::esc_textarea( $form_action->post_content['message'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></textarea>
</p>
