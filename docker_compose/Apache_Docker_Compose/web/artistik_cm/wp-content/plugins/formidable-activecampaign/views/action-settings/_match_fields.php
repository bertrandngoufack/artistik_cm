<p class="frm6">
	<label><?php esc_html_e( 'Double Opt-in', 'frmactivecampaign' ); ?></label>
	<select name="<?php echo esc_attr( $action_control->get_field_name( 'ac_form' ) ); ?>">
		<option value=""><?php esc_html_e( 'Single Opt-in', 'frmactivecampaign' ); ?></option>
		<?php
		if ( ! empty( $ac_forms ) && is_object( $ac_forms ) ) {
			foreach ( $ac_forms as $ac_form ) {
				if ( ! is_object( $ac_form ) || ! $ac_form->sendoptin ) {
					// Only show forms with double option here.
					continue;
				}

				$selected = ( isset( $list_options['ac_form'] ) && $list_options['ac_form'] == $ac_form->id ) ? ' selected="selected"' : '';
				?>
				<option value="<?php echo esc_attr( $ac_form->id ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( FrmAppHelper::truncate( $ac_form->name, 40 ) ); ?></option>
				<?php
			}
		}
		?>
	</select>
</p>

<p class="frm6 frm_first">
	<label>
		<?php esc_html_e( 'Email', 'frmactivecampaign' ); ?>
		<span class="frm_required">*</span>
	</label>
	<select name="<?php echo esc_attr( $action_control->get_field_name( 'fields' ) ); ?>[email]">
		<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
		<?php
		foreach ( $form_fields as $form_field ) :
			$selected = ( isset( $list_options['fields']['email'] ) && $list_options['fields']['email'] == $form_field->id ) ? ' selected="selected"' : '';
			?>
			<option value="<?php echo esc_attr( $form_field->id ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( FrmAppHelper::truncate( $form_field->name, 40 ) ); ?></option>
		<?php endforeach; ?>
	</select>
</p>

<p class="frm6">
	<label><?php esc_html_e( 'First Name', 'frmactivecampaign' ); ?></label>
	<select name="<?php echo esc_attr( $action_control->get_field_name( 'fields' ) ); ?>[first_name]">
		<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
		<?php
		foreach ( $form_fields as $form_field ) :
			$selected = ( isset( $list_options['fields']['first_name'] ) && $list_options['fields']['first_name'] == $form_field->id ) ? ' selected="selected"' : '';
			?>
			<option value="<?php echo esc_attr( $form_field->id ); ?>" <?php echo esc_attr( $selected ); ?>>
				<?php
				echo esc_html( FrmAppHelper::truncate( $form_field->name, 50 ) );
				if ( 'name' === $form_field->type ) {
					echo ' (' . esc_html__( 'First', 'formidable' ) . ')';
				}
				?>
			</option>
		<?php endforeach; ?>
	</select>
</p>
<p class="frm6">
	<label><?php esc_html_e( 'Last Name', 'frmactivecampaign' ); ?></label>
	<select name="<?php echo esc_attr( $action_control->get_field_name( 'fields' ) ); ?>[last_name]">
		<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
		<?php
		foreach ( $form_fields as $form_field ) :
			$selected = ( isset( $list_options['fields']['last_name'] ) && $list_options['fields']['last_name'] == $form_field->id ) ? ' selected="selected"' : '';
			?>
			<option value="<?php echo esc_attr( $form_field->id ); ?>" <?php echo esc_attr( $selected ); ?>>
				<?php
				echo esc_html( FrmAppHelper::truncate( $form_field->name, 50 ) );
				if ( 'name' === $form_field->type ) {
					echo ' (' . esc_html__( 'Last', 'formidable' ) . ')';
				}
				?>
			</option>
		<?php endforeach; ?>
	</select>
</p>
<p class="frm6">
	<label><?php esc_html_e( 'Phone', 'frmactivecampaign' ); ?></label>
	<select name="<?php echo esc_attr( $action_control->get_field_name( 'fields' ) ); ?>[phone]">
		<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
		<?php
		foreach ( $form_fields as $form_field ) :
			$selected = ( isset( $list_options['fields']['phone'] ) && $list_options['fields']['phone'] == $form_field->id ) ? ' selected="selected"' : '';
			?>
			<option value="<?php echo esc_attr( $form_field->id ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( FrmAppHelper::truncate( $form_field->name, 40 ) ); ?></option>
		<?php endforeach; ?>
	</select>
</p>
<p class="frm6">
	<label><?php esc_html_e( 'Tags', 'frmactivecampaign' ); ?></label>
	<select name="<?php echo esc_attr( $action_control->get_field_name( 'fields' ) ); ?>[tags]">
		<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
		<?php
		foreach ( $form_fields as $form_field ) :
			$selected = ( isset( $list_options['fields']['tags'] ) && $list_options['fields']['tags'] == $form_field->id ) ? ' selected="selected"' : '';
			?>
			<option value="<?php echo esc_attr( $form_field->id ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( FrmAppHelper::truncate( $form_field->name, 40 ) ); ?></option>
		<?php endforeach; ?>
	</select>
</p>

<?php

// Custom fields.
if ( is_object( $list_fields ) && ! is_wp_error( $list_fields ) ) :
	if ( ! empty( $list_fields->result_code ) ) :
		// Returned empty.
		if ( '1' == $list_fields->result_code ) :
			$excluded_keys = array( 'result_code', 'result_message', 'result_output' );
			foreach ( $list_fields as $key => $list_field ) :
				if ( in_array( $key, $excluded_keys ) ) {
					continue;
				}
				?>
				<p class="frm6">
					<label><?php echo esc_html( ucfirst( $list_field->title ) ); ?> </label>
					<select name="<?php echo esc_attr( $action_control->get_field_name( 'fields' ) ); ?>[<?php echo esc_attr( $list_field->id ); ?>]">
						<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
						<?php
						foreach ( $form_fields as $form_field ) :
							$selected = ( isset( $list_options['fields'][ $list_field->id ] ) && $list_options['fields'][ $list_field->id ] == $form_field->id ) ? ' selected="selected"' : '';
							?>
							<option value="<?php echo esc_attr( $form_field->id ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( FrmAppHelper::truncate( $form_field->name, 40 ) ); ?></option>
							<?php
						endforeach;
						?>
					</select>
				</p>
				<?php
			endforeach;
		endif;
	endif;
endif;

$send_ip = ! empty( $list_options['send_ip_address'] ) ? $list_options['send_ip_address'] : '';
$instant_autoresponsder = ! empty( $list_options['instant_autoresponsder'] ) ? $list_options['instant_autoresponsder'] : '';
?>
<p class="frm6">
	<label><?php esc_html_e( 'Send IP Address', 'frmactivecampaign' ); ?></label>
	<select name="<?php echo esc_attr( $action_control->get_field_name( 'send_ip_address' ) ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'send_ip_address' ) ); ?>">
		<option value="no"><?php esc_html_e( 'No', 'frmactivecampaign' ); ?></option>
		<option value="yes" <?php selected( $send_ip, 'yes' ); ?>><?php esc_html_e( 'Yes', 'frmactivecampaign' ); ?></option>
	</select>
</p>
<p class="frm6">
	<label><?php esc_html_e( 'Instant Responder?', 'frmactivecampaign' ); ?></label>
	<select name="<?php echo esc_attr( $action_control->get_field_name( 'instant_autoresponsder' ) ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'instant_autoresponsder' ) ); ?>">
		<option value="no"><?php esc_html_e( 'No', 'frmactivecampaign' ); ?></option>
		<option value="yes" <?php selected( $instant_autoresponsder, 'yes' ); ?>><?php esc_html_e( 'Yes', 'frmactivecampaign' ); ?></option>
	</select>
</p>
