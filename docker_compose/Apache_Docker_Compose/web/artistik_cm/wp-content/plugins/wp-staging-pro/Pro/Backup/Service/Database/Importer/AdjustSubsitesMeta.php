<?php

namespace WPStaging\Pro\Backup\Service\Database\Importer;

use WPStaging\Backup\Entity\BackupMetadata;
use WPStaging\Pro\Multisite\Dto\SubsiteDto;
use WPStaging\Pro\Multisite\Service\AbstractAdjustSubsitesMeta;

/**
 * Class responsible for adjusting subsites meta data
 * Source site is the site on which backup was created
 * Destination site is the site to which backup is being restored
 */
class AdjustSubsitesMeta extends AbstractAdjustSubsitesMeta
{
    /** @var string */
    const FILTER_MULTISITE_SUBSITES = 'wpstg.backup.restore.multisites.subsites';

    /**
     * @param BackupMetadata $backupMetadata
     * @return void
     * @throws \UnexpectedValueException
     */
    public function readBackupMetadata(BackupMetadata $backupMetadata)
    {
        $this->isSourceSubdomainInstall = $backupMetadata->getSubdomainInstall();
        $this->sourceSiteUrl            = $backupMetadata->getSiteUrl();
        $this->sourceHomeUrl            = $backupMetadata->getHomeUrl();

        $sourceSiteURLWithoutWWW = str_ireplace('//www.', '//', $this->sourceSiteUrl);
        $parsedURL               = parse_url($sourceSiteURLWithoutWWW);

        if (!is_array($parsedURL) || !array_key_exists('host', $parsedURL)) {
            throw new \UnexpectedValueException("Bad URL format, cannot proceed.");
        }

        $this->sourceSiteDomain = $parsedURL['host'];
        $this->sourceSitePath   = '/';
        if (array_key_exists('path', $parsedURL)) {
            $this->sourceSitePath = $parsedURL['path'];
        }

        $this->sites = [];
        foreach ($backupMetadata->getSites() as $site) {
            $this->sites[] = SubsiteDto::createFromSiteData($site);
        }
    }

    protected function getFilterToUse(): string
    {
        return self::FILTER_MULTISITE_SUBSITES;
    }
}
