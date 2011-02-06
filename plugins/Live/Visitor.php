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
			'ip' => $this->getIp(),
			'idVisit' => $this->getIdVisit(),
			'countActions' => $this->getNumberOfActions(),
			'isVisitorReturning' => $this->isVisitorReturning(),
			'country' => $this->getCountryName(),
			'countryFlag' => $this->getCountryFlag(),
			'continent' => $this->getContinent(),
			'provider' => $this->getProvider(),
			'providerUrl' => $this->getProviderUrl(),
			'idSite' => $this->getIdSite(),
			'serverDate' => $this->getServerDate(),
			'visitLength' => $this->getVisitLength(),
			'visitLengthPretty' => $this->getVisitLengthPretty(),
			'firstActionTimestamp' => $this->getTimestampFirstAction(),
			'lastActionTimestamp' => $this->getTimestampLastAction(),

			'refererType' => $this->getRefererType(),
			'refererName' => $this->getRefererTypeName(),
			'keywords' => $this->getKeywords(),
			'refererUrl' => $this->getRefererUrl(),
			'refererName' => $this->getRefererName(),
			'searchEngineUrl' => $this->getSearchEngineUrl(),
			'searchEngineIcon' => $this->getSearchEngineIcon(),

			'operatingSystem' => $this->getOperatingSystem(),
			'operatingSystemShortName' => $this->getOperatingSystemShortName(),
			'operatingSystemIcon' => $this->getOperatingSystemIcon(),
			'browserFamily' => $this->getBrowserFamily(),
			'browserFamilyDescription' => $this->getBrowserFamilyDescription(),
	 		'browser' => $this->getBrowser(),
			'browserIcon' => $this->getBrowserIcon(),
			'screen' => $this->getScreenType(),
			'resolution' => $this->getResolution(),
			'screenIcon' => $this->getScreenTypeIcon(),
			'plugins' => $this->getPlugins(),
			'pluginIcons' => $this->getPluginIcons(),
			'lastActionDateTime' => $this->getDateTimeLastAction(),
			'isVisitorGoalConverted' => $this->isVisitorGoalConverted(),
			'goalIcon' => $this->getGoalIcon(),
   			'goalType' => $this->getGoalType(),
			'goalName' => $this->getGoalName(),
   			'goalRevenue' => $this->getGoalRevenue(),
			'goalUrl' => $this->getGoalUrl(),
   			'goalTimePretty' => $this->getGoalTimePretty()
		);
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

	function getRefererType()
	{
	    return Piwik_getRefererTypeFromShortName($this->details['referer_type']);
	}

	function getRefererTypeName()
	{
		return Piwik_getRefererTypeLabel($this->details['referer_type']);
	}

	function getKeywords()
	{
		return $this->details['referer_keyword'];
	}

	function getRefererUrl()
	{
		return $this->details['referer_url'];
	}

	function getRefererName()
	{
		return $this->details['referer_name'];
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

	function getGoalType()
	{
		if(isset($this->details['goal_match_attribute'])){
			return ucfirst($this->details['goal_match_attribute']);
		}
		return false;
	}

	function getGoalIcon()
	{
		if(isset($this->details['goal_match_attribute'])){
			$goalicon = '';
			switch ($this->details['goal_match_attribute']) {
				case "file":
					$goalicon = 'plugins/Live/templates/images/download.png';
					break;
				case 'external_website':
					$goalicon = 'plugins/Live/templates/images/outboundlink.png';
					break;
				case 'url':
				case 'manually':
				default:
					$goalicon = 'themes/default/images/goal.png';
					break;
			}
			return $goalicon;
		}
		return false;
	}
	
	function getGoalName()
	{
		if(isset($this->details['goal_name'])){
			return $this->details['goal_name'];
		}
		return false;
	}
	
	function getGoalRevenue()
	{
		if(isset($this->details['goal_revenue'])){
			return $this->details['goal_revenue'];
		}
		return false;
	}

	function getGoalUrl()
	{
		if(isset($this->details['goal_idaction_url'])){
			return $this->details['goal_idaction_url'];
		}
		return false;
	}

	function getGoalTimePretty()
	{
		if(isset($this->details['goal_server_time'])){
			return $this->details['goal_server_time'];
		}
		return false;
	}
}
