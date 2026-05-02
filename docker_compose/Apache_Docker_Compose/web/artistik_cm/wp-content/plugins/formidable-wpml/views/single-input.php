<td>
	<?php
	if ( ! empty( $atts['dropdown_options'] ) ) {
		FrmWpmlSettingsController::show_dropdown_options( $name, $atts['dropdown_options'], $atts['value'] );
	} elseif ( $atts['is_long'] ) {
		?>
		<textarea name="<?php echo esc_attr( $name ); ?>[value]" class="large-text"><?php echo FrmAppHelper::esc_textarea( stripslashes( $atts['value'] ) ); // WPCS: XSS ok. ?></textarea>
	<?php } else { ?>
		<input type="text" value="<?php echo esc_attr( stripslashes( $atts['value'] ) ); ?>" name="<?php echo esc_attr( $name ); ?>[value]" class="large-text" />
	<?php } ?>
	<input type="checkbox" value="<?php echo esc_attr( ICL_STRING_TRANSLATION_COMPLETE ); ?>" id="<?php echo esc_attr( $atts['input_id'] ); ?>_status" name="<?php echo esc_attr( $name ); ?>[status]" <?php checked( $atts['complete'], ICL_STRING_TRANSLATION_COMPLETE ); ?>/>
	<label for="<?php echo esc_attr( $atts['input_id'] ); ?>_status">
		<?php esc_html_e( 'Complete', 'formidable-wpml' ); ?>
	</label>
</td>
