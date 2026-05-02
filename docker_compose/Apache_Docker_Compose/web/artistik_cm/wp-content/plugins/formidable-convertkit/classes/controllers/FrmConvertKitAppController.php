<?php
/**
 * App controller
 *
 * @package FrmConvertKit
 */

/**
 * Class FrmConvertKitAppController
 */
class FrmConvertKitAppController {

	/**
	 * Shows the incompatible notice.
	 *
	 * @return void
	 */
	public static function show_incompatible_notice() {
		if ( FrmConvertKitAppHelper::is_compatible() ) {
			return;
		}
		?>
		<div class="notice notice-error">
			<p><?php esc_html_e( 'You are running an outdated version of Formidable Forms.', 'frm-convertkit' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Initializes plugin translation.
	 *
	 * @return void
	 */
	public static function init_translation() {
		load_plugin_textdomain( 'frm-convertkit', false, FrmConvertKitAppHelper::plugin_folder() . '/languages/' );
	}

	/**
	 * Includes addon updater.
	 *
	 * @return void
	 */
	public static function include_updater() {
		if ( class_exists( 'FrmAddon' ) ) {
			FrmConvertKitUpdate::load_hooks();
		}
	}
}
