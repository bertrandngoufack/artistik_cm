<?php

namespace WPStaging\Pro\Backup\Service\Database\Importer;

class DatabaseSearchReplacer extends AbstractSearchReplacer
{
    protected function normalizePath(string $path): string
    {
        return wp_normalize_path($path);
    }

    protected function getUploadUrl(): string
    {
        $uploadDir = wp_upload_dir(null, false, true);
        if (!is_array($uploadDir)) {
            return '';
        }

        return array_key_exists('baseurl', $uploadDir) ? $uploadDir['baseurl'] : '';
    }
}
