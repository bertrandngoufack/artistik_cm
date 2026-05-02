<?php

namespace WPStaging\Pro\Staging\Tasks\StagingSite\DatabaseAdjustment;

use WPStaging\Framework\Adapter\Database;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Framework\Utils\Urls;
use WPStaging\Pro\Staging\Service\SubsitesAdjuster;
use WPStaging\Pro\Staging\Traits\MultisiteDatabaseAdjustmentTrait;
use WPStaging\Staging\Tasks\DatabaseAdjustmentTask;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

/**
 * Partial Replacement for WPStaging\Framework\CloningProcess\Data\UpdateSiteUrlAndHome
 * This class is responsible for updating the domain and path of the staging site in blogs and site table.
 */
class UpdateDomainAndPathTask extends DatabaseAdjustmentTask
{
    use MultisiteDatabaseAdjustmentTrait;

    /**
     * @return string
     */
    public static function getTaskName()
    {
        return 'staging_update_domain_and_path';
    }

    /**
     * @return string
     */
    public static function getTaskTitle()
    {
        return 'Updating domain and path for the staging network';
    }

    /**
     * @param LoggerInterface $logger
     * @param Cache $cache
     * @param StepsDto $stepsDto
     * @param SeekableQueueInterface $taskQueue
     * @param Urls $urls
     * @param Database $database
     */
    public function __construct(LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue, Urls $urls, Database $database, SubsitesAdjuster $subsitesAdjuster)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue, $urls, $database);
        $this->subsitesAdjuster = $subsitesAdjuster;
    }

    /**
     * @return TaskResponseDto
     */
    public function execute()
    {
        $this->setup();
        if (!$this->updateSiteTableDomainPath()) {
            $this->logger->warning("Failed to update Domain and Path in site table.");
        }

        $blogsTable = $this->jobDataDto->getDatabasePrefix() . 'blogs';
        if ($this->isTableExcluded('blogs')) {
            $this->logger->warning(sprintf("Skipped updating Domain and Path in blogs table as the table is excluded. Blogs Table: %s", $blogsTable));
            return $this->generateResponse();
        }

        $this->adjustSubsites();
        foreach ($this->adjustedSubsites as $blog) {
            $result = $this->executeQuery(
                "UPDATE {$blogsTable} SET domain = %s, path = %s WHERE blog_id = %s AND site_id = %s",
                $blog['adjustedDomain'],
                $blog['adjustedPath'],
                $blog['blogId'],
                $blog['siteId']
            );

            if (!$result) {
                $this->logger->warning(sprintf(esc_html__("Failed to update Domain and Path in blogs table for blog_id: %s and site_id: %s.", "wp-staging"), $blog->getBlogId(), $blog->getSiteId()));
            }
        }

        return $this->generateResponse();
    }

    protected function updateSiteTableDomainPath(): bool
    {
        $siteTable = $this->jobDataDto->getDatabasePrefix() . 'site';
        if ($this->isTableExcluded('site')) {
            $this->logger->warning(sprintf("Skipped updating Domain and Path in site table as the table is excluded. Site Table: %s.", $siteTable));
            return true;
        }

        $result = $this->wpdb->query(
            $this->wpdb->prepare(
                "UPDATE {$siteTable} SET domain = %s, path = %s",
                $this->jobDataDto->getStagingNetworkDomain(),
                $this->jobDataDto->getStagingNetworkPath()
            )
        );

        return $result !== false;
    }
}
