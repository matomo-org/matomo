<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserCountry;

class Archiver extends \Piwik\Plugin\Archiver
{
    public const COUNTRY_RECORD_NAME = 'UserCountry_country';
    public const REGION_RECORD_NAME = 'UserCountry_region';
    public const CITY_RECORD_NAME = 'UserCountry_city';
    public const DISTINCT_COUNTRIES_METRIC = 'UserCountry_distinctCountries';

    // separate region, city & country info in stored report labels
    public const LOCATION_SEPARATOR = '|';

    public const COUNTRY_FIELD = 'location_country';

    public const REGION_FIELD = 'location_region';

    public const CITY_FIELD = 'location_city';

    public const LATITUDE_FIELD = 'location_latitude';
    public const LONGITUDE_FIELD = 'location_longitude';
}
