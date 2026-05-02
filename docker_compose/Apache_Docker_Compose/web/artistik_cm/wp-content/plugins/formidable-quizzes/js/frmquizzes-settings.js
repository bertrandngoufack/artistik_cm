( function( $ ) {
	'use strict';

	const __ = wp.i18n.__;

	const FrmQuizzesGradingScale = {
		init: function() {
			const self = this;
			$( 'body' ).on( 'click', '.grading-scale-row .frm_add_form_row', function( el ) {
				el.preventDefault();
				self.addRow();
			});

			frmDom.util.documentOn( 'click', '.frm_remove_form_row', e => {
				const target = e.target;
				if ( target.closest( '.frm_remove_form_row' ) ) {
					self.removeRow( target );
				}
			});
		},
		getNewGradingRowID: function() {
			const lastGradingScaleRowID = document.querySelector( '.grading-scale-row:last-of-type' ).id;
			const lastRowIndex = parseInt( lastGradingScaleRowID.split( '-' ).pop() ) + 1;
			return 'grading-scale-row-' + lastRowIndex;
		},
		addRow: function() {
			const self             = this;
			const gradingScaleRows = document.querySelector( '.grading-scale-rows' );
			const key              = gradingScaleRows.childElementCount; //Starts at 1
			const newRowID         = self.getNewGradingRowID();

			let newRowHtml = '<div id="' + newRowID + '" class="grading-scale-row">';
			newRowHtml += '<input class="small-text grade" type="text" name="frm_quizzes_grading_scale[' + key + '][grade]" />';
			newRowHtml += '<input class="small-text start" type="text" name="frm_quizzes_grading_scale[' + key + '][start]" />';
			newRowHtml += '<input class="small-text end" type="text" name="frm_quizzes_grading_scale[' + key + '][end]" />';
			newRowHtml += '<a href="#" class="frm_add_form_row" aria-label="' + __( 'Add', 'formidable-quizzes' ) + '">' + frmDom.svg({ href: '#frm_plus_icon' }).outerHTML + '</a>';
			newRowHtml += '<a href="#" data-removeid="' + newRowID + '" class="frm_remove_form_row" aria-label="' + __( 'Remove', 'formidable-quizzes' ) + '">' + frmDom.svg({ href: '#frm_minus_icon' }).outerHTML + '</a>';
			newRowHtml += '</div>';
			gradingScaleRows.insertAdjacentHTML( 'beforeend', newRowHtml );
			gradingScaleRows.lastElementChild.addEventListener( 'click', self.resetKeys );
		},
		removeRow: function( target ) {
			target.closest( '.grading-scale-row' ).remove();
			FrmQuizzesGradingScale.resetKeys();
		},
		resetKeys: function() {
			setTimeout( function() {
				$( '.grading-scale-rows .grading-scale-row' ).each( function( index ) {
					$( this ).find( 'input.grade' ).attr( 'name', 'frm_quizzes_grading_scale[' + index + '][grade]' );
					$( this ).find( 'input.start' ).attr( 'name', 'frm_quizzes_grading_scale[' + index + '][start]' );
					$( this ).find( 'input.end' ).attr( 'name', 'frm_quizzes_grading_scale[' + index + '][end]' );
				});
			}, 600 ); // delay a bit until the current row is removed before resetting input names.
		}
	};

	$( function() {
		FrmQuizzesGradingScale.init();
	});
}( jQuery ) );
