<?php

namespace WPStaging\Pro\Push\Tasks\Filesystem;

use WPStaging\Framework\Filesystem\PartIdentifier;
use WPStaging\Pro\Push\Tasks\FileCopierTask;

class CopyMuPluginsTask extends FileCopierTask
{
    public static function getTaskName(): string
    {
        return parent::getTaskName() . '_' . PartIdentifier::MU_PLUGIN_PART_IDENTIFIER;
    }

    public static function getTaskTitle(): string
    {
        return 'Copying Mu-Plugins to Staging Site';
    }

    protected function getFileIdentifier(): string
    {
        return PartIdentifier::MU_PLUGIN_PART_IDENTIFIER;
    }

    protected function getIsExcluded(): bool
    {
        return $this->jobDataDto->getIsMuPluginsExcluded();
    }
}
