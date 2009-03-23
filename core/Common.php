<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Common.php 581 2008-07-27 23:07:52Z matt $
 *
 * @package Piwik_Helper
 */

/**
 * Static class providing functions used by both the CORE of Piwik and the visitor Tracking engine.
 *
 * This is the only external class loaded by the /piwik.php file.
 * This class should contain only the functions that are used in
 * both the CORE and the piwik.php statistics logging engine.
 *
 * @package Piwik_Helper
 */
class Piwik_Common
{
	/**
	 * Const used to map the referer type to an integer in the log_visit table
	 *
	 */
	const REFERER_TYPE_DIRECT_ENTRY		= 1;
	const REFERER_TYPE_SEARCH_ENGINE	= 2;
	const REFERER_TYPE_WEBSITE			= 3;
	const REFERER_TYPE_CAMPAIGN			= 6;

	/**
	 * Flag used with htmlspecialchar
	 * See php.net/htmlspecialchars
	 *
	 */
	const HTML_ENCODING_QUOTE_STYLE		= ENT_COMPAT;

	
	/**
	 * Returns the table name prefixed by the table prefix.
	 * Works in both Tracker and UI mode.
	 * 
	 * @param string The table name to prefix, ie "log_visit"
	 * @return string The table name prefixed, ie "piwik-production_log_visit"
	 */
	static public function prefixTable($table)
	{
		$prefixTable = false;
		if(class_exists('Piwik_Tracker_Config'))
		{
			$prefixTable = Piwik_Tracker_Config::getInstance()->database['tables_prefix'];
		}
		else
		{
			$config = Zend_Registry::get('config');
			if($config !== false)
			{
				$prefixTable = $config->database->tables_prefix;
			}
		}
		return $prefixTable . $table;
	}
	
	/**
	 * Returns array containing data about the website: goals, URLs, etc.
	 *
	 * @param int $idSite
	 * @return array
	 */
	static function getCacheWebsiteAttributes( $idSite )
	{
		static $cache = null;
		if(is_null($cache))
		{
			require_once "CacheFile.php";
			$cache = new Piwik_CacheFile('tracker');
		}
		$filename = $idSite;
		$cacheContent = $cache->get($filename);
		if($cacheContent !== false)
		{
			return $cacheContent;
		}
		// if DB is not in the registry, we are in tracker mode, we add it in the registry
		if(defined('PIWIK_TRACKER_MODE') 
			&& PIWIK_TRACKER_MODE) 
		{
			Zend_Registry::set('db', Piwik_Tracker::getDatabase());
			//TODO we can remove these includes when #620 is done
			require_once "Zend/Exception.php";
			require_once "Zend/Loader.php"; 
			require_once "Zend/Auth.php";
			require_once "Timer.php";
			require_once "PluginsManager.php";
			require_once "core/Piwik.php";
			require_once "Access.php";
			require_once "Auth.php";
			require_once "API/Proxy.php";
			require_once "Archive.php";
			require_once "Site.php";
			require_once "Date.php";
			require_once "DataTable.php";
			require_once "Translate.php";
			require_once "Mail.php";
			require_once "Url.php";
			require_once "Controller.php";
			require_once "Option.php";
			require_once "View.php";
			require_once "UpdateCheck.php";
			Piwik::createAccessObject();
			Piwik::setUserIsSuperUser();
			$pluginsManager = Piwik_PluginsManager::getInstance();
			$pluginsManager->setPluginsToLoad( Zend_Registry::get('config')->Plugins->Plugins->toArray() );
		}

		$content = array();
		Piwik_PostEvent('Common.fetchWebsiteAttributes', $content, $idSite);
		$cache->set($filename, $content);
		return $content;
	}
	
	/**
	 * Delete existing Tracker cache files and regenerate them
	 * 
	 * @param array $idSites array of idSites to clear cache for
	 * @return void
	 */
	static public function regenerateCacheWebsiteAttributes($idSites = array())
	{
		if(!is_array($idSites))
		{
			$idSites = array( $idSites );
		}
		foreach($idSites as $idSite) {
			self::deleteCacheWebsiteAttributes($idSite);
			self::getCacheWebsiteAttributes($idSite);
		}
	}
	
	static public function deleteCacheWebsiteAttributes( $idSite )
	{
		require_once "CacheFile.php";
		$cache = new Piwik_CacheFile('tracker');
		$filename = $idSite;
		$cache->delete($filename);
	}

	/**
	 * Returns the path and query part from a URL.
	 * Eg. http://piwik.org/test/index.php?module=CoreHome will return /test/index.php?module=CoreHome
	 *
	 * @param string $url either http://piwik.org/test or /
	 * @return string
	 */
	static function getPathAndQueryFromUrl($url)
	{
		$parsedUrl = parse_url( $url );

		$result = '';

		if(isset($parsedUrl['path']))
		{
			$result .= substr($parsedUrl['path'], 1);
		}

		if(isset($parsedUrl['query']))
		{
			$result .= '?'.$parsedUrl['query'];
		}

		return $result;
	}
	
	/**
	 * ending WITHOUT slash
	 * @return string 
	 */
	static public function getPathToPiwikRoot()
	{
		return realpath( dirname(__FILE__). "/../" );
	}
	

	/**
	 * Returns the value of a GET parameter $parameter in an URL query $urlQuery
	 *
	 * @param string $urlQuery result of parse_url()['query'] and htmlentitied (& is &amp;) eg. module=test&amp;action=toto or ?page=test
	 * @param string $param
	 *
	 * @return string|bool Parameter value if found (can be the empty string!), false if not found
	 */
	static public function getParameterFromQueryString( $urlQuery, $parameter)
	{
		$nameToValue = self::getArrayFromQueryString($urlQuery);

		if(isset($nameToValue[$parameter]))
		{
			return $nameToValue[$parameter];
		}
		return false;
	}

	/**
	 * Returns an URL query string in an array format
	 * The input query string should be htmlspecialchar'ed
	 *
	 * @param string urlQuery
	 * @return array array( param1=> value1, param2=>value2)
	 */
	static public function getArrayFromQueryString( $urlQuery )
	{
		if(strlen($urlQuery) == 0)
		{
			return array();
		}
		if($urlQuery[0] == '?')
		{
			$urlQuery = substr($urlQuery, 1);
		}

		$separator = '&amp;';

		$urlQuery = $separator . $urlQuery;
		//		$urlQuery = str_replace(array('%20'), ' ', $urlQuery);
		$refererQuery = trim($urlQuery);

		$values = explode($separator, $refererQuery);

		$nameToValue = array();

		foreach($values as $value)
		{
			if( false !== strpos($value, '='))
			{
				$exploded = explode('=',$value);
				$name = $exploded[0];
				
				// if array without indexes
				if( substr($name,-2,2) == '[]' )
				{
					$name = substr($name, 0, -2);
					if( isset($nameToValue[$name]) == false || is_array($nameToValue[$name]) == false )
					{
						$nameToValue[$name] = array();
					}
					array_push($nameToValue[$name],$exploded[1]);
				}
				else
				{
					$nameToValue[$name] = $exploded[1];
				}
			}
		}
		return $nameToValue;
	}

	static public function mkdir( $path, $mode = 0755, $denyAccess = true )
	{
		if(!is_dir($path))
		{
			$directoryParent = Piwik_Common::realpath(dirname($path));
			if( is_writable($directoryParent) )
			{
				mkdir($path, $mode, true);
			}
		}
		
		if($denyAccess)
		{
			self::createHtAccess($path);
		}
	}

	/**
	 * path without trailing slash
	 */
	static public function createHtAccess( $path )
	{
		@file_put_contents($path . "/.htaccess", "Deny from all");
	}
	
	static public function realpath($path)
	{
		if (file_exists($path)) 
		{
		    return realpath($path);
		} 
	    return $path;
	}
	
	/**
	 * Returns true if the string is a valid filename
	 * File names that start with a-Z or 0-9 and contain a-Z, 0-9, underscore(_), dash(-), and dot(.) will be accepted.
	 * File names beginning with anything but a-Z or 0-9 will be rejected (including .htaccess for example).
	 * File names containing anything other than above mentioned will also be rejected (file names with spaces won't be accepted).
	 *
	 * @param string filename
	 * @return bool
	 *
	 */
	static public function isValidFilename($filename)
	{
		return (0 !== preg_match("/(^[a-zA-Z0-9]+([a-zA-Z\_0-9\.-]*))$/" , $filename));
	}
	
	/**
	 * Returns true if the string passed may be a URL.
	 * We don't need a precise test here because the value comes from the website
	 * tracked source code and the URLs may look very strange.
	 *
	 * @param string $url
	 * @return bool
	 */
	static function isLookLikeUrl( $url )
	{
		return preg_match('/^(ftp|news|http|https)?:\/\/(.*)$/', $url, $matches) !== 0
				&& strlen($matches[2]) > 0;
	}

	/**
	 * Returns the variable after cleaning operations.
	 * NB: The variable still has to be escaped before going into a SQL Query!
	 *
	 * If an array is passed the cleaning is done recursively on all the sub-arrays. \
	 * The keys of the array are filtered as well!
	 *
	 * How this method works:
	 * - The variable returned has been htmlspecialchars to avoid the XSS security problem.
	 * - The single quotes are not protected so "Piwik's amazing" will still be "Piwik's amazing".
	 *
	 * - Transformations are:
	 * 		- '&' (ampersand) becomes '&amp;'
	 *  	- '"'(double quote) becomes '&quot;'
	 * 		- '<' (less than) becomes '&lt;'
	 * 		- '>' (greater than) becomes '&gt;'
	 * - It handles the magic_quotes setting.
	 * - A non string value is returned without modification
	 *
	 * @param mixed The variable to be cleaned
	 * @return mixed The variable after cleaning
	 */
	static public function sanitizeInputValues($value)
	{
		if(is_numeric($value))
		{
			return $value;
		}
		elseif(is_string($value))
		{
			$value = htmlspecialchars($value, Piwik_Common::HTML_ENCODING_QUOTE_STYLE, 'UTF-8');

			// Undo the damage caused by magic_quotes -- only before php 5.3 as it is now deprecated
			if ( version_compare(phpversion(), '5.3') === -1 
				&& get_magic_quotes_gpc())
			{
				$value = stripslashes($value);
			}
		}
		elseif (is_array($value))
		{
			foreach (array_keys($value) as $key)
			{
				$newKey = $key;
				$newKey = Piwik_Common::sanitizeInputValues($newKey);
				if ($key != $newKey)
				{
					$value[$newKey] = $value[$key];
					unset($value[$key]);
				}

				$value[$newKey] = Piwik_Common::sanitizeInputValues($value[$newKey]);
			}
		}
		elseif( !is_null($value)
			&& !is_bool($value))
		{
			throw new Exception("The value to escape has not a supported type. Value = ".var_export($value, true));
		}
		return $value;
	}

	/**
	 * Returns a variable from the $_REQUEST superglobal.
	 * If the variable doesn't have a value or an empty value, returns the defaultValue if specified.
	 * If the variable doesn't have neither a value nor a default value provided, an exception is raised.
	 *
	 * @param string $varName name of the variable
	 * @param string $varDefault default value. If '', and if the type doesn't match, exit() !
	 * @param string $varType Expected type, the value must be one of the following: array, numeric, int, integer, string
	 *
	 * @exception if the variable type is not known
	 * @exception if the variable we want to read doesn't have neither a value nor a default value specified
	 *
	 * @return mixed The variable after cleaning
	 */
	static public function getRequestVar($varName, $varDefault = null, $varType = null, $requestArrayToUse = null)
	{
		if(is_null($requestArrayToUse))
		{
			$requestArrayToUse = $_REQUEST;
		}

		$varDefault = self::sanitizeInputValues( $varDefault );

		if($varType == 'int')
		{
			// settype accepts only integer
			// 'int' is simply a shortcut for 'integer'
			$varType = 'integer';
		}

		// there is no value $varName in the REQUEST so we try to use the default value
		if(empty($varName)
			|| !isset($requestArrayToUse[$varName])
			|| (	!is_array($requestArrayToUse[$varName])
				&& strlen($requestArrayToUse[$varName]) === 0
				)
		)
		{
			if( is_null($varDefault))
			{
				throw new Exception("The parameter '$varName' isn't set in the Request, and a default value wasn't provided.");
			}
			else
			{
				if( !is_null($varType)
					&& in_array($varType, array('string', 'integer', 'array'))
				)
				{
					settype($varDefault, $varType);
				}
				return $varDefault;
			}
		}

		// Normal case, there is a value available in REQUEST for the requested varName
		$value = self::sanitizeInputValues( $requestArrayToUse[$varName] );

		if( !is_null($varType))
		{
			$ok = false;
				
			if($varType == 'string')
			{
				if(is_string($value)) $ok = true;
			}
			elseif($varType == 'numeric')
			{
				if(is_numeric($value) || $value==(int)$value || $value==(float)$value) $ok = true;
			}
			elseif($varType == 'integer')
			{
				if(is_int($value) || $value==(int)$value) $ok = true;
			}
			elseif($varType == 'float')
			{
				if(is_float($value) || $value==(float)$value) $ok = true;
			}
			elseif($varType == 'array')
			{
				if(is_array($value)) $ok = true;
			}
			else
			{
				throw new Exception("\$varType specified is not known. It should be one of the following: array, numeric, int, integer, float, string");
			}
				
			// The type is not correct
			if($ok === false)
			{
				if($varDefault === null)
				{
					throw new Exception("The parameter '$varName' doesn't have a correct type, and a default value wasn't provided.");
				}
				// we return the default value with the good type set
				else
				{
					settype($varDefault, $varType);
					return $varDefault;
				}
			}
		}

		return $value;
	}

	/**
	 * Returns a 32 characters long uniq ID
	 *
	 * @return string 32 chars
	 */
	static public function generateUniqId()
	{
		return md5(uniqid(rand(), true));
	}

	/**
	 * Returns the best possible IP of the current user, in the format A.B.C.D
	 *
	 * @return string ip
	 */
	static public function getIp()
	{
		if(isset($_SERVER['HTTP_CLIENT_IP'])
		&& ($ip = Piwik_Common::getFirstIpFromList($_SERVER['HTTP_CLIENT_IP']))
		&& strpos($ip, "unknown") === false)
		{
			return $ip;
		}
		elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])
		&& $ip = Piwik_Common::getFirstIpFromList($_SERVER['HTTP_X_FORWARDED_FOR'])
		&& isset($ip)
		&& !empty($ip)
		&& strpos($ip, "unknown")===false )
		{
			return $ip;
		}
		elseif( isset($_SERVER['HTTP_CLIENT_IP'])
		&& strlen( Piwik_Common::getFirstIpFromList($_SERVER['HTTP_CLIENT_IP']) ) != 0 )
		{
			return Piwik_Common::getFirstIpFromList($_SERVER['HTTP_CLIENT_IP']);
		}
		else if( isset($_SERVER['HTTP_X_FORWARDED_FOR'])
		&& strlen ($ip = Piwik_Common::getFirstIpFromList($_SERVER['HTTP_X_FORWARDED_FOR'])) != 0)
		{
			return $ip;
		}
		elseif(isset($_SERVER['REMOTE_ADDR']))
		{
			return Piwik_Common::getFirstIpFromList($_SERVER['REMOTE_ADDR']);	
		}
		else
		{
			return '0.0.0.0';	
		}
	}


	/**
	 * Returns the first element of a comma separated list of IPs
	 *
	 * @param string $ip
	 *
	 * @return string first element before ','
	 */
	static private function getFirstIpFromList($ip)
	{
		$p = strpos($ip, ',');
		if($p!==false)
		{
			return trim(Piwik_Common::sanitizeInputValues(substr($ip, 0, $p)));
		}
		return trim(Piwik_Common::sanitizeInputValues($ip));
	}


	/**
	 * Returns the continent of a given country
	 *
	 * @param string Country 2 letters isocode
	 *
	 * @return string Continent (3 letters code : afr, asi, eur, amn, ams, oce)
	 */
	static public function getContinent($country)
	{
		require_once "DataFiles/Countries.php";

		$countryList = $GLOBALS['Piwik_CountryList'];

		if(isset($countryList[$country][0]))
		{
			return $countryList[$country][0];
		}
		else
		{
			return 'unk';
		}
	}

	/**
	 * Returns the browser language code, eg. "en-gb,en;q=0.5"
	 *
	 * @return string
	 */
	static public function getBrowserLanguage($browserLang = NULL)
	{
		static $replacementPatterns = array(
				'/(\\\\.)/',     // quoted-pairs (RFC 3282)
				'/(\s+)/',       // CFWS white space
				'/(\([^)]*\))/', // CFWS comments
				'/(;q=[0-9.]+)/' // quality
			);

		if(is_null($browserLang))
		{
			$browserLang = Piwik_Common::sanitizeInputValues(@$_SERVER['HTTP_ACCEPT_LANGUAGE']);
		}

		if(is_null($browserLang))
		{
			// a fallback might be to infer the language in HTTP_USER_AGENT (i.e., localized build)
			$browserLang = "";
		}
		else
		{
			// language tags are case-insensitive per HTTP/1.1 s3.10 but the region may be capitalized per ISO3166-1;
			// underscores are not permitted per RFC 4646 or 4647 (which obsolete RFC 1766 and 3066),
			// but we guard against a bad user agent which naively uses its locale
			$browserLang = strtolower(str_replace('_', '-', $browserLang));

			// filters
			$browserLang = preg_replace($replacementPatterns, '', $browserLang);

			$browserLang = preg_replace('/((^|,)chrome:.*)/', '', $browserLang, 1); // Firefox bug
			$browserLang = preg_replace('/(,)(?:en-securid,)|(?:(^|,)en-securid(,|$))/', '\\1',	$browserLang, 1); // unregistered language tag

			$browserLang = str_replace('sr-sp', 'sr-rs', $browserLang); // unofficial (proposed) code in the wild
		}

		return $browserLang;
	}

	/**
	 * Returns the visitor country based only on the Browser 'accepted language' information
	 *
	 * @param string $lang browser lang
	 * @param bool If set to true, some assumption will be made and detection guessed more often, but accuracy could be affected
	 * @return string 2 letter ISO code 
	 */
	static public function getCountry( $lang, $enableLanguageToCountryGuess )
	{
		if(empty($lang) || strlen($lang) < 2)
		{
			return 'xx';
		}

		$validCountries = self::getCountriesList();
		return self::extractCountryCodeFromBrowserLanguage($lang, $validCountries, $enableLanguageToCountryGuess);
	}
	
	/**
	 * Returns list of valid country codes
	 *
	 * @return array of 2 letter ISO codes
	 */
	static public function getCountriesList()
	{
		static $countriesList = null;
		if(is_null($countriesList))
		{
			require_once "DataFiles/Countries.php";
			$countriesList = array_keys($GLOBALS['Piwik_CountryList']);
		}
		return $countriesList;
	}
	/**
	 * Returns list of valid country codes
	 *
	 * @return array of 2 letter ISO codes
	 */
	static public function extractCountryCodeFromBrowserLanguage($browserLanguage, $validCountries, $enableLanguageToCountryGuess)
 	{
		static $langToCountry = null;
		if(is_null($langToCountry))
 		{
			require_once "DataFiles/LanguageToCountry.php";
			$langToCountry = array_keys($GLOBALS['Piwik_LanguageToCountry']);
 		}
 
 		if($enableLanguageToCountryGuess)
 		{
			if(preg_match('/^([a-z]{2,3})(?:,|;|$)/', $browserLanguage, $matches))
 			{
				// match language (without region) to infer the country of origin
				if(in_array($matches[1], $langToCountry))
				{
					return $GLOBALS['Piwik_LanguageToCountry'][$matches[1]];
				}
 			}
 		}
 
		if(preg_match_all("/[-]([a-z]{2})/", $browserLanguage, $matches, PREG_SET_ORDER))
 		{
			foreach($matches as $parts)
 			{
				// match location; we don't make any inferences from the language
				if(in_array($parts[1], $validCountries))
				{
					return $parts[1];
				}
 			}
 		}	
		return 'xx';
	}
	
	
 	/**
	 * Returns the visitor language based only on the Browser 'accepted language' information
	 *
	 * @param string $lang browser lang
	 *
	 * @return string 2 letter ISO 639 code
	 */
	static public function extractLanguageCodeFromBrowserLanguage($browserLanguage, $validLanguages)
	{
		// assumes language preference is sorted;
		// does not handle language-script-region tags or language range (*)
		if(preg_match_all("/(?:^|,)([a-z]{2,3})([-][a-z]{2})?/", $browserLanguage, $matches, PREG_SET_ORDER))
 		{
			foreach($matches as $parts)
 			{
				if(count($parts) == 3)
				{
					// match locale (langauge and location)
					if(in_array($parts[1].$parts[2], $validLanguages))
					{
						return $parts[1].$parts[2];
					}
				}
				// match language only (where no region provided)
				if(in_array($parts[1], $validLanguages))
				{
					return $parts[1];
				}
 			}
 		}
		return 'xx';
 	}
	
	/**
	 * Generate random string 
	 *
	 * @param string $length string length
	 * @param string $alphabet characters allowed in random string
	 *
	 * @return string random string with given length
	 */	
	public static function getRandomString($length = 16, $alphabet = "abcdefghijklmnoprstuvwxyz0123456789")
	{
		$chars = $alphabet;
		$str = '';

		list($usec, $sec) = explode(" ", microtime());
		$seed = ((float)$sec+(float)$usec)*100000;
		mt_srand($seed);
		
		for($i = 0; $i < $length; $i++)
		{
			$rand_key = mt_rand(0, strlen($chars)-1);
			$str  .= substr($chars, $rand_key, 1);
		}
		return str_shuffle($str);
	}
}


