<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Morpheus;

class Morpheus extends \Piwik\Plugin
{
    private const DARK_MONOCHROME_ICONS_MANIFEST = 'plugins/Morpheus/icons/dist/dark-monochrome-icons.json';

    public function registerEvents()
    {
        return [
            'AssetManager.addStylesheets' => 'addStylesheets',
        ];
    }

    /**
     * The matomo-icons build ships a manifest listing the monochrome dark-glyph icons
     * (devices, grayscale brand logos, ...) that are nearly invisible on dark backgrounds.
     * We turn that data into CSS here, so the icons repo owns the list and Matomo owns the
     * dark-mode presentation. The list regenerates with the icons - nothing is hardcoded.
     */
    public function addStylesheets(&$mergedContent)
    {
        $css = $this->getDarkModeIconsCss();
        if ($css !== '') {
            $mergedContent .= "\n" . $css;
        }
    }

    private function getDarkModeIconsCss(): string
    {
        $manifestFile = PIWIK_INCLUDE_PATH . '/' . self::DARK_MONOCHROME_ICONS_MANIFEST;
        if (!is_readable($manifestFile)) {
            return '';
        }

        $icons = json_decode((string) file_get_contents($manifestFile), true);
        if (!is_array($icons)) {
            return '';
        }

        $selectors = [];
        foreach ($icons as $icon) {
            // Only accept the expected icon path format; never let manifest contents break
            // out of the selector (e.g. quotes or braces).
            if (!is_string($icon) || !preg_match('#^[A-Za-z0-9._/-]+$#', $icon)) {
                continue;
            }
            $selectors[] = 'img[src*="plugins/Morpheus/icons/dist/' . $icon . '"]';
        }

        if (empty($selectors)) {
            return '';
        }

        return implode(",\n", $selectors)
            . " {\n  filter: var(--theme-filter-on-illustration);\n}\n";
    }
}
