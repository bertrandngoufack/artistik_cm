<?php

namespace WPStaging\Pro\Automations\PluginsUpdater\Job;

use WPStaging\Framework\Job\AbstractJob;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Pro\Automations\PluginsUpdater\Dto\JobPluginsUpdaterDataDto;
use WPStaging\Pro\Automations\PluginsUpdater\Tasks\FinishPluginsUpdaterTask;
use WPStaging\Pro\Automations\PluginsUpdater\Tasks\PluginsActivationTask;
use WPStaging\Pro\Automations\PluginsUpdater\Tasks\StartPluginsUpdaterTask;
use WPStaging\Pro\Automations\PluginsUpdater\Tasks\StagingPluginsUpdaterTask;

class JobPluginsUpdater extends AbstractJob
{
    /**
     * @var JobPluginsUpdaterDataDto
     */
    protected $jobDataDto;

    /**
     * @var array
     */
    protected $tasks = [];

    /**
     * @return string
     */
    public static function getJobName(): string
    {
        return 'staging_plugins_updater';
    }

    /**
     * @return array
     */
    protected function getJobTasks(): array
    {
        return $this->tasks;
    }

    /**
     * @return TaskResponseDto
     */
    protected function execute(): TaskResponseDto
    {
        try {
            $response = $this->getResponse($this->currentTask->execute());
        } catch (\Exception $e) {
            $this->currentTask->getLogger()->critical('Plugins auto updater job failed! Error: ' . $e->getMessage());
            $response = $this->getResponse($this->currentTask->generateResponse(false));
        }

        return $response;
    }

    /**
     * @return void
     */
    protected function init()
    {
        $this->addPluginsUpdaterTasks();
    }

    /**
     * @return void
     */
    protected function addPluginsUpdaterTasks()
    {
        $this->tasks[] = StartPluginsUpdaterTask::class;
        $this->tasks[] = StagingPluginsUpdaterTask::class;
        $this->tasks[] = PluginsActivationTask::class;
        $this->tasks[] = FinishPluginsUpdaterTask::class;
    }
}
