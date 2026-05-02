<?php

namespace WPStaging\Pro\RemoteSync;

use Exception;
use WPStaging\Core\Utils\Logger;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Logger\SseEventCache;
use WPStaging\Pro\RemoteSync\BackgroundProcessing\PreparePull;

trait WithStartPullMethods
{
    protected function startPull(array $params): string
    {
        try {
            $syncSession = new SyncSession();
            $syncSession->setProgressStatus(SyncSession::PROGRESS_STATUS_PREPARED);

            $jobId        = $syncSession->getJobId();
            $params['id'] = $jobId;

            $this->pushEvents($jobId, $params['events'] ?? []);
            $data  = $this->setupPullOptions($params);
            $jobId = WPStaging::make(PreparePull::class)->prepare($data);

            if ($jobId instanceof \WP_Error) {
                throw new Exception('Failed to start pull data: ' . $jobId->get_error_message());
            }

            return $jobId;
        } catch (Exception $e) {
            throw new Exception('Exception thrown while starting the pull: ' . $e->getMessage());
        }
    }

    protected function setupPullOptions(array $options): array
    {
        if (!isset($options['dataUrl'])) {
            throw new Exception('Missing data archive');
        }

        $data = [];
        $data['id']                = $options['id'] ?? null;
        $data['dataUrl']           = $options['dataUrl'];
        $data['isRestRequest']     = true;
        $data['isSyncRequest']     = true;
        $data['isInit']            = true;
        $data['file']              = '';
        $data['tmpDatabasePrefix'] = '';

        return $data;
    }

    /**
     * @param string $jobId
     * @param array $events
     * @return void
     */
    protected function pushEvents(string $jobId, array $events)
    {
        /**
         * lazy load the Logger
         * @var Logger $logger
         */
        $logger = WPStaging::make(Logger::class);
        $logger->setupSseLogger($jobId);

        foreach ($events as $event) {
            $type = $event['type'] ?? '';
            if (empty($type)) {
                continue;
            }

            if (in_array($type, [SseEventCache::EVENT_TYPE_TASK, SseEventCache::EVENT_TYPE_COMPLETE])) {
                $logger->pushSseEvent($type, $event['data']);
                continue;
            }

            $logger->add($event['message'], $type);
        }
    }
}
