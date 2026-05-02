<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( class_exists( 'FrmAIApi' ) ) {
	class FrmTestModeAutofill extends FrmAIApi {

		const API_ROUTE = '/wp-json/s11connect/v1/autofill/';

		/**
		 * @param stdClass $answer The unsanitized answer data.
		 * @return stdClass
		 */
		protected static function sanitize_answer( $answer ) {
			return $answer;
		}
	}
}
