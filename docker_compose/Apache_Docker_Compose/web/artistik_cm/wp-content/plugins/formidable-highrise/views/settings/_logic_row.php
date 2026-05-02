<div id="frm_hrs_logic_<?php echo $meta_name ?>" class="frm_hrs_logic_row">
<span><a class="frm_hrs_remove_tag"> X </a></span>
&nbsp;
<select name="options[hrs_list][hide_field][]" onchange="frmHrsGetFieldValues(this.value,<?php echo $meta_name ?>)">
    <option value=""><?php _e('Select Field', 'formidable') ?></option>
    <?php foreach ($form_fields as $ff){ 
        if(!in_array($ff->type, array('select','radio','checkbox','10radio','scale')))
            continue;
        $selected = ($ff->id == $hide_field) ?' selected="selected"':''; ?>
    <option value="<?php echo $ff->id ?>"<?php echo $selected ?>><?php echo FrmAppHelper::truncate($ff->name, 30); ?></option>
    <?php } ?>
</select>
<?php _e('is', 'formidable'); 

if(!isset($values['hrs_list']['hide_field_cond']))
    $values['hrs_list']['hide_field_cond'] = array($meta_name => '==');

if(!isset($values['hrs_list']['hide_field_cond'][$meta_name]))
    $values['hrs_list']['hide_field_cond'][$meta_name] = '==';   
?>
<select name="options[hrs_list][hide_field_cond][]">
    <option value="==" <?php selected($values['hrs_list']['hide_field_cond'][$meta_name], '==') ?>><?php _e('equal to', 'formidable') ?></option>
    <option value="!=" <?php selected($values['hrs_list']['hide_field_cond'][$meta_name], '!=') ?>><?php _e('NOT equal to', 'formidable') ?> &nbsp;</option>
    <option value=">" <?php selected($values['hrs_list']['hide_field_cond'][$meta_name], '>') ?>><?php _e('greater than', 'formidable') ?></option>
    <option value="<" <?php selected($values['hrs_list']['hide_field_cond'][$meta_name], '<') ?>><?php _e('less than', 'formidable') ?></option>
</select>

<span id="frm_hrs_show_selected_values_<?php echo $meta_name ?>" class="no_taglist">
    <?php if ($hide_field and is_numeric($hide_field)){
        $frm_field = new FrmField();
        $new_field = $frm_field->getOne($hide_field);
        unset($frm_field);

        include('_field_values.php');
    } ?>
</span>
</div>