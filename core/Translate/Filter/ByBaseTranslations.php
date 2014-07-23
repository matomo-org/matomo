<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Translate\Filter;

/**
 */
class ByBaseTranslations extends FilterAbstract
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
     * Removes all translations that aren't present in the base translations set in constructor
     *
     * @param  array $translations
     *
     * @return array   filtered translations
     */
    public function filter($translations)
    {
        $cleanedTranslations = array();

        foreach ($translations as $pluginName => $pluginTranslations) {

            if (empty($this->baseTranslations[$pluginName])) {
                $this->filteredData[$pluginName] = $pluginTranslations;
                continue;
            }

            foreach ($pluginTranslations as $key => $translation) {
                if (isset($this->baseTranslations[$pluginName][$key])) {
                    $cleanedTranslations[$pluginName][$key] = $translation;
                }
            }

            if (!empty($cleanedTranslations[$pluginName])) {
                $diff = array_diff($translations[$pluginName], $cleanedTranslations[$pluginName]);
            } else {
                $diff = $translations[$pluginName];
            }
            if (!empty($diff)) {
                $this->filteredData[$pluginName] = $diff;
            }
        }

        return $cleanedTranslations;
    }
}
