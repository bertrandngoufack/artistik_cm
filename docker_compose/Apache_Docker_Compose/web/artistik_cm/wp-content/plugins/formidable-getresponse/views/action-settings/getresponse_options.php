<?php
/**
 * Show in the form action.
 *
 * @package formidable-getresponse
 */

if ( isset( $error ) ) {
	?>
	<div class="frm_error_style inline">
		<?php echo esc_html( $error ); ?>
	</div>
	<?php
}
?>
<table class="form-table frm-no-margin">
	<tbody>
		<tr class="getresponse_list">
			<td>
				<p>
					<?php
					if ( $lists ) {
						?>
						<label class="frm_left_label" style="clear:none;">
							<?php esc_html_e( 'List', 'formidable-getresponse' ); ?>
							<span class="frm_required">*</span>
						</label>
						<select name="<?php echo esc_attr( $action_control->get_field_name( 'list_id' ) ); ?>">
							<option value=""><?php echo esc_html( '&mdash; Select &mdash;' ); ?></option>
							<?php foreach ( $lists as $list ) { ?>
							<option value="<?php echo esc_attr( $list->campaignId ); ?>" <?php selected( $list_id, $list->campaignId ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName ?>>
								<?php echo esc_html( FrmAppHelper::truncate( $list->name, 40 ) ); ?>
							</option>
							<?php } ?>
						</select>
						<?php
					} else {
						printf(
							/* translators: %1$s: Start link HTML %2$s: end link HTML */
							esc_html__( 'No GetResponse lists found. Please %1$scheck your API key%1$s.', 'formidable-getresponse' ),
							'<a href="' . esc_url( admin_url( 'admin.php?page=formidable-settings&t=getresponse_settings' ) ) . '">',
							'</a>'
						);
					}
					?>
				</p>
				<div class="clear"></div>

				<?php
				if ( isset( $list_fields ) && $list_fields ) {
					include dirname( __FILE__ ) . '/_match_fields.php';
				} else {
					?>
					<div class="frm_getresponse_fields"></div>
					<?php
				}
				?>

</td>
</tr>
</tbody>
</table>
