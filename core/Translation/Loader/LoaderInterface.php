<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Translation\Loader;

/**
 * Loads translations.
 */
interface LoaderInterface
{
    /**
     * @param string $language
     * @param mixed[] $directories Directories containing translation files.
     * @throws \Exception The translation file was not found
     * @return string[] Translations.
     */
    public function load($language, array $directories);
}
