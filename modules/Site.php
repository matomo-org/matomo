<?php
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

