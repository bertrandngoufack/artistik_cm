<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobRestore;

use WPStaging\Framework\SiteInfo;
use WPStaging\Pro\Backup\Task\MultisiteRestoreTask;

class AdjustSubsitesOptionsTask extends MultisiteRestoreTask
{
    public static function getTaskName()
    {
        return 'backup_restore_adjust_subsite_options';
    }

    public static function getTaskTitle()
    {
        return 'Adjusting options for subsites';
    }

    public function execute()
    {
        $this->stepsDto->setTotal(1);

        if ($this->jobDataDto->getIsMissingDatabaseFile()) {
            $this->logger->warning(esc_html__('Skipped updating subsites options due to missing database file.', 'wp-staging'));
            return $this->generateResponse();
        }

        $this->adjustDomainPath();
        $this->adjustOptions();

        $this->logger->info(esc_html__('Updating site options for subsites in database finished.', 'wp-staging'));

        return $this->generateResponse();
    }

    protected function adjustOptions()
    {
        $optionsToRemove       = [];
        $insertOrUpdateOptions = [];
        if ($this->siteInfo->isStagingSite()) {
            $insertOrUpdateOptions[SiteInfo::IS_STAGING_KEY] = 'true';
        } else {
            $optionsToRemove[] = SiteInfo::IS_STAGING_KEY;
        }

        foreach ($this->sites as $blog) {
            $tmpOptionsTable = $this->getSiteOptionTable($blog['blogId']);

            foreach ($optionsToRemove as $optionName) {
                if (!$this->deleteOption($tmpOptionsTable, $optionName)) {
                    $this->logger->warning(sprintf(esc_html__("Failed to remove option '%s' from options table for blog_id: %s and site_id: %s.", "wp-staging"), $optionName, $blog['blogId'], $blog['siteId']));
                }
            }

            foreach ($insertOrUpdateOptions as $optionName => $optionValue) {
                if (!$this->insertOrUpdateOption($tmpOptionsTable, $optionName, $optionValue, false)) {
                    $this->logger->warning(sprintf(esc_html__("Failed to insert/update option '%s' in options table for blog_id: %s and site_id: %s.", "wp-staging"), $optionName, $blog['blogId'], $blog['siteId']));
                }
            }
        }
    }
}
