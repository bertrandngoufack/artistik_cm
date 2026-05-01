<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Logger Configuration
 */
class Logger extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Logger Threshold
     * --------------------------------------------------------------------------
     *
     * You can enable error logging by setting a threshold over zero. The
     * threshold determines what gets logged. Threshold options are:
     *
     * Log Levels:
     *  0 = Disables logging, Error logging TURNED OFF
     *  1 = Emergency Messages  - System is unusable
     *  2 = Alert Messages      - Action Must Be Taken Immediately
     *  3 = Critical Messages   - Application component unavailable, unexpected exception.
     *  4 = Runtime Errors      - Don't need immediate action, but should be monitored.
     *  5 = Warnings            - Exceptional occurrences that are not errors.
     *  6 = Notices             - Normal but significant events.
     *  7 = Info                - Interesting events, like user logging in, etc.
     *  8 = Debug               - Detailed debug information.
     *  9 = All Messages
     *
     * You can also pass an array with threshold levels to show individual log levels:
     *
     * $threshold = [0, 1, 3, 8];
     *
     * For a live site you'll usually enable only error (0, 1, 3, 4, 5, 6) logging.
     * For development you'll usually enable all logging (9).
     */
    public int $threshold = 9; // Activer tous les niveaux de log en mode debug

    /**
     * --------------------------------------------------------------------------
     * Log File Permissions
     * --------------------------------------------------------------------------
     *
     * The system default is 0644 - specifically the same default as the
     * file system. You can change this value to change the permissions
     * of the log files.
     */
    public int $filePermissions = 0644;

    /**
     * --------------------------------------------------------------------------
     * Log Format
     * --------------------------------------------------------------------------
     *
     * Each item that is logged must include a timestamp. You can use PHP
     * date codes for formatting the date, or be given the DateTime object.
     */
    public string $dateFormat = 'Y-m-d H:i:s';

    /**
     * --------------------------------------------------------------------------
     * Log Handlers
     * --------------------------------------------------------------------------
     *
     * The logging system can execute code on the following events by attaching
     * handlers. These are called by the Logger if they are available.
     * You can also create your own handlers by extending the BaseHandler.
     *
     * Available Handlers:
     * - CodeIgniter\Log\Handlers\FileHandler
     * - CodeIgniter\Log\Handlers\ChromeLoggerHandler
     * - CodeIgniter\Log\Handlers\FirePHPHandler
     * - CodeIgniter\Log\Handlers\SlackHandler
     * - CodeIgniter\Log\Handlers\SyslogHandler
     * - CodeIgniter\Log\Handlers\ErrorlogHandler
     */
    public array $handlers = [
        // File Handler
        'CodeIgniter\Log\Handlers\FileHandler' => [
            'handles' => ['critical', 'alert', 'emergency', 'debug', 'error', 'info', 'notice', 'warning'],
            'path'    => WRITEPATH . 'logs/',
            'level'   => 9,
        ],

        // Chrome Logger Handler (pour le debug en navigateur)
        'CodeIgniter\Log\Handlers\ChromeLoggerHandler' => [
            'handles' => ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'],
            'level'   => 9,
        ],
    ];

    /**
     * --------------------------------------------------------------------------
     * Log File Extension
     * --------------------------------------------------------------------------
     *
     * The file extension to use for log files.
     */
    public string $fileExtension = 'log';

    /**
     * --------------------------------------------------------------------------
     * Log File Max Size
     * --------------------------------------------------------------------------
     *
     * The maximum size of a log file in bytes.
     */
    public int $maxFileSize = 1024 * 1024; // 1 MB

    /**
     * --------------------------------------------------------------------------
     * Log File Max Age
     * --------------------------------------------------------------------------
     *
     * The maximum age of a log file in days.
     */
    public int $maxFileAge = 30;
}
