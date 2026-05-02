<?php
/**
 * Show the settings in the form builder.
 *
 * @package FrmAI
 *
 * @var array $field The current field.
 * @var array $watch_fields The selected fields to watch.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="frm_form_field">
	<label>
		<input type="checkbox" name="field_options[hide_ai_<?php echo esc_attr( $field['id'] ); ?>]" id="frm_hide_ai_<?php echo esc_attr( $field['id'] ); ?>" value="1" <?php checked( $field['hide_ai'], 1 ); ?> />
		<?php esc_html_e( 'Hide Response', 'formidable-ai' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Check this box if you do not want to see the AI response immediately in the form.', 'formidable-ai' ); ?>"></span>
	</label>
</p>

<p class="frm_form_field">
	<label for="system_<?php echo esc_attr( $field['id'] ); ?>" class="frm_primary_label">
		<?php esc_html_e( 'Guide Prompt', 'formidable-ai' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Give Open AI more context for the type of response you would like to receive.', 'formidable-ai' ); ?>"></span>
	</label>
	<textarea name="field_options[system_<?php echo esc_attr( $field['id'] ); ?>]"
		id="system_<?php echo esc_attr( $field['id'] ); ?>"
		placeholder="<?php esc_attr_e( 'You are a helpful assistant', 'formidable-ai' ); ?>"
		rows="2"
		><?php echo FrmAppHelper::esc_textarea( $field['system'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></textarea>
</p>

<div class="frm_grid_container">
	<div class="frm_form_field">
		<label class="frm_primary_label">
			<?php esc_html_e( 'Watch Fields', 'formidable-ai' ); ?>
			<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Disclaimer: Do not collect sensitive information in any watched field', 'formidable-ai' ); ?>"></span>
		</label>
		<div id="frm_watch_ai_block_<?php echo absint( $field['id'] ); ?>" class="frm_add_remove">
		<?php
		if ( empty( $field['watch_ai'] ) ) {
			$field_id       = (int) $field['id'];
			$row_key        = 0;
			$selected_field = '';
			include FrmAIAppHelper::plugin_path() . '/classes/views/watch-row.php';
		} else {
			$field_id = (int) $field['id'];
			foreach ( $field['watch_ai'] as $row_key => $selected_field ) {
				include FrmAIAppHelper::plugin_path() . '/classes/views/watch-row.php';
			}
		}
		?>
		</div>
		<a href="#" id="frm_add_watch_ai_link_<?php echo esc_attr( $field['id'] ); ?>" class="frm-small-add frm_add_watch_ai_row frm_add_watch_ai_link">
			<span class="frm_icon_font frm_add_tag"></span>
			<?php esc_html_e( 'Watch another field', 'formidable-ai' ); ?>
		</a>
	</div>
</div>

<p class="howto">
	<?php esc_html_e( 'By using this field, you agree to the ChatGPT terms of service.', 'formidable-ai' ); ?>
</p>
