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
class UnnecassaryWhitespaces extends FilterAbstract
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
     * Removes all unnecassary whitespaces and newlines from the given translations
     *
     * @param array $translations
     *
     * @return array   filtered translations
     */
    public function filter($translations)
    {
        foreach ($translations as $pluginName => $pluginTranslations) {
            foreach ($pluginTranslations as $key => $translation) {

                $baseTranslation = '';
                if (isset($this->baseTranslations[$pluginName][$key])) {
                    $baseTranslation = $this->baseTranslations[$pluginName][$key];
                }

                // remove excessive line breaks (and leading/trailing whitespace) from translations
                $stringNoLineBreak = trim($translation);
                $stringNoLineBreak = str_replace("\r", "", $stringNoLineBreak); # remove useless carrige renturns
                $stringNoLineBreak = preg_replace('/(\n[ ]+)/', "\n", $stringNoLineBreak); # remove useless white spaces after line breaks
                $stringNoLineBreak = preg_replace('/([\n]{2,})/', "\n\n", $stringNoLineBreak); # remove excessive line breaks
                if (empty($baseTranslation) || !substr_count($baseTranslation, "\n")) {
                    $stringNoLineBreak = preg_replace("/[\n]+/", " ", $stringNoLineBreak); # remove all line breaks if english string doesn't contain any
                }
                $stringNoLineBreak = preg_replace('/([ ]{2,})/', " ", $stringNoLineBreak); # remove excessive white spaces again as there might be any now, after removing line breaks
                if ($translation !== $stringNoLineBreak) {
                    $this->filteredData[$pluginName][$key] = $translation;
                    $translations[$pluginName][$key] = $stringNoLineBreak;
                    continue;
                }
            }
        }

        return $translations;
    }
}
