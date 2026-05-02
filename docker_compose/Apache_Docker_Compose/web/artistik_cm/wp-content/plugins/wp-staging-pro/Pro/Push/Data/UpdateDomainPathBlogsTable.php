<?php

namespace WPStaging\Pro\Push\Data;

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Utils\Strings;

class UpdateDomainPathBlogsTable extends DBPushService
{
    /** @var Strings */
    private $strUtil;

    /**
     * @inheritDoc
     */
    protected function internalExecute()
    {
        if (!$this->isNetworkClone()) {
            return true;
        }

        $this->strUtil = WPStaging::make(Strings::class);

        return $this->updateBlogsTable();
    }

    /**
     * @return bool
     */
    private function updateBlogsTable(): bool
    {
        // Early bail if site table is excluded
        if ($this->isTableExcluded($this->stagingPrefix . 'blogs')) {
            $this->log("{$this->stagingPrefix}blogs excluded. Skipping this step");
            return true;
        }

        $tmpBlogsTable = $this->getTmpPrefix() . 'blogs';

        if ($this->isTable($tmpBlogsTable) === false) {
            $this->log('Fatal Error ' . $tmpBlogsTable . ' does not exist');
            $this->returnException('Fatal Error ' . $tmpBlogsTable . ' does not exist');
            return false;
        }

        $currentSiteDomain = $this->getCurrentSiteDomain();
        $currentSitePath   = $this->getCurrentSitePath();
        $stagingPath       = $this->dto->getStagingSitePath();
        $stagingDomain     = $this->dto->getStagingSiteDomain();
        foreach ($this->getStagingMultisiteBlogs() as $blog) {
            $subsitePath   = str_replace(trailingslashit($stagingPath), $currentSitePath, $blog->path);
            $subsiteDomain = str_replace($stagingDomain, $currentSiteDomain, $blog->domain);
            if (strpos($blog->domain, $stagingDomain) === false) {
                $subsiteDomain = $this->getDomainSubsite($blog->domain, $stagingDomain, $currentSiteDomain);
            }

            $this->log(sprintf("Updating domain and path in %s for blog_id = %s to %s and %s respectively", $tmpBlogsTable, $blog->blog_id, esc_url($subsiteDomain), esc_html($subsitePath)));

            $result = $this->updateDatabase($tmpBlogsTable, $blog->blog_id, $subsiteDomain, $subsitePath);
            if ($result === false) {
                $this->returnException("Failed to update domain and path in {$tmpBlogsTable} for blog_id = {$blog->blog_id}. {$this->productionDb->last_error}");
                return false;
            }
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
        $stagingIdentifier = $this->strUtil->strReplaceFirst($currentMainDomain, '', $stagingMainDomain);

        return $this->strUtil->strReplaceFirst($stagingIdentifier, '', $stagingSubsiteDomain);
    }

    /**
     * @param string $blogsTable
     * @param int $subsiteId
     * @param string $subsiteDomain
     * @param string $subsitePath
     * @return bool
     */
    protected function updateDatabase(string $blogsTable, int $subsiteId, string $subsiteDomain, string $subsitePath): bool
    {
        return $this->productionDb->query(
            $this->productionDb->prepare(
                "UPDATE {$blogsTable} SET domain = %s, path = %s WHERE blog_id = %s",
                $subsiteDomain,
                $subsitePath,
                $subsiteId
            )
        );
    }
}
