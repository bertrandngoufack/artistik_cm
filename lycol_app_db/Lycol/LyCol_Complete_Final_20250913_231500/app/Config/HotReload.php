<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Hot Reload Configuration
 */
class HotReload extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Hot Reload Enabled
     * --------------------------------------------------------------------------
     *
     * When true, this will enable hot reload for development.
     */
    public bool $enabled = true;

    /**
     * --------------------------------------------------------------------------
     * Watch Directories
     * --------------------------------------------------------------------------
     *
     * Directories to watch for changes during development.
     */
    public array $watchDirectories = [
        APPPATH,
        ROOTPATH . 'public',
        ROOTPATH . 'writable',
    ];

    /**
     * --------------------------------------------------------------------------
     * Watch File Extensions
     * --------------------------------------------------------------------------
     *
     * File extensions to watch for changes.
     */
    public array $watchExtensions = [
        'php', 'css', 'js', 'html', 'svg', 'json', 'env', 'log',
    ];

    /**
     * --------------------------------------------------------------------------
     * Ignore Directories
     * --------------------------------------------------------------------------
     *
     * Directories to ignore when watching for changes.
     */
    public array $ignoreDirectories = [
        APPPATH . 'Cache',
        APPPATH . 'Logs',
        WRITEPATH . 'cache',
        WRITEPATH . 'logs',
        WRITEPATH . 'uploads',
    ];

    /**
     * --------------------------------------------------------------------------
     * Ignore Files
     * --------------------------------------------------------------------------
     *
     * Files to ignore when watching for changes.
     */
    public array $ignoreFiles = [
        '.git',
        '.svn',
        '.DS_Store',
        'Thumbs.db',
        '*.tmp',
        '*.temp',
        '*.log',
    ];

    /**
     * --------------------------------------------------------------------------
     * Polling Interval
     * --------------------------------------------------------------------------
     *
     * How often to check for changes (in milliseconds).
     */
    public int $pollingInterval = 1000;

    /**
     * --------------------------------------------------------------------------
     * Auto Reload
     * --------------------------------------------------------------------------
     *
     * When true, the page will automatically reload when changes are detected.
     */
    public bool $autoReload = true;

    /**
     * --------------------------------------------------------------------------
     * Reload Delay
     * --------------------------------------------------------------------------
     *
     * Delay before reloading the page (in milliseconds).
     */
    public int $reloadDelay = 500;

    /**
     * --------------------------------------------------------------------------
     * Show Notifications
     * --------------------------------------------------------------------------
     *
     * When true, show notifications when files change.
     */
    public bool $showNotifications = true;

    /**
     * --------------------------------------------------------------------------
     * Debug Mode
     * --------------------------------------------------------------------------
     *
     * When true, show debug information for hot reload.
     */
    public bool $debug = true;
}

