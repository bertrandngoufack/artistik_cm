<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Backup\Ajax;

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\Traits\MemoryExhaustTrait;
use WPStaging\Backup\Job\JobExtractProvider;

/**
 * Executes the extract job in multiple HTTP requests.
 */
class Extract extends AbstractTemplateComponent
{
    use MemoryExhaustTrait;

    /**
     * @var string
     */
    const WPSTG_REQUEST = 'wpstg_extract';

    public function render()
    {
        if (!$this->canRenderAjax()) {
            return;
        }

        $tmpFileToDelete = $this->getMemoryExhaustErrorTmpFile(self::WPSTG_REQUEST);

        $jobExtract = WPStaging::make(JobExtractProvider::class)->getJob();
        $jobExtract->setMemoryExhaustErrorTmpFile($tmpFileToDelete);

        wp_send_json($jobExtract->prepareAndExecute());
    }
}
