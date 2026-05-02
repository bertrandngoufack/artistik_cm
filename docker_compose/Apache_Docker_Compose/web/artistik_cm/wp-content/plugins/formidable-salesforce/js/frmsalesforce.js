/*global jQuery:false, frmsalesforceGlobal, ajaxurl */
( function( $ ) {
	$( function() {
		var settings = $( '#frm_notification_settings' );
		settings.on( 'change', '.frm_single_salesforce_settings select[name$="[object_id]"]', function() {
			var formId = jQuery( 'input[name="id"]' ).val();
			var id = jQuery( this ).val();
			var key = jQuery( this ).closest( '.frm_single_salesforce_settings' ).data( 'actionkey' );
			var div = jQuery( this ).closest( '.salesforce_object' ).find( '.frm_salesforce_fields' );
			jQuery( '.frm_salesforce_loading_field' ).fadeIn( 'slow' );
			jQuery.ajax( {
				type: 'POST', url: ajaxurl,
				data: {
					action: 'frm_salesforce_match_fields',
					form_id: formId,
					object_id: id,
					action_key: key,
					security: frmsalesforceGlobal.nonce
				 },
				success: function( html ) {
					div.html( html ).fadeIn( 'slow' );
					jQuery( '.frm_salesforce_loading_field' ).fadeOut( 'slow' );
				}
			} );
		} );
	} );
} )( jQuery );
