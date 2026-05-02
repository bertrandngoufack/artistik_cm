( function() {

	const { doJsonPost } = frmDom.ajax;

	function addEventListeners() {
		document.addEventListener(
			'click',
			function( e ) {
				if ( typeof e.target.className.includes !== 'undefined' && e.target.className.includes( 'frm_add_watch_ai_row' ) ) {
					addWatchAIRow(e);
				}
			}
		);

	}

	function addWatchAIRow( e ) {
		const id = e.target.closest( '.frm-single-settings' ).dataset.fid;
		const aiBlockRows = document.getElementById( 'frm_watch_ai_block_' + id ).children;

		const formData  = new FormData();
		formData.append( 'form_id',document.getElementById( 'form_id' ).value );
		formData.append( 'field_id', id );
		formData.append( 'row_key', getNewRowId( aiBlockRows, 'frm_watch_ai_' + id + '_' ) );

		doJsonPost( 'add_watch_ai_row', formData ).then(
			response => {
				const watchRowBlock = document.getElementById( 'frm_watch_ai_block_' + id );
				watchRowBlock.insertAdjacentHTML( 'beforeend', response );
				watchRowBlock.style.display = 'block';
			}
		);

		return false;
	}

	function getNewRowId( rows, replace, defaultValue ) {
		if ( ! rows.length ) {
			return 'undefined' !== typeof defaultValue ? defaultValue : 0;
		}
		return parseInt( rows[ rows.length - 1 ].id.replace( replace, '' ), 10 ) + 1;
	}

	addEventListeners();
}() );
