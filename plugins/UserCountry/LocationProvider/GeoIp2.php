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
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\UserCountry\GeoIPLegacyAutoUpdater;
use Piwik\Plugins\UserCountry\LocationProvider;

/**
 * Base type for all GeoIP 2 LocationProviders.
 *
 */
abstract class GeoIp2 extends LocationProvider
{
    /* For testing, use: 'http://piwik-team.s3.amazonaws.com/GeoLite2-City.tar.gz' */
    const GEO_LITE_URL = 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.tar.gz';
    const TEST_IP = '194.57.91.215';

    public static $geoIPDatabaseDir = 'misc';

    /**
     * Stores possible database file names categorized by the type of information
     * GeoIP databases hold.
     *
     * @var array
     */
    public static $dbNames = array(
        'loc' => array('GeoIP2-City.mmdb', 'GeoIP2-Enterprise.mmdb', 'GeoLite2-City.mmdb', 'GeoIP2-Country.mmdb', 'GeoLite2-Country.mmdb'),
        'isp' => array('GeoIP2-ISP.mmdb'),
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
     * Remember time when switched to any GeoIP 2 provider, so we are able to convert data stored before
     */
    public function activate()
    {
        $switched = Option::get(self::SWITCH_TO_GEOIP2_OPTION_NAME);

        if (empty($switched)) {
            Option::set(self::SWITCH_TO_GEOIP2_OPTION_NAME, time());
            // remove auto updating for legacy database
            GeoIPLegacyAutoUpdater::clearOptions();
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
            $expected = array(self::COUNTRY_CODE_KEY => 'FR',
                              self::REGION_CODE_KEY  => 'BFC',
                              self::CITY_NAME_KEY    => 'Besançon');
            $result = array(self::TEST_IP, $expected);
        }
        return $result;
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
}