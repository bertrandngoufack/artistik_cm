<?php
/**
 * Create and manage the form action.
 */
class FrmGetResponseAction extends FrmFormAction {

	public function __construct() {
		$action_ops = array(
			'classes'  => 'frm_getresponse_icon frm_icon_font',
			'limit'    => 99,
			'active'   => true,
			'priority' => 25,
			'event'    => array( 'create', 'update' ),
			'color'    => '#00baff',
		);

		$this->FrmFormAction( 'getresponse', __( 'Add to GetResponse', 'formidable-getresponse' ), $action_ops );
	}

	public function form( $form_action, $args = array() ) {
		$form = $args['form'];

		$list_options = $form_action->post_content;
		$list_id      = $list_options['list_id'];

		$api = new FrmGetResponseAPI();
		$lists = $api->fetch_campaigns();
		if ( ! is_array( $lists ) ) {
			if ( ! empty( $lists ) ) {
				// This is an error message.
				$error = $lists;
			}
			$lists = array();
		}

		if ( ! empty( $lists ) ) {
			$list_fields = $api->fetch_custom_fields();
			if ( ! is_array( $list_fields ) ) {
				if ( ! empty( $list_fields ) ) {
					// This is an error message.
					$error = $list_fields;
				}
				$list_fields = array();
			}

			if ( method_exists( $this, 'get_form_fields' ) ) {
				$form_fields = $this->get_form_fields( $form->id );
			} else {
				$form_fields = FrmField::getAll( 'fi.form_id=' . (int) $form->id . " and fi.type not in ('break', 'divider', 'end_divider', 'html', 'captcha', 'form')", 'field_order' );
			}
		}

		$action_control = $this;
		$nonce = wp_create_nonce( 'frmgetresponse_ajax' );

		include FrmGetResponseAppController::path() . '/views/action-settings/getresponse_options.php';
	}

	public function get_defaults() {
		return array(
			'list_id'         => '',
			'cycle_day'       => '0',
			'fields'          => array(),
		);
	}

	public function get_switch_fields() {
		return array(
			'fields' => array(),
			'groups' => array( array( 'id' ) ),
		);
	}

}
