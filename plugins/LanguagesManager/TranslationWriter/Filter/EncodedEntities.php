<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\LanguagesManager\TranslationWriter\Filter;

class EncodedEntities extends FilterAbstract
{
    /**
     * Decodes all encoded entities in the given translations
     *
     * @param array $translations
     *
     * @return array   filtered translations
     */
    public function filter($translations)
    {
        foreach ($translations as $pluginName => $pluginTranslations) {
            foreach ($pluginTranslations as $key => $translation) {

                // remove encoded entities
                $decoded = $this->decode($translation);
                if ($translation != $decoded) {
                    $this->filteredData[$pluginName][$key] = $translation;
                    $translations[$pluginName][$key] = $decoded;
                    continue;
                }

            }
        }

        return $translations;
    }

    public function decode($s)
    {
        return html_entity_decode(trim($s), ENT_QUOTES, 'UTF-8');
    }
}
