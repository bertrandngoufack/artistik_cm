<?php

namespace WPStaging\Pro\Push\Tasks\Filesystem;

use WPStaging\Framework\Filesystem\PartIdentifier;
use WPStaging\Pro\Push\Tasks\FileCopierTask;

/**
 * Copies themes from staging site to production but to a tmp location first.
 */
class CopyThemesTask extends FileCopierTask
{
    /** @var bool */
    protected $isTmpPath = true;

    public static function getTaskName(): string
    {
        return parent::getTaskName() . '_' . PartIdentifier::THEME_PART_IDENTIFIER;
    }

    public static function getTaskTitle(): string
    {
        return 'Pushing Themes to Production';
    }

    protected function getFileIdentifier(): string
    {
        return PartIdentifier::THEME_PART_IDENTIFIER;
    }

    protected function getIsExcluded(): bool
    {
        return $this->jobDataDto->getIsThemesExcluded();
    }
}
