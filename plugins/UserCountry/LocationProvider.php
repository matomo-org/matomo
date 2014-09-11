<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry;

use Exception;
use Piwik\Common;
use Piwik\IP;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\UserCountry\LocationProvider\DefaultProvider;
use Piwik\Tracker\Cache;
use ReflectionClass;

/**
 * @see plugins/UserCountry/LocationProvider/Default.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/LocationProvider/Default.php';

/**
 * @see plugins/UserCountry/LocationProvider/GeoIp.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/LocationProvider/GeoIp.php';

/**
 * The base class of all LocationProviders.
 *
 * LocationProviders attempt to determine a visitor's location using
 * visit information. All LocationProviders require a visitor's IP address, some
 * require more, such as the browser language.
 */
abstract class LocationProvider
{
    const NOT_INSTALLED = 0;
    const INSTALLED = 1;
    const BROKEN = 2;

    const CURRENT_PROVIDER_OPTION_NAME = 'usercountry.location_provider';

    const GEOGRAPHIC_COORD_PRECISION = 3;

    const CONTINENT_CODE_KEY = 'continent_code';
    const CONTINENT_NAME_KEY = 'continent_name';
    const COUNTRY_CODE_KEY = 'country_code';
    const COUNTRY_NAME_KEY = 'country_name';
    const REGION_CODE_KEY = 'region_code';
    const REGION_NAME_KEY = 'region_name';
    const CITY_NAME_KEY = 'city_name';
    const AREA_CODE_KEY = 'area_code';
    const LATITUDE_KEY = 'lat';
    const LONGITUDE_KEY = 'long';
    const POSTAL_CODE_KEY = 'postal_code';
    const ISP_KEY = 'isp';
    const ORG_KEY = 'org';

    /**
     * An array of all provider instances. Access it through static methods.
     *
     * @var array
     */
    public static $providers = null;

    /**
     * Returns location information based on visitor information.
     *
     * The result of this function will be an array. The array can store some or all of
     * the following information:
     *
     * - Continent Code: The code of the visitor's continent.
     *       (array key is self::CONTINENT_CODE_KEY)
     * - Continent Name: The name of the visitor's continent.
     *       (array key is self::CONTINENT_NAME_KEY)
     * - Country Code: The code of the visitor's country.
     *       (array key is self::COUNTRY_CODE_KEY)
     * - Country Name: The name of the visitor's country.
     *       (array key is self::COUNTRY_NAME_KEY)
     * - Region Code: The code of the visitor's region.
     *       (array key is self::REGION_CODE_KEY)
     * - Region Name: The name of the visitor's region.
     *       (array key is self::REGION_NAME_KEY)
     * - City Name: The name of the visitor's city.
     *       (array key is self::CITY_NAME_KEY)
     * - Area Code: The visitor's area code.
     *       (array key is self::AREA_CODE_KEY)
     * - Latitude: The visitor's latitude.
     *       (array key is self::LATITUDE_KEY)
     * - Longitude: The visitor's longitude.
     *       (array key is self::LONGITUDE_KEY)
     * - Postal Code: The visitor's postal code.
     *       (array key is self::POSTAL_CODE_KEY)
     * - ISP: The visitor's ISP.
     *       (array key is self::ISP_KEY)
     * - Org: The company/organization of the visitor's IP.
     *       (array key is self::ORG_KEY)
     *
     * All LocationProviders will attempt to return the country of the visitor.
     *
     * @param array $info What this must contain depends on the specific provider
     *                    implementation. All providers require an 'ip' key mapped
     *                    to the visitor's IP address.
     * @return array|false
     */
    abstract public function getLocation($info);

    /**
     * Returns true if this provider is available for use, false if otherwise.
     *
     * @return bool
     */
    abstract public function isAvailable();

    /**
     * Returns true if this provider is working, false if otherwise.
     *
     * @return bool
     */
    abstract public function isWorking();

    /**
     * Returns an array mapping location result keys w/ bool values indicating whether
     * that information is supported by this provider. If it is not supported, that means
     * this provider either cannot get this information, or is not configured to get it.
     *
     * @return array eg. array(self::CONTINENT_CODE_KEY => true,
     *                         self::CONTINENT_NAME_KEY => true,
     *                         self::ORG_KEY => false)
     *               The result is not guaranteed to have keys for every type of location
     *               info.
     */
    abstract public function getSupportedLocationInfo();

    /**
     * Returns every available provider instance.
     *
     * @return LocationProvider[]
     */
    public static function getAllProviders()
    {
        if (is_null(self::$providers)) {
            self::$providers = array();
            foreach (get_declared_classes() as $klass) {
                if (is_subclass_of($klass, 'Piwik\Plugins\UserCountry\LocationProvider')) {
                    $klassInfo = new ReflectionClass($klass);
                    if ($klassInfo->isAbstract()) {
                        continue;
                    }

                    self::$providers[] = new $klass;
                }
            }
        }

        return self::$providers;
    }

    /**
     * Returns all provider instances that are 'available'. An 'available' provider
     * is one that is available for use. They may not necessarily be working.
     *
     * @return array
     */
    public static function getAvailableProviders()
    {
        $result = array();
        foreach (self::getAllProviders() as $provider) {
            if ($provider->isAvailable()) {
                $result[] = $provider;
            }
        }
        return $result;
    }

    /**
     * Returns an array mapping provider IDs w/ information about the provider,
     * for each location provider.
     *
     * The following information is provided for each provider:
     *   'id' - The provider's unique string ID.
     *   'title' - The provider's title.
     *   'description' - A description of how the location provider works.
     *   'status' - Either self::NOT_INSTALLED, self::INSTALLED or self::BROKEN.
     *   'statusMessage' - If the status is self::BROKEN, then the message describes why.
     *   'location' - A pretty formatted location of the current IP address
     *                (IP::getIpFromHeader()).
     *
     * An example result:
     * array(
     *     'geoip_php' => array('id' => 'geoip_php',
     *                          'title' => '...',
     *                          'desc' => '...',
     *                          'status' => GeoIp::BROKEN,
     *                          'statusMessage' => '...',
     *                          'location' => '...')
     *     'geoip_serverbased' => array(...)
     * )
     *
     * @param string $newline What to separate lines with in the pretty locations.
     * @param bool $includeExtra Whether to include ISP/Org info in formatted location.
     * @return array
     */
    public static function getAllProviderInfo($newline = "\n", $includeExtra = false)
    {
        $allInfo = array();
        foreach (self::getAllProviders() as $provider) {
            $info = $provider->getInfo();

            $status = self::INSTALLED;
            $location = false;
            $statusMessage = false;

            $availableOrMessage = $provider->isAvailable();
            if ($availableOrMessage !== true) {
                $status = self::NOT_INSTALLED;
                if (is_string($availableOrMessage)) {
                    $statusMessage = $availableOrMessage;
                }
            } else {
                $workingOrError = $provider->isWorking();
                if ($workingOrError === true) // if the implementation is configured correctly, get the location
                {
                    $locInfo = array('ip'                => IP::getIpFromHeader(),
                                     'lang'              => Common::getBrowserLanguage(),
                                     'disable_fallbacks' => true);

                    $location = $provider->getLocation($locInfo);
                    $location = self::prettyFormatLocation($location, $newline, $includeExtra);
                } else // otherwise set an error message describing why
                {
                    $status = self::BROKEN;
                    $statusMessage = $workingOrError;
                }
            }

            $info['status'] = $status;
            $info['statusMessage'] = $statusMessage;
            $info['location'] = $location;

            $allInfo[$info['order']] = $info;
        }

        ksort($allInfo);

        $result = array();
        foreach ($allInfo as $info) {
            $result[$info['id']] = $info;
        }
        return $result;
    }

    /**
     * Returns the ID of the currently used location provider.
     *
     * The used provider is stored in the 'usercountry.location_provider' option.
     *
     * This function should not be called by the Tracker.
     *
     * @return string
     */
    public static function getCurrentProviderId()
    {
        $optionValue = Option::get(self::CURRENT_PROVIDER_OPTION_NAME);
        return $optionValue === false ? DefaultProvider::ID : $optionValue;
    }

    /**
     * Returns the provider instance of the current location provider.
     *
     * This function should not be called by the Tracker.
     *
     * @return \Piwik\Plugins\UserCountry\LocationProvider
     */
    public static function getCurrentProvider()
    {
        return self::getProviderById(self::getCurrentProviderId());
    }

    /**
     * Sets the provider to use when tracking.
     *
     * @param string $providerId The ID of the provider to use.
     * @return \Piwik\Plugins\UserCountry\LocationProvider The new current provider.
     * @throws Exception If the provider ID is invalid.
     */
    public static function setCurrentProvider($providerId)
    {
        $provider = self::getProviderById($providerId);
        if ($provider === false) {
            throw new Exception(
                "Invalid provider ID '$providerId'. The provider either does not exist or is not available");
        }
        Option::set(self::CURRENT_PROVIDER_OPTION_NAME, $providerId);
        Cache::clearCacheGeneral();
        return $provider;
    }

    /**
     * Returns a provider instance by ID or false if the ID is invalid or unavailable.
     *
     * @param string $providerId
     * @return \Piwik\Plugins\UserCountry\LocationProvider|false
     */
    public static function getProviderById($providerId)
    {
        foreach (self::getAvailableProviders() as $provider) {
            if ($provider->getId() == $providerId) {
                return $provider;
            }
        }

        return false;
    }

    public function getId()
    {
        $info = $this->getInfo();

        return $info['id'];
    }

    /**
     * Tries to fill in any missing information in a location result.
     *
     * This method will try to set the continent code, continent name and country code
     * using other information.
     *
     * Note: This function must always be called by location providers in getLocation.
     *
     * @param array $location The location information to modify.
     */
    public function completeLocationResult(&$location)
    {
        // fill in continent code if country code is present
        if (empty($location[self::CONTINENT_CODE_KEY])
            && !empty($location[self::COUNTRY_CODE_KEY])
        ) {
            $countryCode = strtolower($location[self::COUNTRY_CODE_KEY]);
            $location[self::CONTINENT_CODE_KEY] = Common::getContinent($countryCode);
        }

        // fill in continent name if continent code is present
        if (empty($location[self::CONTINENT_NAME_KEY])
            && !empty($location[self::CONTINENT_CODE_KEY])
        ) {
            $continentCode = strtolower($location[self::CONTINENT_CODE_KEY]);
            $location[self::CONTINENT_NAME_KEY] = Piwik::translate('UserCountry_continent_' . $continentCode);
        }

        // fill in country name if country code is present
        if (empty($location[self::COUNTRY_NAME_KEY])
            && !empty($location[self::COUNTRY_CODE_KEY])
        ) {
            $countryCode = strtolower($location[self::COUNTRY_CODE_KEY]);
            $location[self::COUNTRY_NAME_KEY] = Piwik::translate('UserCountry_country_' . $countryCode);
        }

        // deal w/ improper latitude/longitude & round proper values
        if (!empty($location[self::LATITUDE_KEY])) {
            if (is_numeric($location[self::LATITUDE_KEY])) {
                $location[self::LATITUDE_KEY] = round($location[self::LATITUDE_KEY], self::GEOGRAPHIC_COORD_PRECISION);
            } else {
                unset($location[self::LATITUDE_KEY]);
            }
        }

        if (!empty($location[self::LONGITUDE_KEY])) {
            if (is_numeric($location[self::LONGITUDE_KEY])) {
                $location[self::LONGITUDE_KEY] = round($location[self::LONGITUDE_KEY], self::GEOGRAPHIC_COORD_PRECISION);
            } else {
                unset($location[self::LONGITUDE_KEY]);
            }
        }
    }

    /**
     * Returns a prettified location result.
     *
     * @param array|false $locationInfo
     * @param string $newline The line separator (ie, \n or <br/>).
     * @param bool $includeExtra Whether to include ISP/Organization info.
     * @return string
     */
    public static function prettyFormatLocation($locationInfo, $newline = "\n", $includeExtra = false)
    {
        if ($locationInfo === false) {
            return Piwik::translate('General_Unknown');
        }

        // add latitude/longitude line
        $lines = array();
        if (!empty($locationInfo[self::LATITUDE_KEY])
            && !empty($locationInfo[self::LONGITUDE_KEY])
        ) {
            $lines[] = '(' . $locationInfo[self::LATITUDE_KEY] . ', ' . $locationInfo[self::LONGITUDE_KEY] . ')';
        }

        // add city/state line
        $cityState = array();
        if (!empty($locationInfo[self::CITY_NAME_KEY])) {
            $cityState[] = $locationInfo[self::CITY_NAME_KEY];
        }

        if (!empty($locationInfo[self::REGION_CODE_KEY])) {
            $cityState[] = $locationInfo[self::REGION_CODE_KEY];
        } else if (!empty($locationInfo[self::REGION_NAME_KEY])) {
            $cityState[] = $locationInfo[self::REGION_NAME_KEY];
        }

        if (!empty($cityState)) {
            $lines[] = implode(', ', $cityState);
        }

        // add postal code line
        if (!empty($locationInfo[self::POSTAL_CODE_KEY])) {
            $lines[] = $locationInfo[self::POSTAL_CODE_KEY];
        }

        // add country line
        if (!empty($locationInfo[self::COUNTRY_NAME_KEY])) {
            $lines[] = $locationInfo[self::COUNTRY_NAME_KEY];
        } else if (!empty($locationInfo[self::COUNTRY_CODE_KEY])) {
            $lines[] = $locationInfo[self::COUNTRY_CODE_KEY];
        }

        // add extra information (ISP/Organization)
        if ($includeExtra) {
            $lines[] = '';

            $unknown = Piwik::translate('General_Unknown');

            $org = !empty($locationInfo[self::ORG_KEY]) ? $locationInfo[self::ORG_KEY] : $unknown;
            $lines[] = "Org: $org";

            $isp = !empty($locationInfo[self::ISP_KEY]) ? $locationInfo[self::ISP_KEY] : $unknown;
            $lines[] = "ISP: $isp";
        }

        return implode($newline, $lines);
    }

    /**
     * Returns an IP address from an array that was passed into getLocation. This
     * will return an IPv4 address or false if the address is IPv6 (IPv6 is not
     * supported yet).
     *
     * @param  array $info Must have 'ip' key.
     * @return string|bool
     */
    protected function getIpFromInfo($info)
    {
        $ip = $info['ip'];
        if (IP::isMappedIPv4($ip)) {
            return IP::getIPv4FromMappedIPv6($ip);
        } else if (IP::isIPv6($ip)) // IPv6 is not supported (yet)
        {
            return false;
        } else {
            return $ip;
        }
    }
}

