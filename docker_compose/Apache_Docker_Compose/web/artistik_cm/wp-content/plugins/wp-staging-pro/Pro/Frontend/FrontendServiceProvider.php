<?php

namespace WPStaging\Pro\Frontend;

use WPStaging\Frontend\FrontendServiceProvider as CoreFrontendServiceProvider;
use WPStaging\Pro\Staging\AutoLogin\LoginAccessRevoker;
use WPStaging\Pro\Staging\AutoLogin\LoginAuthenticator;

class FrontendServiceProvider extends CoreFrontendServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        parent::register();
        $this->registerLoginAfterPull();
        $this->defineHooks();
    }

    /**
     * @return void
     * Define Hooks
     */
    private function registerLoginAfterPull()
    {
        add_action($this->getMessageAction(), [$this->container->make(LoginAfterPull::class), 'showMessage'], 10, 0); // phpcs:ignore WPStaging.Security.FirstArgNotAString, WPStaging.Security.AuthorizationChecked
    }

    /**
     * @return void
     */
    private function defineHooks()
    {
        static $isRegistered = false;
        if ($isRegistered) {
            return;
        }

        add_action('init', $this->container->callback(LoginAuthenticator::class, 'processAuthentication'), 1);
        add_action('login_message', $this->container->callback(LoginAccessRevoker::class, 'mayShowFailedLoginError'), 1);
        add_action('init', $this->container->callback(LoginAccessRevoker::class, 'maybeRevokeLoginAccess'));
        add_action('rest_api_init', $this->container->callback(LoginAuthenticator::class, 'registerRestRoutes'), 1);
        $isRegistered = true;
    }
}
