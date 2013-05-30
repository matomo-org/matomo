<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Live
 */

/**
 * @see plugins/Referers/functions.php
 * @see plugins/UserCountry/functions.php
 * @see plugins/UserSettings/functions.php
 * @see plugins/Provider/functions.php
 */

require_once PIWIK_INCLUDE_PATH . '/plugins/Referers/functions.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/functions.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/UserSettings/functions.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/Provider/functions.php';

/**
 *
 * @package Piwik_Live
 */
class Piwik_Live_Visitor
{
    const DELIMITER_PLUGIN_NAME = ", ";

    function __construct($visitorRawData)
    {
        $this->details = $visitorRawData;
    }

    function getAllVisitorDetails()
    {
        return array(
            'idSite'                      => $this->getIdSite(),
            'idVisit'                     => $this->getIdVisit(),
            'visitIp'                     => $this->getIp(),
            'visitorId'                   => $this->getVisitorId(),
            'visitorType'                 => $this->getVisitorReturning(),
            'visitorTypeIcon'             => $this->getVisitorReturningIcon(),
            'visitConverted'              => $this->isVisitorGoalConverted(),
            'visitConvertedIcon'          => $this->getVisitorGoalConvertedIcon(),
            'visitEcommerceStatus'        => $this->getVisitEcommerceStatus(),
            'visitEcommerceStatusIcon'    => $this->getVisitEcommerceStatusIcon(),

            'searches'                    => $this->getNumberOfSearches(),
            'actions'                     => $this->getNumberOfActions(),
            // => false are placeholders to be filled in API later
            'actionDetails'               => false,
            'customVariables'             => $this->getCustomVariables(),
            'goalConversions'             => false,
            'siteCurrency'                => false,
            'siteCurrencySymbol'          => false,

            // all time entries
            'serverDate'                  => $this->getServerDate(),
            'visitLocalTime'              => $this->getVisitLocalTime(),
            'visitLocalHour'              => $this->getVisitLocalHour(),
            'visitServerHour'             => $this->getVisitServerHour(),
            'firstActionTimestamp'        => $this->getTimestampFirstAction(),
            'lastActionTimestamp'         => $this->getTimestampLastAction(),
            'lastActionDateTime'          => $this->getDateTimeLastAction(),

            // standard attributes
            'visitDuration'               => $this->getVisitLength(),
            'visitDurationPretty'         => $this->getVisitLengthPretty(),
            'visitCount'                  => $this->getVisitCount(),
            'daysSinceLastVisit'          => $this->getDaysSinceLastVisit(),
            'daysSinceFirstVisit'         => $this->getDaysSinceFirstVisit(),
            'daysSinceLastEcommerceOrder' => $this->getDaysSinceLastEcommerceOrder(),
            'continent'                   => $this->getContinent(),
            'continentCode'               => $this->getContinentCode(),
            'country'                     => $this->getCountryName(),
            'countryCode'                 => $this->getCountryCode(),
            'countryFlag'                 => $this->getCountryFlag(),
            'region'                      => $this->getRegionName(),
            'regionCode'                  => $this->getRegionCode(),
            'city'                        => $this->getCityName(),
            'location'                    => $this->getPrettyLocation(),
            'latitude'                    => $this->getLatitude(),
            'longitude'                   => $this->getLongitude(),
            'provider'                    => $this->getProvider(),
            'providerName'                => $this->getProviderName(),
            'providerUrl'                 => $this->getProviderUrl(),

            'referrerType'                => $this->getRefererType(),
            'referrerTypeName'            => $this->getRefererTypeName(),
            'referrerName'                => $this->getRefererName(),
            'referrerKeyword'             => $this->getKeyword(),
            'referrerKeywordPosition'     => $this->getKeywordPosition(),
            'referrerUrl'                 => $this->getRefererUrl(),
            'referrerSearchEngineUrl'     => $this->getSearchEngineUrl(),
            'referrerSearchEngineIcon'    => $this->getSearchEngineIcon(),
            'operatingSystem'             => $this->getOperatingSystem(),
            'operatingSystemCode'             => $this->getOperatingSystemCode(),
            'operatingSystemShortName'    => $this->getOperatingSystemShortName(),
            'operatingSystemIcon'         => $this->getOperatingSystemIcon(),
            'browserFamily'               => $this->getBrowserFamily(),
            'browserFamilyDescription'    => $this->getBrowserFamilyDescription(),
            'browserName'                 => $this->getBrowser(),
            'browserIcon'                 => $this->getBrowserIcon(),
            'browserCode'                 => $this->getBrowserCode(),
            'browserVersion'              => $this->getBrowserVersion(),
            'screenType'                  => $this->getScreenType(),
            'deviceType'                  => $this->getDeviceType(),
            'resolution'                  => $this->getResolution(),
            'screenTypeIcon'              => $this->getScreenTypeIcon(),
            'plugins'                     => $this->getPlugins(),
            'pluginsIcons'                => $this->getPluginIcons(),
        );
    }

    function getVisitorId()
    {
        if (isset($this->details['idvisitor'])) {
            return bin2hex($this->details['idvisitor']);
        }
        return false;
    }

    function getVisitLocalTime()
    {
        return $this->details['visitor_localtime'];
    }

    function getVisitServerHour()
    {
        return date('G', strtotime($this->details['visit_last_action_time']));
    }

    function getVisitLocalHour()
    {
        return date('G', strtotime('2012-12-21 ' . $this->details['visitor_localtime']));
    }

    function getVisitCount()
    {
        return $this->details['visitor_count_visits'];
    }

    function getDaysSinceLastVisit()
    {
        return $this->details['visitor_days_since_last'];
    }

    function getDaysSinceLastEcommerceOrder()
    {
        return $this->details['visitor_days_since_order'];
    }

    function getDaysSinceFirstVisit()
    {
        return $this->details['visitor_days_since_first'];
    }

    function getServerDate()
    {
        return date('Y-m-d', strtotime($this->details['visit_last_action_time']));
    }

    function getIp()
    {
        if (isset($this->details['location_ip'])) {
            return Piwik_IP::N2P($this->details['location_ip']);
        }
        return false;
    }

    function getIdVisit()
    {
        return $this->details['idvisit'];
    }

    function getIdSite()
    {
        return $this->details['idsite'];
    }

    function getNumberOfActions()
    {
        return $this->details['visit_total_actions'];
    }

    function getNumberOfSearches()
    {
        return $this->details['visit_total_searches'];
    }

    function getVisitLength()
    {
        return $this->details['visit_total_time'];
    }

    function getVisitLengthPretty()
    {
        return Piwik::getPrettyTimeFromSeconds($this->details['visit_total_time']);
    }

    function getVisitorReturning()
    {
        $type = $this->details['visitor_returning'];
        return $type == 2
            ? 'returningCustomer'
            : ($type == 1
                ? 'returning'
                : 'new');
    }

    function getVisitorReturningIcon()
    {
        $type = $this->getVisitorReturning();
        if ($type == 'returning'
            || $type == 'returningCustomer'
        ) {
            return "plugins/Live/templates/images/returningVisitor.gif";
        }
        return null;
    }

    function getTimestampFirstAction()
    {
        return strtotime($this->details['visit_first_action_time']);
    }

    function getTimestampLastAction()
    {
        return strtotime($this->details['visit_last_action_time']);
    }

    function getCountryCode()
    {
        return $this->details['location_country'];
    }

    function getCountryName()
    {
        return Piwik_CountryTranslate($this->getCountryCode());
    }

    function getCountryFlag()
    {
        return Piwik_getFlagFromCode($this->getCountryCode());
    }

    function getContinent()
    {
        return Piwik_ContinentTranslate($this->getContinentCode());
    }

    function getContinentCode()
    {
        return Piwik_Common::getContinent($this->details['location_country']);
    }

    function getCityName()
    {
        if (!empty($this->details['location_city'])) {
            return $this->details['location_city'];
        }
        return null;
    }

    public function getRegionName()
    {
        $region = $this->getRegionCode();
        if ($region != '' && $region != Piwik_Tracker_Visit::UNKNOWN_CODE) {
            return Piwik_UserCountry_LocationProvider_GeoIp::getRegionNameFromCodes(
                $this->details['location_country'], $region);
        }
        return null;
    }

    public function getRegionCode()
    {
        return $this->details['location_region'];
    }

    function getPrettyLocation()
    {
        $parts = array();

        $city = $this->getCityName();
        if (!empty($city)) {
            $parts[] = $city;
        }
        $region = $this->getRegionName();
        if (!empty($region)) {
            $parts[] = $region;
        }

        // add country & return concatenated result
        $parts[] = $this->getCountryName();
        return implode(', ', $parts);
    }

    function getLatitude()
    {
        if (!empty($this->details['location_latitude'])) {
            return $this->details['location_latitude'];
        }
        return null;
    }

    function getLongitude()
    {
        if (!empty($this->details['location_longitude'])) {
            return $this->details['location_longitude'];
        }
        return null;
    }

    function getCustomVariables()
    {
        $customVariables = array();
        for ($i = 1; $i <= Piwik_Tracker::MAX_CUSTOM_VARIABLES; $i++) {
            if (!empty($this->details['custom_var_k' . $i])) {
                $customVariables[$i] = array(
                    'customVariableName' . $i  => $this->details['custom_var_k' . $i],
                    'customVariableValue' . $i => $this->details['custom_var_v' . $i],
                );
            }
        }
        return $customVariables;
    }

    function getRefererType()
    {
        return Piwik_getRefererTypeFromShortName($this->details['referer_type']);
    }

    function getRefererTypeName()
    {
        return Piwik_getRefererTypeLabel($this->details['referer_type']);
    }

    function getKeyword()
    {
        $keyword = $this->details['referer_keyword'];
        if (Piwik_PluginsManager::getInstance()->isPluginActivated('Referers')
            && $this->getRefererType() == 'search'
        ) {
            $keyword = Piwik_Referers::getCleanKeyword($keyword);
        }
        return urldecode($keyword);
    }

    function getRefererUrl()
    {
        if ($this->getRefererType() == 'search') {
            if (Piwik_PluginsManager::getInstance()->isPluginActivated('Referers')
                && $this->details['referer_keyword'] == Piwik_Referers::LABEL_KEYWORD_NOT_DEFINED
            ) {
                return 'http://piwik.org/faq/general/#faq_144';
            } // Case URL is google.XX/url.... then we rewrite to the search result page url
            elseif ($this->getRefererName() == 'Google'
                && strpos($this->details['referer_url'], '/url')
            ) {
                $refUrl = @parse_url($this->details['referer_url']);
                if (isset($refUrl['host'])) {
                    $url = Piwik_getSearchEngineUrlFromUrlAndKeyword('http://google.com', $this->getKeyword());
                    $url = str_replace('google.com', $refUrl['host'], $url);
                    return $url;
                }
            }
        }
        if (Piwik_Common::isLookLikeUrl($this->details['referer_url'])) {
            return $this->details['referer_url'];
        }
        return null;
    }

    function getKeywordPosition()
    {
        if ($this->getRefererType() == 'search'
            && strpos($this->getRefererName(), 'Google') !== false
        ) {
            $url = @parse_url($this->details['referer_url']);
            if (empty($url['query'])) {
                return null;
            }
            $position = Piwik_Common::getParameterFromQueryString($url['query'], 'cd');
            if (!empty($position)) {
                return $position;
            }
        }
        return null;
    }

    function getRefererName()
    {
        return urldecode($this->details['referer_name']);
    }

    function getSearchEngineUrl()
    {
        if ($this->getRefererType() == 'search'
            && !empty($this->details['referer_name'])
        ) {
            return Piwik_getSearchEngineUrlFromName($this->details['referer_name']);
        }
        return null;
    }

    function getSearchEngineIcon()
    {
        $searchEngineUrl = $this->getSearchEngineUrl();
        if (!is_null($searchEngineUrl)) {
            return Piwik_getSearchEngineLogoFromUrl($searchEngineUrl);
        }
        return null;
    }

    function getPlugins()
    {
        $plugins = array(
            'config_pdf',
            'config_flash',
            'config_java',
            'config_director',
            'config_quicktime',
            'config_realplayer',
            'config_windowsmedia',
            'config_gears',
            'config_silverlight',
        );
        $pluginShortNames = array();
        foreach ($plugins as $plugin) {
            if ($this->details[$plugin] == 1) {
                $pluginShortName = substr($plugin, 7);
                $pluginShortNames[] = $pluginShortName;
            }
        }
        return implode(self::DELIMITER_PLUGIN_NAME, $pluginShortNames);
    }

    function getPluginIcons()
    {
        $pluginNames = $this->getPlugins();
        if (!empty($pluginNames)) {
            $pluginNames = explode(self::DELIMITER_PLUGIN_NAME, $pluginNames);
            $pluginIcons = array();

            foreach ($pluginNames as $plugin) {
                $pluginIcons[] = array("pluginIcon" => Piwik_getPluginsLogo($plugin), "pluginName" => $plugin);
            }
            return $pluginIcons;
        }
        return null;
    }

    function getOperatingSystemCode()
    {
        return $this->details['config_os'];
    }

    function getOperatingSystem()
    {
        return Piwik_getOSLabel($this->details['config_os']);
    }

    function getOperatingSystemShortName()
    {
        return Piwik_getOSShortLabel($this->details['config_os']);
    }

    function getOperatingSystemIcon()
    {
        return Piwik_getOSLogo($this->details['config_os']);
    }

    function getBrowserFamilyDescription()
    {
        return Piwik_getBrowserTypeLabel($this->getBrowserFamily());
    }

    function getBrowserFamily()
    {
        return Piwik_getBrowserFamily($this->details['config_browser_name']);
    }

    function getBrowserCode()
    {
        return $this->details['config_browser_name'];
    }

    function getBrowserVersion()
    {
        return $this->details['config_browser_version'];
    }

    function getBrowser()
    {
        return Piwik_getBrowserLabel($this->details['config_browser_name'] . ";" . $this->details['config_browser_version']);
    }

    function getBrowserIcon()
    {
        return Piwik_getBrowsersLogo($this->details['config_browser_name'] . ";" . $this->details['config_browser_version']);
    }

    function getScreenType()
    {
        return Piwik_getScreenTypeFromResolution($this->details['config_resolution']);
    }

    function getDeviceType()
    {
        if(Piwik_PluginsManager::getInstance()->isPluginActivated('DevicesDetection')) {
            return Piwik_getDeviceTypeLabel($this->details['config_device_type']);
        }
        return false;
    }

    function getResolution()
    {
        return $this->details['config_resolution'];
    }

    function getScreenTypeIcon()
    {
        return Piwik_getScreensLogo($this->getScreenType());
    }
    
    function getProvider()
    {
        if (isset($this->details['location_provider'])) {
            return $this->details['location_provider'];
        } else {
            return Piwik_Translate('General_Unknown');
        }
    }

    function getProviderName()
    {
        return Piwik_Provider_getPrettyProviderName($this->getProvider());
    }

    function getProviderUrl()
    {
        return Piwik_getHostnameUrl(@$this->details['location_provider']);
    }

    function getDateTimeLastAction()
    {
        return date('Y-m-d H:i:s', strtotime($this->details['visit_last_action_time']));
    }

    function getVisitEcommerceStatusIcon()
    {
        $status = $this->getVisitEcommerceStatus();

        if (in_array($status, array('ordered', 'orderedThenAbandonedCart'))) {
            return "themes/default/images/ecommerceOrder.gif";
        } elseif ($status == 'abandonedCart') {
            return "themes/default/images/ecommerceAbandonedCart.gif";
        }
        return null;
    }

    function getVisitEcommerceStatus()
    {
        return Piwik_API_API::getVisitEcommerceStatusFromId($this->details['visit_goal_buyer']);
    }

    function getVisitorGoalConvertedIcon()
    {
        return $this->isVisitorGoalConverted()
            ? "themes/default/images/goal.png"
            : null;
    }

    function isVisitorGoalConverted()
    {
        return $this->details['visit_goal_converted'];
    }
}
