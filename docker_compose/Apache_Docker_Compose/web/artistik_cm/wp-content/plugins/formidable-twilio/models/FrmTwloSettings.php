<?php

class FrmTwloSettings {

	private $option_name = 'frm_twlo_options';
	public $settings;

	public function __construct() {
		$settings = get_option( $this->option_name );

		if ( is_object( $settings ) || is_array( $settings ) ) {
			$this->settings = (array) $settings;
		}

		$this->set_default_options(); // Sets defaults for unset options
	}

	private function set_default_options() {
		$settings = $this->default_options();

		foreach ( $settings as $setting => $default ) {
			if ( ! isset( $this->settings[ $setting ] ) ) {
				$this->settings[ $setting ] = $default;
			}
		}
	}

	public function update( $params ) {
		$settings = $this->default_options();
		$changed  = false;

		foreach ( $settings as $setting => $default ) {
			if ( isset( $params[ 'frm_twlo_' . $setting ] ) ) {
				$new_value = sanitize_text_field( $params[ 'frm_twlo_' . $setting ] );
				if ( $new_value !== $this->settings[ $setting ] ) {
					$this->settings[ $setting ] = $new_value;
					$changed = true;
				}
			}
		}

		if ( $changed ) {
			// Reset the cache if the keys change.
			delete_option( 'frmtwlo_numbers' );
		}
	}

	private function default_options() {
		return array(
			'account_sid' => '',
			'auth_token'  => '',
		);
	}

	public function store() {
		// Save the posted value in the database
		update_option( $this->option_name, $this->settings );
	}

}
