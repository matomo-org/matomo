<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Site.php 561 2008-07-21 00:00:35Z matt $
 * 
 * @package Piwik_Site
 */

/**
 * 
 * @package Piwik_Site
 */
class Piwik_Site
{
	protected $id = null;
	
	protected static $infoSites = array();

	function __construct($idsite)
	{
		$this->id = $idsite;
		
		if(!isset(self::$infoSites[$this->id]))
		{
			self::$infoSites[$this->id] = Piwik_SitesManager_API::getSiteFromId($idsite);
		}
	}
	function getName()
	{
		return self::$infoSites[$this->id]['name'];
	}
	function getMainUrl()
	{
		return self::$infoSites[$this->id]['main_url'];
	}
	
	function getId()
	{
		return $this->id;
	}
	
	function getCreationDate()
	{
		$date = self::$infoSites[$this->id]['ts_created'];
		return Piwik_Date::factory($date);
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
}

