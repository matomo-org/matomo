<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Live
 */
namespace Piwik\Plugins\Live;

use Piwik\Common;
use Piwik\IP;
use Piwik\Piwik;
use Piwik\Plugins\API\API as APIMetadata;
use Piwik\Plugins\Referrers\API as APIReferrers;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp;
use Piwik\Tracker;
use Piwik\Tracker\Visit;
use Piwik\UrlHelper;

/**
 * @see plugins/Referrers/functions.php
 * @see plugins/UserCountry/functions.php
 * @see plugins/UserSettings/functions.php
 * @see plugins/Provider/functions.php
 */

require_once PIWIK_INCLUDE_PATH . '/plugins/Referrers/functions.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/functions.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/UserSettings/functions.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/Provider/functions.php';

/**
 * @package Live
 */
class Visitor
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
            'events'                      => $this->getNumberOfEvents(),
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

            'referrerType'                => $this->getReferrerType(),
            'referrerTypeName'            => $this->getReferrerTypeName(),
            'referrerName'                => $this->getReferrerName(),
            'referrerKeyword'             => $this->getKeyword(),
            'referrerKeywordPosition'     => $this->getKeywordPosition(),
            'referrerUrl'                 => $this->getReferrerUrl(),
            'referrerSearchEngineUrl'     => $this->getSearchEngineUrl(),
            'referrerSearchEngineIcon'    => $this->getSearchEngineIcon(),
            'operatingSystem'             => $this->getOperatingSystem(),
            'operatingSystemCode'         => $this->getOperatingSystemCode(),
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
            return IP::N2P($this->details['location_ip']);
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

    function getNumberOfEvents()
    {
        return $this->details['visit_total_events'];
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
        return \Piwik\MetricsFormatter::getPrettyTimeFromSeconds($this->details['visit_total_time']);
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
            return "plugins/Live/images/returningVisitor.gif";
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
        return \Piwik\Plugins\UserCountry\countryTranslate($this->getCountryCode());
    }

    function getCountryFlag()
    {
        return \Piwik\Plugins\UserCountry\getFlagFromCode($this->getCountryCode());
    }

    function getContinent()
    {
        return \Piwik\Plugins\UserCountry\continentTranslate($this->getContinentCode());
    }

    function getContinentCode()
    {
        return Common::getContinent($this->details['location_country']);
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
        if ($region != '' && $region != Visit::UNKNOWN_CODE) {
            return GeoIp::getRegionNameFromCodes(
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
        for ($i = 1; $i <= Tracker::MAX_CUSTOM_VARIABLES; $i++) {
            if (!empty($this->details['custom_var_k' . $i])) {
                $customVariables[$i] = array(
                    'customVariableName' . $i  => $this->details['custom_var_k' . $i],
                    'customVariableValue' . $i => $this->details['custom_var_v' . $i],
                );
            }
        }
        return $customVariables;
    }

    function getReferrerType()
    {
        return \Piwik\Plugins\Referrers\getReferrerTypeFromShortName($this->details['referer_type']);
    }

    function getReferrerTypeName()
    {
        return \Piwik\Plugins\Referrers\getReferrerTypeLabel($this->details['referer_type']);
    }

    function getKeyword()
    {
        $keyword = $this->details['referer_keyword'];
        if (\Piwik\Plugin\Manager::getInstance()->isPluginActivated('Referrers')
            && $this->getReferrerType() == 'search'
        ) {
            $keyword = \Piwik\Plugins\Referrers\API::getCleanKeyword($keyword);
        }
        return urldecode($keyword);
    }

    function getReferrerUrl()
    {
        if ($this->getReferrerType() == 'search') {
            if (\Piwik\Plugin\Manager::getInstance()->isPluginActivated('Referrers')
                && $this->details['referer_keyword'] == APIReferrers::LABEL_KEYWORD_NOT_DEFINED
            ) {
                return 'http://piwik.org/faq/general/#faq_144';
            } // Case URL is google.XX/url.... then we rewrite to the search result page url
            elseif ($this->getReferrerName() == 'Google'
                && strpos($this->details['referer_url'], '/url')
            ) {
                $refUrl = @parse_url($this->details['referer_url']);
                if (isset($refUrl['host'])) {
                    $url = \Piwik\Plugins\Referrers\getSearchEngineUrlFromUrlAndKeyword('http://google.com', $this->getKeyword());
                    $url = str_replace('google.com', $refUrl['host'], $url);
                    return $url;
                }
            }
        }
        if (\Piwik\UrlHelper::isLookLikeUrl($this->details['referer_url'])) {
            return $this->details['referer_url'];
        }
        return null;
    }

    function getKeywordPosition()
    {
        if ($this->getReferrerType() == 'search'
            && strpos($this->getReferrerName(), 'Google') !== false
        ) {
            $url = @parse_url($this->details['referer_url']);
            if (empty($url['query'])) {
                return null;
            }
            $position = UrlHelper::getParameterFromQueryString($url['query'], 'cd');
            if (!empty($position)) {
                return $position;
            }
        }
        return null;
    }

    function getReferrerName()
    {
        return urldecode($this->details['referer_name']);
    }

    function getSearchEngineUrl()
    {
        if ($this->getReferrerType() == 'search'
            && !empty($this->details['referer_name'])
        ) {
            return \Piwik\Plugins\Referrers\getSearchEngineUrlFromName($this->details['referer_name']);
        }
        return null;
    }

    function getSearchEngineIcon()
    {
        $searchEngineUrl = $this->getSearchEngineUrl();
        if (!is_null($searchEngineUrl)) {
            return \Piwik\Plugins\Referrers\getSearchEngineLogoFromUrl($searchEngineUrl);
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
                $pluginIcons[] = array("pluginIcon" => \Piwik\Plugins\UserSettings\getPluginsLogo($plugin), "pluginName" => $plugin);
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
        return \Piwik\Plugins\UserSettings\getOSLabel($this->details['config_os']);
    }

    function getOperatingSystemShortName()
    {
        return \Piwik\Plugins\UserSettings\getOSShortLabel($this->details['config_os']);
    }

    function getOperatingSystemIcon()
    {
        return \Piwik\Plugins\UserSettings\getOSLogo($this->details['config_os']);
    }

    function getBrowserFamilyDescription()
    {
        return \Piwik\Plugins\UserSettings\getBrowserTypeLabel($this->getBrowserFamily());
    }

    function getBrowserFamily()
    {
        return \Piwik\Plugins\UserSettings\getBrowserFamily($this->details['config_browser_name']);
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
        return \Piwik\Plugins\UserSettings\getBrowserLabel($this->details['config_browser_name'] . ";" . $this->details['config_browser_version']);
    }

    function getBrowserIcon()
    {
        return \Piwik\Plugins\UserSettings\getBrowsersLogo($this->details['config_browser_name'] . ";" . $this->details['config_browser_version']);
    }

    function getScreenType()
    {
        return \Piwik\Plugins\UserSettings\getScreenTypeFromResolution($this->details['config_resolution']);
    }

    function getDeviceType()
    {
        if (\Piwik\Plugin\Manager::getInstance()->isPluginActivated('DevicesDetection')) {
            return \Piwik\Plugins\DevicesDetection\getDeviceTypeLabel($this->details['config_device_type']);
        }
        return false;
    }

    function getResolution()
    {
        return $this->details['config_resolution'];
    }

    function getScreenTypeIcon()
    {
        return \Piwik\Plugins\UserSettings\getScreensLogo($this->getScreenType());
    }

    function getProvider()
    {
        if (isset($this->details['location_provider'])) {
            return $this->details['location_provider'];
        } else {
            return Piwik::translate('General_Unknown');
        }
    }

    function getProviderName()
    {
        return \Piwik\Plugins\Provider\getPrettyProviderName($this->getProvider());
    }

    function getProviderUrl()
    {
        return \Piwik\Plugins\Provider\getHostnameUrl(@$this->details['location_provider']);
    }

    function getDateTimeLastAction()
    {
        return date('Y-m-d H:i:s', strtotime($this->details['visit_last_action_time']));
    }

    function getVisitEcommerceStatusIcon()
    {
        $status = $this->getVisitEcommerceStatus();

        if (in_array($status, array('ordered', 'orderedThenAbandonedCart'))) {
            return "plugins/Zeitgeist/images/ecommerceOrder.gif";
        } elseif ($status == 'abandonedCart') {
            return "plugins/Zeitgeist/images/ecommerceAbandonedCart.gif";
        }
        return null;
    }

    function getVisitEcommerceStatus()
    {
        return APIMetadata::getVisitEcommerceStatusFromId($this->details['visit_goal_buyer']);
    }

    function getVisitorGoalConvertedIcon()
    {
        return $this->isVisitorGoalConverted()
            ? "plugins/Zeitgeist/images/goal.png"
            : null;
    }

    function isVisitorGoalConverted()
    {
        return $this->details['visit_goal_converted'];
    }
}
