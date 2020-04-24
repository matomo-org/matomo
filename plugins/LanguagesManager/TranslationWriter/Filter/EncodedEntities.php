<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\LanguagesManager\TranslationWriter\Filter;

use Piwik\Container\StaticContainer;

class EncodedEntities extends FilterAbstract
{
    protected $baseTranslations = array();

    /**
     * Sets base translations
     *
     * @param array $baseTranslations
     */
    public function __construct($baseTranslations = array())
    {
        $this->baseTranslations = $baseTranslations;
    }

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

                if (isset($this->baseTranslations[$pluginName][$key]) &&
                    $this->baseTranslations[$pluginName][$key] != StaticContainer::get('Piwik\Translation\Translator')->clean($this->baseTranslations[$pluginName][$key])) {
                    continue; // skip if base translation already contains encoded entities
                }

                // remove encoded entities
                $decoded = StaticContainer::get('Piwik\Translation\Translator')->clean($translation);
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
