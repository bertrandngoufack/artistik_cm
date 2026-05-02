<div
    id="wpstg--modal--edit--staging-site"
    data-cancelButtonText="<?php esc_attr_e('CANCEL', 'wp-staging'); ?>"
    data-saveButtonText="<?php esc_attr_e('SAVE', 'wp-staging'); ?>"
    style="display: none">
    <h2 class="wpstg--modal--edit--staging-site--title wpstg--grey">
        <?php esc_html_e('Edit Staging Site', 'wp-staging') ?>
    </h2>
    <p>
        <?php esc_html_e('Update the values below only if you moved your staging site to another server and WP STAGING lost connection to the clone site. Don\'t update these values if you are unsure. This can break the pushing capability.', 'wp-staging') ?>
        <a href='https://wp-staging.com/docs/reconnect-staging-site-to-production-website/' target="_blank"><?php esc_html_e('Read More', 'wp-staging') ?></a>
    </p>
    <div id="wpstg--modal--edit--staging-site--content" class=""></div>
</div>
