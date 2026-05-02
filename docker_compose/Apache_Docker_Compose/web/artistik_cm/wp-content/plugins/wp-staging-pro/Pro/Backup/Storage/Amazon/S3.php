<?php

namespace WPStaging\Pro\Backup\Storage\Amazon;

use WPStaging\Framework\Security\Auth as WPStagingAuth;
use WPStaging\Framework\Utils\Sanitize;
use WPStaging\Pro\Backup\Storage\BaseS3\S3Auth;

use function WPStaging\functions\debug_log;

/**
 * Handles Amazon S3 storage provider for backup operations.
 */
class S3 extends S3Auth
{
    /** @var string */
    protected $version = '2006-03-01';

    /**
     * Transient key for caching AWS regions
     * @var string
     */
    const TRANSIENT_AWS_REGIONS = 'wpstg_aws_s3_regions';

    /**
     * AWS IP ranges API endpoint
     * @var string
     */
    const AWS_IP_RANGES_URL = 'https://ip-ranges.amazonaws.com/ip-ranges.json';

    public function __construct(WPStagingAuth $wpstagingAuth, Sanitize $sanitize)
    {
        $this->identifier = 'amazons3';
        $this->label      = 'Amazon S3';
        parent::__construct($wpstagingAuth, $sanitize);
    }

    /**
     * Return list of regions supported by Amazon S3
     * Fetches regions dynamically from AWS IP ranges API with caching
     * Falls back to hardcoded list if API call fails
     * @return array
     */
    public function getRegions()
    {
        // Try to get cached regions first
        $cachedRegions = get_transient(self::TRANSIENT_AWS_REGIONS);
        if (is_array($cachedRegions) && !empty($cachedRegions)) {
            return $cachedRegions;
        }

        // Try to fetch regions from AWS API
        $regions = $this->fetchRegionsFromApi();
        // If API call failed, use fallback list
        if (empty($regions)) {
            $regions = $this->getFallbackRegions();
        }

        // Cache the regions
        if (!empty($regions)) {
            set_transient(self::TRANSIENT_AWS_REGIONS, $regions, WEEK_IN_SECONDS);
        }

        return $regions;
    }

    /**
     * Fetch regions from AWS IP ranges API
     * @return array
     */
    protected function fetchRegionsFromApi()
    {
        $response = wp_remote_get(self::AWS_IP_RANGES_URL, [
            'timeout'   => 10,
            'sslverify' => true,
        ]);

        if (is_wp_error($response)) {
            debug_log('Failed to fetch AWS regions from API: ' . $response->get_error_message());
            return [];
        }

        if (wp_remote_retrieve_response_code($response) !== 200) {
            debug_log('Failed to fetch AWS regions from API HTTP Code: ' . wp_remote_retrieve_response_code($response));
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            debug_log('Failed to parse AWS IP ranges JSON: ' . json_last_error_msg());
            return [];
        }

        $regions = [];
        $this->extractRegions($data['prefixes'] ?? [], $regions);
        $this->extractRegions($data['ipv6_prefixes'] ?? [], $regions);

        asort($regions, SORT_NATURAL | SORT_FLAG_CASE);
        return $regions;
    }

    /**
     * Get human-readable display name for a region code
     * @param string $regionCode
     * @return string
     */
    protected function getRegionDisplayName(string $regionCode): string
    {
        $fallbackRegions = $this->getFallbackRegions();
        if (isset($fallbackRegions[$regionCode])) {
            return $fallbackRegions[$regionCode];
        }

        return $this->formatRegionName($regionCode);
    }

    /**
     * Format a region code into a human-readable name.
     * @param string $regionCode
     * @return string
     */
    protected function formatRegionName(string $regionCode): string
    {
        $parts = explode('-', $regionCode);

        if (count($parts) >= 2) {
            $prefix   = strtolower($parts[0]);
            $area     = $this->getAreaNameFromPrefix($prefix) ?? strtoupper($prefix);
            $location = ucfirst($parts[1]);
            $number   = isset($parts[2]) ? ' ' . $parts[2] : '';

            return $area . ' ' . $location . $number;
        }

        // Fallback: simple UC words
        return ucwords(str_replace('-', ' ', $regionCode));
    }

    /**
     * Get fallback hardcoded list of AWS S3 regions
     * This is used when API call fails or returns incomplete data
     * @return array
     */
    protected function getFallbackRegions(): array
    {
        return [
            'af-south-1'     => 'Africa (Cape Town)',
            'ap-east-1'      => 'Asia Pacific (Hong Kong)',
            'ap-northeast-1' => 'Asia Pacific (Tokyo)',
            'ap-northeast-2' => 'Asia Pacific (Seoul)',
            'ap-northeast-3' => 'Asia Pacific (Osaka)',
            'ap-south-1'     => 'Asia Pacific (Mumbai)',
            'ap-south-2'     => 'Asia Pacific (Hyderabad)',
            'ap-southeast-1' => 'Asia Pacific (Singapore)',
            'ap-southeast-2' => 'Asia Pacific (Sydney)',
            'ap-southeast-3' => 'Asia Pacific (Jakarta)',
            'ap-southeast-4' => 'Asia Pacific (Melbourne)',
            'ca-central-1'   => 'Canada (Central)',
            'ca-west-1'      => 'Canada West (Calgary)',
            'eu-central-1'   => 'Europe (Frankfurt)',
            'eu-central-2'   => 'Europe (Zurich)',
            'eu-north-1'     => 'Europe (Stockholm)',
            'eu-south-1'     => 'Europe (Milan)',
            'eu-south-2'     => 'Europe (Spain)',
            'eu-west-1'      => 'Europe (Ireland)',
            'eu-west-2'      => 'Europe (London)',
            'eu-west-3'      => 'Europe (Paris)',
            'il-central-1'   => 'Israel (Tel Aviv)',
            'me-central-1'   => 'Middle East (UAE)',
            'me-south-1'     => 'Middle East (Bahrain)',
            'sa-east-1'      => 'South America (São Paulo)',
            'us-east-1'      => 'US East (N. Virginia)',
            'us-east-2'      => 'US East (Ohio)',
            'us-west-1'      => 'US West (N. California)',
            'us-west-2'      => 'US West (Oregon)',
        ];
    }

    /**
     * Map AWS region prefix to a readable area name.
     * @param string $prefix
     * @return string|null
     */
    protected function getAreaNameFromPrefix($prefix)
    {
        $map = [
            'af' => 'Africa',
            'ap' => 'Asia Pacific',
            'ca' => 'Canada',
            'cn' => 'China',
            'eu' => 'Europe',
            'il' => 'Israel',
            'me' => 'Middle East',
            'mx' => 'Mexico',
            'sa' => 'South America',
            'us' => 'US',
        ];

        return $map[$prefix] ?? null;
    }

    protected function setupProvider()
    {
        // no-op
    }

    /**
     * Extract S3 regions from AWS IP ranges entries
     * @param array $entries
     * @param array &$regions
     */
    private function extractRegions(array $entries, array &$regions)
    {
        $internalRegions = [
            'GLOBAL',
            'aws-global',
            'AMAZON',
            'ROUTE53',
            'CLOUDFRONT',
            'eusc-de-east-1', // skip internal EU SC
        ];

        foreach ($entries as $prefix) {
            if (!isset($prefix['service']) || $prefix['service'] !== 'S3' || !isset($prefix['region'])) {
                continue;
            }

            $regionCode = $prefix['region'];

            if (in_array($regionCode, $internalRegions, true)) {
                continue;
            }

            if (isset($regions[$regionCode])) {
                continue;
            }

            $regions[$regionCode] = $this->getRegionDisplayName($regionCode);
        }
    }
}
