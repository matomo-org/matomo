<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @author  Krzysztof Szatanik <chris.szatanik@gmail.com>
 *
 */
namespace Piwik\Plugins\UserCountry\LocationProvider;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Piwik\Piwik;
use Piwik\Plugins\UserCountry\LocationProvider;

/**
 * A LocationProvider that uses the PHP implementation of GeoIP.
 *
 */
class GeoIp2 extends LocationProvider
{
    const ID = 'geoip2_php';
    const TITLE = 'GeoIP 2';

    public static $geoIPDatabaseDir = 'misc';

    /**
     * Stores possible database file names categorized by the type of information, sorted by accuracy
     *
     * @var array
     */
    public static $dbNames = [
        'loc' => ['GeoIP2-City.mmdb', 'GeoLite2-City.mmdb', 'GeoIP2-Country.mmdb', 'GeoLite2-Country.mmdb'],
    ];

    /**
     * Reader instance
     *
     * @var \GeoIp2\Database\Reader
     */
    protected $reader;
    /**
     * Region names
     *
     * @var array
     */
    protected $regionNames;
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
    private $_isoRegionCodes;

    /**
     * Constructor.
     *
     * @param array|bool $customDbNames      The possible filenames for each type of GeoIP database.
     *                                       If a key is missing (or the parameter not supplied), then the
     *                                       default database names are used.
     */
    public function __construct($customDbNames = false)
    {
        $this->customDbNames = self::$dbNames;
        if ($customDbNames !== false) {
            foreach ($this->customDbNames as $key => $names) {
                if (isset($customDbNames[$key])) {
                    $this->customDbNames[$key] = $customDbNames[$key];
                }
            }
        }
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
        $desc        = 'GeoIP 2 composer library';
        $installDocs = 'Upload one of files: GeoIP2-City.mmdb, GeoLite2-City.mmdb, GeoIP2-Country.mmdb or GeoLite2-Country.mmdb into the misc Piwik subdirectory (you can do this either by FTP or SSH).';

        $availableDatabaseTypes = [];
        if (self::getPathToGeoIpDatabase(['GeoIP2-City.mmdb', 'GeoLite2-City.mmdb']) !== false) {
            $availableDatabaseTypes[] = Piwik::translate('UserCountry_City');
        }
        if (self::getPathToGeoIpDatabase(['GeoIP2-Country.mmdb', 'GeoLite2-Country.mmdb']) !== false) {
            $availableDatabaseTypes[] = Piwik::translate('UserCountry_Country');
        }

        $extraMessage = '<strong><em>'.Piwik::translate('General_Note').'</em></strong>:&nbsp;'
            .Piwik::translate('UserCountry_GeoIPImplHasAccessTo').':&nbsp;<strong><em>'
            .implode(', ', $availableDatabaseTypes).'</em></strong>.';

        return [
            'id'            => self::ID,
            'title'         => self::TITLE,
            'description'   => $desc,
            'install_docs'  => $installDocs,
            'extra_message' => $extraMessage,
            'order'         => 2,
        ];
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
//        $ip = '194.57.91.215';

        $result = [];
        $reader = $this->getReader();

        try {
            switch ($reader->metadata()->databaseType) {
                case 'GeoLite2-Country':
                case 'GeoIP2-Country':
                    $lookupResult                     = $reader->country($ip);
                    $result[self::CONTINENT_NAME_KEY] = $lookupResult->continent->name;
                    $result[self::CONTINENT_CODE_KEY] = strtoupper($lookupResult->continent->code);
                    $result[self::COUNTRY_CODE_KEY]   = strtoupper($lookupResult->country->isoCode);
                    $result[self::COUNTRY_NAME_KEY]   = $lookupResult->country->name;
                    break;
                case 'GeoLite2-City':
                case 'GeoIP2-City':
                    $lookupResult                     = $reader->city($ip);
                    $result[self::CONTINENT_NAME_KEY] = $lookupResult->continent->name;
                    $result[self::CONTINENT_CODE_KEY] = strtoupper($lookupResult->continent->code);
                    $result[self::COUNTRY_CODE_KEY]   = strtoupper($lookupResult->country->isoCode);
                    $result[self::COUNTRY_NAME_KEY]   = $lookupResult->country->name;
                    $result[self::CITY_NAME_KEY]      = $lookupResult->city->name;
                    $result[self::LATITUDE_KEY]       = $lookupResult->location->latitude;
                    $result[self::LONGITUDE_KEY]      = $lookupResult->location->longitude;
                    $result[self::POSTAL_CODE_KEY]    = $lookupResult->postal->code;
                    $regions                          = $lookupResult->subdivisions;
                    if (isset($regions[0])) {
                        switch ($result[self::COUNTRY_CODE_KEY]) {
                            case 'US':
                            case 'CA':
                                $result[self::REGION_CODE_KEY] = strtoupper($lookupResult->subdivisions[0]->isoCode);
                                break;
                            default:
                                $result[self::REGION_CODE_KEY] = strtoupper(
                                    $this->isoRegionCodeToFIPS(
                                        $result[self::COUNTRY_CODE_KEY],
                                        $lookupResult->subdivisions[0]->isoCode
                                    )
                                );
                                break;
                        }
                        $result[self::REGION_NAME_KEY] = $lookupResult->subdivisions[0]->name;
                    }
                    break;
            }
        } catch (AddressNotFoundException $e) {
            // ignore - do nothing
        }


        $this->completeLocationResult($result);

        return $result;
    }

    /**
     * Returns Reader instance
     *
     * @return Reader
     */
    public function getReader()
    {
        if (empty($this->reader)) {
            foreach ($this->customDbNames as $dbName) {
                $path = self::getPathToGeoIpDatabase($dbName);
                if ($path !== false) {
                    $this->reader = new Reader($path);
                    break;
                }
            }

        }

        return $this->reader;
    }

    /**
     * Convert ISO 3166-2 Code to FIPS 10.2
     *
     * @param $countryCode
     * @param $regionIsoCode
     * @return string|null
     */
    private function isoRegionCodeToFIPS($countryCode, $regionIsoCode)
    {
        $regionIsoCode = strtoupper($regionIsoCode);

        if (empty($this->_isoRegionCodes)) {
            $this->_isoRegionCodes = json_decode(
                file_get_contents(PIWIK_INCLUDE_PATH.'/libs/MaxMindGeoIP/ISO3166-2_to_FIPS.json'),
                true
            );
        }

        return (isset($this->_isoRegionCodes[$countryCode][$regionIsoCode])) ? $this->_isoRegionCodes[$countryCode][$regionIsoCode] : null;

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
        $result = [];

        $result[self::CONTINENT_CODE_KEY] = true;
        $result[self::CONTINENT_NAME_KEY] = true;
        $result[self::COUNTRY_CODE_KEY]   = true;
        $result[self::COUNTRY_NAME_KEY]   = true;
        $result[self::REGION_CODE_KEY]    = true;
        $result[self::REGION_NAME_KEY]    = true;
        $result[self::CITY_NAME_KEY]      = true;
        $result[self::LATITUDE_KEY]       = true;
        $result[self::LONGITUDE_KEY]      = true;
        $result[self::POSTAL_CODE_KEY]    = true;

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
        foreach ($this->customDbNames as $dbName) {
            $path = self::getPathToGeoIpDatabase($dbName);
            if ($path !== false) {
                return true;
            }
        }

        return 'None of supported databases was found.';
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
        return PIWIK_INCLUDE_PATH.'/'.self::$geoIPDatabaseDir.'/'.$filename;
    }
}

require_once PIWIK_INCLUDE_PATH.'/plugins/UserCountry/LocationProvider/GeoIp.php';
require_once PIWIK_INCLUDE_PATH.'/plugins/UserCountry/LocationProvider.php';
