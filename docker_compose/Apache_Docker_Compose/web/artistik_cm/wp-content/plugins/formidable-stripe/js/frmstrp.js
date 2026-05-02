var frmStrpProcess;

function frmStrpProcessJS() {

	var thisForm = false,
		formID = 0,
		event = false,
		card  = false,
		frmstripe,
		running = 100,
		elements,
		isStripeLink = false,
		linkAuthenticationElementIsComplete = false,
		stripeLinkElementIsComplete = false;

	function validateForm( e ) {
		var action, creatingEntry, isInProgressOrAbandoned, saveDraft, isDraft, shouldProcessForm, ccField, goingBack;

		thisForm = this;

		formID = jQuery( thisForm ).find( 'input[name="form_id"]' ).val();
		if ( formID == frm_stripe_vars.form_id ) {
			action                  = jQuery( thisForm ).find( 'input[name="frm_action"]' ).val();
			saveDraft               = savingDraft( thisForm );
			isDraft                 = 'update' === action && saveDraft === '';
			creatingEntry           = 'create' === action && saveDraft !== 1;
			isInProgressOrAbandoned = thisForm.querySelector( 'input[name="secret"]' );
			shouldProcessForm       = creatingEntry || isDraft || isInProgressOrAbandoned;

			if ( shouldProcessForm ) {
				goingBack = typeof frmProForm !== 'undefined' && frmProForm.goingToPreviousPage( thisForm );
				if ( ! goingBack ) {
					ccField = jQuery( thisForm ).find( '.frm-card-element' );
					if ( ccField.length && ! ccField.is( ':hidden' ) ) {
						e.preventDefault();
						event = e;
						processForm( ccField );
						return;
					}
				}
			}
		}

		if ( typeof frmFrontForm.submitFormManual === 'function' ) {
			frmFrontForm.submitFormManual( e, thisForm );
		} else {
			thisForm.submit();
		}

		return false;
	}

	function savingDraft( thisForm ) {
		var isDraft = false;
		if ( typeof frmProForm !== 'undefined' ) {
			isDraft = frmProForm.savingDraft( thisForm );
		}
		return isDraft;
	}

	function processForm( ccField ) {
		var $form, meta, settings, i;

		$form = jQuery( thisForm );

		// Run javascript validation.
		$form.addClass( 'frm_js_validate' );

		if ( ! validateFormSubmit( $form ) ) {
			return;
		}

		// disable the submit button to prevent repeated clicks
		if ( typeof frmFrontForm.showSubmitLoading === 'function' ) {
			frmFrontForm.showSubmitLoading( $form );
		} else {
			$form.find( 'input[type="submit"],input[type="button"],button[type="submit"]' ).attr( 'disabled', 'disabled' );
		}

		meta = addNameAndAddress( $form );

		if ( isStripeLink ) {
			stripeLinkSubmit( $form.get( 0 ), meta );
			return;
		}

		settings = frm_stripe_vars.settings;
		running  = settings.length;

		const delayedCalls = [];

		for ( i = 0; i < settings.length; i++ ) {
			if ( settings[ i ].one !== 'recurring' ) {
				meta = convertToAddressObject( meta );
				if ( frm_stripe_vars.process === 'before' ) {
					handlePayment(
						meta, settings[ i ]
					);
				} else {
					delayedCalls.push({ function: 'createPaymentMethod', meta });
				}
			} else {
				delayedCalls.push({ function: 'createToken', meta });
			}
		}

		triggerDelayedCalls( delayedCalls );

		// Prevent the form from submitting with the default action
		return false;
	}

	/**
	 * Avoid calling createPaymentMethod until the previous call has finished.
	 * We avoid calling them simultaneously because multiple calls can cause
	 * issues with promises never being fulfilled.
	 *
	 * @since 3.1.5
	 *
	 * @param {Array} calls
	 * @returns {void}
	 */
	function triggerDelayedCalls( calls ) {
		if ( ! calls.length ) {
			return;
		}

		let index = 0;

		const nextCall = function() {
			if ( ! calls[ index ]) {
				return;
			}

			const meta = calls[ index ].meta;

			if ( 'createPaymentMethod' === calls[ index ].function ) {
				createPaymentMethod( meta, nextCall );
			} else {
				createToken( meta, nextCall );
			}

			++index;
		};

		nextCall();
	}

	/**
	 * Submit a form for Stripe link.
	 * First it forces a form submission (with AJAX) so create an entry before calling confirmSetup/confirmPayment.
	 * confirmSetup gets called for a recurring payment and confirmPayment is called for one-time payments.
	 * In both cases they redirect to the return url which uses the frmstrplinkreturn AJAX action.
	 *
	 * @since 3.0
	 *
	 * @param {Element} object
	 * @param {Object} meta
	 * @returns {void}
	 */
	function stripeLinkSubmit( object, meta ) {
		object.classList.add( 'frm_trigger_event_on_submit', 'frm_ajax_submit' );

		object.addEventListener( 'frmSubmitEvent', confirmPayment );
		running = 0;
		submitForm();

		function confirmPayment( event ) {
			var params, confirmFunction;

			if ( ! checkEventDataForError( event ) ) {
				return;
			}

			window.onpageshow = function( event ) {
				// Force the form to reload on back button after submitting.
				if ( event.persisted || ( window.performance && window.performance.getEntriesByType( 'navigation' )[0].type === 'back_forward' ) ) {
					window.location.reload();
				}
			};

			params = {
				elements: elements,
				confirmParams: {
					return_url: getReturnUrl(),
					payment_method_data: {
						billing_details: convertToAddressObject( meta )
					}
				}
			};
			confirmFunction = isRecurring() ? 'confirmSetup' : 'confirmPayment';

			frmstripe[ confirmFunction ]( params ).then( handleConfirmPromise );
		}

		function getReturnUrl() {
			var url = new URL( frm_stripe_vars.ajax );
			url.searchParams.append( 'action', 'frmstrplinkreturn' );
			return url.toString();
		}

		function handleConfirmPromise( result ) {
			if ( result.error ) {
				handleConfirmPaymentError( result.error );
			}
		}

		function handleConfirmPaymentError( error ) {
			var fieldset, cardErrors;

			running--;
			enableSubmit();

			fieldset = jQuery( object ).find( '.frm_form_field' );
			fieldset.removeClass( 'frm_doing_ajax' );

			object.classList.remove( 'frm_loading_form' );

			// Don't show validation_error here as those are added automatically to the email and postal code fields, etc.
			if ( 'card_error' === error.type || 'invalid_request_error' === error.type || 'form_submit_error' === error.type ) {
				cardErrors = object.querySelector( '.frm-card-errors' );
				if ( cardErrors ) {
					cardErrors.textContent = error.message;
				}
			}
		}

		/**
		 * Check the event content for any possible errors.
		 * Some types of errors will appear here, like the errors added when calling FrmStrpActionsController::trigger_gateway.
		 *
		 * @since 3.1.5
		 *
		 * @param {CustomEvent} event
		 * @returns {boolean}
		 */
		function checkEventDataForError( event ) {
			var element, error;

			if ( ! event.frmData || ! event.frmData.content.length || -1 === event.frmData.content.indexOf( '<div class="frm_error_style' ) ) {
				return true;
			}

			element = document.createElement( 'div' );
			element.innerHTML = event.frmData.content;

			error = element.querySelector( '.frm_error_style' );
			if ( error ) {
				handleConfirmPaymentError({
					type: 'form_submit_error',
					message: error.textContent
				});
				return false;
			}

			return true;
		}
	}

	/**
	 * Check if the stripe setting is for a recurring payment.
	 *
	 * @since 3.0
	 *
	 * @returns {bool}
	 */
	function isRecurring() {
		var isRecurring	= false;

		each(
			getStripeSettings(),
			function( setting ) {
				if ( 'recurring' === setting.one ) {
					isRecurring = true;
					return false;
				}
			}
		);

		return isRecurring;
	}

	/**
	 * @since 3.0
	 *
	 * @param {Element} form
	 * @param {bool} enabled
	 * @returns {void}
	 */
	function maybeToggleConversationalButtonsOnFinalQuestion( form, enabled ) {
		var formId;

		if ( ! chatWrapperIsOnFinalQuestion( form ) ) {
			return;
		}

		formId = parseInt( form.querySelector( '[name="form_id"]' ).value );
		if ( submitButtonIsConditionallyDisabled( formId ) ) {
			return;
		}

		toggleConversationalButtons( form, enabled );
	}

	/**
	 * @since 3.0
	 *
	 * @param {Element} form
	 * @returns {bool}
	 */
	function chatWrapperIsOnFinalQuestion( form ) {
		return form.nextElementSibling && form.nextElementSibling.classList.contains( 'frm_final_question' );
	}

	/**
	 * Check if the submit button is conditionally disabled.
	 * This is required for Stripe link so the button does not get enabled at the wrong time after completing the Stripe elements.
	 *
	 * @since 3.0
	 *
	 * @param {String} formId
	 * @returns {bool}
	 */
	function submitButtonIsConditionallyDisabled( formId ) {
		return submitButtonIsConditionallyNotAvailable( formId ) && 'disable' === __FRMRULES[ 'submit_' + formId ].hideDisable;
	}

	/**
	 * Check submit button is conditionally "hidden". This is also used for the enabled check and is used in submitButtonIsConditionallyDisabled.
	 *
	 * @since 3.0
	 *
	 * @param {String} formId
	 * @returns bool
	 */
	function submitButtonIsConditionallyNotAvailable( formId ) {
		var hideFields = document.getElementById( 'frm_hide_fields_' + formId );
		return hideFields && -1 !== hideFields.value.indexOf( '["frm_form_' + formId + '_container .frm_final_submit"]' );
	}

	/**
	 * @param {Object} $form
	 * @return {Boolean} false if there are errors.
	 */
	function validateFormSubmit( $form ) {
		var errors, keys;

		errors = frmFrontForm.validateFormSubmit( $form );
		keys   = Object.keys( errors );

		if ( 1 === keys.length && errors[ keys[0] ] === '' ) {
			// Pop the empty error that gets added by invisible recaptcha.
			keys.pop();
		}

		return 0 === keys.length;
	}

	function handlePayment( meta, settings ) {
		var intent, i;

		// TODO: check if on the last page of the form.
		intent = document.getElementsByName( 'frmintent' + formID + '[]' );

		for ( i = 0; i < intent.length; i++ ) {
			authorizeIntent( intent[ i ], meta, settings );
		}
	}

	function authorizeIntent( intent, meta, settings ) {
		var a = intent.getAttribute( 'data-action' );
		if ( a === '' || a == settings.id ) {
			frmstripe.handleCardPayment(
				intent.value,
				card,
				{
					payment_method_data: {
						billing_details: meta
					}
				}
			).then( afterPayment );
		} else {
			running--;
		}
	}

	function afterPayment( result ) {
		var i, x;
		running--;
		enableSubmit();
		if ( result.error ) {
			addError( result.error );
		} else {
			// The payment authorization has succeeded.
			i = document.getElementsByName( 'frmintent' + formID + '[]' );
			for ( x = 0; x < i.length; x++ ) {
				if ( i[ x ].value == result.paymentIntent.client_secret ) {
					i[ x ].value = result.paymentIntent.id;
					i[ x ].setAttribute( 'name', 'frmauth' + formID + '[]' );
				}
			}
			submitForm();
		}
	}

	/**
	 * @param {object}   meta
	 * @param {function} successCallback A function to call when the promise is successful.
	 * @returns {void}
	 */
	function createPaymentMethod( meta, successCallback ) {
		const promise = frmstripe.createPaymentMethod(
			'card',
			card,
			{
				billing_details: meta
			}
		);
		promise.then(
			function( result ) {
				running--;
				// re-enable the submit button
				enableSubmit();

				if ( result.error ) {
					// Inform the user if there was an error
					addError( result.error );
				} else {
					// insert the token into the form so it gets submitted to the server
					jQuery( thisForm ).append( '<input type="hidden" name="stripeMethod" value="' + result.paymentMethod.id + '" />' );
					submitForm();
					successCallback();
				}
			}
		);
	}

	function addNameAndAddress( $form ) {
		var addressContainer,
			prefix,
			i,
			firstField,
			lastField,
			firstFieldContainer,
			lastFieldContainer,
			cardObject = {},
			settings = frm_stripe_vars.settings,
			addressID = '',
			firstNameID = '',
			lastNameID = '',
			getNameFieldValue,
			subFieldEl;

		/**
		 * Gets first, middle or last name from the given field.
		 *
		 * @param {Number|HTMLElement} field        Field ID or Field element.
		 * @param {String}             subFieldName Subfield name.
		 * @return {String}
		 */
		getNameFieldValue = function( field, subFieldName ) {
			if ( 'object' !== typeof field ) {
				field = document.getElementById( 'frm_field_' + field + '_container' );
			}

			if ( ! field || 'object' !== typeof field || 'function' !== typeof field.querySelector ) {
				return '';
			}

			subFieldEl = field.querySelector( '.frm_combo_inputs_container .frm_form_subfield-' + subFieldName + ' input' );
			if ( ! subFieldEl ) {
				return '';
			}

			return subFieldEl.value;
		};

		for ( i = 0; i < settings.length; i++ ) {
			if ( jQuery.inArray( 'stripe', settings[ i ].gateways ) !== -1 ) {
				addressID = settings[ i ].address;
				firstNameID = settings[ i ].first_name;
				lastNameID = settings[ i ].last_name;
			}
		}

		if ( addressID !== '' ) {
			addressContainer = jQuery( document ).find( '#frm_field_' + addressID + '_container, .frm_field_' + addressID + '_container' );
			prefix = '';
			if ( addressContainer.length < 1 ) {
				addressContainer = jQuery( document ).find( 'input[name="item_meta[' + addressID + '][line1]"]' );
				if ( addressContainer.length ) {
					prefix = addressID + '][';
					addressContainer = addressContainer.parent();
				}
			}

			if ( addressContainer.length ) {
				cardObject = addValToRequest( addressContainer, prefix + 'line1', cardObject, 'address_line1' );
				cardObject = addValToRequest( addressContainer, prefix + 'line2', cardObject, 'address_line2' );
				cardObject = addValToRequest( addressContainer, prefix + 'city', cardObject, 'address_city' );
				cardObject = addValToRequest( addressContainer, prefix + 'state', cardObject, 'address_state' );
				cardObject = addValToRequest( addressContainer, prefix + 'zip', cardObject, 'address_zip' );
				// The two letter country code is needed here, so skip it. This is required for Afterpay, which is currently a limitation.

				const countryDropdown = addressContainer.get( 0 ).querySelector( 'select[name$="[' + prefix + 'country]"]' );
				if ( countryDropdown ) {
					const countryOption = countryDropdown.querySelector( 'option[value="' + countryDropdown.value + '"]' );
					if ( countryOption && countryOption.getAttribute( 'data-code' ) ) {
						cardObject.address_country = countryOption.getAttribute( 'data-code' );
					}
				}
			}
		}

		function getNameFieldItem( fieldID, type, $form ) {
			if ( type === 'container' ) {
				return document.querySelector( '#frm_field_' + fieldID + '_container, .frm_field_' + fieldID + '_container' );
			}
			return $form.find( '#frm_field_' + fieldID + '_container input, input[name="item_meta[' + fieldID + ']"], .frm_field_' + fieldID + '_container input' );
		}

		if ( firstNameID !== '' ) {
			firstFieldContainer = getNameFieldItem( firstNameID, 'container' );
			if ( firstFieldContainer && firstFieldContainer.querySelector( '.frm_combo_inputs_container' ) ) { // This is a name field.
				cardObject.name = getNameFieldValue( firstFieldContainer, 'first' );
			} else {
				firstField = getNameFieldItem( firstNameID, 'field', $form );
				if ( firstField.length && firstField.val() ) {
					cardObject.name = firstField.val();
				}
			}
		}

		if ( lastNameID !== '' ) {
			lastFieldContainer = getNameFieldItem( lastNameID, 'container' );
			if ( lastFieldContainer && lastFieldContainer.querySelector( '.frm_combo_inputs_container' ) ) { // This is a name field.
				cardObject.name = cardObject.name + ' ' + getNameFieldValue( lastFieldContainer, 'last' );
			} else {
				lastField = getNameFieldItem( lastNameID, 'field', $form );
				if ( lastField.length && lastField.val() ) {
					cardObject.name = cardObject.name + ' ' + lastField.val();
				}
			}
		}

		return cardObject;
	}

	function addValToRequest( container, inputName, cardObject, objectName ) {
		var input = container.find( 'input[name$="[' + inputName + ']"]' );
		if ( input.length && input.val() ) {
			cardObject[ objectName ] = input.val();
		}
		return cardObject;
	}

	function convertToAddressObject( meta ) {
		var newMeta, k;
		newMeta = { address: {} };
		for ( k in meta ) {
			if ( meta.hasOwnProperty( k ) ) {
				if ( k === 'address_zip' ) {
					newMeta.address.postal_code = meta[ k ];
				} else if ( k.indexOf( 'address_' ) === 0 ) {
					newMeta.address[ k.replace( 'address_', '' ) ] = meta[ k ];
				} else {
					newMeta[k] = meta[ k ];
				}
			}
		}
		return newMeta;
	}

	/**
	 * @param {object} cardData
	 * @param {function} successCallback
	 */
	function createToken( cardData, successCallback ) {
		var promise = frmstripe.createToken( card, cardData );
		promise.then(
			function( result ) {
				running--;
				// re-enable the submit button
				enableSubmit();

				if ( result.error ) {
					// Inform the user if there was an error
					addError( result.error );
				} else {
					// Send the token to your server
					stripeTokenHandler( result.token );

					successCallback();
				}
			}
		);
	}

	function addError( error ) {
		var errorElement, $fieldCont, parentCont;

		errorElement = document.getElementsByClassName( 'frm-card-errors' )[ 0 ];
		if ( typeof errorElement === 'undefined' ) {
			errorElement = document.createElement( 'div' );
			errorElement.classList = 'frm-card-errors frm_error';
			errorElement.textContent = error.message;
			document.getElementsByClassName( 'frm-card-element' )[ 0 ].parentNode.appendChild( errorElement );
		} else {
			errorElement.textContent = error.message;
		}

		$fieldCont = jQuery( thisForm ).find( '.frm-card-errors' );

		if ( $fieldCont.length ) {
			parentCont = $fieldCont.closest( '.frm_form_field' );
			parentCont.addClass( 'frm_blank_field' );
			$fieldCont.textContent = error.message;

			frmFrontForm.scrollMsg( parentCont, thisForm, true );
		}
	}

	function stripeTokenHandler( token ) {
		// insert the token into the form so it gets submitted to the server
		jQuery( thisForm ).append( '<input type="hidden" name="stripeToken" value="' + token.id + '" />' );
		submitForm();
	}

	function submitForm() {
		if ( running > 0 ) {
			return;
		}
		if ( typeof frmFrontForm.submitFormManual === 'function' ) {
			frmFrontForm.submitFormManual( event, thisForm );
		} else {
			jQuery( thisForm ).get( 0 ).submit();
		}
	}

	function enableSubmit() {
		if ( running > 0 ) {
			return;
		}
		if ( typeof frmFrontForm.removeSubmitLoading === 'function' ) {
			thisForm.classList.add( 'frm_loading_form' );
			frmFrontForm.removeSubmitLoading( jQuery( thisForm ), 'enable', 0 );
		} else {
			jQuery( thisForm ).find( 'input[type="submit"],input[type="button"],button[type="submit"]' ).prop( 'disabled', false );
		}
		maybeToggleConversationalButtonsOnFinalQuestion( thisForm, true );
	}

	function deleteCard() {
		var button = this,
			cardId = button.dataset.cid;
		jQuery.ajax({
			url: frm_stripe_vars.root + 'frm-strp/v1/card/' + cardId,
			method: 'DELETE',
			dataType: 'json',
			beforeSend: function( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', frm_stripe_vars.api_nonce );
			},
			success: function( result ) {
				if ( result.success == true ) {
					jQuery( button ).closest( 'tr' ).fadeOut();
				} else {
					button.innerHTML = result.error;
				}
			}
		});
	}

	function getPriceFields() {
		var priceFields = [];

		function checkStripeSettingForPriceFields( setting ) {
			if ( -1 !== setting.fields ) {
				each( setting.fields, addFieldDataToPriceFieldsArray );
			}
		}

		function addFieldDataToPriceFieldsArray( field ) {
			if ( isNaN( field ) ) {
				priceFields.push( 'field_' + field );
			} else {
				priceFields.push( field );
			}
		}

		each( getStripeSettings(), checkStripeSettingForPriceFields );

		return priceFields;
	}

	/**
	 * Get all variables from frm_stripe_vars.settings that match the Stripe gateway.
	 *
	 * @since 3.0
	 *
	 * @returns {array}
	 */
	function getStripeSettings() {
		var stripeSettings = [];
		each(
			frm_stripe_vars.settings,
			function( setting ) {
				if ( -1 !== setting.gateways.indexOf( 'stripe' ) ) {
					stripeSettings.push( setting );
				}
			}
		);
		return stripeSettings;
	}

	// Update price intent on change.
	function priceChanged( _, field, fieldId ) {
		var i, data,
			price = getPriceFields(),
			run = price.indexOf( fieldId ) > -1 || price.indexOf( field.id ) > -1;
		if ( ! run ) {
			for ( i = 0; i < price.length; i++ ) {
				if ( field.id.indexOf( price[ i ]) === 0 ) {
					run = true;
				}
			}
		}
		if ( run ) {
			data = {
				action: 'frm_strp_amount',
				form: JSON.stringify( jQuery( field ).closest( 'form' ).serializeArray() ),
				nonce: frm_stripe_vars.nonce
			};
			postAjax( data, function( response ) {
				// Amount has been conditionally updated.
			});
		}
	}

	function postAjax( data, success ) {
		var xmlHttp = new XMLHttpRequest(),
			params = typeof data == 'string' ? data : Object.keys( data ).map(
				function( k ) {
					return encodeURIComponent( k ) + '=' + encodeURIComponent( data[ k ]);
				}
			).join( '&' );

		xmlHttp.open( 'post', frm_stripe_vars.ajax, true );
		xmlHttp.onreadystatechange = function() {
			var response;
			if ( xmlHttp.readyState > 3 && xmlHttp.status == 200 ) {
				response = xmlHttp.responseText;
				if ( response !== '' ) {
					try {
						response = JSON.parse( response );
					} catch ( error ) {
						response = '';
					}
				}
				success( response );
			}
		};
		xmlHttp.setRequestHeader( 'X-Requested-With', 'XMLHttpRequest' );
		xmlHttp.setRequestHeader( 'Content-type', 'application/x-www-form-urlencoded' );
		xmlHttp.send( params );
		return xmlHttp;
	}

	/**
	 * Handle frmPageChanged events.
	 */
	function onPageChange() {
		loadElements();
		runConditionalLogicOnPaymentFailure();
	}

	/**
	 * The frmPageChanged event is triggered when a payment fails when submitting with AJAX.
	 * When the payment fails, we run conditional logic.
	 * Otherwise fields may be visible when they should be hidden.
	 *
	 * @returns {void}
	 */
	function runConditionalLogicOnPaymentFailure() {
		// On payment failure, run conditional logic.
		if ( ! document.querySelector( '.frm_error_style' ) || 'undefined' === typeof __frmHideOrShowFields ) {
			return;
		}

		frmProForm.hideOrShowFields( __frmHideOrShowFields, 'pageLoad' );
	}

	function loadElements() {
		if ( document.getElementsByClassName( 'frm-card-element' ).length < 1 ) {
			return;
		}

		if ( maybeLoadStripeLink() ) {
			// This function returns true if Stripe link is loading instead. Exit early so we don't load both stripe link and stripe card elements.
			return;
		}

		loadStripeCardElements();
	}

	/**
	 * @since 3.0
	 *
	 * @returns {bool} True if stripe link loads successfully.
	 */
	function maybeLoadStripeLink() {
		var stripeLinkForm, formId, intentField;

		stripeLinkForm = document.querySelector( 'form.frm_stripe_link_form' );
		if ( ! stripeLinkForm ) {
			return false;
		}

		formId      = parseInt( stripeLinkForm.querySelector( 'input[name="form_id"]' ).value );
		intentField = stripeLinkForm.querySelector( 'input[name="frmintent' + formId + '[]"]' );

		if ( ! intentField ) {
			return false;
		}

		loadStripeLinkElements( intentField.value );
		disableSubmit( stripeLinkForm );
		listenForSubmitButtonMutations( stripeLinkForm );
		listenForFieldMutations( stripeLinkForm );

		return true;
	}

	/**
	 * Listen for changes to the submit button disabled attribute.
	 * This is for compatibility with conditional logic on submit.
	 * The submit button should only be enabled if both the conditional logic passes and if the Stripe payment element is complete.
	 *
	 * @since 3.0
	 *
	 * @param {HTMLElement} form
	 * @returns {void}
	 */
	function listenForSubmitButtonMutations( form ) {
		var submitButton = form.querySelector( '.frm_button_submit' );
		if ( ! submitButton ) {
			return;
		}

		observeAttributeMutations( submitButton, handleMutation );

		function handleMutation( mutation ) {
			if ( mutation.attributeName === 'disabled' && ! mutation.target.disabled && ! stripeLinkElementIsComplete ) {
				disableSubmit( form );
			}
		}
	}

	/**
	 * Possibly toggle on and off the submit button when a Stripe Link payment field is conditionally shown or hidden.
	 *
	 * @since 3.1.5
	 *
	 * @param {HTMLElement} form
	 * @returns {void}
	 */
	function listenForFieldMutations( form ) {
		const fieldContainer = getPaymentElementFieldContainer( form );
		if ( ! fieldContainer ) {
			return;
		}

		observeAttributeMutations( fieldContainer, handleMutation );

		const section = fieldContainer.closest( '.frm_section_heading' );
		if ( section ) {
			observeAttributeMutations( section, handleMutation );
		}

		const formId = getFormIdForForm( form );

		/**
		 * Handle a style attribute change for either a payment field container
		 * or the field container of its parent section.
		 *
		 * @param {MutationRecord} mutation
		 * @returns {void}
		 */
		function handleMutation( mutation ) {
			if ( mutation.attributeName !== 'style' ) {
				return;
			}

			if ( submitButtonIsConditionallyDisabled( formId ) ) {
				return;
			}

			const shouldEnable = 'none' === mutation.target.display || readyToSubmitStripeLink( form ) || stripeLinkIsConditionallyDisabled( form );
			if ( ! shouldEnable ) {
				disableSubmit( form );
				return;
			}

			thisForm = form;
			running  = 0;
			enableSubmit();
		}
	}

	/**
	 * @param {HTMLElement} element
	 * @returns {void}
	 */
	function observeAttributeMutations( element, mutationHandler ) {
		const observer = new MutationObserver(
			( mutations ) => each( mutations, mutationHandler )
		);
		observer.observe(
			element,
			{ attributes: true }
		);
	};

	/**
	 * Disable submit button for a target Stripe link form.
	 *
	 * @since 3.0
	 *
	 * @param {HTMLElement} form
	 * @returns {void}
	 */
	function disableSubmit( form ) {
		if ( stripeLinkIsConditionallyDisabled( form ) ) {
			thisForm = form;
			running  = 0;
			enableSubmit();
			return;
		}

		jQuery( form ).find( 'input[type="submit"],input[type="button"],button[type="submit"]' ).not( '.frm_prev_page' ).attr( 'disabled', 'disabled' );
		maybeToggleConversationalButtonsOnFinalQuestion( form, false );
	}

	/**
	 * Check if a Stripe link element is conditionally hidden.
	 * If it is, we should not be disabling the submit button.
	 *
	 * @since 3.1.5
	 *
	 * @param {HTMLElement} form
	 * @returns {boolean}
	 */
	function stripeLinkIsConditionallyDisabled( form ) {
		const fieldContainer = getPaymentElementFieldContainer( form );
		if ( ! fieldContainer ) {
			return false;
		}

		// Field is conditionally hidden.
		if ( 'none' === fieldContainer.style.display ) {
			return true;
		}

		// Section parent is conditionally hidden.
		const parentSection = fieldContainer.closest( '.frm_section_heading' );
		if ( parentSection && 'none' === parentSection.style.display ) {
			return true;
		}

		return false;
	}

	/**
	 * Try to get the field container for a Stripe link payment element.
	 * The field container is checked to determine if the field is conditionally hidden or not.
	 *
	 * @param {HTMLElement} form
	 * @returns {HTMLElement|null}
	 */
	function getPaymentElementFieldContainer( form ) {
		const paymentElement = form.querySelector( '.frm-payment-element' );
		if ( ! paymentElement ) {
			return null;
		}
		return paymentElement.parentElement.closest( '.frm_form_field' );
	}

	/**
	 * Load elements for Stripe link (a Link Authentication Element and a Payment Element).
	 *
	 * @since 3.0
	 *
	 * @param {String} clientSecret
	 * @returns {void}
	 */
	function loadStripeLinkElements( clientSecret ) {
		var cardElement, appearance, isConversational;

		cardElement = document.querySelector( '.frm-card-element' );
		if ( ! cardElement ) {
			return;
		}

		// Customize the Stripe elements using the Stripe Appearance API.
		appearance   = {
			theme: 'stripe',
			variables: {
				fontSizeBase: frm_stripe_vars.style.base.fontSize,
				colorText: maybeAdjustColorForStripe( frm_stripe_vars.appearanceRules['.Input'].color ),
				colorBackground: maybeAdjustColorForStripe( frm_stripe_vars.appearanceRules['.Input'].backgroundColor ),
				fontSmooth: 'auto'
			},
			rules: frm_stripe_vars.appearanceRules
		};
		elements     = frmstripe.elements({ clientSecret: clientSecret, appearance: appearance });
		isStripeLink = true;

		isConversational = maybeAddConversationalSubmitListener( cardElement );

		insertAuthenticationElement( cardElement, isConversational );
		insertPaymentElement( cardElement );

		if ( isConversational ) {
			jQuery( document ).on( 'frmShowField', syncConversationalButtonsAfterContinueAction );
		}
	}

	/**
	 * Sync buttons after a conversaitonal form switches between questions.
	 * A question with a payment element should not be skippable if it isn't "complete".
	 * This is also true for link authentication elements.
	 * If it's on the final question, we also need to check for conditional submit button logic.
	 *
	 * @since 3.0
	 *
	 * @returns {void}
	 */
	function syncConversationalButtonsAfterContinueAction() {
		var form = document.querySelector( '.frm_stripe_link_form' );

		if ( chatWrapperIsOnFinalQuestion( form ) ) {
			toggleConversationalButtons( form, readyToSubmitStripeLink( form ) );
			return;
		}

		// Handle questions before the final question.
		maybeToggleConversationalButtonsOnLinkAuthenticationQuestion( form );

		if ( form.querySelector( '.frm_active_chat_field .frm-payment-element' ) ) {
			toggleConversationalButtons( form, stripeLinkElementIsComplete );
		}
	}

	/**
	 * @since 3.0
	 *
	 * @param {Element} form
	 * @returns {void}
	 */
	function maybeToggleConversationalButtonsOnLinkAuthenticationQuestion( form ) {
		linkAuthenticationElement = form.querySelector( '.frm_active_chat_field .frm-link-authentication-element, .frm_active_chat_field.frm-link-authentication-element' );
		if ( ! linkAuthenticationElement ) {
			return;
		}

		setTimeout(
			function() {
				toggleConversationalButtons( form, linkAuthenticationElementIsComplete );
			},
			0
		);
	}

	/**
	 * Stripe doesn't support RGBA so convert it to HEX.
	 *
	 * @since 3.0
	 *
	 * @param {String} color
	 * @returns {String}
	 */
	function maybeAdjustColorForStripe( color ) {
		var rgba, hex;

		if ( 0 !== color.indexOf( 'rgba' ) ) {
			return color;
		}

		rgba = color.replace( /^rgba?\(|\s+|\)$/g, '' ).split( ',' );
		hex  = `#${( ( 1 << 24 ) + ( parseInt( rgba[0], 10 ) << 16 ) + ( parseInt( rgba[1], 10 ) << 8 ) + parseInt( rgba[2], 10 ) )
			.toString( 16 )
			.slice( 1 )}`;

		return hex;
	}

	/**
	 * Check if a form is conversational and hooks into the conversational submit to handle it here in the Stripe add on instead.
	 *
	 * @param {Element} cardElement
	 * @returns {bool} True if the form is conversational.
	 */
	function maybeAddConversationalSubmitListener( cardElement ) {
		var isConversational, form;

		isConversational = cardElement.closest( '.frm_fields_container' ).classList.contains( 'frm_chat_form' );
		if ( ! isConversational ) {
			return false;
		}

		form = cardElement.closest( 'form' );
		jQuery( form ).on( 'submit', handleConversationalSubmit );

		function handleConversationalSubmit( e ) {
			if ( typeof frmProForm !== 'undefined' && frmProForm.goingToPreviousPage( form ) ) {
				// Going to previous page, don't handle submit here.
				return;
			}

			if ( stripeLinkIsConditionallyDisabled( form ) ) {
				// If Stripe is disabled, submit the form normally.
				return;
			}

			thisForm = form;
			event    = e;
			processForm( cardElement );
			return false;
		}

		return true;
	}

	/**
	 * The Authentication Element includes an email field that works with the Payment element.
	 * If the email matches a Stripe link account, this field will also include the 6 digit code prompt for using your linked credit card instead.
	 *
	 * @since 3.0
	 *
	 * @param {Element} cardElement
	 * @returns {void}
	 */
	function insertAuthenticationElement( cardElement, isConversational ) {
		var addAboveCardElement, emailField, authenticationMountTarget, emailInput, cardFieldContainer, defaultEmailValue, authenticationElement;

		addAboveCardElement       = true;
		emailField                = checkForEmailField();
		authenticationMountTarget = createMountTarget( 'frm-link-authentication-element' );

		if ( false !== emailField ) {
			if ( 'hidden' === emailField.getAttribute( 'type' ) ) {
				emailInput = emailField;
			} else {
				addAboveCardElement = false;
				emailInput          = emailField.querySelector( 'input' );
				replaceEmailField( emailField, emailInput, authenticationMountTarget );
			}
		}

		if ( addAboveCardElement ) {
			// If no email field is found, add the email field above the credit card.
			cardFieldContainer = cardElement.closest( '.frm_form_field' );
			cardFieldContainer.parentNode.insertBefore( authenticationMountTarget, cardFieldContainer );

			if ( isConversational ) {
				if ( cardFieldContainer.classList.contains( 'frm_active_chat_field' ) ) {
					// If the payment field is active on init, make it inactive and set the authentication element to active instead.
					cardFieldContainer.classList.remove( 'frm_active_chat_field' );
					cardFieldContainer.classList.add( 'frm_inactive_chat_field' );
					authenticationMountTarget.classList.add( 'frm_active_chat_field' );
				} else {
					// Set the authoentication element to inactive so it activates when it is next in order.
					authenticationMountTarget.classList.add( 'frm_inactive_chat_field' );
				}

				triggerQuestionCountChangeEvent( cardElement );
			}
		}

		defaultEmailValue     = false !== emailField ? getSettingFieldValue( emailField ) : '';
		authenticationElement = elements.create(
			'linkAuthentication',
			{
				defaultValues: {
					email: defaultEmailValue
				}
			}
		);
		authenticationElement.mount( '.frm-link-authentication-element' );
		authenticationElement.on( 'change', getAuthenticationChangeHandler( cardElement, emailInput, isConversational ) );

		/**
		 * Stripe does not support the red required asterisk that we show on our other fields.
		 * So we add one ourselves positioned absolute on top of the iframe.
		 */
		authenticationElement.on( 'ready', function() {
			authenticationMountTarget.style.position = 'relative';

			const requiredIndicator = document.createElement( 'span' );
			requiredIndicator.textContent    = '*';
			requiredIndicator.className      = 'frm_required';
			requiredIndicator.style.position = 'absolute';
			requiredIndicator.style.fontSize = 'var(--font-size)';
			requiredIndicator.style.top      = '-4px';
			requiredIndicator.style.left     = getEmailAsteriskOffset( cardElement ) + 'px';
			requiredIndicator.style.padding  = 'var(--label-padding)';
			requiredIndicator.setAttribute( 'aria-hidden', 'true' );
			authenticationMountTarget.appendChild( requiredIndicator );
		});
	}

	/**
	 * Create a temporary label element to determine the width of the Email label.
	 *
	 * @since 3.1.6
	 *
	 * @return {number}
	 */
	function getEmailAsteriskOffset( cardElement ) {
		const label = document.createElement( 'label' );
		label.classList.add( 'frm_primary_label', 'form-label' );
		label.textContent = 'Email';
		label.innerHTML  += '&nbsp;';

		const tempContainer = document.createElement( 'div' );
		tempContainer.classList.add( 'with_frm_style' );
		tempContainer.style.position   = 'absolute';
		tempContainer.style.visibility = 'hidden';
		tempContainer.style.height     = '0';
		tempContainer.style.overflow   = 'hidden';

		const formContainer = cardElement.closest( '.with_frm_style' );
		if ( formContainer ) {
			each(
				formContainer.classList,
				function( className ) {
					if ( className.startsWith( 'frm_style_' ) ) {
						tempContainer.classList.add( className );
						return false;
					}
				}
			);
		}

		tempContainer.appendChild( label );
		document.body.appendChild( tempContainer );

		const labelWidth = label.getBoundingClientRect().width;

		document.body.removeChild( tempContainer );

		return labelWidth;
	}

	/**
	 * Trigger an event after a new field is injected. This way the progress bar/text in a Conversational form can stay synced.
	 *
	 * @since 3.0
	 *
	 * @param {Element} cardElement
	 * @returns {void}
	 */
	function triggerQuestionCountChangeEvent( cardElement ) {
		var form, progress;

		form     = cardElement.closest( 'form' );
		progress = form.querySelector( '.frm-chat-progress' );

		if ( progress ) {
			progress.setAttribute( 'frm-question-total', parseInt( progress.getAttribute( 'frm-question-total' ) ) + 1 );
		}

		triggerCustomEvent( form, 'frmUpdatedQuestionCount' );
	}

	/**
	 * Triggers custom JS event.
	 *
	 * @since 3.0
	 *
	 * @param {HTMLElement} el        The HTML element.
	 * @param {String}      eventName Event name.
	 * @returns {void}
	 */
	function triggerCustomEvent( el, eventName ) {
		if ( typeof window.CustomEvent !== 'function' ) {
			return;
		}

		const event = new CustomEvent( eventName );

		el.dispatchEvent( event );
	}

	/**
	 * Get a handler to listen for Authentication element changes.
	 * This is used to sync an email value to a hidden email input if one is mapped to the Stripe setting.
	 * This is also used to toggle conversational buttons based of whether the event is "complete" or not.
	 * In a non-conversational form we need to check if the authentication element is complete as well.
	 * If we do not, the button could still be disabled after everything is filled out if we fill out the email last.
	 *
	 * @since 3.0
	 *
	 * @param {Element} cardElement
	 * @param {Element} emailInput
	 * @param {bool} isConversational
	 * @returns {Function}
	 */
	function getAuthenticationChangeHandler( cardElement, emailInput, isConversational ) {
		function syncEmailInput( emailValue ) {
			if ( 'string' === typeof emailValue && emailValue.length  ) {
				emailInput.value = emailValue;
			}
		}

		return function( event ) {
			var form;

			linkAuthenticationElementIsComplete = event.complete;

			if ( linkAuthenticationElementIsComplete && 'undefined' !== typeof emailInput ) {
				syncEmailInput( event.value.email );
			}

			form = cardElement.closest( 'form' );

			if ( isConversational ) {
				if ( form.querySelector( '.frm_active_chat_field .frm-link-authentication-element, .frm_active_chat_field.frm-link-authentication-element' ) ) {
					toggleConversationalButtons( form, event.complete );
				}
				return;
			}

			if ( readyToSubmitStripeLink( form ) ) {
				thisForm = form;
				running  = 0;
				enableSubmit();
			} else {
				disableSubmit( form );
			}
		};
	}

	/**
	 * @since 3.0
	 *
	 * @param {Element} form
	 * @param {bool} enabled
	 * @returns {void}
	 */
	function toggleConversationalButtons( form, enabled ) {
		var chatWrapper, nextArrow, continueButton;

		if ( ! form.nextElementSibling ) {
			return;
		}

		chatWrapper    = form.nextElementSibling;
		nextArrow      = chatWrapper.querySelector( '.frm_chat_next_arrow' );
		continueButton = chatWrapper.querySelector( '.frm_continue_chat' );

		if ( nextArrow ) {
			nextArrow.classList.toggle( 'frm_disabled_arrow', ! enabled );
		}

		if ( continueButton ) {
			continueButton.toggleAttribute( 'disabled', ! enabled );
		}
	}

	/**
	 * Hide email field and put the Stripe link authentication element to be used in its place.
	 *
	 * @since 3.0
	 *
	 * @param {Element} emailField
	 * @param {Element} emailInput
	 * @param {Element} authenticationMountTarget
	 * @returns {void}
	 */
	function replaceEmailField( emailField, emailInput, authenticationMountTarget ) {
		var emailLabel;

		emailField.insertBefore( authenticationMountTarget, emailInput );
		emailInput.type = 'hidden';
		emailLabel      = emailField.querySelector( '.frm_primary_label' );

		if ( emailLabel ) {
			// Authentication elements include an Email label already, so hide the Formidable label.
			emailLabel.style.display = 'none';
		}
	}

	/**
	 * Returns the layout for the Stripe Link elements.
	 *
	 * @since 3.1.7
	 *
	 * @returns {string}
	 */
	function getLayout() {
		const settings = getStripeSettings()[0];
		return settings.hasOwnProperty( 'layout' ) && settings.layout || 'tabs';
	}

	/**
	 * The Payment element for Stripe link includes credit card, country, and postal code.
	 * When a new Stripe link account is being set up, it will also include an additional block underneath that asks for Phone Number and Full Name.
	 *
	 * @since 3.0
	 *
	 * @param {Element} cardElement
	 * @returns {void}
	 */
	function insertPaymentElement( cardElement ) {
		var paymentElement;

		// Add the payment element above the credit card field.
		// With Stripe Link this is used instead of a Credit Card field (it still includes Credit Card fields).
		cardElement.parentNode.insertBefore( createMountTarget( 'frm-payment-element' ), cardElement );

		paymentElement = elements.create(
			'payment',
			{
				layout: {
					type: getLayout()
				},
				defaultValues: {
					billingDetails: {
						name: getFullNameValueDefault(),
						phone: ''
					}
				}
			}
		);
		paymentElement.mount( '.frm-payment-element' );
		paymentElement.on( 'change', handlePaymentElementChange );

		function handlePaymentElementChange( event ) {
			stripeLinkElementIsComplete = event.complete;
			toggleButtonsOnPaymentElementChange( cardElement );
		}
	}

	/**
	 * @since 3.0
	 *
	 * @param {Element} cardElement
	 * @returns {void}
	 */
	function toggleButtonsOnPaymentElementChange( cardElement ) {
		var form = cardElement.closest( '.frm-show-form' );

		if ( form.querySelector( '.frm_chat_form' ) && ! chatWrapperIsOnFinalQuestion( form ) ) {
			// Treat a conversational form differenently if not on the the final question.
			if ( form.querySelector( '.frm_active_chat_field .frm-payment-element' ) ) {
				toggleConversationalButtons( form, stripeLinkElementIsComplete );
			}
			return;
		}

		// Handle final question or non-conversational form.
		if ( readyToSubmitStripeLink( form ) ) {
			thisForm = form;
			running  = 0;
			enableSubmit();
		} else {
			disableSubmit( form );
		}
	}

	/**
	 * The submit button toggles enabled/disabled based on if the payment element is "complete" or not.
	 *
	 * @since 3.0
	 *
	 * @param {Element} form
	 * @returns {bool}
	 */
	function readyToSubmitStripeLink( form ) {
		const formId = getFormIdForForm( form );
		return linkAuthenticationElementIsComplete && stripeLinkElementIsComplete && ! submitButtonIsConditionallyDisabled( formId );
	}

	/**
	 * Check a form's form_id input for a form ID value.
	 *
	 * @param {HTMLElement} form
	 * @returns {number}
	 */
	function getFormIdForForm( form ) {
		return parseInt( form.querySelector( '[name="form_id"]' ).value );
	}

	/**
	 * Check Stripe settings for first name and last name fields for the default "Full Name" value for Stripe Link's payment element.
	 *
	 * @since 3.0
	 *
	 * @returns {string}
	 */
	function getFullNameValueDefault() {
		var nameValues, firstNameField, lastNameField;
		nameValues      = [];
		firstNameField  = checkForStripeSettingField( 'first_name' );
		if ( false !== firstNameField ) {
			nameValues.push( getSettingFieldValue( firstNameField ) );
		}
		lastNameField   = checkForStripeSettingField( 'last_name' );
		if ( false !== lastNameField ) {
			nameValues.push( getSettingFieldValue( lastNameField ) );
		}
		return nameValues.join( ' ' );
	}

	/**
	 * Get value for a form field. It may be a field container or a hidden input if it's a field from another page.
	 *
	 * @since 3.0
	 *
	 * @param {Element} field
	 * @returns {String}
	 */
	function getSettingFieldValue( field ) {
		var value;
		if ( 'hidden' === field.getAttribute( 'type' ) ) {
			value = field.value;
		} else {
			value = field.querySelector( 'input' ).value;
		}
		return value;
	}

	/**
	 * Check Stripe settings and DOM for a mapped email field.
	 *
	 * @since 3.0
	 *
	 * @returns {Element|false}
	 */
	function checkForEmailField() {
		return checkForStripeSettingField( 'email' );
	}

	/**
	 * @param {string} settingKey supports 'first_name', 'last_name', and 'email'.
	 * @returns {Element|false}
	 */
	function checkForStripeSettingField( settingKey ) {
		var settingField = false;

		each( getStripeSettings(), checkStripeSettingForField );

		function checkStripeSettingForField( currentSetting ) {
			var currentSettingValue, settingIsWrappedAsShortcode, currentFieldId, fieldMatchByKey, fieldContainer, hiddenInput;

			if ( 'string' !== typeof currentSetting[ settingKey ] || ! currentSetting[ settingKey ].length ) {
				return;
			}

			currentSettingValue         = currentSetting[ settingKey ];
			settingIsWrappedAsShortcode = '[' === currentSettingValue[0] && ']' === currentSettingValue[ currentSettingValue.length - 1 ];

			if ( settingIsWrappedAsShortcode ) {
				// Email is wrapped as a shortcode.
				currentFieldId = currentSettingValue.substr( 1, currentSettingValue.length - 2 );

				if ( isNaN( currentFieldId ) ) {
					// If it is not a number, try as a field key.
					fieldMatchByKey = fieldContainer = document.getElementById( 'field_' + currentFieldId );
				}
			} else {
				// First name and last name are not wrapped as shortcodes.
				currentFieldId = currentSettingValue;
			}

			if ( fieldMatchByKey ) {
				fieldContainer = fieldMatchByKey.closest( '.frm_form_field' );
			} else {
				fieldContainer = document.getElementById( 'frm_field_' + currentFieldId + '_container' );
			}

			if ( ! fieldContainer ) {
				hiddenInput = document.querySelector( 'input[name="item_meta[' + currentFieldId + ']"]' );

				if ( ! hiddenInput ) {
					if ( 'first_name' === settingKey ) {
						hiddenInput = document.querySelector( 'input[name="item_meta[' + currentFieldId + '][first]"]' );
					} else if ( 'last_name' === settingKey ) {
						hiddenInput = document.querySelector( 'input[name="item_meta[' + currentFieldId + '][last]"]' );
					}
				}

				if ( hiddenInput ) {
					settingField = hiddenInput;
					return false;
				}

				return;
			}

			settingField = fieldContainer;
			return false;
		}

		return settingField;
	}

	/**
	 * Create and return a new element to use for mounting a Stripe element to.
	 *
	 * @since 3.0
	 *
	 * @param {string} className
	 * @returns {Element}
	 */
	function createMountTarget( className ) {
		var newElement = document.createElement( 'div' );
		newElement.className = className + ' frm_form_field form-field';
		return newElement;
	}

	/**
	 * @returns {void}
	 */
	function loadStripeCardElements() {
		elements = frmstripe.elements();

		card = elements.create(
			'card',
			{
				hidePostalCode: true,
				style: frm_stripe_vars.style
			}
		);
		card.mount( '.frm-card-element' );

		// Show validation messages on change.
		card.addEventListener( 'change', function( event ) {
			var displayError = document.getElementsByClassName( 'frm-card-errors' )[0];
			if ( typeof displayError !== 'undefined' ) {
				if ( event.error ) {
					displayError.textContent = event.error.message;
				} else {
					displayError.textContent = '';
				}
			}
		});
	}

	/**
	 * Check for Price fields on load and possibly update the intent's price.
	 * This is required when a Stripe action uses a shortcode amount when
	 * the amount never changes after load.
	 *
	 * @returns {void}
	 */
	function checkPriceFieldsOnLoad() {
		each(
			getPriceFields(),
			function( fieldId ) {
				var fieldContainer, input;

				fieldContainer = document.getElementById( 'frm_field_' + fieldId + '_container' );
				if ( ! fieldContainer ) {
					return;
				}

				input = fieldContainer.querySelector( 'input[name^=item_meta]' );
				if ( input && '' !== input.value ) {
					priceChanged( null, input, fieldId );
				}
			}
		);
	}

	/**
	 * @since 3.0
	 *
	 * @param {@array|NodeList} items
	 * @param {function} callback
	 */
	function each( items, callback ) {
		var index, length;

		length = items.length;
		for ( index = 0; index < length; index++ ) {
			if ( false === callback( items[ index ], index ) ) {
				break;
			}
		}
	}

	return {
		init: function() {
			var stripeParams = {
				locale: frm_stripe_vars.locale
			};
			if ( 'undefined' !== typeof frm_stripe_vars.account_id ) {
				stripeParams.stripeAccount = frm_stripe_vars.account_id;
			}
			frmstripe = Stripe( frm_stripe_vars.publishable_key, stripeParams );
			loadElements();
			jQuery( document ).on( 'frmPageChanged', onPageChange );
			jQuery( document ).off( 'submit.formidable', '.frm-show-form' );
			jQuery( document ).on( 'submit.frmstrp', '.frm-show-form', validateForm );
			jQuery( 'button.frm-stripe-delete-card' ).click( deleteCard );
			jQuery( document ).on( 'frmFieldChanged', priceChanged );
			checkPriceFieldsOnLoad();
		}
	};
}

frmStrpProcess = frmStrpProcessJS();

jQuery( document ).ready( frmStrpProcess.init );
