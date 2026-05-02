<?php
/**
 * Global settings form
 *
 * @package FrmConvertKit
 *
 * @var FrmConvertKitSettings $settings Settings object.
 */

$api_secret = isset( $settings->settings->api_secret ) ? $settings->settings->api_secret : '';
?>
<table class="form-table">
	<tr class="form-field" valign="top">
		<td width="200px">
			<label for="frm-cvk-api-secret">
				<?php esc_html_e( 'API Secret', 'frm-convertkit' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="frm_convertkit_api_secret" id="frm-cvk-api-secret" value="<?php echo esc_attr( $api_secret ); ?>" class="frm_long_input" />
		</td>
	</tr>

	<tr>
		<td colspan="2">
			<p>
				<button type="button" class="button frm_button" id="frm-cvk-test-api"><?php esc_html_e( 'Test connection', 'frm-convertkit' ); ?></button>
				<span class="spinner" style="float: none;"></span>
			</p>
			<p class="frm_updated_message frm-cvk-success-msg"><?php esc_html_e( 'API connect successfully', 'frm-convertkit' ); ?></p>
			<p class="frm_error_style frm-cvk-failed-msg"><?php esc_html_e( 'API connect failed', 'frm-convertkit' ); ?></p>
			<p class="frm_error_style frm-cvk-empty-msg"><?php esc_html_e( 'API secret is empty', 'frm-convertkit' ); ?></p>
		</td>
	</tr>
</table>

<p>
	<?php
	printf(
		// Translators: %1$s: Link open tag, %2$s: Link close tag.
		esc_html__( 'Please go to the %1$sAdvanced settings%2$s in your ConvertKit account to get the API secret.', 'frm-convertkit' ),
		'<a href="https://app.convertkit.com/account_settings/advanced_settings" target="_blank">',
		'</a>'
	)
	?>
</p>

<style>
	#convertkit_settings .frm-cvk-is-loading .spinner {
		visibility: visible;
	}

	#convertkit_settings td:not(.frm-cvk-is-empty) .frm-cvk-empty-msg,
	#convertkit_settings td:not(.frm-cvk-is-failed) .frm-cvk-failed-msg,
	#convertkit_settings td:not(.frm-cvk-is-success) .frm-cvk-success-msg,
	#convertkit_settings td:not(.frm-cvk-is-empty) .frm-cvk-empty-msg {
		display: none;
	}

	#convertkit_settings td.frm-cvk-is-success .frm-cvk-success-msg {
		display: block !important;
	}
</style>
