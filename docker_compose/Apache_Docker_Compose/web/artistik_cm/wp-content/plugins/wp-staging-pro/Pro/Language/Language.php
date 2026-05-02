<?php

namespace WPStaging\Pro\Language;

use WPStaging\Framework\Language\Language as FrameworkLanguage;

/**
 * Handles translation loading for the pro version.
 *
 * The pro plugin does not bundle its own .mo files to keep the zip small.
 * Instead it loads translations from the free plugin's languages directory,
 * which is always present because the pro version requires the free version.
 */
class Language
{
    /**
     * @param string $locale
     * @param string $moFileLocal
     * @param string[] $moFilesGlobal
     * @return void
     */
    public function loadLanguage(string $locale, string $moFileLocal, array $moFilesGlobal)
    {
        // Load from the free version's languages directory (shared text domain).
        // The pro version does not bundle its own .mo files to keep the zip small.
        $freeMoFile = $this->getFreeMoFile($moFileLocal);
        if (file_exists($freeMoFile)) {
            load_textdomain(FrameworkLanguage::TEXT_DOMAIN, $freeMoFile);
        }

        // Try global .mo files (wp.org language packs)
        foreach ($moFilesGlobal as $moFileGlobal) {
            if (file_exists($moFileGlobal)) {
                load_textdomain(FrameworkLanguage::TEXT_DOMAIN, $moFileGlobal);
            }
        }
    }

    /**
     * Resolve the .mo file path in the free plugin's languages directory.
     */
    private function getFreeMoFile(string $proMoFileLocal): string
    {
        return WP_PLUGIN_DIR . '/wp-staging/languages/' . basename($proMoFileLocal);
    }
}
