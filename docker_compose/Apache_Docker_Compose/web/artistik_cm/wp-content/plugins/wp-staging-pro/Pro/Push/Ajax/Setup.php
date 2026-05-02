<?php

namespace WPStaging\Pro\Push\Ajax;

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\Facades\Sanitize;
use WPStaging\Framework\Job\Exception\ProcessLockedException;
use WPStaging\Framework\Job\ProcessLock;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Pro\Push\Service\DirectoryScanner;
use WPStaging\Pro\Push\Service\TableScanner;
use WPStaging\Pro\Staging\Service\StagingSetup;
use WPStaging\Staging\Dto\StagingSiteDto;
use WPStaging\Staging\Sites;

class Setup extends AbstractTemplateComponent
{
    /**
     * @var StagingSetup
     */
    private $stagingSetup;

    /**
     * @var DirectoryScanner
     */
    private $directoryScanner;

    /**
     * @var TableScanner
     */
    private $tableScanner;

    /**
     * @var Directory
     */
    private $directoryAdapter;

    /**
     * @var ProcessLock
     */
    private $processLock;

    public function __construct(TemplateEngine $templateEngine, StagingSetup $stagingSetup, DirectoryScanner $directoryScanner, TableScanner $tableScanner, Directory $directoryAdapter, ProcessLock $processLock)
    {
        parent::__construct($templateEngine);
        $this->stagingSetup     = $stagingSetup;
        $this->processLock      = $processLock;
        $this->directoryScanner = $directoryScanner;
        $this->directoryAdapter = $directoryAdapter;
        $this->tableScanner     = $tableScanner;
    }

    /**
     * @return void
     */
    public function ajaxSetup()
    {
        if (!$this->canRenderAjax()) {
            return;
        }

        try {
            $this->processLock->checkProcessLocked();
        } catch (ProcessLockedException $e) {
            wp_send_json_error($e->getMessage(), $e->getCode());
        }

        $cloneId = $this->getValidatedCloneId();
        if (empty($cloneId)) {
            throw new \InvalidArgumentException('Invalid clone ID provided.');
        }

        $this->stagingSetup->initPushJob($this->getStagingSiteDtoByCloneId($cloneId));
        $this->directoryScanner->setStagingSetup($this->stagingSetup);
        $this->tableScanner->setStagingSetup($this->stagingSetup);
        $this->directoryScanner->setupPush();

        $result = $this->templateEngine->render(
            'pro/push/setup.php',
            [
                'stagingSetup'     => $this->stagingSetup,
                'stagingSiteDto'   => $this->stagingSetup->getStagingSiteDto(),
                'directoryScanner' => $this->directoryScanner,
                'directoryAdapter' => $this->directoryAdapter,
                'tableScanner'     => $this->tableScanner,
            ]
        );

        wp_send_json_success($result);
    }

    private function getValidatedCloneId(): string
    {
        if (empty($_POST['cloneId'])) {
            return '';
        }

        return Sanitize::sanitizeString($_POST['cloneId']);
    }

    /**
     * @param string $cloneId
     * @return StagingSiteDto
     * @throws \Exception
     */
    private function getStagingSiteDtoByCloneId(string $cloneId): StagingSiteDto
    {
        /**
         * Lazy loading and it is not needed everywhere.
         * @var Sites $stagingSitesService
         */
        $stagingSitesService = WPStaging::make(Sites::class);

        return $stagingSitesService->getStagingSiteDtoByCloneId($cloneId);
    }
}
