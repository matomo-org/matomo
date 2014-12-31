<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Intl\Data\Provider;

/**
 * Provides language data.
 */
class LanguageDataProvider
{
    private $languageList;
    private $languageToCountryList;

    /**
     * Returns the list of valid language codes.
     *
     * @return string[] Array of 2 letter ISO code => language name (in english).
     *                  E.g. `array('en' => 'English', 'ja' => 'Japanese')`.
     * @api
     */
    public function getLanguageList()
    {
        if ($this->languageList === null) {
            $this->languageList = require __DIR__ . '/../Resources/languages.php';
        }

        return $this->languageList;
    }

    /**
     * Returns the list of language to country mappings.
     *
     * @return string[] Array of 2 letter ISO language code => 2 letter ISO country code.
     *                  E.g. `array('fr' => 'fr') // French => France`.
     * @api
     */
    public function getLanguageToCountryList()
    {
        if ($this->languageToCountryList === null) {
            $this->languageToCountryList = require __DIR__ . '/../Resources/languages-to-countries.php';
        }

        return $this->languageToCountryList;
    }
}
