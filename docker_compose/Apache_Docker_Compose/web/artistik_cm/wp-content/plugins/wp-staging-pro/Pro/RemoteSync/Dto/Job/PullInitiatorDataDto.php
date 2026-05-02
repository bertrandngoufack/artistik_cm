<?php

namespace WPStaging\Pro\RemoteSync\Dto\Job;

use WPStaging\Backup\Dto\Job\JobRestoreDataDto;

class PullInitiatorDataDto extends JobRestoreDataDto
{
    /** @var bool */
    protected $isInitiator = true;

    /** @var bool */
    protected $isSyncUploads = false;

    /** @var bool */
    protected $isSyncPlugins = false;

    /** @var bool */
    protected $isSyncThemes = false;

    /** @var bool */
    protected $isSyncDatabase = false;

    /** @var bool */
    protected $isSyncMuPlugins = false;

    /** @var bool */
    protected $isSyncOtherContent = false;

    /** @var bool */
    protected $isTwoWaySync = false;

    /** @var string */
    protected $syncType = 'pull';

    /** @var string */
    protected $remoteUrl = '';

    /** @var string */
    protected $dataUrl = '';

    /** @var string */
    protected $httpAuthUsername = '';

    /** @var string */
    protected $httpAuthPassword = '';

    public function getIsInitiator(): bool
    {
        return $this->isInitiator;
    }

    /**
     * @param bool $isInitiator
     * @return void
     */
    public function setIsInitiator(bool $isInitiator)
    {
        $this->isInitiator = $isInitiator;
    }

    /**
     * @return bool
     */
    public function getIsSyncUploads(): bool
    {
        return $this->isSyncUploads;
    }

    /**
     * @param bool $isSyncUploads
     * @return void
     */
    public function setIsSyncUploads(bool $isSyncUploads)
    {
        $this->isSyncUploads = $isSyncUploads;
    }

    /**
     * @return bool
     */
    public function getIsSyncPlugins(): bool
    {
        return $this->isSyncPlugins;
    }

    /**
     * @param bool $isSyncPlugins
     * @return void
     */
    public function setIsSyncPlugins(bool $isSyncPlugins)
    {
        $this->isSyncPlugins = $isSyncPlugins;
    }

    /**
     * @return bool
     */
    public function getIsSyncThemes(): bool
    {
        return $this->isSyncThemes;
    }

    /**
     * @param bool $isSyncThemes
     * @return void
     */
    public function setIsSyncThemes(bool $isSyncThemes)
    {
        $this->isSyncThemes = $isSyncThemes;
    }

    /**
     * @return bool
     */
    public function getIsSyncDatabase(): bool
    {
        return $this->isSyncDatabase;
    }

    /**
     * @param bool $isSyncDatabase
     * @return void
     */
    public function setIsSyncDatabase(bool $isSyncDatabase)
    {
        $this->isSyncDatabase = $isSyncDatabase;
    }

    /**
     * @return bool
     */
    public function getIsSyncMuPlugins(): bool
    {
        return $this->isSyncMuPlugins;
    }

    /**
     * @param bool $isSyncMuPlugins
     * @return void
     */
    public function setIsSyncMuPlugins(bool $isSyncMuPlugins)
    {
        $this->isSyncMuPlugins = $isSyncMuPlugins;
    }

    /**
     * @return bool
     */
    public function getIsSyncOtherContent(): bool
    {
        return $this->isSyncOtherContent;
    }

    /**
     * @param bool $isSyncOtherContent
     * @return void
     */
    public function setIsSyncOtherContent(bool $isSyncOtherContent)
    {
        $this->isSyncOtherContent = $isSyncOtherContent;
    }

    /**
     * @return bool
     */
    public function getIsTwoWaySync(): bool
    {
        return $this->isTwoWaySync;
    }

    /**
     * @param bool $isTwoWaySync
     * @return void
     */
    public function setIsTwoWaySync(bool $isTwoWaySync)
    {
        $this->isTwoWaySync = $isTwoWaySync;
    }

    public function getSyncType(): string
    {
        return $this->syncType;
    }

    public function setSyncType(string $syncType)
    {
        $this->syncType = $syncType;
    }

    /**
     * @return string
     */
    public function getRemoteUrl(): string
    {
        return $this->remoteUrl;
    }

    /**
     * @param string $remoteUrl
     * @return void
     */
    public function setRemoteUrl(string $remoteUrl)
    {
        $this->remoteUrl = $remoteUrl;
    }

    /**
     * @return string
     */
    public function getDataUrl(): string
    {
        return $this->dataUrl;
    }

    /**
     * @param string $dataUrl
     * @return void
     */
    public function setDataUrl(string $dataUrl)
    {
        $this->dataUrl = $dataUrl;
    }

    public function getHttpAuthUsername(): string
    {
        return $this->httpAuthUsername;
    }

    /**
     * @param string $httpAuthUsername
     * @return void
     */
    public function setHttpAuthUsername(string $httpAuthUsername)
    {
        $this->httpAuthUsername = $httpAuthUsername;
    }

    public function getHttpAuthPassword(): string
    {
        return $this->httpAuthPassword;
    }

    /**
     * @param string $httpAuthPassword
     * @return void
     */
    public function setHttpAuthPassword(string $httpAuthPassword)
    {
        $this->httpAuthPassword = $httpAuthPassword;
    }
}
