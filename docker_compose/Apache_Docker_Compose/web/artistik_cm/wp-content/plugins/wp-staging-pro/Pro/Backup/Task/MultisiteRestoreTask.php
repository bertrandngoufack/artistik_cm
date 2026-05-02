<?php

namespace WPStaging\Pro\Backup\Task;

use UnexpectedValueException;
use wpdb;
use WPStaging\Backup\Task\RestoreTask;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Adapter\Database;
use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\SiteInfo;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Backup\Service\Database\Importer\AdjustSubsitesMeta;
use WPStaging\Pro\Traits\NetworkConstantTrait;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

/**
 * Class MultisiteRestoreTask
 *
 * This is an abstract class for the multisite specific restore actions of restoring a site.
 *
 * @package WPStaging\Pro\Backup\Task
 */
abstract class MultisiteRestoreTask extends RestoreTask
{
    use NetworkConstantTrait;

    /** @var array */
    protected $sites;

    /** @var wpdb */
    protected $wpdb;

    /** @var string */
    protected $sourceSiteDomain;

    /** @var string */
    protected $sourceSitePath;

    /** @var bool */
    protected $isSubdomainInstall;

    /** @var AdjustSubsitesMeta */
    protected $adjustSubsitesMeta;

    /** @var SiteInfo */
    protected $siteInfo;

    public function __construct(Database $database, AdjustSubsitesMeta $adjustSubsitesMeta, LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue, SiteInfo $siteInfo)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);

        $this->wpdb               = WPStaging::getInstance()->get("wpdb");
        $this->adjustSubsitesMeta = $adjustSubsitesMeta;
        $this->siteInfo           = $siteInfo;
        $this->setPrefix($database->getBasePrefix());
        $this->setDatabaseClient($database->getClient());
    }

    /**
     * @throws UnexpectedValueException
     */
    protected function adjustDomainPath()
    {
        $this->adjustSubsitesMeta->readBackupMetadata($this->jobDataDto->getBackupMetadata());
        $this->sourceSiteDomain   = $this->adjustSubsitesMeta->getSourceSiteDomain();
        $this->sourceSitePath     = $this->adjustSubsitesMeta->getSourceSitePath();
        $this->isSubdomainInstall = $this->adjustSubsitesMeta->getIsSourceSubdomainInstall();
        $this->sites              = $this->adjustSubsitesMeta->getAdjustedSubsites($this->getCurrentNetworkDomain(), $this->getCurrentNetworkPath(), get_site_url(), get_home_url(), is_subdomain_install());
    }

    /**
     * Are source and destination network domain and path same?
     * @return bool
     */
    protected function areDomainAndPathSame(): bool
    {
        if ($this->sourceSitePath !== $this->getCurrentNetworkPath()) {
            return false;
        }

        if ($this->sourceSiteDomain === $this->getCurrentNetworkDomain()) {
            return true;
        }

        // Check once again as the domain might be different due to www prefix
        return ('www.' . $this->sourceSiteDomain) === $this->getCurrentNetworkDomain();
    }

    /**
     * @param int $siteId
     * @return string
     */
    protected function getSiteOptionTable($siteId)
    {
        $tmpPrefix = $this->jobDataDto->getTmpDatabasePrefix();
        if ($siteId > 1) {
            return $tmpPrefix . $siteId . '_options';
        }

        return $tmpPrefix . 'options';
    }

    /**
     * @param string $tmpOptionsTable
     * @param string $option
     * @return bool
     */
    protected function isOptionExist(string $tmpOptionsTable, string $option): bool
    {
        $count = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT count(*) FROM {$tmpOptionsTable} WHERE option_name LIKE %s",
                $option
            )
        );

        if ((int)$count === 0) {
            return false;
        }

        return true;
    }

    /**
     * @param string $tmpOptionsTable
     * @param string $option
     * @param string $expectedValue
     * @return bool
     */
    protected function verifyOption(string $tmpOptionsTable, string $option, string $expectedValue): bool
    {
        // Verify whether the option was already with value
        $resultValue = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT option_value FROM {$tmpOptionsTable} WHERE option_name LIKE %s",
                $option
            )
        );

        return $resultValue === $expectedValue;
    }

    /**
     * @param string $tmpOptionsTable
     * @param string $option
     * @param string $value
     * @param bool $autoload
     * @return bool
     */
    protected function insertOption(string $tmpOptionsTable, string $option, string $value, bool $autoload): bool
    {
        if ($this->verifyOption($tmpOptionsTable, $option, $value)) {
            return true;
        }

        $result = $this->wpdb->query(
            $this->wpdb->prepare(
                "INSERT INTO {$tmpOptionsTable} ('option_name', 'option_value', 'autoload') VALUES (%s, %s, %s)",
                $option,
                $value,
                $autoload ? 'yes' : 'no'
            )
        );

        // Nothing to do next. The option is inserted
        if ($result) {
            return true;
        }

        return false;
    }

    /**
     * @param string $tmpOptionsTable
     * @param string $option
     * @param string $value
     * @return bool
     */
    protected function updateOption(string $tmpOptionsTable, string $option, string $value): bool
    {
        if ($this->verifyOption($tmpOptionsTable, $option, $value)) {
            return true;
        }

        $result = $this->wpdb->query(
            $this->wpdb->prepare(
                "UPDATE {$tmpOptionsTable} SET option_value = %s WHERE option_name LIKE %s",
                $value,
                $option
            )
        );

        if ($result) {
            return true;
        }

        return false;
    }

    protected function insertOrUpdateOption(string $tmpOptionsTable, string $option, string $value, bool $autoload = false): bool
    {
        if ($this->isOptionExist($tmpOptionsTable, $option)) {
            return $this->updateOption($tmpOptionsTable, $option, $value);
        }

        return $this->insertOption($tmpOptionsTable, $option, $value, $autoload);
    }

    /**
     * @param string $tmpOptionsTable
     * @param string $option
     * @return bool
     */
    protected function deleteOption(string $tmpOptionsTable, string $option): bool
    {
        if (!$this->isOptionExist($tmpOptionsTable, $option)) {
            return true;
        }

        $result = $this->wpdb->query(
            $this->wpdb->prepare(
                "DELETE FROM {$tmpOptionsTable} WHERE option_name LIKE %s",
                $option
            )
        );

        // Option deleted
        if ($result) {
            return true;
        }

        return false;
    }
}
