<?php

class FrmAwbrAppHelper {

	public static function get_default_options() {
		return array(
			'aweber' => 0,
			'awbr_list' => array(),
		);
	}

	public static function is_formidable_v2() {
		$frm_version = FrmAppHelper::plugin_version();
		return version_compare( $frm_version, '1.07.20', '>' );
	}

	public static function get_entry_or_post_value( $entry, $field_id ) {
		$value = '';
		if ( ! empty( $entry ) && isset( $entry->metas[ $field_id ] ) ) {
			$value = $entry->metas[ $field_id ];
		} elseif ( isset( $_POST['item_meta'][ $field_id ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$value = wp_unslash( $_POST['item_meta'][ $field_id ] );
			FrmAppHelper::sanitize_value( 'sanitize_text_field', $value );
		}
		return $value;
	}

	public static function wrong_account_message( $echo = false ) {
		/* translators: %1$s: Start link HTML, %2$s: end link HTML */
		$translation  = __( 'Your AWeber account info is not correct. %1$sUpdate it now.%2$s', 'formidable-aweber' );
		$settings_url = admin_url( 'admin.php?page=formidable-settings' );

		if ( $echo ) {
			printf( esc_html( $translation ), '<a href="' . esc_url( $settings_url ) . '">', '</a>' );
		}

		return sprintf( esc_html( $translation ), '<a href="' . esc_url( $settings_url ) . '">', '</a>' );
	}

	public static function get_aweber_list( $list_id ) {
		$account = self::get_aweber_account();
		$list = array();
		if ( $account ) {
			try {
				$list = $account->loadFromUrl( '/accounts/' . $account->id . '/lists/' . $list_id );
			} catch ( Exception $exc ) {
				$list = false;
			}
		}
		return $list;
	}

	/**
	* Get the Aweber account for the saved settings
	*/
	public static function get_aweber_account() {
		if ( ! class_exists( 'FrmAWeberAPI' ) ) {
			require_once FrmAwbrAppController::path() . '/aweber_api/aweber.php';
		}

		$account = false;
		try {
			$frm_awbr_settings = new FrmAwbrSettings();
			$aweber = new FrmAWeberAPI( $frm_awbr_settings->settings->consumer_key, $frm_awbr_settings->settings->consumer_secret );
			$account = $aweber->getAccount( $frm_awbr_settings->settings->access_key, $frm_awbr_settings->settings->access_secret );
		} catch ( Exception $exc ) {
			//$error = self::wrong_account_message();
		}
		return $account;
	}
}
