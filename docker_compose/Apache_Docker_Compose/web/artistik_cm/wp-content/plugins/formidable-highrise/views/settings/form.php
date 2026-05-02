    <table class="form-table">
        <tr class="form-field" valign="top">
            <td width="200px"><label><?php _e('Highrise Subdomain', 'formidable') ?></label></td>
        	<td>
                https://<input type="text" name="frm_hrs_account" id="frm_hrs_account" value="<?php echo esc_attr($frm_hrs_settings->settings->account) ?>" class="regular_text" />.highrisehq.com
        	</td>
        </tr>
        <tr class="form-field" valign="top">
            <td><label><?php _e('Highrise API Token', 'formidable') ?></label></td>
        	<td>
                <input type="text" name="frm_hrs_token" id="frm_hrs_token" value="<?php echo $frm_hrs_settings->settings->token ?>" class="frm_long_input" />
        	</td>
        </tr>
    </table>
    
    <p><?php _e('Input the credentials of your Highrise account. Please note that your <b>Highrise Subdomain</b> is the first part of your Highrise URL. For example, if your URL is https://<b>mycompany</b>.highrisehq.com, your Highrise Account Name is <em>mycompany</em>.', 'formidable') ?></p>

