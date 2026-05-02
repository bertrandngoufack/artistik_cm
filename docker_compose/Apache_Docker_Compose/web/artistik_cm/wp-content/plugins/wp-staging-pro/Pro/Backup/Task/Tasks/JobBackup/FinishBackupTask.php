<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobBackup;

use WPStaging\Backup\Task\Tasks\JobBackup\FinishBackupTask as BasicFinishBackupTask;
use WPStaging\Core\WPStaging;
use WPStaging\Pro\Backup\Dto\Traits\SaveBackupsInDBTrait;
use WPStaging\Pro\Push\Ajax\PreparePush;

class FinishBackupTask extends BasicFinishBackupTask
{
    use SaveBackupsInDBTrait;

    /**
     * Retains backups, if at least one remote storage is set.
     *
     * @return void
     */
    protected function saveCloudStorageOptions()
    {
        $this->saveBackupsInDB($this->jobDataDto->getId(), $this->jobDataDto);
    }

    /**
     * @return void
     */
    protected function performFinishBackupAction()
    {
        if ($this->jobDataDto->getIsSyncRequest()) {
            $this->logger->info("Pulled data prepared successfully. Downloading...");
            return;
        }

        $this->getJobTransientCache()->completeJob();

        $this->maybeTriggerPushAfterBackup();
    }

    /**
     * @return void
     */
    private function maybeTriggerPushAfterBackup()
    {
        $pushPrepareData = $this->jobDataDto->getPushPrepareData();

        if (empty($pushPrepareData) || !is_array($pushPrepareData)) {
            return;
        }

        $pushPrepareData['isBackupCreated'] = true;

        $preparePush = WPStaging::make(PreparePush::class);
        $prepared    = $preparePush->prepare($pushPrepareData);

        if ($prepared instanceof \WP_Error) {
            $this->logger->warning(sprintf('Failed to prepare push after backup: %s', $prepared->get_error_message()));
        }
    }

    /**
     * @return void
     */
    protected function logCompressionEntry()
    {
        if (!$this->jobDataDto->getIsCompressed()) {
            return;
        }

        $uncompressedSize = $this->jobDataDto->getBackupSizeUncompressed();
        $compressedSize   = $this->jobDataDto->getBackupSizeCompressed();
        if ($uncompressedSize <= 0 || $compressedSize <= 0) {
            return;
        }

        $compressionRatio = round((1 - ($compressedSize / $uncompressedSize)) * 100, 2);
        $this->logger->info(sprintf(
            'Backup compressed: %s → %s (%.2f%% reduction).',
            size_format($uncompressedSize, 2),
            size_format($compressedSize, 2),
            $compressionRatio
        ));
    }
}
