<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2;

use Piwik\Cache;
use Piwik\Common;
use Piwik\IP;
use Piwik\Piwik;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\GeoIp2\SystemSettings;
use Piwik\SettingsServer;
use Piwik\Url;
use Piwik\View;

/**
 * A LocationProvider that uses an GeoIP 2 module installed in an HTTP Server.
 *
 * To make this provider available, make sure mod_maxminddb / ngx_http_geoip2_module installed and active
 */
class ServerModule extends GeoIp2
{
    const ID = 'geoip2server';
    const TITLE = 'DBIP / GeoIP 2 (%s)';

    public static $defaultGeoIpServerVars = array(
        parent::CONTINENT_CODE_KEY => 'MM_CONTINENT_CODE',
        parent::CONTINENT_NAME_KEY => 'MM_CONTINENT_NAME',
        parent::COUNTRY_CODE_KEY   => 'MM_COUNTRY_CODE',
        parent::COUNTRY_NAME_KEY   => 'MM_COUNTRY_NAME',
        parent::REGION_CODE_KEY    => 'MM_REGION_CODE',
        parent::REGION_NAME_KEY    => 'MM_REGION_NAME',
        parent::LATITUDE_KEY       => 'MM_LATITUDE',
        parent::LONGITUDE_KEY      => 'MM_LONGITUDE',
        parent::POSTAL_CODE_KEY    => 'MM_POSTAL_CODE',
        parent::CITY_NAME_KEY      => 'MM_CITY_NAME',
        parent::ISP_KEY            => 'MM_ISP',
        parent::ORG_KEY            => 'MM_ORG',
    );

    /**
     * Uses a GeoIP 2 database to get a visitor's location based on their IP address.
     *
     * This function will return different results based on the data used and based
     * on how the GeoIP 2 module is configured.
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
            Common::printDebug("The request is for IP address: " . $info['ip'] . " but your IP is: $myIP. GeoIP 2 (Server Module) does not support this use case... ");
            $fallbacks = array(
                Php::ID
            );
            foreach ($fallbacks as $fallbackProviderId) {
                $otherProvider = LocationProvider::getProviderById($fallbackProviderId);
                if ($otherProvider) {
                    Common::printDebug("Used $fallbackProviderId to detect this visitor IP");
                    return $otherProvider->getLocation($info);
                }
            }
            Common::printDebug("FAILED to lookup the geo location of this IP address, as no fallback location providers is configured.");
            return false;
        }

        $result = array();
        foreach (self::getGeoIpServerVars() as $resultKey => $geoipVarName) {
            if (!empty($_SERVER[$geoipVarName])) {
                $result[$resultKey] = $_SERVER[$geoipVarName];
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
        $result[self::CONTINENT_CODE_KEY] = true;
        $result[self::CONTINENT_NAME_KEY] = true;
        $result[self::COUNTRY_CODE_KEY] = true;
        $result[self::COUNTRY_NAME_KEY] = true;

        $result[self::REGION_CODE_KEY] = array_key_exists(self::getGeoIpServerVars(self::REGION_CODE_KEY), $_SERVER);
        $result[self::REGION_NAME_KEY] = array_key_exists(self::getGeoIpServerVars(self::REGION_NAME_KEY), $_SERVER);
        $result[self::LATITUDE_KEY] = array_key_exists(self::getGeoIpServerVars(self::LATITUDE_KEY), $_SERVER);
        $result[self::LONGITUDE_KEY] = array_key_exists(self::getGeoIpServerVars(self::LONGITUDE_KEY), $_SERVER);
        $result[self::POSTAL_CODE_KEY] = array_key_exists(self::getGeoIpServerVars(self::POSTAL_CODE_KEY), $_SERVER);
        $result[self::CITY_NAME_KEY] = array_key_exists(self::getGeoIpServerVars(self::CITY_NAME_KEY), $_SERVER);
        $result[self::ISP_KEY] = array_key_exists(self::getGeoIpServerVars(self::ISP_KEY), $_SERVER);
        $result[self::ORG_KEY] = array_key_exists(self::getGeoIpServerVars(self::ORG_KEY), $_SERVER);

        return $result;
    }

    /**
     * Checks if an mod_maxminddb has been installed and MMDB_ADDR server variable is defined.
     *
     * There's a special check for the Apache module, but we can't check specifically
     * for anything else.
     *
     * @return bool|string
     */
    public function isAvailable()
    {
        if (function_exists('apache_get_modules')) {
            foreach (apache_get_modules() as $name) {
                if (strpos($name, 'maxminddb') !== false) {
                    return true;
                }
            }
        }

        $settings = self::getGeoIpServerVars();

        $available = array_key_exists($settings[self::CONTINENT_CODE_KEY], $_SERVER)
            || array_key_exists($settings[self::COUNTRY_CODE_KEY], $_SERVER)
            || array_key_exists($settings[self::REGION_CODE_KEY], $_SERVER)
            || array_key_exists($settings[self::CITY_NAME_KEY], $_SERVER);

        if ($available) {
            return true;
        }

        // if not available return message w/ extra info
        if (!function_exists('apache_get_modules')) {
            return Piwik::translate('General_Note') . ':&nbsp;' . Piwik::translate('GeoIp2_AssumingNonApache');
        }

        $message = "<strong>" . Piwik::translate('General_Note') . ':&nbsp;'
            . Piwik::translate('GeoIp2_FoundApacheModules')
            . "</strong>:<br/><br/>\n<ul style=\"list-style:disc;margin-left:24px\">\n";
        foreach (apache_get_modules() as $name) {
            $message .= "<li>$name</li>\n";
        }
        $message .= "</ul>";
        return $message;
    }

    /**
     * Returns true if the MMDB_ADDR server variable is defined.
     *
     * @return bool
     */
    public function isWorking()
    {
        $settings = self::getGeoIpServerVars();

        $available = array_key_exists($settings[self::CONTINENT_CODE_KEY], $_SERVER)
            || array_key_exists($settings[self::COUNTRY_CODE_KEY], $_SERVER)
            || array_key_exists($settings[self::REGION_CODE_KEY], $_SERVER)
            || array_key_exists($settings[self::CITY_NAME_KEY], $_SERVER);

        if (!$available) {
            return Piwik::translate('GeoIp2_CannotFindGeoIPServerVar', $settings[self::COUNTRY_CODE_KEY] . ' $_SERVER');
        }

        return true;
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
            $serverDesc = Piwik::translate('GeoIp2_HttpServerModule');
        }

        $title = sprintf(self::TITLE, $serverDesc);

        $desc = Piwik::translate('GeoIp2_LocationProviderDesc_ServerModule', array('<strong>', '</strong>'))
            . '<br/><br/>'
            . Piwik::translate('GeoIp2_GeoIPLocationProviderDesc_ServerBasedAnonWarn')
            . '<br/><br/>'
            . Piwik::translate('GeoIp2_LocationProviderDesc_ServerModule2',
                array('<strong>', '</strong>', '<strong>', '</strong>'));

        $installDocs =
            '<a rel="noreferrer"  target="_blank" href="https://maxmind.github.io/mod_maxminddb/">'
            . Piwik::translate('GeoIp2_HowToInstallApacheModule')
            . '</a><br/>'
            . '<a rel="noreferrer"  target="_blank" href="https://github.com/leev/ngx_http_geoip2_module/blob/master/README.md#installing">'
            . Piwik::translate('GeoIp2_HowToInstallNginxModule')
            . '</a>';

        $geoipServerVars = array();
        foreach ($_SERVER as $key => $value) {
            if (in_array($key, self::getGeoIpServerVars())) {
                $geoipServerVars[] = $key;
            }
        }

        if (empty($geoipServerVars)) {
            $extraMessage = '<strong>' . Piwik::translate('GeoIp2_GeoIPNoServerVars', '$_SERVER') . '</strong>';
        } else {
            $extraMessage = '<strong>' . Piwik::translate('GeoIp2_GeoIPServerVarsFound', '$_SERVER')
                . ":</strong><br/><br/>\n<ul style=\"list-style:disc;margin-left:24px\">\n";
            foreach ($geoipServerVars as $key) {
                $extraMessage .= '<li>' . $key . "</li>\n";
            }
            $extraMessage .= '</ul>';
        }

        $configUrl = Url::getCurrentQueryStringWithParametersModified(array(
            'module' => 'CoreAdminHome', 'action' => 'generalSettings'
        ));
        if (!SettingsServer::isTrackerApiRequest()) {
            // can't render in tracking mode as there is no theme
            $view = new View('@GeoIp2/serverModule');
            $view->configUrl = $configUrl;
            $extraMessage .= $view->render();
        }

        return array('id'            => self::ID,
            'title'         => $title,
            'description'   => $desc,
            'order'         => 3,
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

    /**
     * Returns currently configured server variable name for given type
     *
     * @param string|null $type
     * @return mixed|string
     */
    protected static function getGeoIpServerVars($type = null)
    {
        $storedSettings = self::getSystemSettingsValues();

        if ($type === null) {
            return $storedSettings;
        }

        if (array_key_exists($type, $storedSettings)) {
            return $storedSettings[$type];
        }

        return '';
    }

    protected static function getSystemSettingsValues()
    {
        $cacheKey = 'geoip2variables';

        // use eager cache if this data needs to be available on every tracking request
        $cache = Cache::getEagerCache();

        if ($cache->contains($cacheKey)) {
            return $cache->fetch($cacheKey);
        }

        $settingValues = self::$defaultGeoIpServerVars; // preset with defaults

        try {
            $systemSettings = new SystemSettings();

            foreach ($systemSettings->geoIp2variables as $name => $setting) {
                $settingValues[$name] = $setting->getValue();
            }

            $cache->save($cacheKey, $settingValues);
        } catch (\Exception $e) {
        }

        return $settingValues;
    }

    public function getUsageWarning(): ?string
    {
        $comment = Piwik::translate('GeoIp2_GeoIPLocationProviderNotRecommended') . ' ';
        $comment .= Piwik::translate('GeoIp2_LocationProviderDesc_ServerModule2', array(
            '<a href="https://matomo.org/docs/geo-locate/" rel="noreferrer noopener" target="_blank">', '', '', '</a>'
        ));

        return $comment;
    }
}
