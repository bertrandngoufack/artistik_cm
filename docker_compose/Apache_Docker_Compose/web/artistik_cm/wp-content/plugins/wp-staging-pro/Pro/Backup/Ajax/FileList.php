<?php

namespace WPStaging\Pro\Backup\Ajax;

use WPStaging\Backup\Ajax\BaseFileList;
use WPStaging\Pro\WPStagingPro;

class FileList extends BaseFileList
{
    /**
     * @return void
     */
    public function render()
    {
        if (!$this->canRenderAjax()) {
            return;
        }

        $listableBackups = $this->getBackups();
        $listableBackups = $this->sortBackups($listableBackups);
        $withTemplate    = !empty($_GET['withTemplate']) && $this->sanitize->sanitizeBool($_GET['withTemplate']); //phpcs:ignore
        // Returns a JSON response
        if (!$withTemplate) {
            wp_send_json($listableBackups);
        }

        // Returns an HTML template
        $output = '';
        $isValidLicenseKey = WPStagingPro::isValidLicense();
        if (empty($listableBackups) || !$isValidLicenseKey) {
            $output .= $this->renderTemplate('backup/listing-backups-no-results.php', [
                'urlAssets'         => $this->urlAssets,
                'isProVersion'      => true,
                'isValidLicenseKey' => $isValidLicenseKey,
            ]);
        } else {
            $output .= $this->renderBackups($listableBackups);
        }

        wp_send_json($output);
    }
}
