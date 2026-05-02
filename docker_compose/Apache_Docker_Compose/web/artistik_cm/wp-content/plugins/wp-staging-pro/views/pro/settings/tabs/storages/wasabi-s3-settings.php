<?php

/**
 * @var string $providerId
 */

$auth = \WPStaging\Core\WPStaging::make(\WPStaging\Pro\Backup\Storage\Wasabi\Auth::class);
$providerName = 'Wasabi S3';
$settingText  = '';
$settingLink  = '';
$settingText1 = '';
$settingLink1 = '';
$locationName = 'Wasabi Bucket';
$privacyUrl   = 'https://wp-staging.com/privacy-policy/#Wasabi_S3';

$baseSettingsPath = WPSTG_VIEWS_DIR . "pro/settings/tabs/storages/base-s3-settings.php";
require_once($baseSettingsPath);
