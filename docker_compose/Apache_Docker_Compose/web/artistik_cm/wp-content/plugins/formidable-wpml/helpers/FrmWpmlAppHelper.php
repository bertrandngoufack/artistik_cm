<?php

class FrmWpmlAppHelper {

	public static function plugin_folder() {
		return basename( self::plugin_path() );
	}

	public static function plugin_path() {
		return dirname( dirname( __FILE__ ) );
	}

	public static function plugin_url() {
		return plugins_url( '', self::plugin_path() . '/formidable-wpml.php' );
	}

	/**
	 * @since 1.05
	 */
	public static function get_default_language() {
		return apply_filters( 'wpml_default_language', null );
	}

	/**
	 * @since 1.05
	 */
	public static function get_current_language() {
		return apply_filters( 'wpml_current_language', null );
	}

	/**
	 * Returns the first part of a string, up to the specified number of characters.
	 *
	 * Uses mb_substr when available, which preserves special characters.
	 *
	 * @since 1.07
	 *
	 * @param int $string Original string.
	 * @param int $length Number of characters of the string to be returned.
	 *
	 * @return string The first part of the original string, up to the specified number of characters.
	 */
	public static function get_safe_substring( $string, $length = 160 ) {
		return ( function_exists( 'mb_substr' ) ) ? mb_substr( $string, 0, $length ) : substr( $string, 0, $length );
	}

	/**
	 * Prepends form ID to string and run through get_safe_substring().
	 *
	 * @since 1.11
	 *
	 * @param string $string  The string.
	 * @param int    $form_id Form ID.
	 * @return string
	 */
	public static function prepend_form_id_and_get_safe_substring( $string, $form_id ) {
		$string = $form_id . '_' . $string;
		$string = self::get_safe_substring( $string );
		return $string;
	}
}
