<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Translation\Loader;

/**
 * Loads translations.
 */
interface LoaderInterface
{
    /**
     * @param string $language
     * @param string[] $directories Directories containing translation files.
     * @return array<string, array<string, string>> Translations.
     * @throws \Exception The translation file was not found
     */
    public function load($language, array $directories);
}
