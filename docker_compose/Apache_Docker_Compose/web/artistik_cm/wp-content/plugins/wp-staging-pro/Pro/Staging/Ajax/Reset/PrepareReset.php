<?php

namespace WPStaging\Pro\Staging\Ajax\Reset;

use WPStaging\Core\WPStaging;
use WPStaging\Pro\Staging\Jobs\StagingSiteReset;
use WPStaging\Staging\Ajax\Reset\PrepareReset as PrepareJob;
use WPStaging\Staging\Dto\StagingSiteDto;

class PrepareReset extends PrepareJob
{
    /**
     * @return string
     */
    protected function getJobClass(): string
    {
        return StagingSiteReset::class;
    }

    protected function prepareStagingSiteDto()
    {
        $stagingSite = $this->jobDataDto->getStagingSite();
        $stagingSite->setStatus(StagingSiteDto::STATUS_UNFINISHED_BROKEN);
        $stagingSite->setDatetime(time());
        $stagingSite->setVersion(WPStaging::getVersion());
        $stagingSite->setOwnerId(get_current_user_id());

        // Copy network clone flag and source blog ID from staging site to job data DTO
        $this->jobDataDto->setIsStagingNetwork($stagingSite->getNetworkClone());
        $this->jobDataDto->setSourceBlogId($stagingSite->getSourceBlogId());

        // Set up multisite network values for UpdateDomainAndPathTask
        $this->setupNetworkStagingValues();

        $this->jobDataDto->setStagingSite($stagingSite);
    }

    /**
     * Set up staging network domain and path for multisite network cloning
     * @return void
     */
    protected function setupNetworkStagingValues()
    {
        if (!$this->jobDataDto->getIsStagingNetwork()) {
            return;
        }

        $stagingUrl = $this->jobDataDto->getStagingSiteUrl();

        // Extract domain and path from the staging URL
        $parsedUrl = wp_parse_url($stagingUrl);
        $domain    = $parsedUrl['host'] ?? '';
        $path      = isset($parsedUrl['path']) ? trailingslashit($parsedUrl['path']) : '/';

        $this->jobDataDto->setStagingNetworkDomain($domain);
        $this->jobDataDto->setStagingNetworkPath($path);
    }
}
