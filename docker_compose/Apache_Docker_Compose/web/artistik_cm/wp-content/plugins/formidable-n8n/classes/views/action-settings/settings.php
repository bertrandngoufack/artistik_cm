<?php
/**
 * View for n8n form action settings
 *
 * @package FrmN8N
 *
 * @var object           $form_action Form action post object.
 * @var FrmN8NFormAction $this        Form action object.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$webhook_id_attr = $this->get_field_id( 'webhook' );
?>
<p class="frm_form_field">
	<label for="<?php echo esc_attr( $webhook_id_attr ); ?>"><?php esc_html_e( 'Webhook', 'formidable-n8n' ); ?></label>
	<input
		type="text"
		id="<?php echo esc_attr( $webhook_id_attr ); ?>"
		name="<?php echo esc_attr( $this->get_field_name( 'webhook' ) ); ?>"
		value="<?php echo esc_url( $form_action->post_content['webhook'] ); ?>"
	/>
</p>

<?php $token_id_attr = $this->get_field_id( 'token' ); ?>
<p class="frm_form_field">
	<label for="<?php echo esc_attr( $token_id_attr ); ?>"><?php esc_html_e( 'Authentication Token', 'formidable-n8n' ); ?></label>
	<input
		type="text"
		id="<?php echo esc_attr( $token_id_attr ); ?>"
		name="<?php echo esc_attr( $this->get_field_name( 'token' ) ); ?>"
		value="<?php echo esc_attr( $form_action->post_content['token'] ); ?>"
	/>
	<span class="description"><?php esc_html_e( 'This needs to match the token in the n8n node settings. Set to empty to generate a new one.', 'formidable-n8n' ); ?></span>
</p>

<div class="frm-n8n-mapping-wrapper" id="<?php echo esc_attr( $this->get_field_id( 'mapping_wrapper' ) ); ?>">
	<div class="frm_grid_container">
		<div class="frm4"><?php esc_html_e( 'Key', 'formidable-n8n' ); ?></div>
		<div class="frm6"><?php esc_html_e( 'Value', 'formidable-n8n' ); ?></div>
	</div>

	<div class="frm-n8n-mapping-list">
		<?php
		$name_attr = $this->get_field_name( 'mapping' );
		foreach ( $form_action->post_content['mapping'] as $key => $value ) {
			?>
			<div class="frm-n8n-mapping-item frm_grid_container frm_form_field frm-mb-xs">
				<div class="frm4">
					<input type="text" class="frm-n8n-mapping-item-key" name="<?php echo esc_attr( $name_attr ); ?>[key][]" value="<?php echo esc_attr( $key ); ?>" />
				</div>

				<div class="frm6 frm_has_shortcodes">
					<input
						type="text"
						class="frm-n8n-mapping-item-value"
						name="<?php echo esc_attr( $name_attr ); ?>[value][]"
						id="frm-n8n-mapping-item-value-<?php echo intval( random_int( 0, 999 ) ); ?>"
						value="<?php echo esc_attr( $value ); ?>"
					/>
				</div>

				<div class="frm2">
					<a href="javascript:void(0)" class="frm-n8n-remove-mapping-item" style="vertical-align:sub;">
						<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_minus1_icon' ); ?>
					</a>
					<a href="javascript:void(0)" class="frm-n8n-add-mapping-item" style="vertical-align:sub;">
						<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_plus1_icon' ); ?>
					</a>
				</div>
			</div>
			<?php
		}//end foreach
		?>
	</div>

	<?php $fields_json = wp_json_encode( $this->get_fields_for_setting( $form_action->menu_order ) ); ?>
	<p>
		<a href="#" class="frm-button-secondary frm-n8n-add-mapping-item"><?php esc_html_e( 'Add item', 'formidable-n8n' ); ?></a>
		<a
			href="#"
			class="frm-button-secondary frm-n8n-add-all-mapping-items"
			data-fields="<?php echo esc_attr( $fields_json ? $fields_json : '' ); ?>"
		><?php esc_html_e( 'Add all fields', 'formidable-n8n' ); ?></a>
	</p>

	<div class="frm-n8n-mapping-item-tmpl frm_hidden">
		<div class="frm-n8n-mapping-item frm_grid_container frm_form_field frm-mb-xs">
			<div class="frm4">
				<input type="text" class="frm-n8n-mapping-item-key" name="<?php echo esc_attr( $this->get_field_name( 'mapping' ) ); ?>[key][]" value="" />
			</div>

			<div class="frm6 frm_has_shortcodes">
				<input type="text" class="frm-n8n-mapping-item-value" name="<?php echo esc_attr( $this->get_field_name( 'mapping' ) ); ?>[value][]" value="" />
			</div>

			<div class="frm2">
				<a href="javascript:void(0)" class="frm-n8n-remove-mapping-item" style="vertical-align:sub;">
					<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_minus1_icon' ); ?>
				</a>
				<a href="javascript:void(0)" class="frm-n8n-add-mapping-item" style="vertical-align:sub;">
					<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_plus1_icon' ); ?>
				</a>
			</div>
		</div>
	</div><!-- End .frm-n8n-mapping-item-tmpl -->
</div><!-- End .frm-n8n-data-mapping -->
