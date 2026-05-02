<?php

namespace WPStaging\Pro\RemoteSync;

/**
 * The OOP representation of the key that is responsible for connecting sites for sync feature
 */
class ConnectionKey
{
    /**
     * @var string
     */
    const OPTION_REMOTE_SYNC_API_TOKEN = 'wpstg_remote_sync_api_token';

    /**
     * @var string
     */
    const OPTION_REMOTE_SYNC_PASSWORD = 'wpstg_remote_sync_password';

    /**
     * @var string
     */
    const OPTION_REMOTE_SYNC_ENABLED = 'wpstg_remote_sync_enabled';

    /**
     * @var string
     */
    private $remoteUrl;

    /**
     * @var string
     */
    private $apiToken;

    /**
     * Whether the remote sync feature is accepting incoming connections.
     *
     * @return bool
     */
    public static function isEnabled(): bool
    {
        return get_option(self::OPTION_REMOTE_SYNC_ENABLED, '0') === '1';
    }

    /**
     * @param bool $enabled
     * @return bool
     */
    public static function setEnabled(bool $enabled): bool
    {
        return update_option(self::OPTION_REMOTE_SYNC_ENABLED, $enabled ? '1' : '0', false);
    }

    /**
     * @param string $connectionKey
     * @return ConnectionKey
     */
    public static function parse(string $connectionKey)
    {
        $decodedString = base64_decode($connectionKey);
        if ($decodedString === false) {
            throw new \InvalidArgumentException('Invalid connection key format.');
        }

        $parts = json_decode($decodedString);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid connection key format.');
        }

        if (!isset($parts->remote_url) || !isset($parts->api_token)) {
            throw new \InvalidArgumentException('Invalid connection key format.');
        }

        return new self($parts->remote_url, $parts->api_token);
    }

    /**
     * @param string $remoteUrl
     * @param string $apiToken
     */
    public function __construct(string $remoteUrl = '', string $apiToken = '')
    {
        $this->remoteUrl = $remoteUrl;
        if (empty($this->remoteUrl)) {
            $this->remoteUrl = site_url();
        }

        $this->apiToken = $apiToken;
        if (empty($this->apiToken)) {
            $this->setApiToken();
        }
    }

    public function protect(string $password): bool
    {
        $password = password_hash($password, PASSWORD_BCRYPT);
        return update_option(self::OPTION_REMOTE_SYNC_PASSWORD, $password, false);
    }

    public function unprotect(): bool
    {
        return delete_option(self::OPTION_REMOTE_SYNC_PASSWORD);
    }

    public function isProtected(): bool
    {
        $password = get_option(self::OPTION_REMOTE_SYNC_PASSWORD, false);
        return !empty($password);
    }

    /**
     * @return string
     */
    public function getRemoteUrl(): string
    {
        return $this->remoteUrl;
    }

    /**
     * @return string
     */
    public function getApiToken(): string
    {
        return $this->apiToken;
    }

    /**
     * @return string
     */
    public function getConnectionKey(): string
    {
        return base64_encode(json_encode(
            [
                'remote_url' => $this->remoteUrl,
                'api_token'  => $this->apiToken,
            ]
        ));
    }

    /**
     * @param string $token
     * @param string $password
     * @return bool
     */
    public function authenticate(string $token, string $password = ''): bool
    {
        if (empty($token) || empty($this->apiToken)) {
            return false;
        }

        if ($this->apiToken !== $token) {
            return false;
        }

        if ($this->isProtected()) {
            $storedPassword = get_option(self::OPTION_REMOTE_SYNC_PASSWORD, false);
            return password_verify($password, $storedPassword);
        }

        if (!empty($password)) {
            return false;
        }

        return true;
    }

    /**
     * @return void
     */
    public function regenerate()
    {
        $this->apiToken = bin2hex(random_bytes(16));
        update_option(self::OPTION_REMOTE_SYNC_API_TOKEN, $this->apiToken, false);
    }

    /**
     * @return void
     */
    private function setApiToken()
    {
        $this->apiToken = get_option(self::OPTION_REMOTE_SYNC_API_TOKEN, false);
        if (!empty($this->apiToken)) {
            return;
        }

        $this->regenerate();
    }
}
