<?php

class FrmAwbrAction extends FrmFormAction {

	public function __construct() {
		$action_ops = array(
			'classes'   => 'frm_aweber_icon frm_icon_font',
			'limit'     => 99,
			'active'    => true,
			'priority'  => 25,
			'event'     => array( 'create' ),
		);

		$this->FrmFormAction( 'aweber', __( 'Add to AWeber', 'formidable-aweber' ), $action_ops );
	}

	public function form( $form_action, $args = array() ) {
		$list_options = $form_action->post_content;
		$list_id = $list_options['list_id'];

		$error = false;
		$account = FrmAwbrAppHelper::get_aweber_account();
		if ( $account ) {
			$lists = $account->lists;
			if ( method_exists( $this, 'get_form_fields' ) ) {
				$form_fields = $this->get_form_fields( $args['form']->id );
			} else {
				$form_fields = FrmField::getAll( 'fi.form_id=' . absint( $args['form']->id ) . " and fi.type not in ('break', 'divider', 'end_divider', 'html', 'captcha', 'form')" );
			}

			$list = FrmAwbrAppHelper::get_aweber_list( $list_id );
			if ( empty( $list ) ) {
				$error = true;
			} else {
				$list_fields = array();
				if ( $list->custom_fields ) {
					$list_fields = $list->custom_fields->data['entries'];
				}
				include_once FrmAwbrAppController::path() . '/views/action-settings/_action_scripts.php';

				if ( is_callable( array( $list, 'tags' ) ) ) {
					$tags = $list->tags();
				}
			}
		} else {
			// List ID was not in this account
			$error = true;
		}

		$action_control = $this;

		include FrmAwbrAppController::path() . '/views/action-settings/aweber_options.php';
	}

	public function get_defaults() {
		return array(
			'list_id' => '',
			'fields'  => array(
				'tags'        => '',
				'ad_tracking' => '',
			),
		);
	}

	public function get_switch_fields() {
		return array(
			'fields' => array(),
		);
	}

	public function migrate_values( $action, $form ) {
		if ( ! empty( $form->options['hide_field'] ) ) {
			$action->post_content['conditions']['send_stop'] = 'send';
			foreach ( $form->options['hide_field'] as $k => $field_id ) {
				$action->post_content['conditions'][] = array(
					'hide_field'        => $field_id,
					'hide_field_cond'   => isset( $form->options['hide_field_cond'][ $k ] ) ? $form->options['hide_field_cond'][ $k ] : '==',
					'hide_opt'          => isset( $form->options['hide_opt'][ $k ] ) ? $form->options['hide_opt'][ $k ] : '',
				);
			}
			unset( $action->post_content['hide_field'], $action->post_content['hide_field_cond'] );
			unset( $action->post_content['hide_opt'] );
		}
		$action->post_content['event'] = array( 'create', 'update' );

		return $action;
	}
}
