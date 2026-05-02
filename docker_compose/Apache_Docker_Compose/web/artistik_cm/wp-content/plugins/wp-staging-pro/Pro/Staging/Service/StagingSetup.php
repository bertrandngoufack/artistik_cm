<?php

namespace WPStaging\Pro\Staging\Service;

use WPStaging\Framework\Assets\Assets;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Framework\Utils\WpDefaultDirectories;
use WPStaging\Pro\License\Licensing;
use WPStaging\Staging\Dto\StagingSiteDto;
use WPStaging\Staging\Service\AbstractStagingSetup;

/**
 * @package WPStaging\Staging\Service
 */
class StagingSetup extends AbstractStagingSetup
{
    /**
     * @var bool
     */
    protected $openDisabledSettingsSectionByDefault = false;

    public function __construct(TemplateEngine $templateEngine, Assets $assets, WpDefaultDirectories $wpDefaultDirectories)
    {
        parent::__construct($templateEngine, $assets, $wpDefaultDirectories);
    }

    /**
     * @return void
     */
    public function renderNetworkCloneSettings()
    {
        if (!is_multisite() || !is_main_site()) {
            return;
        }

        $view = $this->templateEngine->render(
            'pro/staging/_partials/network-options.php',
            [
                'stagingSetup' => $this,
                'description'  => $this->templateEngine->render('pro/staging/_partials/network-options-description.php'),
            ]
        );

        echo $view; // phpcs:ignore
    }

    public function getAdvanceSettingsTitle(): string
    {
        return esc_html__("Advanced Settings", "wp-staging");
    }

    /**
     * @return void
     */
    public function renderAdvanceSettingsHeader()
    {
        // no-op for PRO version
    }

    public function renderAdvanceSettings(string $name, string $label, string $description, bool $checked = false, string $additionalClasses = '', string $dataId = '')
    {
        // We disable the settings by default on FREE version.
        $this->renderSettings($name, $label, $description, $checked, false, $additionalClasses, $dataId);
    }

    /**
     * @param StagingSiteDto $stagingSiteDto
     * @return void
     */
    public function initPushJob(StagingSiteDto $stagingSiteDto)
    {
        $this->stagingJob     = StagingSetup::JOB_PUSH;
        $this->stagingSiteDto = $stagingSiteDto;
    }

    /**
     * @return void
     */
    public function renderNewAdminSettings()
    {
        $fields = [
            [
                'label'          => esc_html__('Email: ', 'wp-staging'),
                'name'           => 'wpstg-new-admin-email',
                'type'           => 'email',
                'placeholder'    => '',
                'value'          => '',
                'autocapitalize' => false,
                'disabled'       => false,
            ],
            [
                'label'          => esc_html__('Password: ', 'wp-staging'),
                'name'           => 'wpstg-new-admin-password',
                'type'           => 'password',
                'placeholder'    => '',
                'value'          => '',
                'autocapitalize' => false,
                'autocomplete'   => false,
                'disabled'       => false,
            ],
        ];

        $this->renderSettingsFields($fields);
    }

    /**
     * @return void
     */
    public function renderCustomDirectorySettings()
    {
        $fields = [
            [
                'label'          => esc_html__('Destination Path: ', 'wp-staging'),
                'name'           => 'wpstg_clone_dir',
                'type'           => 'text',
                'placeholder'    => ABSPATH,
                'value'          => '',
                'autocapitalize' => false,
                'disabled'       => false,
                'description'    => $this->templateEngine->render('pro/staging/_partials/destination-path-description.php', [ 'directory' => ABSPATH ]),
            ],
            [
                'label'          => esc_html__('Target Hostname: ', 'wp-staging'),
                'name'           => 'wpstg_clone_hostname',
                'type'           => 'text',
                'placeholder'    => get_site_url(),
                'value'          => '',
                'autocapitalize' => false,
                'disabled'       => false,
                'description'    => $this->templateEngine->render('pro/staging/_partials/target-hostname-description.php', [ 'hostname' => get_site_url() ]),
            ],
        ];

        $this->renderSettingsFields($fields);
    }

    /**
     * Avoid renaming the 'wpstg-db-user' field to 'wpstg-db-username' or simply 'username',
     * and 'wpstg-db-pass' to 'wpstg-db-password' or 'password'.
     * Renaming may lead to unintended autofill behavior if the fields are disabled.
     * @return void
     */
    public function renderExternalDatabaseSettings()
    {
        $fields = [
            [
                'label'          => esc_html__('Server: ', 'wp-staging'),
                'name'           => 'wpstg-db-server',
                'type'           => 'text',
                'placeholder'    => 'localhost',
                'value'          => '',
                'autocapitalize' => false,
                'disabled'       => false,
            ],
            [
                'label'          => esc_html__('User: ', 'wp-staging'),
                'name'           => 'wpstg-db-user',
                'type'           => 'text',
                'placeholder'    => '',
                'value'          => '',
                'autocapitalize' => false,
                'disabled'       => false,
            ],
            [
                'label'          => esc_html__('Password: ', 'wp-staging'),
                'name'           => 'wpstg-db-pass',
                'type'           => 'password',
                'placeholder'    => '',
                'value'          => '',
                'autocapitalize' => false,
                'autocomplete'   => false,
                'disabled'       => false,
            ],
            [
                'label'          => esc_html__('Database: ', 'wp-staging'),
                'name'           => 'wpstg-db-database',
                'type'           => 'text',
                'placeholder'    => '',
                'value'          => '',
                'autocapitalize' => false,
                'disabled'       => false,
            ],
            [
                'label'          => esc_html__('Database Prefix: ', 'wp-staging'),
                'name'           => 'wpstg-db-prefix',
                'type'           => 'text',
                'placeholder'    => 'wp_',
                'value'          => '',
                'autocapitalize' => false,
                'disabled'       => false,
            ],
            [
                'label'    => esc_html__('Enable SSL: ', 'wp-staging'),
                'name'     => 'wpstg-db-ssl',
                'type'     => 'checkbox',
                'value'    => 'true',
                'checked'  => false,
                'disabled' => false,
            ],
        ];

        $this->renderSettingsFields($fields);
    }

    /**
     * @return void
     */
    public function renderEnableWooSchedulerSettings()
    {
        $licenseData     = get_option('wpstg_license_status');
        $licensePriceId  = !empty($licenseData->price_id) ? $licenseData->price_id : '';
        $acceptablePlans = [
            Licensing::AGENCY_LICENSE_PLAN_KEY,
            Licensing::DEVELOPER_LICENSE_PLAN_KEY,
            Licensing::DEVELOPER_LEGACY_LICENSE_PLAN_KEY,
            Licensing::DEVELOPER_30_SITES_LICENSE_PLAN_KEY,
            Licensing::DEVELOPER_NON_RECURRING_LICENSE_PLAN_KEY,
            Licensing::AGENCY_NON_RECURRING_LICENSE_PLAN_KEY,
            Licensing::DEVELOPER_UNLIMITED_SITES_LICENSE_PLAN_KEY,
        ];

        if (!in_array($licensePriceId, $acceptablePlans)) {
            return;
        }

        $checked = true;
        $this->renderAdvanceSettings(
            'wpstg_woo_scheduler_enabled',
            esc_html__('Enable WooCommerce Scheduler', 'wp-staging'),
            esc_html__('Enable WooCommerce Action Scheduler.', 'wp-staging') . '<br /> <br /> <b>' . esc_html__('Note', 'wp-staging') . ': </b>' . sprintf(esc_html__('Enable WooCommerce Action Scheduler/Subscriptions on a staging site. %s.', 'wp-staging'), '<a href="https://wp-staging.com/docs/how-to-disable-woocommerce-subscriptions-on-a-staging-site/" target="_blank" rel="external">' . esc_html__('Read more about that here', 'wp-staging') . '</a>'),
            $checked
        );
    }
}
