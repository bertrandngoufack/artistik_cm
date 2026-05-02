<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class responsible for encrypting and decrypting data.
 *
 * @since 2.0
 */
final class FrmHubSpotEncryptionHelper {

	/**
	 * Key to use for encryption.
	 *
	 * @since 2.0
	 * @var string
	 */
	private $key;

	/**
	 * Salt to use for encryption.
	 *
	 * @since 2.0
	 * @var string
	 */
	private $salt;

	/**
	 * HubSpot Settings.
	 *
	 * @since 2.0
	 * @var FrmHubSpotSettings $settings
	 */
	private $settings;

	/**
	 * Constructor.
	 *
	 * @since 2.0
	 */
	public function __construct() {
		$this->settings = FrmHubSpotAppHelper::get_settings();
		$this->key      = $this->get_key();
		$this->salt     = $this->get_salt();
	}

	/**
	 * Encrypts a value.
	 *
	 * If a user-based key is set, that key is used. Otherwise the default key is used.
	 *
	 * @since 2.0
	 *
	 * @param string $value Value to encrypt.
	 * @return string|WP_Error Encrypted value, or WP_Error on failure.
	 */
	public function encrypt( $value ) {
		$method = 'aes-256-ctr';
		$ivlen  = openssl_cipher_iv_length( $method );
		$iv     = openssl_random_pseudo_bytes( $ivlen );

		$raw_value = openssl_encrypt( $value . $this->salt, $method, $this->key, 0, $iv );
		if ( ! $raw_value ) {
			return new WP_Error( 'php_openssl', __( 'Oops, Something is wrong with your openssl extension please contact your host provider.', 'formidable-hubspot' ) );
		}

		return base64_encode( $iv . $raw_value ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Decrypts a value.
	 *
	 * If a user-based key is set, that key is used. Otherwise the default key is used.
	 *
	 * @since 2.0
	 *
	 * @param string $raw_value Value to decrypt.
	 * @return string|WP_Error Decrypted value, or WP_Error on failure.
	 */
	public function decrypt( $raw_value ) {
		$raw_value = base64_decode( $raw_value, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

		$method = 'aes-256-ctr';
		$ivlen  = openssl_cipher_iv_length( $method );
		$iv     = substr( $raw_value, 0, $ivlen );

		$raw_value = substr( $raw_value, $ivlen );

		$value = openssl_decrypt( $raw_value, $method, $this->key, 0, $iv );
		if ( ! $value || substr( $value, - strlen( $this->salt ) ) !== $this->salt ) {
			return new WP_Error( 'php_openssl', __( 'Oops, It seems your WordPress salt key has been changed or unreadable.', 'formidable-hubspot' ) );
		}

		return substr( $value, 0, - strlen( $this->salt ) );
	}

	/**
	 * Gets the default encryption key to use.
	 *
	 * @since 2.0
	 *
	 * @return string Default (not user-based) encryption key.
	 */
	private function get_key() {
		if ( ! empty( $this->settings->encrypt_key ) ) {
			return $this->settings->encrypt_key;
		}

		$secret_key = $this->generate_crypto_bytes();
		$this->settings->update( array( 'frm_hubspot_encrypt_key' => $secret_key ) );
		$this->settings->store();

		return $secret_key;
	}

	/**
	 * Gets the default encryption salt to use.
	 *
	 * @since 2.0
	 *
	 * @return string Encryption salt.
	 */
	private function get_salt() {
		if ( ! empty( $this->settings->encrypt_salt ) ) {
			return $this->settings->encrypt_salt;
		}

		$secret_salt = $this->generate_crypto_bytes();
		$this->settings->update( array( 'frm_hubspot_encrypt_salt' => $secret_salt ) );
		$this->settings->store();

		return $secret_salt;
	}

	/**
	 * Generate crypto key.
	 *
	 * @since 2.0
	 *
	 * @return string key for encryption.
	 */
	private function generate_crypto_bytes() {
		// Ready for easy migration from old php versions.
		if ( version_compare( phpversion(), '7.0', '>=' ) ) {
			return bin2hex( random_bytes( 25 ) );
		}

		return wp_generate_password( 20 );
	}
}
