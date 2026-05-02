<?php

namespace WPStaging\Pro\Backup\Storage;

use WPStaging\Backup\Storage\Providers;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Security\Auth;
use WPStaging\Framework\Adapter\DateTimeAdapter;

class StorageBase
{
    /** @var Providers */
    private $providers;

    /** @var string */
    private $error;

    /** @var string */
    private $provider;

    /** @var Auth */
    private $wpstagingAuth;

    /**
     * @param Providers $providers
     * @param Auth $wpstagingAuth
     */
    public function __construct(Providers $providers, Auth $wpstagingAuth)
    {
        $this->providers = $providers;
        $this->wpstagingAuth = $wpstagingAuth;
    }

    /**
     * @return void
     */
    public function revoke()
    {
        if (!$this->wpstagingAuth->isAuthenticatedRequest()) {
            $this->jsonResponse('Unauthorized.');
        }

        $authProvider = $this->getProvider();
        if ($authProvider === false) {
            $this->jsonResponse($this->error);
        }

        $result       = $authProvider->revoke();
        $providerName = $this->providers->getStorageProperty($this->provider, 'name', true);
        if (!$result) {
            $this->jsonResponse("Failed to revoke provider for: " . $providerName);
        }

        if ($authProvider->getIdentifier() === 'googledrive') {
            $this->jsonResponse("Settings removed successfully for: " . $providerName, true);
        }

        $this->jsonResponse("Revoke successful for: " . $providerName, true);
    }

    /**
     * @return void
     */
    public function authenticate()
    {
        if (!$this->wpstagingAuth->isAuthenticatedRequest()) {
            $this->jsonResponse('Unauthorized.');
        }

        $authProvider = $this->getProvider();
        if ($authProvider === false) {
            $this->jsonResponse($this->error);
        }

        $result = $authProvider->authenticate();

        $providerName = $this->providers->getStorageProperty($this->provider, 'name', true);
        if ($result !== true) {
            $this->jsonResponse(
                "Authentication failed to $providerName - Open \"System Info > WP STAGING Logs\" for details."
            );
        }

        $this->jsonResponse("Successfully connected to $providerName.", true);
    }

    /**
     * @return string|void
     */
    public function testConnection()
    {
        if (!$this->wpstagingAuth->isAuthenticatedRequest()) {
            $this->jsonResponse('Unauthorized.');
        }

        $authProvider = $this->getProvider();
        if ($authProvider === false) {
            $this->jsonResponse($this->error);
        }

        $result = $authProvider->testConnection();

        $providerName = $this->providers->getStorageProperty($this->provider, 'name', true);
        $errorMsg     = $authProvider->getError() ? '<br>' . esc_html($authProvider->getError()) : '';

        if ($result !== true) {
            $message = "Connection failed to {$providerName}. $errorMsg";
            $this->jsonResponse($message);
        } elseif (!empty($errorMsg)) {
            $message = "$providerName - Connection succeed with warning! $errorMsg";
            $this->jsonResponse($message, false, true);
        }

        $this->jsonResponse($providerName . " - Connection test succeeded.", true);
    }

    /**
     * @return void
     */
    public function updateSettings()
    {
        if (!$this->wpstagingAuth->isAuthenticatedRequest()) {
            $this->jsonResponse('Unauthorized.');
        }

        $authProvider = $this->getProvider();
        if ($authProvider === false) {
            $this->jsonResponse($this->error);
        }

        $result = $authProvider->updateSettings($_POST);

        $providerName = $this->providers->getStorageProperty($this->provider, 'name', true);
        if (!$result) {
            $errorMsg = $authProvider->getError() ? esc_html($authProvider->getError()) : '';
            $this->jsonResponse("Failed to save settings for $providerName : " . $errorMsg);
        }

        if (is_wp_error($result)) {
            $this->jsonResponse("Failed to save settings for $providerName : " . $result->get_error_message());
        }

        $options   = $authProvider->getOptions();
        $updatedAt = '';
        if (!empty($options['lastUpdated'])) {
            $updatedAt = $this->getFormattedProviderUpdatedAt($options['lastUpdated']);
        }

        if ($authProvider->isAuthenticated()) {
            $this->jsonResponse("Settings saved successfully.", true, false, $updatedAt);
        }

        $this->jsonResponse("Settings saved, but connection to $providerName failed. Check the credentials or open \"System Info > WP STAGING Logs\" for details.", true, true, $updatedAt);
    }

    /**
     * @return bool|AbstractStorage
     */
    private function getProvider()
    {
        if (!isset($_POST['provider'])) {
            $this->error = 'Provider not set!';
            return false;
        }

        $provider = sanitize_text_field($_POST['provider']);
        if (!in_array($provider, $this->providers->getStorageIds(true))) {
            $this->error = 'Provider not available for remote storage!';
            return false;
        }

        $authClass = $this->providers->getStorageProperty($provider, 'authClass', true);
        if ($authClass === false || !class_exists($authClass)) {
            $this->error = "Auth class for provider doesn't exist!";
            return false;
        }

        $this->provider = $provider;

        return WPStaging::make($authClass);
    }

    /**
     * @param string $message
     * @param bool $success
     * @param bool $warning
     * @param string $updatedAt
     * @return void
     */
    private function jsonResponse($message = '', $success = false, $warning = false, $updatedAt = '')
    {
        wp_send_json([
            'success'    => $success,
            'warning'    => $warning,
            'message'    => $message,
            'updated_at' => $updatedAt,
        ]);
    }

    /**
     * @return void
     */
    public function deleteSettings()
    {
        if (!$this->wpstagingAuth->isAuthenticatedRequest()) {
            $this->jsonResponse('Unauthorized.');
        }

        $authProvider = $this->getProvider();
        if ($authProvider === false) {
            $this->jsonResponse($this->error);
        }

        $optionName   = 'wpstg_' . $authProvider->getIdentifier();
        $result       = update_option($optionName, [], false);
        $providerName = $this->providers->getStorageProperty($this->provider, 'name', true);
        // Clean up stored credential files (SSH keys, certificates) after clearing options.
        $authProvider->revoke();
        if ($result) {
            $this->jsonResponse("Storage provider {$providerName} successfully removed.", true);
        }

        $this->jsonResponse("Failed to delete settings for " . $providerName);
    }

    /**
     * Remove a single stored credential (key or certificate) from the provider options.
     */
    public function removeCredential()
    {
        if (!$this->wpstagingAuth->isAuthenticatedRequest()) {
            $this->jsonResponse('Authentication failed.');
        }

        $authProvider = $this->getProvider();
        if ($authProvider === false) {
            $this->jsonResponse($this->error);
        }

        $field = isset($_POST['field']) ? sanitize_text_field($_POST['field']) : '';
        if (empty($field) || !in_array($field, ['key', 'ftpCertContent'])) {
            $this->jsonResponse('Invalid field.');
        }

        $optionName = 'wpstg_' . $authProvider->getIdentifier();
        $options    = get_option($optionName, []);
        if (is_array($options) && array_key_exists($field, $options)) {
            $options[$field] = '';
            $updated = update_option($optionName, $options, false);
            if (!$updated) {
                $this->jsonResponse('Failed to remove credential. Could not update the storage options.');
            }
        }

        $this->jsonResponse('Credential removed successfully.', true);
    }

    /**
     * @param int $updatedAt
     * @return string
     */
    private function getFormattedProviderUpdatedAt($updatedAt): string
    {
        $timestamp = is_numeric($updatedAt) && $updatedAt > 0 ? (int) $updatedAt : 0;
        $date      = (new \DateTime())->setTimestamp($timestamp);

        /** @var DateTimeAdapter $dateTimeAdapter */
        $dateTimeAdapter = WPStaging::make(DateTimeAdapter::class);
        return $dateTimeAdapter->transformToWpFormat($date);
    }
}
