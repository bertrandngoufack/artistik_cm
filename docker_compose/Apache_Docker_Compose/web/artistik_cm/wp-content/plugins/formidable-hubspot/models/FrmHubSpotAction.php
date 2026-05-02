<?php

class FrmHubSpotAction extends FrmFormAction {

	public function __construct() {
		$action_ops = array(
			'classes'  => 'frmfont frm_hubspot_icon',
			'color'    => '#ff7a59',
			'limit'    => 99,
			'active'   => true,
			'priority' => 25,
			'event'    => array( 'create', 'update' ),
			'tooltip'  => __( 'Add to HubSpot', 'formidable-hubspot' ),
		);

		$this->FrmFormAction( 'hubspot', __( 'HubSpot', 'formidable-hubspot' ), $action_ops );
	}

	public function form( $form_action, $args = array() ) {
		$form = $args['form'];

		$list_options = $form_action->post_content;
		$list_id      = $list_options['list_id'];
		$action_control = $this;

		$frm_hubspot_settings = FrmHubSpotAppHelper::get_settings();
		$api = new FrmHubSpotAuthHelper( FrmHubSpotAppHelper::get_active_authorization_class() );

		$is_valid_pat_exists = ( new FrmHubSpotPAT() )->is_valid_private_app_access_token( $frm_hubspot_settings->private_app_access_token );
		$is_valid_connection_exists = ! empty( $frm_hubspot_settings->formidable_hubspot_oauth ) || ! is_wp_error( $is_valid_pat_exists );
		// Clear the cache each time there is no valid connection to ensure we are update with possible connection.
		if ( ! $is_valid_connection_exists ) {
			$api->clear_cache();
			include FrmHubSpotAppHelper::path() . '/views/settings/form.php';
			return;
		}

		// Prepare data.
		$lists              = $api->fetch_lists();
		$list_array         = ! empty( $lists->lists ) ? $lists->lists : array();
		$company_list_array = ! empty( $lists->companies ) ? $lists->companies : array();
		$list_fields        = $api->fetch_ordered_properties();

		$view_path = FrmHubSpotAppHelper::path() . '/views/action-settings/';

		// These are required for the _match_fields.php view.
		$field_types = $this->allowed_field_selections();
		$form_fields = $this->get_form_fields_for_settings( (int) $form->id );

		include $view_path . 'hubspot_options.php';
	}

	/**
	 * @param int $form_id
	 * @return array<stdClass>
	 */
	private function get_form_fields_for_settings( $form_id ) {
		if ( method_exists( $this, 'get_form_fields' ) ) {
			$form_fields = $this->get_form_fields( $form_id );

			return array_filter(
				$form_fields,
				function ( $form_field ) {
					return 'hubspot' !== $form_field->type;
				}
			);
		}

		$no_save_fields   = FrmField::no_save_fields();
		$no_save_fields[] = 'hubspot';
		return FrmField::getAll(
			array(
				'fi.type not' => $no_save_fields,
				'fi.form_id'  => $form_id,
			),
			'field_order'
		);
	}

	/**
	 * @since 1.07
	 */
	public function allowed_field_selections() {
		$allow_fields = array(
			'date' => array( 'hidden', 'date' ),
		);
		return apply_filters( 'frm_hubspot_field_types', $allow_fields );
	}

	public function get_defaults() {
		return array(
			'hubspot_hidden_field' => '',
			'list_id'              => '',
			'fields'               => array(),
		);
	}

	public function get_switch_fields() {
		return array(
			'fields' => array(),
			'groups' => array( array( 'id' ) ),
		);
	}

	/**
	 * Make sure the action includes a HubSpot hidden field.
	 *
	 * @since 2.0
	 *
	 * @param array         $new_instance New settings for this instance as input by the user via form().
	 * @param array|WP_Post $old_instance Old settings for this instance.
	 *
	 * @return array Settings to save.
	 */
	public function update( $new_instance, $old_instance ) {
		// Prevents adding a HubSpot field to form when there is no OAuth enabled.
		if ( ! FrmHubSpotAppHelper::get_active_authorization_class() instanceof FrmHubSpotOAuth ) {
			return $new_instance;
		}

		if ( isset( $old_instance->post_content ) ) {
			$old_content = maybe_unserialize( $old_instance->post_content );
			if ( ! empty( $old_content['hubspot_hidden_field'] ) ) {
				$new_instance['post_content']['hubspot_hidden_field'] = $old_content['hubspot_hidden_field'];
				return $new_instance;
			}
		}

		$form_id = $new_instance['menu_order'];
		$new_instance['post_content']['hubspot_hidden_field'] = FrmHubSpotField::maybe_add_hidden_field( $form_id, __( 'HubSpot', 'formidable-hubspot' ) );

		return $new_instance;
	}

	public static function clear_cache() {
		_deprecated_function( __METHOD__, '1.06', 'FrmHubSpotSettingsController::maybe_clear_cache()' );
		FrmHubSpotSettingsController::maybe_clear_cache();
	}

}
