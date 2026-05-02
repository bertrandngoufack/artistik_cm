<div class="wpstg-callout wpstg-callout-warning wpstg-mt-4">
    <p class="wpstg-m-0">
    <?php
        echo esc_html__('When creating backups with WP Staging for different websites or clients, avoid using the same storage location for all backups!', 'wp-staging');
    ?>
        <br>
    <?php
        echo esc_html__('This can lead to backup files from one client being accessible to others, resulting in unauthorized access.', 'wp-staging');
    ?>
        <br>
    <?php
        echo esc_html__('To prevent this, create a dedicated storage location and a separate subdirectory for each website or client\'s backup files.', 'wp-staging');
    ?>
    </p>
</div>
