<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Profiler Configuration
 */
class Profiler extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Profiler Display
     * --------------------------------------------------------------------------
     *
     * When true, this will display the profiler at the bottom of the page.
     */
    public bool $enabled = true;

    /**
     * --------------------------------------------------------------------------
     * Profiler Position
     * --------------------------------------------------------------------------
     *
     * The position of the profiler. Can be 'top' or 'bottom'.
     */
    public string $position = 'bottom';

    /**
     * --------------------------------------------------------------------------
     * Profiler Panels
     * --------------------------------------------------------------------------
     *
     * The panels to display in the profiler.
     */
    public array $panels = [
        'vars'      => \CodeIgniter\Debug\Toolbar\Collectors\Vars::class,
        'database'  => \CodeIgniter\Debug\Toolbar\Collectors\Database::class,
        'events'    => \CodeIgniter\Debug\Toolbar\Collectors\Events::class,
        'files'     => \CodeIgniter\Debug\Toolbar\Collectors\Files::class,
        'logs'      => \CodeIgniter\Debug\Toolbar\Collectors\Logs::class,
        'routes'    => \CodeIgniter\Debug\Toolbar\Collectors\Routes::class,
        'session'   => \CodeIgniter\Debug\Toolbar\Collectors\Session::class,
        'timers'    => \CodeIgniter\Debug\Toolbar\Collectors\Timers::class,
        'views'     => \CodeIgniter\Debug\Toolbar\Collectors\Views::class,
        'cache'     => \CodeIgniter\Debug\Toolbar\Collectors\Cache::class,
        'hooks'     => \CodeIgniter\Debug\Toolbar\Collectors\Hooks::class,
    ];

    /**
     * --------------------------------------------------------------------------
     * Profiler Max History
     * --------------------------------------------------------------------------
     *
     * The maximum number of history entries to keep.
     */
    public int $maxHistory = 20;

    /**
     * --------------------------------------------------------------------------
     * Profiler Theme
     * --------------------------------------------------------------------------
     *
     * The theme to use for the profiler.
     */
    public string $theme = 'default';

    /**
     * --------------------------------------------------------------------------
     * Profiler Debug Mode
     * --------------------------------------------------------------------------
     *
     * When true, this will show additional debug information.
     */
    public bool $debug = true;

    /**
     * --------------------------------------------------------------------------
     * Profiler Performance Monitoring
     * --------------------------------------------------------------------------
     *
     * When true, this will show performance information.
     */
    public bool $performanceMonitoring = true;

    /**
     * --------------------------------------------------------------------------
     * Profiler Memory Usage
     * --------------------------------------------------------------------------
     *
     * When true, this will show memory usage information.
     */
    public bool $showMemoryUsage = true;

    /**
     * --------------------------------------------------------------------------
     * Profiler Execution Time
     * --------------------------------------------------------------------------
     *
     * When true, this will show execution time information.
     */
    public bool $showExecutionTime = true;

    /**
     * --------------------------------------------------------------------------
     * Profiler Database Queries
     * --------------------------------------------------------------------------
     *
     * When true, this will show database queries.
     */
    public bool $showDatabaseQueries = true;

    /**
     * --------------------------------------------------------------------------
     * Profiler File Operations
     * --------------------------------------------------------------------------
     *
     * When true, this will show file operations.
     */
    public bool $showFileOperations = true;

    /**
     * --------------------------------------------------------------------------
     * Profiler Session Data
     * --------------------------------------------------------------------------
     *
     * When true, this will show session data.
     */
    public bool $showSessionData = true;
}

