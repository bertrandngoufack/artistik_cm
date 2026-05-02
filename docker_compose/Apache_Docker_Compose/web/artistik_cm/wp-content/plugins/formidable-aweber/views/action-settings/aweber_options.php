<table class="form-table frm-no-margin">
<tbody>
	<tr class="awbr_list awbr_list_<?php echo esc_attr( $list_id ); ?>">
		<td>
			<p>
				<?php
				if ( $error ) {
					FrmAwbrAppHelper::wrong_account_message( true );
				} else if ( $lists ) {
					?>
					<label class="frm_left_label" style="clear:none;">
						<?php esc_html_e( 'List', 'formidable-aweber' ); ?> <span class="frm_required">*</span>
					</label>
					<select name="<?php echo esc_attr( $action_control->get_field_name( 'list_id' ) ); ?>" id="select_list_<?php echo esc_attr( $list_id ); ?>">
						<option value="">- <?php esc_html_e( 'Select List', 'formidable-aweber' ); ?> -</option>
						<?php foreach ( $lists as $list ) { ?>
							<option value="<?php echo esc_attr( $list->id ); ?>" <?php selected( $list_id, $list->id ); ?>>
								<?php echo esc_html( $list->name ); ?>
							</option>
						<?php } ?>
					</select>
					<?php
				} else {
					esc_html_e( 'No AWeber mailing lists found', 'formidable-aweber' );
				}
				?>
			</p>
			<div class="frm_awbr_fields">
				<?php
				if ( isset( $list_fields ) ) {
					include FrmAwbrAppController::path() . '/views/action-settings/_match_fields.php';
				}
				?>
			</div>
		</td>
	</tr>
</tbody>
</table>
