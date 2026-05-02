
<table class="form-table frm-no-margin">
	<tbody class="salesforce_object">
		<tr>
			<th>
				<label for="<?php echo esc_attr( $action_control->get_field_id( 'object_id' ) ); ?>">
					<?php esc_html_e( 'Object', 'formidable-salesforce' ); ?>
					<span class="frm_required">*</span>
				</label>
			</th>
			<td>
				<select name="<?php echo esc_attr( $action_control->get_field_name( 'object_id' ) ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'object_id' ) ); ?>">
					<option value="">
						<?php esc_html_e( '&mdash; Select &mdash;' ); ?>
					</option>
					<?php foreach ( $objects as $object ) : ?>
						<option value="<?php echo esc_attr( $object['name'] ); ?>" <?php selected( $object_id, $object['name'] ); ?>>
							<?php echo esc_html( FrmAppHelper::truncate( $object['label'], 40 ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<span class="spinner frm_salesforce_loading_field"></span>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="frm_salesforce_fields <?php echo esc_attr( $action_control->get_field_id( 'frm_salesforce_fields' ) ); ?>">
				<?php
				if ( isset( $object_fields ) && $object_fields ) :
					include dirname( __FILE__ ) . '/_match_fields.php';
				endif;
				?>
			</td>
		</tr>
		<tr>
			<td colspan="2" style="text-align:right">
				<span class="clrcache-salesforce-spinner spinner" style="float:none;"></span>
				<a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=formidable&frm_action=settings&t=email_settings&clear_cache=salesforce&id=' . $action_control->form_id ) ); ?>" id="clrcache-salesforce" class="button" >
					<?php esc_html_e( 'Clear Cache', 'formidable-salesforce' ); ?>
				</a>
			</td>
		</tr>
	</tbody>
</table>
