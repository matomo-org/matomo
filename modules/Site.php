<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik
 */

/**
 * 
 * @package Piwik
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
		return new Piwik_Date($date);
	}
	
}

