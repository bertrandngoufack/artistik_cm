<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
class FrmStrpSettingsController {

	/**
	 * Add Stripe section to Global Settings.
	 *
	 * @param array $sections
	 * @return array
	 */
	public static function add_settings_section( $sections ) {
		$sections['stripe'] = array(
			'class'    => __CLASS__,
			'function' => 'route',
			'icon'     => 'frm_icon_font frm_stripe_icon',
		);

		add_action(
			'frm_messages_settings_form',
			/**
			 * @param object $frm_settings
			 * @return void
			 */
			function( $frm_settings ) {
				$stripe_settings = FrmStrpAppHelper::get_settings()->settings;
				require FrmStrpAppHelper::plugin_path() . '/views/settings/messages.php';
			}
		);

		return $sections;
	}

	/**
	 * Handle global settings routing.
	 *
	 * @return void
	 */
	public static function route() {
		self::global_settings_form();
	}

	/**
	 * Print the Stripe section for Global settings.
	 *
	 * @param array $atts
	 * @return void
	 */
	public static function global_settings_form( $atts = array() ) {
		$atts                             = array_merge( $atts, self::get_default_settings_atts() );
		$errors                           = $atts['errors'];
		$message                          = $atts['message'];
		$settings                         = FrmStrpAppHelper::get_settings();
		$stripe_connect_is_live           = FrmStrpConnectHelper::stripe_connect_is_setup( 'live' );
		$stripe_connect_is_on_for_test    = FrmStrpConnectHelper::stripe_connect_is_setup( 'test' );
		$show_legacy_key_options_for_test = ! $stripe_connect_is_on_for_test && self::check_for_legacy_key_data( $settings, array( 'test_publish', 'test_secret' ) );
		$show_legacy_key_options_for_live = ! $stripe_connect_is_live && self::check_for_legacy_key_data( $settings, array( 'live_publish', 'live_secret' ) );
		$keys                             = array();

		if ( $show_legacy_key_options_for_test ) {
			$keys['test_publish'] = __( 'Test Publishable Key', 'formidable-stripe' );
			$keys['test_secret']  = __( 'Test Secret Key', 'formidable-stripe' );
		}

		if ( $show_legacy_key_options_for_live ) {
			$keys['live_publish'] = __( 'Live Publishable Key', 'formidable-stripe' );
			$keys['live_secret']  = __( 'Live Secret Key', 'formidable-stripe' );
		}

		include FrmStrpAppHelper::plugin_path() . '/views/settings/form.php';
	}

	/**
	 * @return array
	 */
	private static function get_default_settings_atts() {
		return array(
			'errors'  => array(),
			'message' => '',
		);
	}

	/**
	 * Check global Stripe settings for API keys.
	 *
	 * @param object $settings
	 * @param array  $keys
	 * @return bool true if old Stripe keys exist. The new Stripe Connect implementation doesn't need any keys from the user
	 */
	private static function check_for_legacy_key_data( $settings, $keys ) {
		foreach ( $keys as $key ) {
			if ( ! empty( $settings->settings->{$key} ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Handle processing changes to global Stripe Settings.
	 *
	 * @return void
	 */
	public static function process_form() {
		$atts = array(
			'errors'  => array(),
			'message' => '',
		);
		$settings = FrmStrpAppHelper::get_settings();
		$settings->update( $_POST );
		$settings->store();
	}

	/**
	 * Move the card description to the main description.
	 *
	 * @since 2.0
	 *
	 * @param array    $field_array
	 * @param stdClass $field
	 * @return array
	 */
	public static function prepare_field_desc( $field_array, $field ) {
		if ( $field->type === 'credit_card' && isset( $field_array['month_desc'] ) && ! empty( $field_array['month_desc'] ) && empty( $field_array['description'] ) ) {
			$has_stripe_action = FrmStrpActionsController::get_actions_before_submit( $field->form_id );
			if ( ! $has_stripe_action ) {
				// Fixes Pro issue #3833. We only want to move the card description if there are Stripe actions.
				// A credit card field without a Stripe action should use the month description.
				return $field_array;
			}

			$field_array['description'] = $field_array['month_desc'];
			$field_array['month_desc'] = '';
		}
		return $field_array;
	}

	/**
	 * Hide a few Credit Card field settings with CSS.
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public static function hide_cc_settings() {
		if ( ! is_callable( 'FrmAppHelper::is_admin_page' ) ) {
			return;
		}

		$editing_form = FrmAppHelper::is_admin_page( 'formidable' ) && isset( $_GET['id'] ) && isset( $_GET['frm_action'] ) && $_GET['frm_action'] === 'edit';
		if ( ! $editing_form ) {
			return;
		}

		$form_id = FrmAppHelper::simple_get( 'id', '', 'absint' );
		if ( empty( $form_id ) ) {
			return;
		}

		$has_stripe_action = FrmStrpActionsController::get_actions_before_submit( $form_id );
		if ( ! $has_stripe_action ) {
			return;
		}

		?>
<style type="text/css">
select[id^="save_cc_"], input[id^="field_options_month_placeholder_"], input[id^="field_options_cvc_placeholder_"], input[id^="field_options_cvc_desc_"], input[id^="field_options_year_placeholder_"], input[id^="field_options_year_desc_"], input[id^="field_options_cc_placeholder_"]{display:none;}
</style>
<script>
function frmHideCcOptsClick() {
	var opt = document.querySelectorAll('.frm-single-settings:not(.frm_hidden)');
	if(opt.length){
		var fid = opt[0].getAttribute('data-fid');
		frmHideCcOpts(fid);
	}
}

function frmHideCcOpts(fid) {
if(typeof fid === 'undefined'){
	fid = '';
}
var i = 0;
var frmcc = document.querySelectorAll('select[id^="save_cc_'+fid+'"], input[id^="field_options_month_placeholder_'+fid+'"], input[id^="field_options_cvc_placeholder_'+fid+'"], input[id^="field_options_cvc_desc_'+fid+'"], input[id^="field_options_year_placeholder_'+fid+'"], input[id^="field_options_year_desc_'+fid+'"], input[id^="field_options_cc_placeholder_'+fid+'"]');
for ( i = 0; i < frmcc.length; i++ ) {
	frmcc[i].parentElement.style.display = 'none';
	var frmp = frmcc[i].parentElement.previousElementSibling;
	if(frmp.tagName === 'LABEL'){
		frmp.style.display = 'none';
	}
}
var frmdes = document.querySelectorAll('input[id^=field_options_month_desc_'+fid+']');
for ( i = 0; i < frmdes.length; i++ ) {
	var thisfid = fid;
	if(fid === ''){
		thisfid = frmdes[i].id.replace( 'field_options_month_desc_', '' );
	}
	var mainDes = document.getElementsByName('field_options[description_'+ thisfid +']');
	if ( mainDes.length && mainDes[0].value != '' ) {
		frmdes[i].parentElement.style.display = 'none';
	} else {
		frmdes[i].parentElement.className = '';
	}
}
frmdes = document.querySelectorAll('input[id^=field_options_cc_desc_'+fid+']');
for ( i = 0; i < frmdes.length; i++ ) {
	if ( frmdes[i].value === '' ) {
		frmdes[i].parentElement.style.display = 'none';
	}
}
}
frmHideCcOpts();
var frmtab = document.getElementById( 'frm-options-panel-tab' );
if ( frmtab !== null ) {
	frmtab.addEventListener('click', frmHideCcOptsClick);
}
</script>
		<?php
	}
}
