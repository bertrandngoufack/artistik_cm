<?php
/**
 * Save and retrieve the Global settings.
 */
class FrmGetResponseSettings {
	public $settings;

	public function __construct() {
		$this->set_default_options();
	}

	public function default_options() {
		return array(
			'api_key'    => '',
		);
	}

	public function set_default_options( $settings = false ) {
		$default_settings = $this->default_options();

		if ( ! $settings ) {
			$settings = $this->get_options();
		} elseif ( true === $settings ) {
			$settings = new stdClass();
		}

		if ( ! isset( $this->settings ) ) {
			$this->settings = new stdClass();
		}

		foreach ( $default_settings as $setting => $default ) {
			if ( is_object( $settings ) && isset( $settings->{$setting} ) ) {
				$this->settings->{$setting} = $settings->{$setting};
			}

			if ( ! isset( $this->settings->{$setting} ) ) {
				$this->settings->{$setting} = $default;
			}
		}
	}

	public function get_options() {
		$settings = get_option( 'frm_getresponse_options' );

		if ( ! is_object( $settings ) ) {
			if ( $settings ) { // Workaround for W3 total cache conflict.
				$settings = unserialize( serialize( $settings ) );
			} else {
				// If unserializing didn't work.
				if ( ! is_object( $settings ) ) {
					if ( $settings ) { // Workaround for W3 total cache conflict.
						$settings = unserialize( serialize( $settings ) );
					} else {
						$settings = $this->set_default_options( true );
					}
					$this->store();
				}
			}
		} else {
			$this->set_default_options( $settings );
		}

		return $this->settings;
	}

	public function update( $params ) {
		$settings = $this->default_options();

		foreach ( $settings as $setting => $default ) {
			if ( isset( $params[ 'frm_getresponse_' . $setting ] ) ) {
				$this->settings->{$setting} = $params[ 'frm_getresponse_' . $setting ];
			}
			unset( $setting, $default );
		}
	}

	public function store() {
		// Save the posted value in the database.
		update_option( 'frm_getresponse_options', $this->settings );
	}
}
