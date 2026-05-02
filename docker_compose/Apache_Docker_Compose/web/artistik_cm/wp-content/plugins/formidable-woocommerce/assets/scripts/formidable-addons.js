jQuery(document).ready(function($) {

	function init_fp_addon_totals() {
		var $variations_form, hasTotal;

		hasTotal = document.getElementById('formidable-addons-total');
		if ( hasTotal === null ) {
			return;
		}

		// monitor any changes that go on in a Formidable form on the product page right before the add to cart button
		$('.product .cart').on( 'change', 'input', function() {
			wc_fp_update_totals();
		});

		// Hide default product price
		$('.product .price, .single_variation_wrap .single_variation').hide();

		$variations_form = $( '.variations_form' );

		// Hide variation total when it is shown
		$variations_form.on( 'show_variation', function() {
			$('.product .price:not(.fp-product-addon-totals), .single_variation_wrap .single_variation').hide();
		});

		// watch the variation form and any time there's a change save the new variation price
		$variations_form.on('found_variation', function( event, variation ) {
			wc_fp_save_variation_price( $(this), variation );
		});

		// display the FP totals right away
		wc_fp_update_totals();
	}
	init_fp_addon_totals();


	// take the passed in calculated value and display it on the front end
	function wc_fp_update_totals() {

		// get the Formidable forms calcuated value
		calc_value = $('.product .cart input.frm_final_total').val();

		// cache the jquery selector
		var $cart = $('.product form.cart');
		var $fp_totals = $cart.find("#formidable-addons-total");

		// get the base product price
		var base_value = wc_fp_get_base_price( $cart, $fp_totals );

		// some people may want to apply addons irrespective of quantity
		// in that case use the filter '' to change this value to false
		if ( wc_fp_addons_params.apply_per_qty ) {
			// determine the quantity
			var qty = parseFloat( $cart.find('input.qty').val() );
			if ( qty <= 0 || isNaN( qty ) ) {
				qty = 1;
			}

			// multiple the quantity of products ordered by the add on cost
			calc_value *= qty;
		}

		// get the new html
		var html = wc_fp_create_html( calc_value, base_value );

		// make sure we have some html
		if ( html ) {
			// display the totals area
			$fp_totals.html( html );
		}
		
	}


	// when a variation selected get the price and store it
	function wc_fp_save_variation_price( $variation_form, variation ) {
		
		var $totals  = $('#formidable-addons-total');
		var $amounts = $( variation.price_html ).find('.amount');

		if ( $amounts.length ) {
			product_price = $amounts.last().text();
			product_price = product_price.replace( wc_fp_addons_params.currency_format_thousand_sep, '' );
			product_price = product_price.replace( wc_fp_addons_params.currency_format_decimal_sep, '.' );
			product_price = product_price.replace(/[^0-9\.]/g, '');
			product_price = parseFloat( product_price );

			$totals.data( 'price', product_price );
		}
		$variation_form.trigger('woocommerce-formidable-product-addons-update');

		// update the totals
		wc_fp_update_totals();
		
	}

	function getTotalForGroupProduct( products ) {
		var key, price, qty, total = 0;

		for ( key in products ) {
			if ( products.hasOwnProperty( key ) ) {
				price = products[ key ];
				qty = document.querySelector( `.product form.cart [name="quantity[${key}]"]`).value;
				if ( qty ) {
					total += parseFloat( qty * price );
				}
			}
		}

		return total;
	}

	/**
	 * @param {Object} $cart 
	 * @param {Object} $fp_totals 
	 * @returns {Number}
	 */
	function wc_fp_get_base_price( $cart, $fp_totals ) {
		var qty, basePrice;
		// get the base price (already saved in the html)
		basePrice = $fp_totals.data( 'price' );

		if ( ! document.querySelector( '.product form.cart [name="quantity"]' ) ) {
			return getTotalForGroupProduct( basePrice );
		}

		// get the quantity from the quantity field
		var qty = parseFloat( $cart.find('input.qty').val() );

		// make sure we have both a quantity and base price
		if ( basePrice > 0 && qty > 0 ) {
			basePrice = parseFloat( qty * basePrice );
		}

		return basePrice;
	}


	// create the HTML for totals area
	function wc_fp_create_html( calc_value, base_value ) {

		var total = parseFloat( base_value ) + parseFloat( calc_value );

		var formatted_addon_total = accounting.formatMoney( total, {
			symbol 		: wc_fp_addons_params.currency_format_symbol,
			decimal 	: wc_fp_addons_params.currency_format_decimal_sep,
			thousand	: wc_fp_addons_params.currency_format_thousand_sep,
			precision 	: wc_fp_addons_params.currency_format_num_decimals,
			format		: wc_fp_addons_params.currency_format
		} );

		result = "<p class='price fp-product-addon-totals'><span class='fp-product-addon-label'>" + wc_fp_addons_params.i18n_total + "</span> <span class='amount fp-product-addon-amount'>" + formatted_addon_total + "</span></p>";

		return result;
	}

	function addAntispamTokenOnWooCommerceFormSubmit() {
		document.addEventListener( 'submit', listenForFormSubmit );

		function listenForFormSubmit( event ) {
			var formContainer;

			if ( -1 === ( ' ' + event.target.className + ' ' ).indexOf( ' cart ' ) ) {
				// Not submitting a WooCommerce cart so exit early.
				return;
			}

			formContainer = event.target.querySelector( '.frm_forms' );
			if ( ! formContainer ) {
				// Not formidable, exit early.
				return;
			}

			if ( ! formContainer.hasAttribute( 'data-token' ) || null !== formContainer.querySelector( '[name="antispam_token"]' ) ) {
				return;
			}

			antispamInput = document.createElement( 'input' );
			antispamInput.type = 'hidden';
			antispamInput.name = 'antispam_token';
			antispamInput.value = formContainer.getAttribute( 'data-token' );
			formContainer.appendChild( antispamInput );
		}
	}

	function addAntispamTokenOnDropzoneUpload() {
		var cart, length, index, formId, formContainer;

		if ( 'undefined' === typeof window.__frmDropzone ) {
			return;
		}

		cart = document.querySelector( 'form.cart' );
		if ( ! cart ) {
			return;
		}

		length = window.__frmDropzone.length;
		for ( index = 0; index < length; ++index ) {
			formId        = window.__frmDropzone[ index ].formID;
			formContainer = cart.querySelector( '#frm_form_' + formId + '_container' );

			if ( ! formContainer ) {
				continue;
			}

			if ( formContainer.hasAttribute( 'data-token' ) ) {
				cart.setAttribute( 'data-token', formContainer.getAttribute( 'data-token' ) );
			}
		}
	} 

	addAntispamTokenOnWooCommerceFormSubmit();
	addAntispamTokenOnDropzoneUpload();
});