<table class="form-table">
    <tbody>
    <tr>
        <td><label for="highrise"><input type="checkbox" name="options[highrise]" id="highrise" value="1" <?php checked($values['highrise'], 1); ?> /> <label for="highrise"><?php _e('Add users who submit this form to your Highrise CRM', 'formidable') ?></label>
        </td>
    </tr>
    <tr class="hide_highrise" <?php echo $hide_highrise ?>><td>

<?php foreach($list_fields as $list_field){ 
    if($list_field['multi']){
        include('_tag_row.php');
    }else{
?>
<p><label class="frm_left_label"><?php echo $list_field['name']; ?></label>
        <select name="options[hrs_list][<?php echo $list_field['tag'] ?>][tag]">
            <option value="">- <?php _e('Select Field', 'formidable') ?> -</option>
            <?php foreach($form_fields as $form_field){ 
                $selected = (isset($values['hrs_list'][$list_field['tag']]) and $values['hrs_list'][$list_field['tag']]['tag'] == $form_field->id) ? ' selected="selected"' : '';
            ?>
            <option value="<?php echo $form_field->id ?>" <?php echo $selected ?>><?php echo FrmAppHelper::truncate($form_field->name, 50); ?></option>
            <?php } ?>
        </select>
</p>
<?php }
} ?>
<p><label class="frm_left_label"><?php _e('Tags', 'formidable') ?></label>
    <input type="text" name="options[hrs_list][tags]" value="<?php echo isset($values['hrs_list']['tags']) ? $values['hrs_list']['tags'] : ''; ?>" class="frm_with_left_label frm_not_email_message" />
</p>
<p><label class="frm_left_label"><?php _e('Attach a note', 'formidable') ?></label>
    <input type="text" name="options[hrs_list][background]" value="<?php echo isset($values['hrs_list']['background']) ? $values['hrs_list']['background'] : 'Inserted from '. get_option('blogname'); ?>" class="frm_with_left_label" />
</p>
<div class="clear"></div>
<div>
    <label class="frm_hrs_logic_label" <?php if(!isset($values['hrs_list']['hide_field']) or empty($values['hrs_list']['hide_field'])){ echo 'style="display:none;"'; } ?>><?php _e('Conditional Logic', 'formidable') ?></label>
    <div class="frm_hrs_logic_rows tagchecklist">
<?php

if(isset($values['hrs_list']['hide_field']) and !empty($values['hrs_list']['hide_field'])){ 
    foreach((array)$values['hrs_list']['hide_field'] as $meta_name => $hide_field){
        include(FrmHrsAppHelper::plugin_path() .'/views/settings/_logic_row.php');
    }
}
?>
    </div>
    <p><a class="button frm_hrs_add_logic_row">+ <?php _e('Add Conditional Logic', 'formidable') ?></a></p>
</div>
    </td></tr>
    </tbody>
</table> 

<style type="text/css">
.themeRoller .highrise_settings{color:#333;display:block !important;}
.frm_left_label{clear:both;float:left;width:170px;}
#form_settings_page .frm_hrs_fields select{max-width:250px;}
</style>
<script type="text/javascript">
jQuery(document).ready(function($){
$('input#highrise').click(function(){
    frm_show_div('hide_highrise',this.checked,1,'.');
});

$('#highrise_settings').on('click', '.frm_hrs_remove_tag', frmHrsRemoveLogicRow);
$('.frm_hrs_add_logic_row').click(frmHrsAddLogicRow);
});

function frmHrsAddLogicRow(){
if(jQuery('.frm_hrs_logic_rows .frm_hrs_logic_row').length)
	var len=1+parseInt(jQuery('.frm_hrs_logic_rows .frm_hrs_logic_row:last').attr('id').replace('frm_hrs_logic_', ''));
else var len=0;
jQuery.ajax({
    type:"POST",url:ajaxurl,
    data:"action=frm_hrs_add_logic_row&form_id=<?php echo $values['id'] ?>&meta_name="+len,
    success:function(html){jQuery('.frm_hrs_logic_label').show();jQuery('.frm_hrs_logic_rows').append(html);}
});
return false;
}

function frmHrsRemoveLogicRow(){
    if(jQuery(this).closest('.frm_hrs_logic_rows').find('.frm_hrs_logic_row').length==1) var c=',.frm_hrs_logic_label';
    else var c='';
    jQuery('#'+jQuery(this).closest('.frm_hrs_logic_row').attr('id')+c).fadeOut(1000, function(){
        jQuery(this).closest('.frm_hrs_logic_row').replaceWith('');
    });
    return false;
}

function frmHrsAddTagRow(tag){
var len=jQuery('.hrs_'+tag+'_row').size()+1;
jQuery.ajax({
    type:"POST",url:ajaxurl,
    data:"action=frm_hrs_add_tag_row&form_id=<?php echo $values['id'] ?>&meta_name="+len+"&tag="+tag,
    success:function(html){jQuery('.hrs_'+tag+'_row:last').after(html);}
});    
}

function frmHrsGetFieldValues(field_id,row){ 
    if(field_id){
    jQuery.ajax({
        type:"POST",url:ajaxurl,
        data:"action=frm_hrs_get_field_values&form_id=<?php echo $values['id'] ?>&field_id="+field_id+'&meta_name='+row,
        success:function(msg){jQuery("#frm_hrs_show_selected_values_"+row).html(msg);} 
    });
    }
}
</script>