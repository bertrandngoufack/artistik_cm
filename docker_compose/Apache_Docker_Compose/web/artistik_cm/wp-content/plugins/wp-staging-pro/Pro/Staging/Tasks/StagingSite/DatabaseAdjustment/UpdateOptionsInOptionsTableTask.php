<?php

namespace WPStaging\Pro\Staging\Tasks\StagingSite\DatabaseAdjustment;

use WPStaging\Framework\Job\Dto\TaskResponseDto;
use WPStaging\Pro\Staging\NetworkClone;
use WPStaging\Pro\Staging\Traits\MultisiteDatabaseAdjustmentTrait;
use WPStaging\Staging\Tasks\StagingSite\DatabaseAdjustment\UpdateOptionsInOptionsTableTask as UpdateOptionsInOptionsTableTaskBase;

/**
 * Replacement for WPStaging\Framework\CloningProcess\Data\UpdateStagingOptionsTable but for PRO version
 */
class UpdateOptionsInOptionsTableTask extends UpdateOptionsInOptionsTableTaskBase
{
    use MultisiteDatabaseAdjustmentTrait;

    /**
     * @return TaskResponseDto
     */
    public function execute()
    {
        $this->setup();
        if (!$this->jobDataDto->getIsStagingNetwork()) {
            $this->updateOptionsTable();
            return $this->generateResponse();
        }

        foreach ($this->getSubsites() as $subsite) {
            $this->setSubsiteId($subsite['blog_id']);
            $this->updateOptionsTable();
        }

        return $this->generateResponse();
    }

    /**
     * @param array $cloneOptions
     */
    protected function adjustCloneOptions(array &$cloneOptions)
    {
        /**
         * add the base directory path and is network clone when cloning into network
         * Required to generate .htaccess file on the staging network.
         */
        if ($this->jobDataDto->getIsStagingNetwork() && $this->isMainSite()) {
            $cloneOptions[NetworkClone::NEW_NETWORK_CLONE_KEY] = 'true';
            $cloneOptions[NetworkClone::NETWORK_BASE_DIR_KEY]  = $this->jobDataDto->getStagingSitePath();
        }
    }

    protected function isMainSite(): bool
    {
        return is_main_site($this->subsiteId);
    }
}
