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
    /**
     * Returns the list of valid language codes.
     *
     * @return string[] Array of 2 letter ISO code => language name (in english).
     *                  E.g. `array('en' => 'English', 'ja' => 'Japanese')`.
     * @api
     */
    public static function getLanguageList()
    {
        require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/Languages.php';

        $languagesList = $GLOBALS['Piwik_LanguageList'];
        return $languagesList;
    }

    /**
     * Returns the list of language to country mappings.
     *
     * @return string[] Array of 2 letter ISO language code => 2 letter ISO country code.
     *                  E.g. `array('fr' => 'fr') // French => France`.
     * @api
     */
    public static function getLanguageToCountryList()
    {
        require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/LanguageToCountry.php';

        $languagesList = $GLOBALS['Piwik_LanguageToCountry'];
        return $languagesList;
    }
}
