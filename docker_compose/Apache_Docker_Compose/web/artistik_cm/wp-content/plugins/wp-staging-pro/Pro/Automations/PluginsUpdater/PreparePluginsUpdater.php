<?php

namespace WPStaging\Pro\Automations\PluginsUpdater;

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Job\Ajax\PrepareJob;
use WPStaging\Framework\Job\Exception\ProcessLockedException;
use WPStaging\Framework\Job\JobTransientCache;
use WPStaging\Pro\Automations\PluginsUpdater\Dto\JobPluginsUpdaterDataDto;
use WPStaging\Pro\Automations\PluginsUpdater\Job\JobPluginsUpdater;

class PreparePluginsUpdater extends PrepareJob
{
    /**
     * @var JobPluginsUpdaterDataDto
     */
    private $jobDataDto;

    /**
     * @var JobPluginsUpdater
     */
    private $jobPluginsUpdater;

    /**
     * @param $data
     * @return void
     */
    public function ajaxPrepare($data)
    {
        try {
            $this->processLock->checkProcessLocked();
        } catch (ProcessLockedException $e) {
            wp_send_json_error($e->getMessage(), $e->getCode());
        }

        $response = $this->prepare($data);

        if ($response instanceof \WP_Error) {
            wp_send_json_error($response->get_error_message(), $response->get_error_code());
        } else {
            wp_send_json_success();
        }
    }

    /**
     * @param $data
     * @return array|\WP_Error
     */
    public function prepare($data = null)
    {
        try {
            $sanitizedData = $this->setupInitialData($data);
        } catch (\Exception $e) {
            return new \WP_Error(400, $e->getMessage());
        }

        return $sanitizedData;
    }

    /**
     * @param $sanitizedData
     * @return array
     */
    private function setupInitialData($sanitizedData): array
    {
        $sanitizedData = $this->validateAndSanitizeData($sanitizedData);
        $this->clearCacheFolder();
        // Lazy-instantiation to avoid process-lock checks conflicting with running processes.
        $services = WPStaging::getInstance()->getContainer();
        /** @var JobPluginsUpdaterDataDto */
        $this->jobDataDto = $services->get(JobPluginsUpdaterDataDto::class);
        /** @var JobPluginsUpdater */
        $this->jobPluginsUpdater = $services->get(JobPluginsUpdater::class);

        $this->jobDataDto->hydrate($sanitizedData);
        $this->jobDataDto->setInit(true);
        $this->jobDataDto->setFinished(false);
        $this->jobDataDto->setStartTime(time());
        $this->jobDataDto->setId(substr(md5(mt_rand() . time()), 0, 12));

        $this->jobPluginsUpdater->setJobDataDto($this->jobDataDto);
        $this->jobPluginsUpdater->getTransientCache()->startJob($this->jobDataDto->getId(), esc_html__('Auto Updating Staging Site Plugins', 'wp-staging'), JobTransientCache::JOB_TYPE_PLUGINS_UPDATER);

        return $sanitizedData;
    }

    /**
     * @param $data
     * @return array
     */
    public function validateAndSanitizeData($data): array
    {
        // Unset any empty value so that we replace them with the defaults.
        foreach ($data as $key => $value) {
            if (empty($value)) {
                unset($data[$key]);
            }
        }

        $defaults = [
            'isAutoUpdatePlugins' => false,
            'stagingUrl'          => '',
            'outdatedPlugins'     => [],
            'isWpCliRequest'      => true,
            'isNetworkClone'      => false,
            'name'                => 'wp_plugins_auto_updater',
            'authToken'           => '',
            'cloneId'             => '',
        ];

        $data = wp_parse_args($data, $defaults);

        // Make sure data has no keys other than the expected ones.
        $data = array_intersect_key($data, $defaults);

        // Make sure data has all expected keys.
        foreach ($defaults as $expectedKey => $value) {
            if (!array_key_exists($expectedKey, $data)) {
                throw new \UnexpectedValueException("Invalid request. Missing '$expectedKey'.");
            }
        }

        $data['isAutoUpdatePlugins']  = $this->jsBoolean($data['isAutoUpdatePlugins']);
        return $data;
    }

    /**
     * Returns the reference to the current PluginUpdater Job, if any.
     *
     * @return JobPluginsUpdater|null The current reference to the PluginUpdater Job, if any.
     */
    public function getJob()
    {
        return $this->jobPluginsUpdater;
    }

    /**
     * Persists the current Job PluginUpdater status.
     *
     * @return bool Whether the current Job status was persisted or not.
     */
    public function persist(): bool
    {
        if (!$this->jobPluginsUpdater instanceof JobPluginsUpdater) {
            return false;
        }

        $this->jobPluginsUpdater->persist();
        return true;
    }
}
