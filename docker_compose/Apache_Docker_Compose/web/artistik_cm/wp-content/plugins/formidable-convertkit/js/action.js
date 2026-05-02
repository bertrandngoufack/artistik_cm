( function() {
	'use strict';

	const Action = {

		init: function() {
			this.handleCustomFields();
			this.handleActionDropdown();
			this.handleResetDropdown();
		},

		handleCustomFields: function() {
			const onClickAdd = event => {
				const rowsEl = event.target.closest( '.frm-cvk-custom-fields-rows' );
				const actionId = event.target.closest( '.frm_form_action_settings' ).dataset.actionkey;
				const lastRowEl = rowsEl.querySelector( '.frm_postmeta_row:last-child' );
				const rowNum    = lastRowEl.id.replace( 'fields_', '' ).replace( '_' + actionId, '' );
				const nextRowNum = parseInt( rowNum ) + 1;

				rowsEl.appendChild( cloneNewRow( lastRowEl, nextRowNum ) );
			};

			const cloneNewRow = ( rowEl, newRowNum ) => {
				const newRowEl = rowEl.cloneNode( true );

				newRowEl.id = newRowEl.id.replace( /(fields_)(\d+)(_\d+)/, '$1' + newRowNum + '$3' );

				const keyEl   = newRowEl.querySelector( '.frm-cvk-custom-fields-key' );
				const valueEl = newRowEl.querySelector( '.frm-cvk-custom-fields-value' );

				keyEl.id   = keyEl.id.replace( /(fields_key_)(\d+)(_\d+)/, '$1' + newRowNum + '$3' );
				valueEl.id = valueEl.id.replace( /(fields_value_)(\d+)(_\d+)/, '$1' + newRowNum + '$3' );

				keyEl.name   = keyEl.name.replace( /\[(\d+)\]\[key\]/, '[' + newRowNum + '][key]' );
				valueEl.name = valueEl.name.replace( /\[(\d+)\]\[value\]/, '[' + newRowNum + '][value]' );

				return newRowEl;
			};

			frmDom.util.documentOn( 'click', '.frm-cvk-add-custom-field-row', onClickAdd );
		},

		handleResetDropdown: function() {
			const loadingClass = 'frm-cvk-is-loading';

			const onReloadSetting = event => {
				event.preventDefault();

				event.target.parentElement.classList.add( loadingClass );

				let selectEls;

				if ( event.target.previousElementSibling && 'SELECT' === event.target.previousElementSibling.tagName ) {
					selectEls = [ event.target.previousElementSibling ];
				} else {
					selectEls = event.target.closest( '.frm_add_remove' ).querySelectorAll( '.frm-cvk-custom-fields-rows select' );
				}

				selectEls.forEach( select => {
					select.setAttribute( 'disabled', 'disabled' );
				});

				frmDom.ajax.doJsonFetch( 'cvk_fetch&method=' + event.target.dataset.method )
					.then( response => {
						selectEls.forEach( select => {
							removeSelectOption( select );
							appendOptions( select, response, event );
							select.removeAttribute( 'disabled' );
						});
						event.target.parentElement.classList.remove( loadingClass );
					})
					.catch( response => console.log( response ) );
			};

			const removeSelectOption = select => {
				const options = select.querySelectorAll( 'option' ); // Do not use select.options, it causes side effect.
				if ( options.length < 2 ) {
					return;
				}

				for ( let i = 1; i < options.length; i++ ) {
					options[ i ].remove();
				}
			};

			const appendOptions = ( select, data, event ) => {
				const value = event.target.dataset.value || 'id';
				const label = event.target.dataset.label || 'name';
				data.forEach( item => {
					const option = frmDom.tag( 'option', item[ label ]);
					option.setAttribute( 'value', item[ value ]);
					select.appendChild( option );
				});
			};

			frmDom.util.documentOn( 'click', '.frm-cvk-reload', onReloadSetting );
		},

		handleActionDropdown: function() {
			const onChangeAction = event => {
				event.target.closest( '.frm-cvk-action-settings' ).setAttribute( 'data-action', event.target.value );
			};

			frmDom.util.documentOn( 'change', '.frm-cvk-action-dropdown', onChangeAction );
		}
	};

	Action.init();
}() );
