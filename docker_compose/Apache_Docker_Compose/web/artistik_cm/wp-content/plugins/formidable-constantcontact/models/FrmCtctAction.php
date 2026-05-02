<?php

class FrmCtctAction extends FrmFormAction {

	public function __construct() {
		$action_ops = array(
			'classes'   => 'frm_constant_contact_icon frm_icon_font',
			'color'     => 'rgb(0,160,210)',
			'limit'     => 99,
			'active'    => true,
			'priority'  => 25,
			'event'     => array( 'create', 'update' ),
		);

		$this->FrmFormAction( 'constantcontact', __( 'Add to Constant Contact', 'formidable-ctct' ), $action_ops );
	}

	public function form( $form_action, $args = array() ) {
		$form_id = $args['form']->id;
		$list_options = $form_action->post_content;
		$list_id = $list_options['list_id'];

		$ctct_api = new FrmCtctAPI();
		$lists    = $ctct_api->get_lists();

		if ( isset( $lists['error'] ) ) {
			echo '<div class="frm_error_style">' .
				FrmAppHelper::kses( $lists['error'], array( 'a' ) ) .
				'</div>'; // WPCS: XSS ok.
			return;
		}

		$labels = array_column( $lists, 'label' );
		array_multisort( $labels, SORT_ASC, $lists );

		$fields = $this->get_all_fields();

		if ( method_exists( $this, 'get_form_fields' ) ) {
			$form_fields = $this->get_form_fields( $form_id );
		} else {
			$form_fields = FrmField::getAll(
				array(
					'fi.form_id'     => (int) $form_id,
					'fi.type not' => FrmField::no_save_fields(),
				),
				'field_order'
			);
		}

		$action_control = $this;
		include FrmCtctAppController::path() . '/views/action-settings/options.php';
	}

	private function get_all_fields() {
		$fields = array(
			'email'        => array(
				'name' => __( 'Email Address', 'formidable-ctct' ),
				'type' => array( 'email', 'text', 'user_id', 'lookup' ),
			),
			'first_name'   => __( 'First Name', 'formidable-ctct' ),
			'last_name'    => __( 'Last Name', 'formidable-ctct' ),
			'company_name' => __( 'Company Name', 'formidable-ctct' ),
			'job_title'    => __( 'Job Title', 'formidable-ctct' ),
			'home_address' => array(
				'name' => __( 'Home Address', 'formidable-ctct' ),
				'type' => array( 'address' ),
			),
			'work_address' => array(
				'name' => __( 'Work Address', 'formidable-ctct' ),
				'type' => array( 'address' ),
			),
			'home_phone'   => array(
				'name' => __( 'Home phone', 'formidable-ctct' ),
				'type' => array( 'phone', 'number', 'text', 'lookup' ),
			),
			'work_phone'   => array(
				'name' => __( 'Work phone', 'formidable-ctct' ),
				'type' => array( 'phone', 'number', 'text', 'lookup' ),
			),
			'anniversary'  => array(
				'name' => __( 'Anniversary', 'formidable-ctct' ),
				'type' => array( 'date' ),
			),
			'birthday'  => array(
				'name' => __( 'Birthday', 'formidable-ctct' ),
				'type' => array( 'date' ),
			),
		);

		$ctct_api = new FrmCtctAPI();
		$custom_fields = $ctct_api->get_custom_fields();
		foreach ( $custom_fields as $key => $field ) {
			$fields[ 'custom_field_' . $key ] = $field;
		}

		return apply_filters( 'frm_ctct_fields', $fields );
	}

	public function get_defaults() {
		return array(
			'list_id' => '',
			'fields'  => array(),
		);
	}

	public function get_switch_fields() {
		return array(
			'fields' => array(),
		);
	}

	public static function clear_cache() {
		check_ajax_referer( 'frmctct_ajax', 'security', true );
		delete_transient( 'frm-ctct-lists' );
		delete_transient( 'frm-ctct-fields' );
		wp_die();
	}
}
