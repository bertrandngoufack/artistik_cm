<?php

/**
 * Handles staging site reset from the command line with support for exclusion options.
 *
 * Resets an existing staging site to match the current production site state.
 * Supports single site, single subsite (multisite), and full network staging sites.
 */

namespace WPStaging\Pro\WpCli\Commands;

use Exception;
use WP_CLI;
use WPStaging\Core\WPStaging;
use WPStaging\Pro\Staging\BackgroundProcessing\PrepareReset;
use WPStaging\Staging\Traits\StagingSiteGetterTrait;

class StagingSiteResetCommand implements CommandInterface
{
    use StagingSiteGetterTrait;

    /**
     * Reset a staging site using the Background Processing system.
     *
     * @param array               $args      A list of positional arguments (key=value format).
     * @param array<string,mixed> $assocArgs A map of associative arguments (flags like --quiet).
     * @return mixed
     * @throws WP_CLI\ExitException If the job preparation fails.
     */
    public function __invoke(array $args = [], array $assocArgs = [])
    {
        $options = $this->extractOptionsFromArgs($args);
        $data = $this->setupStagingSiteResetData($options);

        try {
            $jobId = WPStaging::make(PrepareReset::class)->prepare($data);

            if ($jobId instanceof \WP_Error) {
                WP_CLI::error('Failed to prepare request for resetting staging site: ' . $jobId->get_error_message());
            }

            $quiet = isset($assocArgs['quiet']);
            if (!$quiet) {
                WP_CLI::success(
                    sprintf(
                        "Staging Site reset started with Job ID %s\nUse the \"%s\" command to check its status.",
                        $jobId,
                        "wp wpstg status '$jobId'"
                    )
                );
            } else {
                WP_CLI::line($jobId);
            }
        } catch (\Exception $e) {
            WP_CLI::error('Exception thrown while preparing the reset of staging site: ' . $e->getMessage());
        }
    }

    /**
     * @param array $args
     * @return array
     */
    protected function extractOptionsFromArgs(array $args): array
    {
        $options = [];
        foreach ($args as $arg) {
            $parts = explode('=', $arg, 2);
            if (count($parts) === 2) {
                $options[$parts[0]] = $parts[1];
            }
        }

        return $options;
    }

    /**
     * @param array $options
     * @return array
     */
    protected function setupStagingSiteResetData(array $options): array
    {
        $data = $this->getDefaultData();

        try {
            $data['cloneId'] = $this->validateStagingSiteByIdOrName($options);
        } catch (Exception $e) {
            WP_CLI::error($e->getMessage());
        }

        // Table exclusions
        $data = $this->setupTableOptions($options, $data);

        // Directory exclusions
        $data = $this->setupDirectoryOptions($options, $data);

        // Exclusion rules
        $data = $this->setupExclusionRules($options, $data);

        $data['isWpCliRequest'] = true;
        $data['isInit'] = true;

        return $data;
    }

    /**
     * @return array
     */
    protected function getDefaultData(): array
    {
        return [
            'cloneId'                => '',
            'allTablesExcluded'      => false,
            'excludedTables'         => [],
            'includedTables'         => [],
            'nonSiteTables'          => [],
            'excludedDirectories'    => [],
            'extraDirectories'       => [],
            'excludeGlobRules'       => [],
            // Exclude rules
            'excludeSizeGreaterThan' => 8,
            'excludeFileRules'       => [],
            'excludeFolderRules'     => [],
            'excludeExtensionRules'  => [],
        ];
    }

    /**
     * @param array $options
     * @param array $data
     * @return array
     */
    protected function setupTableOptions(array $options, array $data): array
    {
        if (isset($options['excluded-tables'])) {
            $tables = explode(',', $options['excluded-tables']);
            $data['excludedTables'] = $this->sanitizeArray($tables);
        }

        if (isset($options['included-tables'])) {
            $tables = explode(',', $options['included-tables']);
            $data['includedTables'] = $this->sanitizeArray($tables);
        }

        if (isset($options['non-site-tables'])) {
            $tables = explode(',', $options['non-site-tables']);
            $data['nonSiteTables'] = $this->sanitizeArray($tables);
        }

        return $data;
    }

    /**
     * @param array $options
     * @param array $data
     * @return array
     */
    protected function setupDirectoryOptions(array $options, array $data): array
    {
        if (isset($options['excluded-directories'])) {
            $dirs = explode(',', $options['excluded-directories']);
            $data['excludedDirectories'] = $this->sanitizeArray($dirs);
        }

        if (isset($options['extra-directories'])) {
            $dirs = explode(',', $options['extra-directories']);
            $data['extraDirectories'] = $this->sanitizeArray($dirs);
        }

        return $data;
    }

    /**
     * @param array $options
     * @param array $data
     * @return array
     */
    protected function setupExclusionRules(array $options, array $data): array
    {
        if (isset($options['exclude-size-greater-than'])) {
            $size = (float)$options['exclude-size-greater-than'];
            if ($size < 0) {
                WP_CLI::error('exclude-size-greater-than must be a non-negative number.');
            }

            $data['excludeSizeGreaterThan'] = $size;
        }

        if (isset($options['exclude-file-rules'])) {
            $data['excludeFileRules'] = $this->sanitizeArray(explode(',', $options['exclude-file-rules']));
        }

        if (isset($options['exclude-folder-rules'])) {
            $data['excludeFolderRules'] = $this->sanitizeArray(explode(',', $options['exclude-folder-rules']));
        }

        if (isset($options['exclude-extension-rules'])) {
            $data['excludeExtensionRules'] = $this->sanitizeArray(explode(',', $options['exclude-extension-rules']));
        }

        return $data;
    }

    /**
     * @param array $items
     * @return array
     */
    protected function sanitizeArray(array $items): array
    {
        return array_map(function ($item) {
            return sanitize_text_field(trim($item));
        }, array_filter($items));
    }
}
