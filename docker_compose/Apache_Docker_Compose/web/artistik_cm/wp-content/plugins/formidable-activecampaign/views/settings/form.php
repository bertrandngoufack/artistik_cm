	<table class="form-table">
		 <tr class="form-field" valign="top">
			<td width="200px">
				<label for="frm_activecampaign_api_url">
					<?php esc_html_e( 'ActiveCampaign API URL', 'frmactivecampaign' ); ?>
				</label>
			</td>
			<td>
				<input type="text" name="frm_activecampaign_api_url" id="frm_activecampaign_api_url" value="<?php echo esc_attr( $settings->settings->api_url ); ?>" class="frm_long_input" />
				<p class="howto">
					<?php esc_html_e( 'Add full URL e.g https://abctest748.api-us1.com', 'frmactivecampaign' ); ?>
				</p>
			</td>
		</tr>
		<tr class="form-field" valign="top">
			<td>
				<label for="frm_activecampaign_api_key">
					<?php esc_html_e( 'ActiveCampaign API Key', 'frmactivecampaign' ); ?>
				</label>
			</td>
			<td>
				<input type="text" name="frm_activecampaign_api_key" id="frm_activecampaign_api_key" value="<?php echo esc_attr( $settings->settings->api_key ); ?>" class="frm_long_input" />
				<p class="howto">
					<?php esc_html_e( 'The API key is visible on the ActiveCampaign dashboard', 'frmactivecampaign' ); ?>
				</p>
			</td>
		</tr>
	</table>

<h3><?php esc_html_e( 'How to setup ActiveCampaign', 'frmactivecampaign' ); ?></h3>
<ol>
	<li>
		<?php
		echo sprintf(
			// translators: %1$s - ActiveCampaign settings path, %2$s - Link to ActiveCampaign dashboard.
			__( 'Go to the <strong>%1$s</strong> page on the %2$s.', 'frmactivecampaign' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_html__( 'Settings → Developer', 'frmactivecampaign' ),
			'<a href="https://www.activecampaign.com/login/" target="_blank" rel="noopener">' . esc_html__( 'ActiveCampaign dashboard', 'frmactivecampaign' ) . '</a>'
		);
		?>
	</li>
	<li><?php esc_html_e( 'Copy and paste your ActiveCampaign API URL and Key and paste them in the settings above.', 'frmactivecampaign' ); ?></li>
</ol>

<p>
	<img src="https://s3.amazonaws.com/fp.strategy11.com/images/add-ons/ac/activecampaign-api-key.png" alt="<?php esc_attr_e( 'Get ActiveCampaign API key', 'frmactivecampaign' ); ?>" style="max-width:650px" />
</p>

<p><a href="https://formidableforms.com/knowledgebase/activecampaign-forms/#kb-setup-activecampaign-integration" target="_blank" rel="noopener" class="button-secondary frm-button-secondary"><?php esc_html_e( 'Learn more', 'frmactivecampaign' ); ?></a></p>
