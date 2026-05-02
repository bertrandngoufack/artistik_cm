<?php
$copy_args = array(
	'strings_translations' => FrmWpmlString::get_strings_translations_mapping( $translations ),
);
?>
<div id="form_settings_page" class="wrap">
	<?php
	include FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php';

	if ( is_callable( 'FrmAppHelper::get_admin_header' ) ) {
		FrmAppHelper::get_admin_header( array( 'form' => $form ) );
	} else {
		?>
		<h2><?php esc_html_e( 'Translate Form', 'formidable-wpml' ); ?></h2>
		<?php
		FrmAppController::get_form_nav( $id, true );
	}
	?>

<form method="post" class="frm_wrap">

	<div class="clear"></div> 

	<div id="poststuff" class="metabox-holder">
	<div id="post-body">

		<p style="clear:left;">
			<input type="submit" value="<?php esc_attr_e( 'Update', 'formidable' ); ?>" name="update_translations" class="button-primary frm-button-primary" />
			<?php esc_html_e( 'or', 'formidable' ); ?>
			<a class="button-secondary frm-button-secondary cancel" href="<?php echo esc_url( admin_url( 'admin.php?page=formidable&frm_action=settings&id=' . absint( $id ) ) ); ?>">
				<?php esc_html_e( 'Cancel', 'formidable' ); ?>
			</a>
		</p>

		<input type="hidden" name="id" value="<?php echo esc_attr( $id ); ?>" />
		<input type="hidden" name="frm_action" value="update_translate" />
		<?php
		wp_nonce_field( 'frm_translate_form_nonce', 'frm_translate_form' );

		$list_table->search_box( __( 'Search', 'formidable-wpml' ), 'frm_wpml_search' );
		$list_table->call_display_tablenav( 'top' );
		?>
		<table class="widefat fixed striped">
			<thead>
				<tr>
					<th class="manage-column" width="170px"> </th>
					<?php
					foreach ( $langs as $lang ) {
						if ( $lang['code'] == $default_language ) {
							continue;
						}

						$col_order[] = $lang['code'];
						?>
						<th class="manage-column frm_lang_<?php echo esc_attr( $lang['code'] ); ?>">
							<?php echo esc_html( $lang['display_name'] ); ?>
						</th>
					<?php } ?>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $page_strings as $string ) {
					$col = 0;

					$dropdown_options = FrmWpmlSettingsController::maybe_get_translation_options( $string, compact( 'id', 'fields' ) );

					$copy_args['string'] = $string;
					?>
					<tr>
						<td>
							<?php echo esc_html( htmlspecialchars( stripslashes( $string->value ) ) ); ?>
						</td>
						<?php
						foreach ( $translations as $trans ) {
							if ( $trans->string_id != $string->id || ! in_array( $trans->language, $col_order ) || $trans->language == $default_language ) {
								continue;
							}

							$col++;
							$next_col = array_search( $trans->language, $col_order );
							for ( $col; $col < $next_col; $col++ ) {
								FrmWpmlSettingsController::include_single_input(
									array(
										'input_id'         => $string->id . '_' . $col_order[ $col ],
										'value'            => '',
										'is_long'          => strlen( $string->value ) > 80,
										'dropdown_options' => $dropdown_options,
									)
								);

								$copy_args['lang'] = $col_order[ $col ];
								FrmWpmlString::maybe_print_copy_data_inputs( $string->id . '_' . $col_order[ $col ], $copy_args );
							}

							FrmWpmlSettingsController::include_single_input(
								array(
									'input_id'         => $string->id . '_' . $col_order[ $col ],
									'value'            => $trans->value,
									'complete'         => $trans->status,
									'is_long'          => strlen( $string->value ) > 80,
									'dropdown_options' => $dropdown_options,
								)
							);

							$copy_args['lang'] = $col_order[ $col ];
							FrmWpmlString::maybe_print_copy_data_inputs( $trans->id, $copy_args );

							unset( $trans );
						}

						if ( $col < $lang_count ) {
							$col++;
							for ( $col; $col <= $lang_count; $col++ ) {
								FrmWpmlSettingsController::include_single_input(
									array(
										'input_id'         => $string->id . '_' . $col_order[ $col ],
										'value'            => '',
										'is_long'          => strlen( $string->value ) > 80,
										'dropdown_options' => $dropdown_options,
									)
								);

								$copy_args['lang'] = $col_order[ $col ];
								FrmWpmlString::maybe_print_copy_data_inputs( $string->id . '_' . $col_order[ $col ], $copy_args );
							}
						}
						unset( $string );
						?>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		<?php $list_table->call_display_tablenav( 'bottom' ); ?>
		<p>
			<input type="submit" value="<?php esc_attr_e( 'Update', 'formidable' ); ?>" name="update_translations" class="button-primary frm-button-primary" />
			<?php esc_html_e( 'or', 'formidable' ); ?>
			<a class="button-secondary frm-button-secondary cancel" href="<?php echo esc_url( admin_url( 'admin.php?page=formidable&frm_action=settings&id=' . absint( $id ) ) ); ?>">
				<?php esc_html_e( 'Cancel', 'formidable' ); ?>
			</a>
		</p>
	</div>

	</div>

	</form>

</div>

<script type="text/javascript">
jQuery(document).ready(function($){
	$('select[name^="frm_wpml"], input[name^="frm_wpml"]:not([type=checkbox])').change(frmWPMLComplete);
	document.getElementById( 'frm_wpml_search-search-input' ).addEventListener( 'keydown', function( event ) {
		if ( event.key !== 'Enter' ) {
			return;
		}
		event.preventDefault();
		document.getElementById( 'search-submit' ).click();
	});

	document.getElementById( 'search-submit' ).addEventListener( 'click', function( event ) {
		event.preventDefault();
		const currentUrl = new URL(window.location.href);

		// Update the query parameter
		currentUrl.searchParams.set('s', document.getElementById( 'frm_wpml_search-search-input' ).value);
		window.history.replaceState({}, '', currentUrl);
		this.closest('form').submit();
	});	

})
function frmWPMLComplete(){
	if ( jQuery(this).val() == '' ) {
		jQuery(this).next('input[type=checkbox]').prop( 'checked', false );
	} else {
		jQuery(this).next('input[type=checkbox]').prop( 'checked', true );
	}
}
</script>
