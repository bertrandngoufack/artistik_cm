<?php

if ( ! isset($options[$list_field['tag']]['tag']) && isset($meta_name) ) {
    $options[$list_field['tag']]['tag'][ $meta_name ] = '';
} else if ( ! isset($options[$list_field['tag']]) ) {
    $options[$list_field['tag']] = array('tag' => array(''));
} else if ( ! isset( $options[ $list_field['tag'] ]['tag'] ) ) {
	$options[$list_field['tag']]['tag'][] = '';
}

foreach ( (array) $options[$list_field['tag']]['tag'] as $tag_key => $tag_val ) { 
    if ( 'phone_number' == $list_field['tag'] && ! isset( $adding_row ) ) {
		$adding_row = true;
		echo '</table>';
        echo $contact_heading;
		echo '<table class="form-table">';
    }
?>
<tr class="frm_hrs_fields hrs_<?php echo esc_attr( $list_field['tag'] ) ?>_row" id="<?php echo esc_attr( $action_control->get_field_id( 'frm_hrs_'. $list_field['tag'] .'_'. $tag_key ) ) ?>" data-tag-key="<?php echo esc_attr( $tag_key ) ?>">
    <th><label><?php echo ( 'instant_messenger' == $list_field['tag'] ) ? __('IM', 'frmhrs') : $list_field['name']; ?></label></th>
    <td><?php
		if ( ! in_array( $list_field['tag'], $show_add ) ) {
            $show_add[] = $list_field['tag'];
            $remove = false;
        } else { 
            $remove = true; 
        }

		if ( isset( $list_field['fields'] ) && ! empty( $list_field['fields'] ) ) {
        	if ( ! is_array( $tag_val ) ) {
        		$tag_val = array( 'street' => $tag_val );
        	}
			$tag_val = array_merge( $list_field['defaults'], $tag_val );

			foreach ( $list_field['fields'] as $field_name => $field_info ) {
        ?>
			<input type="text" name="<?php echo esc_attr( $action_control->get_field_name( $list_field['tag'] ) ) ?>[tag][<?php echo esc_attr( $tag_key ) ?>][<?php echo esc_attr( $field_name ) ?>]" value="<?php echo esc_attr( $tag_val[ $field_name ] ) ?>" class="frm_not_email_message alignleft" id="<?php echo esc_attr( $action_control->get_field_id( $list_field['tag'] . '_tag_' . $tag_key . '_' . $field_name ) ) ?>" placeholder="<?php echo esc_attr( $field_info['placeholder'] ) ?>" style="width:<?php echo esc_attr( $field_info['size'] ) ?>"/>
		<?php
			}
		} else { ?>
        <input type="text" name="<?php echo esc_attr( $action_control->get_field_name( $list_field['tag'] ) ) ?>[tag][]" value="<?php echo esc_attr( $tag_val ) ?>" class="<?php echo ( $list_field['tag'] == 'email_address' ) ? 'frm_not_email_to' : 'frm_not_email_message'; ?>" id="<?php echo esc_attr( $action_control->get_field_id($list_field['tag'] . '_tag_' . $tag_key ) ) ?>" />
		<?php
		}

		if ( isset( $list_field['protocol'] ) ) { ?>
        <select name="<?php echo $action_control->get_field_name($list_field['tag']) ?>[protocol][]">
        <?php foreach ( $list_field['protocol'] as $pro ) { 
            $selected = ( isset($options[$list_field['tag']]['protocol']) && isset($options[$list_field['tag']]['protocol'][$tag_key]) && $options[$list_field['tag']]['protocol'][$tag_key] == $pro ) ? ' selected="selected"' : ''; ?>
            <option value="<?php echo esc_attr( $pro ) ?>" <?php echo $selected ?>><?php echo $pro ?></option>
        <?php } ?>
        </select>    
        <?php
		}

		if ( isset( $list_field['location'] ) ) { ?>
        <select name="<?php echo $action_control->get_field_name($list_field['tag']) ?>[location][]">
		<?php foreach ( $list_field['location'] as $loc ) {
			$selected = ( isset( $options[ $list_field['tag'] ]['location'] ) && isset( $options[ $list_field['tag'] ]['location'][ $tag_key ] ) && $options[ $list_field['tag'] ]['location'][ $tag_key ] == $loc ) ? ' selected="selected"' : ''; ?>
            <option value="<?php echo esc_attr( $loc ) ?>" <?php echo $selected ?>><?php echo $loc ?></option>
        <?php } ?>
        </select>    
        <?php } ?>
        
        
        <?php if ( $remove ) { ?>
        <a class="frm_icon_font frm_remove_tag" data-removeid="<?php echo esc_attr( $action_control->get_field_id( 'frm_hrs_'. $list_field['tag'] . '_' . $tag_key ) ) ?>" ></a>
        <?php } ?>
        <a class="frm_icon_font frm_add_tag" data-tag="<?php echo esc_attr( $list_field['tag'] ) ?>"></a>
	</td>
</tr>
<?php } ?>