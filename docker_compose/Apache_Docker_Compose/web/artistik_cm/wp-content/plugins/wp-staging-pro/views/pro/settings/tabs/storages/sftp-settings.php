<?php

/**
 * @var string $providerId
 */

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Facades\UI\Toggle;
use WPStaging\Pro\Backup\Storage\SFTP\Auth;

/** @var Auth */
$storage = WPStaging::make(Auth::class);

$ftpModeOptions = [
    Auth::FTP_UPLOAD_MODE_PUT          => 'PUT MODE',
    Auth::FTP_UPLOAD_MODE_APPEND       => 'APPEND MODE',
    Auth::FTP_UPLOAD_MODE_NON_BLOCKING => 'NON-BLOCKING MODE',
];

if ($storage->isEncrypted()) {
    require_once WPSTG_VIEWS_DIR . "pro/settings/tabs/storages/encrypted-notice.php";
}

$options          = $storage->getOptions();
$ftpType          = $options['ftpType'] ?? Auth::CONNECTION_TYPE_SFTP;
$host             = $options['host'] ?? '';
$port             = $options['port'] ?? ($ftpType === 'ftp' ? '21' : '22');
$username         = $options['username'] ?? '';
$password         = $options['password'] ?? '';
$ssl              = $options['ssl'] ?? false;
$passive          = $options['passive'] ?? false;
$useFtpExtension  = $options['useFtpExtension'] ?? false;
$ftpMode          = $options['ftpMode'] ?? Auth::FTP_UPLOAD_MODE_PUT;
$passphrase       = $options['passphrase'] ?? '';
$maxBackupsToKeep = max(2, $options['maxBackupsToKeep'] ?? 15);
$location         = $options['location'] ?? 'wpstg-backups/' . parse_url(site_url(), PHP_URL_HOST);
$verifyCert       = $options['verifyCert'] ?? true;
$allowInsecure    = $options['allowInsecure'] ?? false;
$ftpCertPath      = $options['ftpCertPath'] ?? '';
$ftpCertContent   = $options['ftpCertContent'] ?? '';
$fingerprint      = $options['fingerprint'] ?? '';
$privateKey       = $options['key'] ?? '';
$useSshKey        = $options['useSshKey'] ?? false;
$hasDbKey         = !empty($privateKey);
$isSftp           = $ftpType === Auth::CONNECTION_TYPE_SFTP;
$hasWpConfigKey   = defined('WPSTG_STORAGE_SFTP_KEY') && WPSTG_STORAGE_SFTP_KEY !== '';
$hasKey           = $hasDbKey || $hasWpConfigKey;
?>

<div class="wpstg-bg-white dark:wpstg-bg-[#141b27] wpstg-provider-settings-container">
    <div class="wpstg-max-w-3xl wpstg-py-1 wpstg-space-y-6 wpstg-provider-settings-container-inner">
        <header>
            <h1 class="wpstg-text-2xl wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100"><?php echo esc_html__('Upload backups to your server', 'wp-staging'); ?></h1>
            <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400"><?php echo esc_html__('Connect WP Staging to your server so it can upload backup files automatically.', 'wp-staging'); ?></p>
        </header>
        <div class="wpstg-callout wpstg-callout-info wpstg-mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            <div>
                <div class="wpstg-text-sm wpstg-font-semibold"><?php echo esc_html__('Quick tip', 'wp-staging'); ?></div>
                <div class="wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400">
                    <?php echo esc_html__('Not sure what to choose? Pick', 'wp-staging'); ?>
                    <span class="wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100">SFTP</span>
                    <?php echo esc_html__('. It works on most servers and needs no certificates.', 'wp-staging'); ?>
                </div>
            </div>
        </div>

        <form class="wpstg-space-y-6" id="wpstg-provider-settings-form">
            <div id="wpstg-provider-test-connection-fields" class="wpstg-space-y-6">
                <section class="wpstg-card wpstg-card-body">
                    <h2 class="wpstg-text-2xl wpstg-font-semibold wpstg-tracking-tight wpstg-text-slate-900 dark:wpstg-text-slate-100 wpstg-m-0"><?php echo esc_html__('Connection type', 'wp-staging'); ?></h2>
                    <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400"><?php echo esc_html__('Select how your server accepts uploads.', 'wp-staging'); ?></p>
                    <input type="hidden" name="provider" value="<?php echo esc_attr($providerId); ?>" />
                    <input id="wpstg-connection-type" type="hidden" name="ftp_type" value="<?php echo esc_attr($ftpType); ?>" />
                    <div class="wpstg-mt-4 wpstg-grid wpstg-gap-3 sm:wpstg-grid-cols-2">
                        <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-protocol-sftp" class="wpstg-radio-card">
                            <div class="wpstg-checkbox-card-content">
                                <div class="wpstg-flex wpstg-items-center wpstg-gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="wpstg-h-5 wpstg-w-5 wpstg-text-primary"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path></svg>
                                    <span class="wpstg-text-sm wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100">SFTP</span>
                                    <span class="wpstg-badge wpstg-badge-green wpstg-ml-0"><?php echo esc_html__('Recommended', 'wp-staging'); ?></span>
                                </div>
                                <p class="wpstg-mt-1 wpstg-mb-0 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400"><?php echo esc_html__('Secure upload via SSH. No certificate setup.', 'wp-staging'); ?></p>
                            </div>
                            <input name="connection_type" id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-protocol-sftp" type="radio" value="<?php echo esc_attr(Auth::CONNECTION_TYPE_SFTP); ?>" class="wpstg-radio" <?php checked($ftpType, Auth::CONNECTION_TYPE_SFTP); ?> />
                        </label>
                        <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-protocol-ftp" class="wpstg-radio-card">
                            <div class="wpstg-checkbox-card-content">
                                <div class="wpstg-flex wpstg-items-center wpstg-gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="wpstg-h-5 wpstg-w-5 wpstg-text-muted-foreground"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>                                    <span class="wpstg-text-sm wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100">FTPS</span>
                                    <span class="wpstg-badge wpstg-badge-blue wpstg-ml-0">TLS</span>
                                </div>
                                <p class="wpstg-mt-1 wpstg-mb-0 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400"><?php echo esc_html__('Secure upload via FTP + TLS. Some dev servers use self-signed certificates.', 'wp-staging'); ?></p>
                            </div>
                            <input name="connection_type" id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-protocol-ftp" type="radio" value="<?php echo esc_attr(Auth::CONNECTION_TYPE_FTP); ?>" class="wpstg-radio" <?php checked($ftpType, Auth::CONNECTION_TYPE_FTP); ?> />
                        </label>
                    </div>
                    <div class="wpstg-callout wpstg-callout-info wpstg-gap-2 wpstg-p-3 wpstg-text-sm wpstg-mt-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="wpstg-h-5 wpstg-w-5" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                        <span class="wpstg-only-sftp <?php echo $isSftp ? '' : 'hidden'; ?>"><?php echo esc_html__('SFTP uses port 22 by default. It\'s the simplest and most secure option.', 'wp-staging'); ?></span>
                        <span class="wpstg-only-ftp <?php echo $isSftp ? 'hidden' : ''; ?>"> <?php echo esc_html__('FTPS uses port 21 (explicit) or 990 (implicit). Certificate configuration may be required.', 'wp-staging'); ?></span>
                    </div>
                </section>
                <section class="wpstg-card wpstg-card-body">
                    <h2 class="wpstg-text-2xl wpstg-font-semibold wpstg-tracking-tight wpstg-text-slate-900 dark:wpstg-text-slate-100 wpstg-m-0"><?php echo esc_html__('Server connection', 'wp-staging'); ?></h2>
                    <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400"><?php echo esc_html__('Enter the connection details for your remote server. Your hosting provider can provide these values.', 'wp-staging'); ?></p>
                    <div class="wpstg-mt-4 wpstg-grid wpstg-gap-4 sm:wpstg-grid-cols-2">
                        <div>
                            <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-host" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php echo esc_html__('Server address', 'wp-staging'); ?></label>
                            <input type="text" name="host" placeholder="example.com" value="<?php echo esc_attr($host); ?>" id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-host" class="wpstg-input wpstg-input-lg wpstg-w-full" />
                            <p class="wpstg-only-ftp <?php echo $isSftp ? 'hidden' : ''; ?> wpstg-mt-1 wpstg-mb-0 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400"><?php echo esc_html__('Use a domain name when possible. For FTPS, SSL certificate checks may fail when using an IP address.', 'wp-staging'); ?></p>
                        </div>
                        <div>
                            <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-port" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php echo esc_html__('Port', 'wp-staging'); ?></label>
                            <input type="number" name="port" value="<?php echo esc_attr($port); ?>" id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-port" class="wpstg-input wpstg-input-lg wpstg-w-full" />
                            <p class="wpstg-only-ftp <?php echo $isSftp ? 'hidden' : ''; ?> wpstg-mt-1 wpstg-mb-0 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400"><?php echo esc_html__('Default: 21', 'wp-staging'); ?></p>
                            <p class="wpstg-only-sftp <?php echo $isSftp ? '' : 'hidden'; ?> wpstg-mt-1 wpstg-mb-0 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400"><?php echo esc_html__('Default: 22', 'wp-staging'); ?></p>
                        </div>
                        <div>
                            <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-username" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php echo esc_html__('Username', 'wp-staging'); ?></label>
                            <input type="text" name="username" placeholder="SSH User" value="<?php echo esc_attr($username); ?>" id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-username" class="wpstg-input wpstg-input-lg wpstg-w-full" />
                        </div>
                        <div id="wpstg-sftp-password-container" class="<?php echo $hasKey && $useSshKey ? 'wpstg-field-blurred' : ''; ?>">
                            <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-password" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php echo esc_html__('Password', 'wp-staging'); ?></label>
                            <div class="wpstg-password-toggle-wrapper">
                                <input type="password" name="password" placeholder="••••••••" value="<?php echo esc_attr($password); ?>" id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-password" class="wpstg-input wpstg-input-lg wpstg-w-full" />
                                <?php require WPSTG_VIEWS_DIR . '_main/partials/password-toggle-button.php'; ?>
                            </div>
                            <p class="wpstg-only-sftp <?php echo $isSftp ? '' : 'hidden'; ?> wpstg-mt-1 wpstg-mb-0 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400"><?php echo esc_html__('You can use an SSH private key instead of a password.', 'wp-staging'); ?></p>
                        </div>
                        <!-- SSH key toggle — between password and remote path -->
                        <label class="wpstg-only-sftp <?php echo $isSftp ? '' : 'hidden'; ?> sm:wpstg-col-span-2 wpstg-flex wpstg-cursor-pointer wpstg-items-center wpstg-gap-3 wpstg-px-4 wpstg-py-3 wpstg-rounded-xl wpstg-border wpstg-border-solid wpstg-border-slate-200 dark:wpstg-border-slate-700">
                            <?php Toggle::render('wpstg-use-ssh-key-toggle', 'use_ssh_key', 'true', $useSshKey, ['classes' => '']); ?>
                            <div class="wpstg-text-sm wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100"><?php echo esc_html__('Use SSH private key', 'wp-staging'); ?></div>
                        </label>

                        <!-- SSH private key card — SFTP only, hidden when toggle is off -->
                        <div id="wpstg-ssh-key-card" class="wpstg-only-sftp <?php echo ($isSftp && $hasKey && $useSshKey) ? '' : 'hidden'; ?> sm:wpstg-col-span-2 wpstg-rounded-xl wpstg-border wpstg-border-solid wpstg-border-slate-200 dark:wpstg-border-slate-700 wpstg-bg-slate-50 dark:wpstg-bg-slate-800/50 wpstg-px-5 wpstg-py-4" <?php echo $hasWpConfigKey ? 'data-has-wpconfig-key="1"' : ''; ?>>
                            <h3 class="wpstg-text-lg wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100 wpstg-m-0"><?php echo esc_html__('SSH private key', 'wp-staging'); ?></h3>
                            <p class="wpstg-mt-0.5 wpstg-mb-0 wpstg-text-sm wpstg-text-slate-500 dark:wpstg-text-slate-400"><?php echo esc_html__('Store private key encrypted in DB or save it in the WPSTG_STORAGE_SFTP_KEY in wp-config.php (most secure).', 'wp-staging'); ?></p>

                            <!-- SSH Key fields -->
                            <div class="wpstg-mt-3 wpstg-space-y-3" id="wpstg-ssh-key-panel">

                                <div class="wpstg-inline-flex wpstg-gap-1 wpstg-rounded-xl wpstg-border wpstg-border-solid wpstg-border-slate-200/80 dark:wpstg-border-slate-700/80 wpstg-bg-slate-100/80 dark:wpstg-bg-slate-900/60 wpstg-p-1">
                                    <button type="button" class="wpstg-ssh-key-tab <?php echo !$hasWpConfigKey ? 'wpstg-ssh-key-tab--active' : 'wpstg-opacity-50 wpstg-cursor-not-allowed'; ?> wpstg-rounded-lg wpstg-px-4 wpstg-py-2 wpstg-text-xs wpstg-font-semibold wpstg-border-0 wpstg-cursor-pointer" data-tab="paste" <?php echo $hasWpConfigKey ? 'disabled' : ''; ?>><?php echo esc_html__('Paste key', 'wp-staging'); ?></button>
                                    <button type="button" class="wpstg-ssh-key-tab <?php echo $hasWpConfigKey ? 'wpstg-opacity-50 wpstg-cursor-not-allowed' : ''; ?> wpstg-rounded-lg wpstg-px-4 wpstg-py-2 wpstg-text-xs wpstg-font-medium wpstg-border-0 wpstg-cursor-pointer" data-tab="upload" <?php echo $hasWpConfigKey ? 'disabled' : ''; ?>><?php echo esc_html__('Upload file', 'wp-staging'); ?></button>
                                    <button type="button" class="wpstg-ssh-key-tab <?php echo $hasWpConfigKey ? 'wpstg-ssh-key-tab--active' : ''; ?> wpstg-rounded-lg wpstg-px-4 wpstg-py-2 wpstg-text-xs wpstg-font-medium wpstg-border-0 wpstg-cursor-pointer" data-tab="wpconfig"><?php echo esc_html__('WPSTG_STORAGE_SFTP_KEY', 'wp-staging'); ?></button>
                                </div>

                                <!-- Tab: Paste Key -->
                                <div class="wpstg-ssh-key-panel <?php echo $hasWpConfigKey ? 'wpstg-hidden' : ''; ?>" data-panel="paste">
                                    <?php if ($hasDbKey && !$hasWpConfigKey) : ?>
                                        <div class="wpstg-flex wpstg-items-center wpstg-gap-2 wpstg-mb-3 wpstg-px-3 wpstg-py-2 wpstg-rounded-lg wpstg-bg-emerald-50/60 dark:wpstg-bg-emerald-900/20 wpstg-text-xs wpstg-font-medium wpstg-text-emerald-700 dark:wpstg-text-emerald-400">
                                            <svg class="wpstg-h-3.5 wpstg-w-3.5 wpstg-flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            <?php echo esc_html__('SSH private key is stored encrypted in the database.', 'wp-staging'); ?>
                                        </div>
                                        <button type="button" class="wpstg-btn-link-danger wpstg-remove-stored-key" data-field="key"><?php echo esc_html__('Remove key from database', 'wp-staging'); ?></button>
                                    <?php else : ?>
                                        <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-key-textarea" class="wpstg-mb-1 wpstg-block wpstg-text-sm wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100"><?php echo esc_html__('Private key', 'wp-staging'); ?></label>
                                        <textarea name="key" id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-key-textarea" rows="4" class="wpstg-w-full wpstg-rounded-lg wpstg-border wpstg-border-solid wpstg-border-slate-300 dark:wpstg-border-slate-600 wpstg-bg-white dark:wpstg-bg-slate-900 wpstg-px-3 wpstg-py-2 wpstg-font-mono wpstg-text-xs wpstg-leading-5 wpstg-text-slate-700 dark:wpstg-text-slate-300" placeholder="-----BEGIN PRIVATE KEY-----&#10;...&#10;-----END PRIVATE KEY-----"></textarea>
                                        <div class="wpstg-flex wpstg-items-center wpstg-gap-2 wpstg-mt-2 wpstg-px-3 wpstg-py-2 wpstg-rounded-lg wpstg-bg-slate-100/60 dark:wpstg-bg-slate-800/40 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="wpstg-h-3.5 wpstg-w-3.5 wpstg-flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            <?php echo esc_html__('Your private key is encrypted before it is stored.', 'wp-staging'); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Tab: Upload Key File -->
                                <div class="wpstg-ssh-key-panel wpstg-hidden" data-panel="upload">
                                    <label class="wpstg-mb-1 wpstg-block wpstg-text-sm wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100"><?php echo esc_html__('Upload private key file', 'wp-staging'); ?></label>
                                    <input type="file" name="key_file" id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-key-file" class="wpstg-mt-1 file:wpstg-mr-3 dark:file:wpstg-bg-slate-600 dark:file:wpstg-text-slate-200 dark:hover:file:wpstg-bg-slate-500 file:wpstg-border-0 file:wpstg-py-1.5 file:wpstg-px-3 file:wpstg-rounded-lg hover:file:wpstg-bg-slate-100 hover:file:wpstg-cursor-pointer wpstg-text-sm" />
                                    <div class="wpstg-flex wpstg-items-center wpstg-gap-2 wpstg-mt-2 wpstg-px-3 wpstg-py-2 wpstg-rounded-lg wpstg-bg-slate-100/60 dark:wpstg-bg-slate-800/40 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="wpstg-h-3.5 wpstg-w-3.5 wpstg-flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        <?php echo esc_html__('Read into memory, encrypted, stored in database. Never saved to disk.', 'wp-staging'); ?>
                                    </div>
                                </div>

                                <!-- Tab: wp-config.php -->
                                <div class="wpstg-ssh-key-panel <?php echo $hasWpConfigKey ? '' : 'wpstg-hidden'; ?>" data-panel="wpconfig">
                                    <?php if ($hasWpConfigKey) : ?>
                                        <div class="wpstg-flex wpstg-items-center wpstg-gap-2 wpstg-mb-3 wpstg-px-3 wpstg-py-2 wpstg-rounded-lg wpstg-bg-emerald-50/60 dark:wpstg-bg-emerald-900/20 wpstg-text-xs wpstg-font-medium wpstg-text-emerald-700 dark:wpstg-text-emerald-400">
                                            <svg class="wpstg-h-3.5 wpstg-w-3.5 wpstg-flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            <?php echo esc_html__('WPSTG_STORAGE_SFTP_KEY constant is defined and will be used.', 'wp-staging'); ?>
                                        </div>
                                    <?php endif; ?>
                                    <p class="wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400 wpstg-m-0"><?php echo esc_html__('Add this constant to your wp-config.php for the most secure storage:', 'wp-staging'); ?></p>
                                    <div class="wpstg-relative wpstg-mt-2">
                                        <textarea id="wpstg-sftp-wpconfig-snippet" readonly rows="4" class="wpstg-w-full wpstg-rounded-lg wpstg-border wpstg-border-solid wpstg-border-slate-200 dark:wpstg-border-slate-700 wpstg-bg-slate-100 dark:wpstg-bg-slate-800 wpstg-px-3 wpstg-py-3 wpstg-pr-20 wpstg-font-mono wpstg-text-xs wpstg-leading-5 wpstg-text-slate-700 dark:wpstg-text-slate-300 wpstg-resize-none">define('WPSTG_STORAGE_SFTP_KEY', '-----BEGIN RSA PRIVATE KEY-----
...your key content here...
-----END RSA PRIVATE KEY-----');</textarea>
                                        <button type="button" id="wpstg-sftp-wpconfig-copy-btn" class="wpstg-absolute wpstg-top-2 wpstg-right-2 wpstg-btn wpstg-btn-sm wpstg-btn-ghost wpstg-flex wpstg-items-center wpstg-gap-1" aria-label="<?php echo esc_attr__('Copy snippet to clipboard', 'wp-staging'); ?>">
                                            <svg class="wpstg-copy-icon wpstg-h-4 wpstg-w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                            <svg class="wpstg-check-icon wpstg-h-4 wpstg-w-4 wpstg-hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span class="wpstg-copy-btn-text wpstg-text-xs"><?php echo esc_html__('Copy', 'wp-staging'); ?></span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Passphrase -->
                                <div>
                                    <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-passphrase" class="wpstg-mb-1 wpstg-block wpstg-text-sm wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100"><?php echo esc_html__('Key Passphrase', 'wp-staging'); ?></label>
                                    <input type="password" name="passphrase" id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-passphrase" value="<?php echo esc_attr($passphrase); ?>" placeholder="Leave empty if your key has no passphrase" class="wpstg-input wpstg-input-lg wpstg-w-full" />
                                </div>
                            </div>
                        </div>

                        <!-- Verify server identity — SFTP only, hidden when toggle is off -->
                        <div id="wpstg-verify-server-card" class="wpstg-only-sftp <?php echo ($isSftp && $hasKey && $useSshKey) ? '' : 'hidden'; ?> sm:wpstg-col-span-2 wpstg-mt-3 wpstg-rounded-xl wpstg-border wpstg-border-solid wpstg-border-slate-200 dark:wpstg-border-slate-700 wpstg-bg-slate-50 dark:wpstg-bg-slate-800/50 wpstg-px-5 wpstg-py-4">
                            <h3 class="wpstg-text-lg wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100 wpstg-m-0"><?php echo esc_html__('Verify server identity', 'wp-staging'); ?></h3>
                            <p class="wpstg-mt-0.5 wpstg-mb-0 wpstg-text-sm wpstg-text-slate-500 dark:wpstg-text-slate-400"><?php echo esc_html__('Optional, but recommended to avoid connecting to the wrong server.', 'wp-staging'); ?></p>
                            <div class="wpstg-mt-3">
                                <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-fingerprint" class="wpstg-mb-1 wpstg-block wpstg-text-sm wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100"><?php echo esc_html__('Host key fingerprint', 'wp-staging'); ?></label>
                                <input type="text" name="fingerprint" id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-fingerprint" placeholder="SHA256:DjBdHy9kchOX+3P+BrIL9jp+t8Ri5yYBz0Bsm6wRr4jQ" value="<?php echo esc_attr($fingerprint); ?>" class="wpstg-input wpstg-input-lg wpstg-w-full" />
                                <p class="wpstg-mt-1.5 wpstg-mb-0 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400"><?php echo esc_html__('Your host can provide this fingerprint.', 'wp-staging'); ?></p>
                            </div>
                        </div>

                        <div class="sm:wpstg-col-span-2">
                            <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-folder-name" class="wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php echo esc_html__('Backup destination', 'wp-staging'); ?></label>
                            <input type="text" name="location" id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-folder-name" placeholder="<?php echo esc_attr('wpstg-backups/' . parse_url(site_url(), PHP_URL_HOST)); ?>" value="<?php echo esc_attr($location); ?>" class="wpstg-input wpstg-input-lg wpstg-w-full" />
                            <p class="wpstg-mt-1 wpstg-mb-0 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400"><?php echo esc_html__('Use a relative path from the FTP home directory, such as `backups/domain.com`, or an absolute path, such as `/home/user/backups/domain.com`. The folder is created automatically if it does not exist.', 'wp-staging'); ?></p>
                        </div>
                    </div>
                    <div class="wpstg-callout wpstg-callout-warning wpstg-mt-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="wpstg-h-5 wpstg-w-5 wpstg-flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                        <div>
                            <div class="wpstg-text-sm wpstg-font-semibold"><?php echo esc_html__('Use separate backup destinations', 'wp-staging'); ?></div>
                            <p class="wpstg-mt-0.5 wpstg-mb-0 wpstg-text-sm"><?php echo esc_html__('Sharing the same remote path across multiple websites or clients can expose backups to the wrong site. Always use a dedicated subfolder for each website, e.g. /backups/domain-xy.com', 'wp-staging'); ?></p>
                        </div>
                    </div>
                </section>
                <section id="wpstg-ftp-options-<?php echo esc_attr($providerId); ?>" class="wpstg-card wpstg-card-body wpstg-only-ftp <?php echo $isSftp ? 'hidden' : ''; ?>">
                    <div class="wpstg-flex wpstg-w-full wpstg-items-center wpstg-justify-between wpstg-gap-4 wpstg-cursor-pointer wpstg-toggle-header">
                        <div>
                            <h2 class="wpstg-text-2xl wpstg-font-semibold wpstg-tracking-tight wpstg-text-slate-900 dark:wpstg-text-slate-100 wpstg-m-0"><?php echo esc_html__('FTP options', 'wp-staging'); ?></h2>
                            <p class="wpstg-mt-1 wpstg-mb-0 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400"><?php echo esc_html__('Advanced connection settings.', 'wp-staging'); ?></p>
                        </div>
                        <div class="wpstg-flex wpstg-h-9 wpstg-w-9 wpstg-flex-shrink-0 wpstg-items-center wpstg-justify-center wpstg-rounded-full wpstg-border wpstg-border-solid wpstg-border-slate-200/60 dark:wpstg-border-slate-700/60 wpstg-text-slate-400 dark:wpstg-text-slate-500" data-toggle-id="wpstg-ftp-options">
                            <svg xmlns="http://www.w3.org/2000/svg" class="wpstg-h-4 wpstg-w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>
                    <div class="wpstg-hidden wpstg-mt-4" id="wpstg-ftp-options">
                        <label class="wpstg-flex wpstg-cursor-pointer wpstg-items-center wpstg-justify-between wpstg-gap-4 wpstg-py-3">
                            <div>
                                <div class="wpstg-text-sm wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100"><?php echo esc_html__('Enable SSL/TLS (FTPS)', 'wp-staging'); ?></div>
                                <div class="wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400"><?php echo esc_html__('Encrypts the FTP connection. Recommended for secure file transfers.', 'wp-staging'); ?></div>
                            </div>
                            <?php Toggle::render("wpstg-storage-provider-{$providerId}-ssl", 'ssl', 'true', $ssl === true, ['classes' => '']); ?>
                        </label>
                        <label class="wpstg-flex wpstg-cursor-pointer wpstg-items-center wpstg-justify-between wpstg-gap-4 wpstg-py-3">
                            <div>
                                <div class="wpstg-text-sm wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100"><?php echo esc_html__('Passive mode', 'wp-staging'); ?></div>
                                <div class="wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400"><?php echo esc_html__('Required for most servers behind firewalls. Keep this on unless your host says otherwise.', 'wp-staging'); ?></div>
                            </div>
                            <?php Toggle::render("wpstg-storage-provider-{$providerId}-passive", 'passive', 'true', $passive === true, ['classes' => '']); ?>
                        </label>
                        <label class="wpstg-flex wpstg-cursor-pointer wpstg-items-center wpstg-justify-between wpstg-gap-4 wpstg-py-3">
                            <div>
                                <div class="wpstg-text-sm wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100"><?php echo esc_html__('Use PHP FTP extension', 'wp-staging'); ?></div>
                                <div class="wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400"><?php echo esc_html__('Try this if the default cURL connection fails. Requires PHP FTP extension.', 'wp-staging'); ?></div>
                            </div>
                            <?php Toggle::render("wpstg-storage-provider-{$providerId}-use-ftp-extension", 'use_ftp_extension', 'true', $useFtpExtension === true, ['classes' => '']); ?>
                        </label>
                        <div id="wpstg-ftp-curl-upload-modes" class="wpstg-flex wpstg-items-center wpstg-justify-between wpstg-gap-4 wpstg-py-3 <?php echo $useFtpExtension ? 'wpstg-hidden' : ''; ?>">
                            <div>
                                <div class="wpstg-text-sm wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100"><?php echo esc_html__('Upload mode', 'wp-staging'); ?></div>
                                <div class="wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400"><?php echo esc_html__('How files are uploaded. PUT mode works for most servers.', 'wp-staging'); ?></div>
                            </div>
                            <select name="ftp_mode" id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-ftp-mode" class="wpstg-input wpstg-w-48">
                                <?php foreach ($ftpModeOptions as $modeValue => $modeLabel) : ?>
                                    <option value="<?php echo esc_attr((string)$modeValue); ?>" <?php selected($ftpMode, $modeValue); ?>><?php echo esc_html($modeLabel); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </section>
                <?php $hasWpConfigCert = defined('WPSTG_STORAGE_FTPS_CERT') && WPSTG_STORAGE_FTPS_CERT !== ''; ?>
                <?php $hasDbCert = !empty($ftpCertContent); ?>
                <?php $hasCert = $hasDbCert || $hasWpConfigCert; ?>
                <section id="wpstg-ftps-security-<?php echo esc_attr($providerId); ?>" class="wpstg-card wpstg-card-body wpstg-only-ftp <?php echo $isSftp ? 'hidden' : ''; ?>">
                    <div class="wpstg-flex wpstg-items-start wpstg-justify-between wpstg-gap-4">
                        <div>
                            <h2 class="wpstg-text-2xl wpstg-font-semibold wpstg-tracking-tight wpstg-text-slate-900 dark:wpstg-text-slate-100 wpstg-m-0"><?php echo esc_html__('FTPS security', 'wp-staging'); ?></h2>
                            <p class="wpstg-mt-1 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400"><?php echo esc_html__('Recommended for real websites. For dev servers with self-signed certificates, upload the certificate below.', 'wp-staging'); ?></p>
                        </div>
                        <span class="wpstg-badge wpstg-badge-blue">FTPS</span>
                    </div>
                    <label class="wpstg-mt-4 wpstg-flex wpstg-cursor-pointer wpstg-items-center wpstg-justify-between wpstg-gap-4 wpstg-rounded-xl wpstg-border wpstg-border-solid wpstg-border-slate-200 dark:wpstg-border-slate-700 wpstg-px-4 wpstg-py-3">
                        <div>
                            <div class="wpstg-text-sm wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100"><?php echo esc_html__('Verify server certificate', 'wp-staging'); ?></div>
                            <div class="wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400"><?php echo esc_html__('Keep this on. Turn it off only if you understand the risk.', 'wp-staging'); ?></div>
                        </div>
                        <?php Toggle::render("wpstg-storage-provider-{$providerId}-verify-cert", 'verify_cert', 'true', $verifyCert === true, ['classes' => '']); ?>
                    </label>

                    <!-- Certificate input — three-tab pattern matching SSH key section -->
                    <div class="wpstg-mt-4 wpstg-rounded-xl wpstg-border wpstg-border-solid wpstg-border-slate-200 dark:wpstg-border-slate-700 wpstg-bg-slate-50 dark:wpstg-bg-slate-800/50 wpstg-px-5 wpstg-py-4">
                        <h3 class="wpstg-text-lg wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100 wpstg-m-0"><?php echo esc_html__('Custom certificate', 'wp-staging'); ?></h3>
                        <p class="wpstg-mt-0.5 wpstg-mb-0 wpstg-text-sm wpstg-text-slate-500 dark:wpstg-text-slate-400"><?php echo esc_html__('For dev servers with self-signed certificates. This keeps the connection secure and avoids "Error 60".', 'wp-staging'); ?></p>

                        <div class="wpstg-mt-3 wpstg-space-y-3" id="wpstg-ftp-cert-panel">
                            <!-- Segmented control -->
                            <div class="wpstg-inline-flex wpstg-gap-1 wpstg-rounded-xl wpstg-border wpstg-border-solid wpstg-border-slate-200/80 dark:wpstg-border-slate-700/80 wpstg-bg-slate-100/80 dark:wpstg-bg-slate-900/60 wpstg-p-1">
                                <button type="button" class="wpstg-cert-tab <?php echo !$hasWpConfigCert ? 'wpstg-cert-tab--active' : 'wpstg-opacity-50 wpstg-cursor-not-allowed'; ?> wpstg-rounded-lg wpstg-px-4 wpstg-py-2 wpstg-text-xs wpstg-font-semibold wpstg-border-0 wpstg-cursor-pointer" data-cert-tab="paste" <?php echo $hasWpConfigCert ? 'disabled' : ''; ?>><?php echo esc_html__('Paste certificate', 'wp-staging'); ?></button>
                                <button type="button" class="wpstg-cert-tab <?php echo $hasWpConfigCert ? 'wpstg-opacity-50 wpstg-cursor-not-allowed' : ''; ?> wpstg-rounded-lg wpstg-px-4 wpstg-py-2 wpstg-text-xs wpstg-font-medium wpstg-border-0 wpstg-cursor-pointer" data-cert-tab="upload" <?php echo $hasWpConfigCert ? 'disabled' : ''; ?>><?php echo esc_html__('Upload file', 'wp-staging'); ?></button>
                                <button type="button" class="wpstg-cert-tab <?php echo $hasWpConfigCert ? 'wpstg-cert-tab--active' : ''; ?> wpstg-rounded-lg wpstg-px-4 wpstg-py-2 wpstg-text-xs wpstg-font-medium wpstg-border-0 wpstg-cursor-pointer" data-cert-tab="wpconfig"><?php echo esc_html__('Load from wp-config.php', 'wp-staging'); ?></button>
                            </div>

                            <!-- Tab: Paste certificate -->
                            <div class="wpstg-cert-panel <?php echo $hasWpConfigCert ? 'wpstg-hidden' : ''; ?>" data-cert-panel="paste">
                                <?php if ($hasDbCert && !$hasWpConfigCert) : ?>
                                    <div class="wpstg-flex wpstg-items-center wpstg-gap-2 wpstg-mb-3 wpstg-px-3 wpstg-py-2 wpstg-rounded-lg wpstg-bg-emerald-50/60 dark:wpstg-bg-emerald-900/20 wpstg-text-xs wpstg-font-medium wpstg-text-emerald-700 dark:wpstg-text-emerald-400">
                                        <svg class="wpstg-h-3.5 wpstg-w-3.5 wpstg-flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        <?php echo esc_html__('FTPS certificate is stored encrypted in the database.', 'wp-staging'); ?>
                                    </div>
                                    <button type="button" class="wpstg-btn-link-danger wpstg-remove-stored-key" data-field="ftpCertContent"><?php echo esc_html__('Remove certificate from database', 'wp-staging'); ?></button>
                                <?php else : ?>
                                    <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-cert-textarea" class="wpstg-mb-1 wpstg-block wpstg-text-sm wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100"><?php echo esc_html__('Certificate (PEM)', 'wp-staging'); ?></label>
                                    <textarea name="ftp_cert_content" id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-cert-textarea" rows="4" class="wpstg-w-full wpstg-rounded-lg wpstg-border wpstg-border-solid wpstg-border-slate-300 dark:wpstg-border-slate-600 wpstg-bg-white dark:wpstg-bg-slate-900 wpstg-px-3 wpstg-py-2 wpstg-font-mono wpstg-text-xs wpstg-leading-5 wpstg-text-slate-700 dark:wpstg-text-slate-300" placeholder="-----BEGIN CERTIFICATE-----&#10;...&#10;-----END CERTIFICATE-----"></textarea>
                                    <div class="wpstg-flex wpstg-items-center wpstg-gap-2 wpstg-mt-2 wpstg-px-3 wpstg-py-2 wpstg-rounded-lg wpstg-bg-slate-100/60 dark:wpstg-bg-slate-800/40 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="wpstg-h-3.5 wpstg-w-3.5 wpstg-flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        <?php echo esc_html__('Your certificate is encrypted before it is stored.', 'wp-staging'); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab: Upload file -->
                            <div class="wpstg-cert-panel wpstg-hidden" data-cert-panel="upload">
                                <label class="wpstg-mb-1 wpstg-block wpstg-text-sm wpstg-font-semibold wpstg-text-slate-900 dark:wpstg-text-slate-100"><?php echo esc_html__('Upload certificate file', 'wp-staging'); ?></label>
                                <input type="file" accept=".crt,.pem" name="ftp-certificate-file" id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-cert-file" class="wpstg-mt-1 file:wpstg-mr-3 dark:file:wpstg-bg-slate-600 dark:file:wpstg-text-slate-200 dark:hover:file:wpstg-bg-slate-500 file:wpstg-border-0 file:wpstg-py-1.5 file:wpstg-px-3 file:wpstg-rounded-lg hover:file:wpstg-bg-slate-100 hover:file:wpstg-cursor-pointer wpstg-text-sm" />
                                <p class="wpstg-mt-1 wpstg-mb-0 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400"><?php echo esc_html__('Accepted formats: .crt, .pem', 'wp-staging'); ?></p>
                                <div class="wpstg-flex wpstg-items-center wpstg-gap-2 wpstg-mt-2 wpstg-px-3 wpstg-py-2 wpstg-rounded-lg wpstg-bg-slate-100/60 dark:wpstg-bg-slate-800/40 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="wpstg-h-3.5 wpstg-w-3.5 wpstg-flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <?php echo esc_html__('Read into memory, encrypted, stored in database. Never saved to disk.', 'wp-staging'); ?>
                                </div>
                            </div>

                            <!-- Tab: wp-config.php -->
                            <div class="wpstg-cert-panel <?php echo $hasWpConfigCert ? '' : 'wpstg-hidden'; ?>" data-cert-panel="wpconfig">
                                <?php if ($hasWpConfigCert) : ?>
                                    <div class="wpstg-flex wpstg-items-center wpstg-gap-2 wpstg-mb-3 wpstg-px-3 wpstg-py-2 wpstg-rounded-lg wpstg-bg-emerald-50/60 dark:wpstg-bg-emerald-900/20 wpstg-text-xs wpstg-font-medium wpstg-text-emerald-700 dark:wpstg-text-emerald-400">
                                        <svg class="wpstg-h-3.5 wpstg-w-3.5 wpstg-flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        <?php echo esc_html__('WPSTG_STORAGE_FTPS_CERT constant is defined and will be used.', 'wp-staging'); ?>
                                    </div>
                                <?php endif; ?>
                                <p class="wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400 wpstg-m-0"><?php echo esc_html__('Add this constant to your wp-config.php for the most secure storage:', 'wp-staging'); ?></p>
                                <div class="wpstg-relative wpstg-mt-2">
                                    <textarea id="wpstg-ftps-wpconfig-snippet" readonly rows="4" class="wpstg-w-full wpstg-rounded-lg wpstg-border wpstg-border-solid wpstg-border-slate-200 dark:wpstg-border-slate-700 wpstg-bg-slate-100 dark:wpstg-bg-slate-800 wpstg-px-3 wpstg-py-3 wpstg-pr-20 wpstg-font-mono wpstg-text-xs wpstg-leading-5 wpstg-text-slate-700 dark:wpstg-text-slate-300 wpstg-resize-none">define('WPSTG_STORAGE_FTPS_CERT', '-----BEGIN CERTIFICATE-----
...your certificate content here...
-----END CERTIFICATE-----');</textarea>
                                    <button type="button" id="wpstg-ftps-wpconfig-copy-btn" class="wpstg-absolute wpstg-top-2 wpstg-right-2 wpstg-btn wpstg-btn-sm wpstg-btn-ghost wpstg-flex wpstg-items-center wpstg-gap-1" aria-label="<?php echo esc_attr__('Copy snippet to clipboard', 'wp-staging'); ?>">
                                        <svg class="wpstg-copy-icon wpstg-h-4 wpstg-w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        <svg class="wpstg-check-icon wpstg-h-4 wpstg-w-4 wpstg-hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span class="wpstg-copy-btn-text wpstg-text-xs"><?php echo esc_html__('Copy', 'wp-staging'); ?></span>
                                    </button>
                                </div>
                                <p class="wpstg-mt-1.5 wpstg-mb-0 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400"><?php echo esc_html__('This takes priority over certificates stored in the database.', 'wp-staging'); ?></p>
                            </div>
                        </div>
                    </div>

                </section>
                <section class="wpstg-card wpstg-card-body">
                    <h2 class="wpstg-text-2xl wpstg-font-semibold wpstg-tracking-tight wpstg-text-slate-900 dark:wpstg-text-slate-100 wpstg-m-0"><?php echo esc_html__('Backup retention', 'wp-staging'); ?></h2>
                    <p class="wpstg-mt-1 wpstg-mb-0 wpstg-text-sm wpstg-text-slate-600 dark:wpstg-text-slate-400"><?php echo esc_html__('Control how many backups to keep on your server.', 'wp-staging'); ?></p>
                    <div class="wpstg-flex wpstg-items-center wpstg-justify-between wpstg-mt-4">
                        <div>
                            <label for="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-max-backups" class="wpstg-justify-between wpstg-block wpstg-text-sm wpstg-font-medium wpstg-text-slate-800 dark:wpstg-text-slate-200"><?php echo esc_html__('Maximum backups to keep', 'wp-staging'); ?></label>
                            <p class="wpstg-mt-1 wpstg-mb-0 wpstg-text-xs wpstg-text-slate-500 dark:wpstg-text-slate-400"><?php echo esc_html__('Older backups are automatically deleted when this limit is reached.', 'wp-staging'); ?></p>
                        </div>
                        <input type="number" name="max_backups_to_keep" id="wpstg-storage-provider-<?php echo esc_attr($providerId); ?>-max-backups" placeholder="15" min="1" max="100" value="<?php echo esc_attr($maxBackupsToKeep); ?>" class="wpstg-input wpstg-input-lg wpstg-w-1/4" />
                    </div>
                </section>
            </div>
            <section id="wpstg-static-action-buttons" class="wpstg-card wpstg-card-body">
                <div class="wpstg-flex wpstg-gap-3 wpstg-items-center">
                    <button type="button" id="wpstg-btn-save-provider-settings" class="wpstg-btn wpstg-btn-md wpstg-btn-primary"><?php echo esc_html__('Save Settings', 'wp-staging'); ?></button>
                    <button type="button" id="wpstg-btn-provider-test-connection" class="!wpstg-mb-0 wpstg-btn wpstg-btn-md wpstg-btn-outline"><?php echo esc_html__('Test connection', 'wp-staging'); ?></button>
                    <span class="wpstg-action-badge-inline"><?php require_once WPSTG_VIEWS_DIR . "pro/settings/tabs/storages/last-saved-notice.php"; ?></span>
                    <button type="button" id="wpstg-btn-delete-provider-settings" class="wpstg-btn-link-danger wpstg-ml-auto"><?php echo esc_html__('Delete Settings', 'wp-staging'); ?></button>
                </div>
            </section>
        </form>
    </div>
</div>
<!-- Sticky action bar — visible when minimum fields are populated -->
<div id="wpstg-sticky-action-bar" class="wpstg-sticky-action-bar">
    <button type="button" id="wpstg-btn-save-provider-settings-sticky" class="wpstg-btn wpstg-btn-md wpstg-btn-primary"><?php echo esc_html__('Save Settings', 'wp-staging'); ?></button>
    <button type="button" id="wpstg-btn-provider-test-connection-sticky" class="!wpstg-mb-0 wpstg-btn wpstg-btn-md wpstg-btn-outline"><?php echo esc_html__('Test connection', 'wp-staging'); ?></button>
</div>
<!-- Tab switching, file uploads, and copy handlers are in wpstg-remote-storage.js -->
