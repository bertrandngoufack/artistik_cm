<?php
/**
 * Custom fields setting.
 *
 * @package FrmConvertKit
 *
 * @var array         $cvk_fields     ConvertKit custom fields.
 * @var array         $custom_fields  Form action custom fields.
 * @var array         $row_data       Row data.
 * @var int           $row_num        Row index.
 * @var stdClass      $form_action    Form action object.
 * @var array         $args           See {@see FrmConvertKitAction::form()}.
 * @var FrmFormAction $action_control Action control.
 */

?>
<div id="<?php echo esc_attr( $action_control->get_field_id( 'fields_' . $row_num ) ); ?>" class="frm_postmeta_row frm_grid_container">
	<div class="frm4 frm_form_field">
		<label class="screen-reader-text" for="<?php echo esc_attr( $action_control->get_field_id( 'fields_key_' . $row_num ) ); ?>">
			<?php esc_html_e( 'Field', 'frm-convertkit' ); ?>
		</label>

		<select name="<?php echo esc_attr( $action_control->get_field_name( 'fields' ) ); ?>[<?php echo absint( $row_num ); ?>][key]" id="<?php echo esc_attr( $action_control->get_field_id( 'fields_key_' . $row_num ) ); ?>" class="frm-cvk-custom-fields-key">
			<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
			<?php
			if ( is_array( $cvk_fields ) ) {
				foreach ( $cvk_fields as $cvk_field ) {
					?>
					<option value="<?php echo esc_attr( $cvk_field->key ); ?>" <?php selected( $cvk_field->key, $row_data['key'] ); ?>><?php echo esc_html( $cvk_field->label ); ?></option>
					<?php
				}
			}
			?>
		</select>
	</div>
	<div class="frm7 frm_form_field frm_has_shortcodes">
		<label class="screen-reader-text" for="<?php echo esc_attr( $action_control->get_field_id( 'fields_value_' . $row_num ) ); ?>">
			<?php esc_html_e( 'Value', 'frm-convertkit' ); ?>
		</label>
		<input type="text" name="<?php echo esc_attr( $action_control->get_field_name( 'fields' ) ); ?>[<?php echo absint( $row_num ); ?>][value]" value="<?php echo esc_attr( $row_data['value'] ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'fields_value_' . $row_num ) ); ?>" class="frm-cvk-custom-fields-value" />
	</div>
	<div class="frm1 frm_form_field frm-inline-select">
		<a href="javascript:void(0)" class="frm_remove_tag frm_icon_font" data-removeid="<?php echo esc_attr( $action_control->get_field_id( 'fields_' . $row_num ) ); ?>"></a>
		<a href="javascript:void(0)" class="frm_add_tag frm_icon_font frm-cvk-add-custom-field-row"></a>
	</div>
</div>
