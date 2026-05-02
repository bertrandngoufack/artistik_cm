( function() {
	'use strict';

	frmDom.util.documentOn( 'click', '#frm-cvk-test-api', function( event ) {
		const loadingClass = 'frm-cvk-is-loading';
		const successClass = 'frm-cvk-is-success';
		const failedClass  = 'frm-cvk-is-failed';
		const emptyClass   = 'frm-cvk-is-empty';

		const wrapperEl = event.target.closest( 'td' );
		wrapperEl.classList.remove( successClass, failedClass, emptyClass );

		const apiSecret = document.getElementById( 'frm-cvk-api-secret' ).value;
		if ( ! apiSecret ) {
			wrapperEl.classList.add( emptyClass );
			return;
		}

		wrapperEl.classList.add( loadingClass );

		const request = fetch( 'https://api.convertkit.com/v3/account?api_secret=' + apiSecret );
		request.then( function( response ) {
			if ( 200 === response.status ) {
				wrapperEl.classList.add( successClass );
			} else {
				wrapperEl.classList.add( failedClass );
			}

			wrapperEl.classList.remove( loadingClass );
		});
	});
}() );
