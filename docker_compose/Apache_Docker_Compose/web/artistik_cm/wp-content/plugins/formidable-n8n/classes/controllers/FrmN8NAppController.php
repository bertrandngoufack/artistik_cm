<?php
/**
 * App controller
 *
 * @package FrmN8N
 */

/**
 * Class FrmN8NAppController
 */
class FrmN8NAppController {

	/**
	 * Shows the incompatible notice.
	 *
	 * @return void
	 */
	public static function show_incompatible_notice() {
		if ( FrmN8NAppHelper::is_compatible() ) {
			return;
		}
		?>
		<div class="notice notice-error">
			<p><?php esc_html_e( 'You are running an outdated version of Formidable Forms.', 'formidable-n8n' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Initializes plugin translation.
	 *
	 * @return void
	 */
	public static function init_translation() {
		load_plugin_textdomain( 'formidable-n8n', false, FrmN8NAppHelper::plugin_folder() . '/languages/' );
	}

	/**
	 * Includes addon updater.
	 *
	 * @return void
	 */
	public static function include_updater() {
		if ( class_exists( 'FrmAddon' ) ) {
			FrmN8NUpdate::load_hooks();
		}
	}

	/**
	 * Loads form action scripts.
	 *
	 * @return void
	 */
	public static function admin_scripts() {
		if ( ! FrmAppHelper::is_form_builder_page() || ! in_array( FrmAppHelper::get_param( 'frm_action' ), array( 'settings', 'update_settings' ) ) ) {
			return;
		}

		$form_id = FrmAppHelper::get_param( 'id', '', 'get', 'intval' );
		if ( ! $form_id ) {
			return;
		}

		wp_enqueue_script(
			'frm-n8n-admin',
			FrmN8NAppHelper::plugin_url() . '/js/admin.js',
			array( 'formidable_dom' ),
			FrmN8NAppHelper::$plug_version,
			true
		);

		wp_localize_script(
			'frm-n8n-admin',
			'frmN8NAdminI18n',
			array(
				'addAllFieldsConfirm' => __( 'All current mapping items will be removed. Do you want to do that?', 'formidable-n8n' ),
			)
		);
	}
}
