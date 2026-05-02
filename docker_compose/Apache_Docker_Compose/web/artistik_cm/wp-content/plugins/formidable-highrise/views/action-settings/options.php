<table class="form-table">
    <tbody>

<?php
    
foreach ( $list_fields as $list_field ) {
    if ( $list_field['multi'] ) {
        include('_tag_row.php');
    } else {
        if ( isset( $custom_heading ) && isset( $list_field['custom'] ) && ! empty( $custom_heading ) ) {
			echo '</table>';
            echo $custom_heading;
			echo '<table class="form-table">';
            $custom_heading = '';
        } else if (  'twitter_account' == $list_field['tag'] ) {
			echo '</table>';
            echo $social_heading;
			echo '<table class="form-table">';
        }
        
        if ( isset( $options[ $list_field['tag'] ] ) && isset( $options[ $list_field['tag'] ]['tag'] ) && is_array( $options[ $list_field['tag'] ]['tag'] ) ) {
            $options[ $list_field['tag'] ]['tag'] = reset( $options[ $list_field['tag'] ]['tag'] );
        }
?>
    <tr>
		<th><label><?php echo esc_html( $list_field['name'] ); ?></label></th>
		<td><input type="text" name="<?php echo esc_attr( $action_control->get_field_name( $list_field['tag'] ) ) ?>[tag]" value="<?php echo esc_attr( ( isset( $options[ $list_field['tag'] ] ) && isset( $options[ $list_field['tag'] ]['tag']) ) ? $options[ $list_field['tag'] ]['tag'] : ''); ?>" class="frm_not_email_message large-text" id="<?php echo esc_attr( $action_control->get_field_id( $list_field['tag'] . '_tag' ) ) ?>" /></td>
	</tr>
<?php }
} ?>

	<tr>
		<th><label><?php _e('Tags', 'formidable') ?></label></th>
		<td><input type="text" name="<?php echo esc_attr( $action_control->get_field_name('tags') ); ?>" value="<?php echo esc_attr( $options['tags'] ) ?>" class="frm_not_email_message large-text" id="<?php echo esc_attr( $action_control->get_field_id('tags') ) ?>" /></td>
	</tr>
	<tr>
		<th><label><?php _e('Attach a note', 'formidable') ?></label></th>
		<td><input type="text" name="<?php echo esc_attr( $action_control->get_field_name('background') ) ?>" value="<?php echo esc_attr( $options['background'] ) ?>" class="frm_not_email_message large-text" id="<?php echo esc_attr( $action_control->get_field_id('background') ) ?>" /></td>
	</tr>
    </tbody>
</table> 
