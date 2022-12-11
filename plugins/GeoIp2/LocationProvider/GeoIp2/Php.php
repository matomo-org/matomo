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
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Log;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\GeoIp2\GeoIP2AutoUpdater;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\SettingsPiwik;
use Piwik\View;

/**
 * A LocationProvider that uses the PHP implementation of GeoIP 2.
 *
 */
class Php extends GeoIp2
{
    const ID = 'geoip2php';
    const TITLE = 'DBIP / GeoIP 2 (Php)';

    /**
     * The GeoIP2 reader instances used. This array will contain at most two
     * of them: one for location info and one for ISP info
     *
     * Each instance is mapped w/ one of the following keys: 'loc', 'isp'
     *
     * @var Reader[] of GeoIP instances
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

    public function __destroy()
    {
        $this->clearCachedInstances();
    }

    private function isIspDbEnabled()
    {
        return StaticContainer::get('geopip2.ispEnabled');
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
                    case 'DBIP-Country-Lite':
                    case 'DBIP-Country':
                    case 'DBIP-Location (compat=Country)':
                        $lookupResult = $reader->country($ip);
                        $this->setCountryResults($lookupResult, $result);
                        break;
                    case 'GeoLite2-City':
                    case 'DBIP-City-Lite':
                    case 'DBIP-City':
                    case 'GeoIP2-City':
                    case 'GeoIP2-City-Africa':
                    case 'GeoIP2-City-Asia-Pacific':
                    case 'GeoIP2-City-Europe':
                    case 'GeoIP2-City-North-America':
                    case 'GeoIP2-City-South-America':
                    case 'DBIP-Location (compat=City)':
                        $lookupResult = $reader->city($ip);
                        $this->setCountryResults($lookupResult, $result);
                        $this->setCityResults($lookupResult, $result);
                        break;
                    case 'GeoIP2-Enterprise':
                    case 'DBIP-Location-ISP (compat=Enterprise)':
                    case 'DBIP-Enterprise':
                        $lookupResult = $reader->enterprise($ip);
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
        if ($this->isIspDbEnabled()) {
            $ispGeoIp = $this->getGeoIpInstance($key = 'isp');
        } else {
            $ispGeoIp = false;
        }
        if ($ispGeoIp) {
            try {
                switch ($ispGeoIp->metadata()->databaseType) {
                    case 'GeoIP2-ISP':
                        $lookupResult = $ispGeoIp->isp($ip);
                        $result[self::ISP_KEY] = $lookupResult->isp;
                        $result[self::ORG_KEY] = $lookupResult->organization;
                        break;
                    case 'GeoLite2-ASN':
                    case 'DBIP-ASN-Lite (compat=GeoLite2-ASN)':
                        $lookupResult = $ispGeoIp->asn($ip);
                        $result[self::ISP_KEY] = $lookupResult->autonomousSystemOrganization;
                        $result[self::ORG_KEY] = $lookupResult->autonomousSystemOrganization;
                        break;
                    case 'GeoIP2-Enterprise':
                    case 'DBIP-ISP (compat=Enterprise)':
                    case 'DBIP-Location-ISP (compat=Enterprise)':
                    case 'DBIP-ISP':
                    case 'DBIP-Enterprise':
                        $lookupResult = $ispGeoIp->enterprise($ip);
                        $result[self::ISP_KEY] = $lookupResult->traits->isp;
                        $result[self::ORG_KEY] = $lookupResult->traits->organization;
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

    /**
     * Returns a generalized name for the type of GeoIP database that is configured to load. The result is suitable
     * for use as a filename, is not the exact value of the databaseType metadata.
     *
     * @param string $dbType 'loc', 'isp', etc.
     */
    public function detectDatabaseType($dbType)
    {
        $reader = $this->getGeoIpInstance($dbType);
        if (empty($reader)) {
            throw new \Exception("Unable to determine what type of database this is.");
        }

        $specificDatabaseTypeMetadata = $reader->metadata()->databaseType;
        switch ($specificDatabaseTypeMetadata) {
            case 'DBIP-Country-Lite':
            case 'DBIP-Location (compat=Country)':
                return 'DBIP-Country';
            case 'DBIP-City-Lite':
            case 'DBIP-Location (compat=City)':
                return 'DBIP-City';
            case 'GeoLite2-Country':
                return 'GeoIP2-Country';
            case 'DBIP-ISP (compat=Enterprise)':
                return 'DBIP-ISP';
            case 'DBIP-ASN-Lite (compat=GeoLite2-ASN)':
                return 'DBIP-ASN';
            case 'DBIP-Location-ISP (compat=Enterprise)':
                return 'DBIP-Enterprise';
            case 'GeoLite2-City':
            case 'GeoIP2-City-Africa':
            case 'GeoIP2-City-Asia-Pacific':
            case 'GeoIP2-City-Europe':
            case 'GeoIP2-City-North-America':
            case 'GeoIP2-City-South-America':
                return 'GeoIP2-City';
            case 'GeoIP2-ISP':
            case 'GeoLite2-ASN':
            case 'DBIP-Country':
            case 'DBIP-City':
            case 'GeoIP2-City':
            case 'GeoIP2-Enterprise':
            case 'GeoIP2-Country':
            case 'DBIP-ISP':
                return $specificDatabaseTypeMetadata;
            default:
                throw new \Exception("Unknown database type: $specificDatabaseTypeMetadata");
        }
    }

    protected function setCountryResults($lookupResult, &$result)
    {
        $result[self::CONTINENT_NAME_KEY] = $lookupResult->continent->name;
        $result[self::CONTINENT_CODE_KEY] = strtoupper($lookupResult->continent->code ?? '');
        $result[self::COUNTRY_CODE_KEY]   = strtoupper($lookupResult->country->isoCode ?? '');
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
            $subdivisionIsoCode = $subdivision->isoCode ? strtoupper($subdivision->isoCode) : '';

            // In some cases the region code might be returned including the country code
            // e.g. AE-DU instead of only DU. In that case we remove the prefix
            // see https://github.com/matomo-org/matomo/issues/19323
            if (0 === strpos($subdivisionIsoCode, $result[self::COUNTRY_CODE_KEY] . '-')) {
                $subdivisionIsoCode = substr($subdivisionIsoCode, strlen($result[self::COUNTRY_CODE_KEY]) + 1);
            }

            $result[self::REGION_CODE_KEY] = $subdivisionIsoCode ? : $this->determineRegionIsoCodeByNameAndCountryCode($subdivision->name, $result[self::COUNTRY_CODE_KEY]);
            $result[self::REGION_NAME_KEY] = $subdivision->name;
        }
    }

    /**
     * Try to determine the ISO region code based on the region name and country code
     *
     * @param string $regionName
     * @param string $countryCode
     * @return string
     */
    protected function determineRegionIsoCodeByNameAndCountryCode($regionName, $countryCode)
    {
        $regionNames = self::getRegionNames();

        if (empty($regionNames[$countryCode])) {
            return '';
        }

        foreach ($regionNames[$countryCode] as $isoCode => $name) {
            if (mb_strtolower($name) === mb_strtolower($regionName)) {
                return $isoCode;
            }
        }

        return '';
    }

    protected function determinSubdivision($subdivisions, $countryCode)
    {
        if (in_array($countryCode, ['GB'])) {
            return end($subdivisions);
        }

        return reset($subdivisions);
    }

    /**
     * Returns true if this location provider is available. That is the case if either a location or a isp database is
     * available
     *
     * @return bool
     */
    public function isAvailable()
    {
        $pathLoc = self::getPathToGeoIpDatabase($this->customDbNames['loc']);
        $pathIsp = $this->isIspDbEnabled() && self::getPathToGeoIpDatabase($this->customDbNames['isp']);
        return $pathLoc !== false || $pathIsp !== false;
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

        $reader = $this->getGeoIpInstance($key = 'loc');
        if ($reader) {
            // country & continent info always available
            $result[self::CONTINENT_CODE_KEY] = true;
            $result[self::CONTINENT_NAME_KEY] = true;
            $result[self::COUNTRY_CODE_KEY] = true;
            $result[self::COUNTRY_NAME_KEY] = true;

            switch ($reader->metadata()->databaseType) {
                case 'GeoIP2-Enterprise':
                case 'GeoLite2-City':
                case 'DBIP-City-Lite':
                case 'DBIP-City':
                case 'GeoIP2-City':
                case 'GeoIP2-City-Africa':
                case 'GeoIP2-City-Asia-Pacific':
                case 'GeoIP2-City-Europe':
                case 'GeoIP2-City-North-America':
                case 'GeoIP2-City-South-America':
                case 'DBIP-Enterprise':
                case 'DBIP-Location-ISP (compat=Enterprise)':
                case 'DBIP-ISP (compat=Enterprise)':
                case 'DBIP-Location (compat=City)':
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
        if ($this->isIspDbEnabled() && $this->getGeoIpInstance($key = 'isp')) {
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

        $installDocs = '<a rel="noreferrer"  target="_blank" href="https://matomo.org/faq/how-to/faq_163">'
            . Piwik::translate('UserCountry_HowToInstallGeoIPDatabases')
            . '</a>';

        $availableInfo = $this->getSupportedLocationInfo();

        $availableDatabaseTypes = array();

        if (isset($availableInfo[self::CITY_NAME_KEY]) && $availableInfo[self::CITY_NAME_KEY]) {
            $availableDatabaseTypes[] = Piwik::translate('UserCountry_City');
        }

        if (isset($availableInfo[self::COUNTRY_NAME_KEY]) && $availableInfo[self::COUNTRY_NAME_KEY]) {
            $availableDatabaseTypes[] = Piwik::translate('UserCountry_Country');
        }

        if (isset($availableInfo[self::ISP_KEY]) && $availableInfo[self::ISP_KEY]) {
            $availableDatabaseTypes[] = Piwik::translate('GeoIp2_ISPDatabase');
        }

        if (!empty($availableDatabaseTypes)) {
            $extraMessage = '<strong>' . Piwik::translate('General_Note') . '</strong>:&nbsp;'
                . Piwik::translate('GeoIp2_GeoIPImplHasAccessTo') . ':&nbsp;<strong>'
                . implode(', ', $availableDatabaseTypes) . '</strong>.';
        } else {
            $extraMessage = '<strong>' . Piwik::translate('General_Note') . '</strong>:&nbsp;'
                . Piwik::translate('GeoIp2_GeoIPNoDatabaseFound');
        }

        return array('id'            => self::ID,
            'title'         => self::TITLE,
            'description'   => $desc,
            'install_docs'  => $installDocs,
            'extra_message' => $extraMessage,
            'order'         => 2);
    }

    public function renderConfiguration()
    {
        $view = new View('@GeoIp2/configuration.twig');
        $today = Date::today();

        $urls = GeoIP2AutoUpdater::getConfiguredUrls();
        $view->geoIPLocUrl = $urls['loc'];
        $view->geoIPIspUrl = $urls['isp'];
        $view->geoIPUpdatePeriod = GeoIP2AutoUpdater::getSchedulePeriod();

        $view->hasGeoIp2Provider = Manager::getInstance()->isPluginActivated('GeoIp2');
        $view->isProviderPluginActive = Manager::getInstance()->isPluginActivated('Provider');

        $geoIPDatabasesInstalled = $view->hasGeoIp2Provider ? GeoIp2::isDatabaseInstalled() : false;

        $view->geoIPDatabasesInstalled = $geoIPDatabasesInstalled;
        $view->updatePeriodOptions = [
            'month' => Piwik::translate('Intl_PeriodMonth'),
            'week' => Piwik::translate('Intl_PeriodWeek')
        ];


        // if using a server module, they are working and there are no databases
        // in misc, then the databases are located outside of Matomo, so we cannot update them
        $view->showGeoIPUpdateSection = true;
        $currentProviderId = LocationProvider::getCurrentProviderId();
        if (!$geoIPDatabasesInstalled
            && in_array($currentProviderId, [GeoIp2\ServerModule::ID])
            && LocationProvider::getCurrentProvider()->isWorking()
            && LocationProvider::getCurrentProvider()->isAvailable()
        ) {
            $view->showGeoIPUpdateSection = false;
        }

        $view->isInternetEnabled = SettingsPiwik::isInternetEnabled();


        $view->dbipLiteUrl = GeoIp2::getDbIpLiteUrl();
        $view->dbipLiteFilename = "dbip-city-lite-{$today->toString('Y-m')}.mmdb";
        $view->dbipLiteDesiredFilename = "DBIP-City.mmdb";
        $view->nextRunTime = GeoIP2AutoUpdater::getNextRunTime();

        $lastRunTime = GeoIP2AutoUpdater::getLastRunTime();

        if ($lastRunTime !== false) {
            $view->lastTimeUpdaterRun = '<strong>' . $lastRunTime->toString() . '</strong>';
        }
        return $view->render();
    }

    public function renderSetUpGuide()
    {
        $today = Date::today();
        $view = new View('@GeoIp2/setupguide.twig');

        $view->dbipLiteUrl = GeoIp2::getDbIpLiteUrl();
        $view->dbipLiteFilename = "dbip-city-lite-{$today->toString('Y-m')}.mmdb";
        $view->dbipLiteDesiredFilename = "DBIP-City.mmdb";

        return $view->render();
    }

    /**
     * Clears the cached instances and releases the file handles
     */
    public function clearCachedInstances()
    {
        if (empty($this->readerCache)) {
            return;
        }

        foreach ($this->readerCache as $reader) {
            $reader->close();
        }

        unset($this->readerCache);
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
