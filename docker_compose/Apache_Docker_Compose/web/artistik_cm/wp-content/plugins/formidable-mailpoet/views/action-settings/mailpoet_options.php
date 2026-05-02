
<table class="form-table frm-no-margin">
	<tbody>
		<tr class="mailpoet_list">
			<td>
				<p>
					<?php if ( $lists ) : ?>
						<label class="frm_left_label" style="clear:none;">
							<?php esc_html_e( 'List', 'frmmailpoet' ); ?>
							<span class="frm_required">*</span>
						</label>
						<select name="<?php echo esc_attr( $action_control->get_field_name( 'list_id' ) ); ?>">
							<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
							<?php foreach ( $lists as  $key => $list ) { ?>
								<option value="<?php echo esc_attr( $list['id'] ); ?>" <?php selected( $list_id, $list['id'] ); ?>>
									<?php echo esc_html( FrmAppHelper::truncate( $list['name'], 40 ) ); ?>
								</option>
							<?php } ?>
						</select>
						<?php
					else :
						esc_html_e( 'No MailPoet list found ', 'frmmailpoet' );
					endif;
					?>
				</p>
				<div class="clear"></div>
				<?php include( dirname( __FILE__ ) . '/_match_fields.php' ); ?>
				<div class="frm_mailpoet_fields"></div>
			</td>
		</tr>
	</tbody>
</table>
