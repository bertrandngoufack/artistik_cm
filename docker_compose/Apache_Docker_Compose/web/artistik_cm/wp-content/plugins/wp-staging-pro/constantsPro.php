<?php

// WP STAGING version number
if (!defined('WPSTGPRO_VERSION')) {
    define('WPSTGPRO_VERSION', '6.7.3');
}

// Compatible up to WordPress Version
if (!defined('WPSTG_COMPATIBLE')) {
    define('WPSTG_COMPATIBLE', '7.0.0');
}

if (!defined('WPSTG_REQUIRE_FREE')) {
    /**
     * Whether or not the free version is required. Note: This should be activated only for dev purposes, exceptionally and can have unwanted side effects.
     *
     * @var bool
     */
    define('WPSTG_REQUIRE_FREE', apply_filters('wpstg.require_free', true));
}
