<?php

namespace WPStaging\Pro\Analytics\Actions;

use WPStaging\Framework\Analytics\AnalyticsEventWithTimeDto;

class AnalyticsRemoteSync extends AnalyticsEventWithTimeDto
{
    /** @var bool */
    public $is_sync_database;

    /** @var bool */
    public $is_sync_plugins;

    /** @var bool */
    public $is_sync_themes;

    /** @var bool */
    public $is_sync_uploads;

    /** @var bool */
    public $is_sync_muplugins;

    /** @var bool */
    public $is_sync_wp_content;

    /** @var string pull|push */
    public $sync_type;

    /** @var string */
    public $remote_url = '';

    /** @var bool */
    public $is_two_way_sync;

    /** @var bool */
    public $is_initiator;

    public function getEventAction()
    {
        return 'event_remote_sync';
    }

    /**
     * @param string $jobId
     * @param array|object $eventData
     * @return void
     */
    public function enqueueStartEvent($jobId, $eventData)
    {
        // Accept array-like input coming from Remote Sync flow
        $data = is_array($eventData) ? $eventData : (array)$eventData;

        $this->is_sync_database      = !empty($data['isSyncDatabase']);
        $this->is_sync_plugins       = !empty($data['isSyncPlugins']);
        $this->is_sync_themes        = !empty($data['isSyncThemes']);
        $this->is_sync_uploads       = !empty($data['isSyncUploads']);
        $this->is_sync_muplugins     = !empty($data['isSyncMuPlugins']);
        $this->is_sync_wp_content    = !empty($data['isSyncOtherContent']);
        $this->is_two_way_sync       = !empty($data['isTwoWaySync']);
        $this->is_initiator          = !empty($data['isInitiator']);
        $this->sync_type             = isset($data['syncType']) ? (string)$data['syncType'] : 'pull';
        $this->remote_url            = isset($data['remoteUrl']) ? (string)$data['remoteUrl'] : '';

        parent::enqueueStartEvent($jobId, $eventData);
    }
}
