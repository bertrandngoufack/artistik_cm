<?php

class FrmTwloAction extends FrmFormAction {

	public function __construct() {
		$action_ops = array(
			'classes'  => 'frm_sms_icon frm_icon_font',
			'color'    => '#ff7a59',
			'limit'    => 99,
			'active'   => true,
			'priority' => 41,
			'event'    => array( 'create', 'update', 'delete' ),
		);
		$this->FrmFormAction( 'twilio', __( 'Send Twilio SMS', 'frmtwlo' ), $action_ops );
	}

	public function form( $form_action, $args = array() ) {
		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		$frm_twlo_settings = new FrmTwloSettings();
		$phone_numbers     = FrmTwloAppController::get_phone_numbers();
		$test_numbers      = array(
			'15005550006' => __( 'Test number: +1 (500) 555-0006', 'frmtwlo' ),
		);

		if ( is_array( $phone_numbers ) ) {
			$phone_numbers = $phone_numbers + $test_numbers;
		}

		$use_custom  = 'custom' === $form_action->post_content['from'];
		$has_from    = $form_action->post_content['from'] && ! $use_custom;
		$is_selected = false;

		include FrmTwloAppController::path() . '/views/_twilio_action.php';
	}

	public function get_defaults() {
		return array(
			'to'      => '',
			'from'    => '',
			'message' => '',
			'custom'  => '',
		);
	}
}
