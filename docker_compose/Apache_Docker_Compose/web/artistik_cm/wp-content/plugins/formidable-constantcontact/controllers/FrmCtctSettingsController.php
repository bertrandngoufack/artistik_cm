<?php
class FrmCtctSettingsController {

	/**
	 * @param array $sections
	 * @return array
	 */
	public static function add_settings_section( $sections ) {
		$sections['constantcontact'] = array(
			'class'    => __CLASS__,
			'function' => 'route',
			'name'     => 'Constant Contact',
			'icon'     => 'frm_constant_contact_icon frm_icon_font',
		);
		return $sections;
	}

	/**
	 * @param array $actions
	 * @return array
	 */
	public static function register_actions( $actions ) {
		$actions['constantcontact'] = 'FrmCtctAction';

		include_once FrmCtctAppController::path() . '/models/FrmCtctAction.php';

		return $actions;
	}

	/**
	 * @since 2.01
	 *
	 * @return FrmCtctSettings
	 */
	public static function get_settings() {
		global $frm_ctct_settings;
		if ( empty( $frm_ctct_settings ) ) {
			$frm_ctct_settings = new FrmCtctSettings();
		}
		return $frm_ctct_settings;
	}

	/**
	 * @return void
	 */
	public static function display_form() {
		$frm_ctct_settings = self::get_settings();
		$settings          = $frm_ctct_settings->settings;
		$ctct_api          = new FrmCtctAPI();

		if ( 'constantcontact_settings' === FrmAppHelper::simple_get( 't' ) ) {
			$code_in_url = FrmAppHelper::simple_get( 'ctct_code' );
		}

		require_once FrmCtctAppController::path() . '/views/settings/form.php';
	}

	/**
	 * @return void
	 */
	public static function process_form() {
		$frm_ctct_settings = self::get_settings();

		$process_form = FrmAppHelper::get_post_param( 'process_form', '', 'sanitize_text_field' );
		if ( wp_verify_nonce( $process_form, 'process_form_nonce' ) ) {
			$frm_ctct_settings->update( $_POST );
			$frm_ctct_settings->store();
		}

		self::display_form();
	}

	/**
	 * @return void
	 */
	public static function route() {
		$action = FrmAppHelper::get_param( 'action' );
		if ( 'process-form' === $action ) {
			self::process_form();
			return;
		}

		self::display_form();
	}

	/**
	 * Get the Auth URL just in time for Constant Contact to redirect the user to.
	 * Called via frm_ctct_auth_url action.
	 */
	public static function auth_url() {
		if ( ! wp_verify_nonce( FrmAppHelper::simple_get( '_wpnonce', '', 'sanitize_text_field' ), '-1' ) ) {
			// TODO show an error that nonce failed to validate.
			wp_safe_redirect( admin_url( 'admin.php?page=formidable-settings&t=constantcontact_settings' ) );
			die();
		}

		$api      = new FrmCtctAPI();
		$redirect = $api->call_api_for_auth_url();
		if ( ! $redirect['success'] ) {
			wp_die( esc_html( $redirect['error'] ), 403 );
		}

		wp_redirect( $redirect['url'] );
		die();
	}

	/**
	 * Maybe echo a deprecated API warning for users using the legacy Constant Contact API.
	 *
	 * @return void
	 */
	public static function maybe_show_deprecated_api_warning() {
		$ctct_settings = self::get_settings();
		if ( ! $ctct_settings->using_legacy_api() ) {
			return;
		}
		?>
		<div class="frm_warning_style">
			<?php self::print_deprecated_api_warning_body(); ?>
		</div>
		<?php
	}

	/**
	 * @return void
	 */
	public static function print_deprecated_api_warning_body() {
		$ctct_api = new FrmCtctAPI();
		printf(
			/* translators: %1$s: Start link HTML, %2$s: end link HTML */
			esc_html__( 'Constant Contact has a new authorization service. Click %1$shere%2$s to connect to the new service.', 'formidable-ctct' ),
			'<a href="' . esc_url( $ctct_api->auth_url() ) . '">',
			'</a>'
		);
	}
}
