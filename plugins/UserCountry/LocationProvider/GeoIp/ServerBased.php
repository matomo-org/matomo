<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\UserCountry\LocationProvider\GeoIp;

use Piwik\Common;
use Piwik\IP;
use Piwik\Piwik;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp;
use Piwik\Plugins\UserCountry\LocationProvider;

/**
 * A LocationProvider that uses an GeoIP module installed in an HTTP Server.
 *
 * To make this provider available, make sure the GEOIP_ADDR server
 * variable is set.
 *
 */
class ServerBased extends GeoIp
{
    const ID = 'geoip_serverbased';
    const TITLE = 'GeoIP Legacy (%s)';
    const TEST_SERVER_VAR = 'GEOIP_ADDR';
    const TEST_SERVER_VAR_ALT = 'GEOIP_COUNTRY_CODE';
    const TEST_SERVER_VAR_ALT_IPV6 = 'GEOIP_COUNTRY_CODE_V6';

    private static $geoIpServerVars = array(
        parent::COUNTRY_CODE_KEY => 'GEOIP_COUNTRY_CODE',
        parent::COUNTRY_NAME_KEY => 'GEOIP_COUNTRY_NAME',
        parent::REGION_CODE_KEY  => 'GEOIP_REGION',
        parent::REGION_NAME_KEY  => 'GEOIP_REGION_NAME',
        parent::AREA_CODE_KEY    => 'GEOIP_AREA_CODE',
        parent::LATITUDE_KEY     => 'GEOIP_LATITUDE',
        parent::LONGITUDE_KEY    => 'GEOIP_LONGITUDE',
        parent::POSTAL_CODE_KEY  => 'GEOIP_POSTAL_CODE',
    );

    private static $geoIpUtfServerVars = array(
        parent::CITY_NAME_KEY => 'GEOIP_CITY',
        parent::ISP_KEY       => 'GEOIP_ISP',
        parent::ORG_KEY       => 'GEOIP_ORGANIZATION',
    );

    /**
     * Uses a GeoIP database to get a visitor's location based on their IP address.
     *
     * This function will return different results based on the data used and based
     * on how the GeoIP module is configured.
     *
     * If a region database is used, it may return the country code, region code,
     * city name, area code, latitude, longitude and postal code of the visitor.
     *
     * Alternatively, only the country code may be returned for another database.
     *
     * If your HTTP server is not configured to include all GeoIP information, some
     * information will not be available to Piwik.
     *
     * @param array $info Must have an 'ip' field.
     * @return array
     */
    public function getLocation($info)
    {
        $ip = $this->getIpFromInfo($info);

        // geoip modules that are built into servers can't use a forced IP. in this case we try
        // to fallback to another version.
        $myIP = IP::getIpFromHeader();
        if (!self::isSameOrAnonymizedIp($ip, $myIP)
            && (!isset($info['disable_fallbacks'])
                || !$info['disable_fallbacks'])
        ) {
            Common::printDebug("The request is for IP address: " . $info['ip'] . " but your IP is: $myIP. GeoIP Server Module (apache/nginx) does not support this use case... ");
            $fallbacks = array(
                Pecl::ID,
                Php::ID
            );
            foreach ($fallbacks as $fallbackProviderId) {
                $otherProvider = LocationProvider::getProviderById($fallbackProviderId);
                if ($otherProvider) {
                    Common::printDebug("Used $fallbackProviderId to detect this visitor IP");
                    return $otherProvider->getLocation($info);
                }
            }
            Common::printDebug("FAILED to lookup the geo location of this IP address, as no fallback location providers is configured. We recommend to configure Geolocation PECL module to fix this error.");

            return false;
        }

        $result = array();
        foreach (self::$geoIpServerVars as $resultKey => $geoipVarName) {
            if (!empty($_SERVER[$geoipVarName])) {
                $result[$resultKey] = $_SERVER[$geoipVarName];
            }

            $geoipVarNameV6 = $geoipVarName . '_V6';
            if (!empty($_SERVER[$geoipVarNameV6])) {
                $result[$resultKey] = $_SERVER[$geoipVarNameV6];
            }
        }
        foreach (self::$geoIpUtfServerVars as $resultKey => $geoipVarName) {
            if (!empty($_SERVER[$geoipVarName])) {
                $result[$resultKey] = utf8_encode($_SERVER[$geoipVarName]);
            }
        }
        $this->completeLocationResult($result);
        return $result;
    }

    /**
     * Returns an array describing the types of location information this provider will
     * return.
     *
     * There's no way to tell exactly what database the HTTP server is using, so we just
     * assume country and continent information is available. This can make diagnostics
     * a bit more difficult, unfortunately.
     *
     * @return array
     */
    public function getSupportedLocationInfo()
    {
        $result = array();

        // assume country info is always available. it's an error if it's not.
        $result[self::COUNTRY_CODE_KEY] = true;
        $result[self::COUNTRY_NAME_KEY] = true;
        $result[self::CONTINENT_CODE_KEY] = true;
        $result[self::CONTINENT_NAME_KEY] = true;

        return $result;
    }

    /**
     * Checks if an HTTP server module has been installed. It checks by looking for
     * the GEOIP_ADDR server variable.
     *
     * There's a special check for the Apache module, but we can't check specifically
     * for anything else.
     *
     * @return bool|string
     */
    public function isAvailable()
    {
        // check if apache module is installed
        if (function_exists('apache_get_modules')) {
            foreach (apache_get_modules() as $name) {
                if (strpos($name, 'geoip') !== false) {
                    return true;
                }
            }
        }

        $available = !empty($_SERVER[self::TEST_SERVER_VAR])
            || !empty($_SERVER[self::TEST_SERVER_VAR_ALT])
            || !empty($_SERVER[self::TEST_SERVER_VAR_ALT_IPV6])
        ;

        if ($available) {
            return true;
        }

        // if not available return message w/ extra info
        if (!function_exists('apache_get_modules')) {
            return Piwik::translate('General_Note') . ':&nbsp;' . Piwik::translate('UserCountry_AssumingNonApache');
        }

        $message = "<strong>" . Piwik::translate('General_Note') . ':&nbsp;'
            . Piwik::translate('UserCountry_FoundApacheModules')
            . "</strong>:<br/><br/>\n<ul style=\"list-style:disc;margin-left:24px\">\n";
        foreach (apache_get_modules() as $name) {
            $message .= "<li>$name</li>\n";
        }
        $message .= "</ul>";
        return $message;
    }

    /**
     * Returns true if the GEOIP_ADDR server variable is defined.
     *
     * @return bool
     */
    public function isWorking()
    {
        if (empty($_SERVER[self::TEST_SERVER_VAR])
            && empty($_SERVER[self::TEST_SERVER_VAR_ALT])
            && empty($_SERVER[self::TEST_SERVER_VAR_ALT_IPV6])
        ) {
            return Piwik::translate("UserCountry_CannotFindGeoIPServerVar", self::TEST_SERVER_VAR . ' $_SERVER');
        }

        return true; // can't check for another IP
    }

    /**
     * Returns information about this location provider. Contains an id, title & description:
     *
     * array(
     *     'id' => 'geoip_serverbased',
     *     'title' => '...',
     *     'description' => '...'
     * );
     *
     * @return array
     */
    public function getInfo()
    {
        if (function_exists('apache_note')) {
            $serverDesc = 'Apache';
        } else {
            $serverDesc = Piwik::translate('UserCountry_HttpServerModule');
        }

        $title = sprintf(self::TITLE, $serverDesc);
        $desc = Piwik::translate('UserCountry_GeoIpLocationProviderDesc_ServerBased1', array('<strong>', '</strong>'))
            . '<br/><br/>'
             . Piwik::translate('UserCountry_GeoIpLocationProviderDesc_ServerBasedAnonWarn')
            . '<br/><br/>'
            . Piwik::translate('UserCountry_GeoIpLocationProviderDesc_ServerBased2',
                array('<strong>', '</strong>', '<strong>', '</strong>'));
        $installDocs =
            '<a rel="noreferrer"  target="_blank" href="https://matomo.org/faq/how-to/#faq_165">'
            . Piwik::translate('UserCountry_HowToInstallApacheModule')
            . '</a><br/>'
            . '<a rel="noreferrer"  target="_blank" href="https://matomo.org/faq/how-to/#faq_166">'
            . Piwik::translate('UserCountry_HowToInstallNginxModule')
            . '</a>';

        $geoipServerVars = array();
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'GEOIP') === 0) {
                $geoipServerVars[] = $key;
            }
        }

        if (empty($geoipServerVars)) {
            $extraMessage = '<strong>' . Piwik::translate('UserCountry_GeoIPNoServerVars', '$_SERVER') . '</strong>';
        } else {
            $extraMessage = '<strong>' . Piwik::translate('UserCountry_GeoIPServerVarsFound', '$_SERVER')
                . ":</strong><br/><br/>\n<ul style=\"list-style:disc;margin-left:24px\">\n";
            foreach ($geoipServerVars as $key) {
                $extraMessage .= '<li>' . $key . "</li>\n";
            }
            $extraMessage .= '</ul>';
        }

        return array('id'            => self::ID,
                     'title'         => $title,
                     'description'   => $desc,
                     'order'         => 12,
                     'install_docs'  => $installDocs,
                     'extra_message' => $extraMessage);
    }

    /**
     * Checks if two IP addresses are the same or if the first is the anonymized
     * version of the other.
     *
     * @param string $ip
     * @param string $currentIp This IP should not be anonymized.
     * @return bool
     */
    public static function isSameOrAnonymizedIp($ip, $currentIp)
    {
        $ip = array_reverse(explode('.', $ip));
        $currentIp = array_reverse(explode('.', $currentIp));

        if (count($ip) != count($currentIp)) {
            return false;
        }

        foreach ($ip as $i => $byte) {
            if ($byte == 0) {
                $currentIp[$i] = 0;
            } else {
                break;
            }
        }

        foreach ($ip as $i => $byte) {
            if ($byte != $currentIp[$i]) {
                return false;
            }
        }
        return true;
    }
}
