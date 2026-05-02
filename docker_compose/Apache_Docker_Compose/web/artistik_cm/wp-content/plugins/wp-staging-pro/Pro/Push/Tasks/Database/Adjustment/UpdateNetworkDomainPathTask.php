<?php

namespace WPStaging\Pro\Push\Tasks\Database\Adjustment;

use WPStaging\Framework\Adapter\Database;
use WPStaging\Framework\Database\TableService;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Framework\Utils\Strings;
use WPStaging\Framework\Utils\Urls;
use WPStaging\Pro\Push\Tasks\DatabaseAdjustmentTask;
use WPStaging\Pro\Traits\NetworkConstantTrait;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

/**
 * This class is responsible for updating network domain and path during staging site push.
 */
class UpdateNetworkDomainPathTask extends DatabaseAdjustmentTask
{
    use NetworkConstantTrait;

    /** @var Strings */
    protected $strings;

    /**
     * @param LoggerInterface $logger
     * @param Cache $cache
     * @param StepsDto $stepsDto
     * @param SeekableQueueInterface $taskQueue
     * @param Urls $urls
     * @param Database $database
     * @param TableService $tableService
     * @param Strings $strings
     */
    public function __construct(LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue, Urls $urls, Database $database, TableService $tableService, Strings $strings)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue, $urls, $database, $tableService);
        $this->strings = $strings;
    }

    public static function getTaskName(): string
    {
        return 'push_update_network_domain_path';
    }

    public static function getTaskTitle(): string
    {
        return 'Update network domain and path';
    }

    public function execute(): TaskResponseDto
    {
        // Early bail if not network clone.
        if (!$this->jobDataDto->getStagingSite()->getNetworkClone()) {
            return $this->generateResponse();
        }

        $this->setup();
        if (!$this->adjustSiteTable()) {
            return $this->generateResponse();
        }

        $this->adjustBlogsTable();

        return $this->generateResponse();
    }

    /**
     * Adjusts the network domain and path in the blogs table.
     */
    protected function adjustBlogsTable(): bool
    {
        // Early bail if site table is excluded
        if ($this->isStagingTableExcluded('blogs')) {
            $this->logger->warning("{$this->stagingPrefix}blogs excluded. Skipping adjusting domain and path for blogs table");
            return true;
        }

        $tmpBlogsTable = $this->tmpPrefix . 'blogs';

        if ($this->isTableExists($tmpBlogsTable) === false) {
            $this->logger->error('Fatal Error ' . $tmpBlogsTable . ' does not exist');
            return false;
        }

        $currentSiteDomain = $this->getCurrentNetworkDomain();
        $currentSitePath   = $this->getCurrentNetworkPath();
        $stagingPath       = $this->getStagingSitePath();
        $stagingDomain     = $this->getStagingSiteDomain();
        foreach ($this->getStagingSubsites() as $blog) {
            $subsitePath   = str_replace(trailingslashit($stagingPath), $currentSitePath, $blog->path);
            $subsiteDomain = str_replace($stagingDomain, $currentSiteDomain, $blog->domain);
            if (strpos($blog->domain, $stagingDomain) === false) {
                $subsiteDomain = $this->getDomainSubsite($blog->domain, $stagingDomain, $currentSiteDomain);
            }

            $this->logger->info(sprintf("Updating domain and path in %s for blog_id = %s to %s and %s respectively", $tmpBlogsTable, $blog->blog_id, esc_url($subsiteDomain), esc_html($subsitePath)));

            $result = $this->executeBlogsQuery($tmpBlogsTable, (int)$blog->blog_id, $subsiteDomain, $subsitePath);
            if ($result === false) {
                $this->logger->error("Failed to update domain and path in {$tmpBlogsTable} for blog_id = {$blog->blog_id}. {$this->wpdb->last_error}");
                return false;
            }
        }

        return true;
    }

    /**
     * Adjust the site table with the current domain and path.
     */
    private function adjustSiteTable(): bool
    {
        // Early bail if site table is excluded
        if ($this->isStagingTableExcluded('site')) {
            $this->logger->warning("{$this->stagingPrefix}site excluded. Skipping this step");
            return true;
        }

        $tmpSiteTable = $this->tmpPrefix . 'site';

        if ($this->isTableExists($tmpSiteTable) === false) {
            $this->logger->error('Fatal Error ' . $tmpSiteTable . ' does not exist');
            return false;
        }

        $currentSiteDomain = $this->getCurrentNetworkDomain();
        $currentSitePath   = $this->getCurrentNetworkPath();
        $this->logger->info(sprintf("Updating domain and path in %s to %s and %s respectively", $tmpSiteTable, esc_url($currentSiteDomain), esc_html($currentSitePath)));
        // Replace URLs
        $result = $this->wpdb->query(
            $this->wpdb->prepare(
                "UPDATE {$tmpSiteTable} SET domain = %s, path = %s",
                $currentSiteDomain,
                $currentSitePath
            )
        );

        if ($result === false) {
            $this->logger->error("Failed to update domain and path in {$tmpSiteTable}. {$this->wpdb->last_error}");
            return false;
        }

        return true;
    }

    /**
     * Get domain for the different domain subsite
     *
     * @param string $stagingSubsiteDomain
     * @param string $stagingMainDomain
     * @param string $currentMainDomain
     * @return string
     */
    protected function getDomainSubsite(string $stagingSubsiteDomain, string $stagingMainDomain, string $currentMainDomain): string
    {
        $stagingIdentifier = $this->strings->strReplaceFirst($currentMainDomain, '', $stagingMainDomain);

        return $this->strings->strReplaceFirst($stagingIdentifier, '', $stagingSubsiteDomain);
    }

    /**
     * @param string $blogsTable
     * @param int $subsiteId
     * @param string $subsiteDomain
     * @param string $subsitePath
     * @return bool
     */
    protected function executeBlogsQuery(string $blogsTable, int $subsiteId, string $subsiteDomain, string $subsitePath): bool
    {
        return $this->wpdb->query(
            $this->wpdb->prepare(
                "UPDATE {$blogsTable} SET domain = %s, path = %s WHERE blog_id = %s",
                $subsiteDomain,
                $subsitePath,
                $subsiteId
            )
        );
    }

    /**
     * @return string
     */
    protected function getStagingSiteDomain()
    {
        $stagingSiteDomain = parse_url($this->jobDataDto->getStagingSite()->getUrl(), PHP_URL_HOST);
        if (defined('DOMAIN_CURRENT_SITE') && strpos(DOMAIN_CURRENT_SITE, 'www.') !== 0) {
            $stagingSiteDomain = str_ireplace('www.', '', $stagingSiteDomain);
        }

        return $stagingSiteDomain;
    }

    /**
     * @return string
     */
    protected function getStagingSitePath()
    {
        $parsedUrl = parse_url($this->jobDataDto->getStagingSite()->getUrl());
        if (isset($parsedUrl['path'])) {
            $stagingSitePath = $parsedUrl['path'];
        } else {
            $stagingSitePath = '/';
        }

        return $stagingSitePath;
    }
}
