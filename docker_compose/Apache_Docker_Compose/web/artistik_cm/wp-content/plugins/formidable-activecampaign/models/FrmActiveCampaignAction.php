<?php
/**
 * Create and manage the form action.
 */
class FrmActiveCampaignAction extends FrmFormAction {

	public function __construct() {
		$action_ops = array(
			'classes'   => 'frm_activecampaign_icon frm_icon_font',
			'color'     => 'var(--primary-hover)',
			'limit'     => 99,
			'active'    => true,
			'priority'  => 25,
			'event'     => array( 'create', 'update', 'import' ),
		);

		$this->FrmFormAction( 'activecampaign', __( 'Add to ActiveCampaign', 'frmactivecampaign' ), $action_ops );
	}

	public function form( $form_action, $args = array() ) {
		$list_options = $form_action->post_content;
		$list_id = $list_options['list_id'];

		$api = new FrmActiveCampaignAPI();
		$lists = $api->get_lists();
		$list_fields = $api->get_custom_fields();
		$ac_forms    = $api->get_forms();

		$form = $args['form'];
		if ( method_exists( $this, 'get_form_fields' ) ) {
			$form_fields = $this->get_form_fields( $form->id );
		} else {
			$form_fields = FrmField::getAll( 'fi.form_id=' . (int) $form->id . " and fi.type not in ('break', 'divider', 'end_divider', 'html', 'captcha', 'form')", 'field_order' );
		}

		$action_control = $this;

		include FrmActiveCampaignAppController::path() . '/views/action-settings/options.php';
	}

	public function get_defaults() {
		return array(
			'list_id' => '',
			'fields'  => array(),
			'ac_form' => '',
			'resubscribe'     => false,
			'send_ip_address' => 'no',
		);
	}

	public function get_switch_fields() {
		return array(
			'fields' => array(),
			'groups' => array( array( 'id' ) ),
		);
	}

	public static function clear_cache() {
		check_ajax_referer( 'frmactivecampaign_ajax', 'security', true );
		delete_transient( 'frm-activecampaign-lists' );
		delete_transient( 'frm-activecampaign-custom-fields' );
		delete_transient( 'frm-activecampaign-forms' );
		wp_die();
	}

}
