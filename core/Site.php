<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * 
 * @package Piwik
 */
class Piwik_Site
{
	protected $id = null;
	
	public static $infoSites = array();

	function __construct($idsite)
	{
		$this->id = $idsite;
		if(!isset(self::$infoSites[$this->id]))
		{
			self::$infoSites[$this->id] = Piwik_SitesManager_API::getInstance()->getSiteFromId($idsite);
		}
	}
	
	/**
	 * Sets the cached Site data with an array that associates site IDs with
	 * individual site data.
	 *
	 * @param array $sites The array of sites data. Indexed by site ID.
	 */
	public static function setSites($sites)
	{
		self::$infoSites = $sites;
	}
	
	/**
	 * Sets the cached Site data with a non-associated array of site data.
	 *
	 * @param array $sites The array of sites data.
	 */
	public static function setSitesFromArray($sites)
	{
		$sitesById = array();
		foreach($sites as $site)
		{
			$sitesById[$site['idsite']] = $site;
		}
		self::setSites($sitesById);
	}
	
	function __toString()
	{
		return "site id=".$this->getId().",
				 name=".$this->getName() .",
				 url = ". $this->getMainUrl() .",
				 IPs excluded = ".$this->getExcludedIps().",
				 timezone = ".$this->getTimezone().",
				 currency = ".$this->getCurrency().",
				 creation date = ".$this->getCreationDate();
	}
	
	function getName()
	{
		return $this->get('name');
	}
	
	function getMainUrl()
	{
		return $this->get('main_url');
	}
	
	function getId()
	{
		return $this->id;
	}
	
	protected function get( $name)
	{
		if(!isset(self::$infoSites[$this->id][$name]))
		{
			throw new Exception('The requested website id = '.(int)$this->id.' couldn\'t be found');
		}
		return self::$infoSites[$this->id][$name];
	}

	function getCreationDate()
	{
		$date = $this->get('ts_created');
		return Piwik_Date::factory($date);
	}

	function getTimezone()
	{
		return $this->get('timezone');
	}
	
	function getCurrency()
	{
		return $this->get('currency');
	}
	
	function getExcludedIps()
	{
		return $this->get('excluded_ips');
	}

	function getExcludedQueryParameters()
	{
		return $this->get('excluded_parameters');
	}
	function isEcommerceEnabled()
	{
		return $this->get('ecommerce') == 1;
	}
	
	/**
	 * @param string comma separated idSite list
	 * @return array of valid integer
	 */
	static public function getIdSitesFromIdSitesString( $string )
	{
		if(is_array($string))
		{
			return $string;
		}
		$ids = explode(',', $string);
		$validIds = array();
		foreach($ids as $id)
		{
			$id = trim($id);
			if(!empty($id) && is_numeric($id))
			{
			    $validIds[] = $id;
			}
		}
		return $validIds;
	}
	
	static public function clearCache()
	{
		self::$infoSites = array();
	}
}
