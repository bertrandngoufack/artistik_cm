<div class="frm_grid_container">
	<p class="frm6">
		<?php if ( is_object( $lists ) ) : ?>
			<label>
				<?php esc_html_e( 'List', 'frmactivecampaign' ); ?>
				<span class="frm_required">*</span>
			</label>
			<select name="<?php echo esc_attr( $action_control->get_field_name( 'list_id' ) ); ?>">
				<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
				<?php
				$excluded_keys = array( 'result_code', 'result_message', 'result_output' );
				foreach ( $lists as  $key => $list ) :
					if ( ! in_array( $key, $excluded_keys ) ) :
						?>
					<option value="<?php echo esc_attr( $list->id ); ?>" <?php selected( $list_id, $list->id ); ?>>
						<?php echo esc_html( FrmAppHelper::truncate( $list->name, 40 ) ); ?>
					</option>
					<?php endif; ?>
				<?php endforeach; ?>
			</select>
		<?php else : ?>
			<?php esc_html_e( 'No ActiveCampaign lists found. Please add a list ActiveCampaign and check if the API key is correct and try again.', 'frmactivecampaign' ); ?>
		<?php endif; ?>
	</p>
	<p class="frm6">
		<?php if ( is_object( $lists ) ) : ?>
			<label>&nbsp;</label>
			<a href="javascript:void(0)" class="clrcache-activecampaign button frm-button-secondary">
				<?php esc_html_e( 'Clear Cache', 'frmactivecampaign' ); ?>
			</a>
			<span style="float:none" class="clrcache-activecampaign-spinner spinner"></span>
			<?php
		else :
			echo esc_html( $lists ) . '<br/>';
		endif;
		?>
	</p>
	<p class="frm6">
		<label>
			<input type="checkbox" value="1" name="<?php echo esc_attr( $action_control->get_field_name( 'resubscribe' ) ); ?>" <?php checked( isset( $list_options['resubscribe'] ) ? $list_options['resubscribe'] : 0, 1 ); ?>/>
			<?php esc_html_e( 'Resubscribe a contact who has unsubscribed from this list.', 'frmactivecampaign' ); ?>
		</label>
	</p>

	<?php include dirname( __FILE__ ) . '/_match_fields.php'; ?>
</div>
