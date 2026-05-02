<?php
/**
 * Save and retrieve the Global settings.
 *
 * @package FrmConvertKit
 */
class FrmConvertKitSettings {

	/**
	 * Settings data.
	 *
	 * @var stdClass|null
	 */
	public $settings;

	/**
	 * Option name.
	 *
	 * @var string
	 */
	private $option = 'frm_convertkit_options';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->get_options();
	}

	/**
	 * Gets default settings.
	 *
	 * @return array
	 */
	public function default_options() {
		return array(
			'api_secret' => '',
		);
	}

	/**
	 * Gets settings.
	 *
	 * @return stdClass|null
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * Sets default settings.
	 *
	 * @return void
	 */
	private function set_default_options() {
		$default_settings = $this->default_options();

		if ( ! $this->settings ) {
			$this->settings = new stdClass();
		} else {
			$this->settings = (object) $this->settings;
		}

		foreach ( $default_settings as $setting => $default ) {
			if ( ! isset( $this->settings->{$setting} ) ) {
				$this->settings->{$setting} = $default;
			}
		}
	}

	/**
	 * Gets options.
	 *
	 * @return stdClass|null
	 */
	public function get_options() {
		$settings = get_option( $this->option );
		if ( $settings ) {
			$this->settings = (object) $settings;
		}
		$this->set_default_options();

		return $this->settings;
	}

	/**
	 * Updates form action.
	 *
	 * @return void
	 */
	public function update() {
		$process_form = FrmAppHelper::get_post_param( 'process_form', '', 'sanitize_text_field' );
		if ( ! wp_verify_nonce( $process_form, 'process_form_nonce' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that', 'formidable' ) );
		}

		$settings = $this->default_options();

		foreach ( $settings as $setting => $default ) {
			if ( isset( $_POST[ 'frm_convertkit_' . $setting ] ) ) {
				$this->settings->{$setting} = sanitize_text_field( wp_unslash( $_POST[ 'frm_convertkit_' . $setting ] ) );
			}
			unset( $setting, $default );
		}
	}

	/**
	 * Save the posted value in the database
	 *
	 * @return void
	 */
	public function store() {
		update_option( $this->option, $this->settings );
	}
}
