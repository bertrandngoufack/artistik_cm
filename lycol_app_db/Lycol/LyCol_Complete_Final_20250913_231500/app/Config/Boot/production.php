<?php

/*
 |--------------------------------------------------------------------------
 | ERROR DISPLAY
 |--------------------------------------------------------------------------
 | Don't show ANY in production environments. Instead, let the system catch
 | it and display a generic error message.
 |
 | If you set 'display_errors' to '1', CI4's detailed error report will show.
 */
error_reporting(E_ALL & ~E_DEPRECATED);
// If you want to suppress more types of errors.
// error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);

// Permettre le debug si l'environnement le demande
if (getenv('CI_ENVIRONMENT') === 'development' || getenv('CI_DEBUG') === 'true') {
    ini_set('display_errors', '1');
    defined('CI_DEBUG') || define('CI_DEBUG', true);
    defined('SHOW_DEBUG_BACKTRACE') || define('SHOW_DEBUG_BACKTRACE', true);
} else {
    ini_set('display_errors', '0');
    defined('CI_DEBUG') || define('CI_DEBUG', false);
}

/*
 |--------------------------------------------------------------------------
 | DEBUG MODE
 |--------------------------------------------------------------------------
 | Debug mode is an experimental flag that can allow changes throughout
 | the system. It's not widely used currently, and may not survive
 | release of the framework.
 */
// Le debug est maintenant géré conditionnellement ci-dessus
