<?php

namespace WPStaging\Pro\Push\Tasks;

use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Push\Service\FileCopier;
use WPStaging\Pro\Push\Service\PushFileDeployer;
use WPStaging\Staging\Dto\Task\FileCopierTaskDto;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

abstract class FileCopierTask extends PushTask
{
    /** @var FileCopier */
    protected $fileCopier;

    /** @var PushFileDeployer */
    protected $deployer;

    /** @var FileCopierTaskDto */
    protected $currentTaskDto;

    /** @var bool */
    protected $isTmpPath = false;

    public function __construct(FileCopier $fileCopier, PushFileDeployer $deployer, LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
        $this->fileCopier = $fileCopier;
        $this->deployer   = $deployer;
    }

    public static function getTaskName(): string
    {
        return 'push_file_copier_task';
    }

    public static function getTaskTitle(): string
    {
        return 'Copying Files from Staging Site';
    }

    public function execute(): TaskResponseDto
    {
        // Early bail if the task is excluded
        if ($this->getIsExcluded()) {
            return $this->generateResponse(true);
        }

        $this->prepareFileCopyingTask();
        // If no file let's skip this task
        if ($this->stepsDto->getTotal() === 0) {
            return $this->generateResponse(true);
        }

        $this->fileCopier->execute();

        $this->currentTaskDto->setBigFileDto($this->fileCopier->getBigFileDto());
        $this->setCurrentTaskDto($this->currentTaskDto);

        if ($this->stepsDto->isFinished()) {
            $this->deployItems();

            return $this->generateResponse(true);
        }

        return $this->generateResponse(false);
    }

    /** @return string */
    abstract protected function getFileIdentifier(): string;

    /** @return bool */
    protected function getIsExcluded(): bool
    {
        return false;
    }

    /** @return string */
    protected function getCurrentTaskType(): string
    {
        return FileCopierTaskDto::class;
    }

    /** @return void */
    protected function maybeTmpCleanup()
    {
        if ($this->isTmpPath) {
            $this->deployer->cleanupTmpDirectory();
        }
    }

    /** @return void */
    protected function deployItems()
    {
        if (!$this->isTmpPath) {
            return;
        }

        $this->deployer->deployItems();
        $errors = $this->deployer->getErrors();
        foreach ($errors as $error) {
            $this->logger->warning($error);
        }
    }

    /**
     * @return void
     */
    private function prepareFileCopyingTask()
    {
        $this->fileCopier->inject($this->taskQueue, $this->logger, $this->stepsDto);
        $this->fileCopier->setupBigFileBeingCopied($this->currentTaskDto->getBigFileDto());
        $this->fileCopier->setIsTmpPath($this->isTmpPath);
        $this->fileCopier->setup($this->jobDataDto->getStagingSitePath(), $this->getFileIdentifier(), true);
        $this->deployer->setFileIdentifier($this->getFileIdentifier());
        $this->deployer->setPreserveExistingItems(!$this->jobDataDto->getIsCleanPluginsThemes());
        if ($this->stepsDto->getTotal() > 0) {
            return;
        }

        $this->maybeTmpCleanup();
        $this->stepsDto->setTotal($this->jobDataDto->getDiscoveredFilesByIdentifier($this->getFileIdentifier()));
    }
}
