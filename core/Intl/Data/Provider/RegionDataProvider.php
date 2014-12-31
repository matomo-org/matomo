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
    private $continentList;

    /**
     * Returns the list of continent codes.
     *
     * @return string[] Array of 3 letter continent codes
     * @api
     */
    public function getContinentList()
    {
        if ($this->continentList === null) {
            $this->continentList = require __DIR__ . '/../Resources/continents.php';
        }

        return $this->continentList;
    }

    /**
     * Returns the list of valid country codes.
     *
     * @param bool $includeInternalCodes
     * @return string[] Array of 2 letter country ISO codes => 3 letter continent code
     * @api
     */
    public function getCountryList($includeInternalCodes = false)
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
