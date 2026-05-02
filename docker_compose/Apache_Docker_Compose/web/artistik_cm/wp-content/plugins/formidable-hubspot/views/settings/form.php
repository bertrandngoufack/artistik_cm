<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
$unique      = uniqid();
$show_toggle = is_callable( 'FrmHtmlHelper::toggle' );
$has_oauth   = empty( $frm_hubspot_settings->formidable_hubspot_oauth );
// If PAT is exist but OAuth is not connected we are turning toggle off otherwise toggle value gonna used.
$toggle_value     = ( ! $has_oauth && is_wp_error( $is_valid_pat_exists ) ) ? true : $frm_hubspot_settings->auth_toggle;
?>

<h3 class="frm-no-border frm-mt-0">
	<?php esc_html_e( 'HubSpot Authentication', 'formidable-hubspot' ); ?>
</h3>

<?php if ( ! $is_valid_connection_exists ) : ?>
	<ol class="howto">
		<li>
			<?php
			printf(
				/* translators: %1$s: Start link HTML %2$s: end link HTML */
				esc_html__( 'Use the toggle to choose between Oauth and the Private App Access Token. %1$sLearn more%2$s.', 'formidable-hubspot' ),
				'<a href="https://developers.hubspot.com/blog/hubspot-integration-choosing-private-public-hubspot-apps" target="_blank" rel="noopener">',
				'</a>'
			);
			?>
		</li>
		<li><?php esc_html_e( 'If you selected OAuth, click on the Connect button.', 'formidable-hubspot' ); ?></li>
		<li>
			<?php
			printf(
				/* translators: %1$s: Start link HTML %2$s: end link HTML */
				esc_html__( 'If not using OAuth, %1$screate a Private App Access Token%2$s from your HubSpot dashboard and add it below.', 'formidable-hubspot' ),
				'<a href="' . esc_url( 'https://formidableforms.com/knowledgebase/hubspot-forms/#kb-use-private-app-access-token' ) . '" target="_blank" rel="noopener">',
				'</a>'
			);
			?>
		</li>
	</ol>
	<br/>
<?php endif; ?>

<div class="frm_wrap">
	<p>
		<?php
		if ( $show_toggle ) :
				$toggle_id = 'frm_hubspot_private_app_toggle' . $unique;
				FrmHtmlHelper::toggle(
					$toggle_id,
					'frm_hubspot_setting_toggle',
					array(
						'checked'       => $toggle_value,
						'on_label'      => __( 'Connect with OAuth', 'formidable-hubspot' ),
						'show_labels'   => true,
						'echo'          => true,
					)
				);
			?>
		<?php endif; ?>
	</p>
	<div class="frm_grid_container frm_hubspot_oauth_app_settings <?php echo ! $toggle_value ? 'frm_hidden' : ''; ?>">
		<p class="frm12">
			<a href="#" id="frm-hubspot-connect-with-app<?php echo esc_attr( $unique ); ?>" name="frm-hubspot-connect-with-app" class="button-primary frm-button-primary frm-hubspot-connect-with-app" data-action="<?php echo esc_attr( $has_oauth ? 'authorize' : 'revoke' ); ?>" >
				<?php echo $has_oauth ? esc_html__( 'Connect to HubSpot', 'formidable-hubspot' ) : esc_html__( 'Disconnect HubSpot', 'formidable-hubspot' ); ?>
			</a>
		</p>
	</div>

	<div class="frm_grid_container frm_hubspot_private_app_settings <?php echo $toggle_value ? 'frm_hidden' : ''; ?>">
		<p class="frm4">
			<label class="frm4" for="frm_hubspot_private_app_access_token<?php echo esc_attr( $unique ); ?>" >
				<?php esc_html_e( 'HubSpot Private App Access Token', 'formidable-hubspot' ); ?>
			</label>
		</p>
		<p class="frm8">
			<input class="frm8" id="frm_hubspot_private_app_access_token<?php echo esc_attr( $unique ); ?>" type="text" name="frm_hubspot_private_app_access_token" value="<?php echo esc_attr( $frm_hubspot_settings->private_app_access_token ); ?>" />
		</p>
		<!-- Include PAT save button on action page only -->
		<?php if ( isset( $action_control ) && $action_control instanceof FrmHubSpotAction ) : ?>
			<p class="frm12">
				<a href="#" id="frm-hubspot-connect-with-pat<?php echo esc_attr( $unique ); ?>" name="frm-hubspot-connect-with-pat" class="button-primary frm-button-primary frm-hubspot-connect-with-pat" >
					<?php esc_html_e( 'Save Private Access Token', 'formidable-hubspot' ); ?>
				</a>
			</p>
		<?php endif; ?>
	</div>

	<?php if ( $frm_hubspot_settings->api_key ) : ?>
		<div class="frm_hubspot_public_app_settings">
			<p>
				<label for="frm_hubspot_api_key<?php echo esc_attr( $unique ); ?>" >
					<?php esc_html_e( 'HubSpot API Key', 'formidable-hubspot' ); ?>
				</label>
				<input id="frm_hubspot_api_key<?php echo esc_attr( $unique ); ?>" type="text" name="frm_hubspot_api_key" value="<?php echo esc_attr( $frm_hubspot_settings->api_key ); ?>" disabled />
			</p>
			<p class="howto">
				<?php
				printf(
					/* translators: %1$s: Start link HTML HubSpot API Key %2$s: end link HTML HubSpot API Key */
					esc_html__( 'Starting November 30, 2022, HubSpot API keys will no longer be able to be used as an authentication method to access HubSpot APIs. In addition, starting July 15, 2022, accounts without a HubSpot API key already generated will no longer be able to create one %1$s more info %2$s.', 'formidable-hubspot' ),
					'<a href="' . esc_url( 'https://knowledge.hubspot.com/integrations/how-do-i-get-my-hubspot-api-key' ) . '" target="_blank" rel="noopener">',
					'</a>'
				);
				?>
			</p>
		</div>
	<?php endif; ?>
</div>
