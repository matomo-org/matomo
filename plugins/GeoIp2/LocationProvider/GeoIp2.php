<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\GeoIp2\LocationProvider;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\UserCountry\LocationProvider;

/**
 * Base type for all GeoIP 2 LocationProviders.
 *
 */
abstract class GeoIp2 extends LocationProvider
{
    const GEO_LITE_URL = 'https://geolite.maxmind.com/download/geoip/database/GeoLite2-City.tar.gz';
    const TEST_IP = '194.57.91.215';
    const SWITCH_TO_ISO_REGIONS_OPTION_NAME = 'usercountry.switchtoisoregions';

    /**
     * Cached region name array. Data is from geoipregionvars.php.
     *
     * @var array
     */
    private static $regionNames = null;

    /**
     * Stores possible database file names categorized by the type of information
     * GeoIP databases hold.
     *
     * @var array
     */
    public static $dbNames = array(
        'loc' => array('GeoIP2-City.mmdb', 'GeoIP2-City-Africa.mmdb', 'GeoIP2-City-Asia-Pacific.mmdb', 'GeoIP2-City-Europe.mmdb', 'GeoIP2-City-North-America.mmdb', 'GeoIP2-City-South-America.mmdb', 'GeoIP2-Enterprise.mmdb', 'GeoIP2-Country.mmdb', 'GeoLite2-City.mmdb', 'GeoLite2-Country.mmdb'),
        'isp' => array('GeoIP2-ISP.mmdb', 'GeoLite2-ASN.mmdb'),
    );

    /**
     * Returns true if this provider has been setup correctly, the error message if not.
     *
     * @return bool|string
     */
    public function isWorking()
    {
        // test with an example IP to make sure the provider is working
        // NOTE: At the moment only country, region & city info is tested.
        try {
            $supportedInfo = $this->getSupportedLocationInfo();

            list($testIp, $expectedResult) = self::getTestIpAndResult();

            // get location using test IP
            $location = $this->getLocation(array('ip' => $testIp));

            // check that result is the same as expected
            $isResultCorrect = true;
            foreach ($expectedResult as $key => $value) {
                // if this provider is not configured to support this information type, skip it
                if (empty($supportedInfo[$key])) {
                    continue;
                }

                if (empty($location[$key])
                    || $location[$key] != $value
                ) {
                    $isResultCorrect = false;
                }
            }

            if (!$isResultCorrect) {
                $unknown = Piwik::translate('General_Unknown');

                $location = "'"
                    . (empty($location[self::CITY_NAME_KEY]) ? $unknown : $location[self::CITY_NAME_KEY])
                    . ", "
                    . (empty($location[self::REGION_CODE_KEY]) ? $unknown : $location[self::REGION_CODE_KEY])
                    . ", "
                    . (empty($location[self::COUNTRY_CODE_KEY]) ? $unknown : $location[self::COUNTRY_CODE_KEY])
                    . "'";

                $expectedLocation = "'" . $expectedResult[self::CITY_NAME_KEY] . ", "
                    . $expectedResult[self::REGION_CODE_KEY] . ", "
                    . $expectedResult[self::COUNTRY_CODE_KEY] . "'";

                $bind = array($testIp, $location, $expectedLocation);
                return Piwik::translate('UserCountry_TestIPLocatorFailed', $bind);
            }

            return true;
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * Returns the path of an existing GeoIP 2 database or false if none can be found.
     *
     * @param array $possibleFileNames The list of possible file names for the GeoIP database.
     * @return string|false
     */
    public static function getPathToGeoIpDatabase($possibleFileNames)
    {
        foreach ($possibleFileNames as $filename) {
            $path = self::getPathForGeoIpDatabase($filename);
            if (file_exists($path)) {
                return $path;
            }
        }
        return false;
    }

    /**
     * Returns full path for a GeoIP 2 database managed by Piwik.
     *
     * @param string $filename Name of the .dat file.
     * @return string
     */
    public static function getPathForGeoIpDatabase($filename)
    {
        if (strpos($filename, '/') !== false && file_exists($filename)) {
            return $filename;
        }

        return StaticContainer::get('path.geoip2') . $filename;
    }

    /**
     * Returns test IP used by isWorking and expected result.
     *
     * @return array eg. array('1.2.3.4', array(self::COUNTRY_CODE_KEY => ...))
     */
    private static function getTestIpAndResult()
    {
        static $result = null;
        if (is_null($result)) {
            $expected = array(self::COUNTRY_CODE_KEY => 'FR',
                self::REGION_CODE_KEY  => 'BFC',
                self::CITY_NAME_KEY    => 'Besançon');
            $result = array(self::TEST_IP, $expected);
        }
        return $result;
    }

    public function activate()
    {
        $option = Option::get(self::SWITCH_TO_ISO_REGIONS_OPTION_NAME);
        if (empty($option)) {
            Option::set(self::SWITCH_TO_ISO_REGIONS_OPTION_NAME, time());
        }
    }

    /**
     * Returns true if there is a GeoIP 2 database in the 'misc' directory.
     *
     * @return bool
     */
    public static function isDatabaseInstalled()
    {
        return self::getPathToGeoIpDatabase(self::$dbNames['loc'])
            || self::getPathToGeoIpDatabase(self::$dbNames['isp']);
    }

    /**
     * Returns the type of GeoIP 2 database ('loc' or 'isp') based on the
     * filename (eg, 'GeoLite2-City.mmdb', 'GeoIP2-ISP.mmdb', etc).
     *
     * @param string $filename
     * @return string|false 'loc', 'isp' or false if cannot find a database type.
     */
    public static function getGeoIPDatabaseTypeFromFilename($filename)
    {
        foreach (self::$dbNames as $key => $names) {
            foreach ($names as $name) {
                if ($name === $filename) {
                    return $key;
                }
            }
        }
        return false;
    }

    /**
     * Returns a region name for a country code + region code.
     *
     * @param string $countryCode
     * @param string $regionCode
     * @return string The region name or 'Unknown' (translated).
     */
    public static function getRegionNameFromCodes($countryCode, $regionCode)
    {
        $regionNames = self::getRegionNames();

        $countryCode = strtoupper($countryCode);
        $regionCode = strtoupper($regionCode);

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
    public static function getRegionNames()
    {
        if (is_null(self::$regionNames)) {
            self::$regionNames = require_once __DIR__ . '/../data/isoRegionNames.php';
        }

        return self::$regionNames;
    }

    /**
     * Converts an old FIPS region code to ISO
     *
     * @param string $countryCode
     * @param string $fipsRegionCode
     * @param bool $returnOriginalIfNotFound  return given region code if no mapping was found
     * @return array
     */
    public static function convertRegionCodeToIso($countryCode, $fipsRegionCode, $returnOriginalIfNotFound = false)
    {
        static $mapping;
        if(empty($mapping)) {
            $mapping = include __DIR__ . '/../data/regionMapping.php';
        }
        $countryCode = strtoupper($countryCode);
        if (empty($countryCode) || in_array($countryCode, ['EU', 'AP', 'A1', 'A2'])) {
            return ['', ''];
        }
        if (in_array($countryCode, ['US', 'CA'])) { // US and CA always haven been iso codes
            return [$countryCode, $fipsRegionCode];
        }
        if ($countryCode == 'TI') {
            $countryCode = 'CN';
            $fipsRegionCode = '14';
        }
        $isoRegionCode = $returnOriginalIfNotFound ? $fipsRegionCode : '';
        if (!empty($fipsRegionCode) && !empty($mapping[$countryCode][$fipsRegionCode])) {
            $isoRegionCode = $mapping[$countryCode][$fipsRegionCode];
        }
        return [$countryCode, $isoRegionCode];
    }

    /**
     * Returns an IP address from an array that was passed into getLocation. This
     * will return an IPv4 address or IPv6 address.
     *
     * @param  array $info Must have 'ip' key.
     * @return string|null
     */
    protected function getIpFromInfo($info)
    {
        $ip = \Piwik\Network\IP::fromStringIP($info['ip']);

        return $ip->toString();
    }
}
