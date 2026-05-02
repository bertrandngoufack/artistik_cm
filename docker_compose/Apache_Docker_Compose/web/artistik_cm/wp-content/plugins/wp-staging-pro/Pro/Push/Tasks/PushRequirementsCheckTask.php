<?php

namespace WPStaging\Pro\Push\Tasks;

use RuntimeException;
use WPStaging\Backend\Modules\SystemInfo;
use WPStaging\Framework\Adapter\Database;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Analytics\Actions\AnalyticsStagingPush;
use WPStaging\Framework\Filesystem\DiskWriteCheck;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Framework\Job\Exception\DiskNotWritableException;
use WPStaging\Staging\Sites;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

class PushRequirementsCheckTask extends PushTask
{
    /** @var Directory */
    protected $directory;

    /** @var Database */
    protected $database;

    /** @var Filesystem */
    protected $filesystem;

    /** @var DiskWriteCheck */
    protected $diskWriteCheck;

    /** @var AnalyticsStagingPush */
    protected $analyticsStagingPush;

    /** @var SystemInfo */
    protected $systemInfo;

    /** @var Sites */
    protected $sites;

    public function __construct(
        Directory $directory,
        Database $database,
        Filesystem $filesystem,
        LoggerInterface $logger,
        Cache $cache,
        StepsDto $stepsDto,
        SeekableQueueInterface $taskQueue,
        DiskWriteCheck $diskWriteCheck,
        AnalyticsStagingPush $analyticsStagingPush,
        SystemInfo $systemInfo,
        Sites $sites
    ) {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
        $this->directory            = $directory;
        $this->filesystem           = $filesystem;
        $this->diskWriteCheck       = $diskWriteCheck;
        $this->analyticsStagingPush = $analyticsStagingPush;
        $this->systemInfo           = $systemInfo;
        $this->database             = $database;
        $this->sites                = $sites;
    }

    public static function getTaskName(): string
    {
        return 'push_requirements_check';
    }

    public static function getTaskTitle(): string
    {
        return 'Requirements Check';
    }

    public function execute(): TaskResponseDto
    {
        if (!$this->stepsDto->getTotal()) {
            $this->stepsDto->setTotal(1);
        }

        try {
            $this->logStartHeader();
            $this->logger->writeLogHeader();
            $this->logger->writeInstalledPluginsAndThemes();
            $this->writeStagingSettingsLogs();
            $this->cannotPushIfCantWriteToDisk();
            $this->cannotPushIfStagingSiteNoExists();
        } catch (RuntimeException $e) {
            $this->analyticsStagingPush->enqueueFinishEvent($this->jobDataDto->getId(), $this->jobDataDto);
            $this->logger->critical($e->getMessage());

            return $this->generateResponse(false);
        }

        $this->logRequirementsCheckPassed();

        return $this->generateResponse();
    }

    /**
     * @return void
     */
    protected function logStartHeader()
    {
        $this->logger->info('#################### Start Staging Site Push Job ####################');
    }

    /**
     * @return void
     */
    protected function logRequirementsCheckPassed()
    {
        $this->logger->info('Staging Site push requirements passed...');
    }

    /**
     * @return void
     * @throws RuntimeException
     */
    protected function cannotPushIfCantWriteToDisk()
    {
        try {
            $this->diskWriteCheck->testDiskIsWriteable();
        } catch (DiskNotWritableException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    /**
     * @return void
     * @throws RuntimeException
     */
    protected function cannotPushIfStagingSiteNoExists()
    {
        $stagingSitePath = $this->jobDataDto->getStagingSitePath();
        if (!is_dir($stagingSitePath)) {
            throw new RuntimeException(esc_html__('Cannot push staging site. Staging site directory does not exist!', 'wp-staging'));
        }
    }

    /**
     * @return void
     */
    protected function writeStagingSettingsLogs()
    {
        $this->logger->info('Staging Settings:');
        $this->logger->info('Staging Site Path: ' . $this->jobDataDto->getStagingSitePath());
        $this->logger->info('Staging Site URL: ' . $this->jobDataDto->getStagingSiteUrl());
        $this->logger->info('Database Prefix: ' . $this->jobDataDto->getDatabasePrefix());
        $this->logger->info('Clone ID: ' . $this->jobDataDto->getCloneId());
        $this->logger->info('Clone Name: ' . $this->jobDataDto->getStagingSite()->getCloneName());
        $this->logger->info('Is External Database: ' . ($this->jobDataDto->getIsExternalDatabase() ? 'Yes' : 'No'));
    }
}
