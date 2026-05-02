<?php

namespace WPStaging\Pro\Push\Tasks\Filesystem;

use WPStaging\Framework\Filesystem\PartIdentifier;
use WPStaging\Pro\Push\Tasks\FileCopierTask;

/**
 * Copies uploads from staging site to production.
 * When isCleanUploads option is enabled, this task will first delete
 * the production uploads directory before copying new uploads from staging.
 */
class CopyUploadsTask extends FileCopierTask
{
    public static function getTaskName(): string
    {
        return parent::getTaskName() . '_' . PartIdentifier::UPLOAD_PART_IDENTIFIER;
    }

    public static function getTaskTitle(): string
    {
        return 'Pushing Media to Production';
    }

    protected function getFileIdentifier(): string
    {
        return PartIdentifier::UPLOAD_PART_IDENTIFIER;
    }

    protected function getIsExcluded(): bool
    {
        return $this->jobDataDto->getIsUploadsExcluded();
    }
}
