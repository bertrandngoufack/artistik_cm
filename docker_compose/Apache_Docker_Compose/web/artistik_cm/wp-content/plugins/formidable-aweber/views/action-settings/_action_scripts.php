<script type="text/javascript">
jQuery(document).ready(function($){
$('#aweber_settings').on('change', 'select[name="awbr_list[]"]', frmAwbrFields);
});

function frmAwbrFields(id,htmlid){
	var form_id = jQuery('input[name="id"]').val(),
		$dropdown = jQuery(this),
    	id = $dropdown.val(),
		key = $dropdown.closest('.frm_single_aweber_settings').data('actionkey');
    var htmlid = $dropdown.attr('id').replace('select_list_', '');
    var div = $dropdown.closest('.awbr_list').find('.frm_awbr_fields');
    div.empty().append('<img class="frm_awbr_loading_field" src="'+ frm_js.images_url +'/wpspin_light.gif" alt="'+ frm_js.loading +'" style="display:none;"/>');
    jQuery('.frm_awbr_loading_field').fadeIn('slow');
    jQuery.ajax({
        type:'POST',url:ajaxurl,
        data:{action:'frm_awbr_match_fields',form_id:form_id,list_id:id,action_key:key},
        success:function(html){jQuery('.frm_awbr_loading_field').replaceWith(html).fadeIn('slow');}
    });
}

</script>