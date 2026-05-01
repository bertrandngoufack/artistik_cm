<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;

/**
 * Filter Configuration
 *
 * You can enable or disable filters for a given class or method.
 * Filters are run through the list before the route/method is called.
 * You can also add your own filters to run through the list.
 *
 * Example:
 *  public $filters = [
 *      'auth' => ['before' => ['admin/*', 'admin']],
 *      'csrf' => ['before' => ['admin/*']],
 *  ];
 */
class Filters extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Filter Debug
     * --------------------------------------------------------------------------
     *
     * When true, this will enable filter debugging.
     */
    public bool $debug = true;

    /**
     * --------------------------------------------------------------------------
     * Filter Logging
     * --------------------------------------------------------------------------
     *
     * When true, this will log filter information.
     */
    public bool $logging = true;

    /**
     * Configures aliases for Filter classes to
     * make reading things nicer and simpler.
     */
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'auth'          => \App\Filters\AuthFilter::class,
        'parent'        => \App\Filters\ParentFilter::class,
        'mobile'        => \App\Filters\MobileFilter::class,
    ];

    /**
     * List of filter aliases that are always
     * applied before and after every request.
     */
    public array $globals = [
        'before' => [
            // 'honeypot',
            // 'csrf',
            // 'invalidchars',
        ],
        'after' => [
            'toolbar',
            // 'honeypot',
            // 'secureheaders',
        ],
    ];

    /**
     * List of filter aliases that works on a
     * particular HTTP method (GET, POST, etc.).
     *
     * Example:
     * 'post' => ['foo', 'bar']
     *
     * If you use this, you should disable auto-routing because auto-routing
     * permits any HTTP method to access a controller. Accessing the controller
     * with a method you don't expect could bypass the filter.
     */
    public array $methods = [];

    /**
     * List of filter aliases that should run on any
     * before or after URI patterns.
     *
     * Example:
     * 'isLoggedIn' => ['before' => ['account/*', 'profiles/*']]
     */
    public array $filters = [
        'auth' => ['before' => ['admin/*']],
        'parent' => ['before' => ['parents/*']],
        'mobile' => ['before' => ['mobile/*']],
    ];
}
