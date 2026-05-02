<?php

namespace WPStaging\Pro\Automations\PluginsUpdater\Dto;

use WPStaging\Framework\Job\Dto\JobDataDto;

class JobPluginsUpdaterDataDto extends JobDataDto
{
    /**
     * @var bool
     */
    private $isAutoUpdatePlugins = false;

    /**
     * @var string
     */
    private $stagingUrl = '';

    /**
     * @var array
     */
    private $outdatedPlugins = [];

    /**
     * @var array
     */
    private $apiResponse = [];

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var bool
     */
    private $pluginRetry = false;

    /**
     * @var bool
     */
    private $isNetworkClone = false;

    /**
     * @var int
     */
    private $networkUpdateRetries = 0;

    /**
     * @var string
     */
    private $authToken = '';

    /**
     * @var string
     */
    private $cloneId = '';

    /**
     * @param bool $isAutoUpdatePlugins
     * @return void
     */
    public function setIsAutoUpdatePlugins(bool $isAutoUpdatePlugins)
    {
        $this->isAutoUpdatePlugins = $isAutoUpdatePlugins;
    }

    /**
     * @return bool
     */
    public function getIsAutoUpdatePlugins(): bool
    {
        return $this->isAutoUpdatePlugins;
    }

    /**
     * @param string $stagingUrl
     * @return void
     */
    public function setStagingUrl(string $stagingUrl)
    {
        $this->stagingUrl = $stagingUrl;
    }

    /**
     * @return string
     */
    public function getStagingUrl(): string
    {
        return $this->stagingUrl;
    }

    /**
     * @param array $outdatedPlugins
     * @return void
     */
    public function setOutdatedPlugins(array $outdatedPlugins)
    {
        $this->outdatedPlugins = $outdatedPlugins;
    }

    /**
     * @return array
     */
    public function getOutdatedPlugins(): array
    {
        return $this->outdatedPlugins;
    }

    /**
     * @param array $apiResponse
     * @return void
     */
    public function setApiResponse(array $apiResponse)
    {
        $this->apiResponse = $apiResponse;
    }

    /**
     * @return array
     */
    public function getApiResponse(): array
    {
        return $this->apiResponse;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param bool $retry
     * @return void
     */
    public function setPluginRetry(bool $retry = false)
    {
        $this->pluginRetry = $retry;
    }

    /**
     * @return bool
     */
    public function getPluginRetry(): bool
    {
        return $this->pluginRetry;
    }

    /**
     * @param int $pluginIndex
     * @return array
     */
    public function getPluginToUpdate(int $pluginIndex = 0): array
    {
        if (array_key_exists($pluginIndex, $this->outdatedPlugins)) {
            return $this->outdatedPlugins[$pluginIndex];
        }

        return [];
    }

    /**
     * @param bool $isNetworkClone
     * @return void
     */
    public function setIsNetworkClone(bool $isNetworkClone = false)
    {
        $this->isNetworkClone = $isNetworkClone;
    }

    /**
     * @return bool
     */
    public function getIsNetworkClone(): bool
    {
        return $this->isNetworkClone;
    }

    /**
     * @param int $networkUpdateRetries
     * @return void
     */
    public function setNetworkUpdateRetries(int $networkUpdateRetries)
    {
        $this->networkUpdateRetries = $networkUpdateRetries;
    }

    /**
     * @return int
     */
    public function getNetworkUpdateRetries(): int
    {
        return $this->networkUpdateRetries;
    }

    /**
     * @param string $authToken
     * @return void
     */
    public function setAuthToken(string $authToken)
    {
        $this->authToken = $authToken;
    }

    /**
     * @return string
     */
    public function getAuthToken(): string
    {
        return $this->authToken;
    }

    /**
     * @param string $cloneId
     * @return void
     */
    public function setCloneId(string $cloneId)
    {
        $this->cloneId = $cloneId;
    }

    /**
     * @return string
     */
    public function getCloneId(): string
    {
        return $this->cloneId;
    }
}
