<?php

namespace WPStaging\Pro\RemoteSync;

use Exception;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Job\JobTransientCache;
use WPStaging\Framework\Logger\SseEventCache;

class SyncSession
{
    /**
     * @var string
     */
    const TRANSIENT_SESSION = 'wpstg_remote_sync_session';

    /**
     * @var string
     */
    const TRANSIENT_SESSION_DATA = 'wpstg_remote_sync_session_data';

    /**
     * Until started we set the session expiry to 3 minutes.
     * @var int
     */
    const SESSION_EXPIRY = 60 * 3; // 3 minutes

    /**
     * It is very unlikely that a sync process requires more than 6 hours.
     * @var int
     */
    const SESSION_EXPIRY_RUNNING = 60 * 60 * 6; // 6 hours

    /**
     * @var string
     */
    const TRANSIENT_SESSION_EVENTS_OFFSET = 'wpstg_remote_sync_session_events_offset';

    /**
     * @var int
     */
    const TRANSIENT_SESSION_EVENTS_OFFSET_EXPIRY = 60 * 5; // 5 minutes

    /**
     * @var string
     */
    const SYNC_TYPE_PULL = 'pull';

    /**
     * @var string
     */
    const PROGRESS_STATUS_AUTHENTICATED = 'authenticated';

    /**
     * @var string
     */
    const PROGRESS_STATUS_INITIATED = 'initiated';

    /**
     * @var string
     */
    const PROGRESS_STATUS_STARTED = 'started';

    /**
     * @var string
     */
    const PROGRESS_STATUS_PREPARED = 'prepared';

    /**
     * @var bool
     */
    const INITIATOR = true;

    /**
     * @var string
     */
    private $jobId = '';

    /**
     * @var string
     */
    private $type = '';

    /**
     * @var string
     */
    private $pushSiteUrl = '';

    /**
     * @var string
     */
    private $pullSiteUrl = '';

    /**
     * @var string
     */
    private $token = '';

    /**
     * @var bool
     */
    private $isInitiator = false;

    /**
     * @var bool
     */
    private $isTwoWaySync = false;

    /**
     * What is current status of the sync process,
     * Useful in one-way sync
     * @var string
     */
    private $progressStatus = '';

    /**
     * @var array
     */
    private $data = [];

    /**
     * @param string $jobId
     * @param string $type
     * @param string $token
     * @param string $pushSiteUrl
     * @param string $pullSiteUrl
     */
    public function __construct(string $jobId = '', string $type = '', string $token = '', string $pullSiteUrl = '', string $pushSiteUrl = '')
    {
        if (empty($jobId) && empty($type) && empty($token) && empty($pullSiteUrl) && empty($pushSiteUrl)) {
            try {
                $this->fromString(get_transient(self::TRANSIENT_SESSION));
            } catch (Exception $e) {
            }

            return;
        }

        $this->jobId          = $jobId;
        $this->type           = $type;
        $this->token          = $token;
        $this->pullSiteUrl    = $pullSiteUrl;
        $this->pushSiteUrl    = $pushSiteUrl;
        $this->isTwoWaySync   = false;
        $this->isInitiator    = false;
        $this->progressStatus = '';
    }

    /**
     * @return string
     */
    public function getJobId(): string
    {
        return $this->jobId;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getPushSiteUrl(): string
    {
        return $this->pushSiteUrl;
    }

    /**
     * @return string
     */
    public function getPullSiteUrl(): string
    {
        return $this->pullSiteUrl;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getProgressStatus(): string
    {
        return $this->progressStatus;
    }

    public function isTwoWaySync(): bool
    {
        return $this->isTwoWaySync;
    }

    public function isInitiator(): bool
    {
        return $this->isInitiator;
    }

    public function toString(): string
    {
        $data = [
            'jobId'          => $this->jobId,
            'type'           => $this->type,
            'pushSiteUrl'    => $this->pushSiteUrl,
            'pullSiteUrl'    => $this->pullSiteUrl,
            'token'          => $this->token,
            'isTwoWaySync'   => $this->isTwoWaySync,
            'isInitiator'    => $this->isInitiator,
            'progressStatus' => $this->progressStatus,
        ];

        return base64_encode(json_encode($data));
    }

    public function isRunning(): bool
    {
        return !empty($this->jobId) && !empty($this->token) && !empty($this->pushSiteUrl) && !empty($this->pullSiteUrl);
    }

    /**
     * @param bool $isInitiator
     * @return bool
     */
    public function authenticate(bool $isInitiator = false): bool
    {
        $this->isInitiator = $isInitiator;
        $this->progressStatus = self::PROGRESS_STATUS_AUTHENTICATED;
        return set_transient(self::TRANSIENT_SESSION, $this->toString(), self::SESSION_EXPIRY);
    }

    /**
     * @return bool
     */
    public function initiate(): bool
    {
        $this->progressStatus = self::PROGRESS_STATUS_INITIATED;
        return set_transient(self::TRANSIENT_SESSION, $this->toString(), self::SESSION_EXPIRY);
    }

    public function start(): bool
    {
        $this->progressStatus = self::PROGRESS_STATUS_STARTED;
        return set_transient(self::TRANSIENT_SESSION, $this->toString(), self::SESSION_EXPIRY_RUNNING);
    }

    public function stop(): bool
    {
        delete_transient(self::TRANSIENT_SESSION_DATA);
        return delete_transient(self::TRANSIENT_SESSION);
    }

    public function setTwoWaySync(bool $isTwoWaySync): bool
    {
        $this->isTwoWaySync = $isTwoWaySync;
        return $this->authenticate($this->isInitiator);
    }

    public function setProgressStatus(string $status): bool
    {
        $this->progressStatus = $status;
        return set_transient(self::TRANSIENT_SESSION, $this->toString(), self::SESSION_EXPIRY_RUNNING);
    }

    public function setData(string $key, array $value): bool
    {
        $this->data = get_transient(self::TRANSIENT_SESSION_DATA);
        if (empty($this->data)) {
            $this->data = [];
        }

        $this->data[$key] = $value;

        return set_transient(self::TRANSIENT_SESSION_DATA, $this->data, self::SESSION_EXPIRY_RUNNING);
    }

    public function getData(string $key): array
    {
        $this->data = get_transient(self::TRANSIENT_SESSION_DATA);
        if (empty($this->data)) {
            return [];
        }

        if (!isset($this->data[$key])) {
            return [];
        }

        return $this->data[$key];
    }

    /**
     * @param string $encodedData
     * @return void
     */
    public function fromString(string $encodedData)
    {
        $decodedString = base64_decode($encodedData, true);
        if ($decodedString === false || empty($decodedString)) {
            throw new \InvalidArgumentException('Invalid session format.');
        }

        $parts = json_decode($decodedString);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid session format.');
        }

        if (!isset($parts->pushSiteUrl) || !isset($parts->pullSiteUrl) || !isset($parts->token) || !isset($parts->jobId) || !isset($parts->type)) {
            throw new \InvalidArgumentException('Invalid session format.');
        }

        $this->jobId          = $parts->jobId;
        $this->type           = $parts->type;
        $this->pushSiteUrl    = $parts->pushSiteUrl;
        $this->pullSiteUrl    = $parts->pullSiteUrl;
        $this->token          = $parts->token;
        $this->isTwoWaySync   = $parts->isTwoWaySync ?? false;
        $this->isInitiator    = $parts->isInitiator ?? false;
        $this->progressStatus = $parts->progressStatus ?? '';
    }

    /**
     * @param string $token
     * @return bool
     */
    public function validateSessionToken(string $token): bool
    {
        if (empty($this->token) || empty($token)) {
            return false;
        }

        return hash_equals($this->token, $token);
    }

    /**
     * @param string $encodedData
     * @return SyncSession
     */
    public static function parse(string $encodedData)
    {
        $self = new self();
        $self->fromString($encodedData);

        return $self;
    }

    /**
     * @return array
     */
    public function getEvents(): array
    {
        $offset = get_transient(self::TRANSIENT_SESSION_EVENTS_OFFSET);
        if (empty($offset)) {
            $offset = 0;
        }

        /**
         * lazy load the SseEventCache
         * @var SseEventCache $sseEventCache
         */
        $sseEventCache = WPStaging::make(SseEventCache::class);
        $sseEventCache->setJobId($this->getJobId());
        $sseEventCache->load();
        set_transient(self::TRANSIENT_SESSION_EVENTS_OFFSET, $sseEventCache->getCount(), self::TRANSIENT_SESSION_EVENTS_OFFSET_EXPIRY);

        return $sseEventCache->getEvents($offset);
    }

    /**
     * @return string
     */
    public function getRemoteUrl(): string
    {
        if ($this->isInitiator) {
            return $this->pushSiteUrl;
        }

        return $this->pullSiteUrl;
    }

    /**
     * Check if any sync job is currently running
     * @return bool
     */
    public static function isAnySyncRunning(): bool
    {
        $jobTransientCache = WPStaging::make(JobTransientCache::class);
        return $jobTransientCache->getJobStatus() === JobTransientCache::STATUS_RUNNING;
    }
}
