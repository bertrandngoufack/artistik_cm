<?php

namespace WPStaging\Pro\Backup\Storage\Wasabi;

use WPStaging\Framework\Security\Auth as WPStagingAuth;
use WPStaging\Framework\Utils\Sanitize;
use WPStaging\Pro\Backup\Storage\BaseS3\S3Auth;

class Auth extends S3Auth
{
    protected $version = 'latest';

    /** @var null|string */
    protected $endpoint = 's3.[region]wasabisys.com';

    public function __construct(WPStagingAuth $wpstagingAuth, Sanitize $sanitize)
    {
        $this->identifier = 'wasabi-s3';
        $this->label = 'Wasabi S3';
        parent::__construct($wpstagingAuth, $sanitize);
    }

    /**
     * Return list of regions supported by Wasabi S3
     * @return array
     *
     * @todo Refactor to use API when Wasabi S3 provide an API to fetch regions
     */
    public function getRegions()
    {
        return [
            'us-east-1'      => 'US East (N. Virginia)',
            'us-east-2'      => 'US East (N. Virginia)',
            'us-central-1'   => 'US Central (Texas)',
            'us-west-1'      => 'US West (Oregon)',
            'ap-southeast-1' => 'Asia Pacific (Singapore)',
            'ap-southeast-2' => 'Asia Pacific (Sydney)',
            'ap-northeast-1' => 'Asia Pacific (Tokyo)',
            'ap-northeast-2' => 'Asia Pacific (Osaka)',
            'ca-central-1'   => 'Canada (Toronto)',
            'eu-central-1'   => 'Europe (Amsterdam)',
            'eu-central-2'   => 'Europe (Frankfurt)',
            'eu-west-1'      => 'Europe (London)',
            'eu-west-2'      => 'Europe (Paris)',
            'eu-south-1'     => 'Europe (Milan)',
        ];
    }

    /**
     * @param array $settings
     * @return bool
     */
    public function updateSettings($settings)
    {
        // Handle custom region for Wasabi
        if (isset($settings['region']) && $settings['region'] === 'custom' && isset($settings['custom_region'])) {
            $settings['region'] = $this->sanitize->sanitizeString($settings['custom_region']);
            // Remove the custom_region from settings to avoid storing it separately
            unset($settings['custom_region']);
        }

        return parent::updateSettings($settings);
    }

    protected function setupProvider()
    {
        // no-op
    }
}
