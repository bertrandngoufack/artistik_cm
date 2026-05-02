( function() {
	'use strict';

	/**
	 * Shows the custom confirmation modal if possible.
	 *
	 * @param {String}   message  Confirm message.
	 * @param {Function} callback Callback function.
	 *
	 * @return {Void}
	 */
	function showConfirm( message, callback ) {
		const wrapper = document.querySelector( '.frm_wrap' );
		if ( ! wrapper ) {
			if ( confirm( message ) ) {
				callback();
			}
			return;
		}

		const temporaryAnchor = document.createElement( 'a' );
		temporaryAnchor.setAttribute( 'data-frmverify', message );
		temporaryAnchor.setAttribute( 'data-frmverify-btn', 'frm-button-red' );
		temporaryAnchor.setAttribute( 'href', '#' );

		wrapper.appendChild( temporaryAnchor );
		temporaryAnchor.click();
		wrapper.removeChild( temporaryAnchor );

		const confirmButton = document.getElementById( 'frm-confirmed-click' );

		const onConfirm = event => {
			event.preventDefault();
			callback();
			const dismissButton = document.getElementById( 'frm_confirm_modal' ).querySelector( '.dismiss' );
			dismissButton.click();
		}

		if ( confirmButton ) {
			confirmButton.addEventListener( 'click', onConfirm, false );
		}
	}

	/**
	 * Appends new item to the list.
	 *
	 * @param {HTMLElement} wrapperEl The form action wrapper.
	 * @param {String}      key       Item key.
	 * @param {String}      value     Item value.
	 *
	 * @return {Void}
	 */
	function appendNewItem( wrapperEl, key, value ) {
		const listEl = wrapperEl.querySelector( '.frm-n8n-mapping-list' );
		const tmplEl = wrapperEl.querySelector( '.frm-n8n-mapping-item-tmpl' );
		const newItem = tmplEl.firstElementChild.cloneNode( true );

		if ( key ) {
			newItem.querySelector( '.frm-n8n-mapping-item-key' ).value = key;
		}

		const valueInput = newItem.querySelector( '.frm-n8n-mapping-item-value' );

		if ( value ) {
			valueInput.value = value;
		}

		// Need to set a unique ID to make the shortcodes box work.
		valueInput.id = 'frm-n8n-mapping-item-value-' + Math.round( Math.random() * 999 );

		listEl.appendChild( newItem );
	}

	frmDom.util.documentOn( 'click', '.frm-n8n-add-mapping-item', e => {
		e.preventDefault();
		appendNewItem( e.target.closest( '.frm-n8n-mapping-wrapper' ) );
	});

	frmDom.util.documentOn( 'click', '.frm-n8n-remove-mapping-item', e => {
		e.preventDefault();
		e.target.closest( '.frm-n8n-mapping-item' ).remove();
	});

	frmDom.util.documentOn( 'click', '.frm-n8n-add-all-mapping-items', e => {
		e.preventDefault();
		if ( ! e.target.dataset.fields ) {
			return;
		}

		const wrapperEl  = e.target.closest( '.frm-n8n-mapping-wrapper' );
		let currentItems = wrapperEl.querySelectorAll( '.frm-n8n-mapping-list .frm-n8n-mapping-item' );

		const appendAll = () => {
			try {
				const fields = JSON.parse( e.target.dataset.fields );

				fields.forEach( field => {
					appendNewItem( wrapperEl, field.key, '[' + field.id + ']' );
				} );
			} catch ( error ) {
				console.log( error );
			}
		}

		if ( ! currentItems || ! currentItems.length ) {
			// Do not need to show the confirmation modal if there is no current items.
			appendAll();
			return;
		}

		showConfirm(
			frmN8NAdminI18n.addAllFieldsConfirm,
			() => {
				// Query these again to prevent caching.
				currentItems = wrapperEl.querySelectorAll( '.frm-n8n-mapping-list .frm-n8n-mapping-item' );
				currentItems.forEach( item => item.remove() );
				appendAll();
			}
		);
	});
}() );
