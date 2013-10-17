<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

namespace Piwik\Translate\Filter;


use Piwik\Translate;

/**
 * @package Piwik
 * @subpackage Piwik::translate
 */
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
        foreach ($translations AS $pluginName => $pluginTranslations) {
            foreach ($pluginTranslations AS $key => $translation) {

                // remove encoded entities
                $decoded = Translate::clean($translation);
                if ($translation != $decoded) {
                    $this->filteredData[$pluginName][$key] = $translation;
                    $translations[$pluginName][$key] = $decoded;
                    continue;
                }

            }
        }

        return $translations;
    }
}