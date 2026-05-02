<?php

/**
 * Handles staging site creation from the command line with support for all staging options.
 *
 * Supports single site, single subsite (multisite), and full network staging
 * with advanced options for exclusions, external database, custom admin, and more.
 */

namespace WPStaging\Pro\WpCli\Commands;

use WP_CLI;
use WPStaging\Core\WPStaging;
use WPStaging\Pro\Staging\BackgroundProcessing\PrepareCreate;

class StagingSiteCreateCommand implements CommandInterface
{
    /**
     * Create a staging site using the Background Processing system.
     *
     * @param array               $args      A list of positional arguments (key=value format).
     * @param array<string,mixed> $assocArgs A map of associative arguments (flags like --quiet).
     * @return mixed
     * @throws WP_CLI\ExitException If the job preparation fails.
     */
    public function __invoke(array $args = [], array $assocArgs = [])
    {
        $options = $this->extractOptionsFromArgs($args);
        $data = $this->setupStagingSiteCreateData($options);

        try {
            $jobId = WPStaging::make(PrepareCreate::class)->prepare($data);

            if ($jobId instanceof \WP_Error) {
                WP_CLI::error('Failed to prepare request for creating staging site: ' . $jobId->get_error_message());
            }

            $quiet = isset($assocArgs['quiet']);
            if (!$quiet) {
                WP_CLI::success(
                    sprintf(
                        "Staging Site creation started with Job ID %s\nUse the \"%s\" command to check its status.",
                        $jobId,
                        "wp wpstg status '$jobId'"
                    )
                );
            } else {
                WP_CLI::line($jobId);
            }
        } catch (\Exception $e) {
            WP_CLI::error('Exception thrown while preparing the creation of staging site: ' . $e->getMessage());
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
    protected function setupStagingSiteCreateData(array $options): array
    {
        $data = $this->getDefaultData();
        $data = $this->setupBasicOptions($options, $data);
        $data = $this->setupExclusionRules($options, $data);
        $data = $this->setupAdvancedOptions($options, $data);
        $data = $this->setupMultisiteOptions($options, $data);

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
            'cloneId'                 => (string)time(),
            'name'                    => '',
            'allTablesExcluded'       => false,
            'excludedTables'          => [],
            'includedTables'          => [],
            'nonSiteTables'           => [],
            'excludedDirectories'     => [],
            'extraDirectories'        => [],
            'excludeGlobRules'        => [],
            // Exclude rules
            'excludeSizeGreaterThan'  => 8,
            'excludeFileRules'        => [],
            'excludeFolderRules'      => [],
            'excludeExtensionRules'   => [],
            // Advanced settings
            'useNewAdminAccount'      => false,
            'adminEmail'              => '',
            'adminPassword'           => '',
            'useCustomDatabase'       => false,
            'databaseServer'          => '',
            'databaseDatabase'        => '',
            'databaseUser'            => '',
            'databasePassword'        => '',
            'databasePrefix'          => 'wp_',
            'databaseSsl'             => false,
            'cloneDir'                => '',
            'cloneHostname'           => '',
            'isEmailsAllowed'         => true,
            'isCronEnabled'           => true,
            'isWooSchedulerEnabled'   => true,
            'isUploadsSymlinked'      => false,
            'isEmailsReminderEnabled' => false,
            'isAutoUpdatePlugins'     => false,
            // Multisite
            'networkClone'            => false,
            'sourceBlogId'            => is_multisite() ? get_current_blog_id() : 0,
        ];
    }

    /**
     * @param array $options
     * @param array $data
     * @return array
     */
    protected function setupBasicOptions(array $options, array $data): array
    {
        if (isset($options['name'])) {
            $name = $this->sanitizeName($options['name']);
            if (empty($name)) {
                WP_CLI::error('Staging site name cannot be empty.');
            }

            $data['name'] = $name;
        }

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
                WP_CLI::error('exclude-size-greater-than must be a positive number.');
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
     * @param array $options
     * @param array $data
     * @return array
     */
    protected function setupAdvancedOptions(array $options, array $data): array
    {
        // New admin account
        if (isset($options['admin-email']) && isset($options['admin-password'])) {
            $email = $this->validateEmail($options['admin-email']);
            $password = $options['admin-password'];

            if (strlen($password) < 8) {
                WP_CLI::warning('Password is less than 8 characters. Consider using a stronger password.');
            }

            $data['useNewAdminAccount'] = true;
            $data['adminEmail'] = $email;
            $data['adminPassword'] = $password;
        } elseif (isset($options['admin-email']) || isset($options['admin-password'])) {
            WP_CLI::error('Both admin-email and admin-password must be provided together.');
        }

        // External database
        if (isset($options['database-server'])) {
            $data['useCustomDatabase'] = true;
            $data['databaseServer'] = sanitize_text_field($options['database-server']);

            if (!isset($options['database-name'])) {
                WP_CLI::error('database-name is required when using an external database.');
            }

            if (!isset($options['database-user'])) {
                WP_CLI::error('database-user is required when using an external database.');
            }

            $data['databaseDatabase'] = sanitize_text_field($options['database-name']);
            $data['databaseUser'] = sanitize_text_field($options['database-user']);
            $data['databasePassword'] = $options['database-password'] ?? '';
            $data['databasePrefix'] = sanitize_text_field($options['database-prefix'] ?? 'wp_');
            $data['databaseSsl'] = $this->parseBool($options['database-ssl'] ?? 'false');
        }

        // Custom path and URL
        if (isset($options['custom-path'])) {
            $customPath = $options['custom-path'];

            // Prevent path traversal attacks
            if (strpos($customPath, '..') !== false) {
                WP_CLI::error('Invalid custom path: path traversal is not allowed.');
            }

            $data['cloneDir'] = sanitize_text_field($customPath);
        }

        if (isset($options['custom-url'])) {
            $url = $options['custom-url'];

            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                WP_CLI::error('Invalid URL format provided for custom-url.');
            }

            $data['cloneHostname'] = esc_url_raw($url);
        }

        // Boolean options
        if (isset($options['allow-emails'])) {
            $data['isEmailsAllowed'] = $this->parseBool($options['allow-emails']);
        }

        if (isset($options['symlink-uploads'])) {
            $data['isUploadsSymlinked'] = $this->parseBool($options['symlink-uploads']);
        }

        if (isset($options['enable-cron'])) {
            $data['isCronEnabled'] = $this->parseBool($options['enable-cron']);
        }

        if (isset($options['enable-woo-scheduler'])) {
            $data['isWooSchedulerEnabled'] = $this->parseBool($options['enable-woo-scheduler']);
        }

        if (isset($options['enable-email-reminder'])) {
            $data['isEmailsReminderEnabled'] = $this->parseBool($options['enable-email-reminder']);
        }

        if (isset($options['enable-auto-update-plugins'])) {
            $data['isAutoUpdatePlugins'] = $this->parseBool($options['enable-auto-update-plugins']);
        }

        return $data;
    }

    /**
     * @param array $options
     * @param array $data
     * @return array
     */
    protected function setupMultisiteOptions(array $options, array $data): array
    {
        if (!is_multisite()) {
            if (isset($options['type']) && $options['type'] !== 'single') {
                WP_CLI::error('Multisite options are only available on multisite installations.');
            }

            return $data;
        }

        $type = $options['type'] ?? 'single';
        $validTypes = ['single', 'subsite', 'network'];

        if (!in_array($type, $validTypes, true)) {
            WP_CLI::error("Invalid staging type '{$type}'. Valid types are: " . implode(', ', $validTypes) . '.');
        }

        if ($type === 'network') {
            if (!is_main_site()) {
                WP_CLI::error('Network cloning is only available from the main site.');
            }

            $data['networkClone'] = true;
        }

        if (isset($options['source-blog-id'])) {
            $blogId = (int)$options['source-blog-id'];
            $this->validateSubsiteBlogId($blogId);
            $data['sourceBlogId'] = $blogId;
        } elseif ($type === 'subsite') {
            $data['sourceBlogId'] = get_current_blog_id();
        }

        return $data;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function sanitizeName(string $name): string
    {
        $name = str_replace(['"', "'"], '', $name);
        return sanitize_file_name($name);
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

    /**
     * @param string $email
     * @return string
     */
    protected function validateEmail(string $email): string
    {
        $email = sanitize_email($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            WP_CLI::error('Invalid email format provided for admin-email.');
        }

        return $email;
    }

    /**
     * @param int $blogId
     * @return void
     */
    protected function validateSubsiteBlogId(int $blogId)
    {
        if (!is_multisite()) {
            return;
        }

        if ($blogId < 1) {
            WP_CLI::error('source-blog-id must be greater than 0.');
        }

        if (!get_blog_details($blogId)) {
            WP_CLI::error("Subsite with blog ID {$blogId} does not exist.");
        }
    }

    /**
     * @param string $value
     * @return bool
     */
    protected function parseBool(string $value): bool
    {
        $value = strtolower(trim($value));
        return in_array($value, ['true', '1', 'yes', 'on'], true);
    }
}
