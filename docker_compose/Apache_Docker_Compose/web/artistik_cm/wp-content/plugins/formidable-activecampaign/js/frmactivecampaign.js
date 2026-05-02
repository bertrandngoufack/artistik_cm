/*global jQuery:false, frmactivecampaignGlobal, ajaxurl */

( function( $ ) {
	$( function() {
		$( 'body' ).on( 'click', '.clrcache-activecampaign', function( event ) {
			var data;
			event.preventDefault();
			$( '.clrcache-activecampaign-spinner' ).css( 'visibility', 'visible' );
			data = {
				action: 'clear_activecampaign_fields_cache',
				security: frmactivecampaignGlobal.nonce
			};
			$.post( ajaxurl, data, function() {
				$( '.clrcache-activecampaign-spinner' ).css( 'visibility', 'hidden' );
				location.reload();
			} );
		} );
	} );
} )( jQuery );
