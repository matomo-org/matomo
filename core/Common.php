<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * Static class providing functions used by both the CORE of Piwik and the visitor Tracking engine.
 *
 * This is the only external class loaded by the /piwik.php file.
 * This class should contain only the functions that are used in
 * both the CORE and the piwik.php statistics logging engine.
 *
 * @package Piwik
 */
class Piwik_Common
{
	/**
	 * Const used to map the referer type to an integer in the log_visit table
	 */
	const REFERER_TYPE_DIRECT_ENTRY		= 1;
	const REFERER_TYPE_SEARCH_ENGINE	= 2;
	const REFERER_TYPE_WEBSITE			= 3;
	const REFERER_TYPE_CAMPAIGN			= 6;

	/**
	 * Flag used with htmlspecialchar
	 * See php.net/htmlspecialchars
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
		static $prefixTable = null;
		if(is_null($prefixTable))
		{
			if(!empty($GLOBALS['PIWIK_TRACKER_MODE']))
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
		require_once PIWIK_INCLUDE_PATH . '/core/Loader.php';

		static $cache = null;
		if(is_null($cache))
		{
			$cache = new Piwik_CacheFile('tracker');
		}
		$filename = $idSite;
		$cacheContent = $cache->get($filename);
		if($cacheContent !== false)
		{
			return $cacheContent;
		}
		if(!empty($GLOBALS['PIWIK_TRACKER_MODE']))
		{
			require_once PIWIK_INCLUDE_PATH . '/core/PluginsManager.php';
			require_once PIWIK_INCLUDE_PATH . '/core/Translate.php';
			require_once PIWIK_INCLUDE_PATH . '/core/Option.php';

			try {
				$access = Zend_Registry::get('access');
			} catch (Exception $e) {
				Piwik::createAccessObject();
			}
			try {
				$config = Zend_Registry::get('config');
			} catch (Exception $e) {
				Piwik::createConfigObject();
			}
			try {
				$db = Zend_Registry::get('db');
			} catch (Exception $e) {
				Piwik::createDatabaseObject();
			}

			$pluginsManager = Piwik_PluginsManager::getInstance();
			$pluginsManager->loadPlugins( Zend_Registry::get('config')->Plugins->Plugins->toArray() );
		}

		$isSuperUser = Piwik::isUserIsSuperUser();
		Piwik::setUserIsSuperUser();
		$content = array();
		Piwik_PostEvent('Common.fetchWebsiteAttributes', $content, $idSite);
		
		// we remove the temporary Super user privilege
		Piwik::setUserIsSuperUser($isSuperUser);
		
		// if nothing is returned from the plugins, we don't save the content
		// this is not expected: all websites are expected to have at least one URL
		if(!empty($content))
		{
			$cache->set($filename, $content);
		}
		return $content;
	}

	/**
	 * Regenerate Tracker cache files
	 *
	 * @param array $idSites array of idSites to clear cache for
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

	/**
	 * Delete existing Tracker cache
	 *
	 * @param string $idSite (website ID of the site to clear cache for
	 */
	static public function deleteCacheWebsiteAttributes( $idSite )
	{
		$cache = new Piwik_CacheFile('tracker');
		$filename = $idSite;
		$cache->delete($filename);
	}

	/**
	 * Deletes all Tracker cache files
	 */
	static public function deleteAllCache()
	{
		$cache = new Piwik_CacheFile('tracker');
		$cache->deleteAll();
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
		return realpath( dirname(__FILE__). "/.." );
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

		$separator = '&';

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

	/**
	 * Builds a URL from the result of parse_url function
	 * Copied from the PHP comments at http://php.net/parse_url
	 * @param array
	 */
    static public function getParseUrlReverse($parsed) 
    {
        if (!is_array($parsed)) 
        {
        	return false;
        }
        
        $uri = !empty($parsed['scheme']) ? $parsed['scheme'].':'.((strtolower($parsed['scheme']) == 'mailto') ? '' : '//') : '';
        $uri .= !empty($parsed['user']) ? $parsed['user'].(!empty($parsed['pass']) ? ':'.$parsed['pass'] : '').'@' : '';
        $uri .= !empty($parsed['host']) ? $parsed['host'] : '';
        $uri .= !empty($parsed['port']) ? ':'.$parsed['port'] : '';
        
        if (!empty($parsed['path'])) 
        {
            $uri .= (substr($parsed['path'], 0, 1) == '/') 
            			? $parsed['path'] 
            			: ((!empty($uri) ? '/' : '' ) . $parsed['path']);
        }
        
        $uri .= !empty($parsed['query']) ? '?'.$parsed['query'] : '';
        $uri .= !empty($parsed['fragment']) ? '#'.$parsed['fragment'] : '';
        return $uri;
    }
    
	/**
	 * Create directory if permitted
	 *
	 * @param string $path
	 * @param int $mode (in octal)
	 * @param bool $denyAccess
	 */
	static public function mkdir( $path, $mode = 0755, $denyAccess = true )
	{
		if(!is_dir($path))
		{
			@mkdir($path, $mode, $recursive = true);
		}

		if($denyAccess)
		{
			self::createHtAccess($path);
		}
	}

	/**
	 * Create .htaccess file in specified directory
	 *
	 * Apache-specific; for IIS @see web.config
	 *
	 * @param string $path without trailing slash
	 * @param string $content
	 */
	static public function createHtAccess( $path, $content = "<Files \"*\">\nDeny from all\n</Files>\n" )
	{
		@file_put_contents($path . '/.htaccess', $content);
	}

	/**
	 * Get canonicalized absolute path
	 * See http://php.net/realpath
	 *
	 * @param string $path
	 * @return string canonicalized absolute path
	 */
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
		return (0 !== preg_match('/(^[a-zA-Z0-9]+([a-zA-Z_0-9.-]*))$/', $filename));
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
		return preg_match('~^(ftp|news|http|https)?://(.*)$~', $url, $matches) !== 0
				&& strlen($matches[2]) > 0;
	}

	/**
	 * Returns the variable after cleaning operations.
	 * NB: The variable still has to be escaped before going into a SQL Query!
	 *
	 * If an array is passed the cleaning is done recursively on all the sub-arrays.
	 * The array's keys are filtered as well!
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
			$value = self::sanitizeInputValue($value);

			// Undo the damage caused by magic_quotes; deprecated in php 5.3 but not removed until php 6
			if ( version_compare(phpversion(), '6') === -1
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
				$newKey = self::sanitizeInputValues($newKey);
				if ($key != $newKey)
				{
					$value[$newKey] = $value[$key];
					unset($value[$key]);
				}

				$value[$newKey] = self::sanitizeInputValues($value[$newKey]);
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
	 * Sanitize a single input value
	 *
	 * @param string $value
	 * @return string sanitized input
	 */
	static public function sanitizeInputValue($value)
	{
		return htmlspecialchars($value, self::HTML_ENCODING_QUOTE_STYLE, 'UTF-8');
	}

	/**
	 * Unsanitize a single input value
	 *
	 * @param string $value
	 * @return string unsanitized input
	 */
	static public function unsanitizeInputValue($value)
	{
		return htmlspecialchars_decode($value, self::HTML_ENCODING_QUOTE_STYLE);
	}

	/**
	 * Returns a sanitized variable value from the $_GET and $_POST superglobal.
	 * If the variable doesn't have a value or an empty value, returns the defaultValue if specified.
	 * If the variable doesn't have neither a value nor a default value provided, an exception is raised.
	 *
	 * @see sanitizeInputValues() for the applied sanitization
	 *
	 * @param string $varName name of the variable
	 * @param string $varDefault default value. If '', and if the type doesn't match, exit() !
	 * @param string $varType Expected type, the value must be one of the following: array, int, integer, string
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
			$requestArrayToUse = $_GET + $_POST;
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
			elseif($varType == 'integer')
			{
				if($value == (string)(int)$value) $ok = true;
			}
			elseif($varType == 'float')
			{
				if($value == (string)(float)$value) $ok = true;
			}
			elseif($varType == 'array')
			{
				if(is_array($value)) $ok = true;
			}
			else
			{
				throw new Exception("\$varType specified is not known. It should be one of the following: array, int, integer, float, string");
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
	 * Unserialize (serialized) array
	 *
	 * @param string
	 * @return array or original string if not unserializable
	 */
	public static function unserialize_array( $str )
	{
		// we set the unserialized version only for arrays as you can have set a serialized string on purpose
		if (preg_match('/^a:[0-9]+:{/', $str)
			&& !preg_match('/(^|;|{|})O:[0-9]+:"/', $str)
			&& strpos($str, "\0") === false)
		{
			if( ($arrayValue = @unserialize($str)) !== false
				&& is_array($arrayValue) )
			{
				return $arrayValue;
			}
		}

		// return original string
		return $str;
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
	 * Get salt from [superuser] section
	 *
	 * @return string
	 */
	static public function getSalt()
	{
		static $salt = null;
		if(is_null($salt))
		{
			if(!empty($GLOBALS['PIWIK_TRACKER_MODE']))
			{
				$salt = Piwik_Tracker_Config::getInstance()->superuser['salt'];
			}
			else
			{
				$config = Zend_Registry::get('config');
				if($config !== false)
				{
					$salt = $config->superuser->salt;
				}
			}
		}
		return $salt;
	}

	/**
	 * Convert dotted IP to a stringified integer representation
	 *
	 * @return string ip
	 */
	static public function getIp( $ipStringFrom = false )
	{
		if($ipStringFrom === false) 
		{
			$ipStringFrom = self::getIpString();
		}

		// accept ipv4-mapped addresses
		if(strpos($ipStringFrom, '::ffff:') === 0)
		{
			$ipStringFrom = substr($ipStringFrom, 7);
		}

		return sprintf("%u", ip2long($ipStringFrom));
	}

	/**
	 * Returns the best possible IP of the current user, in the format A.B.C.D
	 *
	 * @return string ip
	 */
	static public function getIpString()
	{
		// note: these may be spoofed
		static $clientHeaders = array(
			// ISP proxy
			'HTTP_CLIENT_IP',

			// de facto standard
			'HTTP_X_FORWARDED_FOR',
		);

		foreach($clientHeaders as $clientHeader)
		{
			if(!empty($_SERVER[$clientHeader]))
			{
				$ip = self::getFirstIpFromList($_SERVER[$clientHeader]);
				if(!empty($ip) && stripos($ip, 'unknown') === false)
				{
					return $ip;
				}
			}
		}

		// default
		if(isset($_SERVER['REMOTE_ADDR']))
		{
			return self::getFirstIpFromList($_SERVER['REMOTE_ADDR']);
		}

		return '0.0.0.0';
	}

	/**
	 * Returns the first element of a comma separated list of IPs
	 *
	 * @param string $ip
	 *
	 * @return string first element before ','
	 */
	static public function getFirstIpFromList($ip)
	{
		$p = strpos($ip, ',');
		if($p!==false)
		{
			return trim(self::sanitizeInputValues(substr($ip, 0, $p)));
		}
		return trim(self::sanitizeInputValues($ip));
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
		require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/Countries.php';
		$countryList = $GLOBALS['Piwik_CountryList'];
		if(isset($countryList[$country]))
		{
			return $countryList[$country];
		}
		return 'unk';
	}

	/**
	 * Returns the browser language code, eg. "en-gb,en;q=0.5"
	 *
	 * @param string $browerLang (optional browser language)
	 * @return string
	 */
	static public function getBrowserLanguage($browserLang = NULL)
	{
		static $replacementPatterns = array(
				// extraneous bits of RFC 3282 that we ignore
				'/(\\\\.)/',		// quoted-pairs
				'/(\s+)/',			// CFWS white space
				'/(\([^)]*\))/',	// CFWS comments
				'/(;q=[0-9.]+)/',	// quality

				// found in the LANG environment variable
				'/\.(.*)/',			// charset (e.g., en_CA.UTF-8)
				'/^C$/',			// POSIX 'C' locale
			);

		if(is_null($browserLang))
		{
			$browserLang = self::sanitizeInputValues(@$_SERVER['HTTP_ACCEPT_LANGUAGE']);
			if(empty($browserLang) && self::isPhpCliMode())
			{
				$browserLang = @$_ENV['LANG'];
			}
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
			$browserLang = preg_replace('/(,)(?:en-securid,)|(?:(^|,)en-securid(,|$))/', '$1',	$browserLang, 1); // unregistered language tag

			$browserLang = str_replace('sr-sp', 'sr-rs', $browserLang); // unofficial (proposed) code in the wild
		}

		return $browserLang;
	}

	/**
	 * Returns the visitor country based on the Browser 'accepted language'
	 * information, but provides a hook for geolocation via IP address.
	 *
	 * @param string $lang browser lang
	 * @param bool If set to true, some assumption will be made and detection guessed more often, but accuracy could be affected
	 * @param string $ip
	 * @return string 2 letter ISO code
	 */
	static public function getCountry( $lang, $enableLanguageToCountryGuess, $ip )
	{
		$country = null;
		Piwik_PostEvent('Common.getCountry', $country, $ip);
		if($country)
		{
			return $country;
		}

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
			require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/Countries.php';
			$countriesList = array_keys($GLOBALS['Piwik_CountryList']);
		}
		return $countriesList;
	}

	/**
	 * Returns list of valid country codes
	 *
	 * @param string $browserLanguage
	 * @param array of string $validCountries
	 * @param bool $enableLanguageToCountryGuess (if true, will guess country based on language that lacks region information)
	 * @return array of 2 letter ISO codes
	 */
	static public function extractCountryCodeFromBrowserLanguage($browserLanguage, $validCountries, $enableLanguageToCountryGuess)
 	{
		static $langToCountry = null;
		if(is_null($langToCountry))
 		{
			require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/LanguageToCountry.php';
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

		if(!empty($validCountries) && preg_match_all('/[-]([a-z]{2})/', $browserLanguage, $matches, PREG_SET_ORDER))
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
	 * @return string 2 letter ISO 639 code
	 */
	static public function extractLanguageCodeFromBrowserLanguage($browserLanguage, $validLanguages)
	{
		// assumes language preference is sorted;
		// does not handle language-script-region tags or language range (*)
		if(!empty($validLanguages) && preg_match_all('/(?:^|,)([a-z]{2,3})([-][a-z]{2})?/', $browserLanguage, $matches, PREG_SET_ORDER))
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
	 * Extracts a keyword from a raw not encoded URL.
	 * Will only extract keyword if a known search engine has been detected.
	 * Returns the keyword:
	 * - in UTF8: automatically converted from other charsets when applicable
	 * - strtolowered: "QUErY test!" will return "query test!"
	 * - trimmed: extra spaces before and after are removed
	 *
	 * Lists of supported search engines can be found in /core/DataFiles/SearchEngines.php
	 * The function returns false when a keyword couldn't be found.
	 * 	 eg. if the url is "http://www.google.com/partners.html" this will return false,
	 *       as the google keyword parameter couldn't be found.
	 *
	 * @see unit tests in /tests/core/Common.test.php
	 * @param string URL referer URL, eg. $_SERVER['HTTP_REFERER']
	 * @return array|false false if a keyword couldn't be extracted,
	 * 						or array(
	 * 							'name' => 'Google',
	 * 							'keywords' => 'my searched keywords')
	 */
	static public function extractSearchEngineInformationFromUrl($refererUrl)
	{
		$refererParsed = @parse_url($refererUrl);
		$refererHost = '';
		if(isset($refererParsed['host']))
		{
			$refererHost = $refererParsed['host'];
		}
		if(empty($refererHost))
		{
			return false;
		}
		// some search engines (eg. Bing Images) use the same domain 
		// as an existing search engine (eg. Bing), we must also use the url path 
		$refererPath = '';
		if(isset($refererParsed['path']))
		{
			$refererPath = $refererParsed['path'];
		}
		// no search query
		if(!isset($refererParsed['query']))
		{
			return false;
		}
		require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/SearchEngines.php';

		$refererHostPath = $refererHost . $refererPath;
		if(array_key_exists($refererHostPath, $GLOBALS['Piwik_SearchEngines']))
		{
			$refererHost = $refererHostPath;
		}
		elseif(!array_key_exists($refererHost, $GLOBALS['Piwik_SearchEngines']))
		{
			return false;
		}
		$searchEngineName = $GLOBALS['Piwik_SearchEngines'][$refererHost][0];
		$variableNames = null;
		if(isset($GLOBALS['Piwik_SearchEngines'][$refererHost][1]))
		{
			$variableNames = $GLOBALS['Piwik_SearchEngines'][$refererHost][1];
		}
		if(!$variableNames)
		{
			$url = $GLOBALS['Piwik_SearchEngines_NameToUrl'][$searchEngineName];
			$variableNames = $GLOBALS['Piwik_SearchEngines'][$url][1];
		}
		if(!is_array($variableNames))
		{
			$variableNames = array($variableNames);
		}
		$query = $refererParsed['query'];

		if($searchEngineName == 'Google Images'
			|| ($searchEngineName == 'Google' && strpos($refererUrl, '/imgres') !== false) )
		{
			$query = urldecode(trim(strtolower(self::getParameterFromQueryString($query, 'prev'))));
			$query = str_replace('&', '&amp;', strstr($query, '?'));
			$searchEngineName = 'Google Images';
		}

		foreach($variableNames as $variableName)
		{
			// search for keywords now &vname=keyword
			$key = strtolower(self::getParameterFromQueryString($query, $variableName));
			$key = trim(urldecode($key));
			if(!empty($key))
			{
				break;
			}
		}
		if(empty($key))
		{
			return false;
		}

		if(function_exists('iconv')
			&& isset($GLOBALS['Piwik_SearchEngines'][$refererHost][3]))
		{
			$charset = trim($GLOBALS['Piwik_SearchEngines'][$refererHost][3]);
			if(!empty($charset))
			{
				$key = @iconv($charset, 'utf-8//IGNORE', $key);
			}
		}
		return array(
			'name' => $searchEngineName,
			'keywords' => $key,
		);
	}

	/**
	 * Generate random string
	 *
	 * @param string $length string length
	 * @param string $alphabet characters allowed in random string
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

	/**
	 * Returns true if PHP was invoked from command-line interface (shell)
	 *
	 * @since added in 0.4.4
	 * @return bool true if PHP invoked as a CGI or from CLI
	 */
	static public function isPhpCliMode()
	{
		$remoteAddr = @$_SERVER['REMOTE_ADDR'];
		return	PHP_SAPI == 'cli' ||
				(substr(PHP_SAPI, 0, 3) == 'cgi' && empty($remoteAddr));
	}
}
