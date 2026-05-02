<?php
/**
 * Class FrmN8NFormAction
 *
 * @package FrmN8N
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmN8NFormAction
 */
class FrmN8NFormAction extends FrmFormAction {

	/**
	 * Track the fields for setting. Keys are form IDs and values are array of fields.
	 *
	 * @var array
	 */
	protected static $fields_for_setting = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$action_ops = array(
			'classes'  => 'frm_n8n_icon frm_icon_font',
			'color'    => '#EA4B71',
			'limit'    => 99,
			'active'   => true,
			'priority' => 41,
			'event'    => array( 'draft', 'create', 'update', 'delete', 'import' ),
			'tooltip'  => __( 'Send to n8n', 'formidable-n8n' ),
		);

		if ( is_callable( array( 'FrmAbandonmentActionController', 'email_action_control' ) ) ) {
			$action_ops['event'][] = 'abandoned';
		}

		$this->FrmFormAction( 'n8n', __( 'n8n', 'formidable-n8n' ), $action_ops );
	}

	/**
	 * Prints the action settings.
	 *
	 * @param WP_Post $instance Form action post object.
	 * @param array   $args     Args.
	 *
	 * @return void
	 */
	public function form( $instance, $args = array() ) {
		$form_action = $instance;

		include FrmN8NAppHelper::plugin_path() . '/classes/views/action-settings/settings.php';
	}

	/**
	 * Gets form action default data.
	 *
	 * @return array
	 */
	public function get_defaults() {
		return array(
			'webhook' => '',
			'token'   => FrmN8NFormActionController::generate_token(),
			'mapping' => array(),
		);
	}

	/**
	 * Gets fields for setting.
	 *
	 * @param int $form_id Form ID.
	 *
	 * @return array
	 */
	protected function get_fields_for_setting( $form_id ) {
		if ( isset( self::$fields_for_setting[ $form_id ] ) ) {
			return self::$fields_for_setting[ $form_id ];
		}

		$form_fields = $this->get_form_fields( $form_id );

		$fields = array();

		foreach ( $form_fields as $form_field ) {
			$fields[] = array(
				'id'  => $form_field->id,
				'key' => $form_field->field_key,
			);
		}

		self::$fields_for_setting[ $form_id ] = $fields;

		return $fields;
	}
}
