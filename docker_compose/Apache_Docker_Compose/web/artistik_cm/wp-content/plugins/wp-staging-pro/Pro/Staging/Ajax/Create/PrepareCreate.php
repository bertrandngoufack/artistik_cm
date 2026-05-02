<?php

namespace WPStaging\Pro\Staging\Ajax\Create;

use RuntimeException;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Facades\Sanitize;
use WPStaging\Pro\Staging\Jobs\StagingSiteCreate;
use WPStaging\Staging\Ajax\Create\PrepareCreate as PrepareJob;
use WPStaging\Staging\Dto\StagingSiteDto;

class PrepareCreate extends PrepareJob
{
    /**
     * @return string
     */
    protected function getJobClass(): string
    {
        return StagingSiteCreate::class;
    }

    protected function getDefaults(): array
    {
        $defaults = parent::getDefaults();

        // Add additional defaults for Pro version
        $defaults['useNewAdminAccount']      = false;
        $defaults['adminEmail']              = '';
        $defaults['adminPassword']           = '';
        $defaults['useCustomDatabase']       = false;
        $defaults['databaseServer']          = '';
        $defaults['databaseDatabase']        = '';
        $defaults['databaseUser']            = '';
        $defaults['databasePassword']        = '';
        $defaults['databasePrefix']          = 'wp_';
        $defaults['databaseSsl']             = false;
        $defaults['cloneDir']                = '';
        $defaults['cloneHostname']           = '';
        $defaults['isEmailsAllowed']         = true;
        $defaults['isCronEnabled']           = true;
        $defaults['isWooSchedulerEnabled']   = true;
        $defaults['isUploadsSymlinked']      = false;
        $defaults['isEmailsReminderEnabled'] = false;
        $defaults['isAutoUpdatePlugins']     = false;
        $defaults['networkClone']            = false;

        return $defaults;
    }

    protected function getAdvanceSettings(): array
    {
        if (empty($_POST['wpstgCreateData'])) {
            return [];
        }

        $config = [
            'useNewAdminAccount'      => 'bool',
            'adminEmail'              => 'string',
            'adminPassword'           => 'string',
            'useCustomDatabase'       => 'bool',
            'databaseServer'          => 'string',
            'databaseDatabase'        => 'string',
            'databaseUser'            => 'string',
            'databasePassword'        => 'string',
            'databasePrefix'          => 'string',
            'databaseSsl'             => 'bool',
            'cloneDir'                => 'string',
            'cloneHostname'           => 'string',
            'isEmailsAllowed'         => 'bool',
            'isCronEnabled'           => 'bool',
            'isWooSchedulerEnabled'   => 'bool',
            'isEmailsReminderEnabled' => 'bool',
            'isUploadsSymlinked'      => 'bool',
            'isAutoUpdatePlugins'     => 'bool',
            'networkClone'            => 'bool',
        ];

        $data = Sanitize::sanitizeArray($_POST['wpstgCreateData'], $config);

        // Let the parent class handle the basic sanitization
        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $config)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    protected function validateAndSanitizeAdvanceSettingsData(array $data): array
    {
        // New admin user
        $data['adminEmail']    = Sanitize::sanitizeEmail($data['adminEmail'] ?? '');
        $data['adminPassword'] = Sanitize::sanitizePassword($data['adminPassword'] ?? '');

        // Database
        $data['databaseServer']   = sanitize_text_field($data['databaseServer'] ?? '');
        $data['databaseName']     = sanitize_text_field($data['databaseDatabase'] ?? '');
        $data['databaseUser']     = sanitize_text_field($data['databaseUser'] ?? '');
        $data['databasePassword'] = Sanitize::sanitizePassword($data['databasePassword'] ?? '');
        $data['databasePrefix']   = sanitize_text_field($data['databasePrefix'] ?? 'wp_');

        // Path
        $data['customPath'] = Sanitize::sanitizePath($data['cloneDir'] ?? '');
        $data['customUrl']  = Sanitize::sanitizeUrl($data['cloneHostname'] ?? '');

        // Network clone - map to DTO property name (only available from main site)
        $data['isStagingNetwork'] = !empty($data['networkClone']) && is_multisite() && is_main_site();

        // Capture source blog ID for multisite (0 or 1 = main site, 2+ = subsite)
        $data['sourceBlogId'] = is_multisite() ? get_current_blog_id() : 1;

        // Ensure users and usermeta tables are always cloned when creating a staging site from a subsite.
        if (!$data['isStagingNetwork'] && $data['sourceBlogId'] > 1) {
            global $wpdb;
            $basePrefix = $wpdb->base_prefix;
            $usersTables = [
                $basePrefix . 'users',
                $basePrefix . 'usermeta',
            ];

            if (!isset($data['nonSiteTables']) || !is_array($data['nonSiteTables'])) {
                $data['nonSiteTables'] = [];
            }

            foreach ($usersTables as $usersTable) {
                if (!in_array($usersTable, $data['nonSiteTables'], true)) {
                    $data['nonSiteTables'][] = $usersTable;
                }
            }
        }

        unset($data['cloneDir'], $data['cloneHostname'], $data['databaseDatabase'], $data['networkClone']);

        return $data;
    }

    protected function getDestinationPath(array $data): string
    {
        $absPath = trailingslashit($this->filesystem->normalizePath($this->directory->getAbsPath()));

        if (empty($data['customPath'])) {
            return $absPath . $data['name'];
        }

        $customPath = trailingslashit($this->filesystem->normalizePath($data['customPath']));

        // Throw fatal error
        if ($customPath === $absPath) {
            throw new RuntimeException('Error: Target path must be different from the root of the current website.');
        }

        return $customPath;
    }

    protected function getDestinationUrl(array $data): string
    {
        if (!empty($data['customUrl'])) {
            return $data['customUrl'];
        }

        return trailingslashit(home_url()) . $data['name'];
    }

    protected function prepareStagingSiteDto()
    {
        $stagingSite = new StagingSiteDto();
        $stagingSite->setCloneId($this->jobDataDto->getCloneId());
        $stagingSite->setCloneName($this->jobDataDto->getName());
        $stagingSite->setPath($this->jobDataDto->getStagingSitePath());
        $stagingSite->setUrl($this->jobDataDto->getStagingSiteUrl());
        $stagingSite->setStatus(StagingSiteDto::STATUS_UNFINISHED_BROKEN);
        $stagingSite->setDatetime(time());
        $stagingSite->setVersion(WPStaging::getVersion());
        $stagingSite->setOwnerId(get_current_user_id());
        if ($this->jobDataDto->getUseCustomDatabase()) {
            $stagingSite->setDatabaseServer($this->jobDataDto->getDatabaseServer());
            $stagingSite->setDatabaseDatabase($this->jobDataDto->getDatabaseName());
            $stagingSite->setDatabaseUser($this->jobDataDto->getDatabaseUser());
            $stagingSite->setDatabasePassword($this->jobDataDto->getDatabasePassword());
            $stagingSite->setDatabaseSsl($this->jobDataDto->getDatabaseSsl());
            $stagingSite->setDatabasePrefix($this->jobDataDto->getDatabasePrefix());
        } else {
            $this->jobDataDto->setDatabasePrefix($this->findDatabasePrefix());
            $stagingSite->setPrefix($this->jobDataDto->getDatabasePrefix());
        }

        // Copy advanced staging options
        $stagingSite->setIsEmailsReminderEnabled($this->jobDataDto->getIsEmailsReminderEnabled());
        $stagingSite->setIsAutoUpdatePlugins($this->jobDataDto->getIsAutoUpdatePlugins());
        $stagingSite->setIsUploadsSymlink($this->jobDataDto->getIsUploadsSymlinked());
        $stagingSite->setNetworkClone($this->jobDataDto->getIsStagingNetwork());
        $stagingSite->setSourceBlogId($this->jobDataDto->getSourceBlogId());

        // Set up multisite network values
        $this->setupNetworkStagingValues();

        $this->jobDataDto->setStagingSite($stagingSite);
        if ($stagingSite->getIsExternalDatabase()) {
            $this->jobDataDto->setIsExternalDatabase(true);
        }
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
