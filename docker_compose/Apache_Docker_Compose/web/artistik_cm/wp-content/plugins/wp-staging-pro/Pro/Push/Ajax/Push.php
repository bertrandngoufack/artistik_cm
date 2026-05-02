<?php

namespace WPStaging\Pro\Push\Ajax;

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\Traits\MemoryExhaustTrait;
use WPStaging\Pro\Push\Jobs\StagingSitePush;

class Push extends AbstractTemplateComponent
{
    use MemoryExhaustTrait;

    /**
     * @var string
     */
    const WPSTG_REQUEST = 'staging_site_push';

    /**
     * @return void
     */
    public function render()
    {
        if (!$this->canRenderAjax()) {
            return;
        }

        $tmpFileToDelete = $this->getMemoryExhaustErrorTmpFile(self::WPSTG_REQUEST);

        $jobPush = WPStaging::make(StagingSitePush::class);
        $jobPush->setMemoryExhaustErrorTmpFile($tmpFileToDelete);

        wp_send_json($jobPush->prepareAndExecute());
    }
}
