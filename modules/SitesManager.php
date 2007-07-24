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

	/**
	 * The minimum access required for each public method of this class.
	 */
	protected $minimumAccessRequired = array(
		'getSites' 				=> 'view',
		'getSiteUrlsFromId' 	=> 'view',
		'getSitesId' 			=> 'view',
		'getAllSitesId'			=> 'superuser',
		'replaceSiteUrls' 		=> 'admin',
		'siteExists'			=> 'view',
	);

	/**
	 * Returns the list of sites available for the authentificated user.
	 * The sites returned are the ones for which the user has a 'view' or a 'admin' access.
	 * If the user is the Super User he has access to the full list of websites.
	 * 
	 * @return array for each site, an array of information (idsite, name, main_url, etc.)
	 */
	static public function getSites()
	{
		$db = Zend_Registry::get('db');
		$sites = $db->fetchAll("SELECT * FROM ".Piwik::prefixTable("site"));
		return $sites;
	}
	
	/**
	 * Returns the website information : name, main_url
	 * 
	 * @exception if the site ID doesn't exist
	 * @return array
	 */
	static public function getSiteFromId( $idSite )
	{
		self::checkIdSite($idSite);
		$db = Zend_Registry::get('db');
		$site = $db->fetchRow("SELECT * FROM ".Piwik::prefixTable("site")." WHERE idsite = ?", $idSite);
		return $site;
	}
	
	/**
	 * Returns the list of alias URLs registered for the given idSite
	 * 
	 * @return array list of URLs
	 */
	static public function getSiteUrlsFromId( $idsite )
	{
		$db = Zend_Registry::get('db');
		$urls = $db->fetchCol("SELECT url 
								FROM ".Piwik::prefixTable("site_url"). " 
								WHERE idsite = ?", $idsite);
		return $urls;
	}
	
	/**
	 * Returns the list of idSites available for the authentificated user.
	 * 
	 * @see getSites()
	 * @return array the list of websites ID
	 */
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
	
	/**
	 * Returns the list of all the websites ID registered
	 * 
	 * @return array the list of websites ID
	 */
	static public function getAllSitesId()
	{
		$db = Zend_Registry::get('db');
		$idSites = $db->fetchCol("SELECT idsite FROM ".Piwik::prefixTable('site'));
		return $idSites;
	}
	
	/**
	 * Returns the list of websites ID with the 'admin' access for the current user
	 * 
	 * @return array list of websites ID
	 */
	static public function getSitesIdWithAdminAccess()
	{
		return array();
	}
	
	/**
	 * Returns true if the idSite given do exist in the database
	 * 
	 * @return bool true if the websites exists
	 */
	static public function siteExists( $idsite )
	{
		$sites = self::getSitesId();
		return is_int($idsite) && in_array($idsite, $sites);
	}
	
	/**
	 * Add a website to the database.
	 * 
	 * The website is defined by a name and an array of URLs.
	 * The name must not be empty.
	 * The URLs array must contain at least one URL called the 'main_url' ; 
	 * if several URLs are provided in the array, they will be recorded as Alias URLs for
	 * this website.
	 * 
	 * @return int the website ID created
	 */
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
		
		return $idSite;
	}
	
	/**
	 * Add a list of alias Urls to the given idSite
	 * 
	 * If some URLs given in parameter are already recorded as alias URLs for this website,
	 * they won't be duplicated. The 'main_url' of the website won't be affected by this method.
	 * 
	 * @return int the number of inserted URLs
	 */
	static public function addSiteUrls( $idsite,  $aUrls)
	{
		$urls = self::getSiteUrlsFromId($idsite);
		$toInsert = array_diff($aUrls, $urls);
		self::insertSiteUrls($idsite, $toInsert);
		
		return count($toInsert);
	}
	
	/**
	 * Replaces the list of alias URLs for the given idSite
	 * 
	 * The 'main_url' of the website won't be affected by this method. It only affects
	 * the alias URLs.
	 * 
	 * @param int the website ID
	 * @param array the array of URLs
	 * 
	 * @return int the number of inserted URLs
	 */
	static public function replaceSiteUrls( $idsite,  $aUrls)
	{	
		self::checkIdsite($idsite);
		$aUrls = self::cleanParameterUrls($aUrls);
		self::checkUrls($aUrls);
		
		self::deleteSiteUrls($idsite);
		$insertedUrls = self::addSiteUrls($idsite, $aUrls);
		return $insertedUrls;
	}
	
	/**
	 * Insert the list of alias URLs for the website.
	 * The URLs must not exist already for this website!
	 */
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
	
	/**
	 * Delete all the alias URLs for the given idSite
	 */
	static private function deleteSiteUrls($idsite)
	{
		$db = Zend_Registry::get('db');
		$db->query("DELETE FROM ".Piwik::prefixTable("site_url") ." WHERE idsite = ?", $idsite);
	}
	
	/**
	 * Remove the final slash in the URLs if found
	 * 
	 * @return string the URL without the trailing slash
	 */
	static private function removeTrailingSlash($url)
	{
		// if there is a final slash, we take the URL without this slash (expected URL format)
		if($url[strlen($url)-1] == '/')
		{
			$url = substr($url,0,strlen($url)-1);
		}
		return $url;
	}
	
	/**
	 * Tests if the URL is a valid URL
	 * 
	 * @return bool
	 */
	static private function isValidUrl( $url )
	{
		return ereg('^http[s]?://[A-Za-z0-9\/_.-]', $url);
	}
	
	/**
	 * Check that the website name has a correct format.
	 * 
	 * @exception 
	 */
	static private function checkName($name)
	{
		if(empty($name))
		{
			throw new Exception("The site name can't be empty.");
		}
	}

	/**
	 * Check that the array of URLs are valid URLs
	 * 
	 * @exception 
	 */
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
	
	/**
	 * Clean the parameter URLs:
	 * - if the parameter is a string make it an array
	 * - remove the trailing slashes if found
	 * 
	 * @return array the array of cleaned URLs
	 */
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

	/**
	 * Check that the website ID has a correct type (integer greater than zero)
	 * and that it matches an existing website in the database.
	 * 
	 * @exception
	 */
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
	
}
?>
