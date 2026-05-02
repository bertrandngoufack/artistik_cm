<?php

namespace WPStaging\Pro\Backup\Storage\Wasabi;

use WPStaging\Framework\Adapter\Directory;
use WPStaging\Pro\Backup\Storage\BaseS3\S3Downloader as BaseS3Downloader;

class Downloader extends BaseS3Downloader
{
    public function __construct(Auth $auth, Directory $directory)
    {
        parent::__construct($auth, $directory);
    }
}
