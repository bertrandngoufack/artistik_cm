<?php
/**
 * Select fields to watch.
 *
 * @package FrmAI
 *
 * @var int        $row_key  The index of the row.
 * @var int        $field_id The ID of the field.
 * @var int        $form_id  The ID of the form.
 * @var int|string $selected_field The ID of the selected field or blank.
 * @var array      $watch_fields Fields to select from.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p id="frm_watch_ai_<?php echo esc_attr( $field_id . '_' . $row_key ); ?>" class="frm_single_option frm_no_top_margin">
	<select name="field_options[watch_ai_<?php echo esc_attr( $field_id ); ?>][]">
		<option value=""><?php esc_html_e( '&mdash; Select Field &mdash;', 'formidable-pro' ); ?></option>
		<?php
		foreach ( $watch_fields as $field_option ) {
			if ( (int) $field_option->id === $field_id ||
				FrmField::is_no_save_field( $field_option->type ) ||
				in_array( $field_option->type, array( 'credit_card', 'password' ), true )
				) {
				continue;
			}

			$selected = ( (int) $field_option->id === (int) $selected_field ) ? ' selected="selected"' : '';
			?>
		<option value="<?php echo esc_attr( $field_option->id ); ?>" <?php echo esc_attr( $selected ); ?>>
			<?php
			echo esc_html( empty( $field_option->name ) ? $field_option->id . ' ' . __( '(no label)', 'formidable-pro' ) : $field_option->name );
			?>
		</option>
		<?php } ?>
	</select>
	<a href="javascript:void(0)" class="frm_remove_tag frm_icon_font frm-inline-select" data-removeid="frm_watch_ai_<?php echo esc_attr( $field_id . '_' . $row_key ); ?>" data-fieldid="<?php echo esc_attr( $field_id ); ?>"></a>
</p>
