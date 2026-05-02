<?php
if ( ! empty( $message ) ) {
	?>
	<div id="message" class="updated fade" style="padding:5px;"><?php echo esc_html( $message ); ?></div>    
	<?php
}

if ( ! empty( $error ) ) {
	?>
	<div class="frm_error_style"><?php echo esc_html( $error ); ?></div> 
	<?php
}
?>

<table class="form-table">
	<tr class="form-field">
		<td width="170px">
			<label><?php esc_html_e( 'Authorization ID', 'formidable-aweber' ); ?></label>
		</td>
		<td><input type="text" name="frm_awbr_oauth_id" id="frm_awbr_oauth_id" value="<?php echo esc_attr( $frm_awbr_settings->settings->oauth_id ); ?>" class="frm_long_input" /><br/>
		</td>
	</tr>
</table>

<br/>
<h4><?php esc_html_e( 'To setup AWeber:', 'formidable-aweber' ); ?></h4>
<ol>
	<li><?php esc_html_e( 'Before you can use AWeber with your forms, you first need to authorize it to access your AWeber account.', 'formidable-aweber' ); ?> <a href="https://auth.aweber.com/1.0/oauth/authorize_app/17608414" target="_blank"><?php esc_html_e( 'Click here to get your AWeber authorization ID.', 'formidable-aweber' ); ?></a></li>
	<li><?php esc_html_e( 'After you login to AWeber, you will be given an authorization ID. Copy it and paste it below.', 'formidable-aweber' ); ?></li>
	<li><?php esc_html_e( 'Click Update. If you see a success message you are ready to setup your forms to create AWeber contacts.', 'formidable-aweber' ); ?></li>
</ol>
