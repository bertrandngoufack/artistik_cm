<?php
if ( is_array( $object_fields ) ) :
	foreach ( $object_fields as $list_field ) :
		?>
		<div class="salesforce-field-cont" >
			<label for="<?php echo esc_attr( $action_control->get_field_id( 'fields' ) . '-' . $list_field['name'] ); ?>" class="frm_left_label">
				<?php echo esc_html( ucfirst( $list_field['label'] ) ); ?>

				<?php if ( $list_field['required'] ) : ?>
					<span class="frm_required">*</span>
				<?php endif; ?>
				<?php if ( ! empty( $list_field['type'] ) ) : ?>
					<span class="remote-field-info">
						(<?php echo esc_html( ucfirst( $list_field['type'] ) ); ?>)
						<?php
						if ( isset( $list_field['picklistValues'] ) ) {
							echo esc_html( FrmAppHelper::truncate( $list_field['picklistValues'], 100 ) );
						}
						?>
					</span>
				<?php endif; ?>
			</label>

			<select name="<?php echo esc_attr( $action_control->get_field_name( 'fields' ) ); ?>[<?php echo esc_attr( $list_field['name'] ); ?>]" id="<?php echo esc_attr( $action_control->get_field_id( 'fields' ) . '-' . $list_field['name'] ); ?>">
				<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
				<?php
				foreach ( $form_fields as $form_field ) :
					$selected = ( isset( $list_options['fields'][ $list_field['name'] ] ) && $list_options['fields'][ $list_field['name'] ] == $form_field->id ) ? ' selected="selected"' : '';
					?>
					<option value="<?php echo esc_attr( $form_field->id ); ?>" <?php echo esc_attr( $selected ); ?> >
						<?php echo esc_html( FrmAppHelper::truncate( $form_field->name, 40 ) ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="clear"></div>
	<?php endforeach; ?>
	<div class="salesforce-field-cont" >
		<label for="<?php echo esc_attr( $action_control->get_field_id( 'update_field' ) ); ?>" class="frm_left_label">
			<?php esc_html_e( 'Update existing record by', 'formidable-salesforce' ); ?>
			<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Update the Salesforce record that matches the value in the selected field. Matching by email address is the most common for leads and contacts.', 'formidable-salesforce' ); ?>"></span>
		</label>

		<select name="<?php echo esc_attr( $action_control->get_field_name( 'update_field' ) ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'update_field' ) ); ?>">
			<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
			<?php
			foreach ( $object_fields as $list_field ) :
				?>
				<option value="<?php echo esc_attr( $list_field['name'] ); ?>" <?php selected( $list_options['update_field'], $list_field['name'] ); ?> >
					<?php echo esc_html( ucfirst( $list_field['label'] ) ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
<?php endif; ?>
