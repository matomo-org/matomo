<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\LocationProvider\GeoIp;

use Piwik\Log;
use Piwik\Piwik;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp;

/**
 * A LocationProvider that uses the PHP implementation of GeoIP.
 *
 */
class Php extends GeoIp
{
    const ID = 'geoip_php';
    const TITLE = 'GeoIP (Php)';

    /**
     * The GeoIP database instances used. This array will contain at most three
     * of them: one for location info, one for ISP info and another for organization
     * info.
     *
     * Each instance is mapped w/ one of the following keys: 'loc', 'isp', 'org'
     *
     * @var array of GeoIP instances
     */
    private $geoIpCache = array();

    /**
     * Possible filenames for each type of GeoIP database. When looking for a database
     * file in the 'misc' subdirectory, files with these names will be looked for.
     *
     * This variable is an array mapping either the 'loc', 'isp' or 'org' strings with
     * an array of filenames.
     *
     * By default, this will be set to Php::$dbNames.
     *
     * @var array
     */
    private $customDbNames;

    /**
     * Constructor.
     *
     * @param array|bool $customDbNames The possible filenames for each type of GeoIP database.
     *                                   eg array(
     *                                       'loc' => array('GeoLiteCity.dat'),
     *                                       'isp' => array('GeoIP.dat', 'GeoIPISP.dat')
     *                                       'org' => array('GeoIPOrg.dat')
     *                                   )
     *                                   If a key is missing (or the parameter not supplied), then the
     *                                   default database names are used.
     */
    public function __construct($customDbNames = false)
    {
        $this->customDbNames = parent::$dbNames;
        if ($customDbNames !== false) {
            foreach ($this->customDbNames as $key => $names) {
                if (isset($customDbNames[$key])) {
                    $this->customDbNames[$key] = $customDbNames[$key];
                }
            }
        }
    }

    /**
     * Closes all open geoip instances.
     */
    public function __destruct()
    {
        foreach ($this->geoIpCache as $instance) {
            geoip_close($instance);
        }
    }

    /**
     * Uses a GeoIP database to get a visitor's location based on their IP address.
     *
     * This function will return different results based on the data used. If a city
     * database is used, it may return the country code, region code, city name, area
     * code, latitude, longitude and postal code of the visitor.
     *
     * Alternatively, if used with a country database, only the country code will be
     * returned.
     *
     * @param array $info Must have an 'ip' field.
     * @return array
     */
    public function getLocation($info)
    {
        $ip = $this->getIpFromInfo($info);

        $result = array();

        $locationGeoIp = $this->getGeoIpInstance($key = 'loc');
        if ($locationGeoIp) {
            switch ($locationGeoIp->databaseType) {
                case GEOIP_CITY_EDITION_REV0: // city database type
                case GEOIP_CITY_EDITION_REV1:
                case GEOIP_CITYCOMBINED_EDITION:
                    $location = geoip_record_by_addr($locationGeoIp, $ip);
                    if (!empty($location)) {
                        $result[self::COUNTRY_CODE_KEY] = $location->country_code;
                        $result[self::REGION_CODE_KEY] = $location->region;
                        $result[self::CITY_NAME_KEY] = utf8_encode($location->city);
                        $result[self::AREA_CODE_KEY] = $location->area_code;
                        $result[self::LATITUDE_KEY] = $location->latitude;
                        $result[self::LONGITUDE_KEY] = $location->longitude;
                        $result[self::POSTAL_CODE_KEY] = $location->postal_code;
                    }
                    break;
                case GEOIP_REGION_EDITION_REV0: // region database type
                case GEOIP_REGION_EDITION_REV1:
                    $location = geoip_region_by_addr($locationGeoIp, $ip);
                    if (!empty($location)) {
                        $result[self::COUNTRY_CODE_KEY] = $location[0];
                        $result[self::REGION_CODE_KEY] = $location[1];
                    }
                    break;
                case GEOIP_COUNTRY_EDITION: // country database type
                    $result[self::COUNTRY_CODE_KEY] = geoip_country_code_by_addr($locationGeoIp, $ip);
                    break;
                default: // unknown database type, log warning and fallback to country edition
                    Log::warning("Found unrecognized database type: %s", $locationGeoIp->databaseType);

                    $result[self::COUNTRY_CODE_KEY] = geoip_country_code_by_addr($locationGeoIp, $ip);
                    break;
            }
        }

        // NOTE: ISP & ORG require commercial dbs to test. this code has been tested manually,
        // but not by system tests.
        $ispGeoIp = $this->getGeoIpInstance($key = 'isp');
        if ($ispGeoIp) {
            $isp = geoip_org_by_addr($ispGeoIp, $ip);
            if (!empty($isp)) {
                $result[self::ISP_KEY] = utf8_encode($isp);
            }
        }

        $orgGeoIp = $this->getGeoIpInstance($key = 'org');
        if ($orgGeoIp) {
            $org = geoip_org_by_addr($orgGeoIp, $ip);
            if (!empty($org)) {
                $result[self::ORG_KEY] = utf8_encode($org);
            }
        }

        if (empty($result)) {
            return false;
        }

        $this->completeLocationResult($result);
        return $result;
    }

    /**
     * Returns true if this location provider is available. Piwik ships w/ the MaxMind
     * PHP library, so this provider is available if a location GeoIP database can be found.
     *
     * @return bool
     */
    public function isAvailable()
    {
        $path = self::getPathToGeoIpDatabase($this->customDbNames['loc']);
        return $path !== false;
    }

    /**
     * Returns true if this provider has been setup correctly, the error message if
     * otherwise.
     *
     * @return bool|string
     */
    public function isWorking()
    {
        if (!function_exists('mb_internal_encoding')) {
            return Piwik::translate('UserCountry_GeoIPCannotFindMbstringExtension',
                array('mb_internal_encoding', 'mbstring'));
        }

        $geoIpError = false;
        $catchGeoIpError = function ($errno, $errstr, $errfile, $errline) use (&$geoIpError) {
            $filename = basename($errfile);
            if ($filename == 'geoip.inc'
                || $filename == 'geoipcity.inc'
            ) {
                $geoIpError = array($errno, $errstr, $errfile, $errline);
            } else {
                throw new \Exception("Error in PHP GeoIP provider: $errstr on line $errline of $errfile"); // unexpected
            }
        };

        // catch GeoIP errors
        set_error_handler($catchGeoIpError);
        $result = parent::isWorking();
        restore_error_handler();

        if ($geoIpError) {
            list($errno, $errstr, $errfile, $errline) = $geoIpError;
            Log::warning("Got GeoIP error when testing PHP GeoIP location provider: %s(%s): %s", $errfile, $errline, $errstr);

            return Piwik::translate('UserCountry_GeoIPIncorrectDatabaseFormat');
        }

        return $result;
    }

    /**
     * Returns an array describing the types of location information this provider will
     * return.
     *
     * The location info this provider supports depends on what GeoIP databases it can
     * find.
     *
     * This provider will always support country & continent information.
     *
     * If a region database is found, then region code & name information will be
     * supported.
     *
     * If a city database is found, then region code, region name, city name,
     * area code, latitude, longitude & postal code are all supported.
     *
     * If an organization database is found, organization information is
     * supported.
     *
     * If an ISP database is found, ISP information is supported.
     *
     * @return array
     */
    public function getSupportedLocationInfo()
    {
        $result = array();

        // country & continent info always available
        $result[self::CONTINENT_CODE_KEY] = true;
        $result[self::CONTINENT_NAME_KEY] = true;
        $result[self::COUNTRY_CODE_KEY] = true;
        $result[self::COUNTRY_NAME_KEY] = true;

        $locationGeoIp = $this->getGeoIpInstance($key = 'loc');
        if ($locationGeoIp) {
            switch ($locationGeoIp->databaseType) {
                case GEOIP_CITY_EDITION_REV0: // city database type
                case GEOIP_CITY_EDITION_REV1:
                case GEOIP_CITYCOMBINED_EDITION:
                    $result[self::REGION_CODE_KEY] = true;
                    $result[self::REGION_NAME_KEY] = true;
                    $result[self::CITY_NAME_KEY] = true;
                    $result[self::AREA_CODE_KEY] = true;
                    $result[self::LATITUDE_KEY] = true;
                    $result[self::LONGITUDE_KEY] = true;
                    $result[self::POSTAL_CODE_KEY] = true;
                    break;
                case GEOIP_REGION_EDITION_REV0: // region database type
                case GEOIP_REGION_EDITION_REV1:
                    $result[self::REGION_CODE_KEY] = true;
                    $result[self::REGION_NAME_KEY] = true;
                    break;
                default: // country or unknown database type
                    break;
            }
        }

        // check if isp info is available
        if ($this->getGeoIpInstance($key = 'isp')) {
            $result[self::ISP_KEY] = true;
        }

        // check of org info is available
        if ($this->getGeoIpInstance($key = 'org')) {
            $result[self::ORG_KEY] = true;
        }

        return $result;
    }

    /**
     * Returns information about this location provider. Contains an id, title & description:
     *
     * array(
     *     'id' => 'geoip_php',
     *     'title' => '...',
     *     'description' => '...'
     * );
     *
     * @return array
     */
    public function getInfo()
    {
        $desc = Piwik::translate('UserCountry_GeoIpLocationProviderDesc_Php1') . '<br/><br/>'
            . Piwik::translate('UserCountry_GeoIpLocationProviderDesc_Php2',
                array('<strong><em>', '</em></strong>', '<strong><em>', '</em></strong>'));
        $installDocs = '<em><a target="_blank" href="http://piwik.org/faq/how-to/#faq_163">'
            . Piwik::translate('UserCountry_HowToInstallGeoIPDatabases')
            . '</em></a>';

        $availableDatabaseTypes = array();
        if (self::getPathToGeoIpDatabase(array('GeoIPCity.dat', 'GeoLiteCity.dat')) !== false) {
            $availableDatabaseTypes[] = Piwik::translate('UserCountry_City');
        }
        if (self::getPathToGeoIpDatabase(array('GeoIPRegion.dat')) !== false) {
            $availableDatabaseTypes[] = Piwik::translate('UserCountry_Region');
        }
        if (self::getPathToGeoIpDatabase(array('GeoIPCountry.dat')) !== false) {
            $availableDatabaseTypes[] = Piwik::translate('UserCountry_Country');
        }
        if (self::getPathToGeoIpDatabase(array('GeoIPISP.dat')) !== false) {
            $availableDatabaseTypes[] = 'ISP';
        }
        if (self::getPathToGeoIpDatabase(array('GeoIPOrg.dat')) !== false) {
            $availableDatabaseTypes[] = Piwik::translate('UserCountry_Organization');
        }

        $extraMessage = '<strong><em>' . Piwik::translate('General_Note') . '</em></strong>:&nbsp;'
            . Piwik::translate('UserCountry_GeoIPImplHasAccessTo') . ':&nbsp;<strong><em>'
            . implode(', ', $availableDatabaseTypes) . '</em></strong>.';

        return array('id'            => self::ID,
                     'title'         => self::TITLE,
                     'description'   => $desc,
                     'install_docs'  => $installDocs,
                     'extra_message' => $extraMessage,
                     'order'         => 2);
    }

    /**
     * Returns a GeoIP instance. Creates it if necessary.
     *
     * @param string $key 'loc', 'isp' or 'org'. Determines the type of GeoIP database
     *                    to load.
     * @return object|false
     */
    private function getGeoIpInstance($key)
    {
        if (empty($this->geoIpCache[$key])) {
            // make sure region names are loaded & saved first
            parent::getRegionNames();
            require_once PIWIK_INCLUDE_PATH . '/libs/MaxMindGeoIP/geoipcity.inc';

            $pathToDb = self::getPathToGeoIpDatabase($this->customDbNames[$key]);
            if ($pathToDb !== false) {
                $this->geoIpCache[$key] = geoip_open($pathToDb, GEOIP_STANDARD); // TODO support shared memory
            }
        }

        return empty($this->geoIpCache[$key]) ? false : $this->geoIpCache[$key];
    }
}

