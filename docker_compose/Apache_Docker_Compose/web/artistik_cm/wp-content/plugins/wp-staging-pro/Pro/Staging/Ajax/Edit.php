<?php

namespace WPStaging\Pro\Staging\Ajax;

use Throwable;
use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Framework\Utils\DatabaseOptions;
use WPStaging\Framework\Utils\Sanitize;
use WPStaging\Staging\Dto\StagingSiteDto;
use WPStaging\Staging\Sites;

class Edit extends AbstractTemplateComponent
{
    /** @var Sites */
    private $sites;

    /** @var Sanitize */
    private $sanitize;

    /** @var DatabaseOptions */
    private $databaseOptions;

    public function __construct(Sites $sites, Sanitize $sanitize, TemplateEngine $templateEngine, DatabaseOptions $databaseOptions)
    {
        parent::__construct($templateEngine);
        $this->sites           = $sites;
        $this->sanitize        = $sanitize;
        $this->databaseOptions = $databaseOptions;
    }

    public function ajaxModalContent()
    {
        if (!$this->canRenderAjax()) {
            wp_send_json_error('Invalid request.');
        }

        $cloneId = $this->sanitize->sanitizeString(isset($_POST['cloneId']) ? $_POST['cloneId'] : '');
        if (empty($cloneId)) {
            wp_send_json_error('Invalid request. Clone ID missing!');
        }

        try {
            wp_send_json_success($this->templateEngine->render('pro/staging/modal/edit-staging-site-modal-content.php', [
                'stagingSite' => $this->sites->getStagingSiteDtoByCloneId($cloneId),
                'cloneId'     => $cloneId,
            ]));
        } catch (Throwable $ex) {
            wp_send_json_error($ex->getMessage());
        }
    }

    public function ajaxSave()
    {
        if (!$this->canRenderAjax()) {
            wp_send_json_error('Invalid request.');
        }

        $cloneId = $this->sanitize->sanitizeString(isset($_POST['cloneId']) ? $_POST['cloneId'] : '');
        if (empty($cloneId)) {
            wp_send_json_error('Invalid request. Clone ID missing!');
        }

        $stagingSites = $this->sites->tryGettingStagingSites();
        if (empty($stagingSites)) {
            wp_send_json_error('No staging sites found.');
        }

        if (!array_key_exists($cloneId, $stagingSites)) {
            wp_send_json_error('Invalid clone ID.');
        }

        $stagingSite = new StagingSiteDto();
        $stagingSite->hydrate($stagingSites[$cloneId]);

        $stagingSite->setCloneId($cloneId);
        $stagingSite->setCloneName($this->validatePostAndSanitizeString('cloneName'));
        $stagingSite->setDirectoryName(preg_replace("#\W+#", '-', strtolower($this->validatePostAndSanitizeString('directoryName'))));
        $stagingSite->setPath($this->validatePostAndSanitizeString('path'));
        $stagingSite->setUrl($this->validatePostAndSanitizeUrl('url'));
        $stagingSite->setPrefix($this->validatePostAndSanitizeString('prefix'));
        // external database access data
        $stagingSite->setDatabaseUser($this->validatePostAndSanitizeString('databaseUser'));
        $stagingSite->setDatabasePassword($this->validatePostAndSanitizePassword('databasePassword'));
        $stagingSite->setDatabaseDatabase($this->validatePostAndSanitizeString('databaseDatabase'));
        $stagingSite->setDatabaseServer($this->validatePostAndSanitizeString('databaseServer'));
        $stagingSite->setDatabasePrefix($this->validatePostAndSanitizeString('databasePrefix'));
        $stagingSite->setDatabaseSsl($this->validatePostAndSanitizeBool('databaseSsl'));
        $status = $this->validatePostAndSanitizeString('status');
        if (!$this->isValidStatus($status)) {
            wp_send_json_error('Invalid staging site status.');
        }

        $stagingSite->setStatus($status);
        // Set some values if not present !
        $stagingSite->setOwnerId(empty($stagingSite->getOwnerId()) ? get_current_user_id() : $stagingSite->getOwnerId());
        $stagingSite->setDatetime(empty($stagingSite->getDatetime()) ? time() : $stagingSite->getDatetime());

        $stagingSites[$cloneId] = $stagingSite->toArray();

        $result = $this->databaseOptions->updateOption(Sites::STAGING_SITES_OPTION, $stagingSites);

        wp_send_json([
            'success' => $result,
            'data'    => $result ? esc_html__('Staging site updated successfully.', 'wp-staging') : esc_html__('Failed to update staging site.', 'wp-staging'),
        ]);
    }

    protected function validatePostAndSanitizeString(string $fieldName): string
    {
        return isset($_POST[$fieldName]) ? $this->sanitize->sanitizeString($_POST[$fieldName]) : '';
    }

    protected function validatePostAndSanitizePassword(string $fieldName): string
    {
        return isset($_POST[$fieldName]) ? $this->sanitize->sanitizePassword($_POST[$fieldName]) : '';
    }

    protected function validatePostAndSanitizeBool(string $fieldName): bool
    {
        return !empty($_POST[$fieldName]) ? $this->sanitize->sanitizeBool($_POST[$fieldName]) : false;
    }

    protected function validatePostAndSanitizeUrl(string $fieldName): string
    {
        return isset($_POST[$fieldName]) ? $this->sanitize->sanitizeUrl($_POST[$fieldName]) : '';
    }

    protected function getAllowedStatuses(): array
    {
        return [
            StagingSiteDto::STATUS_FINISHED,
            StagingSiteDto::STATUS_UNFINISHED_BROKEN,
        ];
    }

    protected function isValidStatus(string $status): bool
    {
        return in_array($status, $this->getAllowedStatuses(), true);
    }
}
