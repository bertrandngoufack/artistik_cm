<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Debug Configuration
 */
class Debug extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Debug Mode
     * --------------------------------------------------------------------------
     *
     * When true, this will enable debug mode and show detailed error messages.
     */
    public bool $debug = true;

    /**
     * --------------------------------------------------------------------------
     * Error Display
     * --------------------------------------------------------------------------
     *
     * When true, this will display detailed error messages.
     */
    public bool $displayErrors = true;

    /**
     * --------------------------------------------------------------------------
     * Error Reporting
     * --------------------------------------------------------------------------
     *
     * Set the error reporting level.
     */
    public int $errorReporting = E_ALL;

    /**
     * --------------------------------------------------------------------------
     * Debug Backtrace
     * --------------------------------------------------------------------------
     *
     * When true, this will show debug backtraces along with error information.
     */
    public bool $showDebugBacktrace = true;

    /**
     * --------------------------------------------------------------------------
     * Log Level
     * --------------------------------------------------------------------------
     *
     * Set the log level for debugging.
     */
    public string $logLevel = 'debug';

    /**
     * --------------------------------------------------------------------------
     * Database Debug
     * --------------------------------------------------------------------------
     *
     * When true, this will show database queries and errors.
     */
    public bool $databaseDebug = true;

    /**
     * --------------------------------------------------------------------------
     * Performance Profiling
     * --------------------------------------------------------------------------
     *
     * When true, this will enable performance profiling.
     */
    public bool $performanceProfiling = true;

    /**
     * --------------------------------------------------------------------------
     * Memory Usage Display
     * --------------------------------------------------------------------------
     *
     * When true, this will display memory usage information.
     */
    public bool $showMemoryUsage = true;

    /**
     * --------------------------------------------------------------------------
     * Execution Time Display
     * --------------------------------------------------------------------------
     *
     * When true, this will display execution time information.
     */
    public bool $showExecutionTime = true;
}

