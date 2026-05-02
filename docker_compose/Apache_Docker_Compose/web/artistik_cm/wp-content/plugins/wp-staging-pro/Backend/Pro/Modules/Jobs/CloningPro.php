<?php

namespace WPStaging\Backend\Pro\Modules\Jobs;

use WPStaging\Backend\Modules\Jobs\Cloning;
use WPStaging\Backend\Modules\Jobs\Data as CloningData;
use WPStaging\Framework\Facades\Hooks;
use WPStaging\Framework\Utils\Sanitize;

/**
 * @package WPStaging\Backend\Pro\Modules\Jobs
 */
class CloningPro extends Cloning
{
    /** @var string */
    const FILTER_CLONING_EMAIL_ALLOWED = 'wpstg_cloning_email_allowed';

    /**
     * This is required to be also added here to make phpcs for xss pass.
     * @var Sanitize
     */
    protected $sanitize;

    /**
     * @return CloningProData
     */
    public function getDataJob(): CloningData
    {
        return new CloningProData();
    }

    /**
     * @return void
     */
    protected function setAdvancedCloningOptions()
    {
        $this->options->useNewAdminAccount = isset($_POST["useNewAdminAccount"]) && $this->sanitize->sanitizeBool($_POST["useNewAdminAccount"]);

        if (!empty($_POST["adminEmail"])) {
            $this->options->adminEmail = $this->sanitize->sanitizeEmail($_POST["adminEmail"]);
        }

        if (!empty($_POST["adminPassword"])) {
            $this->options->adminPassword = $this->sanitize->sanitizePassword($_POST["adminPassword"]);
            $this->options->adminPassword = wp_hash_password($this->options->adminPassword);
        }

        if (!empty($_POST["databaseServer"])) {
            $this->options->databaseServer = $this->sanitize->sanitizeString($_POST["databaseServer"]);
        }

        if (!empty($_POST["databaseUser"])) {
            $this->options->databaseUser = $this->sanitize->sanitizeString($_POST["databaseUser"]);
        }

        if (!empty($_POST["databasePassword"])) {
            $this->options->databasePassword = $this->sanitize->sanitizePassword($_POST["databasePassword"]);
        }

        if (!empty($_POST["databaseDatabase"])) {
            $this->options->databaseDatabase = $this->sanitize->sanitizeString($_POST["databaseDatabase"]);
        }

        if (!empty($_POST["databasePrefix"])) {
            $this->options->databasePrefix = $this->strUtil->maybeAppendUnderscore($this->sanitize->sanitizeString($_POST["databasePrefix"]));
        }

        if (isset($_POST["databaseSsl"]) && 'true' === $this->sanitize->sanitizeString($_POST["databaseSsl"])) {
            $this->options->databaseSsl = true;
        }

        if (!empty($_POST["cloneDir"])) {
            $this->options->cloneDir = trailingslashit(wpstg_urldecode($this->sanitize->sanitizePath($_POST["cloneDir"])));
        }

        if (!empty($_POST["cloneHostname"])) {
            $this->options->cloneHostname = $this->sanitize->sanitizeString($_POST["cloneHostname"]);
        }

        $this->options->isEmailsAllowed = Hooks::applyFilters(
            self::FILTER_CLONING_EMAIL_ALLOWED,
            isset($_POST['isEmailsAllowed']) && $this->sanitize->sanitizeBool($_POST['isEmailsAllowed'])
        );

        $this->options->isCronEnabled           = empty($_POST['isCronEnabled']) ? false : $this->sanitize->sanitizeBool($_POST['isCronEnabled']);
        $this->options->isWooSchedulerEnabled   = empty($_POST['isWooSchedulerEnabled']) ? false : $this->sanitize->sanitizeBool($_POST['isWooSchedulerEnabled']);
        $this->options->isEmailsReminderEnabled = empty($_POST['isEmailsReminderEnabled']) ? false : $this->sanitize->sanitizeBool($_POST["isEmailsReminderEnabled"]);
        $this->options->isAutoUpdatePlugins   = empty($_POST["isAutoUpdatePlugins"]) ? false : $this->sanitize->sanitizeBool($_POST["isAutoUpdatePlugins"]);
    }
}
