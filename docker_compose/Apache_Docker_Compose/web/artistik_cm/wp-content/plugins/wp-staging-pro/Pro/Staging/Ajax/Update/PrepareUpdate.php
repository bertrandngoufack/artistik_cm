<?php

namespace WPStaging\Pro\Staging\Ajax\Update;

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Facades\Sanitize;
use WPStaging\Pro\Staging\Jobs\StagingSiteUpdate;
use WPStaging\Staging\Ajax\Update\PrepareUpdate as PrepareJob;
use WPStaging\Staging\Dto\StagingSiteDto;

class PrepareUpdate extends PrepareJob
{
    /**
     * @return string
     */
    protected function getJobClass(): string
    {
        return StagingSiteUpdate::class;
    }

    protected function getDefaults(): array
    {
        $defaults = parent::getDefaults();

        // Add additional defaults for Pro version
        $defaults['isEmailsAllowed']         = true;
        $defaults['isEmailsReminderEnabled'] = false;
        $defaults['isAutoUpdatePlugins']     = false;

        return $defaults;
    }

    protected function getAdvanceSettings(): array
    {
        if (empty($_POST['wpstgUpdateData'])) {
            return [];
        }

        $config = [
            'isEmailsAllowed'         => 'bool',
            'isEmailsReminderEnabled' => 'bool',
            'isAutoUpdatePlugins'     => 'bool',
        ];

        $data = Sanitize::sanitizeArray($_POST['wpstgUpdateData'], $config);

        // Let the parent class handle the basic sanitization
        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $config)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    protected function prepareStagingSiteDto()
    {
        $stagingSite = $this->jobDataDto->getStagingSite();
        $stagingSite->setStatus(StagingSiteDto::STATUS_UNFINISHED_BROKEN);
        $stagingSite->setDatetime(time());
        $stagingSite->setVersion(WPStaging::getVersion());
        $stagingSite->setOwnerId(get_current_user_id());

        // Copy advanced staging options
        $stagingSite->setIsEmailsReminderEnabled($this->jobDataDto->getIsEmailsReminderEnabled());
        $stagingSite->setIsAutoUpdatePlugins($this->jobDataDto->getIsAutoUpdatePlugins());

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
