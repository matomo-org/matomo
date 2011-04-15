<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
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
			'idSite' => $this->getIdSite(),
			'idVisit' => $this->getIdVisit(),
			'visitIp' => $this->getIp(),
			'visitorId' => $this->getVisitorId(),
			'visitorType' => $this->isVisitorReturning() ? 'returning' : 'new',
			'visitConverted' => $this->isVisitorGoalConverted(),
		
			'actions' => $this->getNumberOfActions(),
			// => false are placeholders to be filled in API later
			'actionDetails' => false,
			'customVariables' => $this->getCustomVariables(),
			'goalConversions' => false,
			'siteCurrency' => false,

			// all time entries
			'serverDate' => $this->getServerDate(),
			'visitLocalTime' => $this->getVisitLocalTime(),
			'firstActionTimestamp' => $this->getTimestampFirstAction(),
			'lastActionTimestamp' => $this->getTimestampLastAction(),
			'lastActionDateTime' => $this->getDateTimeLastAction(),
		
			// standard attributes
			'visitDuration' => $this->getVisitLength(),
			'visitDurationPretty' => $this->getVisitLengthPretty(),
			'visitCount' => $this->getVisitCount(),
			'daysSinceLastVisit' => $this->getDaysSinceLastVisit(),
			'daysSinceFirstVisit' => $this->getDaysSinceFirstVisit(),
			'country' => $this->getCountryName(),
			'countryFlag' => $this->getCountryFlag(),
			'continent' => $this->getContinent(),
			'provider' => $this->getProvider(),
			'providerUrl' => $this->getProviderUrl(),
			'referrerType' => $this->getRefererType(),
			'referrerTypeName' => $this->getRefererTypeName(),
			'referrerName' => $this->getRefererName(),
			'referrerKeyword' => $this->getKeyword(),
			'referrerKeywordPosition' => $this->getKeywordPosition(),
			'referrerUrl' => $this->getRefererUrl(),
			'referrerSearchEngineUrl' => $this->getSearchEngineUrl(),
			'referrerSearchEngineIcon' => $this->getSearchEngineIcon(),
			'operatingSystem' => $this->getOperatingSystem(),
			'operatingSystemShortName' => $this->getOperatingSystemShortName(),
			'operatingSystemIcon' => $this->getOperatingSystemIcon(),
			'browserFamily' => $this->getBrowserFamily(),
			'browserFamilyDescription' => $this->getBrowserFamilyDescription(),
	 		'browserName' => $this->getBrowser(),
			'browserIcon' => $this->getBrowserIcon(),
			'screenType' => $this->getScreenType(),
			'resolution' => $this->getResolution(),
			'screenTypeIcon' => $this->getScreenTypeIcon(),
			'plugins' => $this->getPlugins(),
			'pluginsIcons' => $this->getPluginIcons(),
		);
	}

	function getVisitorId()
	{
		if(isset($this->details['idvisitor']))
		{
			return bin2hex($this->details['idvisitor']);
		}
		return false;
	}
	
	function getVisitLocalTime()
	{
		return $this->details['visitor_localtime'];
	}
	
	function getVisitCount()
	{
		return $this->details['visitor_count_visits'];
	}
	
	function getDaysSinceLastVisit()
	{
		return $this->details['visitor_days_since_last'];
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
		if(isset($this->details['location_ip']))
		{
			return Piwik_Common::long2ip($this->details['location_ip']);
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

	function getVisitLength()
	{
		return $this->details['visit_total_time'];
	}

	function getVisitLengthPretty()
	{
		return Piwik::getPrettyTimeFromSeconds($this->details['visit_total_time']);
	}

	function isVisitorReturning()
	{
		return $this->details['visitor_returning'];
	}

	function getTimestampFirstAction()
	{
		return strtotime($this->details['visit_first_action_time']);
	}

	function getTimestampLastAction()
	{
		return strtotime($this->details['visit_last_action_time']);
	}

	function getCountryName()
	{
		return Piwik_CountryTranslate($this->details['location_country']);
	}

	function getCountryFlag()
	{
		return Piwik_getFlagFromCode($this->details['location_country']);
	}

	function getContinent()
	{
		return Piwik_ContinentTranslate($this->details['location_continent']);
	}

	function getCustomVariables()
	{
		$customVariables = array();
		for($i = 1; $i <= Piwik_Tracker::MAX_CUSTOM_VARIABLES; $i++)
		{
			if(!empty($this->details['custom_var_k'.$i])
				&& !empty($this->details['custom_var_v'.$i]))
			{
				$customVariables[$i] = array(
					'customVariableName'.$i => $this->details['custom_var_k'.$i],
					'customVariableValue'.$i => $this->details['custom_var_v'.$i],
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
		return urldecode($this->details['referer_keyword']);
	}

	function getRefererUrl()
	{
		return $this->details['referer_url'];
	}
	
	function getKeywordPosition()
	{
		if($this->getRefererType() == 'search'
			&& strpos($this->getRefererName(), 'Google') !== false)
		{
			$url = $this->getRefererUrl();
			$url = @parse_url($url);
			if(empty($url['query']))
			{
				return null;
			}
			$position = Piwik_Common::getParameterFromQueryString($url['query'], 'cd');
			if(!empty($position))
			{
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
		if($this->getRefererType() == 'search'
		    && !empty($this->details['referer_name']))
		{
			return Piwik_getSearchEngineUrlFromName($this->details['referer_name']);
		}
		return null;
	}

	function getSearchEngineIcon()
	{
		$searchEngineUrl = $this->getSearchEngineUrl();
		if( !is_null($searchEngineUrl) )
		{
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
		foreach($plugins as $plugin)
		{
			if($this->details[$plugin] == 1)
			{
				$pluginShortName = substr($plugin, 7);
				$pluginShortNames[] = $pluginShortName;
			}
		}
		return implode(self::DELIMITER_PLUGIN_NAME, $pluginShortNames);
	}

	function getPluginIcons()
	{
		$pluginNames = $this->getPlugins();
		if( !empty($pluginNames) )
		{
			$pluginNames = explode(self::DELIMITER_PLUGIN_NAME, $pluginNames);
			$pluginIcons = array();

			foreach($pluginNames as $plugin) {
				$pluginIcons[] = array("pluginIcon" =>Piwik_getPluginsLogo($plugin), "pluginName" =>$plugin);
			}
			return $pluginIcons;
		}
		return null;
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
		return Piwik_getHostnameName($this->details['location_provider']);
	}

	function getProviderUrl()
	{
		return Piwik_getHostnameUrl($this->details['location_provider']);
	}

	function getDateTimeLastAction()
	{
		return date('Y-m-d H:i:s', strtotime($this->details['visit_last_action_time']));
	}

	function isVisitorGoalConverted()
	{
		return $this->details['visit_goal_converted'];
	}
}
