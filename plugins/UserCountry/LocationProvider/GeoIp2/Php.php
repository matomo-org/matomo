<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\LocationProvider\GeoIp2;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Piwik\Log;
use Piwik\Piwik;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp2;

/**
 * A LocationProvider that uses the PHP implementation of GeoIP 2.
 *
 */
class Php extends GeoIp2
{
    const ID = 'geoip2php';
    const TITLE = 'GeoIP 2 (Php)';

    /**
     * The GeoIP2 reader instances used. This array will contain at most two
     * of them: one for location info and one for ISP info
     *
     * Each instance is mapped w/ one of the following keys: 'loc', 'isp'
     *
     * @var array of GeoIP instances
     */
    private $readerCache = array();

    /**
     * Possible filenames for each type of GeoIP database. When looking for a database
     * file in the 'misc' subdirectory, files with these names will be looked for.
     *
     * This variable is an array mapping either the 'loc' or 'isp' strings with
     * an array of filenames.
     *
     * By default, this will be set to GeoIp2::$dbNames.
     *
     * @var array
     */
    private $customDbNames;

    /**
     * Constructor.
     *
     * @param array|bool $customDbNames The possible filenames for each type of GeoIP database.
     *                                   eg array(
     *                                       'loc' => array('GeoLite2-City.mmdb'),
     *                                       'isp' => array('GeoIP2.mmdb', 'GeoIP2-ISP.mmdb')
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
     * Uses a GeoIP 2 database to get a visitor's location based on their IP address.
     *
     * This function will return different results based on the data used. If a city
     * database is used, it may return the country code, region code, city name, area
     * code, latitude, longitude and postal code of the visitor.
     *
     * Alternatively, if used with a country database, only the country code will be
     * returned.
     *
     * @param array $info Must have an 'ip' field.
     * @return array|false
     */
    public function getLocation($info)
    {
        $ip = $this->getIpFromInfo($info);

        $result = [];
        $reader = $this->getGeoIpInstance('loc');
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
                case 'GeoIP2-Enterprise':
                case 'GeoLite2-City':
                case 'GeoIP2-City':
                    if ($reader->metadata()->databaseType === 'GeoIP2-Enterprise') {
                        $lookupResult                 = $reader->enterprise($ip);
                    } else {
                        $lookupResult                 = $reader->city($ip);
                    }
                    $result[self::CONTINENT_NAME_KEY] = $lookupResult->continent->name;
                    $result[self::CONTINENT_CODE_KEY] = strtoupper($lookupResult->continent->code);
                    $result[self::COUNTRY_CODE_KEY]   = strtoupper($lookupResult->country->isoCode);
                    $result[self::COUNTRY_NAME_KEY]   = $lookupResult->country->name;
                    $result[self::CITY_NAME_KEY]      = $lookupResult->city->name;
                    $result[self::LATITUDE_KEY]       = $lookupResult->location->latitude;
                    $result[self::LONGITUDE_KEY]      = $lookupResult->location->longitude;
                    $result[self::POSTAL_CODE_KEY]    = $lookupResult->postal->code;
                    if (isset($lookupResult->subdivisions[0])) {
                        $result[self::REGION_CODE_KEY] = strtoupper($lookupResult->subdivisions[0]->isoCode);
                        $result[self::REGION_NAME_KEY] = $lookupResult->subdivisions[0]->name;
                    }
                    break;
                default: // unknown database type log warning
                    Log::warning("Found unrecognized database type: %s", $reader->metadata()->databaseType);
                    break;
            }
        } catch (AddressNotFoundException $e) {
            // ignore - do nothing
        }

        // NOTE: ISP & ORG require commercial dbs to test.
        $ispGeoIp = $this->getGeoIpInstance($key = 'isp');
        if ($ispGeoIp) {
            try {
                $lookupResult          = $ispGeoIp->isp($ip);
                $result[self::ISP_KEY] = $lookupResult->isp;
                $result[self::ORG_KEY] = $lookupResult->organization;
            } catch (AddressNotFoundException $e) {
                // ignore - do nothing
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
     * If an ISP/organization database is found, ISP/organization information is supported.
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

        $reader = $this->getGeoIpInstance($key = 'loc');
        if ($reader) {
            switch ($reader->metadata()->databaseType) {
                case 'GeoIP2-Enterprise':
                case 'GeoIP2-City':
                case 'GeoLite2-City':
                    $result[self::REGION_CODE_KEY] = true;
                    $result[self::REGION_NAME_KEY] = true;
                    $result[self::CITY_NAME_KEY] = true;
                    $result[self::AREA_CODE_KEY] = true;
                    $result[self::POSTAL_CODE_KEY] = true;
                    $result[self::LATITUDE_KEY] = true; // will be removed from GeoLite2-City in 2019
                    $result[self::LONGITUDE_KEY] = true; // will be removed from GeoLite2-City in 2019
                    break;
            }
        }

        // check if isp info is available
        if ($this->getGeoIpInstance($key = 'isp')) {
            $result[self::ISP_KEY] = true;
            $result[self::ORG_KEY] = true;
        }

        return $result;
    }

    /**
     * Returns information about this location provider. Contains an id, title & description:
     *
     * array(
     *     'id' => 'geoip2_php',
     *     'title' => '...',
     *     'description' => '...'
     * );
     *
     * @return array
     */
    public function getInfo()
    {
        $desc = Piwik::translate('UserCountry_GeoIp2LocationProviderDesc_Php') . '<br/><br/>';

        if (extension_loaded('maxminddb')) {
            $desc .= Piwik::translate('UserCountry_GeoIp2LocationProviderDesc_Php_WithExtension',
                array('<strong>', '</strong>'));
        } else {
            $desc .= Piwik::translate('UserCountry_GeoIp2LocationProviderDesc_Php_NoExtension',
                array('<strong>', '</strong>', '<strong>', '</strong>'));
        }

        $installDocs = '<a rel="noreferrer"  target="_blank" href="https://matomo.org/faq/how-to/#faq_163">'
            . Piwik::translate('UserCountry_HowToInstallGeoIPDatabases')
            . '</a>';

        $availableDatabaseTypes = array();
        if (self::getPathToGeoIpDatabase(['GeoIP2-Enterprise.mmdb', 'GeoIP2-City.mmdb', 'GeoLite2-City.mmdb']) !== false) {
            $availableDatabaseTypes[] = Piwik::translate('UserCountry_City');
        }
        if (self::getPathToGeoIpDatabase(['GeoIP2-Country.mmdb', 'GeoLite2-Country.mmdb']) !== false) {
            $availableDatabaseTypes[] = Piwik::translate('UserCountry_Country');
        }
        if (self::getPathToGeoIpDatabase(['GeoIP2-ISP.mmdb']) !== false) {
            $availableDatabaseTypes[] = Piwik::translate('UserCountry_ISPDatabase');
        }

        $extraMessage = '<strong>' . Piwik::translate('General_Note') . '</strong>:&nbsp;'
            . Piwik::translate('UserCountry_GeoIPImplHasAccessTo') . ':&nbsp;<strong>'
            . implode(', ', $availableDatabaseTypes) . '</strong>.';

        return array('id'            => self::ID,
                     'title'         => self::TITLE,
                     'description'   => $desc,
                     'install_docs'  => $installDocs,
                     'extra_message' => $extraMessage,
                     'order'         => 2);
    }

    /**
     * Returns a GeoIP2 reader instance. Creates it if necessary.
     *
     * @param string $key 'loc' or 'isp'. Determines the type of GeoIP database
     *                    to load.
     * @return Reader|false
     */
    private function getGeoIpInstance($key)
    {
        if (empty($this->readerCache[$key])) {
            $pathToDb = self::getPathToGeoIpDatabase($this->customDbNames[$key]);
            if ($pathToDb !== false) {
                $this->readerCache[$key] = new Reader($pathToDb);
            }
        }

        return empty($this->readerCache[$key]) ? false : $this->readerCache[$key];
    }
}

