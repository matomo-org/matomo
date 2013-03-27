<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_UserCountry
 */

/**
 * Return the flag image path for a given country
 *
 * @param string $code ISO country code
 * @return string Flag image path
 */
function Piwik_getFlagFromCode($code)
{
    $pathInPiwik = 'plugins/UserCountry/flags/%s.png';
    $pathWithCode = sprintf($pathInPiwik, $code);
    $absolutePath = PIWIK_INCLUDE_PATH . '/' . $pathWithCode;
    if (file_exists($absolutePath)) {
        return $pathWithCode;
    }
    return sprintf($pathInPiwik, Piwik_Tracker_Visit::UNKNOWN_CODE);
}

/**
 * Returns the translated continent name for a given continent code
 *
 * @param string $label Continent code
 * @return string Continent name
 */
function Piwik_ContinentTranslate($label)
{
    if ($label == 'unk' || $label == '') {
        return Piwik_Translate('General_Unknown');
    }
    return Piwik_Translate('UserCountry_continent_' . $label);
}

/**
 * Returns the translated country name for a given country code
 *
 * @param string $label country code
 * @return string Country name
 */
function Piwik_CountryTranslate($label)
{
    if ($label == Piwik_Tracker_Visit::UNKNOWN_CODE || $label == '') {
        return Piwik_Translate('General_Unknown');
    }
    return Piwik_Translate('UserCountry_country_' . $label);
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
function Piwik_UserCountry_getElementFromStringArray($label, $separator, $index, $emptyValue = false)
{
    if ($label == Piwik_DataTable::LABEL_SUMMARY_ROW) {
        return false; // so no metadata/column is added
    }

    $segments = explode($separator, $label);
    return empty($segments[$index]) ? $emptyValue : $segments[$index];
}

/**
 * Returns the region name using the label of a Visits by Region report.
 *
 * @param string $label A label containing a region code followed by '|' and a country code, eg,
 *                      'P3|GB'.
 * @return string|false The region name or false if $label == Piwik_DataTable::LABEL_SUMMARY_ROW.
 */
function Piwik_UserCountry_getRegionName($label)
{
    if ($label == Piwik_DataTable::LABEL_SUMMARY_ROW) {
        return false; // so no metadata/column is added
    }

    if ($label == '') {
        return Piwik_Translate('General_Unknown');
    }

    list($regionCode, $countryCode) = explode(Piwik_UserCountry::LOCATION_SEPARATOR, $label);
    return Piwik_UserCountry_LocationProvider_GeoIp::getRegionNameFromCodes($countryCode, $regionCode);
}

/**
 * Returns the name of a region + the name of the region's country using the label of
 * a Visits by Region report.
 *
 * @param string $label A label containing a region code followed by '|' and a country code, eg,
 *                      'P3|GB'.
 * @return string|false eg. 'Ile de France, France' or false if $label == Piwik_DataTable::LABEL_SUMMARY_ROW.
 */
function Piwik_UserCountry_getPrettyRegionName($label)
{
    if ($label == Piwik_DataTable::LABEL_SUMMARY_ROW) {
        return $label;
    }

    if ($label == '') {
        return Piwik_Translate('General_Unknown');
    }

    list($regionCode, $countryCode) = explode(Piwik_UserCountry::LOCATION_SEPARATOR, $label);

    $result = Piwik_UserCountry_LocationProvider_GeoIp::getRegionNameFromCodes($countryCode, $regionCode);
    if ($countryCode != Piwik_Tracker_Visit::UNKNOWN_CODE && $countryCode != '') {
        $result .= ', ' . Piwik_CountryTranslate($countryCode);
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
 *                      Piwik_DataTable::LABEL_SUMMARY_ROW.
 */
function Piwik_UserCountry_getPrettyCityName($label)
{
    if ($label == Piwik_DataTable::LABEL_SUMMARY_ROW) {
        return $label;
    }

    if ($label == '') {
        return Piwik_Translate('General_Unknown');
    }

    // get city name, region code & country code
    $parts = explode(Piwik_UserCountry::LOCATION_SEPARATOR, $label);
    $cityName = $parts[0];
    $regionCode = $parts[1];
    $countryCode = $parts[2];

    if ($cityName == Piwik_Tracker_Visit::UNKNOWN_CODE || $cityName == '') {
        $cityName = Piwik_Translate('General_Unknown');
    }

    $result = $cityName;
    if ($countryCode != Piwik_Tracker_Visit::UNKNOWN_CODE && $countryCode != '') {
        if ($regionCode != '' && $regionCode != Piwik_Tracker_Visit::UNKNOWN_CODE) {
            $regionName = Piwik_UserCountry_LocationProvider_GeoIp::getRegionNameFromCodes($countryCode, $regionCode);
            $result .= ', ' . $regionName;
        }
        $result .= ', ' . Piwik_CountryTranslate($countryCode);
    }
    return $result;
}
