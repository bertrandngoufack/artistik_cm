<?php

namespace WPStaging\Backend\Pro;

use WPStaging\Framework\Filesystem\FileObject;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Pro\License\Licensing;
use WPStaging\Core\WPStaging;

class WpstgRestoreDownloader
{
    private $capabilities = ['update_plugins'];
    private $token        = '';

    /**
     * @return void
     */
    public function addAccess(array $capabilities)
    {
        $capabilities       = array_merge($this->capabilities, $capabilities);
        $this->capabilities = array_filter($capabilities);
    }

    /**
     * @return bool
     */
    public function isCurrentUserCanDownload(): bool
    {
        foreach ($this->capabilities as $capability) {
            if (current_user_can($capability)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return void
     */
    public function setToken(string $token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    private function getToken(): string
    {
        if (!empty($this->token)) {
            return $this->token;
        }

        // @see dev/docs/wpstg-restore/README.md
        $token = get_option(implode('', array_map(function ($integer) {
            return chr($integer);
        }, array_reverse([121, 101, 107, 95, 101, 115, 110, 101, 99, 105, 108, 95, 103, 116, 115, 112, 119]))));

        return $token;
    }

    /**
     * @return bool
     */
    private function isMatchWithRestoreFile(string $filePath): bool
    {
        return preg_match('@^\.ht\.wpstg\-restore\-template\-[a-f0-9]{12}\.dist$@', basename($filePath));
    }

    /**
     * @return string
     */
    private function scanForRestoreFile(string $dirPath): string
    {
        clearstatcache();
        if (!file_exists($dirPath) || !is_dir($dirPath)) {
            return '';
        }

        $wpstgRestoreFile = '';
        foreach (glob($dirPath . '/.ht.wpstg-restore-template-*.dist') as $filePath) {
            if (!file_exists($filePath) || !is_file($filePath) || !$this->isMatchWithRestoreFile($filePath)) {
                continue;
            }

            $wpstgRestoreFile = $filePath;
            break;
        }

        return $wpstgRestoreFile;
    }

    /**
     * @return string
     */
    private function getRestoreFile(): string
    {
        $wpstgRestoreFile = $this->scanForRestoreFile(WPSTG_PLUGIN_DIR);
        if (!empty($wpstgRestoreFile)) {
            return $wpstgRestoreFile;
        }

        if (defined('WPSTG_IS_DEV') && (bool)WPSTG_IS_DEV || defined('WPSTG_IS_DEV_AUTOLOADER') && (bool)WPSTG_IS_DEV_AUTOLOADER) {
            $wpstgRestoreFile = $this->scanForRestoreFile(WPSTG_PLUGIN_DIR . '/wpstg-restore/dist');
        }

        return $wpstgRestoreFile;
    }

    /**
     * @return void
     */
     public function downloadFile()
    {
        if (!$this->isCurrentUserCanDownload() || !check_admin_referer('wpstg_restorer_nonce', 'wpstg_restorer_nonce') || !defined('WPSTGPRO_VERSION')) {
            wp_die(esc_html__('Invalid access', 'wp-staging'), 'WP Staging Restore', ['response' => 403, 'back_link' => true]);
        }

        $outputFileName = 'wpstg-restore.php';

        // Remove wpstg-restore.php file in root path
        if (!empty($_POST['wpstg-remove-restorer']) && !empty($outputFileName)) { // @phpstan-ignore-line
            $filePath = ABSPATH . $outputFileName;
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $dirPath = ABSPATH . 'wpstg-restore';
            if (file_exists($dirPath) && is_dir($dirPath)) {
                (new Filesystem())->setRecursive(true)->delete($dirPath);
            }

            wp_safe_redirect(wp_get_referer());
            return;
        }

        $backupFileName = '';
        if (!empty($_POST['backup-file-name'])) {
            $backupFileName = sanitize_text_field($_POST['backup-file-name']);
            $backupFileName = md5(bin2hex($backupFileName));
        }

        $backupFileId = '';
        if (!empty($_POST['backup-file-id'])) {
            $backupFileId = sanitize_text_field($_POST['backup-file-id']);
            $backupFileId = md5(bin2hex($backupFileId));
        }

        $copyToCurrentSitePath = '';
        if (!empty($_POST['copy-to-current-site'])) {
            $copyToCurrentSitePath = sanitize_text_field($_POST['copy-to-current-site']);

            if ($copyToCurrentSitePath !== get_site_url()) {
                wp_die(esc_html__('Failed to install wpstg-restore.php in the root path. Invalid site url', 'wp-staging'), 'WP Staging Restore', ['response' => 200, 'back_link' => false]);
            }

            if (!is_writable(ABSPATH)) {
                wp_die(esc_html__('Failed to install wpstg-restore.php in the root path. The root path is not writeable. Please contact support@wp-staging for help.', 'wp-staging'), 'WP Staging Restore', ['response' => 200, 'back_link' => false]);
            }
        }

        // Get wpstg-restore-.dist file
        $wpstgRestoreFile = $this->getRestoreFile();

        if (empty($wpstgRestoreFile) || !file_exists($wpstgRestoreFile)) {
            wp_die(esc_html__('The wpstg-restore.php file not found. Please contact support@wp-staging for help.', 'wp-staging'), 'WP Staging Restore', ['response' => 200, 'back_link' => true]);
        }

        $token = $this->getToken();
        if (empty($token)) {
            wp_die(esc_html__('Invalid access', 'wp-staging'), 'WP Staging | Restore', ['response' => 403, 'back_link' => true]);
        }

        // @see dev/docs/wpstg-restore/README.md
        $embed = implode(',', array_map(function ($string) {
            return ord($string);
        }, array_reverse(str_split($token))));

        $outputFileContent = '';
        $fileObject        = new FileObject($wpstgRestoreFile, FileObject::MODE_READ);
        while ($fileObject->valid()) {
            $fileContent = $fileObject->fgets();
            if (strpos($fileContent, '/**@wpstg-restorer-halt**/') !== false) {
                continue;
            }

            if (strpos($fileContent, '{{wpstgRestorerBackupFileName}}') !== false) {
                $fileContent = str_replace('{{wpstgRestorerBackupFileName}}', $backupFileName, $fileContent);
            }

            if (strpos($fileContent, '{{wpstgRestorerBackupFileId}}') !== false) {
                $fileContent = str_replace('{{wpstgRestorerBackupFileId}}', $backupFileId, $fileContent);
            }

            // @see dev/docs/wpstg-restore/README.md
            $outputFileContent .= str_replace('[53,98,55,55,51,48,101,101,102,57,57,99]', '[' . $embed . ']', $fileContent);
        }
        $fileObject = null;

        if (empty($outputFileContent)) {
            wp_die(esc_html__('The wpstg-restore.php file is empty', 'wp-staging'), 'WP Staging Restore', ['response' => 200, 'back_link' => true]);
        }
        
        // Apply filter to patch the restore script content
        if (has_filter('wpstg.restore.download.content')) {
            $outputFileContent = apply_filters('wpstg.restore.download.content', $outputFileContent);
        }

        if (!empty($copyToCurrentSitePath)) {
            try {
                if (has_filter('wpstg.restore.download.content')) {
                    $outputFileContent = apply_filters('wpstg.restore.download.content', $outputFileContent);
                }
                
                if (empty(file_put_contents(ABSPATH . $outputFileName, $outputFileContent, LOCK_EX))) {
                    wp_die(esc_html__('Failed to install wpstg-restore.php in the root path. Please contact support@wp-staging for help.', 'wp-staging'), 'WP Staging Restore', ['response' => 200, 'back_link' => false]);
                }
            } catch (\Throwable $e) {
                wp_die(sprintf(esc_html__('Failed to install wpstg-restore.php in the root path. Please contact support@wp-staging for help: %s', 'wp-staging'), $e->getMessage()), 'WP Staging Restore', ['response' => 200, 'back_link' => false]);
            }

            wp_safe_redirect($copyToCurrentSitePath . '/' . $outputFileName);
            exit;
        }

        nocache_headers();
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $outputFileName . '"');
        header('Content-Length: ' . strlen($outputFileContent));
        exit($outputFileContent); // phpcs:ignore
    }
}
