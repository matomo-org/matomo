<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 */

/**
 * 
 * @package Piwik
 */
class Piwik_Site
{
	protected $id = null;

	function __construct($idsite)
	{
		$this->id = $idsite;
		
		$this->info = Piwik_SitesManager_API::getSiteFromId($idsite);
	}
	
	function getId()
	{
		return $this->id;
	}
	
	function getCreationDate()
	{
		$date = $this->info['ts_created'];
		return new Piwik_Date($date);
	}
	
}

