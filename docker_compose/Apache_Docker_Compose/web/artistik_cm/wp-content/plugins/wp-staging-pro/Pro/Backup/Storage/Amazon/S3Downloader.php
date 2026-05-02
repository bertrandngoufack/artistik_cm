<?php

namespace WPStaging\Pro\Backup\Storage\Amazon;

use WPStaging\Framework\Adapter\Directory;
use WPStaging\Pro\Backup\Storage\BaseS3\S3Downloader as BaseS3Downloader;

class S3Downloader extends BaseS3Downloader
{
    public function __construct(S3 $auth, Directory $directory)
    {
        parent::__construct($auth, $directory);
    }
}
