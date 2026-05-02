<?php
/**
 * Add and Save Global settings
 */
class FrmActiveCampaignSettingsController {

	public static function add_settings_section( $sections ) {
		$sections['activecampaign'] = array(
			'class'    => 'FrmActiveCampaignSettingsController',
			'function' => 'route',
			'name'     => 'ActiveCampaign',
			'icon'     => 'frm_activecampaign_icon frm_icon_font',
		);
		return $sections;
	}

	public static function register_actions( $actions ) {
		$actions['activecampaign'] = 'FrmActiveCampaignAction';

		include_once FrmActiveCampaignAppController::path() . '/models/FrmActiveCampaignAction.php';

		return $actions;
	}

	public static function display_form() {
		$settings = new FrmActiveCampaignSettings();
		$frm_version = FrmAppHelper::plugin_version();

		require_once FrmActiveCampaignAppController::path() . '/views/settings/form.php';
	}

	public static function process_form() {
		$settings = new FrmActiveCampaignSettings();
		$settings->update();
		$settings->store();
		$message = __( 'Settings Saved', 'frmactivecampaign' );

		require_once FrmActiveCampaignAppController::path() . '/views/settings/form.php';
	}

	public static function route() {
		$action = FrmAppHelper::get_param( 'action' );
		if ( 'process-form' == $action ) {
			return self::process_form();
		} else {
			return self::display_form();
		}
	}
}
