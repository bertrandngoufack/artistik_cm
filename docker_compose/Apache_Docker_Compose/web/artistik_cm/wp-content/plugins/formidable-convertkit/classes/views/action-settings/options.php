<?php
/**
 * Form action options
 *
 * @package FrmConvertKit
 *
 * @var FrmConvertKitAPI $cvk_api        ConvertKit API.
 * @var array|string     $cvk_forms      List of ConvertKit forms.
 * @var array|string     $cvk_sequences  List of ConvertKit sequences.
 * @var array|string     $cvk_fields     List of ConvertKit custom fields.
 * @var stdClass         $form_action    Form action object.
 * @var array            $args           See {@see FrmConvertKitAction::form()}.
 * @var FrmFormAction    $action_control Action control.
 */

$api_action  = $form_action->post_content['api_action'];
$form_id     = $form_action->post_content['form_id'];
$sequence_id = $form_action->post_content['sequence_id'];
$update      = ! empty( $form_action->post_content['update'] );
$first_name  = $form_action->post_content['first_name'];
$email       = $form_action->post_content['email'];
$tags        = $form_action->post_content['tags'];

$settings = new FrmConvertKitSettings();
if ( empty( $settings->settings->api_secret ) ) {
	?>
	<p>
		<?php
		printf(
			// Translators: %1$s: link open tag, %2$s: link close tag.
			esc_html__( 'The API secret is empty. Please add the API secret in %1$sGlobal settings > ConvertKit%2$s.', 'frm-convertkit' ),
			'<a href="' . esc_url( admin_url( 'admin.php?page=formidable-settings&t=convertkit_settings' ) ) . '">',
			'</a>'
		)
		?>
	</p>
	<?php
	return;
}
?>
<div class="frm_grid_container frm-cvk-action-settings" data-action="<?php echo esc_attr( $api_action ); ?>">
	<?php $id_attr = $action_control->get_field_id( 'api_action' ); ?>
	<p class="frm6">
		<label for="<?php echo esc_attr( $id_attr ); ?>">
			<?php esc_html_e( 'Action', 'frm-convertkit' ); ?>
			<span class="frm_required">*</span>
		</label>

		<select name="<?php echo esc_attr( $action_control->get_field_name( 'api_action' ) ); ?>" id="<?php echo esc_attr( $id_attr ); ?>" class="frm-cvk-action-dropdown">
			<option value=""><?php esc_html_e( 'Subscribe to a form or update', 'frm-convertkit' ); ?></option>
			<option value="subscribe_sequence" <?php selected( 'subscribe_sequence', $api_action ); ?>><?php esc_html_e( 'Subscribe to a sequence or update', 'frm-convertkit' ); ?></option>
			<option value="remove_tag" <?php selected( 'remove_tag', $api_action ); ?>><?php esc_html_e( 'Remove tag(s) from a subscriber', 'frm-convertkit' ); ?></option>
			<option value="unsubscribe" <?php selected( 'unsubscribe', $api_action ); ?>><?php esc_html_e( 'Unsubscribe', 'frm-convertkit' ); ?></option>
		</select>
	</p>

	<p class="frm6 frm-cvk-form-setting">
		<?php if ( is_array( $cvk_forms ) ) : ?>
			<label>
				<?php esc_html_e( 'Form', 'frm-convertkit' ); ?>
				<span class="frm_required">*</span>
			</label>
			<select name="<?php echo esc_attr( $action_control->get_field_name( 'form_id' ) ); ?>">
				<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
				<?php
				foreach ( $cvk_forms as $key => $form ) :
					?>
					<option value="<?php echo esc_attr( $form->id ); ?>" <?php selected( $form_id, $form->id ); ?>>
						<?php echo esc_html( FrmAppHelper::truncate( $form->name, 40 ) ); ?>
					</option>
				<?php endforeach; ?>
			</select>

			<a href="#" class="frm-cvk-reload" data-method="get_forms">
				<?php esc_html_e( 'Reload', 'frm-convertkit' ); ?>
				<span class="spinner"></span>
			</a>
		<?php else : ?>
			<?php echo esc_html( $cvk_forms ); ?>
		<?php endif; ?>
	</p>

	<p class="frm6 frm-cvk-sequence-setting">
		<?php if ( is_array( $cvk_sequences ) ) : ?>
			<label>
				<?php esc_html_e( 'Sequence', 'frm-convertkit' ); ?>
				<span class="frm_required">*</span>
			</label>
			<select name="<?php echo esc_attr( $action_control->get_field_name( 'sequence_id' ) ); ?>">
				<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
				<?php
				foreach ( $cvk_sequences as $key => $sequence ) :
					?>
					<option value="<?php echo esc_attr( $sequence->id ); ?>" <?php selected( $sequence_id, $sequence->id ); ?>>
						<?php echo esc_html( FrmAppHelper::truncate( $sequence->name, 40 ) ); ?>
					</option>
				<?php endforeach; ?>
			</select>

			<a href="#" class="frm-cvk-reload" data-method="get_sequences">
				<?php esc_html_e( 'Reload', 'frm-convertkit' ); ?>
				<span class="spinner"></span>
			</a>
		<?php else : ?>
			<?php echo esc_html( $cvk_sequences ); ?>
		<?php endif; ?>
	</p>

	<p class="frm_has_shortcodes frm6">
		<?php $id_attr = $action_control->get_field_id( 'email' ); ?>
		<label for="<?php echo esc_attr( $id_attr ); ?>"><?php esc_html_e( 'Email', 'frm-convertkit' ); ?></label>
		<input type="text" id="<?php echo esc_attr( $id_attr ); ?>" name="<?php echo esc_attr( $action_control->get_field_name( 'email' ) ); ?>" value="<?php echo esc_attr( $email ); ?>" />
	</p>

	<p class="frm_has_shortcodes frm6 frm-cvk-subscriber-update-setting">
		<?php $id_attr = $action_control->get_field_id( 'first_name' ); ?>
		<label for="<?php echo esc_attr( $id_attr ); ?>"><?php esc_html_e( 'First name', 'frm-convertkit' ); ?></label>
		<input type="text" id="<?php echo esc_attr( $id_attr ); ?>" name="<?php echo esc_attr( $action_control->get_field_name( 'first_name' ) ); ?>" value="<?php echo esc_attr( $first_name ); ?>" />
	</p>

	<p class="frm_has_shortcodes frm6 frm-cvk-tags-setting">
		<?php $id_attr = $action_control->get_field_id( 'tags' ); ?>
		<label for="<?php echo esc_attr( $id_attr ); ?>"><?php esc_html_e( 'Tags (separate by commas)', 'frm-convertkit' ); ?></label>
		<input type="text" id="<?php echo esc_attr( $id_attr ); ?>" name="<?php echo esc_attr( $action_control->get_field_name( 'tags' ) ); ?>" value="<?php echo esc_attr( $tags ); ?>" />
	</p>

	<?php include FrmConvertKitAppHelper::plugin_path() . '/classes/views/action-settings/custom-fields.php'; ?>
</div>
