/**
 * Constant Contact settings JavaScript. Loaded on both Global Settings and in Form Settings pages.
 */
var FrmFormsCtct = window.FrmFormsCtct || ( function( document, _, $ ) {

	/*global jQuery:false, frmGlobal */

	/**
	 * Public functions and properties.
	 *
	 * @type {Object}
	 */
	const app = {
		init: function() {
			document.addEventListener( 'click', handleClickEvent );

			const settingsAnchor = document.querySelector( 'a[href="#constantcontact_settings"]' );
			if ( settingsAnchor ) {
				showCtctWarningAfterTabChange( settingsAnchor );
			}
		}
	};

	function handleClickEvent( event ) {
		if ( 'clrcache-constantcontact' === event.target.id ) {
			event.preventDefault();
			clearCtctCache();
			return;
		}
	}

	function clearCtctCache() {
		$( '.clrcache-constantcontact-spinner' ).css( 'visibility', 'visible' );
		data = {
			action: 'clear_ctct_lists_cache',
			security: frmctctGlobal.nonce
		};
		$.post( ajaxurl, data, () => {
			$( '.clrcache-constantcontact-spinner' ).css( 'visibility', 'hidden' );
			location.reload();
		} );
	}

	/**
	 * Warnings all get hidden on tab change. Make sure that the warning for legacy Authorization service is shown when the tab is clicked.
	 * @param {Element} settingsAnchor 
	 * @returns {void}
	 */
	function showCtctWarningAfterTabChange( settingsAnchor ) {
		const li = settingsAnchor.parentNode;
		if ( 'LI' !== li.nodeName || ! li.parentNode.classList.contains( 'frm-category-tabs' ) ) {
			return;
		}

		const observer = new MutationObserver( handleAttributeMutation );
		const oberserverAtts = {
			attributes:    true,
			childList:     false,
			characterData: false
		};
		observer.observe( li, oberserverAtts );

		function handleAttributeMutation() {
			if ( ! li.classList.contains( 'active' ) ) {
				return;
			}

			const settings = document.getElementById( 'constantcontact_settings' );
			if ( ! settings ) {
				return;
			}

			const warning = settings.querySelector( '.frm_warning_style' );
			if ( ! warning ) {
				return;
			}

			const cta = warning.querySelector( 'a' );
			if ( ! cta || -1 === cta.href.indexOf( 'action=frm_ctct_auth_url' ) ) {
				return;
			}

			warning.style.display = 'block'; // Prevent global admin JavaScript from hiding legacy Authorization service warning on tab change.
		}
	}

	// Provide access to public functions/properties.
	return app;

}( document, window, jQuery ) );
FrmFormsCtct.init();
