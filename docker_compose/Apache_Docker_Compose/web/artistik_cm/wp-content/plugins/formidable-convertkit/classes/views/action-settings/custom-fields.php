<?php
/**
 * Custom fields setting.
 *
 * @package FrmConvertKit
 *
 * @var array|string  $cvk_fields     ConvertKit custom fields.
 * @var stdClass      $form_action    Form action object.
 * @var array         $args           See {@see FrmConvertKitAction::form()}.
 * @var FrmFormAction $action_control Action control.
 */

$custom_fields = $form_action->post_content['fields'];
if ( ! $custom_fields || ! is_array( $custom_fields ) ) {
	$custom_fields = array(
		array(
			'key'   => '',
			'value' => '',
		),
	);
}
?>
<div class="frm-cvk-custom-fields-section">
	<h3><?php esc_html_e( 'Custom fields', 'frm-convertkit' ); ?></h3>
	<div class="frm_add_remove">
		<p class="frm_grid_container frm_no_margin">
			<label class="frm4 frm_form_field">
				<?php esc_html_e( 'Field', 'formidable' ); ?>
			</label>
			<label class="frm6 frm_form_field">
				<?php esc_html_e( 'Value', 'formidable' ); ?>
			</label>
		</p>

		<div class="frm-cvk-custom-fields-rows">
			<?php
			foreach ( $custom_fields as $row_num => $row_data ) {
				if ( empty( $row_data['key'] ) && $row_num ) {
					continue;
				}
				include FrmConvertKitAppHelper::plugin_path() . '/classes/views/action-settings/_data_row.php';
			}
			?>
		</div>

		<a href="#" class="frm-cvk-reload" data-method="get_custom_fields" data-value="key" data-label="label">
			<?php esc_html_e( 'Reload custom fields', 'frm-convertkit' ); ?>
			<span class="spinner"></span>
		</a>
	</div>
</div>
