<?php

namespace WPStaging\Pro\Push\Service;

use WPStaging\Staging\Service\Database\RowsExporter as BaseRowsExporter;
use WPStaging\Staging\Interfaces\StagingOperationDtoInterface;

/**
 * For push operations, we need to reverse the search and replace parameters.
 */
class RowsExporter extends BaseRowsExporter
{
    protected function getSearchReplaceParams(): array
    {
        if (!$this->jobDataDto instanceof StagingOperationDtoInterface) {
            throw new \RuntimeException('JobDataDto must be an instance of StagingOperationDtoInterface.');
        }

        $search    = $this->generateHostnamePatterns($this->getHostnameWithoutScheme($this->jobDataDto->getStagingSiteUrl()));
        $replace   = $this->generateHostnamePatterns($this->getSourceHostname());
        $search[]  = $this->getFinalPrefix();
        $replace[] = $this->getPrefix();

        return [
            'search'  => $search,
            'replace' => $replace,
        ];
    }
}
