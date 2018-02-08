<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\LocationProvider\GeoIp;

use Piwik\Piwik;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp;

/**
 * A LocationProvider that uses the PECL implementation of GeoIP.
 *
 * FIXME: For some reason, if the PECL module is loaded & an organization DB is available, the PHP
 * module won't return organization info. If the PECL module is not loaded, organization info is returned.
 *
 */
class Pecl extends GeoIp
{
    const ID = 'geoip_pecl';
    const TITLE = 'GeoIP Legacy (PECL)';

    /**
     * For tests.
     */
    public static $forceDisable = false;

    /**
     * Uses the GeoIP PECL module to get a visitor's location based on their IP address.
     *
     * This function will return different results based on the data available. If a city
     * database can be detected by the PECL module, it may return the country code,
     * region code, city name, area code, latitude, longitude and postal code of the visitor.
     *
     * Alternatively, if only the country database can be detected, only the country code
     * will be returned.
     *
     * The GeoIP PECL module will detect the following filenames:
     * - GeoIP.dat
     * - GeoIPCity.dat
     * - GeoIPISP.dat
     * - GeoIPOrg.dat
     *
     * Note how GeoLiteCity.dat, the name for the GeoLite city database, is not detected
     * by the PECL module.
     *
     * @param array $info Must have an 'ip' field.
     * @return array
     */
    public function getLocation($info)
    {
        $ip = $this->getIpFromInfo($info);

        $result = array();

        // get location data
        if (self::isCityDatabaseAvailable()) {
            // Must hide errors because missing IPV6:
            $location = @geoip_record_by_name($ip);
            if (!empty($location)) {
                $result[self::COUNTRY_CODE_KEY] = $location['country_code'];
                $result[self::REGION_CODE_KEY] = $location['region'];
                $result[self::CITY_NAME_KEY] = utf8_encode($location['city']);
                $result[self::AREA_CODE_KEY] = $location['area_code'];
                $result[self::LATITUDE_KEY] = $location['latitude'];
                $result[self::LONGITUDE_KEY] = $location['longitude'];
                $result[self::POSTAL_CODE_KEY] = $location['postal_code'];
            }
        } else if (self::isRegionDatabaseAvailable()) {
            $location = @geoip_region_by_name($ip);
            if (!empty($location)) {
                $result[self::REGION_CODE_KEY] = $location['region'];
                $result[self::COUNTRY_CODE_KEY] = $location['country_code'];
            }
        } else {
            $result[self::COUNTRY_CODE_KEY] = @geoip_country_code_by_name($ip);
        }

        // get organization data if the org database is available
        if (self::isOrgDatabaseAvailable()) {
            $org = @geoip_org_by_name($ip);
            if ($org !== false) {
                $result[self::ORG_KEY] = utf8_encode($org);
            }
        }

        // get isp data if the isp database is available
        if (self::isISPDatabaseAvailable()) {
            $isp = @geoip_isp_by_name($ip);
            if ($isp !== false) {
                $result[self::ISP_KEY] = utf8_encode($isp);
            }
        }

        if (empty($result)) {
            return false;
        }

        $this->completeLocationResult($result);
        return $result;
    }

    /**
     * Returns true if the PECL module is installed and loaded, false if otherwise.
     *
     * @return bool
     */
    public function isAvailable()
    {
        return !self::$forceDisable && function_exists('geoip_db_avail');
    }

    /**
     * Returns true if the PECL module that is installed can be successfully used
     * to get the location of an IP address.
     *
     * @return bool
     */
    public function isWorking()
    {
        // if no no location database is available, this implementation is not setup correctly
        if (!self::isLocationDatabaseAvailable()) {
            $dbDir = dirname(geoip_db_filename(GEOIP_COUNTRY_EDITION)) . '/';
            $quotedDir = "'$dbDir'";

            // check if the directory the PECL module is looking for exists
            if (!is_dir($dbDir)) {
                return Piwik::translate('UserCountry_PeclGeoIPNoDBDir', array($quotedDir, "'geoip.custom_directory'"));
            }

            // check if the user named the city database GeoLiteCity.dat
            if (file_exists($dbDir . 'GeoLiteCity.dat')) {
                return Piwik::translate('UserCountry_PeclGeoLiteError',
                    array($quotedDir, "'GeoLiteCity.dat'", "'GeoIPCity.dat'"));
            }

            return Piwik::translate('UserCountry_CannotFindPeclGeoIPDb',
                array($quotedDir, "'GeoIP.dat'", "'GeoIPCity.dat'"));
        }

        return parent::isWorking();
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

        if (self::isCityDatabaseAvailable()) {
            $result[self::REGION_CODE_KEY] = true;
            $result[self::REGION_NAME_KEY] = true;
            $result[self::CITY_NAME_KEY] = true;
            $result[self::AREA_CODE_KEY] = true;
            $result[self::LATITUDE_KEY] = true;
            $result[self::LONGITUDE_KEY] = true;
            $result[self::POSTAL_CODE_KEY] = true;
        } else if (self::isRegionDatabaseAvailable()) {
            $result[self::REGION_CODE_KEY] = true;
            $result[self::REGION_NAME_KEY] = true;
        }

        // check if organization info is available
        if (self::isOrgDatabaseAvailable()) {
            $result[self::ORG_KEY] = true;
        }

        // check if ISP info is available
        if (self::isISPDatabaseAvailable()) {
            $result[self::ISP_KEY] = true;
        }

        return $result;
    }

    /**
     * Returns information about this location provider. Contains an id, title & description:
     *
     * array(
     *     'id' => 'geoip_pecl',
     *     'title' => '...',
     *     'description' => '...'
     * );
     *
     * @return array
     */
    public function getInfo()
    {
        $desc = Piwik::translate('UserCountry_GeoIpLocationProviderDesc_Pecl1') . '<br/><br/>'
            . Piwik::translate('UserCountry_GeoIpLocationProviderDesc_Pecl2');
        $installDocs = '<a rel="noreferrer"  target="_blank" href="https://matomo.org/faq/how-to/#faq_164">'
            . Piwik::translate('UserCountry_HowToInstallGeoIpPecl')
            . '</a>';

        $extraMessage = false;
        if ($this->isAvailable()) {
            $peclDir = ini_get('geoip.custom_directory');
            if ($peclDir === false) {
                $extraMessage = Piwik::translate('UserCountry_GeoIPPeclCustomDirNotSet', "'geoip.custom_directory'");
            } else {
                $extraMessage = 'The \'geoip.custom_directory\' PHP ini option is set to \'' . $peclDir . '\'.';
            }

            $availableDatabaseTypes = array();
            if (self::isCityDatabaseAvailable()) {
                $availableDatabaseTypes[] = Piwik::translate('UserCountry_City');
            }
            if (self::isRegionDatabaseAvailable()) {
                $availableDatabaseTypes[] = Piwik::translate('UserCountry_Region');
            }
            if (self::isCountryDatabaseAvailable()) {
                $availableDatabaseTypes[] = Piwik::translate('UserCountry_Country');
            }
            if (self::isISPDatabaseAvailable()) {
                $availableDatabaseTypes[] = 'ISP';
            }
            if (self::isOrgDatabaseAvailable()) {
                $availableDatabaseTypes[] = Piwik::translate('UserCountry_Organization');
            }

            $extraMessage .= '<br/><br/>' . Piwik::translate('UserCountry_GeoIPImplHasAccessTo') . ':&nbsp;<strong>'
                . implode(', ', $availableDatabaseTypes) . '</strong>.';

            $extraMessage = '<strong>' . Piwik::translate('General_Note') . ':&nbsp;</strong>' . $extraMessage;
        }

        return array('id'            => self::ID,
                     'title'         => self::TITLE,
                     'description'   => $desc,
                     'install_docs'  => $installDocs,
                     'extra_message' => $extraMessage,
                     'order'         => 3);
    }

    /**
     * Returns true if the PECL module can detect a location database (either a country,
     * region or city will do).
     *
     * @return bool
     */
    public static function isLocationDatabaseAvailable()
    {
        return self::isCityDatabaseAvailable()
        || self::isRegionDatabaseAvailable()
        || self::isCountryDatabaseAvailable();
    }

    /**
     * Returns true if the PECL module can detect a city database.
     *
     * @return bool
     */
    public static function isCityDatabaseAvailable()
    {
        return geoip_db_avail(GEOIP_CITY_EDITION_REV0)
        || geoip_db_avail(GEOIP_CITY_EDITION_REV1);
    }

    /**
     * Returns true if the PECL module can detect a region database.
     *
     * @return bool
     */
    public static function isRegionDatabaseAvailable()
    {
        return geoip_db_avail(GEOIP_REGION_EDITION_REV0)
        || geoip_db_avail(GEOIP_REGION_EDITION_REV1);
    }

    /**
     * Returns true if the PECL module can detect a country database.
     *
     * @return bool
     */
    public static function isCountryDatabaseAvailable()
    {
        return geoip_db_avail(GEOIP_COUNTRY_EDITION);
    }

    /**
     * Returns true if the PECL module can detect an organization database.
     *
     * @return bool
     */
    public static function isOrgDatabaseAvailable()
    {
        return geoip_db_avail(GEOIP_ORG_EDITION);
    }

    /**
     * Returns true if the PECL module can detect an ISP database.
     *
     * @return bool
     */
    public static function isISPDatabaseAvailable()
    {
        return geoip_db_avail(GEOIP_ISP_EDITION);
    }
}
