<?php
/**
 * Save and retrieve the Global settings.
 */
class FrmActiveCampaignSettings {

	public $settings;
	private $option = 'frm_activecampaign_options';

	public function __construct() {
		$this->get_options();
	}

	public function default_options() {
		return array(
			'api_key' => '',
			'api_url' => '',
		);
	}

	public function get_settings() {
		return $this->settings;
	}

	private function set_default_options() {
		$default_settings = $this->default_options();

		if ( empty( $this->settings ) ) {
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

	public function get_options() {
		$this->settings = get_option( $this->option );
		$this->set_default_options();

		return $this->settings;
	}

	public function update() {
		$process_form = FrmAppHelper::get_post_param( 'process_form', '', 'sanitize_text_field' );
		if ( ! wp_verify_nonce( $process_form, 'process_form_nonce' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that', 'formidable' ) );
		}

		$settings = $this->default_options();

		foreach ( $settings as $setting => $default ) {
			if ( isset( $_POST[ 'frm_activecampaign_' . $setting ] ) ) {
				$this->settings->{$setting} = sanitize_text_field( wp_unslash( $_POST[ 'frm_activecampaign_' . $setting ] ) );
			}
			unset( $setting, $default );
		}
	}

	/**
	 * Save the posted value in the database
	 */
	public function store() {
		update_option( $this->option, $this->settings );
	}
}
