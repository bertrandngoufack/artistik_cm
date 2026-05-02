<?php

if ( ! is_array( $list_fields ) ) {
	$list_fields = array();
}

$list_fields[] = array(
	'name' => __( 'Email', 'formidable-aweber' ),
	'id'   => 'email',
);
$list_fields[] = array(
	'name' => __( 'Full Name', 'formidable-aweber' ),
	'id'   => 'name',
);
$list_fields   = array_reverse( $list_fields );

$field_name = $action_control->get_field_name( 'fields' );

foreach ( $list_fields as $list_field ) {
	if ( is_numeric( $list_field['id'] ) ) {
		$list_field['id'] = $list_field['name'];
	}
	?>

<p>
	<label class="frm_left_label">
		<?php echo esc_html( $list_field['name'] ); ?>
	</label>
	<select name="<?php echo esc_attr( $field_name ); ?>[<?php echo esc_attr( $list_field['id'] ); ?>]">
		<option value="">- <?php esc_html_e( 'Select Field', 'formidable-aweber' ); ?> -</option>
		<?php
		foreach ( $form_fields as $form_field ) {
			$selected = ( isset( $list_options['fields'][ $list_field['id'] ] ) && $list_options['fields'][ $list_field['id'] ] == $form_field->id );
			?>
			<option value="<?php echo esc_attr( $form_field->id ); ?>" <?php echo $selected ? ' selected="selected"' : ''; ?>>
				<?php echo esc_html( stripslashes( $form_field->name ) ); ?>
			</option>
		<?php } ?>
	</select>
</p>
<?php } ?>
<p class="frm_has_shortcodes">
	<label>
		<?php esc_html_e( 'Ad Tracking', 'formidable-aweber' ); ?>
	</label>
	<input type="text" name="<?php echo esc_attr( $field_name ); ?>[ad_tracking]" value="<?php echo esc_attr( $list_options['fields']['ad_tracking'] ); ?>" class="large-text" id="ad_tracking" />
</p>
<p class="frm_has_shortcodes">
	<label>
		<?php esc_html_e( 'Tags', 'formidable-aweber' ); ?>
	</label>
	<input type="text" name="<?php echo esc_attr( $field_name ); ?>[tags]" value="<?php echo esc_attr( $list_options['fields']['tags'] ); ?>" class="large-text" id="tags" />
</p>
<?php if ( ! empty( $tags ) ) { ?>
	<div id="frm_aweber_tags_wrapper">
		<b><?php esc_html_e( 'Previous tags:', 'formidable-aweber' ); ?></b>
		<?php
		$last_tag = end( $tags );
		foreach ( $tags as $tag_value ) {
			echo '<a class="frm-aweber-tag" href="#">' . esc_html( $tag_value ) . '</a>';
			if ( $tag_value !== $last_tag ) {
				echo ', ';
			}
		}
		?>
	</div>
	<script type="text/javascript">
		(function() {
			document.addEventListener( 'click', function( event ) {
				if ( Array.from( event.target.classList ).indexOf( 'frm-aweber-tag' ) >= 0 ) {
					event.preventDefault();
					onFrmAweberTagClick( event.target );
				}			
			});

			function onFrmAweberTagClick( tag ) {
				var valueIndex, value, newValue,
					tags = document.getElementById( 'tags' ),
					initialValues = tags.value.split( ',' ),
					filteredValues = new Array();

				for ( valueIndex in initialValues ) {
					value = initialValues[ valueIndex ].trim();
					if ( value.length && filteredValues.indexOf( value ) === -1 ) {
						filteredValues.push( value );
					}
				}

				newValue = tag.textContent.trim();
				if ( filteredValues.indexOf( newValue ) === -1 ) {
					filteredValues.push( newValue );
				}

				tags.value = filteredValues.join( ', ' );
			}
		})();
	</script>
<?php } ?>
