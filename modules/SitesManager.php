<?php

class Piwik_SitesManager extends Piwik_APIable
{
	static private $instance = null;
	protected function __construct()
	{
		parent::__construct();
	}
	
	static public function getInstance()
	{
		if (self::$instance == null)
		{            
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	protected $roles = array(
		'getSites' 				=> 'anonymous',
		'getSitesId' 			=> 'anonymous',
		'getSiteUrlsFromId' 	=> 'view',
		'replaceSiteUrls' 		=> 'admin',
		'siteExists'			=> 'anonymous',
	);
	
	static private function checkName($name)
	{
		if(empty($name))
		{
			throw new Exception("The site name can't be empty.");
		}
	}

	static private function checkUrls($aUrls)
	{
		foreach($aUrls as $url)
		{			
			if(!self::isValidUrl($url))
			{
				throw new Exception("The url '$url' is not a valid URL.");
			}
		}
	}
	
	static private function cleanParameterUrls( $aUrls )
	{
		if(!is_array($aUrls))
		{
			$aUrls = array($aUrls);
		}
		foreach($aUrls as &$url)
		{
			$url = self::removeTrailingSlash($url);
		}
		return $aUrls;
	}

	static private function checkIdsite($idsite)
	{
		if(!is_int($idsite)
			|| $idsite <= 0)
		{
			throw new Exception("Idsite must be an integer > 0.");
		}
		
		if(!self::siteExists($idsite))
		{
			throw new Exception("The site with Idsite = $idsite doesn't exist.");
		}
	}
	
	static public function addSite( $name, $aUrls )
	{
		self::checkName($name);
		$aUrls = self::cleanParameterUrls($aUrls);
		self::checkUrls($aUrls);
		
		if(count($aUrls) == 0)
		{
			throw new Exception("You must specify at least one URL for the site.");
		}
		
		$db = Zend_Registry::get('db');
		
		$url = $aUrls[0];
		$aUrls = array_slice($aUrls, 1);
		
		$db->insert(Piwik::prefixTable("site"), array(
									'name' => $name,
									'main_url' => $url,
									)
								);
									
		$idSite = $db->lastInsertId();
		
		self::insertSiteUrls($idSite, $aUrls);
	}
	
	static private function insertSiteUrls($idSite, $aUrls)
	{
		$db = Zend_Registry::get('db');
		foreach($aUrls as $url)
		{
			$db->insert(Piwik::prefixTable("site_url"), array(
									'idsite' => $idSite,
									'url' => $url
									)
								);
		}
	}
	
	static public function addSiteUrls( $idsite,  $aUrls)
	{
		$urls = self::getSiteUrlsFromId($idsite);
		$toInsert = array_diff($aUrls, $urls);
		self::insertSiteUrls($idsite, $toInsert);	
	}
	
	static public function replaceSiteUrls( $idsite,  $aUrls)
	{	
		self::checkIdsite($idsite);
		$aUrls = self::cleanParameterUrls($aUrls);
		self::checkUrls($aUrls);
		
		self::deleteSiteUrls($idsite);
		self::addSiteUrls($idsite, $aUrls);		
	}
	
	static private function deleteSiteUrls($idsite)
	{
		$db = Zend_Registry::get('db');
		$db->query("DELETE FROM ".Piwik::prefixTable("site_url") ." WHERE idsite = ?", $idsite);
	}
	
	static public function getSites()
	{
		$db = Zend_Registry::get('db');
		$sites = $db->fetchAll("SELECT * FROM ".Piwik::prefixTable("site"));
		return $sites;
	}
	
	static public function getSiteUrlsFromId( $idsite )
	{
		$db = Zend_Registry::get('db');
		$urls = $db->fetchCol("SELECT url FROM ".Piwik::prefixTable("site_url"). " WHERE idsite = ?", $idsite);
		return $urls;
	}
	static public function getSitesId()
	{
		$sites = self::getSites();
		$aSitesId = array();
		foreach($sites as $site)
		{
			$aSitesId[] = $site["idsite"];
		}
		return $aSitesId;
	}
	
	static public function getAllSitesId()
	{
		$db = Zend_Registry::get('db');
		$idSites = $db->fetchCol("SELECT idsite FROM ".Piwik::prefixTable('site'));
		return $idSites;
	}
	
	static public function getSitesIdWithAdminAccess()
	{
		return array();
	}
	
	static public function siteExists( $idsite )
	{
		$sites = self::getSitesId();
		return in_array($idsite, $sites);
	}
	
	static private function removeTrailingSlash($url)
	{
		// if there is a final slash, we take the URL without this slash (expected URL format)
		if($url[strlen($url)-1] == '/')
		{
			$url = substr($url,0,strlen($url)-1);
		}
		return $url;
	}
	static private function isValidUrl( $url )
	{
		return ereg('^http[s]?://[A-Za-z0-9\/_.-]', $url);
	}
}
?>
