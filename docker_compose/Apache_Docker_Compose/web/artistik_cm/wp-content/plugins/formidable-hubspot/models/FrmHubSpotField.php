<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to access this file directly.' );
}

/**
 * HubSpot hidden field.
 * This hidden field would be created on each action update.
 * We are storing the HubSpot contact ID in this field per entry so we could easily follow the right entry for CRUD.
 *
 * @since 2.0
 */
class FrmHubSpotField extends FrmFieldHidden {

	/**
	 * The field type.
	 *
	 * @since 2.0
	 *
	 * @var string
	 */
	protected $type = 'hubspot';

	/**
	 * This field collects input.
	 *
	 * @since 2.0
	 *
	 * @var bool
	 */
	protected $has_input = false;

	/**
	 * This field type uses the normal HTML.
	 *
	 * @since 2.0
	 *
	 * @var bool
	 */
	protected $has_html = false;

	/**
	 * Add a hubspot hidden field.
	 *
	 * @since 2.0
	 *
	 * @param int    $form_id
	 * @param string $name
	 * @return bool|int
	 */
	public static function maybe_add_hidden_field( $form_id, $name ) {
		$new_values                = FrmFieldsHelper::setup_new_vars( 'hubspot', $form_id );
		$new_values['name']        = $name;
		$new_values['field_order'] = 0;
		return FrmField::create( $new_values );
	}

	/**
	 * @since 2.0
	 * @return array
	 */
	protected function field_settings_for_type() {
		$settings            = parent::field_settings_for_type();
		$settings['default'] = false;
		return $settings;
	}

	/**
	 * @since 2.0
	 * @return string
	 */
	public function prepare_field_html( $args ) {
		if ( ! is_callable( 'FrmProFieldsHelper::insert_hidden_fields' ) ) {
			return '';
		}

		$html = '';
		$args = $this->fill_display_field_values( $args );

		$this->field['html_id'] = $args['html_id'];

		ob_start();
		FrmProFieldsHelper::insert_hidden_fields( $this->field, $args['field_name'], $this->field['value'] );
		$html .= ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * @since 2.0
	 * @return string
	 */
	protected function include_form_builder_file() {
		return FrmHubSpotAppHelper::path() . '/views/field/backend-builder.php';
	}

}
