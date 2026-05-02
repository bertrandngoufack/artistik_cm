<?php

/**
 * Used in only multisite and PRO version
 * @see \WPStaging\Pro\Staging\Service\StagingSetup::renderNetworkCloneSettings
 */

?>

<?php esc_html_e('Clone the entire multisite network as a staging multisite.', 'wp-staging'); ?>
<br/> <br/>
<b><?php esc_html_e('Note', 'wp-staging') ?>: </b> <?php esc_html_e('Changing this option resets all selected database tables. Use the menu link "Database Tables" below to select all desired tables.', 'wp-staging'); ?>
<br/>
<br/>
<span class="wpstg--red"> <?php esc_html_e('Though cloning of the entire multisite network works with the same database, it is recommended to use another database to keep the multisite network completely separated from the production network.', 'wp-staging'); ?></span>
