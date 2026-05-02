<?php

class FrmMailPoetAction extends FrmFormAction {

	public function __construct() {
		$action_ops = array(
			'classes'   => 'frm_mailpoet_icon frm_icon_font',
			'limit'     => 99,
			'active'    => true,
			'priority'  => 25,
			'event'     => array( 'create', 'update' ),
			'color'     => 'var(--orange)',
		);

		$this->FrmFormAction( 'mailpoet', __( 'Add to MailPoet', 'frmmailpoet' ), $action_ops );
	}

	public function form( $form_action, $args = array() ) {
		$list_options = $form_action->post_content;
		$list_id      = $list_options['list_id'];

		if ( is_callable( '\MailPoet\API\API::MP' ) && defined( 'PDO::MYSQL_ATTR_INIT_COMMAND' ) ) {
			$api         = \MailPoet\API\API::MP( 'v1' );
			$lists       = $api->getLists();
			$list_fields = $api->getSubscriberFields();
		} else {
			$lists       = array();
			$list_fields = array();
		}

		if ( method_exists( $this, 'get_form_fields' ) ) {
			$form_fields = $this->get_form_fields( $args['form']->id );
		} else {
			$form_fields = FrmField::getAll( 'fi.form_id=' . (int) $args['form']->id . " and fi.type not in ('break', 'divider', 'end_divider', 'html', 'captcha', 'form')", 'field_order' );
		}

		if ( ! is_array( $lists ) || is_wp_error( $lists ) ) {
			$lists = false;
		}

		$action_control = $this;
		include FrmMailPoetAppController::path() . '/views/action-settings/mailpoet_options.php';
	}

	public function get_defaults() {
		return array(
			'list_id' => '',
			'fields' => array(),
			'send_confirmation_email' => 'no',
			'schedule_welcome_email' => 'no',
		);
	}

	public function get_switch_fields() {
		return array(
			'fields' => array(),
			'groups' => array( array( 'id' ) ),
		);
	}

}
