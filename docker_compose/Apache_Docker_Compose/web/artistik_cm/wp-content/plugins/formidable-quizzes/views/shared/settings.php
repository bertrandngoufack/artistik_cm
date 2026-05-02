<?php
/**
 * Shared view used in Form Settings as well as scored quiz settings
 *
 * @package FrmQuizzes
 * @since 3.1.7
 *
 * @var array $settings Settings array.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to access this file directly.' );
}
?>
<h3>
	<?php
	esc_html_e( 'Quiz Settings', 'formidable-quizzes' );
	if ( get_class( $this ) === 'FrmQuizzesAction' ) {
		FrmAppHelper::tooltip_icon( __( 'Choose settings for your Quiz Actions.', 'formidable-quizzes' ) );
	} else {
		FrmAppHelper::tooltip_icon( __( 'Choose randomization settings for your form fields.', 'formidable-quizzes' ) );
	}
	?>
</h3>
<div class="frm_grid_container">
	<?php
	foreach ( $settings as $key => $setting ) {
		$name_attr = $this->get_field_name( $key );
		$id_attr   = $this->get_field_id( $key );
		$value     = $this->get_setting_value( $key );
		?>
		<div class="frm_form_field <?php echo esc_attr( isset( $setting['class'] ) ? $setting['class'] : '' ); ?>">
			<?php
			switch ( $setting['type'] ) {
				case 'checkbox':
					?>
					<label>
						<input
							type="checkbox"
							id="<?php echo esc_attr( $id_attr ); ?>"
							name="<?php echo esc_attr( $name_attr ); ?>"
							value="1"
							<?php checked( $value, '1' ); ?>
						/>
						<?php echo esc_html( $setting['label'] ); ?>
					</label>
					<?php
					break;

				case 'select':
					?>
					<label for="<?php echo esc_attr( $id_attr ); ?>"><?php echo esc_html( $setting['label'] ); ?></label>
					<select name="<?php echo esc_attr( $name_attr ); ?>" id="<?php echo esc_attr( $id_attr ); ?>">
						<?php foreach ( $setting['options'] as $option_key => $option_value ) : ?>
							<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $value, $option_key ); ?>>
								<?php echo esc_html( $option_value ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<?php
					break;

				case 'toggle':
					FrmHtmlHelper::toggle(
						$id_attr,
						$name_attr,
						array(
							'div_class' => 'with_frm_style frm_toggle',
							'checked'   => ! empty( $value ),
							'echo'      => true,
						)
					);
					echo '<label for="' . esc_attr( $id_attr ) . '">' . esc_html( $setting['label'] ) . '</label>';
					break;

				case 'image':
					$image_filename = '';
					if ( ! is_numeric( $value ) || ! wp_attachment_is_image( $value ) ) {
						// The image may be deleted, or it may have been imported from another site.
						$value = '';
					}
					?>
					<div class="frm_image_preview_wrapper">
						<input type="hidden" class="frm_image_id" name="<?php echo esc_attr( $name_attr ); ?>" value="<?php echo esc_attr( $value ); ?>" />
						<div class="frm_image_preview_frame<?php echo $value ? '' : ' frm_hidden'; ?>">
							<div class="frm_image_styling_frame" style="margin-left: 0;">
								<?php
								if ( ! $value ) {
									?>
									<img src="" class="frm_hidden" />
									<?php
								} else {
									$image_filename = basename( get_attached_file( $value ) );
									echo wp_get_attachment_image( $value );
								}
								?>

								<div class="frm_image_data">
									<div class="frm_image_preview_title"><?php echo esc_attr( $image_filename ); ?></div>
									<div href="javascript:void(0)" class="frm_remove_image_option" title="<?php esc_attr_e( 'Remove image', 'formidable-pro' ); ?>" tabindex="0" role="button">
										<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_delete_icon' ); ?>
										<?php esc_attr_e( 'Delete', 'formidable-pro' ); ?>
									</div>
								</div>
							</div>
						</div>
						<button type="button" class="frm_choose_image_box frm_button frm_no_style_button<?php echo $value ? ' frm_hidden' : ''; ?>" style="margin-left: 0;">
							<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_upload_icon' ); ?>
							<?php esc_attr_e( 'Upload image', 'formidable-pro' ); ?>
						</button>
					</div>
					<?php
					unset( $image_filename );
					break;

				case 'rte':
					$editor_args = array(
						'textarea_name' => $name_attr,
						'textarea_rows' => 6,
						'editor_class'  => 'frm_not_email_message',
					);
					?>
					<div><?php wp_editor( $value, $id_attr, $editor_args ); ?></div>
					<?php
					unset( $editor_args );
					break;
			}
			?>

			<?php if ( ! empty( $setting['help'] ) ) : ?>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php echo esc_attr( $setting['help'] ); ?>"></span>
			<?php endif; ?>
		</div>
		<?php
	}
	?>
</div>
