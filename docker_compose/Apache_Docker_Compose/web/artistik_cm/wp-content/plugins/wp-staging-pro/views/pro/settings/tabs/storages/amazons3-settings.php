<?php

/**
 * @var string $providerId
 */

$auth = \WPStaging\Core\WPStaging::make(\WPStaging\Pro\Backup\Storage\Amazon\S3::class);
$providerName = 'Amazon S3';
$settingText  = __('How to create Amazon API keys and a S3 bucket', 'wp-staging');
$settingLink  = 'https://wp-staging.com/docs/how-to-backup-website-to-amazon-s3-bucket/';
$settingText1 = '';
$settingLink1 = '';
$privacyUrl   = 'https://wp-staging.com/privacy-policy/#Amazon_S3';

$baseSettingsPath = WPSTG_VIEWS_DIR . "pro/settings/tabs/storages/base-s3-settings.php";
require_once($baseSettingsPath);
