<?php
/**
 * Create and manage the form action.
 */
class FrmConvertKitAction extends FrmFormAction {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$action_ops = array(
			'classes'   => 'frm_icon_font frm_convertkit_icon',
			'color'     => '#fb6068',
			'limit'     => 99,
			'active'    => true,
			'priority'  => 25,
			'event'     => array( 'create', 'update', 'import' ),
		);

		$this->FrmFormAction( 'convertkit', __( 'Add to ConvertKit', 'frm-convertkit' ), $action_ops );
	}

	/**
	 * Displays action settings.
	 *
	 * @param stdClass $form_action Form action object.
	 * @param array    $args        Args.
	 * @return void
	 */
	public function form( $form_action, $args = array() ) {
		$options = $form_action->post_content;
		$form_id = $options['form_id'];

		$cvk_api       = new FrmConvertKitAPI();
		$cvk_forms     = $cvk_api->get_forms();
		$cvk_sequences = $cvk_api->get_sequences();
		$cvk_fields    = $cvk_api->get_custom_fields();

		$form = $args['form'];
		if ( method_exists( $this, 'get_form_fields' ) ) {
			$form_fields = $this->get_form_fields( $form->id );
		} else {
			$form_fields = FrmField::getAll( 'fi.form_id=' . (int) $form->id . " and fi.type not in ('break', 'divider', 'end_divider', 'html', 'captcha', 'form')", 'field_order' );
		}

		$action_control = $this;

		include FrmConvertKitAppHelper::plugin_path() . '/classes/views/action-settings/options.php';
	}

	/**
	 * Gets action setting defaults.
	 *
	 * @return array
	 */
	public function get_defaults() {
		return array(
			'api_action'  => '',
			'form_id'     => '',
			'sequence_id' => '',
			'email'       => '',
			'first_name'  => '',
			'tags'        => '',
			'fields'      => array(),
		);
	}
}
