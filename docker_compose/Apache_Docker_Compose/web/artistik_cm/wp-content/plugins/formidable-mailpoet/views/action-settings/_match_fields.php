<div class="frm_mailpoet_fields <?php echo esc_attr( $action_control->get_field_id( 'frm_mailpoet_fields' ) ); ?>">
	<?php foreach ( $list_fields as $key => $list_field ) { ?>
			<p><label class="frm_left_label"><?php echo esc_html( ucfirst( $list_field['name'] ) ); ?></label>
				<select name="<?php echo esc_attr( $action_control->get_field_name( 'fields' ) ); ?>[<?php echo esc_attr( $list_field['id'] ); ?>]">
				<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
					<?php
					foreach ( $form_fields as $form_field ) :
						$selected = ( isset( $list_options['fields'][ $list_field['id'] ] ) && $list_options['fields'][ $list_field['id'] ] == $form_field->id ) ? ' selected="selected"' : '';
						?>
						<option value="<?php echo esc_attr( $form_field->id ); ?>" <?php echo esc_attr( $selected ); ?> ><?php echo esc_html( FrmAppHelper::truncate( $form_field->name, 40 ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
	<?php } ?>
	<?php
	$send_confirmation_email = ! empty( $list_options['send_confirmation_email'] ) ? $list_options['send_confirmation_email'] : '';
	$schedule_welcome_email = ! empty( $list_options['schedule_welcome_email'] ) ? $list_options['schedule_welcome_email'] : '';
	?>
	<p>
		<label class="frm_left_label"><?php esc_html_e( 'Send confirmation email', 'frmmailpoet' ); ?></label>
		<select name="<?php echo esc_attr( $action_control->get_field_name( 'send_confirmation_email' ) ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( '$send_confirmation_email' ) ); ?>">
			<option value="no">
				<?php esc_html_e( 'No', 'frmmailpoet' ); ?>
			</option>
			<option value="yes" <?php selected( $send_confirmation_email, 'yes' ); ?>>
				<?php esc_html_e( 'Yes', 'frmmailpoet' ); ?>
			</option>
		</select>
	</p>
	<?php if ( is_plugin_active( 'mailpoet-premium/mailpoet-premium.php' ) ) { ?>
	<p>
		<label class="frm_left_label"><?php esc_html_e( 'Schedule welcome email', 'frmmailpoet' ); ?></label>
		<select name="<?php echo esc_attr( $action_control->get_field_name( 'schedule_welcome_email' ) ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'schedule_welcome_email' ) ); ?>">
			<option value="no">
				<?php esc_html_e( 'No', 'frmmailpoet' ); ?>
			</option>
			<option value="yes" <?php selected( $schedule_welcome_email, 'yes' ); ?>>
				<?php esc_html_e( 'Yes', 'frmmailpoet' ); ?>
			</option>
		</select>
	</p>
	<?php } else { ?>
		<input type="hidden" name="<?php echo esc_attr( $action_control->get_field_name( 'schedule_welcome_email' ) ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'schedule_welcome_email' ) ); ?>" value="no" />
	<?php } ?>
</div>
