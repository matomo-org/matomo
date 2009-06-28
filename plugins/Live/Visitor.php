<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Live
 */

require_once "Referers/functions.php";
require_once "UserCountry/functions.php";
require_once "UserSettings/functions.php";
require_once "Provider/functions.php";

class Piwik_Live_Visitor
{
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
		);
	}
	
	function getServerDate()
	{
		return $this->details['visit_server_date'];
	}
	
	function getIp()
	{
		if(isset($this->details['location_ip']))
		{
			return long2ip($this->details['location_ip']);
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
		$map = array(
			Piwik_Common::REFERER_TYPE_SEARCH_ENGINE => 'searchEngine',
			Piwik_Common::REFERER_TYPE_WEBSITE => 'website',
			Piwik_Common::REFERER_TYPE_DIRECT_ENTRY => 'directEntry',
			Piwik_Common::REFERER_TYPE_CAMPAIGN => 'campaign',
		);
		if(isset($map[$this->details['referer_type']]))
		{
			return $map[$this->details['referer_type']];
		}
		return $map[Piwik_Common::REFERER_TYPE_DIRECT_ENTRY];
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
		if($this->getRefererType() == 'searchEngine'
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
	 		'config_director',
	 		'config_realplayer',
	 		'config_windowsmedia'
		);
		$return = array();
		foreach($plugins as $plugin)
		{
			if($this->details[$plugin] == 1) 
			{
				$pluginShortName = substr($plugin, 7);
				$return[] = $pluginShortName; 
			}
		}
		return implode(", ", $return);
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
}
