<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use MaxMind\Db\Reader\InvalidDatabaseException;
use Piwik\Log;
use Piwik\Piwik;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2;
use Piwik\Plugins\Marketplace\Api\Exception;

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

        if (empty($ip)) {
            return false;
        }

        $result = [];
        $reader = $this->getGeoIpInstance('loc');
        if ($reader) {
            try {
                switch ($reader->metadata()->databaseType) {
                    case 'GeoLite2-Country':
                    case 'GeoIP2-Country':
                        $lookupResult = $reader->country($ip);
                        $this->setCountryResults($lookupResult, $result);
                        break;
                    case 'GeoIP2-Enterprise':
                    case 'GeoLite2-City':
                    case 'GeoIP2-City':
                    case 'GeoIP2-City-Africa':
                    case 'GeoIP2-City-Asia-Pacific':
                    case 'GeoIP2-City-Europe':
                    case 'GeoIP2-City-North-America':
                    case 'GeoIP2-City-South-America':
                        if ($reader->metadata()->databaseType === 'GeoIP2-Enterprise') {
                            $lookupResult = $reader->enterprise($ip);
                        } else {
                            $lookupResult = $reader->city($ip);
                        }
                        $this->setCountryResults($lookupResult, $result);
                        $this->setCityResults($lookupResult, $result);
                        break;
                    default: // unknown database type log warning
                        Log::warning("Found unrecognized database type: %s", $reader->metadata()->databaseType);
                        break;
                }
            } catch (AddressNotFoundException $e) {
                // ignore - do nothing
            }
        }

        // NOTE: ISP & ORG require commercial dbs to test.
        $ispGeoIp = $this->getGeoIpInstance($key = 'isp');
        if ($ispGeoIp) {
            try {
                switch ($ispGeoIp->metadata()->databaseType) {
                    case 'GeoIP2-ISP':
                        $lookupResult = $ispGeoIp->isp($ip);
                        $result[self::ISP_KEY] = $lookupResult->isp;
                        $result[self::ORG_KEY] = $lookupResult->organization;
                        break;
                    case 'GeoLite2-ASN':
                        $lookupResult = $ispGeoIp->asn($ip);
                        $result[self::ISP_KEY] = $lookupResult->autonomousSystemOrganization;
                        $result[self::ORG_KEY] = $lookupResult->autonomousSystemOrganization;
                        break;
                }
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

    protected function setCountryResults($lookupResult, &$result)
    {
        $result[self::CONTINENT_NAME_KEY] = $lookupResult->continent->name;
        $result[self::CONTINENT_CODE_KEY] = strtoupper($lookupResult->continent->code);
        $result[self::COUNTRY_CODE_KEY]   = strtoupper($lookupResult->country->isoCode);
        $result[self::COUNTRY_NAME_KEY]   = $lookupResult->country->name;
    }

    protected function setCityResults($lookupResult, &$result)
    {
        $result[self::CITY_NAME_KEY]      = $lookupResult->city->name;
        $result[self::LATITUDE_KEY]       = $lookupResult->location->latitude;
        $result[self::LONGITUDE_KEY]      = $lookupResult->location->longitude;
        $result[self::POSTAL_CODE_KEY]    = $lookupResult->postal->code;
        if (is_array($lookupResult->subdivisions) && count($lookupResult->subdivisions) > 0) {
            $subdivisions = $lookupResult->subdivisions;
            $subdivision = $this->determinSubdivision($subdivisions, $result[self::COUNTRY_CODE_KEY]);
            $result[self::REGION_CODE_KEY] = strtoupper($subdivision->isoCode);
            $result[self::REGION_NAME_KEY] = $subdivision->name;
        }
    }

    protected function determinSubdivision($subdivisions, $countryCode)
    {
        if (in_array($countryCode, ['GB'])) {
            return end($subdivisions);
        }

        return reset($subdivisions);
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
                case 'GeoLite2-City':
                case 'GeoIP2-City':
                case 'GeoIP2-City-Africa':
                case 'GeoIP2-City-Asia-Pacific':
                case 'GeoIP2-City-Europe':
                case 'GeoIP2-City-North-America':
                case 'GeoIP2-City-South-America':
                    $result[self::REGION_CODE_KEY] = true;
                    $result[self::REGION_NAME_KEY] = true;
                    $result[self::CITY_NAME_KEY] = true;
                    $result[self::POSTAL_CODE_KEY] = true;
                    $result[self::LATITUDE_KEY] = true;
                    $result[self::LONGITUDE_KEY] = true;
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
        $desc = Piwik::translate('GeoIp2_LocationProviderDesc_Php') . '<br/><br/>';

        if (extension_loaded('maxminddb')) {
            $desc .= Piwik::translate('GeoIp2_LocationProviderDesc_Php_WithExtension',
                array('<strong>', '</strong>'));
        }

        $installDocs = '<a rel="noreferrer"  target="_blank" href="https://matomo.org/faq/how-to/#faq_163">'
            . Piwik::translate('UserCountry_HowToInstallGeoIPDatabases')
            . '</a>';

        $availableDatabaseTypes = array();
        if (self::getPathToGeoIpDatabase(['GeoIP2-City.mmdb', 'GeoIP2-City-Africa.mmdb', 'GeoIP2-City-Asia-Pacific.mmdb', 'GeoIP2-City-Europe.mmdb', 'GeoIP2-City-North-America.mmdb', 'GeoIP2-City-South-America.mmdb', 'GeoIP2-Enterprise.mmdb', 'GeoLite2-City.mmdb']) !== false) {
            $availableDatabaseTypes[] = Piwik::translate('UserCountry_City');
        }
        if (self::getPathToGeoIpDatabase(['GeoIP2-Country.mmdb', 'GeoLite2-Country.mmdb']) !== false) {
            $availableDatabaseTypes[] = Piwik::translate('UserCountry_Country');
        }
        if (self::getPathToGeoIpDatabase(self::$dbNames['isp']) !== false) {
            $availableDatabaseTypes[] = Piwik::translate('UserCountry_ISPDatabase');
        }

        if (!empty($availableDatabaseTypes)) {
            $extraMessage = '<strong>' . Piwik::translate('General_Note') . '</strong>:&nbsp;'
                . Piwik::translate('UserCountry_GeoIPImplHasAccessTo') . ':&nbsp;<strong>'
                . implode(', ', $availableDatabaseTypes) . '</strong>.';
        } else {
            $extraMessage = '<strong>' . Piwik::translate('General_Note') . '</strong>:&nbsp;'
                . Piwik::translate('UserCountry_GeoIPNoDatabaseFound');
        }

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
            if (is_string($this->customDbNames[$key])
                && strpos( $this->customDbNames[$key], '/') !== false
                && @file_exists($this->customDbNames[$key])) {
                // for performance reason we only check for file exists if the string contains a slash and is not only a
                // filename
                $pathToDb = $this->customDbNames[$key];
            } else {
                $pathToDb = self::getPathToGeoIpDatabase($this->customDbNames[$key]);
            }
            if ($pathToDb !== false) {
                try {
                    $this->readerCache[$key] = new Reader($pathToDb);
                } catch (InvalidDatabaseException $e) {
                    // ignore invalid database exception
                }
            }
        }

        return empty($this->readerCache[$key]) ? false : $this->readerCache[$key];
    }
}
