<?php

namespace WPStaging\Pro\Staging\Tasks\StagingSite\DatabaseAdjustment;

use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Pro\Staging\Traits\MultisiteDatabaseAdjustmentTrait;
use WPStaging\Staging\Tasks\StagingSite\DatabaseAdjustment\UpdatePrefixInOptionsTableTask as UpdatePrefixInOptionsTableTaskBase;

/**
 * Replacement for WPStaging\Framework\CloningProcess\Data\UpdateWpOptionsTablePrefix but for PRO version
 */
class UpdatePrefixInOptionsTableTask extends UpdatePrefixInOptionsTableTaskBase
{
    use MultisiteDatabaseAdjustmentTrait;

    /**
     * @return TaskResponseDto
     */
    public function execute()
    {
        $this->setup();
        $currentPrefix = $this->database->getPrefix();
        $stagingPrefix = $this->jobDataDto->getDatabasePrefix();
        if ($stagingPrefix === $currentPrefix) {
            $this->logger->info("Database prefix {$stagingPrefix} is already the same. Skipping for options table.");
            return $this->generateResponse();
        }

        if (!$this->jobDataDto->getIsStagingNetwork()) {
            $this->updatePrefixInOptionsTable($currentPrefix, $stagingPrefix);
            return $this->generateResponse();
        }

        $currentPrefix = $this->database->getBasePrefix();
        foreach ($this->getSubsites() as $subsite) {
            $this->setSubsiteId($subsite['blog_id']);
            $this->updatePrefixInOptionsTable($currentPrefix, $stagingPrefix);
        }

        return $this->generateResponse();
    }
}
