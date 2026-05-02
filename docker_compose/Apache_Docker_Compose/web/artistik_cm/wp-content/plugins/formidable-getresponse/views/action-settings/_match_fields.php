<?php
/**
 * Get the GetResponse fields to match up to Formidable.
 *
 * @package formidable-getresponse
 */

?>
<div class="frm_getresponse_fields <?php echo esc_attr( $action_control->get_field_id( 'frm_getresponse_fields' ) ); ?>">

	<p>
		<label class="frm_left_label">
			<?php esc_html_e( 'First Name', 'formidable-getresponse' ); ?>
			<span class="frm_required">*</span>
		</label>
		<select name="<?php echo esc_attr( $action_control->get_field_name( 'fields' ) ); ?>[first_name]">
			<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
			<?php
			foreach ( $form_fields as $form_field ) {
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
			<?php } ?>
		</select>
	</p>

	<p>
		<label class="frm_left_label">
			<?php esc_html_e( 'Last Name', 'formidable-getresponse' ); ?>
		</label>
		<select name="<?php echo esc_attr( $action_control->get_field_name( 'fields' ) ); ?>[last_name]">
			<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
			<?php
			foreach ( $form_fields as $form_field ) {
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
			<?php } ?>
		</select>
	</p>
	<p>
		<label class="frm_left_label">
			<?php esc_html_e( 'Email', 'formidable-getresponse' ); ?>
			<span class="frm_required">*</span>
		</label>
		<select name="<?php echo esc_attr( $action_control->get_field_name( 'fields' ) ); ?>[email]">
			<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
			<?php
			foreach ( $form_fields as $form_field ) {
				$selected = ( isset( $list_options['fields']['email'] ) && $list_options['fields']['email'] == $form_field->id ) ? ' selected="selected"' : '';
				?>
				<option value="<?php echo esc_attr( $form_field->id ); ?>" <?php echo esc_attr( $selected ); ?>>
					<?php echo esc_html( FrmAppHelper::truncate( $form_field->name, 40 ) ); ?>
				</option>
			<?php } ?>
		</select>
	</p>

	<?php
	foreach ( $list_fields as $field ) {
		$field_id = $field->customFieldId; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		?>
		<p>
			<label class="frm_left_label">
				<?php echo esc_html( $field->name ); ?>
			</label>
			<select name="<?php echo esc_attr( $action_control->get_field_name( 'fields' ) ); ?>[<?php echo esc_attr( $field_id ); ?>]">
				<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
				<?php
				foreach ( $form_fields as $form_field ) {
					$selected = ( isset( $list_options['fields'][ $field_id ] ) && $list_options['fields'][ $field_id ] == $form_field->id ) ? ' selected="selected"' : '';
					?>
					<option value="<?php echo esc_attr( $form_field->id ); ?>" <?php echo esc_attr( $selected ); ?>>
						<?php echo esc_html( FrmAppHelper::truncate( $form_field->name, 40 ) ); ?>
					</option>
				<?php } ?>
			</select>
		</p>
		<?php
	}
	?>
	<p>
		<label class="frm_left_label">
			<?php
			esc_html_e( 'Autoresponder Cycle Day', 'formidable-getresponse' );
			if ( is_callable( 'FrmAppHelper::tooltip_icon' ) ) {
				FrmAppHelper::tooltip_icon( esc_html__( 'If this list begins a multi-day autoresponder, you may start this contact in the middle of the campaign. Leave blank to start at the beginning of the autoresponder email campaign.', 'formidable-getresponse' ) );
			}
			?>
		</label>
		<input type="number" name="<?php echo esc_attr( $action_control->get_field_name( 'cycle_day' ) ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'cycle_day' ) ); ?>" value="<?php echo isset( $list_options['cycle_day'] ) ? esc_attr( $list_options['cycle_day'] ) : ''; ?>" />
	</p>
</div>
