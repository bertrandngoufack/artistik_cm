<?php

/**
 * @var string $providerId
 */

$auth = \WPStaging\Core\WPStaging::make(\WPStaging\Pro\Backup\Storage\DigitalOceanSpaces\Auth::class);
$providerName = 'DigitalOcean Spaces';
$settingText  = __('Create tokens on DigitalOcean', 'wp-staging');
$settingLink  = 'https://cloud.digitalocean.com/account/api/tokens';
$settingText1 = __('Create Space on DigitalOcean', 'wp-staging');
$settingLink1 = 'https://cloud.digitalocean.com/spaces/new';
$locationName = 'Space';
$privacyUrl   = 'https://wp-staging.com/privacy-policy/#DigitalOcean_Spaces';

$baseSettingsPath = WPSTG_VIEWS_DIR . "pro/settings/tabs/storages/base-s3-settings.php";
require_once($baseSettingsPath);
