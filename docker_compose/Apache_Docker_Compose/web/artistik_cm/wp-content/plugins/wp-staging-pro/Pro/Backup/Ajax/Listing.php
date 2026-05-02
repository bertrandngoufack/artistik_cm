<?php

namespace WPStaging\Pro\Backup\Ajax;

use WPStaging\Backup\Ajax\BaseListing;
use WPStaging\Core\WPStaging;
use WPStaging\Pro\License\Licensing;
use WPStaging\Pro\WPStagingPro;

class Listing extends BaseListing
{
    /**
     * @return string
     */
    protected function getTemplate(): string
    {
        return 'pro/backup/listing.php';
    }

    /**
     * @return void
     */
    public function render()
    {
        if (!$this->canRenderAjax()) {
            return;
        }

        if (!WPStagingPro::isValidLicense() && is_multisite()) {
            $result = $this->templateEngine->render('backup/free-version.php');
        } else {
            $directories = $this->getDirectories();
            $result = $this->templateEngine->render(
                $this->getTemplate(),
                array_merge($this->getCommonRenderData(), ['directories' => $directories])
            );
        }

        wp_send_json($result);
    }

    /**
     * @return array
     */
    protected function getCommonRenderData(): array
    {
        /**
         * @var Licensing $licensing
         */
        $licensing = WPStaging::make(Licensing::class);
        $data      = parent::getCommonRenderData();
        return array_merge($data, [
            'isProVersion'      => true,
            'isValidLicense'    => WPStagingPro::isValidLicense(),
            'isPersonalLicense' => $licensing->isPersonalLicense(),
            'licenseType'       => $licensing->getLicenseType(),
        ]);
    }
}
