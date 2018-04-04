<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\LocationProvider;

use Exception;
use Piwik\Piwik;
use Piwik\Plugins\UserCountry\LocationProvider;

/**
 * Base type for all GeoIP LocationProviders.
 *
 */
abstract class GeoIp extends LocationProvider
{
    /* For testing, use: 'http://piwik-team.s3.amazonaws.com/GeoLiteCity.dat.gz' */
    const GEO_LITE_URL = 'http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz';
    const TEST_IP = '194.57.91.215';

    public static $geoIPDatabaseDir = 'misc';

    /**
     * Stores possible database file names categorized by the type of information
     * GeoIP databases hold.
     *
     * @var array
     */
    public static $dbNames = array(
        'loc' => array('GeoIPCity.dat', 'GeoLiteCity.dat', 'GeoIP.dat'),
        'isp' => array('GeoIPISP.dat'),
        'org' => array('GeoIPOrg.dat'),
    );

    /**
     * Attempts to fill in some missing information in a GeoIP location.
     *
     * This method will call LocationProvider::completeLocationResult and then
     * try to set the region name of the location if the country code & region
     * code are set.
     *
     * @param array $location The location information to modify.
     */
    public function completeLocationResult(&$location)
    {
        parent::completeLocationResult($location);

        list($location[self::COUNTRY_CODE_KEY], $location[self::REGION_CODE_KEY]) = self::convertRegionCodeToIso($location[self::COUNTRY_CODE_KEY], $location[self::REGION_CODE_KEY]);

        if (!empty($location[self::REGION_CODE_KEY])
            && !empty($location[self::COUNTRY_CODE_KEY])
        ) {
            $location[self::REGION_NAME_KEY] = self::getRegionNameFromCodes($location[self::COUNTRY_CODE_KEY], $location[self::REGION_CODE_KEY]);
        }
    }

    /**
     * Returns true if this provider has been setup correctly, the error message if
     * otherwise.
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
     * Returns a region name for a country code + region code.
     *
     * @param string $countryCode
     * @param string $regionCode
     * @return string The region name or 'Unknown' (translated).
     */
    public static function getRegionNameFromCodes($countryCode, $regionCode)
    {
        return GeoIp2::getRegionNameFromCodes($countryCode, $regionCode);
    }

    /**
     * Returns an array of region names mapped by country code & region code.
     *
     * @return array
     */
    public static function getRegionNames()
    {
        return GeoIp2::getRegionNames();
    }

    /**
     * Returns the path of an existing GeoIP database or false if none can be found.
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
     * Returns full path for a GeoIP database managed by Piwik.
     *
     * @param string $filename Name of the .dat file.
     * @return string
     */
    public static function getPathForGeoIpDatabase($filename)
    {
        return PIWIK_INCLUDE_PATH . '/' . self::$geoIPDatabaseDir . '/' . $filename;
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
            // TODO: what happens when IP changes? should we get this information from piwik.org?
            $expected = array(self::COUNTRY_CODE_KEY => 'FR',
                              self::REGION_CODE_KEY  => 'I',
                              self::CITY_NAME_KEY    => 'Besançon');
            $result = array(self::TEST_IP, $expected);
        }
        return $result;
    }

    /**
     * Returns true if there is a GeoIP database in the 'misc' directory.
     *
     * @return bool
     */
    public static function isDatabaseInstalled()
    {
        return self::getPathToGeoIpDatabase(self::$dbNames['loc'])
        || self::getPathToGeoIpDatabase(self::$dbNames['isp'])
        || self::getPathToGeoIpDatabase(self::$dbNames['org']);
    }

    /**
     * Returns the type of GeoIP database ('loc', 'isp' or 'org') based on the
     * filename (eg, 'GeoLiteCity.dat', 'GeoIPISP.dat', etc).
     *
     * @param string $filename
     * @return string|false 'loc', 'isp', 'org', or false if cannot find a database
     *                      type.
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
}