( function() {
	/**
	 * Track the pending fill data.
	 * All pending data is sent in one request (to retrieve an AI response).
	 */
	let pendingFillData = [];

	/**
	 * Track the delayed fill data.
	 * This data is already pre-determined, but the input is not yet updated until the AI request is complete.
	 */
	let delayedFillData = [];

	/**
	 * Track the confirmation fields.
	 * These will use the same values as their target field.
	 */
	let delayedConfirmationFields = [];

	let quickJumpWasJustClicked = false;
	let promptHasBeenShown = false;
	let shouldPromptToDisableRequiredFieldValidation = () => {
		if ( ! frmTestModeVars.hasRequiredFields || frmTestModeVars.hasPostedData || promptHasBeenShown || ! quickJumpWasJustClicked ) {
			return false;
		}

		return ! document.getElementById( 'frm_testmode_disable_required_fields' ).checked;
	};

	let promptModal;

	document.addEventListener( 'change', function( event ) {
		if ( event.target ) {
			switch ( event.target.id ) {
				case 'frm_testmode_preview_role':
					handlePreviewRoleChange( event );
					break;
				case 'frm_testmode_show_all_hidden_fields':
					handleShowAllHiddenFieldsChange( event );
					break;
				case 'frm_testmode_disable_required_fields':
					handleDisableRequiredFieldsChange( event );
					break;
			}
		}
	} );

	const form = document.querySelector( 'form.frm-show-form' );
	if ( form ) {
		form.addEventListener( 'frmBeforeNewRepeaterRow', function( event ) {
			event.frmData.frm_testmode = Array.from( document.querySelectorAll( '[name^="frm_testmode["]' ) ).reduce(
				function( acc, input ) {
					let useValue;
					if ( 'checkbox' === input.type ) {
						useValue = input.checked ? '1' : '0';
					} else {
						useValue = input.value;
					}
					acc[ input.name.replace( 'frm_testmode[', '' ).replace( ']', '' ) ] = useValue;
					return acc;
				},
				{}
			);
		} );

		jQuery( form ).on( 'submit', function( event ) {
			if ( ! shouldPromptToDisableRequiredFieldValidation() ) {
				return;
			}

			promptHasBeenShown = true;
			quickJumpWasJustClicked = false;

			promptModal = frmDom.modal.maybeCreateModal(
				'frm_testmode_disable_required_fields_modal',
				{
					title: 'Disable Required Field Validation',
					content: getPromptToDisableRequiredFieldValidationModalContent(),
					footer: getPromptToDisableRequiredFieldValidationModalFooter()
				}
			);

			event.preventDefault();
			event.stopPropagation();
			return false;
		} );
	}

	function getPromptToDisableRequiredFieldValidationModalContent() {
		const modalContent = frmDom.div({
			children: [
				frmDom.tag( 'p', 'This form includes required fields that may prevent jumping to this page.' ),
				frmDom.tag( 'p', 'Would you like to disable required field validation as well?' ),
			]
		});
		modalContent.querySelector( 'p' ).style.marginTop = 0;
		modalContent.querySelector( 'p:last-of-type' ).style.marginBottom = 0;
		modalContent.style.padding = 'var(--gap-md)';
		return modalContent;
	}

	function getPromptToDisableRequiredFieldValidationModalFooter() {
		const yesButton = frmDom.modal.footerButton({
			text: 'Yes',
			buttonType: 'primary'
		});
		const noButton  = frmDom.modal.footerButton(
			{
				text: 'No',
				buttonType: 'secondary'
			}
		);
		noButton.style.marginRight = 'var(--gap-xs)';

		yesButton.addEventListener( 'click', function() {
			document.getElementById( 'frm_testmode_disable_required_fields' ).checked = true;
			disableRequiredJsValidation();
			jQuery( promptModal ).dialog( 'close' );
			jQuery( form ).submit();
		} );

		noButton.addEventListener( 'click', function() {
			jQuery( promptModal ).dialog( 'close' );
			jQuery( form ).submit();
		} );

		const modalFooter = frmDom.div({ children: [ noButton, yesButton ] });
		return modalFooter;
	}

	function listenForPaginationButtonClicks() {
		const allPaginationButtons = document.querySelectorAll( '#frm_test_mode_pagination input[type="button"]' );
		const listener = function() {
			quickJumpWasJustClicked = true;
	
			allPaginationButtons.forEach( function( button ) {
				button.removeEventListener( 'click', listener );
			} );
		};
		allPaginationButtons.forEach( function( button ) {
			button.addEventListener( 'click', listener );
		} );
	}

	listenForPaginationButtonClicks();

	document.addEventListener( 'click', function( event ) {
		if ( ! event.target ) {
			return;
		}

		if ( event.target.id === 'frm_testmode_fill_in_empty_form_fields' ) {
			event.preventDefault();

			if ( ! event.target.classList.contains( 'frm_noallow' ) ) {
				handleFillInEmptyFormFields( event );
			}

			return;
		}

		if ( event.target.id === 'frm_testmode_start_over' ) {
			maybeReloadPageOnStartOverClick( event );
		}
	} );

	maybeDisableJsValidation();

	jQuery( document ).on( 'frmPageChanged', function() {
		maybeDisableJsValidation();
	} );

	function maybeDisableJsValidation() {
		const checkbox = document.getElementById( 'frm_testmode_disable_required_fields' );
		if ( checkbox && checkbox.checked ) {
			disableRequiredJsValidation();
		}
	}

	/**
	 * Maybe reload the page on start over click.
	 * The start over button might not work if no form is shown (when a success or error message is shown instead).
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	function maybeReloadPageOnStartOverClick( event ) {
		const successMessage = document.querySelector( '.frm_forms > .frm_message' );
		const errorMessage   = document.querySelector( '.frm_forms > .frm_error_style' );
		if ( ! successMessage && ! errorMessage ) {
			// Check URL for test mode context.
			const url            = new URL( window.location.href );
			const keysToCheckFor = [
				'frm_testmode_show_all_hidden_fields',
				'frm_testmode_role',
				'frm_testmode_enabled_form_actions',
				'frm_testmode_disable_required_fields'
			];

			if ( keysToCheckFor.some( key => url.searchParams.has( key ) ) ) {
				keysToCheckFor.forEach( key => url.searchParams.delete( key ) );
				window.location.href = url.toString();
				return;
			}

			return;
		}

		event.preventDefault();

		const url = new URL( window.location.href );
		setTestModeContextInUrl( url );
		window.location.href = url.toString();
	}

	/**
	 * Handle preview role change.
	 *
	 * @param {Event} event
	 * @return {void}
	 */
	function handlePreviewRoleChange( event ) {
		const url = new URL( window.location.href );
		setTestModeContextInUrl( url );
		window.location.href = url.toString();
	}

	/**
	 * Handle Show all hidden fields change.
	 *
	 * @param {Event} event
	 * @return {void}
	 */
	function handleShowAllHiddenFieldsChange( event ) {
		const url = new URL( window.location.href );
		setTestModeContextInUrl( url );
		window.location.href = url.toString();
	}

	/**
	 * @param {URL} url 
	 * @return {void}
	 */
	function setTestModeContextInUrl( url ) {
		const disabledFieldsCheckbox   = document.getElementById( 'frm_testmode_disable_required_fields' );
		const showHiddenFieldsCheckbox = document.getElementById( 'frm_testmode_show_all_hidden_fields' );
		const previewRoleDropdown      = document.getElementById( 'frm_testmode_preview_role' );

		if ( disabledFieldsCheckbox.checked ) {
			url.searchParams.set( 'frm_testmode_disable_required_fields', '1' );
		} else {
			url.searchParams.delete( 'frm_testmode_disable_required_fields' );
		}

		if ( showHiddenFieldsCheckbox.checked ) {
			url.searchParams.set( 'frm_testmode_show_all_hidden_fields', '1' );
		} else {
			url.searchParams.delete( 'frm_testmode_show_all_hidden_fields' );
		}

		if ( previewRoleDropdown.value ) {
			url.searchParams.set( 'frm_testmode_role', previewRoleDropdown.value );
		} else {
			url.searchParams.delete( 'frm_testmode_role' );
		}

		const enabledFormActions  = document.getElementById( 'frm_testmode_enabled_form_actions' );
		if ( enabledFormActions ) {
			const enabledFormActionIdsCsv = jQuery( enabledFormActions ).val();
			if ( enabledFormActionIdsCsv.length ) {
				url.searchParams.set( 'frm_testmode_enabled_form_actions', enabledFormActionIdsCsv );
			} else {
				url.searchParams.set( 'frm_testmode_enabled_form_actions', '-1' );
			}
		}
	}

	/**
	 * When JavaScript validation is enabled, remove the frm_required_field class so the required field
	 * validation does not happen client side.
	 *
	 * @param {Event} event
	 * @return {void}
	 */
	function handleDisableRequiredFieldsChange( event ) {
		const jsValidate = document.querySelector( '.frm_js_validate' );
		if ( ! jsValidate ) {
			return;
		}

		if ( event.target.checked ) {
			disableRequiredJsValidation();
		} else {
			enableRequiredJsValidation();
		}
	}

	function disableRequiredJsValidation() {
		const requiredFields = document.querySelectorAll( '.frm_required_field' );
		requiredFields.forEach( ( field ) => {
			field.classList.remove( 'frm_required_field' );
			field.classList.add( 'frm_disabled_required_field' );
		} );
	}

	function enableRequiredJsValidation() {
		const requiredFields = document.querySelectorAll( '.frm_disabled_required_field' );
		requiredFields.forEach( ( field ) => {
			field.classList.add( 'frm_required_field' );
			field.classList.remove( 'frm_disabled_required_field' );
		} );
	}

	/**
	 * Handle fill in empty form fields.
	 *
	 * @param {Event} event
	 * @return {void}
	 */
	function handleFillInEmptyFormFields( event ) {
		event.preventDefault();

		const fieldsContainer = document.querySelector( '.frm_fields_container' );
		if ( ! fieldsContainer ) {
			return;
		}

		pendingFillData = [];
		delayedFillData = [];
		delayedConfirmationFields = [];

		fillTextFields( fieldsContainer );
		fillTextAreaFields( fieldsContainer );
		fillPhoneFields( fieldsContainer );
		fillUrlFields( fieldsContainer );
		fillEmailFields( fieldsContainer );
		fillRichTextFields( fieldsContainer );

		if ( pendingFillData.length > 0 ) {
			makeAIRequestForFillData();
		} else {
			fillFieldsThatDoNotRelyOnAi();
		}
	}

	function fillFieldsThatDoNotRelyOnAi() {
		const fieldsContainer = document.querySelector( '.frm_fields_container' );
		if ( ! fieldsContainer ) {
			return;
		}

		// These fields are delayed but not sent in the requests.
		// This way all auto-filling happens at the same time.
		fillNumberFields( fieldsContainer );
		fillRadioFields( fieldsContainer );
		fillCheckboxFields( fieldsContainer );
		fillSelectFields( fieldsContainer );

		if ( delayedFillData.length > 0 ) {
			delayedFillData.forEach( function( field ) {
				field.input.value = field.value;
				field.input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			} );
		}
	}

	function makeAIRequestForFillData() {
		const container = document.getElementById( 'frm_testing_mode' );
		if ( ! container ) {
			return;
		}

		container.classList.add( 'frm_autofilling_form' );
		const autofillButton = document.getElementById( 'frm_testmode_fill_in_empty_form_fields' );
		autofillButton?.classList.add( 'frm_loading_button' );

		// Make an AI request to fill in the pending fields.
		const formData = new FormData();
		formData.append( 'action', 'frm_testmode_fill_in_empty_form_fields' );
		formData.append( 'pendingFillData', JSON.stringify( preparePendingFillDataForRequest() ) );

		doJsonPost( formData ).then(
			function( data ) {
				container.classList.remove( 'frm_autofilling_form' );
				autofillButton?.classList.remove( 'frm_loading_button' );

				if ( data.success ) {
					populateFieldsWithAutofillAiResponse( data.data );
					populateConfirmationFieldsWithAiResponse( data.data );
					fillFieldsThatDoNotRelyOnAi();
				}  else if ( 'string' === typeof data.data ) {
					showAiError( data.data );
				}
			}
		);
	}

	/**
	 * Show an error modal with the given message.
	 *
	 * @param {String} message
	 * @return {Void}
	 */
	function showAiError( message ) {
		const modalContent = frmDom.div( message );
		modalContent.style.padding = 'var(--gap-md)';
		const confirmButton = frmDom.modal.footerButton({
			text: 'OK',
			buttonType: 'primary'
		});
		const modalFooter = frmDom.div({ child: confirmButton });
		frmDom.modal.maybeCreateModal(
			'frm_ai_autofill_error_modal',
			{
				content: modalContent,
				footer: modalFooter
			}
		);
	}

	function populateFieldsWithAutofillAiResponse( data ) {
		let index = 0;
		pendingFillData.forEach(
			function( field ) {
				if ( field.input.classList.contains( 'tmce-active' ) ) {
					const editorId = field.input.id.replace( 'wp-', '' ).replace( '-wrap', '' );
					const editor   = tinymce.get( editorId );
					if ( editor ) {
						editor.setContent( data[ index ].value );
					}
				} else {
					field.input.value = maybeFixValue( data[ index ].value, field );
					field.input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
					index++;
				}
			}
		);
	}

	function populateConfirmationFieldsWithAiResponse( data ) {
		let index = 0;
		pendingFillData.forEach(
			function( field ) {
				const match = delayedConfirmationFields.find(
					function( delayedField ) {
						return delayedField.targetInput === field.input;
					}
				);
				if ( match ) {
					match.input.value = maybeFixValue( data[ index ].value, field );
					match.input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
				}

				index++;
			}
		);
	}

	function maybeFixValue( value, field ) {
		if ( field.context === 'Email Address' ) {
			value = value.replace( 'Email: ', '' );
			value = value.toLowerCase();
		}
		return value;
	}

	function preparePendingFillDataForRequest() {
		return pendingFillData.map( ( field ) => {
			const label               = document.querySelector( 'label[for="' + field.input.id + '"]' );
			const requestDataForField = {
				context: field.context
			};

			const labelText = label?.textContent.replace( '*', '' ).trim() ?? '';
			if ( '' !== labelText ) {
				requestDataForField.label = labelText;
			}

			const fieldKey    = field.input.id.replace( 'field_', '' );
			const description = document.querySelector( '#frm_desc_field_' + fieldKey );
			if ( description ) {
				requestDataForField.description = description.textContent.trim();
			}

			return requestDataForField;
		} );
	}

	/**
	 * Make a POST request to the AJAX url that expects a JSON response.
	 *
	 * @param {FormData} formData 
	 * @returns 
	 */
	function doJsonPost( formData ) {
		formData.append( 'nonce', frm_js.nonce );
		return fetch(
			frm_js.ajax_url,
			{
				method: 'POST',
				body: formData
			}
		).then(
			function( response ) {
				return response.json();
			}
		);
	}

	/**
	 * Fill text fields.
	 *
	 * @param {HTMLElement} fieldsContainer
	 * @return {void}
	 */
	function fillTextFields( fieldsContainer ) {
		fieldsContainer.querySelectorAll( 'input[type="text"]' ).forEach( ( input ) => {
			if ( input.classList.contains( 'frm_verify' ) || input.value !== '' ) {
				return;
			}

			if ( ! input.offsetParent ) {
				// Do not fill a hidden field.
				return;
			}

			if ( input.name.endsWith( '[line2]' ) ) {
				// Do not bother filling in line 2 in an address field.
				return;
			}

			if ( input.name.endsWith( '[first]' ) ) {
				pendingFillData.push({
					input,
					context: 'First Name'
				});
			} else if ( input.name.endsWith( '[last]' ) ) {
				pendingFillData.push({
					input,
					context: 'Last Name'
				});
			} else if ( input.name.endsWith( '[city]' ) ) {
				pendingFillData.push({
					input,
					context: 'City ' + getCountryForContext( input )
				});
			} else if ( input.name.endsWith( '[state]' ) ) {
				pendingFillData.push({
					input,
					context: 'State ' + getCountryForContext( input )
				});
			} else if ( input.name.endsWith( '[zip]' ) ) {
				pendingFillData.push({
					input,
					context: 'Zip Code ' + getCountryForContext( input )
				});
			} else if ( input.name.endsWith( '[line1]' ) ) {
				pendingFillData.push({
					input,
					context: 'Address Line 1 ' + getCountryForContext( input )
				});
			} else if ( input.classList.contains( 'frm_date' ) ) {
				pendingFillData.push({
					input,
					context: getDateFieldContext( input )
				});
			} else {
				let context = 'Text field';

				if ( input.pattern ) {
					context += ' (Regex Pattern: ' + input.pattern + ')';
				}

				pendingFillData.push({
					input,
					context
				});
			}
		} );
	}

	function getDateFieldContext( input ) {
		let match = null;
		if ( window.__frmDatepicker ) {
			match = __frmDatepicker.find( rule => rule.triggerID === '#' + input.id );
		}

		const context = 'Date';
		if ( ! match ) {
			return context;
		}

		const details = {
			'Format': match.options.fpDateFormat,
			'Year Range': match.options.yearRange
		};

		if ( match.formidable_dates ) {
			const addonSettings = match.formidable_dates;
			if ( 'date' === addonSettings.minimum_date_cond ) {
				details['Minimum Date'] = addonSettings.minimum_date_val;
			}
			if ( 'date' === addonSettings.maximum_date_cond ) {
				details['Maximum Date'] = addonSettings.maximum_date_val;
			}
		}

		const detailString = Object.entries( details ).map( ( [ key, value ] ) => key + ': ' + value ).join( ', ' );
		return context + ' (' + detailString + ')';
	}

	function getCountryForContext( input ) {
		const comboContainer = input.closest( '.frm_combo_inputs_container' );
		if ( ! comboContainer ) {
			return '';
		}

		const countryDropdown = comboContainer.querySelector( '[name$="[country]"]' );
		if ( ! countryDropdown ) {
			return '';
		}

		if ( '' !== countryDropdown.value ) {
			return '(Country: ' + countryDropdown.value + ')';
		}

		let exists = false;
		delayedFillData.some( ( field ) => {
			if ( field.input === countryDropdown ) {
				exists = true;
				return true;
			}
			return false;
		} );
		if ( exists ) {
			return '(Country: ' + delayedFillData.find( ( field ) => {
				return field.input === countryDropdown;
			} ).value + ')';
		}

		const countryOptions = getElementsWithNonEmptyValues( countryDropdown.options );
		const randomOption   = countryOptions[ Math.floor( Math.random() * countryOptions.length ) ];

		delayedFillData.push({
			input: countryDropdown,
			value: randomOption.value
		});

		return '(Country: ' + randomOption.value + ')';
	}

	/**
	 * Fill textarea fields.
	 *
	 * @param {HTMLElement} fieldsContainer
	 * @return {void}
	 */
	function fillTextAreaFields( fieldsContainer ) {
		fieldsContainer.querySelectorAll( 'textarea' ).forEach( ( textarea ) => {
			if ( textarea.value !== '' ) {
				return;
			}

			pendingFillData.push({
				input: textarea,
				context: 'Textarea'
			});
		} );
	}

	/**
	 * Fill number fields.
	 *
	 * @param {HTMLElement} fieldsContainer
	 * @return {void}
	 */
	function fillNumberFields( fieldsContainer ) {
		fieldsContainer.querySelectorAll( 'input[type="number"]' ).forEach( ( input ) => {
			if ( input.value !== '' ) {
				return;
			}

			// Generate a random number based on the number range.
			const min = parseInt( input.getAttribute( 'min' ), 10 );
			const max = parseInt( input.getAttribute( 'max' ), 10 );

			const step      = input.getAttribute( 'step' );
			let randomValue = Math.floor( Math.random() * ( max - min + 1 ) ) + min;
			if ( step && ! isNaN( step ) ) {
				randomValue = Math.round( randomValue / step ) * step;
			}

			input.value = randomValue;
			input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		} );
	}

	/**
	 * Fill phone fields.
	 *
	 * @param {HTMLElement} fieldsContainer
	 * @return {void}
	 */
	function fillPhoneFields( fieldsContainer ) {
		fieldsContainer.querySelectorAll( 'input[type="tel"]' ).forEach( ( input ) => {
			if ( input.value !== '' ) {
				return;
			}

			let context = 'Phone';
			if ( input.pattern ) {
				context += ' (Regex Pattern: ' + input.pattern + ')';
			}

			pendingFillData.push({ input, context });
		} );
	}

	/**
	 * Fill URL fields.
	 *
	 * @param {HTMLElement} fieldsContainer
	 * @return {void}
	 */
	function fillUrlFields( fieldsContainer ) {
		fieldsContainer.querySelectorAll( 'input[type="url"]' ).forEach( ( input ) => {
			if ( input.value !== '' ) {
				return;
			}

			pendingFillData.push({
				input,
				context: 'URL'
			});
		} );
	}

	/**
	 * Fill email fields.
	 *
	 * @param {HTMLElement} fieldsContainer
	 * @return {void}
	 */
	function fillEmailFields( fieldsContainer ) {
		fieldsContainer.querySelectorAll( 'input[type="email"]' ).forEach( ( input ) => {
			if ( input.value !== '' ) {
				return;
			}

			if ( input.id.startsWith( 'field_conf_' ) ) {
				delayConfirmationField( input );
				return;
			}

			pendingFillData.push({
				input,
				context: 'Email Address'
			});
		} );
	}

	/**
	 * Fill rich text fields.
	 *
	 * @param {HTMLElement} fieldsContainer
	 * @return {void}
	 */
	function fillRichTextFields( fieldsContainer ) {
		fieldsContainer.querySelectorAll( '.frm_form_field .tmce-active' ).forEach( ( input ) => {
			if ( ! window.tinymce ) {
				return;
			}

			pendingFillData.push({
				input,
				context: 'Rich Text'
			});
		} );
	}

	function delayConfirmationField( input ) {
		const targetInput = document.getElementById( input.id.replace( 'conf_', '' ) );
		if ( ! targetInput ) {
			return;
		}

		delayedConfirmationFields.push({
			input,
			targetInput: targetInput
		});
	}

	/**
	 * Fill radio button fields (including star rating fields).
	 *
	 * @param {HTMLElement} fieldsContainer
	 * @return {void}
	 */
	function fillRadioFields( fieldsContainer ) {
		const radioButtonNames = new Set();
		fieldsContainer.querySelectorAll( 'input[type="radio"]' ).forEach( ( radio ) => {
			radioButtonNames.add( radio.name );
		} );

		radioButtonNames.forEach( ( name ) => {
			let radioButtons = fieldsContainer.querySelectorAll( 'input[type="radio"][name="' + name + '"]' );

			if ( getCheckedElements( radioButtons ).length > 0 ) {
				return;
			}

			radioButtons = removeOtherOptions( radioButtons );
			if ( radioButtons.length === 0 ) {
				return;
			}

			const randomRadio  = radioButtons[Math.floor( Math.random() * radioButtons.length )];
			randomRadio.checked = true;

			// Star rating fields require a click event for the stars to visibly change.
			if ( randomRadio.parentElement?.classList?.contains( 'frm-star-group' ) ) {
				const event = new MouseEvent( 'click', { bubbles: true, cancelable: true } );
				randomRadio.dispatchEvent( event );
			} else {
				randomRadio.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			}
		} );
	}

	/**
	 * Fill checkbox fields, including Toggle field types.
	 *
	 * @param {HTMLElement} fieldsContainer
	 * @return {void}
	 */
	function fillCheckboxFields( fieldsContainer ) {
		const checkboxNames = new Set();
		fieldsContainer.querySelectorAll( 'input[type="checkbox"]' ).forEach( ( checkbox ) => {
			checkboxNames.add( checkbox.name );
		} );

		checkboxNames.forEach( ( name ) => {
			let checkboxes = fieldsContainer.querySelectorAll( 'input[type="checkbox"][name="' + name + '"]' );

			if ( getCheckedElements( checkboxes ).length > 0 ) {
				return;
			}

			checkboxes = removeOtherOptions( checkboxes );
			if ( checkboxes.length === 0 ) {
				return;
			}

			const randomCheckbox = checkboxes[Math.floor( Math.random() * checkboxes.length )];
			randomCheckbox.checked = true;
			randomCheckbox.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		} );
	}

	/**
	 * Fill select fields.
	 *
	 * @param {HTMLElement} fieldsContainer
	 * @return {void}
	 */
	function fillSelectFields( fieldsContainer ) {
		fieldsContainer.querySelectorAll( 'select' ).forEach( ( select ) => {
			if ( select.value !== '' ) {
				return;
			}

			const isCountryField = select.name.endsWith( '[country]' );
			if ( isCountryField ) {
				// Country fields are handled somewhere else, so skip them here.
				return;
			}

			// Select a random option that is not blank.
			const options = Array.from( select.options );
			const nonEmptyOptions = removeOtherOptions( getElementsWithNonEmptyValues( options ) );
			if ( nonEmptyOptions.length === 0 ) {
				return;
			}

			const randomOption = nonEmptyOptions[Math.floor( Math.random() * nonEmptyOptions.length )];
			randomOption.selected = true;

			select.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		} );
	}

	function getElementsWithNonEmptyValues( elements ) {
		return Array.from( elements ).filter( function( element ) {
			return element.value !== '';
		} );
	}

	function removeOtherOptions( elements ) {
		return Array.from( elements ).filter( function( element ) {
			if ( element.classList.contains( 'frm_other_trigger' ) ) {
				return false;
			}

			return -1 === element.id.indexOf( '-other' );
		} );
	}

	function getCheckedElements( elements ) {
		return Array.from( elements ).filter( function( element ) {
			return element.checked;
		} );
	}
}() );
