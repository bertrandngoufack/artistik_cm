<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Toolbar Configuration
 */
class Toolbar extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Toolbar Display
     * --------------------------------------------------------------------------
     *
     * When true, this will display the toolbar at the bottom of the page.
     */
    public bool $enabled = true;

    /**
     * --------------------------------------------------------------------------
     * Toolbar Position
     * --------------------------------------------------------------------------
     *
     * The position of the toolbar. Can be 'top' or 'bottom'.
     */
    public string $position = 'bottom';

    /**
     * --------------------------------------------------------------------------
     * Toolbar Collectors
     * --------------------------------------------------------------------------
     *
     * List of toolbar collectors that will be called when Debug Toolbar
     * fires up and collects data from.
     */
    public array $collectors = [
        \CodeIgniter\Debug\Toolbar\Collectors\Timers::class,
        \CodeIgniter\Debug\Toolbar\Collectors\Database::class,
        \CodeIgniter\Debug\Toolbar\Collectors\Logs::class,
        \CodeIgniter\Debug\Toolbar\Collectors\Views::class,
        \CodeIgniter\Debug\Toolbar\Collectors\Files::class,
        \CodeIgniter\Debug\Toolbar\Collectors\Routes::class,
        \CodeIgniter\Debug\Toolbar\Collectors\Events::class,
    ];

    /**
     * --------------------------------------------------------------------------
     * Collect Var Data
     * --------------------------------------------------------------------------
     *
     * If set to false var data from the views will not be collected. Useful to
     * avoid high memory usage when there are lots of data passed to the view.
     */
    public bool $collectVarData = true;

    /**
     * --------------------------------------------------------------------------
     * Max Queries
     * --------------------------------------------------------------------------
     *
     * If the Database Collector is enabled, it will log every query that the
     * the system generates so they can be displayed on the toolbar's timeline
     * and in the query log. This can lead to memory issues in some instances
     * with hundreds of queries.
     *
     * `$maxQueries` defines the maximum amount of queries that will be stored.
     */
    public int $maxQueries = 100;

    /**
     * --------------------------------------------------------------------------
     * Watched Directories
     * --------------------------------------------------------------------------
     *
     * Contains an array of directories that will be watched for changes and
     * used to determine if the hot-reload feature should reload the page or not.
     * We restrict the values to keep performance as high as possible.
     *
     * NOTE: The ROOTPATH will be prepended to all values.
     *
     * @var list<string>
     */
    public array $watchedDirectories = [
        'app',
    ];

    /**
     * --------------------------------------------------------------------------
     * Watched File Extensions
     * --------------------------------------------------------------------------
     *
     * Contains an array of file extensions that will be watched for changes and
     * used to determine if the hot-reload feature should reload the page or not.
     */
    public array $watchedExtensions = [
        'php', 'css', 'js', 'html', 'svg', 'json', 'env',
    ];

    /**
     * --------------------------------------------------------------------------
     * Toolbar Views Path
     * --------------------------------------------------------------------------
     *
     * The full path to the the views that are used by the toolbar.
     * This MUST have a trailing slash.
     */
    public string $viewsPath = SYSTEMPATH . 'Debug/Toolbar/Views/';

    /**
     * --------------------------------------------------------------------------
     * Toolbar Panels
     * --------------------------------------------------------------------------
     *
     * The panels to display in the toolbar.
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
    ];

    /**
     * --------------------------------------------------------------------------
     * Toolbar Max History
     * --------------------------------------------------------------------------
     *
     * The maximum number of history entries to keep.
     */
    public int $maxHistory = 20;

    /**
     * --------------------------------------------------------------------------
     * Toolbar Theme
     * --------------------------------------------------------------------------
     *
     * The theme to use for the toolbar.
     */
    public string $theme = 'default';

    /**
     * --------------------------------------------------------------------------
     * Toolbar Debug Mode
     * --------------------------------------------------------------------------
     *
     * When true, this will show additional debug information.
     */
    public bool $debug = true;
}
