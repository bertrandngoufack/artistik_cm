<?php

namespace WPStaging\Pro\Push\Tasks\Database\Adjustment;

use WPStaging\Framework\Adapter\Database;
use WPStaging\Framework\Database\TableService;
use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\SiteInfo;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Framework\Utils\Urls;
use WPStaging\Pro\Push\Tasks\OptionAdjustmentTask;
use WPStaging\Staging\Sites;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

/**
 * This class is responsible for removing options during staging site push.
 */
class RemoveOptionsTask extends OptionAdjustmentTask
{
    /** @var SiteInfo */
    protected $siteInfo;

    /**
     * @param LoggerInterface $logger
     * @param Cache $cache
     * @param StepsDto $stepsDto
     * @param SeekableQueueInterface $taskQueue
     * @param Urls $urls
     * @param Database $database
     * @param TableService $tableService
     * @param SiteInfo $siteInfo
     */
    public function __construct(LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue, Urls $urls, Database $database, TableService $tableService, SiteInfo $siteInfo)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue, $urls, $database, $tableService);
        $this->siteInfo = $siteInfo;
    }

    public static function getTaskName(): string
    {
        return 'push_remove_options';
    }

    public static function getTaskTitle(): string
    {
        return 'Remove staging site related options';
    }

    public function adjustOptionsTable(): TaskResponseDto
    {
        $this->logger->info("Remove staging site specific options from $this->tmpOptionsTable");

        if ($this->siteInfo->isStagingSite()) {
            $this->logger->info("Current site is a staging site. Skipping removing staging site specific options.");
            return $this->generateResponse();
        }

        $sql = $this->wpdb->prepare(
            "DELETE FROM `$this->tmpOptionsTable` WHERE `option_name` = %s;\n",
            'wpstg_connection'
        );

        $sql .= $this->wpdb->prepare(
            "DELETE FROM `$this->tmpOptionsTable` WHERE `option_name` = %s;\n",
            'wpstg_emails_disabled'
        );

        $sql .= $this->wpdb->prepare(
            "DELETE FROM `$this->tmpOptionsTable` WHERE `option_name` = %s;\n",
            'wpstg_entire_network_clone_notice'
        );

        $sql .= $this->wpdb->prepare(
            "DELETE FROM `$this->tmpOptionsTable` WHERE `option_name` = %s;\n",
            'wpstg_resave_permalinks_executed'
        );

        /*
         * Prevent Staging Site created before WPSTAGING Pro 4.0.5
         * from re-inserting the old staging sites option on Push.
         */
        $sql .= $this->wpdb->prepare(
            "DELETE FROM `$this->tmpOptionsTable` WHERE `option_name` = %s;\n",
            'wpstg_existing_clones_beta'
        );

        $sql .= $this->wpdb->prepare(
            "DELETE FROM `$this->tmpOptionsTable` WHERE `option_name` = %s;\n",
            Sites::STAGING_LOGIN_LINK_SETTINGS
        );

        $this->executeBulk($sql);

        return $this->generateResponse();
    }
}
