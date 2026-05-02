<?php
/**
 * @var bool $isPullFromWpCom // true if the data was Pulled from wordpress.com but current site is not a wordpress.com site
 * @var string $resetPasswordArticleLink // link to article on how to reset password in different ways
 */
?>
<style>
    .unselectable {
        -moz-user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
    #login-after-pull {
        width: 320px;
        padding-top: 20px;
        margin: auto;
    }
    #login-after-pull h1 {
        margin-bottom: 20px;
    }
    #login-after-pull p {
        margin-bottom: 10px;
    }
    #login {
        padding-top:40px;
    }
</style>
<div id="login-after-pull">
    <h1 class="unselectable"><?php esc_html_e('Congratulations!', 'wp-staging'); ?></h1>
    <p class="unselectable"><?php esc_html_e('You have just pulled data from another site.', 'wp-staging'); ?></p>
    <p class="unselectable"><?php esc_html_e('Now you need to log-in using email and password of the site from which the data was just Pulled.', 'wp-staging'); ?></p>
    <?php if ($isPullFromWpCom) : ?>
    <p>
        <?php esc_html_e('This data was Pulled from a WordPress.com hosted site. Your WordPress.com password may not work and you will need to reset your password to get a new one!', 'wp-staging'); ?>
        <?php echo sprintf(esc_html__('Read this %s to find out how to reset the password.', 'wp-staging'), '<a href="' . esc_url($resetPasswordArticleLink) . '" target="_blank">' . esc_html__('article', 'wp-staging') . '</a>'); ?>
    </p>
    <?php endif; ?>
</div>
