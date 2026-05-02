<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobRestore;

use WPStaging\Pro\Backup\Task\MultisiteRestoreTask;

class UpdateSubsitesUrlsTask extends MultisiteRestoreTask
{
    public static function getTaskName()
    {
        return 'backup_restore_update_site_home_url';
    }

    public static function getTaskTitle()
    {
        return 'Updating site and home url for subsites';
    }

    public function execute()
    {
        $this->stepsDto->setTotal(1);

        if ($this->jobDataDto->getIsMissingDatabaseFile()) {
            $this->logger->warning(esc_html__('Skipped updating subsites URLs due to missing database file.', 'wp-staging'));
            return $this->generateResponse();
        }

        if ($this->jobDataDto->getIsSameSiteBackupRestore()) {
            $this->logger->info(esc_html__('Skipped updating site URL and home URL, as they already same.', 'wp-staging'));
            return $this->generateResponse();
        }

        $this->adjustDomainPath();
        // Skip if destination domain and path already same
        if ($this->areDomainAndPathSame() && $this->isSubdomainInstall === is_subdomain_install()) {
            $this->logger->info(esc_html__('Skipped updating site URL and home URL, as they already same.', 'wp-staging'));
            return $this->generateResponse();
        }

        $this->updateOptionsTableSiteHomeURL();

        $this->logger->info(esc_html__('Updating site URL and home URL for subsites in database finished.', 'wp-staging'));

        return $this->generateResponse();
    }

    protected function updateOptionsTableSiteHomeURL()
    {
        foreach ($this->sites as $blog) {
            if ($blog['adjustedSiteUrl'] === $blog['siteUrl'] && $blog['adjustedHomeUrl'] === $blog['homeUrl']) {
                continue;
            }

            $tmpOptionsTable = $this->getSiteOptionTable($blog['blogId']);

            if (!$this->updateOption($tmpOptionsTable, 'siteurl', $blog['adjustedSiteUrl'])) {
                $this->logger->warning(sprintf(esc_html__("Failed to update site URL in options table for blog_id: %s and site_id: %s.", "wp-staging"), $blog['blogId'], $blog['siteId']));
            }

            if (!$this->updateOption($tmpOptionsTable, 'home', $blog['adjustedHomeUrl'])) {
                $this->logger->warning(sprintf(esc_html__("Failed to update home URL in options table for blog_id: %s and site_id: %s.", "wp-staging"), $blog['blogId'], $blog['siteId']));
            }
        }
    }
}
