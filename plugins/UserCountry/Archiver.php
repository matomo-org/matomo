<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\UserCountry;

class Archiver extends \Piwik\Plugin\Archiver
{
    const COUNTRY_RECORD_NAME = 'UserCountry_country';
    const REGION_RECORD_NAME = 'UserCountry_region';
    const CITY_RECORD_NAME = 'UserCountry_city';
    const DISTINCT_COUNTRIES_METRIC = 'UserCountry_distinctCountries';

    // separate region, city & country info in stored report labels
    const LOCATION_SEPARATOR = '|';

    const COUNTRY_FIELD = 'location_country';

    const REGION_FIELD = 'location_region';

    const CITY_FIELD = 'location_city';

    const LATITUDE_FIELD = 'location_latitude';
    const LONGITUDE_FIELD = 'location_longitude';
}
