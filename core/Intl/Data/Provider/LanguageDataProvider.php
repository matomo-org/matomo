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
        require PIWIK_INCLUDE_PATH . '/core/DataFiles/Languages.php';

        return $GLOBALS['Piwik_LanguageList'];
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
        require PIWIK_INCLUDE_PATH . '/core/DataFiles/LanguageToCountry.php';

        return $GLOBALS['Piwik_LanguageToCountry'];
    }
}
