<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Intl\Data\Provider;

/**
 * Provides region related data (continents, countries, etc.).
 */
class RegionDataProvider
{
    /**
     * Returns the list of continent codes.
     *
     * @return string[] Array of 3 letter continent codes
     * @api
     */
    public function getContinentList()
    {
        require __DIR__ . '/../Resources/Countries.php';

        return $GLOBALS['Piwik_ContinentList'];
    }

    /**
     * Returns the list of valid country codes.
     *
     * @param bool $includeInternalCodes
     * @return string[] Array of 2 letter country ISO codes => 3 letter continent code
     * @api
     */
    public static function getCountryList($includeInternalCodes = false)
    {
        require __DIR__ . '/../Resources/Countries.php';

        $countriesList = $GLOBALS['Piwik_CountryList'];
        $extras = $GLOBALS['Piwik_CountryList_Extras'];

        if ($includeInternalCodes) {
            return array_merge($countriesList, $extras);
        }

        return $countriesList;
    }
}
