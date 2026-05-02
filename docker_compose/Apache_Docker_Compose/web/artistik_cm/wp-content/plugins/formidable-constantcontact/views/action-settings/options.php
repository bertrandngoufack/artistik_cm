
<div class="frm_grid_container">
	<p class="frm6 frm_form_field">
		<label>
			<?php esc_html_e( 'List', 'formidable-ctct' ); ?>
			<span class="frm_required">*</span>
		</label>

		<select name="<?php echo esc_attr( $action_control->get_field_name( 'list_id' ) ); ?>">
			<option value="">
				&mdash; <?php esc_html_e( 'Select' ); ?>  &mdash;
			</option>
			<?php foreach ( $lists as $list ) : ?>
				<option value="<?php echo esc_attr( $list['id'] ); ?>" <?php selected( $list_id, $list['id'] ); ?>>
					<?php echo esc_html( FrmAppHelper::truncate( $list['label'], 40 ) ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>
	<p class="frm6 frm_form_field">
		<label>&nbsp;</label>
		<a href="javascript:void(0)" id="clrcache-constantcontact" class="button-secondary frm-button-secondary">
			<?php esc_html_e( 'Clear Cache', 'formidable-ctct' ); ?>
		</a>
		<span style="float:none" class="clrcache-constantcontact-spinner spinner"></span>
	</p>

		<?php
		foreach ( $fields as $field_key => $field_label ) :
			$allowed_types = array();
			if ( is_array( $field_label ) ) {
				$allowed_types = $field_label['type'];
				$field_label   = $field_label['name'];
			}
			if ( ! isset( $list_options['fields'][ $field_key ] ) ) {
				$list_options['fields'][ $field_key ] = '';
			}
			?>
			<p class="frm6 frm_form_field frm_ctct_fields <?php echo esc_attr( $action_control->get_field_id( 'frm_ctct_fields' ) ); ?>">
					<label for="<?php echo esc_attr( $action_control->get_field_id( 'fields-' . $field_key ) ); ?>">
						<?php echo esc_html( $field_label ); ?>
						<?php if ( 'email' === $field_key ) : ?>
							<span class="frm_required">*</span>
						<?php endif; ?>
					</label>
					<select name="<?php echo esc_attr( $action_control->get_field_name( 'fields' ) ); ?>[<?php echo esc_attr( $field_key ); ?>]" id="<?php echo esc_attr( $action_control->get_field_id( 'fields-' . $field_key ) ); ?>">
						<option value="">&mdash; <?php esc_html_e( 'Select' ); ?> &mdash;</option>
						<?php
						$has_option = false;
						foreach ( $form_fields as $form_field ) :
							if ( ! empty( $allowed_types ) && ! in_array( $form_field->type, $allowed_types ) ) {
								continue;
							}
							$has_option = true;
							?>
							<option value="<?php echo esc_attr( $form_field->id ); ?>" <?php selected( $list_options['fields'][ $field_key ], $form_field->id ); ?> >
								<?php echo esc_html( FrmAppHelper::truncate( $form_field->name, 40 ) ); ?>
							</option>
						<?php endforeach; ?>
						<?php if ( ! $has_option ) { ?>
							<option value=""><?php esc_html_e( 'No matching fields available', 'formidable-ctct' ); ?></option>
						<?php } ?>
					</select>
			</p>
		<?php endforeach; ?>
</div>
