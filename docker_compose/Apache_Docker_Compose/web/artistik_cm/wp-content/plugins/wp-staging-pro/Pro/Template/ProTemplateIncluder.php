<?php

namespace WPStaging\Pro\Template;

use WPStaging\Core\Forms\Form;
use WPStaging\Framework\Assets\Assets;
use WPStaging\Pro\WPStagingPro;

class ProTemplateIncluder
{
    /** @var string */
    private $proViewsFolder;

    /** @var bool */
    private $isValidLicense;

    /** @var Assets */
    private $assets;

    /**
     * @param Assets $assets
     */
    public function __construct(Assets $assets)
    {
        $this->assets         = $assets;
        $this->proViewsFolder = WPSTG_VIEWS_DIR . 'pro/';
        $this->isValidLicense = WPStagingPro::isValidLicense();
    }

    /**
     * Add the "Push" button to the template
     *
     * @param string $cloneID
     * @param array $data
     * @param object $license
     * @return void
     */
    public function addPushButton(string $cloneID, array $data, $license)
    {
        $isValidLicense           = $this->isValidLicense;
        $assets                   = $this->assets;
        $newStagingFeatureEnabled = (defined('WPSTG_NEW_STAGING') && WPSTG_NEW_STAGING);
        include $this->proViewsFolder . 'clone/ajax/push-button.php';
    }

    /**
     * Add the "Edit this Clone" link to the template
     *
     * @param string $cloneID
     * @param array $data
     * @param object $license
     * @return void
     */
    public function addEditCloneLink(string $cloneID, array $data, $license)
    {
        $isValidLicense = $this->isValidLicense;
        $assets         = $this->assets;
        include $this->proViewsFolder . 'clone/ajax/edit-clone.php';
    }

    /**
     * Add generate login link to the action menu for staging site
     *
     * @param string $cloneID
     * @param array $data
     * @param object $license
     * @return void
     */
    public function addGenerateLoginLink(string $cloneID, array $data, $license)
    {
        $isValidLicense = $this->isValidLicense;
        $assets         = $this->assets;
        include $this->proViewsFolder . 'clone/ajax/generate-login.php';
    }

    /**
     * Add "Sync User Account" button on the actions tab
     *
     * @param string $cloneID
     * @param array $data
     * @return void
     */
    public function addSyncAccountButton(string $cloneID, array $data)
    {
        $isValidLicense = $this->isValidLicense;
        $assets         = $this->assets;
        include $this->proViewsFolder . 'clone/ajax/sync-button.php';
    }

    /**
     * @param Form $form
     */
    public function addProSettings(Form $form)
    {
        if (!$this->isValidLicense) {
            return;
        }

        include $this->proViewsFolder . 'settings/general.php';
    }

    /**
     * Add Pro templates on listing page of staging tab
     * @return void
     */
    public function addStagingModalTemplates()
    {
        include $this->proViewsFolder . 'staging/modal/edit-staging-site-modal.php';
    }

    /**
     * @return void
     */
    public function addRemoteSyncModalTemplates()
    {
        include $this->proViewsFolder . 'remote-sync/remote-sync-wait.php';
    }

    /**
     * @return void
     */
    public function addMultiSiteCloneOption()
    {
        include $this->proViewsFolder . 'clone/multi-site-cloning-options.php';
    }

    public function addBackupOption()
    {
        if (!$this->isValidLicense) {
            return;
        }

        require_once(WPSTG_VIEWS_DIR . "backup/free-version.php");
    }
}
