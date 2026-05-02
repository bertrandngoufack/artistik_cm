<?php
/**
 * Show in the global settings.
 *
 * @package formidable-getresponse
 */

?>
<p class="howto">
	<?php
	printf(
		/* translators: %1$s: Start link HTML %2$s: end link HTML */
		esc_html__( 'API keys can be found at %1$sGetResponse Integrations and API%2$s', 'formidable-getresponse' ),
		'<a href="https://app.getresponse.com/api" target="_blank" rel="noopener">',
		'</a>'
	);
	?>
</p>
<p>
	<label class="frm_left_label">
		<?php esc_html_e( 'GetResponse API Key', 'formidable-getresponse' ); ?>
	</label>
	<input type="text" name="frm_getresponse_api_key" id="frm_getresponse_api_key" value="<?php echo esc_attr( $frm_getresponse_settings->settings->api_key ); ?>" class="frm_with_left_label" />
</p>
