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
use WPStaging\Staging\Tasks\StagingSite\DatabaseAdjustment\UpdateSiteUrlAndHomeTask as UpdateSiteUrlAndHomeTaskBase;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

/**
 * Partial Replacement for WPStaging\Framework\CloningProcess\Data\UpdateSiteUrlAndHome but for PRO version
 * This only updates site_url and home in the options table
 */
class UpdateSiteUrlAndHomeTask extends UpdateSiteUrlAndHomeTaskBase
{
    use MultisiteDatabaseAdjustmentTrait;

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
        if (!$this->jobDataDto->getIsStagingNetwork()) {
            $this->updateOptionsTable($this->jobDataDto->getStagingSiteUrl());
            return $this->generateResponse();
        }

        $this->adjustSubsites();
        foreach ($this->adjustedSubsites as $blog) {
            $this->setSubsiteId($blog['blogId']);
            $this->updateOptionsTable($blog['adjustedSiteUrl']);
        }

        return $this->generateResponse();
    }
}
