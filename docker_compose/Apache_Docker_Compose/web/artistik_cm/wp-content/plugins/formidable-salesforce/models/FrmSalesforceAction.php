<?php
/**
 * Create and customize a new form action type.
 */
class FrmSalesforceAction extends FrmFormAction {

	public function __construct() {
		$action_ops = array(
			'classes'   => 'frm_icon_font frm_salesforce_icon frm-inverse',
			'color'     => '#00A0E0',
			'limit'     => 99,
			'active'    => true,
			'priority'  => 25,
			'event'     => array( 'create' ),
		);

		$this->FrmFormAction( 'salesforce', __( 'Add to Salesforce', 'formidable-salesforce' ), $action_ops );
	}

	public function form( $form_action, $args = array() ) {
		$this->form_id = $args['form']->id;
		$list_options = $form_action->post_content;
		$object_id = $list_options['object_id'];

		$salesforce = new FrmSalesforceAPI();
		$objects = $salesforce->fetch_custom_objects();

		if ( $object_id ) {
			$salesforce = new FrmSalesforceAPI();
			$object_fields = $salesforce->fetch_object_fields( $object_id );

			if ( method_exists( $this, 'get_form_fields' ) ) {
				$form_fields = $this->get_form_fields( $this->form_id );
			} else {
				$form_fields = FrmField::getAll( 'fi.form_id=' . (int) $this->form_id . " and fi.type not in ('break', 'divider', 'end_divider', 'html', 'captcha', 'form')", 'field_order' );
			}
		}

		$action_control = $this;
		include FrmSalesforceAppController::path() . '/views/action-settings/salesforce_options.php';
	}

	public function get_defaults() {
		return array(
			'object_id' => '',
			'fields'    => array(),
			'update_field' => '',
		);
	}

	public function get_switch_fields() {
		return array(
			'fields' => array(),
			'groups' => array( array( 'id' ) ),
		);
	}

	public static function clear_cache() {
		check_ajax_referer( 'frmsalesforce_ajax', 'security', true );
		delete_transient( 'frm-salesforce-objects' );
		delete_transient( 'frm-salesforce-object-fields' );
		wp_die();
	}
}
