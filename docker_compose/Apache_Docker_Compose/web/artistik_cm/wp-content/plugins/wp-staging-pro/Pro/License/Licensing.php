<?php

namespace WPStaging\Pro\License;

use WPStaging;
use WPStaging\Core\Cron\Cron;
use WPStaging\Framework\Facades\Sanitize;
use WPStaging\Framework\Facades\Escape;
use WPStaging\Framework\Facades\UI\Alert;
use WPStaging\Framework\Language\Language;
use WPStaging\Framework\Notices\Notices;
use WPStaging\Framework\SiteInfo;

use function WPStaging\functions\debug_log;

/**
 * Manages WP Staging Pro license activation, validation, and updates
 *
 * This class handles all aspects of license management including:
 * - License key activation and deactivation via EDD API
 * - License status validation (valid, expired, disabled, invalid)
 * - Automatic license checks via WordPress cron
 * - License plan detection (Personal, Business, Developer, Agency)
 * - Plugin update integration through EDD Software Licensing
 * - Admin notices for license-related issues
 *
 * The class integrates with Easy Digital Downloads (EDD) Software Licensing API
 * to provide secure license management and automatic plugin updates for WP Staging Pro.
 */
class Licensing
{
    /**
     * @var string
     */
    const WPSTG_LICENSE_KEY = 'wpstg_license_key';

    /** @var string 'valid' or 'invalid' */
    const WPSTG_LICENSE_STATUS = 'wpstg_license_status';

    /**
     * @var string
     */
    const WPSTG_STORE_URL = 'https://wp-staging.com/edd-sl-api';

    /**
     * @var string
     */
    const WPSTG_FALLBACK_STORE_URL = 'https://wp-staging.com';

    /**
     * @var string
     */
    const WPSTG_ITEM_NAME = 'WP STAGING PRO';

    /**
     * @var string
     */
    const WPSTG_AUTHOR_NAME = 'Rene Hermenau';

    /**
     * @var string
     */
    const LICENSE_EXPIRED = 'expired';

    /**
     * @var string
     */
    const LICENSE_VALID = 'valid';

    /**
     * @var string
     */
    const LICENSE_INVALID = 'invalid';

    /**
     * @var string
     */
    const LICENSE_DISABLED = 'disabled';

    /**
     * @var string
     */
    const LICENSE_INACTIVE = 'inactive';

    /**
     * @var string
     */
    const PERSONAL_LICENSE_PLAN_KEY = '1';

    /**
     * @var string
     */
    const PERSONAL_LICENSE_2025_PLAN_KEY = '15';

    /**
     * @var string
     */
    const PERSONAL_NON_RECURRING_LICENSE_PLAN_KEY = '10';

    /**
     * @var string
     */
    const BUSINESS_LICENSE_PLAN_KEY = '7';

    /**
     * @var string
     */
    const BUSINESS_5_SITES_LICENSE_PLAN_KEY = '4';

    /**
     * @var string
     */
    const BUSINESS_NON_RECURRING_LICENSE_PLAN_KEY = '11';

    /**
     * @var string
     */
    const DEVELOPER_LICENSE_PLAN_KEY = '13';

    /**
     * @var string
     */
    const DEVELOPER_LEGACY_LICENSE_PLAN_KEY = '6';

    /**
     * @var string
     */
    const DEVELOPER_30_SITES_LICENSE_PLAN_KEY = '2';

    /**
     * @var string
     */
    const DEVELOPER_NON_RECURRING_LICENSE_PLAN_KEY = '12';

        /**
     * @var string
     */
    const DEVELOPER_UNLIMITED_SITES_LICENSE_PLAN_KEY = '8';

    /**
     * @var string
     */
    const AGENCY_LICENSE_PLAN_KEY = '3';

    /**
     * @var string
     */
    const AGENCY_NON_RECURRING_LICENSE_PLAN_KEY = '14';

    /**
     * @var string
     */
    const BUSINESS_LICENSE_UPGRADE_PLAN_KEY = '4';

    /**
     * @var string
     */
    const DEVELOPER_LICENSE_UPGRADE_PLAN_KEY = '6';

    /**
     * @var string
     */
    const AGENCY_LICENSE_UPGRADE_PLAN_KEY = '5';

    /**
     * @var string
     */
    const LICENSE_TYPE_BASIC = 'basic';

    /**
     * @var string
     */
    const LICENSE_TYPE_PERSONAL = 'personal';

    /**
     * @var string
     */
    const LICENSE_TYPE_PERSONAL_LEGACY = 'personal_legacy';

    /**
     * @var string
     */
    const LICENSE_TYPE_BUSINESS = 'business';

    /**
     * @var string
     */
    const LICENSE_TYPE_DEVELOPER = 'developer';

    /**
     * @var string
     */
    const LICENSE_TYPE_DEVELOPER_LEGACY = 'developer_legacy';

    /**
     * @var string
     */
    const LICENSE_TYPE_DEVELOPER_30_SITES = 'developer_30_sites';

    /**
     * @var string
     */
    const LICENSE_TYPE_AGENCY = 'agency';

    /**
     * @var string
     */
    const PRICING_LINK = '<a href=\"https://wp-staging.com/#pricing\" target=\"_blank\">wp-staging.com</a>';

    /**
     * @var string
     */
    const SUPPORT_EMAIL_LINK = '<a href=\"mailto:support@wp-staging.com\" target=\"_blank\">support@wp-staging.com</a>';

    /**
     * The license key
     * @var string
     */
    private $licenseKey = '';

    /**
     * @var SiteInfo
     */
    private $siteInfo;

    public function __construct()
    {
        $this->siteInfo = WPStaging\Core\WPStaging::make(SiteInfo::class);
        $this->registerHooks();
    }

    /**
     * @return void
     */
    private function registerHooks()
    {
        static $isRegistered = false;
        if ($isRegistered) {
            return;
        }

        add_action(Notices::ACTION_ADMIN_NOTICES, [$this, 'adminNotices']);
        add_action('admin_init', [$this, 'activateLicense']);
        add_action('admin_init', [$this, 'deactivateLicense']);
        add_action(Cron::ACTION_DAILY_EVENT, [$this, 'updateLicenseData']);

        if (!defined('WPSTG_STORE_URL')) {
            define('WPSTG_STORE_URL', self::WPSTG_STORE_URL);
        }

        if (!defined('WPSTG_ITEM_NAME')) {
            define('WPSTG_ITEM_NAME', self::WPSTG_ITEM_NAME);
        }

        $this->licenseKey = trim(get_option(self::WPSTG_LICENSE_KEY));
        // Initialize the EDD software licensing API
        $this->pluginUpdater();

        $isRegistered = true;
    }

    /**
     * EDD software licensing API
     * @return void
     */
    public function pluginUpdater()
    {
        // Check for 'undefined' here because WPSTG_PLUGIN_FILE will be undefined if plugin is uninstalled to prevent issue #216
        $pluginFile = !defined('WPSTG_PLUGIN_FILE') ? null : WPSTG_PLUGIN_FILE;

        new EDD_SL_Plugin_Updater(
            WPSTG_STORE_URL,
            $pluginFile,
            [
                'version'   => WPStaging\Core\WPStaging::getVersion(),
                'license'   => $this->licenseKey,
                'item_name' => WPSTG_ITEM_NAME,
                'author'    => self::WPSTG_AUTHOR_NAME,
                'beta'      => $this->isBetaVersion(),
            ]
        );
    }

    /**
     * Activate the license key
     * @return void
     */
    public function activateLicense()
    {
        if (isset($_POST['wpstg_activate_license']) && !empty($_POST[self::WPSTG_LICENSE_KEY])) {
            // Early bail if nonce is invalid
            if (!check_admin_referer('wpstg_license_nonce', 'wpstg_license_nonce')) {
                return;
            }

            $licenseKey = Sanitize::sanitizeString($_POST[self::WPSTG_LICENSE_KEY]);
            update_option(self::WPSTG_LICENSE_KEY, $licenseKey);

            $apiParams = [
                'edd_action' => 'activate_license',
                'license'    => $licenseKey,
                'item_name'  => urlencode(WPSTG_ITEM_NAME),
                'url'        => home_url(),
            ];

            $response = $this->makeApiRequest($apiParams);

            $message       = '';
            $licenseAction = '';
            $responseBody  = wp_remote_retrieve_body($response);
            $responseCode  = wp_remote_retrieve_response_code($response);
            if (is_wp_error($response) || $responseCode !== 200) {
                if (is_wp_error($response)) {
                    $message = $response->get_error_message();
                    debug_log('Activate License Error: ' . $message);
                } else {
                    debug_log("Activate License Response: " . wp_strip_all_tags(json_encode($response)));
                    $message = $this->formatApiResponse($response);
                }

                $message .= sprintf(
                    Escape::escapeHtml(__('<br/>You may find a solution in <a href="%s" target="_blank">this article</a>.', 'wp-staging')),
                    'https://wp-staging.com/docs/curl-error-35-unknown-ssl-protocol-error-in-connection/'
                );
            } else {
                $licenseData = json_decode($responseBody);

                if (!empty($licenseData) && $licenseData->success === false) {
                    $licenseData->error = $licenseData->error ?? '';
                    $licenseAction      = 'buy';
                    switch ($licenseData->error) {
                        case 'expired':
                            $licenseAction = 'renew';
                            $message = sprintf(
                                __('Your license key expired on %s. Renew the license key on %s or contact %s for help.', 'wp-staging'),
                                date_i18n(get_option('date_format'), strtotime($licenseData->expires, current_time('timestamp'))),
                                self::PRICING_LINK,
                                self::SUPPORT_EMAIL_LINK
                            );
                            break;
                        case 'disabled':
                            $message = sprintf(
                                __('Your license key has been disabled. Please contact %s', 'wp-staging'),
                                self::SUPPORT_EMAIL_LINK
                            );
                            break;
                        case 'missing':
                            $message = sprintf(
                                __('The license key you entered is not recognized. Pro features will not work until a valid key is activated. Please obtain a new key from %s or contact %s for help.', 'wp-staging'),
                                self::PRICING_LINK,
                                self::SUPPORT_EMAIL_LINK
                            );
                            break;
                        case 'key_mismatch':
                            $message = sprintf(
                                __('Your License key is invalid. Get a new license key from %s or contact %s for help.', 'wp-staging'),
                                self::PRICING_LINK,
                                self::SUPPORT_EMAIL_LINK
                            );
                            break;
                        case 'missing_url':
                             $message = sprintf(
                                 __('Could not activate license. URL not provided. Get a new license key from %s or contact %s for help.', 'wp-staging'),
                                 self::PRICING_LINK,
                                 self::SUPPORT_EMAIL_LINK
                             );
                            break;
                        case 'license_not_activable':
                            $message = sprintf(
                                __('Attempting to activate a bundle\'s parent license. Get a new license key from %s or contact %s for help.', 'wp-staging'),
                                self::PRICING_LINK,
                                self::SUPPORT_EMAIL_LINK
                            );
                            break;
                        case 'invalid':
                            $message = sprintf(
                                __('Your license key is invalid. Get a new license key from %s or contact %s for help.', 'wp-staging'),
                                self::PRICING_LINK,
                                self::SUPPORT_EMAIL_LINK
                            );
                            break;
                        case 'site_inactive':
                            $licenseAction = 'upgrade';
                            $message       = sprintf(
                                __('Your license key has reached its activation limit. Upgrade your license key on %s for more active sites.', 'wp-staging'),
                                self::PRICING_LINK
                            );
                            break;
                        case 'item_name_mismatch':
                            $message = sprintf(
                                __('The license key you entered for %s is invalid. Pro features will not work until a valid key is activated. Get a new one from %s or contact %s for assistance.', 'wp-staging'),
                                WPSTG_ITEM_NAME,
                                self::PRICING_LINK,
                                self::SUPPORT_EMAIL_LINK
                            );
                            break;
                        case 'invalid_item_id':
                            $message = sprintf(
                                __('Could not activate license. Invalid Item ID. Get a new license key from %s or contact %s for help.', 'wp-staging'),
                                self::PRICING_LINK,
                                self::SUPPORT_EMAIL_LINK
                            );
                            break;
                        case 'no_activations_left':
                            $licenseAction = 'upgrade';
                            $message = sprintf(
                                __('Your license key has reached its activation limit. Upgrade your license key on %s for more active sites.', 'wp-staging'),
                                self::PRICING_LINK
                            );
                            break;
                        default:
                            $message = sprintf(
                                __('This license key is not valid. You can buy one from %s or contact %s', 'wp-staging'),
                                self::PRICING_LINK,
                                self::SUPPORT_EMAIL_LINK
                            );
                            break;
                    }
                }
            }

            if (!empty($message)) {
                $upgradeId = $this->getUpgradeId($licenseData->price_id ?? '');
                $licenseId = $licenseData->license_id ?? '';
                $baseUrl   = admin_url('admin.php?page=wpstg-license');
                $redirect  = add_query_arg(['wpstg_licensing' => 'false', 'message' => urlencode($message), 'licenseAction' => $licenseAction, 'licenseId' => $licenseId, 'upgradeId' => $upgradeId, 'licenseKey' => $licenseKey], $baseUrl);
                if (!empty($licenseData)) {
                    update_option(self::WPSTG_LICENSE_STATUS, $licenseData);
                }

                wp_redirect($redirect);
                exit();
            }

            // $licenseData->license will be either "valid" or "invalid"
            update_option(self::WPSTG_LICENSE_STATUS, $licenseData);
            wp_redirect(admin_url('admin.php?page=wpstg-license'));
            exit();
        }
    }

    /**
     * Deactivate the license key
     * @return void
     */
    public function deactivateLicense()
    {
        if (isset($_POST['wpstg_deactivate_license'])) {
            // Early bail if nonce is invalid
            if (!check_admin_referer('wpstg_license_nonce', 'wpstg_license_nonce')) {
                return;
            }

            $license = trim(get_option(self::WPSTG_LICENSE_KEY));

            $apiParams = [
                'edd_action' => 'deactivate_license',
                'license'    => $license,
                'item_name'  => urlencode(WPSTG_ITEM_NAME), // the name of our product in EDD
                'url'        => home_url(),
            ];

            $response = $this->makeApiRequest($apiParams);

            if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
                if (is_wp_error($response)) {
                    $message = $response->get_error_message();
                } else {
                    $message = __('An error occurred, please try again.', 'wp-staging');
                }

                $baseUrl  = admin_url('admin.php?page=wpstg-license');
                $redirect = add_query_arg(['wpstg_licensing' => 'false', 'message' => urlencode($message)], $baseUrl);
                wp_redirect($redirect);
                exit();
            }

            $licenseData = json_decode(wp_remote_retrieve_body($response));
            if ($licenseData->license === 'deactivated' || $licenseData->license === 'failed') {
                delete_option(self::WPSTG_LICENSE_STATUS);
                delete_option(self::WPSTG_LICENSE_KEY);
            }

            wp_redirect(admin_url('admin.php?page=wpstg-license'));
            exit();
        }
    }

    /**
     * Check if license key is valid. Usually called via cron once per day
     *
     * @access  public
     * @return  void
     * @since   2.0.3
     */
    public function updateLicenseData()
    {
        $licenseKey = trim(get_option(self::WPSTG_LICENSE_KEY));
        if (empty($licenseKey)) {
            return;
        }

        if ($this->siteInfo->isStagingSite()) {
            return; // Do not check license on staging sites otherwise license will marked as site_inactive
        }

        $apiParams = [
            'edd_action' => 'check_license',
            'license'    => $licenseKey,
            'item_name'  => urlencode(WPSTG_ITEM_NAME),
            'url'        => home_url(),
        ];

        $response = $this->makeApiRequest($apiParams);

        if (is_wp_error($response)) {
            return;
        }

        $licenseData = json_decode(wp_remote_retrieve_body($response));
        if ($this->isExpired($licenseData)) {
            $licenseData->error = self::LICENSE_EXPIRED; // @phpstan-ignore-line
        }

        if ($this->isDisabled($licenseData)) {
            $licenseData->error = self::LICENSE_DISABLED; // @phpstan-ignore-line
            delete_option(self::WPSTG_LICENSE_KEY);
        }

        if (!empty($licenseData)) {
            update_option(self::WPSTG_LICENSE_STATUS, $licenseData);
        }
    }

    /**
     * This is a means of catching errors from the activation method above and displaying it to the customer
     * @return void
     */
    public function adminNotices()
    {
        static $isDisplayed = [];
        if (isset($_GET['wpstg_licensing']) && !empty($_GET['message'])) {
            $message       = filter_input(INPUT_GET, 'message');
            $licenseAction = filter_input(INPUT_GET, 'licenseAction');
            $licenseId     = filter_input(INPUT_GET, 'licenseId');
            $upgradeId     = filter_input(INPUT_GET, 'upgradeId');
            $licenseKey    = filter_input(INPUT_GET, 'licenseKey');

            if (!empty($isDisplayed[$message])) {
                return;
            }

            switch ($_GET['wpstg_licensing']) {
                case 'false':
                    $title      = __('WP STAGING - License Activation Error', 'wp-staging');
                    $buttonText = '';
                    $buttonUrl  = '';
                    if (!empty($licenseAction) && $licenseAction === 'buy') {
                        $buttonText = __('Purchase License', 'wp-staging');
                        $buttonUrl  = 'https://wp-staging.com/#pricing';
                    } elseif (!empty($licenseAction) && $licenseAction === 'renew') {
                        $buttonText = __('Renew License', 'wp-staging');
                        $buttonUrl  = Language::localizeCheckoutUrl("https://wp-staging.com/checkout/?nocache=true&edd_license_key=$licenseKey&download_id=11");
                    } elseif (!empty($licenseAction) && $licenseAction === 'upgrade') {
                        $buttonText = __('Upgrade License', 'wp-staging');
                        $buttonUrl  = Language::localizeCheckoutUrl("https://wp-staging.com/checkout/?nocache=true&edd_action=sl_license_upgrade&license_id=$licenseId&upgrade_id=$upgradeId");
                    } else {
                        $title = ''; // Don't show any title if no action is specified
                    }

                    Alert::render($title, $message, $buttonText, $buttonUrl);
                    $isDisplayed[$message] = true;
                    break;
                case 'true':
                default:
                    // You can add a custom success message here if activation is successful
                    break;
            }
        }
    }

    /**
     * Most pro features are available even if a license has been expired.
     * The only requirement is that a license was valid in the past or still is it.
     * @return bool
     */
    public function isValidOrExpiredLicenseKey(): bool
    {
        if ($this->siteInfo->isLocal()) {
            return true;
        }

        if (!($licenseData = get_option(self::WPSTG_LICENSE_STATUS))) {
            return false;
        }

        if (isset($licenseData->license) && $licenseData->license === self::LICENSE_VALID) {
            return true;
        }

        if ($this->isDisabled($licenseData)) {
            return false;
        }

        if ($this->isExpired($licenseData)) {
            return true;
        }

        return false;
    }

    /**
     * Whether the license has been registered and is valid or expired.
     * Unlike isValidOrExpiredLicenseKey(), this does NOT bypass the check on local sites.
     *
     * @return bool
     */
    public function isRegisteredLicense(): bool
    {
        $licenseData = get_option(self::WPSTG_LICENSE_STATUS);
        if (!$licenseData) {
            return false;
        }

        if ($this->isDisabled($licenseData)) {
            return false;
        }

        if (isset($licenseData->license) && $licenseData->license === self::LICENSE_VALID) {
            return true;
        }

        if ($this->isExpired($licenseData)) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isInvalidOrExpiredLicenseKey(): bool
    {

        if (!($licenseData = get_option(self::WPSTG_LICENSE_STATUS))) {
            return true;
        }

        if ($this->isExpired($licenseData)) {
            return true;
        }

        if (isset($licenseData->license) && $licenseData->license === self::LICENSE_VALID) {
            return false;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getAvailableLicensePlansByPriceId(): array
    {
        $availableLicensePlansByPriceId = [
            'priceId' => [
                self::PERSONAL_LICENSE_PLAN_KEY                  => [
                    'name' => 'Personal License',
                ],
                self::PERSONAL_LICENSE_2025_PLAN_KEY             => [
                    'name' => 'Personal License',
                ],
                self::PERSONAL_NON_RECURRING_LICENSE_PLAN_KEY    => [
                    'name' => 'Personal License',
                ],
                self::BUSINESS_LICENSE_PLAN_KEY                  => [
                    'name' => 'Business License',
                ],
                self::BUSINESS_NON_RECURRING_LICENSE_PLAN_KEY    => [
                    'name' => 'Business License',
                ],
                self::BUSINESS_5_SITES_LICENSE_PLAN_KEY          => [
                    'name' => 'Business License',
                ],
                self::DEVELOPER_LICENSE_PLAN_KEY                 => [
                    'name' => 'Developer License',
                ],
                self::DEVELOPER_NON_RECURRING_LICENSE_PLAN_KEY   => [
                    'name' => 'Developer License',
                ],
                self::AGENCY_LICENSE_PLAN_KEY                    => [
                    'name' => 'Agency License',
                ],
                self::AGENCY_NON_RECURRING_LICENSE_PLAN_KEY      => [
                    'name' => 'Agency License',
                ],
                self::DEVELOPER_LEGACY_LICENSE_PLAN_KEY          => [
                    'name' => 'Developer Legacy License',
                ],
                self::DEVELOPER_30_SITES_LICENSE_PLAN_KEY        => [
                    'name' => 'Developer License',
                ],
                self::DEVELOPER_UNLIMITED_SITES_LICENSE_PLAN_KEY => [
                    'name' => 'Developer Legacy License',
                ],
            ],
        ];

        return $availableLicensePlansByPriceId['priceId'];
    }

    /**
     * Strict plan check: rejects both expired AND disabled licenses.
     * Unlike isAgencyOrDeveloperPlan(), this enforces the disabled check even
     * on local sites where isValidOrExpiredLicenseKey() bypasses it.
     * Used for Remote Sync and CLI Integration.
     *
     * @return bool
     */
    public function isActiveAgencyOrDeveloperPlan(): bool
    {
        if (!$this->isValidOrExpiredLicenseKey()) {
            return false;
        }

        $licenseData = get_option(self::WPSTG_LICENSE_STATUS);

        // Developer/Agency features require a valid (non-expired) license
        if (is_object($licenseData) && $this->isExpired($licenseData)) {
            return false;
        }

        // On local sites isValidOrExpiredLicenseKey() bypasses the disabled check,
        // so we must check explicitly here to block disabled licenses.
        if (is_object($licenseData) && $this->isDisabled($licenseData)) {
            return false;
        }

        $license = (object)$licenseData;
        if (empty($license->price_id)) {
            return false;
        }

        $developerPlans        = $this->getDeveloperLicensePlanIds();
        $agencyPlans           = $this->getAgencyLicensePlanIds();
        $developerPlanOrHigher = array_merge($developerPlans, $agencyPlans);

        return in_array($license->price_id, $developerPlanOrHigher, true);
    }

    /**
     * Lenient plan check: accepts expired licenses, rejects disabled only
     * on production (local sites bypass via isValidOrExpiredLicenseKey()).
     *
     * Use this for features that must remain accessible even after
     * expiration (e.g. Temporary Logins — locking users out of their
     * own sites on expiration would be harmful).
     *
     * @return bool
     */
    public function isAgencyOrDeveloperPlan(): bool
    {
        if (!$this->isValidOrExpiredLicenseKey()) {
            return false;
        }

        $license = (object)get_option(self::WPSTG_LICENSE_STATUS);
        if (empty($license->price_id)) {
            return false;
        }

        $developerPlans        = $this->getDeveloperLicensePlanIds();
        $agencyPlans           = $this->getAgencyLicensePlanIds();
        $developerPlanOrHigher = array_merge($developerPlans, $agencyPlans);

        return in_array($license->price_id, $developerPlanOrHigher, true);
    }

    /**
     * Whether the license belongs to a Developer/Agency plan that has expired.
     * Used to show a targeted renewal message instead of the generic upsell.
     *
     * @return bool
     */
    public function isExpiredDeveloperOrAgencyPlan(): bool
    {
        if (!$this->isValidOrExpiredLicenseKey()) {
            return false;
        }

        $licenseData = get_option(self::WPSTG_LICENSE_STATUS);
        if (!is_object($licenseData) || !$this->isExpired($licenseData)) {
            return false;
        }

        $license = (object)$licenseData;
        if (empty($license->price_id)) {
            return false;
        }

        $developerPlans        = $this->getDeveloperLicensePlanIds();
        $agencyPlans           = $this->getAgencyLicensePlanIds();
        $developerPlanOrHigher = array_merge($developerPlans, $agencyPlans);

        return in_array($license->price_id, $developerPlanOrHigher, true);
    }

    /**
     * Lenient plan check: local sites always return true.
     * Used for cloud storage and other business-tier features.
     *
     * @return bool
     */
    public function isBusinessPlanOrHigher(): bool
    {
        if ($this->siteInfo->isLocal()) {
            return true;
        }

        if (!$this->isValidOrExpiredLicenseKey()) {
            return false;
        }

        $license = (object)get_option(self::WPSTG_LICENSE_STATUS);
        if (empty($license->price_id)) {
            return false;
        }

        $businessPlans        = $this->getBusinessLicensePlanIds();
        $developerPlans       = $this->getDeveloperLicensePlanIds();
        $agencyPlans          = $this->getAgencyLicensePlanIds();
        $businessPlanOrHigher = array_merge($businessPlans, $developerPlans, $agencyPlans);
        return in_array($license->price_id, $businessPlanOrHigher, true);
    }

    /**
     * @return bool
     */
    public function isPersonalLicense(): bool
    {
        if (!$this->isValidOrExpiredLicenseKey()) {
            return false;
        }

        $license = (object)get_option(self::WPSTG_LICENSE_STATUS);
        if (empty($license->price_id)) {
            return false;
        }

        return (int)$license->price_id === (int)self::PERSONAL_LICENSE_2025_PLAN_KEY;
    }

    /**
     * Get license type based on price ID
     * @return string
     */
    public function getLicenseType(): string
    {
        if (!$this->isValidOrExpiredLicenseKey()) {
            return self::LICENSE_TYPE_BASIC;
        }

        $license = (object)get_option(self::WPSTG_LICENSE_STATUS);
        if (empty($license->price_id)) {
            return self::LICENSE_TYPE_BASIC;
        }

        $priceId = (string)$license->price_id;

        switch ($priceId) {
            case self::PERSONAL_LICENSE_PLAN_KEY:
                return self::LICENSE_TYPE_PERSONAL_LEGACY;
            case self::PERSONAL_LICENSE_2025_PLAN_KEY:
                return self::LICENSE_TYPE_PERSONAL;
            case self::PERSONAL_NON_RECURRING_LICENSE_PLAN_KEY:
                return self::LICENSE_TYPE_PERSONAL;
            case self::BUSINESS_LICENSE_PLAN_KEY:
                return self::LICENSE_TYPE_BUSINESS;
            case self::BUSINESS_5_SITES_LICENSE_PLAN_KEY:
                return self::LICENSE_TYPE_BUSINESS;
            case self::BUSINESS_NON_RECURRING_LICENSE_PLAN_KEY:
                return self::LICENSE_TYPE_BUSINESS;
            case self::DEVELOPER_LICENSE_PLAN_KEY:
                return self::LICENSE_TYPE_DEVELOPER;
            case self::DEVELOPER_NON_RECURRING_LICENSE_PLAN_KEY:
                return self::LICENSE_TYPE_DEVELOPER;
            case self::DEVELOPER_LEGACY_LICENSE_PLAN_KEY:
                return self::LICENSE_TYPE_DEVELOPER_LEGACY;
            case self::DEVELOPER_30_SITES_LICENSE_PLAN_KEY:
                return self::LICENSE_TYPE_DEVELOPER_30_SITES;
            case self::AGENCY_LICENSE_PLAN_KEY:
                return self::LICENSE_TYPE_AGENCY;
            case self::AGENCY_NON_RECURRING_LICENSE_PLAN_KEY:
                return self::LICENSE_TYPE_AGENCY;
            case self::DEVELOPER_UNLIMITED_SITES_LICENSE_PLAN_KEY:
                return self::LICENSE_TYPE_DEVELOPER;
            default:
                return self::LICENSE_TYPE_BASIC;
        }
    }

    /**
     * Make API request with fallback to legacy URL if primary fails
     *
     * @param array $bodyParams API parameters
     * @return array|\WP_Error The response or WP_Error on failure.
     */
    public function makeApiRequest(array $bodyParams)
    {
        // First try with primary API URL
        $response = wp_remote_post(
            self::WPSTG_STORE_URL,
            [
                'timeout'   => 15,
                'sslverify' => false,
                'body'      => $bodyParams,
            ]
        );

        $responseCode = wp_remote_retrieve_response_code($response);
        if (!is_wp_error($response) && $responseCode === 200) {
            return $response;
        }

        debug_log('Primary API URL failed. Trying fallback URL. Error: ' . (is_wp_error($response) ? $response->get_error_message() : "Response code: $responseCode"));

        $response = wp_remote_post(
            self::WPSTG_FALLBACK_STORE_URL,
            [
                'timeout'   => 15,
                'sslverify' => false,
                'body'      => $bodyParams,
            ]
        );

        return $response;
    }

    public function getPersonalLicensePlanIds(): array
    {
        return [
            self::PERSONAL_LICENSE_2025_PLAN_KEY,
        ];
    }

    public function getPersonalLegacyLicensePlanIds(): array
    {
        return [
            self::PERSONAL_LICENSE_PLAN_KEY,
            self::PERSONAL_NON_RECURRING_LICENSE_PLAN_KEY,
        ];
    }

    public function getBusinessLicensePlanIds(): array
    {
        return [
            self::BUSINESS_LICENSE_PLAN_KEY,
            self::BUSINESS_5_SITES_LICENSE_PLAN_KEY,
            self::BUSINESS_NON_RECURRING_LICENSE_PLAN_KEY,
        ];
    }

    public function getDeveloperLicensePlanIds(): array
    {
        return [
            self::DEVELOPER_LICENSE_PLAN_KEY,
            self::DEVELOPER_30_SITES_LICENSE_PLAN_KEY,
            self::DEVELOPER_NON_RECURRING_LICENSE_PLAN_KEY,
        ];
    }

    public function getAgencyLicensePlanIds(): array
    {
        return [
            self::DEVELOPER_LEGACY_LICENSE_PLAN_KEY, // its lagacy unlimited plan equivalent to agency
            self::DEVELOPER_UNLIMITED_SITES_LICENSE_PLAN_KEY, // its lagacy unlimited plan equivalent to agency
            self::AGENCY_LICENSE_PLAN_KEY,
            self::AGENCY_NON_RECURRING_LICENSE_PLAN_KEY,
        ];
    }

    /**
     * @return bool
     */
    private function isBetaVersion(): bool
    {
        return defined('WPSTG_IS_BETA') && WPSTG_IS_BETA === true;
    }

    /**
     * @param array|\WP_Error $response
     * @return string
     */
    private function formatApiResponse($response): string
    {
        $responseCode    = wp_remote_retrieve_response_code($response);
        $responseMessage = wp_remote_retrieve_response_message($response);
        $responseHeaders = wp_remote_retrieve_headers($response);
        $responseBody    = wp_remote_retrieve_body($response);
        $message         = 'An error occurred, please try again.';
        $message        .= '<div class="wpstg-license-activation-error-message-wrapper">';
        $message        .= sprintf('<strong>Response Code:</strong> %s', esc_html($responseCode));
        $message        .= sprintf('<br/><strong>Response Message:</strong> %s', esc_html($responseMessage));
        if (!empty($responseHeaders)) {
            $formattedHeaders = '<ul class="wpstg-license-activation-error-message">';
            foreach ($responseHeaders as $key => $value) {
                $formattedHeaders .= sprintf('<li><strong>%s:</strong> %s</li>', esc_html($key), esc_html($value));
            }

            $formattedHeaders .= '</ul>';
            $message          .= sprintf('<br/><strong>Response Headers:</strong> %s', $formattedHeaders);
        }

        if (!empty($responseBody)) {
            $message      .= sprintf(
                '<strong>Response Body:</strong><div class="wpstg-license-activation-error-body">%s</div>',
                $responseBody
            );
        }

        $message .= '</div>';
        return $message;
    }

    /**
     * @param object $licenseData
     * @return bool
     */
    private function isExpired($licenseData): bool
    {
        if (isset($licenseData->license) && $licenseData->license === self::LICENSE_EXPIRED) {
            return true;
        }

        if (isset($licenseData->error) && $licenseData->error === self::LICENSE_EXPIRED) {
            return true;
        }

        return false;
    }

    /**
     * @param object $licenseData
     * @return bool
     */
    private function isDisabled($licenseData): bool
    {
        if (isset($licenseData->license) && $licenseData->license === self::LICENSE_DISABLED) {
            return true;
        }

        if (isset($licenseData->license) && $licenseData->license === self::LICENSE_INACTIVE) {
            return true;
        }

        if (isset($licenseData->license) && $licenseData->license === self::LICENSE_INVALID) {
            return true;
        }

        if (isset($licenseData->error) && $licenseData->error === self::LICENSE_DISABLED) {
            return true;
        }

        return false;
    }

    /**
     * Build the EDD checkout URL to upgrade the current license to the next tier.
     * Returns empty string if no upgrade is available (e.g. already on Agency/Developer).
     *
     * @return string
     */
    public function getUpgradeUrl(): string
    {
        $licenseData = get_option(self::WPSTG_LICENSE_STATUS);
        if (empty($licenseData) || !is_object($licenseData)) {
            return '';
        }

        // EDD upgrade only works with an active license
        $status = $licenseData->license ?? '';
        if ($status !== 'valid') {
            return '';
        }

        $licenseId = $licenseData->license_id ?? '';
        $priceId   = $licenseData->price_id ?? '';
        if (empty($licenseId) || empty($priceId)) {
            return '';
        }

        $upgradeId = $this->getUpgradeId($priceId);
        if (empty($upgradeId)) {
            return '';
        }

        return Language::localizeCheckoutUrl(sprintf(
            'https://wp-staging.com/checkout/?nocache=true&edd_action=sl_license_upgrade&license_id=%s&upgrade_id=%s',
            urlencode($licenseId),
            urlencode($upgradeId)
        ));
    }

    /**
     * Build the EDD checkout URL to upgrade directly to the Developer plan.
     * Used by Remote Sync upsell — Developer is the minimum plan that includes it.
     *
     * @return string
     */
    public function getUpgradeToDevUrl(): string
    {
        $licenseData = get_option(self::WPSTG_LICENSE_STATUS);
        if (empty($licenseData) || !is_object($licenseData)) {
            return '';
        }

        $status = $licenseData->license ?? '';

        // Active license: use EDD upgrade path (prorated)
        if ($status === 'valid') {
            $licenseId = $licenseData->license_id ?? '';
            if (empty($licenseId)) {
                return '';
            }

            return Language::localizeCheckoutUrl(sprintf(
                'https://wp-staging.com/checkout/?nocache=true&edd_action=sl_license_upgrade&license_id=%s&upgrade_id=%s',
                urlencode($licenseId),
                urlencode(self::DEVELOPER_LICENSE_UPGRADE_PLAN_KEY)
            ));
        }

        // Expired license: direct checkout for Developer plan
        if ($this->isExpired($licenseData)) {
            return Language::localizeCheckoutUrl(sprintf(
                'https://wp-staging.com/checkout/?edd_action=add_to_cart&download_id=11&edd_options[price_id]=%s',
                urlencode(self::DEVELOPER_LICENSE_PLAN_KEY)
            ));
        }

        return '';
    }

    private function getUpgradeId(string $priceId): string
    {
        $upgradeMap = [
            self::PERSONAL_LICENSE_PLAN_KEY                  => self::BUSINESS_LICENSE_UPGRADE_PLAN_KEY,
            self::PERSONAL_LICENSE_2025_PLAN_KEY             => self::BUSINESS_LICENSE_UPGRADE_PLAN_KEY,
            self::PERSONAL_NON_RECURRING_LICENSE_PLAN_KEY    => self::BUSINESS_LICENSE_UPGRADE_PLAN_KEY,
            self::BUSINESS_LICENSE_PLAN_KEY                  => self::DEVELOPER_LICENSE_UPGRADE_PLAN_KEY,
            self::BUSINESS_5_SITES_LICENSE_PLAN_KEY          => self::DEVELOPER_LICENSE_UPGRADE_PLAN_KEY,
            self::BUSINESS_NON_RECURRING_LICENSE_PLAN_KEY    => self::DEVELOPER_LICENSE_UPGRADE_PLAN_KEY,
            self::DEVELOPER_LICENSE_PLAN_KEY                 => self::AGENCY_LICENSE_UPGRADE_PLAN_KEY,
            self::DEVELOPER_NON_RECURRING_LICENSE_PLAN_KEY   => self::AGENCY_LICENSE_UPGRADE_PLAN_KEY,
            self::DEVELOPER_30_SITES_LICENSE_PLAN_KEY        => self::AGENCY_LICENSE_UPGRADE_PLAN_KEY,
            self::AGENCY_LICENSE_PLAN_KEY                    => '',
            self::DEVELOPER_UNLIMITED_SITES_LICENSE_PLAN_KEY => '',
            self::AGENCY_NON_RECURRING_LICENSE_PLAN_KEY      => '',
            self::DEVELOPER_LEGACY_LICENSE_PLAN_KEY          => '',
        ];

        return $upgradeMap[$priceId] ?? '';
    }
}
