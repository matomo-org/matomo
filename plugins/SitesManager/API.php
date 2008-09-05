<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_SitesManager
 */

/**
 * 
 * @package Piwik_SitesManager
 */
class Piwik_SitesManager_API extends Piwik_Apiable
{
	static private $instance = null;
	static public function getInstance()
	{
		if (self::$instance == null)
		{            
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	static public $methodsNotToPublish = array();
	
	/**
	 * Returns the javascript tag for the given idSite.
	 * This tag must be included on every page to be tracked by Piwik
	 *
	 * @param int $idSite
	 * @return string The Javascript tag ready to be included on the HTML pages
	 */
	static public function getJavascriptTag( $idSite, $piwikUrl = '', $actionName = '')
	{
		Piwik::checkUserHasViewAccess($idSite);
		
		$actionName = "'".addslashes(Piwik_Common::sanitizeInputValues($actionName))."'";
		if(empty($piwikUrl))
		{
			$piwikUrl = Piwik_Url::getCurrentUrlWithoutFileName();
		}
		$piwikUrl = addslashes(Piwik_Common::sanitizeInputValues($piwikUrl));
		
		$htmlEncoded = Piwik::getJavascriptCode($idSite, $piwikUrl, $actionName);
		$htmlEncoded = str_replace(array('<br>','<br />','<br/>'), '', $htmlEncoded);
		return html_entity_decode($htmlEncoded);
	}
	
	/**
	 * Returns the website information : name, main_url
	 * 
	 * @exception if the site ID doesn't exist or the user doesn't have access to it
	 * @return array
	 */
	static public function getSiteFromId( $idSite )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$site = Zend_Registry::get('db')->fetchRow("SELECT * FROM ".Piwik::prefixTable("site")." WHERE idsite = ?", $idSite);
		return $site;
	}
	
	/**
	 * Returns the list of alias URLs registered for the given idSite.
	 * The website ID must be valid when calling this method!
	 * 
	 * @return array list of alias URLs
	 */
	static private function getAliasSiteUrlsFromId( $idsite )
	{
		$db = Zend_Registry::get('db');
		$urls = $db->fetchCol("SELECT url 
								FROM ".Piwik::prefixTable("site_url"). " 
								WHERE idsite = ?", $idsite);
		return $urls;
	}
	
	/**
	 * Returns the list of all URLs registered for the given idSite (main_url + alias URLs).
	 * 
	 * @exception if the website ID doesn't exist or the user doesn't have access to it
	 * @return array list of URLs
	 */
	static public function getSiteUrlsFromId( $idSite )
	{
		Piwik::checkUserHasViewAccess($idSite);
		$site = self::getSiteFromId($idSite);
		$urls = self::getAliasSiteUrlsFromId($idSite);
		return array_merge(array($site['main_url']), $urls);
	}
	
	/**
	 * Returns the list of all the websites ID registered
	 * 
	 * @return array the list of websites ID
	 */
	static public function getAllSitesId()
	{
		Piwik::checkUserIsSuperUser();
		return Zend_Registry::get('db')->fetchCol("SELECT idsite FROM ".Piwik::prefixTable('site'));
	}
	
	
	/**
	 * Returns the list of websites with the 'admin' access for the current user.
	 * For the superUser it returns all the websites in the database.
	 * 
	 * @return array for each site, an array of information (idsite, name, main_url, etc.)
	 */
	static public function getSitesWithAdminAccess()
	{
		$sitesId = self::getSitesIdWithAdminAccess();
		return self::getSitesFromIds($sitesId);
	}
	
	/**
	 * Returns the list of websites with the 'view' access for the current user.
	 * For the superUser it doesn't return any result because the superUser has admin access on all the websites (use getSitesWithAtLeastViewAccess() instead).
	 * 
	 * @return array for each site, an array of information (idsite, name, main_url, etc.)
	 */
	static public function getSitesWithViewAccess()
	{
		$sitesId = self::getSitesIdWithViewAccess();
		return self::getSitesFromIds($sitesId);
	}
	
	/**
	 * Returns the list of websites with the 'view' or 'admin' access for the current user.
	 * For the superUser it returns all the websites in the database.
	 * 
	 * @return array array for each site, an array of information (idsite, name, main_url, etc.)
	 */
	static public function getSitesWithAtLeastViewAccess()
	{
		$sitesId = self::getSitesIdWithAtLeastViewAccess();
		return self::getSitesFromIds($sitesId);
	}
	
	/**
	 * Returns the list of websites ID with the 'admin' access for the current user.
	 * For the superUser it returns all the websites in the database.
	 * 
	 * @return array list of websites ID
	 */
	static public function getSitesIdWithAdminAccess()
	{
		$sitesId = Zend_Registry::get('access')->getSitesIdWithAdminAccess();
		return $sitesId;
	}
	
	/**
	 * Returns the list of websites ID with the 'view' access for the current user.
	 * For the superUser it doesn't return any result because the superUser has admin access on all the websites (use getSitesIdWithAtLeastViewAccess() instead).
	 * 
	 * @return array list of websites ID
	 */
	static public function getSitesIdWithViewAccess()
	{
		return Zend_Registry::get('access')->getSitesIdWithViewAccess();
	}
	
	/**
	 * Returns the list of websites ID with the 'view' or 'admin' access for the current user.
	 * For the superUser it returns all the websites in the database.
	 * 
	 * @return array list of websites ID
	 */
	static public function getSitesIdWithAtLeastViewAccess()
	{
		return Zend_Registry::get('access')->getSitesIdWithAtLeastViewAccess();
	}

	/**
	 * Returns the list of websites from the ID array in parameters.
	 * The user access is not checked in this method so the ID have to be accessible by the user!
	 * 
	 * @param array list of website ID
	 */
	static private function getSitesFromIds( $idSites )
	{
		if(count($idSites) === 0)
		{
			return array();
		}
		$db = Zend_Registry::get('db');
		$sites = $db->fetchAll("SELECT * 
								FROM ".Piwik::prefixTable("site")." 
								WHERE idsite IN (".implode(", ", $idSites).")
								ORDER BY idsite ASC");
		return $sites;
	}
	
	/**
	 * Add a website in the database.
	 * 
	 * The website is defined by a name and an array of URLs.
	 * The name must not be empty.
	 * The URLs array must contain at least one URL called the 'main_url' ; 
	 * if several URLs are provided in the array, they will be recorded as Alias URLs for
	 * this website.
	 * 
	 * Requires Super User access.
	 * 
	 * @return int the website ID created
	 */
	static public function addSite( $siteName, $urls )
	{
		Piwik::checkUserIsSuperUser();
		
		self::checkName($siteName);
		$urls = self::cleanParameterUrls($urls);
		self::checkUrls($urls);
		self::checkAtLeastOneUrl($urls);
		
		$db = Zend_Registry::get('db');
		
		$url = $urls[0];
		$urls = array_slice($urls, 1);
		
		$db->insert(Piwik::prefixTable("site"), array(
									'name' => $siteName,
									'main_url' => $url,
									)
								);
									
		$idSite = $db->lastInsertId();
		
		self::insertSiteUrls($idSite, $urls);
		
		// we reload the access list which doesn't yet take in consideration this new website
		Zend_Registry::get('access')->loadAccess();

		return (int)$idSite;
	}
	
	/**
	 * Delete a website from the database, given its Id.
	 * 
	 * Requires Super User access. 
	 *
	 * @param int $idSite
	 */
	static public function deleteSite( $idSite )
	{
		Piwik::checkUserIsSuperUser();
		
		$nbSites = count(Piwik_SitesManager_API::getAllSitesId());

		if($nbSites == 1)
		{
			throw new Exception(Piwik_TranslateException("SitesManager_ExceptionDeleteSite"));
		}
		
		$db = Zend_Registry::get('db');
		
		$db->query("DELETE FROM ".Piwik::prefixTable("site")." 
					WHERE idsite = ?", $idSite);
		
		$db->query("DELETE FROM ".Piwik::prefixTable("site_url")." 
					WHERE idsite = ?", $idSite);
		
		$db->query("DELETE FROM ".Piwik::prefixTable("access")." 
					WHERE idsite = ?", $idSite);
		
		// TODO we should also delete all the data relative to this website...
		// post an event here that will be catched by the core and plugins to clean the data
	}
	
	
	/**
	 * Checks that the array has at least one element
	 * 
	 * @exception if the parameter is not an array or if array empty 
	 */
	static private function checkAtLeastOneUrl( $urls )
	{
		if(!is_array($urls)
			|| count($urls) == 0)
		{
			throw new Exception(Piwik_TranslateException("SitesManager_ExceptionNoUrl"));
		}
	}

	/**
	 * Add a list of alias Urls to the given idSite
	 * 
	 * If some URLs given in parameter are already recorded as alias URLs for this website,
	 * they won't be duplicated. The 'main_url' of the website won't be affected by this method.
	 * 
	 * @return int the number of inserted URLs
	 */
	static public function addSiteAliasUrls( $idSite,  $urls)
	{
		Piwik::checkUserHasAdminAccess( $idSite );
		
		$urls = self::cleanParameterUrls($urls);
		self::checkUrls($urls);
		
		$urlsInit = self::getSiteUrlsFromId($idSite);
		$toInsert = array_diff($urls, $urlsInit);
		self::insertSiteUrls($idSite, $toInsert);
		
		return count($toInsert);
	}
	
	/**
	 * Replaces the list of URLs (main_url and alias URLs) for the given idSite. 
	 *   
	 * @param int the website ID
	 * @param array the array of URLs; The first URL is the main_url and is mandatory. 
	 * 
	 * @exception if the website ID doesn't exist or the user doesn't have access to it
	 * @exception if there is no URL
	 * @exception if any of the URLs has not a correct format
	 * 
	 * @return int the number of inserted URLs
	 */
	static public function replaceSiteUrls( $idSite,  $urls)
	{
		Piwik::checkUserHasAdminAccess($idSite);
		
		$urls = self::cleanParameterUrls($urls);
		self::checkUrls($urls);
		self::checkAtLeastOneUrl($urls);
		
		$site = self::getSiteFromId($idSite);
		
		$site['main_url'] = $urls[0];
		self::updateSite($site['idsite'], $site['name'], $site['main_url']);
		
		$urls = array_slice($urls,1);
		self::deleteSiteAliasUrls($idSite);
		
		$insertedUrls = self::addSiteAliasUrls($idSite, $urls);
		
		// we have updated the main_url at least, and maybe some alias URLs
		return 1 + $insertedUrls;
	}
	
	
	/**
	 * Update an existing website.
	 * If only one URL is specified then only the main url will be updated.
	 * If several URLs are specified, both the main URL and the alias URLs will be updated.
	 * 
	 * @param int website ID defining the website to edit
	 * @param string website name
	 * @param string|array the website URLs
	 * 
	 * @exception if any of the parameter is not correct
	 * 
	 * @return bool true on success
	 */
	static public function updateSite( $idSite, $siteName, $urls = null)
	{
		Piwik::checkUserHasAdminAccess($idSite);

		self::checkName($siteName);
		
		// SQL fields to update
		$bind = array();
		
		if(!is_null($urls))
		{
			$urls = self::cleanParameterUrls($urls);
			self::checkUrls($urls);
			self::checkAtLeastOneUrl($urls);
			$url = $urls[0];
			
			$bind['main_url'] = $url;
		}
		
		$bind['name'] = $siteName;
		
		$db = Zend_Registry::get('db');
		
		
		$db->update(Piwik::prefixTable("site"), 
							$bind,
							"idsite = $idSite"
								);
		// if there are more than 1 url for this website we need to set also the alias URLs
		// we use the replaceSiteUrls function ; it is not great because it will update the 
		// same row we have just updated... but it is better than duplicating the logic
		if(count($urls) > 1)
		{
			self::replaceSiteUrls($idSite, $urls);
		}
		
	}
	
	/**
	 * Insert the list of alias URLs for the website.
	 * The URLs must not exist already for this website!
	 */
	static private function insertSiteUrls($idSite, $urls)
	{
		if(count($urls) != 0)
		{
			$db = Zend_Registry::get('db');
			foreach($urls as $url)
			{
				$db->insert(Piwik::prefixTable("site_url"), array(
										'idsite' => $idSite,
										'url' => $url
										)
									);
			}
		}
	}
	
	/**
	 * Delete all the alias URLs for the given idSite.
	 */
	static private function deleteSiteAliasUrls($idsite)
	{
		$db = Zend_Registry::get('db');
		$db->query("DELETE FROM ".Piwik::prefixTable("site_url") ." 
					WHERE idsite = ?", $idsite);
	}
	
	/**
	 * Remove the final slash in the URLs if found
	 * 
	 * @return string the URL without the trailing slash
	 */
	static private function removeTrailingSlash($url)
	{
		// if there is a final slash, we take the URL without this slash (expected URL format)
		if(strlen($url) > 5
			&& $url[strlen($url)-1] == '/')
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
		return ereg('^http[s]?://([A-Za-z0-9\/_%~.:-])*$', $url);
	}
	
	/**
	 * Check that the website name has a correct format.
	 * 
	 * @exception if the website name is empty
	 */
	static private function checkName($siteName)
	{
		if(empty($siteName))
		{
			throw new Exception(Piwik_TranslateException("SitesManager_ExceptionEmptyName"));
		}
	}

	/**
	 * Check that the array of URLs are valid URLs
	 * 
	 * @exception if any of the urls is not valid
	 * @param array
	 */
	static private function checkUrls($urls)
	{
		foreach($urls as $url)
		{			
			if(!self::isValidUrl($url))
			{
				throw new Exception(sprintf(Piwik_TranslateException("SitesManager_ExceptionInvalidUrl"),$url));
			}
		}
	}
	
	/**
	 * Clean the parameter URLs:
	 * - if the parameter is a string make it an array
	 * - remove the trailing slashes if found
	 * 
	 * @param string|array urls
	 * @return array the array of cleaned URLs
	 */
	static private function cleanParameterUrls( $urls )
	{
		if(!is_array($urls))
		{
			$urls = array($urls);
		}
		foreach($urls as &$url)
		{
			$url = self::removeTrailingSlash($url);
		}
		$urls = array_unique($urls);
		
		return $urls;
	}
}

