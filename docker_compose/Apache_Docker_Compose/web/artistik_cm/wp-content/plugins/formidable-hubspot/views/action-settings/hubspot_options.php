<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm_grid_container frm_hubspot_action_container">
	<p>
		<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=formidable&frm_action=settings&t=email_settings&clear_cache=hubspot&id=' . $form->id ) ) ); ?>" id="clrcache-hubspot" class="button frm-button-secondary">
			<?php esc_html_e( 'Clear Cache', 'formidable-hubspot' ); ?>
		</a>
		<span style="float:none" class="clrcache-hubspot-spinner spinner"></span>
	</p>
	<?php
	if ( $list_array || $company_list_array ) {
		?>
		<p class="frm6 frm_form_field">
			<label>
				<?php esc_html_e( 'List', 'formidable-hubspot' ); ?>
			</label>
			<select name="<?php echo esc_attr( $action_control->get_field_name( 'list_id' ) ); ?>">
				<option value=""><?php esc_html_e( '&mdash; Select &mdash;', 'formidable-hubspot' ); ?></option>
				<?php
				if ( $list_array ) {
					$optgroup_label     = __( 'Contact Lists', 'formidable-hubspot' );
					$current_list_array = $list_array;
					include $view_path . '/_hubspot_list_optgroup.php';
				}

				if ( $company_list_array ) {
					$optgroup_label     = __( 'Companies', 'formidable-hubspot' );
					$current_list_array = $company_list_array;
					include $view_path . '/_hubspot_list_optgroup.php';
				}
				?>
			</select>
		</p>
		<?php
	}

	require $view_path . '/_match_fields.php';
	?>
</div>
