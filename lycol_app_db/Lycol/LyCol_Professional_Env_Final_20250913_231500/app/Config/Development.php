<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Development Configuration
 */
class Development extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Hot Reload
     * --------------------------------------------------------------------------
     *
     * When true, this will enable hot reload for development.
     */
    public bool $hotReload = true;

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
    ];

    /**
     * --------------------------------------------------------------------------
     * Watch File Extensions
     * --------------------------------------------------------------------------
     *
     * File extensions to watch for changes.
     */
    public array $watchExtensions = [
        'php', 'css', 'js', 'html', 'svg', 'json', 'env',
    ];

    /**
     * --------------------------------------------------------------------------
     * Development Server
     * --------------------------------------------------------------------------
     *
     * Configuration for the development server.
     */
    public array $developmentServer = [
        'host' => '0.0.0.0',
        'port' => 8080,
        'documentRoot' => ROOTPATH . 'public',
        'router' => ROOTPATH . 'public/index.php',
        'watch' => true,
        'reload' => true,
    ];

    /**
     * --------------------------------------------------------------------------
     * Debug Information
     * --------------------------------------------------------------------------
     *
     * Show debug information in development.
     */
    public bool $showDebugInfo = true;

    /**
     * --------------------------------------------------------------------------
     * Error Display
     * --------------------------------------------------------------------------
     *
     * Show detailed error messages in development.
     */
    public bool $showErrors = true;

    /**
     * --------------------------------------------------------------------------
     * Performance Monitoring
     * --------------------------------------------------------------------------
     *
     * Enable performance monitoring in development.
     */
    public bool $performanceMonitoring = true;

    /**
     * --------------------------------------------------------------------------
     * Memory Usage Display
     * --------------------------------------------------------------------------
     *
     * Show memory usage information.
     */
    public bool $showMemoryUsage = true;

    /**
     * --------------------------------------------------------------------------
     * Execution Time Display
     * --------------------------------------------------------------------------
     *
     * Show execution time information.
     */
    public bool $showExecutionTime = true;
}

