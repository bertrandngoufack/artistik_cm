<?php

namespace WPStaging\Pro\Push\Tasks\Filesystem;

use WPStaging\Framework\Filesystem\PartIdentifier;
use WPStaging\Pro\Push\Tasks\FileCopierTask;

/**
 * Copies plugins from staging site to production but to a tmp location first.
 */
class CopyPluginsTask extends FileCopierTask
{
    /** @var bool */
    protected $isTmpPath = true;

    public static function getTaskName(): string
    {
        return parent::getTaskName() . '_' . PartIdentifier::PLUGIN_PART_IDENTIFIER;
    }

    public static function getTaskTitle(): string
    {
        return 'Pushing Plugins to Production';
    }

    protected function getFileIdentifier(): string
    {
        return PartIdentifier::PLUGIN_PART_IDENTIFIER;
    }

    protected function getIsExcluded(): bool
    {
        return $this->jobDataDto->getIsPluginsExcluded();
    }
}
