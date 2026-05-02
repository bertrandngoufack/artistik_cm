<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! empty( $code_in_url ) ) {
	?>
	<p class="frm_warning_style">
		<?php esc_html_e( 'Click "Update" in the top right to confirm your new Constant Contact Authorization Code.', 'formidable-ctct' ); ?>
	</p>
	<?php /* Add JavaScript to warn the user that there are unsaved changes. */ ?>
	<script>
		( function() {
			const wrapper = document.getElementById( 'frm-publishing' );
			if ( ! wrapper ) {
				return;
			}

			let clickedUpdate = false;
			wrapper.querySelector( '.frm-button-primary' ).addEventListener(
				'click',
				() => clickedUpdate = true
			);

			window.addEventListener(
				'beforeunload',
				function( event ) {
					if ( clickedUpdate ) {
						return;
					}

					event.preventDefault();
					event.returnValue = '';
				}
			);
		}() );
	</script>
	<?php
	$settings->auth_code = $code_in_url;
}
?>

<p class="howto">
	<?php
	/* translators: %1$s: Start link HTML, %2$s: end link HTML */
	printf( esc_html__( 'Learn more about the Constant Contact %1$sAuthorization code%2$s.', 'formidable-ctct' ), '<a href="https://formidableforms.com/knowledgebase/constant-contact-forms/" target="_blank">', '</a>' );
	?>
</p>

<?php FrmCtctSettingsController::maybe_show_deprecated_api_warning(); ?>

<p>
	<a id="frmcc-auth-btn" href="<?php echo esc_url( $ctct_api->auth_url() ); ?>" class="button-secondary frm-button-secondary">
		<?php esc_html_e( 'Get Authorization Code', 'formidable-ctct' ); ?> &rarr;
	</a>
</p>

<p>
	<label for="frm_ctct_auth_code" class="frm_left_label">
		<?php esc_html_e( 'Authorization Code', 'formidable-ctct' ); ?>
	</label>
	<input type="text" name="frm_ctct_auth_code" id="frm_ctct_auth_code" value="<?php echo esc_attr( $settings->auth_code ); ?>" class="frm_with_left_label" />
</p>
