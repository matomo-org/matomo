<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\UserCountry;

use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2;
use Piwik\Tracker\Visit;

/**
 * Return the flag image path for a given country
 *
 * @param string $code ISO country code
 * @return string Flag image path
 */
function getFlagFromCode($code)
{
    if (strtolower($code) == 'ti') {
        $code = 'cn';
    }

    $pathInPiwik = 'plugins/Morpheus/icons/dist/flags/%s.png';
    $pathWithCode = sprintf($pathInPiwik, $code);
    $absolutePath = PIWIK_INCLUDE_PATH . '/' . $pathWithCode;
    if (file_exists($absolutePath)) {
        return $pathWithCode;
    }
    return sprintf($pathInPiwik, Visit::UNKNOWN_CODE);
}

/**
 * Returns the translated continent name for a given continent code
 *
 * @param string $label Continent code
 * @return string Continent name
 */
function continentTranslate($label)
{
    if ($label == 'unk' || $label == '') {
        return Piwik::translate('General_Unknown');
    }
    return Piwik::translate('Intl_Continent_' . $label);
}

/**
 * Returns the translated country name for a given country code
 *
 * @param string $label country code
 * @return string Country name
 */
function countryTranslate($label)
{
    if ($label == Visit::UNKNOWN_CODE || $label == '') {
        return Piwik::translate('General_Unknown');
    }

    if (strtolower($label) == 'ti') {
        $label = 'cn';
    }

    // Try to get name from Intl plugin
    $key = 'Intl_Country_' . strtoupper($label);
    $country = Piwik::translate($key);

    if ($country != $key) {
        return $country;
    }

    // Handle special country codes
    $key = 'UserCountry_country_' . $label;
    $country = Piwik::translate($key);

    if ($country != $key) {
        return $country;
    }

    return Piwik::translate('General_Unknown');
}

/**
 * Splits a label by a certain separator and returns the N-th element.
 *
 * @param string $label
 * @param string $separator eg. ',' or '|'
 * @param int $index The element index to extract.
 * @param mixed $emptyValue The value to remove if the element is absent. Defaults to false,
 *                          so no new metadata/column is added.
 * @return string|false Returns false if $label == DataTable::LABEL_SUMMARY_ROW, otherwise
 *                      explode($separator, $label)[$index].
 */
function getElementFromStringArray($label, $separator, $index, $emptyValue = false)
{
    if ($label == DataTable::LABEL_SUMMARY_ROW) {
        return false; // so no metadata/column is added
    }

    $segments = explode($separator, $label);
    return empty($segments[$index]) ? $emptyValue : $segments[$index];
}

/**
 * Returns region name for the given regionCode / countryCode combination
 * using the currently set location provider
 *
 * @param string $countryCode
 * @param string $regionCode
 * @return string
 */
function getRegionNameFromCodes($countryCode, $regionCode)
{
    $name = GeoIp2::getRegionNameFromCodes($countryCode, $regionCode);

    // fallback if no translation with GeoIP2
    if ($name == Piwik::translate('General_Unknown')) {
        $name = getLegacyRegionNameFromCodes($countryCode, $regionCode);
    }

    return $name;
}


/**
 * Returns a region name for a country code + region code.
 *
 * @param string $countryCode
 * @param string $regionCode
 * @return string The region name or 'Unknown' (translated).
 */
function getLegacyRegionNameFromCodes($countryCode, $regionCode)
{
    $regionNames = getRegionNames();

    $countryCode = strtoupper($countryCode);
    $regionCode = strtoupper($regionCode);

    // ensure tibet is shown as region of china
    if ($countryCode == 'TI' && $regionCode == '1') {
        $regionCode = '14';
        $countryCode = 'CN';
    }

    if (isset($regionNames[$countryCode][$regionCode])) {
        return $regionNames[$countryCode][$regionCode];
    } else {
        return Piwik::translate('General_Unknown');
    }
}

/**
 * Returns an array of region names mapped by country code & region code.
 *
 * @return array
 */
function getRegionNames()
{
    static $regionNames;

    if (is_null($regionNames)) {
        $GEOIP_REGION_NAME = array();
        require_once PIWIK_INCLUDE_PATH . '/libs/MaxMindGeoIP/geoipregionvars.php';
        $regionNames = $GEOIP_REGION_NAME;
    }

    return $regionNames;
}


/**
 * Returns the region name using the label of a Visits by Region report.
 *
 * @param string $label A label containing a region code followed by '|' and a country code, eg,
 *                      'P3|GB'.
 * @return string|false The region name or false if $label == DataTable::LABEL_SUMMARY_ROW.
 */
function getRegionName($label)
{
    if ($label == DataTable::LABEL_SUMMARY_ROW) {
        return false; // so no metadata/column is added
    }

    if ($label == '') {
        return Piwik::translate('General_Unknown');
    }

    list($regionCode, $countryCode) = explode(Archiver::LOCATION_SEPARATOR, $label);
    return getRegionNameFromCodes($countryCode, $regionCode);
}

/**
 * Returns the name of a region + the name of the region's country using the label of
 * a Visits by Region report.
 *
 * @param string $label A label containing a region code followed by '|' and a country code, eg,
 *                      'P3|GB'.
 * @return string|false eg. 'Ile de France, France' or false if $label == DataTable::LABEL_SUMMARY_ROW.
 */
function getPrettyRegionName($label)
{
    if ($label == DataTable::LABEL_SUMMARY_ROW) {
        return $label;
    }

    if ($label == '') {
        return Piwik::translate('General_Unknown');
    }

    list($regionCode, $countryCode) = explode(Archiver::LOCATION_SEPARATOR, $label);

    $result = getRegionNameFromCodes($countryCode, $regionCode);
    if ($countryCode != Visit::UNKNOWN_CODE && $countryCode != '') {
        $result .= ', ' . countryTranslate($countryCode);
    }
    return $result;
}

/**
 * Returns the name of a city + the name of its region + the name of its country using
 * the label of a Visits by City report.
 *
 * @param string $label A label containing a city name, region code + country code,
 *                      separated by two '|' chars: 'Paris|A8|FR'
 * @return string|false eg. 'Paris, Ile de France, France' or false if $label ==
 *                      DataTable::LABEL_SUMMARY_ROW.
 */
function getPrettyCityName($label)
{
    if ($label == DataTable::LABEL_SUMMARY_ROW) {
        return $label;
    }

    if ($label == '') {
        return Piwik::translate('General_Unknown');
    }

    // get city name, region code & country code
    $parts = explode(Archiver::LOCATION_SEPARATOR, $label);
    $cityName = $parts[0];
    $regionCode = $parts[1];
    $countryCode = @$parts[2];

    if ($cityName == Visit::UNKNOWN_CODE || $cityName == '') {
        $cityName = Piwik::translate('General_Unknown');
    }

    $result = $cityName;
    if ($countryCode != Visit::UNKNOWN_CODE && $countryCode != '') {
        if ($regionCode != '' && $regionCode != Visit::UNKNOWN_CODE) {
            $regionName = getRegionNameFromCodes($countryCode, $regionCode);
            $result .= ', ' . $regionName;
        }
        $result .= ', ' . countryTranslate($countryCode);
    }
    return $result;
}
