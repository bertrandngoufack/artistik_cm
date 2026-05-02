<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

// Map each field in HubSpot to a form field.
if ( is_wp_error( $list_fields ) || ! is_array( $list_fields ) ) {
	return;
}

foreach ( $list_fields as $propertygroup ) {
	if ( empty( $propertygroup->properties ) ) {
		// This heading is empty, so don't show it.
		continue;
	}

	if ( 'emailinformation' === $propertygroup->name || 'conversioninformation' === $propertygroup->name ) {
		// This heading is empty, so don't show it.
		continue;
	}

	echo '<h3>' . esc_html( $propertygroup->displayName ) . '</h3>';

	foreach ( $propertygroup->properties as $property ) {
		$is_savable = isset( $property->name ) && isset( $property->label );
		if ( ! $is_savable || $property->readOnlyValue ) {
			continue;
		}

		$is_form_field = $property->formField || 'hs_analytics_source' === $property->name;
		if ( ! $is_form_field ) {
			continue;
		}

		$is_select   = ! empty( $property->options );
		$use_hs_opts = 'hs_analytics_source' === $property->name;
		if ( $is_select ) {
			$opts = array();
			foreach ( $property->options as $opt ) {
				$opts[ $opt->value ] = $opt->label;
			}
		}

		?>
		<p class="frm6 frm_form_field">
			<label>
				<?php
				echo esc_html( ucfirst( $property->label ) );
				if ( 'text' !== $property->fieldType ) {
					echo ' <span class="howto remote-field-info">(' . esc_html( $property->fieldType ) . ')</span>';
				}
				echo 'Email' == $property->label ? ' <span class="frm_required">*</span>' : '';
				?>
			</label>
			<select name="<?php echo esc_attr( $action_control->get_field_name( 'fields' ) ); ?>[<?php echo esc_attr( $property->name ); ?>]">
				<option value=""> </option>
				<?php
				if ( $use_hs_opts ) {
					foreach ( $opts as $v => $opt ) {
						$selected = ( isset( $list_options['fields'][ $property->name ] ) && $list_options['fields'][ $property->name ] == $v ) ? ' selected="selected"' : '';
						?>
						<option value="<?php echo esc_attr( $v ); ?>" <?php echo esc_attr( $selected ); ?>>
							<?php echo esc_html( $opt ); ?>
						</option>
						<?php
					}
				} else {
					foreach ( $form_fields as $form_field ) {
						if ( isset( $field_types[ $property->fieldType ] ) && ! in_array( $form_field->type, $field_types[ $property->fieldType ] ) ) {
							continue;
						}
						$selected = ( isset( $list_options['fields'][ $property->name ] ) && $list_options['fields'][ $property->name ] == $form_field->id ) ? ' selected="selected"' : '';
						?>
						<option value="<?php echo esc_attr( $form_field->id ); ?>" <?php echo esc_attr( $selected ); ?>>
							<?php
							echo esc_html( FrmAppHelper::truncate( $form_field->name, 40 ) );

							if ( 'name' === $form_field->type ) {
								switch ( $property->name ) {
									case 'firstname':
										echo ' (';
										esc_html_e( 'First', 'formidable' ); // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
										echo ')';
										break;

									case 'lastname':
										echo ' (';
										esc_html_e( 'Last', 'formidable' ); // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
										echo ')';
										break;
								}
							}
							?>
						</option>
						<?php
					}
				}
				?>
			</select>
			<?php if ( $is_select && ! $use_hs_opts ) { ?>
			<span class="howto remote-field-info">
				<?php
				esc_html_e( 'Limited options.', 'formidable-hubspot' );
				if ( count( $opts ) < 10 ) {
					echo ' ' . esc_html( implode( ', ', array_keys( $opts ) ) );
				}
				?>
			</span>
			<?php } ?>
		</p>
		<?php
	}
}
