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
	}
	function getId()
	{
		return $this->id;
	}
}

