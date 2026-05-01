<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Images\Handlers\GDHandler;
use CodeIgniter\Images\Handlers\ImageMagickHandler;

class Images extends BaseConfig
{
    /**
     * Default handler used if no other handler is specified.
     */
    public string $defaultHandler = 'gd';

    /**
     * The path to the image library.
     * Required for ImageMagick, GraphicsMagick, or NetPBM.
     */
    public string $libraryPath = '/usr/local/bin/convert';

    /**
     * --------------------------------------------------------------------------
     * Image Debug
     * --------------------------------------------------------------------------
     *
     * When true, this will enable image debugging.
     */
    public bool $debug = true;

    /**
     * --------------------------------------------------------------------------
     * Image Logging
     * --------------------------------------------------------------------------
     *
     * When true, this will log image information.
     */
    public bool $logging = true;

    /**
     * The available handler classes.
     *
     * @var array<string, string>
     */
    public array $handlers = [
        'gd'      => GDHandler::class,
        'imagick' => ImageMagickHandler::class,
    ];
}
