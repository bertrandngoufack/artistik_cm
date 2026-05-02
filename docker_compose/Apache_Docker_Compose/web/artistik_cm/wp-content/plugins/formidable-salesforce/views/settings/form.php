<table class="form-table">
	<tr class="form-field" valign="top">
		<td width="200px">
			<label for="frm_salesforce_environment"><?php esc_html_e( 'Environment', 'formidable-salesforce' ); ?></label>
		</td>
		<td>

			<select name="frm_salesforce_environment" id="frm_salesforce_environment">
				<option <?php selected( $frm_salesforce_settings->settings->environment, 'live' ); ?> value='live'>
					<?php esc_html_e( 'Live', 'formidable-salesforce' ); ?>
				</option>
				<option <?php selected( $frm_salesforce_settings->settings->environment, 'sandbox' ); ?> value='sandbox'>
					<?php esc_html_e( 'Sandbox', 'formidable-salesforce' ); ?>
				</option>
			</select>

		</td>
	</tr>

	<tr class="form-field" valign="top">
		<td>
			<label for="frm_salesforce_client_id"><?php esc_html_e( 'Consumer Key', 'formidable-salesforce' ); ?></label>
		</td>
		<td>
			<input type="text" name="frm_salesforce_client_id" id="frm_salesforce_client_id" value="<?php echo esc_attr( $frm_salesforce_settings->settings->client_id ); ?>" class="frm_long_input" />
			<br/>
			<span class="howto">
				<?php
				/* translators: %1$s: Start link HTML, %2$s: end link HTML */
				printf( esc_html__( 'Create a new app in salesforce. Learn how to %1$sget Consumer key and Consumer Secret key%2$s', 'formidable-salesforce' ), '<a href="https://formidableforms.com/knowledgebase/salesforce-forms/" target="_blank">', '</a>' );
				?>
			</span>
		</td>
	</tr>

	<tr class="form-field" valign="top">
		<td>
			<label for="frm_salesforce_client_secret"><?php esc_html_e( 'Consumer Secret Key', 'fformidable-salesforce' ); ?></label>
		</td>
		<td>
			<input type="text" name="frm_salesforce_client_secret" id="frm_salesforce_client_secret" value="<?php echo esc_attr( $frm_salesforce_settings->settings->client_secret ); ?>" class="frm_long_input" />
		</td>
	</tr>

	<?php if ( ! empty( $frm_salesforce_settings->settings->client_id ) ) { ?>
		<tr class="form-field" valign="top">
			<td></td>
			<td>
				<?php FrmSalesforceSettingsController::include_authorize_button( $frm_salesforce_settings->settings ); ?>
			</td>
		</tr>

		<tr class="form-field" valign="top">
			<td>
				<label><?php esc_html_e( 'Auth Code', 'formidable-salesforce' ); ?></label>
			</td>
			<td>
				<input type="text" name="frm_salesforce_auth_code" id="frm_salesforce_auth_code" value="<?php echo esc_attr( urldecode( $frm_salesforce_settings->settings->auth_code ) ); ?>" class="frm_long_input" />
				<br/>
				<span class="howto">
					<?php esc_html_e( 'Paste the code copied from authorization window here and click the \'Update\' button.', 'formidable-salesforce' ); ?>
				</span>
			</td>
		</tr>
	<?php } ?>

</table>

<script type="text/javascript">
	( function ( $ ) {
	$( function () {
		// Authorization
		$( '.formidable_salesforce_authorization' ).click( function ( e ) {
			e.preventDefault();
			var authUrl = $( this ).attr( 'href' );
			if ( authUrl == '' ) {
				alert( 'Please add and save Consumer Key and Consumer Secret first.' )
				return false;
			}
			var win = window.open( $( this ).attr( 'href' ), "formidablesalesforceauthwindow", 'width=1000, height=600' );
			var pollTimer = window.setInterval( function () {
				try {

					if ( win.document.URL.indexOf( "<?php echo esc_url( home_url() ); ?>" ) != -1 ) {
						window.clearInterval( pollTimer );
						var response_url = win.document.URL;
						var auth_code = formidable_salesforce_gup( response_url, 'code' );
						win.close();
						// We don't have an access token yet, have to go to the server for it
						var data = {
							action: 'formidable_salesforce_finish_code_exchange',
							auth_code: decodeURIComponent( auth_code ),
							security: "<?php echo esc_js( wp_create_nonce( 'salesforce-auth-ajax-nonce' ) ); ?>"
						};
						$.post( "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>", data, function ( response ) {
							location.reload();

						} );
					}
				} catch ( e ) { }
			}, 500 );
		} )

		$( '.formidable_salesforce_deauthorize' ).click( function ( e ) {
			e.preventDefault();

			var data = {
				action: 'formidable_salesforce_revoke',
			};
			$.post( ajaxurl, data, function ( response ) {
				location.reload();
			} );
		} )

	} )

} )( jQuery )

// helper function to parse out the query string params
function formidable_salesforce_gup( url, name ) {
	name = name.replace( /[\[]/, "\\\[" ).replace( /[\]]/, "\\\]" );
	var regexS = "[\\?#&]" + name + "=([^&#]*)";
	var regex = new RegExp( regexS );
	var results = regex.exec( url );
	if ( results == null )
		return "";
	else
		return results[ 1 ];
}

</script>
