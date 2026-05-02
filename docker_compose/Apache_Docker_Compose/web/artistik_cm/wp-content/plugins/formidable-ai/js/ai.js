( function() {
	/* globals __FRMAI, frmFrontForm, jQuery */

	let aiSubmitting = false;
	let lastRequest = [];
	let aiSettings = [];
	const xhrRequests = {};

	if ( typeof jQuery !== 'undefined' ) {
		// Catch the Ajax submit if possible.
		jQuery(document).on( 'frmPageChanged', function(e, form, response) {
			if ( typeof __FRMAI  === 'undefined' || ! response.content.includes('frm_ai_response') ) {
				return;
			}
			maybeTriggerFirst();
		});
	}

	document.addEventListener( 'DOMContentLoaded', function() {
		init();
	});

	function init() {
		if ( typeof __FRMAI  === 'undefined' ) {
			return;
		}
		for (let i = 0; i < __FRMAI.length; i++) {
			let watchedFields = __FRMAI[i]['watch'];
			const fieldId = __FRMAI[i]['field'];
			aiSettings[fieldId] = __FRMAI[i];
			lastRequest[fieldId] = [];

			for (let i = 0; i < watchedFields.length; i++) {
				// Standardize the field name.
				let selector = watchedFields[i].replace( '#', '' );
				selector = selector.replace( '[id^="', '' ).replace( '-"]', '' ).replace( '_"]', '' );
				lastRequest[fieldId][selector] = '';
			}
			addListeners( fieldId );
		}
	}

	/**
	 * Trigger a check on the first watched field when the page is changed.
	 */
	function maybeTriggerFirst() {
		for (let i = 0; i < __FRMAI.length; i++) {
			if ( __FRMAI[i].trigger ) {
				document.querySelectorAll( __FRMAI[i].watch ).forEach( function(el, index) {
					if ( index === 0 ) {
						init();
						el.dispatchEvent(new Event('blur'));
					}
				});
			}
		}
	}

	function addListeners( fieldId ) {
		const settings = aiSettings[fieldId];
		document.querySelectorAll(settings.watch).forEach(function(el, index) {
			let allowed = ['INPUT', 'TEXTAREA', 'SELECT'];
			if ( ! allowed.includes( el.tagName ) ) {
				return;
			}

			el.addEventListener('blur', function(e){
				aiGetAnswer(e, fieldId);
			});

			document.addEventListener('frmShowField', function() {
				// We don't know which field this is from, so check.
				el.dispatchEvent(new Event('blur'));
			});

			if ( settings.trigger ) {
				// Trigger a check on each watched field when the form is loaded.
				el.dispatchEvent(new Event('blur'));
			}

			if ( index === 0 ) {
				// Catch the submit event and trigger an API check if possible.
				el.closest('form').addEventListener('submit', function(e) {
					e.preventDefault();

					const answerField = document.getElementById( settings.id );
					jQuery(document).off('submit.formidable', '.frm-show-form', frmFrontForm.submitForm);
					if ( aiHasAnswer(answerField) ) {
						jQuery(document).on('submit.formidable', '.frm-show-form', frmFrontForm.submitForm);
					} else {
						aiSubmitting = e;
						el.dispatchEvent(new Event('blur'));
						const hasEmptyValue = Object.values(lastRequest[fieldId]).some(function(v){
							if (v.trim() === '') {
								return true;
							}
						});
						if ( hasEmptyValue ) {
							jQuery(document).on('submit.formidable', '.frm-show-form', frmFrontForm.submitForm);
						}
					}
				});
			}
		});
	}

	function aiHasAnswer( answerField ) {
		if (! answerField || ! answerField.value) {
			return false;
		}
		const defaultVal = answerField.getAttribute('data-frmval');
		return defaultVal !== answerField.value;
	}

	function aiGetAnswer( e, fieldId ) {
		const target = e.target || e.srcElement;
		const fieldValue = getFieldVal( target );
		const fieldCall  = getStandardName( target.id, fieldId );

		if ( lastRequest[fieldId][ fieldCall ] === fieldValue || fieldValue === '' ) {
			// Don't check if the value hasn't changed.
			return;
		}

		const settings = aiSettings[fieldId];
		if ( isFieldConditionallyHidden( settings.field ) || typeof lastRequest[fieldId][fieldCall] === 'undefined' ) {
			// Don't check if the field is hidden or if it's not in the lastRequest array.
			return;
		}

		lastRequest[fieldId][ fieldCall ] = fieldValue;
		const hasEmptyValue = Object.values( lastRequest[fieldId] ).some( function(v) {
			return v.trim() === '';
		});

		const aiForm = target.closest('form');

		if ( hasEmptyValue || aiIsSpam( aiForm ) ) {
			return;
		}

		const showAnswer = document.getElementById( 'frm_ai_response_' + fieldId );
		const answerField = document.getElementById( settings.id );

		// Show loading indicator.
		aiForm.classList.add('frm_loading_form');
		aiShowLoader( showAnswer );

		const xhr = new XMLHttpRequest();
		if ( 'object' === typeof xhrRequests[ fieldId ] && xhrRequests[ fieldId ] instanceof XMLHttpRequest && 'function' === typeof xhrRequests[ fieldId ].abort ) {
			xhrRequests[ fieldId ].abort();
		}

		xhrRequests[ fieldId ] = xhr;

		xhr.open( 'post', settings.ajax + '?action=frm_ai_get_answer' ), true;
		xhr.onreadystatechange = function() {
			if ( xhr.readyState > 3 && xhr.status == 200 ) {
				let response = xhr.responseText;
				handleResponse( response, answerField, showAnswer, aiForm );
			}
		}
		xhr.setRequestHeader( 'X-Requested-With', 'XMLHttpRequest' );
		xhr.setRequestHeader( 'Content-Type', 'application/json' );
		xhr.send( JSON.stringify({
			question: Object.values(lastRequest[fieldId]).join( ' ' ).replace(/\n/g, ' '),
			prompt: answerField.getAttribute('data-ai-prompt'),
			token: aiToken(aiForm),
			id: answerField.getAttribute('data-form-id'),
		}));
	}

	/**
	 * Get values for dropdowns, checkboxes, and radio buttons.
	 *
	 * @param {object} target
	 * @returns string
	 */
	function getFieldVal( target ) {
		let value = target.value;
		let checked = null;
		let comboContainer = target.parentElement.parentElement;

		if ( comboContainer.classList.contains( 'frm_combo_inputs_container' ) ) {
			value = '';
			comboContainer.querySelectorAll( 'input, select, textarea' ).forEach( function( el ) {
				value += ' ' + el.value;
			});
		} else if ( target.type === 'checkbox' || target.type === 'radio' ) {
			checked = document.querySelectorAll( '[name="' + target.name + '"]:checked' );
		} else if ( target.type === 'select' ) {
			checked = target.querySelectorAll( 'option:checked' );
		}

		if ( checked ) {
			value = '';
			checked.forEach( function( el ) {
				value += el.value + "\n";
			});
		}

		return value;
	}

	/**
	 * Get the standard field name from the field id.
	 *
	 * @param {string} fieldCall
	 * @param {number} fieldId
	 * @returns string
	 */
	function getStandardName( fieldCall, fieldId ) {
		if ( lastRequest[fieldId][fieldCall] === undefined ) {
			// Loop through existing request and find the key that matches fieldCall.
			for ( let key in lastRequest[fieldId] ) {
				if ( fieldCall.startsWith( key ) ) {
					fieldCall = key;
					break;
				}
			}
		}
		return fieldCall;
	}

	function handleResponse( response, answerField, showAnswer, aiForm ) {
		if ( response !== '' ) {
			response = JSON.parse( response );
		}

		if ( typeof response.data === 'object' ) {
			answerField.value = Object.values( response.data ).join( " \r\n" );
		} else if ( typeof response.data === 'array' ) {
			answerField.value = response.data.join( " \r\n" );
		} else if ( response.data ) {
			answerField.value = response.data;
		}

		if ( showAnswer !== null ) {
			showAnswer.textContent = ''; // Remove loading indicator.
		}

		if ( response.success ) {
			answerField.dispatchEvent( new Event( 'change', {bubbles: true} ) );
			if ( showAnswer === null && aiSubmitting ) {
				frmFrontForm.submitFormManual( aiSubmitting, aiForm );
			} else if( showAnswer !== null ) {
				showAnswer.closest('.frm_form_field').classList.remove('frm_none_container');
				showAnswer.classList.add('frm_ai_answer');
				for (let i = 0; i < response.data.length; i++) {
					const p = document.createElement('p');
					const text = document.createTextNode(response.data[i]);
					p.appendChild(text);
					showAnswer.appendChild(p);
				}
			}
		}
		aiForm.classList.remove('frm_loading_form');
	}

	function aiShowLoader( showAnswer ) {
		if (showAnswer !== null) {
			const loader = showAnswer.querySelector('.frm_ai_loading');
			if (loader !== null){
				loader.style.display = 'block';
			}
			const frmDefault = showAnswer.querySelector('.frm_ai_default');
			if (frmDefault !== null){
				frmDefault.style.display = 'none';
			}
		}
	}

	function aiIsSpam( form ) {
		if ( aiIsHeadless() ) {
			return true;
		}
		let formID = form.parentElement.id.replace('frm_form_', '').replace('_container', '');
		let check = document.getElementById('frm_email_' + formID);
		if ( check === null ) {
			check = document.getElementById('frm_verify_' + formID);
		}
		return check !== null && check.value !== '';
	}

	function aiIsHeadless() {
		return (
			window._phantom || window.callPhantom || window.__phantomas ||
			window.Buffer || window.emit || window.spawn
		);
	}

	function aiToken( form ) {
		return form.getAttribute('data-token');
	}

	function isFieldConditionallyHidden( fieldId ) {
		let container = document.getElementById( 'frm_field_' + fieldId + '_container' );
		return container && container.style.display === 'none';
	}
}() );
