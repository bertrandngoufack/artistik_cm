<script type="text/javascript">
jQuery(document).ready(function($){
$('#frm_notification_settings').on('click', '.frm_single_highrise_settings .frm_add_tag', frmHrsAddTagRow);
});

function frmHrsAddTagRow(){
    var tag = jQuery(this).data('tag');
    var form_id = jQuery('input[name="id"]').val();
    var cont = jQuery(this).closest('.frm_single_highrise_settings');
    var len = cont.find('.hrs_'+tag+'_row:last').data('tag-key')+1;
    var key = cont.data('actionkey');
    jQuery.ajax({
        type:'POST',url:ajaxurl,
        data:{action:'frm_hrs_add_tag_row', form_id:form_id, meta_name:len, tag:tag, action_key:key},
        success:function(html){
            cont.find('.hrs_'+tag+'_row:last').after(html);
        }
    });
}
</script>