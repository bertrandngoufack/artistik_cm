<?php

namespace WPStaging\Pro\Staging\Traits;

use WPStaging\Pro\Multisite\Dto\AdjustedSubsiteDto;
use WPStaging\Pro\Staging\Service\SubsitesAdjuster;
use WPStaging\Pro\Traits\NetworkConstantTrait;

/**
 * Provide or override methods in Tasks to support multisite database adjustments
 * Must be used only inside DatabaseAdjustmentTask
 */
trait MultisiteDatabaseAdjustmentTrait
{
    use NetworkConstantTrait;

    /**
     * @var int
     */
    protected $subsiteId = 1;

    /**
     * @var SubsitesAdjuster
     */
    protected $subsitesAdjuster;

    /**
     * @var AdjustedSubsiteDto[]
     */
    protected $adjustedSubsites = [];

    /**
     * @param int $subsiteId
     * @return void
     */
    protected function setSubsiteId(int $subsiteId)
    {
        $this->subsiteId = $subsiteId;
    }

    protected function getOptionsTableName(): string
    {
        if (!$this->jobDataDto->getIsStagingNetwork()) {
            return $this->getPrefixedStagingTableName('options');
        }

        if ($this->subsiteId === 0 || $this->subsiteId === 1) {
            return $this->getPrefixedStagingTableName('options');
        }

        return $this->getPrefixedStagingTableName($this->subsiteId . '_options');
    }

    protected function getSubsites(): array
    {
        $subsites = [];
        foreach (get_sites() as $site) {
            $subsites[] = [
                'site_id'  => $site->site_id,
                'blog_id'  => $site->blog_id,
                'domain'   => $site->domain,
                'path'     => $site->path,
                'site_url' => get_site_url($site->blog_id),
                'home_url' => get_home_url($site->blog_id),
            ];
        }

        return $subsites;
    }

    protected function adjustSubsites()
    {
        $this->setDatabaseClient($this->database->getClient());
        $this->setPrefix($this->database->getBasePrefix());
        $this->subsitesAdjuster->setSourceSites($this->getSubsites());
        $this->subsitesAdjuster->setSourceSiteDomain($this->getCurrentNetworkDomain());
        $this->subsitesAdjuster->setSourceSitePath($this->getCurrentNetworkPath());
        $this->subsitesAdjuster->setSourceHomeUrl(home_url());
        $this->subsitesAdjuster->setSourceSiteUrl(site_url());
        $this->subsitesAdjuster->setSourceSubdomainInstall(is_subdomain_install());

        $this->adjustedSubsites = $this->subsitesAdjuster->getAdjustedSubsites($this->jobDataDto->getStagingNetworkDomain(), $this->jobDataDto->getStagingNetworkPath(), $this->jobDataDto->getStagingSiteUrl(), $this->jobDataDto->getStagingSiteUrl(), is_subdomain_install());
    }
}
