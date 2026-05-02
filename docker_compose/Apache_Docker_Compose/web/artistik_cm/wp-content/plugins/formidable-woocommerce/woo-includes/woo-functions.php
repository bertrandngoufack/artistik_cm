<?php

if ( class_exists( 'WC_Formidable_App_Helper' ) && ! function_exists( 'is_woocommerce_active' ) ) {
	function is_woocommerce_active() {
		_deprecated_function( __FUNCTION__, '1.10', 'WC_Formidable_App_Helper::is_woocommerce_active' );
		return WC_Formidable_App_Helper::is_woocommerce_active();
	}
}
