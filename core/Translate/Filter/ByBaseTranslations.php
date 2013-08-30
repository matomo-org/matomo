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

use Piwik\Translate\Filter\FilterAbstract;

/**
 * @package Piwik
 * @subpackage Piwik_Translate
 */
class ByBaseTranslations extends FilterAbstract
{
    /**
     * Removes all translations that aren't present in the base translations set in constructor
     *
     * @param  array  $translations
     *
     * @return array   filtered translations
     */
    public function filter($translations)
    {
        $cleanedTranslations = array();

        foreach ($translations AS $pluginName => $pluginTranslations) {

            if (empty($this->_baseTranslations[$pluginName])) {
                $this->_filteredData[$pluginName] = $pluginTranslations;
                continue;
            }

            foreach ($pluginTranslations as $key => $translation) {
                if (isset($this->_baseTranslations[$pluginName][$key])) {
                    $cleanedTranslations[$pluginName][$key] = $translation;
                }
            }

            if (!empty($cleanedTranslations[$pluginName])) {
                $diff = array_diff($translations[$pluginName], $cleanedTranslations[$pluginName]);
            } else {
                $diff = $translations[$pluginName];
            }
            if (!empty($diff)) $this->_filteredData[$pluginName] = $diff;
        }

        return $cleanedTranslations;
    }
}