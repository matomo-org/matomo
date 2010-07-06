<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
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
			throw new Exception('Requested website was not loaded. ');
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
	
	/**
	 * @param string comma separated idSite list
	 * @return array of valid integer
	 */
	static public function getIdSitesFromIdSitesString( $string )
	{
		$ids = explode(',', $string);
		$validIds = array();
		foreach($ids as $id)
		{
			$id = trim($id);
			$validIds[] = $id;
		}
		return $validIds;
	}
	
	static public function clearCache()
	{
		self::$infoSites = array();
	}
}
