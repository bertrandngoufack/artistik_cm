<?php

class FrmCtctSettings {

	/**
	 * @var stdClass|null $settings
	 */
	public $settings;

	/**
	 * @var string $option_name
	 */
	public $option_name = 'frm_ctct_options';

	/**
	 * @return void
	 */
	public function __construct() {
		$this->set_default_options();
	}

	/**
	 * @return array
	 */
	private function default_options() {
		return array(
			'access_token'  => '',
			'refresh_token' => '',
			'auth_code'     => '',
			'last_checked'  => '',
			'api_key'       => '',
			'api_index'     => 0,
			'api_version'   => 1,
		);
	}

	/**
	 * @param mixed $settings
	 * @return void
	 */
	private function set_default_options( $settings = false ) {
		$default_settings = $this->default_options();

		if ( ! $settings ) {
			$settings = $this->get_options();
		}

		$this->settings = new stdClass();

		foreach ( $default_settings as $setting => $default ) {
			if ( is_object( $settings ) && isset( $settings->{$setting} ) ) {
				$this->settings->{$setting} = $settings->{$setting};
			}

			if ( ! isset( $this->settings->{$setting} ) ) {
				$this->settings->{$setting} = $default;
			}
		}
	}

	/**
	 * @return stdClass
	 */
	private function get_options() {
		$settings = get_option( $this->option_name );
		if ( ! empty( $settings ) ) {
			$this->set_default_options( $settings );
		}

		return $this->settings;
	}

	/**
	 * @param array $params
	 * @return void
	 */
	public function update( $params ) {
		$settings  = $this->default_options();
		$auth_code = $this->settings->auth_code;

		foreach ( $settings as $setting => $default ) {
			if ( isset( $params[ 'frm_ctct_' . $setting ] ) ) {
				$this->settings->{$setting} = sanitize_text_field( $params[ 'frm_ctct_' . $setting ] );
			}
			unset( $setting, $default );
		}

		if ( $this->settings->auth_code !== $auth_code ) {
			$this->exchange_auth_code_for_access_token();
		}
	}

	/**
	 * @return void
	 */
	private function exchange_auth_code_for_access_token() {
		$ctct_api = new FrmCtctAPI();
		$response = $ctct_api->get_access_token( $this->settings->auth_code );

		if ( ! $response ) {
			return;
		}

		if ( ! is_object( $response ) || ! isset( $response->access_token ) ) {
			?>
				<div class="frm_error frm_error_style"><?php echo esc_html( print_r( $response, 1 ) ); ?></div>
			<?php
			return;
		}

		$ctct_api->set_tokens( $response, $this );
		?>
			<div class="frm_updated_message"><?php esc_html_e( 'Constant Contact Authorization Code accepted' ); ?></div>
		<?php
	}

	/**
	 * @return void
	 */
	public function store() {
		update_option( $this->option_name, $this->settings );
	}

	/**
	 * @since 1.04
	 *
	 * @return bool
	 */
	public function using_legacy_api() {
		return ! empty( $this->settings->access_token ) && $this->settings->api_version < 2;
	}
}
