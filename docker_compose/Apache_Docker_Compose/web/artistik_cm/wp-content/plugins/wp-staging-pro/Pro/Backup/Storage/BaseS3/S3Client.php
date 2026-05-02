<?php

namespace WPStaging\Pro\Backup\Storage\BaseS3;

use WPStaging\Framework\Traits\HttpRequestTrait;

/**
 * Custom S3Client implementation to replace AWS SDK dependency
 * Acts as a drop-in replacement for AWS\S3\S3Client
 */
class S3Client
{
    use HttpRequestTrait;

    /** @var array */
    private $config;

    /** @var string */
    private $accessKey;

    /** @var string */
    private $secretKey;

    /** @var string */
    private $region;

    /** @var string */
    private $endpoint;

    /** @var bool */
    private $usePathStyleEndpoint;

    /** @var bool */
    private $ssl;

    /** @var string */
    private $host = '';

    public function __construct(array $config)
    {
        $this->config                = $config;
        $this->accessKey             = $config['credentials']['key']    ?? '';
        $this->secretKey             = $config['credentials']['secret'] ?? '';
        $this->region                = $config['region']                ?? 'us-east-1';
        $this->endpoint              = $config['endpoint']              ?? '';
        $this->usePathStyleEndpoint  = $config['use_path_style_endpoint'] ?? false;
        $this->ssl                   = isset($config['http']['verify']) ? (bool)$config['http']['verify'] : true;
    }

    public function putObject(array $params): array
    {
        $body        = $params['Body']        ?? '';
        $contentType = $params['ContentType'] ?? 'binary/octet-stream';

        $headers = [
            'Content-Type'   => $contentType,
            'Content-Length' => strlen($body),
        ];

        // Add optional headers
        if (isset($params['ContentMD5'])) {
            $headers['Content-MD5'] = $params['ContentMD5'];
        }

        $result = $this->sendRequest('PUT', $params, $headers, $body);

        return [
            'ETag'      => $result['etag'],
            '@metadata' => [
                'statusCode' => $result['statusCode'],
            ],
        ];
    }

    public function getObject(array $params): array
    {
        $headers = [];
        if (!empty($params['Range'])) {
            $headers['Range'] = $params['Range'];
        }

        $result = $this->sendRequest('GET', $params, $headers);

        return [
            'Body'      => $result['body'],
            '@metadata' => [
                'statusCode' => $result['statusCode'],
            ],
        ];
    }

    public function deleteObject(array $params): array
    {
        $result = $this->sendRequest('DELETE', $params);

        return [
            '@metadata' => [
                'statusCode' => $result['statusCode'],
            ],
        ];
    }

    public function listObjects(array $params): array
    {
        $prefix = $params['Prefix'] ?? '';
        $query = 'list-type=2';

        if (!empty($prefix)) {
            $query .= '&prefix=' . rawurlencode($prefix);
        }

        $result = $this->sendRequest('GET', $params, [], '', $query);
        $body   = $result['body'];

        $xml             = @simplexml_load_string($body);
        $responseInArray = json_decode(json_encode($xml), true);

        $contents = [];
        if (!empty($responseInArray['Contents'])) {
            $contents = $responseInArray['Contents'];
            if (!is_array(current($contents))) {
                $contents = [$contents];
            }
        }

        return [
            'Contents'  => $contents,
            '@metadata' => [
                'statusCode' => 200,
            ],
        ];
    }

    public function createMultipartUpload(array $params): array
    {
        $result = $this->sendRequest('POST', $params, [], '', 'uploads=');
        $body   = $result['body'];

        preg_match('/<UploadId>(.+?)<\/UploadId>/', $body, $matches);
        $uploadId = $matches[1] ?? '';

        return [
            'UploadId'  => $uploadId,
            '@metadata' => [
                'statusCode' => 200,
            ],
        ];
    }

    /**
     * Upload part for multipart upload
     *
     * @param array $params
     * @return array
     */
    public function uploadPart(array $params): array
    {
        $body  = $params['Body'];
        $query = "partNumber={$params['PartNumber']}&uploadId=" . rawurlencode($params['UploadId']);

        $headers = [
            'Content-Length' => strlen($body),
            'Content-Type'   => 'application/octet-stream',
        ];

        if (isset($params['ContentMD5'])) {
            $headers['Content-MD5'] = $params['ContentMD5'];
        }

        $result = $this->sendRequest('PUT', $params, $headers, $body, $query);

        return [
            'ETag'      => $result['etag'],
            '@metadata' => [
                'statusCode' => $result['statusCode'],
            ],
        ];
    }

    public function completeMultipartUpload(array $params): array
    {
        $uploadId = $params['UploadId'];
        $parts    = $params['MultipartUpload']['Parts'] ?? [];
        $query    = 'uploadId=' . rawurlencode($uploadId);

        $xml = '<CompleteMultipartUpload>';
        foreach ($parts as $part) {
            $partNumber = $part['PartNumber'];
            $etag       = $part['ETag'];
            $xml .= "<Part><PartNumber>$partNumber</PartNumber><ETag>$etag</ETag></Part>";
        }

        $xml .= '</CompleteMultipartUpload>';

        $headers = [
            'Content-Type'   => 'application/xml',
            'Content-Length' => strlen($xml),
        ];

        $result = $this->sendRequest('POST', $params, $headers, $xml, $query);

        return [
            '@metadata' => [
                'statusCode' => $result['statusCode'],
            ],
        ];
    }

    public function abortMultipartUpload(array $params): array
    {
        $query   = 'uploadId=' . rawurlencode($params['UploadId']);
        $result  = $this->sendRequest('DELETE', $params, [], '', $query);

        return [
            '@metadata' => [
                'statusCode' => $result['statusCode'],
            ],
        ];
    }

    /**
     * Upload a file using multipart upload or simple upload
     *
     * @param string $bucket
     * @param string $key
     * @param string $body
     * @param string $acl
     * @param array $options
     * @return array
     */
    public function upload(string $bucket, string $key, string $body, string $acl = 'private', array $options = []): array
    {
        $params = [
            'Bucket'      => $bucket,
            'Key'         => $key,
            'Body'        => $body,
            'ContentType' => 'binary/octet-stream',
        ];

        // Add options from $options['params'] if present
        if (isset($options['params']) && is_array($options['params'])) {
            foreach ($options['params'] as $paramKey => $paramValue) {
                $params[$paramKey] = $paramValue;
            }
        }

        return $this->putObject($params);
    }

    public function getObjectLockConfiguration(array $params): array
    {
        $result = $this->sendRequest('GET', $params, [], '', 'object-lock=');
        $body   = $result['body'];

        $xml                = @simplexml_load_string($body);
        $objectLockEnabled  = (!empty($xml) && (string)$xml->ObjectLockEnabled === 'Enabled');

        return [
            'ObjectLockConfiguration' => [
                'ObjectLockEnabled' => $objectLockEnabled ? 'Enabled' : 'Disabled',
            ],
            '@metadata'               => [
                'statusCode' => 200,
            ],
        ];
    }

    public function deleteObjects(array $params): array
    {
        $objects = $params['Delete']['Objects'] ?? [];
        $xmlBody = '<Delete>';
        foreach ($objects as $object) {
            $key      = $object['Key'];
            $xmlBody .= "<Object><Key>$key</Key></Object>";
        }

        $xmlBody .= '<Quiet>true</Quiet></Delete>';
        $headers = [
            'Content-Type' => 'application/xml',
            'Content-MD5'  => base64_encode(md5($xmlBody, true)),
        ];

        $result = $this->sendRequest('POST', $params, $headers, $xmlBody, 'delete=');

        return [
            '@metadata' => [
                'statusCode' => $result['statusCode'],
            ],
        ];
    }

    private function sendRequest(string $method, array $params, array $extraHeaders = [], string $body = '', string $query = ''): array
    {
        $bucket = $params['Bucket'];
        $key    = $params['Key'] ?? '';

        $uri = $this->getUri($bucket, $key);
        $url = $this->getScheme() . $this->getHost($bucket) . $uri;

        if (!empty($query)) {
            $url .= '?' . $query;
        }

        $headers = array_merge([
            'Host'                 => $this->getHost($bucket),
            'x-amz-content-sha256' => hash('sha256', $body ?: ''),
        ], $extraHeaders);

        $signedHeaders = $this->getAuthHeader($method, $headers, $uri, $query, $body);
        $headers       = array_merge($headers, $signedHeaders);

        $args = [
            'method'  => $method,
            'headers' => $headers,
        ];

        if (!empty($body)) {
            $args['body'] = $body;
        }

        $response = $this->getRemoteRequest($url, $args);

        return [
            'response'   => $response,
            'statusCode' => wp_remote_retrieve_response_code($response),
            'body'       => wp_remote_retrieve_body($response),
            'etag'       => wp_remote_retrieve_header($response, 'etag'),
        ];
    }

    private function getUri(string $bucket, string $key = ''): string
    {
        if ($this->usePathStyleEndpoint || strpos($this->getHost($bucket), $bucket) === false) {
            return '/' . $bucket . ($key ? '/' . $key : '/');
        }

        return $key ? '/' . $key : '/';
    }

    private function getHost(string $bucket = ''): string
    {
        if (!empty($this->host)) {
            return $this->host;
        }

        if (empty($this->endpoint)) {
            $this->endpoint = "s3.[region]amazonaws.com";
        }

        $this->host = preg_replace('/^https?:\/\//', '', $this->endpoint);

        // Custom endpoint or provider-specific endpoint
        if (strpos($this->host, '[region]') !== false) {
            // Replace [region] placeholder
            $this->host = str_replace('[region]', $this->region . '.', $this->endpoint);
        }

        if (!$this->usePathStyleEndpoint) {
            $this->host = "$bucket.{$this->host}";
        }

        return $this->host;
    }

    private function getScheme(): string
    {
        return $this->ssl ? 'https://' : 'http://';
    }

    /**
     * Generate AWS Signature V4 authorization header
     *
     * @param string $method
     * @param array $headers
     * @param string $uri
     * @param string $query
     * @param string $body
     * @return array
     */
    private function getAuthHeader(string $method, array $headers, string $uri = '/', string $query = '', string $body = ''): array
    {
        $time = gmdate('Ymd\THis\Z');
        $date = gmdate('Ymd');

        $headers['x-amz-date'] = $time;
        ksort($headers);

        $canonicalHeaders = '';
        foreach ($headers as $key => $value) {
            $canonicalHeaders .= strtolower($key) . ':' . trim($value) . "\n";
        }

        $signedHeaders = implode(';', array_map('strtolower', array_keys($headers)));

        // Use the proper SHA256 hash for the body
        $hashedBody = hash('sha256', $body);

        $canonicalRequest = implode("\n", [
            $method,
            $uri,
            $query,
            $canonicalHeaders,
            $signedHeaders,
            $hashedBody,
        ]);

        $credentialScope = "$date/{$this->region}/s3/aws4_request";
        $stringToSign    = implode("\n", [
            'AWS4-HMAC-SHA256',
            $time,
            $credentialScope,
            hash('sha256', $canonicalRequest),
        ]);

        $kDate    = hash_hmac('sha256', $date, 'AWS4' . $this->secretKey, true);
        $kRegion  = hash_hmac('sha256', $this->region, $kDate, true);
        $kService = hash_hmac('sha256', 's3', $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

        $signature     = hash_hmac('sha256', $stringToSign, $kSigning);

        $authorization = "AWS4-HMAC-SHA256 Credential={$this->accessKey}/$credentialScope, SignedHeaders=$signedHeaders, Signature=$signature";

        return [
            'Authorization' => $authorization,
            'x-amz-date'    => $time,
        ];
    }
}
