<?php
if(!$new_field)
    return;
    
if ($new_field->type == 'data'){

    if (isset($new_field->field_options['form_select']) && is_numeric($new_field->field_options['form_select'])){
        $frm_entry_meta = new FrmEntryMeta();
        $new_entries = $frm_entry_meta->getAll("it.field_id=".$new_field->field_options['form_select']);
        unset($frm_entry_meta);
    }
        
    $new_field->options = array();
    if (isset($new_entries) && !empty($new_entries)){
        foreach ($new_entries as $ent)
            $new_field->options[$ent->item_id] = $ent->meta_value;
    }
}else if(isset($new_field->field_options['post_field']) and $new_field->field_options['post_field'] == 'post_status'){
    $new_field->options = FrmProFieldsHelper::get_status_options($new_field);
}else{
    $new_field->options = stripslashes_deep(maybe_unserialize($new_field->options));
}
    

if(isset($new_field->field_options['post_field']) and $new_field->field_options['post_field'] == 'post_category'){
    $new_field = (array)$new_field;
    $new_field['value'] = (isset($field) and isset($values['hrs_list']['hide_opt'][$meta_name])) ? $values['hrs_list']['hide_opt'][$meta_name] : '';
    $new_field['exclude_cat'] = (isset($new_field->field_options['exclude_cat'])) ? $new_field->field_options['exclude_cat'] : '';
    echo FrmFieldsHelper::dropdown_categories(array('name' => "options[hrs_list][hide_opt][]", 'id' => "options[hrs_list][hide_opt]", 'field' => $new_field) );
}else{ ?>
<select name="options[hrs_list][hide_opt][]">
    <option value=""><?php echo ($new_field->type == 'data') ? __('Anything', 'formidable') : __('Select', 'formidable'); ?></option>
    <?php if($new_field->options){
        $temp_field = (array)$new_field;
        foreach($new_field->field_options as $fkey => $fval){
            $temp_field[$fkey] = $fval;
            unset($fkey);
            unset($fval);
        }
        
        foreach ($new_field->options as $opt_key => $opt){
            $field_val = apply_filters('frm_field_value_saved', $opt, $opt_key, $temp_field); //use VALUE instead of LABEL
            $opt = apply_filters('frm_field_label_seen', $opt, $opt_key, $temp_field);
            unset($field_array);
            $val = (isset($values['hrs_list']) and isset($values['hrs_list']['hide_opt']) and isset($values['hrs_list']['hide_opt'][$meta_name])) ? $values['hrs_list']['hide_opt'][$meta_name] : '';
        ?>
    <option value="<?php echo esc_attr($field_val); ?>"<?php selected($val, $field_val) ?>><?php echo FrmAppHelper::truncate($opt, 25); ?></option>
    <?php
            unset($val);
        } 
        unset($temp_field);
    } ?>
</select>
<?php 
} ?>