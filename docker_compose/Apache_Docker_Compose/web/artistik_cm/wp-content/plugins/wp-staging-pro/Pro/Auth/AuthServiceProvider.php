<?php

namespace WPStaging\Pro\Auth;

use WPStaging\Framework\DI\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected function registerClasses()
    {
        // no-op
    }

    protected function addHooks()
    {
        add_filter('wp_ajax_wpstg_render_temporary_login_interface', $this->container->callback(TemporaryLogins::class, 'ajaxLoadTemporaryLoginInterface'), 10, 1);
        add_filter('wp_ajax_wpstg_save_temporary_login_data', $this->container->callback(TemporaryLogins::class, 'ajaxSaveTemporaryLoginData'), 10, 1);
        add_filter('wp_ajax_wpstg_list_temporary_logins_data', $this->container->callback(TemporaryLogins::class, 'ajaxGetTemporaryLoginsData'), 10, 1);
        add_filter('wp_ajax_wpstg_delete_temporary_login_data', $this->container->callback(TemporaryLogins::class, 'ajaxRemoveTemporaryLoginsData'), 10, 1);
        add_filter('wp_ajax_wpstg_update_temporary_login_interface', $this->container->callback(TemporaryLogins::class, 'ajaxLoadUpdateTemporaryLoginInterface'), 10, 1);
        add_action('admin_bar_menu', $this->container->callback(TemporaryLogins::class, 'mayBeShowTemporaryLoginTab'), 100);
        add_action('admin_head', $this->container->callback(TemporaryLogins::class, 'addTemporaryLoginTabCss'), 100);
        add_action('admin_init', $this->container->callback(TemporaryLogins::class, 'mayBeLogoutTemporaryUser'));
    }
}
