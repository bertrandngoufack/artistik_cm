<?php 
if(!isset($values['hrs_list'][$list_field['tag']]['tag']) and isset($meta_name)){
    $values['hrs_list'][$list_field['tag']]['tag'] = array($meta_name => array());
}else if(!isset($values['hrs_list'][$list_field['tag']])){
    $values['hrs_list'][$list_field['tag']] = array('tag' => array(''));
}

foreach((array)$values['hrs_list'][$list_field['tag']]['tag'] as $tag_key => $tag_val){ ?>
<div class="frm_hrs_fields hrs_<?php echo $list_field['tag'] ?>_row" id="frm_hrs_<?php echo $list_field['tag'] ?>_<?php echo $tag_key ?>">
    <label class="frm_left_label"><?php echo $list_field['name']; ?></label>
        <?php if(!in_array($list_field['tag'], $show_add)){ ?> 
        <a class="button" href="javascript:frmHrsAddTagRow('<?php echo $list_field['tag'] ?>');"><?php _e('Add', 'formidable') ?></a>
        <?php 
            $show_add[] = $list_field['tag'];
            $remove = false;
        }else{ 
            $remove = true; 
        } ?>
        
        <?php if($remove){ ?>
        <div class="tagchecklist">
        <span><a href="javascript:frm_remove_tag('#frm_hrs_<?php echo $list_field['tag'] ?>_<?php echo $tag_key ?>');"> X </a></span>
        <span style="margin-right:5px;">&nbsp;</span>
        <?php } ?>
        <select name="options[hrs_list][<?php echo $list_field['tag'] ?>][tag][]">
            <option value="">- <?php _e('Select Field', 'formidable') ?> -</option>
            <?php foreach($form_fields as $form_field){ 
                if($list_field['tag'] == 'email_address' and !in_array($form_field->type, array('email', 'hidden', 'user_id')))
                    continue;
                $selected = ($tag_val == $form_field->id) ? ' selected="selected"' : '';
            ?>
            <option value="<?php echo $form_field->id ?>" <?php echo $selected ?>><?php echo stripslashes($form_field->name) ?></option>
            <?php } ?>
        </select>
        
        <?php if(isset($list_field['protocol'])){ ?>
        <select name="options[hrs_list][<?php echo $list_field['tag'] ?>][protocol][]">
        <?php foreach($list_field['protocol'] as $pro){ 
            $selected = (isset($values['hrs_list'][$list_field['tag']]['protocol']) and isset($values['hrs_list'][$list_field['tag']]['protocol'][$tag_key]) and $values['hrs_list'][$list_field['tag']]['protocol'][$tag_key] == $pro) ? ' selected="selected"' : ''; ?>
            <option value="<?php echo $pro ?>" <?php echo $selected ?>><?php echo $pro ?></option>
        <?php } ?>
        </select>    
        <?php } ?>
        
        <?php if(isset($list_field['location'])){ ?>
        <select name="options[hrs_list][<?php echo $list_field['tag'] ?>][location][]">
        <?php foreach($list_field['location'] as $loc){ 
            $selected = (isset($values['hrs_list'][$list_field['tag']]['location']) and isset($values['hrs_list'][$list_field['tag']]['location'][$tag_key]) and $values['hrs_list'][$list_field['tag']]['location'][$tag_key] == $loc) ? ' selected="selected"' : ''; ?>
            <option value="<?php echo $loc ?>" <?php echo $selected ?>><?php echo $loc ?></option>
        <?php } ?>
        </select>    
        <?php } ?>
        
        <?php if($remove){ ?>
        </div>
        <?php } ?>
</div>
<?php } ?>