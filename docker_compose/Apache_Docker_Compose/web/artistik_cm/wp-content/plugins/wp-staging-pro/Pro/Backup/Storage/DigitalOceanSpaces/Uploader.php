<?php

namespace WPStaging\Pro\Backup\Storage\DigitalOceanSpaces;

use WPStaging\Framework\Utils\Strings;
use WPStaging\Pro\Backup\Storage\BaseS3\S3Uploader as BaseS3Uploader;

class Uploader extends BaseS3Uploader
{
    public function __construct(Auth $auth, Strings $strings)
    {
        parent::__construct($auth, $strings);
    }

    public function getProviderName()
    {
        return 'DigitalOcean Spaces';
    }
}
