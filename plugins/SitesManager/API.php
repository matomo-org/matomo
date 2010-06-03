<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_SitesManager
 */

/**
 *
 * @package Piwik_SitesManager
 */
class Piwik_SitesManager_API 
{
	static private $instance = null;
	
	/**
	 * @return Piwik_SitesManager_API
	 */
	static public function getInstance()
	{
		if (self::$instance == null)
		{            
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	const OPTION_EXCLUDED_IPS_GLOBAL = 'SitesManager_ExcludedIpsGlobal';
	const OPTION_DEFAULT_TIMEZONE = 'SitesManager_DefaultTimezone';
	const OPTION_DEFAULT_CURRENCY = 'SitesManager_DefaultCurrency';
	const OPTION_EXCLUDED_QUERY_PARAMETERS_GLOBAL = 'SitesManager_ExcludedQueryParameters';
	
	/**
	 * Returns the javascript tag for the given idSite.
	 * This tag must be included on every page to be tracked by Piwik
	 *
	 * @param int $idSite
	 * @return string The Javascript tag ready to be included on the HTML pages
	 */
	public function getJavascriptTag( $idSite, $piwikUrl = '', $actionName = '')
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
	public function getSiteFromId( $idSite )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$site = Zend_Registry::get('db')->fetchRow("SELECT * FROM ".Piwik_Common::prefixTable("site")." WHERE idsite = ?", $idSite);
		return $site;
	}
	
	/**
	 * Returns the list of alias URLs registered for the given idSite.
	 * The website ID must be valid when calling this method!
	 * 
	 * @return array list of alias URLs
	 */
	private function getAliasSiteUrlsFromId( $idsite )
	{
		$db = Zend_Registry::get('db');
		$result = $db->fetchAll("SELECT url 
								FROM ".Piwik_Common::prefixTable("site_url"). " 
								WHERE idsite = ?", $idsite);
		$urls = array();
		foreach($result as $url)
		{
			$urls[] = $url['url'];
		}
		return $urls;
	}
	
	/**
	 * Returns the list of all URLs registered for the given idSite (main_url + alias URLs).
	 * 
	 * @exception if the website ID doesn't exist or the user doesn't have access to it
	 * @return array list of URLs
	 */
	public function getSiteUrlsFromId( $idSite )
	{
		Piwik::checkUserHasViewAccess($idSite);
		$site = $this->getSiteFromId($idSite);
		$urls = $this->getAliasSiteUrlsFromId($idSite);
		return array_merge(array($site['main_url']), $urls);
	}
	
	/**
	 * Returns the list of all the websites ID registered
	 * 
	 * @return array the list of websites ID
	 */
	public function getAllSitesId()
	{
		Piwik::checkUserIsSuperUser();
		$result = Piwik_FetchAll("SELECT idsite FROM ".Piwik_Common::prefixTable('site'));
		$idSites = array();
		foreach($result as $idSite)
		{
			$idSites[] = $idSite['idsite'];
		}
		return $idSites;
	}
	
	
	/**
	 * Returns the list of websites with the 'admin' access for the current user.
	 * For the superUser it returns all the websites in the database.
	 * 
	 * @return array for each site, an array of information (idsite, name, main_url, etc.)
	 */
	public function getSitesWithAdminAccess()
	{
		$sitesId = $this->getSitesIdWithAdminAccess();
		return $this->getSitesFromIds($sitesId);
	}
	
	/**
	 * Returns the list of websites with the 'view' access for the current user.
	 * For the superUser it doesn't return any result because the superUser has admin access on all the websites (use getSitesWithAtLeastViewAccess() instead).
	 * 
	 * @return array for each site, an array of information (idsite, name, main_url, etc.)
	 */
	public function getSitesWithViewAccess()
	{
		$sitesId = $this->getSitesIdWithViewAccess();
		return $this->getSitesFromIds($sitesId);
	}
	
	/**
	 * Returns the list of websites with the 'view' or 'admin' access for the current user.
	 * For the superUser it returns all the websites in the database.
	 * 
	 * @return array array for each site, an array of information (idsite, name, main_url, etc.)
	 */
	public function getSitesWithAtLeastViewAccess()
	{
		$sitesId = $this->getSitesIdWithAtLeastViewAccess();
		return $this->getSitesFromIds($sitesId);
	}
	
	/**
	 * Returns the list of websites ID with the 'admin' access for the current user.
	 * For the superUser it returns all the websites in the database.
	 * 
	 * @return array list of websites ID
	 */
	public function getSitesIdWithAdminAccess()
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
	public function getSitesIdWithViewAccess()
	{
		return Zend_Registry::get('access')->getSitesIdWithViewAccess();
	}
	
	/**
	 * Returns the list of websites ID with the 'view' or 'admin' access for the current user.
	 * For the superUser it returns all the websites in the database.
	 * 
	 * @return array list of websites ID
	 */
	public function getSitesIdWithAtLeastViewAccess()
	{
		return Zend_Registry::get('access')->getSitesIdWithAtLeastViewAccess();
	}

	/**
	 * Returns the list of websites from the ID array in parameters.
	 * The user access is not checked in this method so the ID have to be accessible by the user!
	 * 
	 * @param array list of website ID
	 */
	private function getSitesFromIds( $idSites )
	{
		if(count($idSites) === 0)
		{
			return array();
		}
		$db = Zend_Registry::get('db');
		$sites = $db->fetchAll("SELECT * 
								FROM ".Piwik_Common::prefixTable("site")." 
								WHERE idsite IN (".implode(", ", $idSites).")
								ORDER BY idsite ASC");
		return $sites;
	}

	/**
	 * Returns the list of websites ID associated with a URL.
	 *
	 * @param string $url
	 * @return array list of websites ID
	 */
	public function getSitesIdFromSiteUrl( $url )
	{
		$url = $this->removeTrailingSlash($url);

		if(Piwik::isUserIsSuperUser())
		{
			$ids = Zend_Registry::get('db')->fetchAll(
					'SELECT idsite FROM ' . Piwik_Common::prefixTable('site') . ' WHERE main_url = ? ' .
					'UNION SELECT idsite FROM ' . Piwik_Common::prefixTable('site_url') . ' WHERE url = ?', array($url, $url));
		}
		else
		{
			$login = Piwik::getCurrentUserLogin();
			$ids = Zend_Registry::get('db')->fetchAll(
					'SELECT idsite FROM ' . Piwik_Common::prefixTable('site') . ' WHERE main_url = ? ' .
					'AND idsite IN (' . Piwik_Access::getSqlAccessSite('idsite') . ') ' .
					'UNION SELECT idsite FROM ' . Piwik_Common::prefixTable('site_url') . ' WHERE url = ? ' .
					'AND idsite IN (' . Piwik_Access::getSqlAccessSite('idsite') . ')', array($url, $login, $url, $login));
		}

		return $ids;
	}

	/**
	 * Add a website.
	 * Requires Super User access.
	 * 
	 * The website is defined by a name and an array of URLs.
	 * @param string Site name
	 * @param array|string The URLs array must contain at least one URL called the 'main_url' ; 
	 * 					    if several URLs are provided in the array, they will be recorded 
	 * 						as Alias URLs for this website.
	 * @param string Comma separated list of IPs to exclude from the reports (allows wildcards)
	 * @param string Timezone string, eg. 'Europe/London'
	 * 
	 * @return int the website ID created
	 */
	public function addSite( $siteName, $urls, $excludedIps = null, $excludedQueryParameters = null, $timezone = null, $currency = null )
	{
		Piwik::checkUserIsSuperUser();
		
		$this->checkName($siteName);
		$urls = $this->cleanParameterUrls($urls);
		$this->checkUrls($urls);
		$this->checkAtLeastOneUrl($urls);
		$timezone = trim($timezone);
		
		if(empty($timezone))
		{
			$timezone = $this->getDefaultTimezone();
		}
		$this->checkValidTimezone($timezone);
		
		if(empty($currency))
		{
			$currency = $this->getDefaultCurrency();
		}
		$this->checkValidCurrency($currency);
		
		$db = Zend_Registry::get('db');
		
		$url = $urls[0];
		$urls = array_slice($urls, 1);
		
		$bind = array(	'name' => $siteName,
						'main_url' => $url,
						'ts_created' => Piwik_Date::now()->getDatetime()
		);
	
		$bind['excluded_ips'] = $this->checkAndReturnExcludedIps($excludedIps);
		$bind['excluded_parameters'] = $this->checkAndReturnExcludedQueryParameters($excludedQueryParameters);
		$bind['timezone'] = $timezone;
		$bind['currency'] = $currency;
		$db->insert(Piwik_Common::prefixTable("site"), $bind);
									
		$idSite = $db->lastInsertId();
		
		$this->insertSiteUrls($idSite, $urls);
		
		// we reload the access list which doesn't yet take in consideration this new website
		Zend_Registry::get('access')->reloadAccess();
		$this->postUpdateWebsite($idSite);

		return (int)$idSite;
	}
	
	private function postUpdateWebsite($idSite)
	{
		Piwik_Common::regenerateCacheWebsiteAttributes($idSite);	
	}
	
	/**
	 * Delete a website from the database, given its Id.
	 * 
	 * Requires Super User access. 
	 *
	 * @param int $idSite
	 */
	public function deleteSite( $idSite )
	{
		Piwik::checkUserIsSuperUser();
		
		$idSites = Piwik_SitesManager_API::getInstance()->getAllSitesId();
		if(!in_array($idSite, $idSites))
		{
			throw new Exception("website id = $idSite not found");
		}
		$nbSites = count($idSites);
		if($nbSites == 1)
		{
			throw new Exception(Piwik_TranslateException("SitesManager_ExceptionDeleteSite"));
		}
		
		$db = Zend_Registry::get('db');
		
		$db->query("DELETE FROM ".Piwik_Common::prefixTable("site")." 
					WHERE idsite = ?", $idSite);
		
		$db->query("DELETE FROM ".Piwik_Common::prefixTable("site_url")." 
					WHERE idsite = ?", $idSite);
		
		$db->query("DELETE FROM ".Piwik_Common::prefixTable("access")." 
					WHERE idsite = ?", $idSite);
		
		Piwik_Common::deleteCacheWebsiteAttributes($idSite);
	}
	
	
	/**
	 * Checks that the array has at least one element
	 * 
	 * @exception if the parameter is not an array or if array empty 
	 */
	private function checkAtLeastOneUrl( $urls )
	{
		if(!is_array($urls)
			|| count($urls) == 0)
		{
			throw new Exception(Piwik_TranslateException("SitesManager_ExceptionNoUrl"));
		}
	}

	private function checkValidTimezone($timezone)
	{
		$timezones = $this->getTimezonesList();
		foreach($timezones as $continent => $cities)
		{
			foreach($cities as $timezoneId => $city)
			{
				if($timezoneId == $timezone)
				{
					return true;
				}
			}
		}		
		throw new Exception(Piwik_TranslateException('SitesManager_ExceptionInvalidTimezone', array($timezone)));
	}
	
	private function checkValidCurrency($currency)
	{
		if(!in_array($currency, array_keys($this->getCurrencyList())))
		{			
			throw new Exception(Piwik_TranslateException('SitesManager_ExceptionInvalidCurrency', array($currency, "USD, EUR, etc.")));
		}
	}
	
	/**
	 * Checks that the submitted IPs (comma separated list) are valid
	 * Returns the cleaned up IPs
	 * @param $excludedIps 
	 * 
	 * @return array of IPs
	 */
	private function checkAndReturnExcludedIps($excludedIps)
	{
		$ips = explode(',', $excludedIps);
		$ips = array_map('trim', $ips);
		$ips = array_filter($ips, 'strlen');
		foreach($ips as $ip)
		{
			if(!$this->isValidIp($ip))
			{
				throw new Exception(Piwik_TranslateException('SitesManager_ExceptionInvalidIPFormat', array($ip, "1.2.3.4 or 1.2.3.*")));
			}
		}
		$ips = implode(',', $ips);
		return $ips;
	}
	/**
	 * Add a list of alias Urls to the given idSite
	 * 
	 * If some URLs given in parameter are already recorded as alias URLs for this website,
	 * they won't be duplicated. The 'main_url' of the website won't be affected by this method.
	 * 
	 * @return int the number of inserted URLs
	 */
	public function addSiteAliasUrls( $idSite,  $urls)
	{
		Piwik::checkUserHasAdminAccess( $idSite );
		
		$urls = $this->cleanParameterUrls($urls);
		$this->checkUrls($urls);
		
		$urlsInit = $this->getSiteUrlsFromId($idSite);
		$toInsert = array_diff($urls, $urlsInit);
		$this->insertSiteUrls($idSite, $toInsert);
		$this->postUpdateWebsite($idSite);
		
		return count($toInsert);
	}
	
	/**
	 * Sets IPs to be excluded from all websites. IPs can contain wildcards.
	 * Will also apply to websites created in the future.
	 * 
	 * @param string Comma separated list of IPs to exclude from being tracked (allows wildcards)
	 * @return bool
	 */
	public function setGlobalExcludedIps($excludedIps)
	{
		Piwik::checkUserIsSuperUser();
		$excludedIps = $this->checkAndReturnExcludedIps($excludedIps);
		Piwik_SetOption(self::OPTION_EXCLUDED_IPS_GLOBAL, $excludedIps);
		Piwik_Common::deleteAllCache();
		return true;
	}
	
	/**
	 * Returns the list of URL query parameters that are excluded from all websites 
	 * 
	 * @return string Comma separated list of URL parameters
	 */
	public function getExcludedQueryParametersGlobal()
	{
		Piwik::checkUserHasSomeAdminAccess();
		return Piwik_GetOption(self::OPTION_EXCLUDED_QUERY_PARAMETERS_GLOBAL);
	}
	
	/**
	 * Sets list of URL query parameters to be excluded on all websites.
	 * Will also apply to websites created in the future.
	 * 
	 * @param string Comma separated list of URL query parameters to exclude from URLs
	 * @return bool
	 */
	public function setGlobalExcludedQueryParameters($excludedQueryParameters)
	{
		Piwik::checkUserIsSuperUser();
		$excludedQueryParameters = $this->checkAndReturnExcludedQueryParameters($excludedQueryParameters);
		Piwik_SetOption(self::OPTION_EXCLUDED_QUERY_PARAMETERS_GLOBAL, $excludedQueryParameters);
		Piwik_Common::deleteAllCache();
		return true;
	}
	
	/**
	 * Returns the list of IPs that are excluded from all websites 
	 * 
	 * @return string Comma separated list of IPs
	 */
	public function getExcludedIpsGlobal()
	{
		Piwik::checkUserHasSomeAdminAccess();
		return Piwik_GetOption(self::OPTION_EXCLUDED_IPS_GLOBAL);
	}
	
	/**
	 * Returns the default currency that will be set when creating a website through the API.
	 * 
	 * @return string Currency ID eg. 'USD'
	 */
	public function getDefaultCurrency()
	{
		Piwik::checkUserHasSomeAdminAccess();
		$defaultCurrency = Piwik_GetOption(self::OPTION_DEFAULT_CURRENCY);
		if($defaultCurrency)
		{
			return $defaultCurrency;
		}
		return 'USD';
	}
	
	/**
	 * Sets the default currency that will be used when creating websites
	 * 
	 * @param $defaultCurrency string eg. 'USD'
	 * @return bool
	 */
	public function setDefaultCurrency($defaultCurrency)
	{
		Piwik::checkUserIsSuperUser();
		$this->checkValidCurrency($defaultCurrency);
		Piwik_SetOption(self::OPTION_DEFAULT_CURRENCY, $defaultCurrency);
		return true;
	}
	
	/**
	 * Returns the default timezone that will be set when creating a website through the API.
	 * Via the UI, if the default timezone is not UTC, it will be pre-selected in the drop down
	 * 
	 * @return string Timezone eg. UTC+7 or Europe/Paris
	 */
	public function getDefaultTimezone()
	{
		$defaultTimezone = Piwik_GetOption(self::OPTION_DEFAULT_TIMEZONE);
		if($defaultTimezone)
		{
			return $defaultTimezone;
		}
		return 'UTC';
	}
	
	/**
	 * Sets the default timezone that will be used when creating websites
	 * 
	 * @param $defaultTimezone string eg. Europe/Paris or UTC+8
	 * @return bool
	 */
	public function setDefaultTimezone($defaultTimezone)
	{
		Piwik::checkUserIsSuperUser();
		$this->checkValidTimezone($defaultTimezone);
		Piwik_SetOption(self::OPTION_DEFAULT_TIMEZONE, $defaultTimezone);
		return true;
	}
	
	/**
	 * Update an existing website.
	 * If only one URL is specified then only the main url will be updated.
	 * If several URLs are specified, both the main URL and the alias URLs will be updated.
	 * 
	 * @param int website ID defining the website to edit
	 * @param string website name
	 * @param string|array the website URLs
	 * @param string Comma separated list of IPs to exclude from being tracked (allows wildcards)
	 * @param string Timezone
	 * 
	 * @exception if any of the parameter is not correct
	 * 
	 * @return bool true on success
	 */
	public function updateSite( $idSite, $siteName, $urls = null, $excludedIps = null, $excludedQueryParameters = null, $timezone = null, $currency = null)
	{
		Piwik::checkUserHasAdminAccess($idSite);

		$this->checkName($siteName);
		
		// SQL fields to update
		$bind = array();
		
		if(!is_null($urls))
		{
			$urls = $this->cleanParameterUrls($urls);
			$this->checkUrls($urls);
			$this->checkAtLeastOneUrl($urls);
			$url = $urls[0];
			
			$bind['main_url'] = $url;
		}

		if(!is_null($currency))
		{
			$currency = trim($currency);
			$this->checkValidCurrency($currency);
			$bind['currency'] = $currency;
		}
		if(!is_null($timezone))
		{
			$timezone = trim($timezone);
			$this->checkValidTimezone($timezone);
			$bind['timezone'] = $timezone;
		}
		
		$bind['excluded_ips'] = $this->checkAndReturnExcludedIps($excludedIps);
		$bind['excluded_parameters'] = $this->checkAndReturnExcludedQueryParameters($excludedQueryParameters);
		$bind['name'] = $siteName;
		$db = Zend_Registry::get('db');
		$db->update(Piwik_Common::prefixTable("site"), 
							$bind,
							"idsite = $idSite"
								);
								
		// we now update the main + alias URLs
		$this->deleteSiteAliasUrls($idSite);
		if(count($urls) > 1)
		{
			$insertedUrls = $this->addSiteAliasUrls($idSite, array_slice($urls,1));
		}
		$this->postUpdateWebsite($idSite);
	}
	
	private function checkAndReturnExcludedQueryParameters($parameters)
	{
		$parameters = trim($parameters);
		if(empty($parameters))
		{
			return '';
		}
		
		$parameters = explode(',', $parameters);
		$parameters = array_map('trim', $parameters);
		$parameters = array_filter($parameters, 'strlen');
		$parameters = array_unique($parameters);
		return implode(',', $parameters);
	}
	
	/**
	 * Returns the list of supported currencies 
	 * @see getCurrencySymbols()
	 * @return array ( currencyId => currencyName)
	 */
	public function getCurrencyList()
	{
		return array(
    		'USD' => 'US Dollar ($)',
            'EUR' => 'Euro (€)',
            'JPY' => 'Japanese Yen (¥)',
            'GBP' => 'British Pound Sterling (£)',
            'AUD' => 'Australian Dollar (A$)',
            'KRW' => 'South Korean Won (₩)',
            'BRL' => 'Brazilian Real (R$)',
            'CNY' => 'Chinese Yuan Renminbi (CN¥)',
            'DKK' => 'Danish Krone (Dkr)',
            'RUB' => 'Russian Ruble (RUB)',
            'SEK' => 'Swedish Krona (Skr)',
            'NOK' => 'Norwegian Krone (Nkr)',
            'PLN' => 'Polish Zloty (zł)',
            'TRY' => 'Turkish Lira (TL)',
            'TWD' => 'New Taiwan Dollar (NT$)',
            'HKD' => 'Hong Kong Dollar (HK$)',
            'THB' => 'Thai Baht (฿)',
            'IDR' => 'Indonesian Rupiah (Rp)',
            'ARS' => 'Argentine Peso (AR$)',
            'MXN' => 'Mexican Peso (MXN)',
            'VND' => 'Vietnamese Dong (₫)',
            'PHP' => 'Philippine Peso (Php)',
            'INR' => 'Indian Rupee (Rs.)',
			'VEF' => 'Venezuelan bolívar (Bs. F)',
            'CHF' => 'Swiss Franc (Fr.)',
		);
	}
	
	/**
	 * Returns the list of currency symbols
	 * @see getCurrencyList()
	 * @return array( currencyId => currencySymbol )
	 */
	public function getCurrencySymbols()
	{
		return array(
    		'USD' => '$',
            'EUR' => '€',
            'JPY' => '¥',
            'GBP' => '£',
            'AUD' => 'A$',
            'KRW' => '₩',
            'BRL' => 'R$',
            'CNY' => 'CN¥',
            'DKK' => 'Dkr',
            'RUB' => 'RUB',
            'SEK' => 'Skr',
            'NOK' => 'Nkr',
            'PLN' => 'zł',
            'TRY' => 'TL',
            'TWD' => 'NT$',
            'HKD' => 'HK$',
            'THB' => '฿',
            'IDR' => 'Rp',
            'ARS' => 'AR$',
            'MXN' => 'MXN',
            'VND' => '₫',
            'PHP' => 'Php',
            'INR' => 'Rs.',
			'VEF' => 'Bs. F',
            'CHF' => 'Fr.',
		);
	}
	
	
	/**
	 * Returns the list of timezones supported. 
	 * Used for addSite and updateSite
	 * 
	 * @TODO NOT COMPATIBLE WITH API RESPONSE AUTO BUILDER
	 * 
	 * @return array of timezone strings
	 */
	public function getTimezonesList()
	{
		if(!Piwik::isTimezoneSupportEnabled())
		{
			return array('UTC' => $this->getTimezonesListUTCOffsets());
		}
		
		$continents = array( 'Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific');
		$timezones = timezone_identifiers_list();
		
		$return = array();
		foreach($timezones as $timezone)
		{
			$timezoneExploded = explode('/', $timezone);
			$continent = $timezoneExploded[0];
			
			// only display timezones that are grouped by continent
			if(!in_array($continent, $continents))
			{
				continue;
			}
			$city = $timezoneExploded[1];
			if(!empty($timezoneExploded[2]))
			{
				$city .= ' - '.$timezoneExploded[2];
			}
			$city = str_replace('_', ' ', $city);
			$return[$continent][$timezone] = $city;
		}

		foreach($continents as $continent)
		{
			ksort($return[$continent]);
		}

		$return['UTC'] = $this->getTimezonesListUTCOffsets();
		return $return;
	}
	
	private function getTimezonesListUTCOffsets()
	{
		// manually add the UTC offsets
		$GmtOffsets = array (-12, -11.5, -11, -10.5, -10, -9.5, -9, -8.5, -8, -7.5, -7, -6.5, -6, -5.5, -5, -4.5, -4, -3.5, -3, -2.5, -2, -1.5, -1, -0.5,
			0, 0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5, 5.5, 5.75, 6, 6.5, 7, 7.5, 8, 8.5, 8.75, 9, 9.5, 10, 10.5, 11, 11.5, 12, 12.75, 13, 13.75, 14);
			
		$return = array();
		foreach($GmtOffsets as $offset)
		{
			if($offset > 0)
			{
				$offset = '+'.$offset;
			}
			elseif($offset == 0)
			{
				$offset = '';
			}
			$offset = 'UTC' . $offset;
			$offsetName = str_replace(array('.25','.5','.75'), array(':15',':30',':45'), $offset);
			$return[$offset] = $offsetName;
		}
		return $return;
	}
	
	/**
	 * Insert the list of alias URLs for the website.
	 * The URLs must not exist already for this website!
	 */
	private function insertSiteUrls($idSite, $urls)
	{
		if(count($urls) != 0)
		{
			$db = Zend_Registry::get('db');
			foreach($urls as $url)
			{
				$db->insert(Piwik_Common::prefixTable("site_url"), array(
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
	private function deleteSiteAliasUrls($idsite)
	{
		$db = Zend_Registry::get('db');
		$db->query("DELETE FROM ".Piwik_Common::prefixTable("site_url") ." 
					WHERE idsite = ?", $idsite);
	}
	
	/**
	 * Remove the final slash in the URLs if found
	 * 
	 * @return string the URL without the trailing slash
	 */
	private function removeTrailingSlash($url)
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
	private function isValidUrl( $url )
	{
		return Piwik_Common::isLookLikeUrl($url);
	}
	
	/**
	 * Tests if the IP is a valid IP, allowing wildcards, except in the first octet.
	 * Wildcards can only be used from right to left, ie. 1.1.*.* is allowed, but 1.1.*.1 is not.
	 * 
	 * @param $ip
	 * @return bool
	 */
	private function isValidIp( $ip )
	{
		return preg_match('~^(\d+)\.(\d+)\.(\d+)\.(\d+)$~', $ip, $matches) !== 0
			|| preg_match('~^(\d+)\.(\d+)\.(\d+)\.\*$~', $ip, $matches) !== 0
			|| preg_match('~^(\d+)\.(\d+)\.\*.\*$~', $ip, $matches) !== 0
			|| preg_match('~^(\d+)\.\*\.\*\.\*$~', $ip, $matches) !== 0
			;
	}
	
	/**
	 * Check that the website name has a correct format.
	 * 
	 * @exception if the website name is empty
	 */
	private function checkName($siteName)
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
	private function checkUrls($urls)
	{
		foreach($urls as $url)
		{			
			if(!$this->isValidUrl($url))
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
	private function cleanParameterUrls( $urls )
	{
		if(!is_array($urls))
		{
			$urls = array($urls);
		}
		
		$urls = array_map('urldecode', $urls);
		foreach($urls as &$url)
		{
			$url = $this->removeTrailingSlash($url);
		}
		$urls = array_unique($urls);
		
		return $urls;
	}
}
