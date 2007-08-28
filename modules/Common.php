<?php
/**
 * Static class providing functions used by both the CORE of Piwik and the
 * visitor logging engine. 
 * 
 * This is the only external class loaded by the Piwik.php file.
 * This class should contain only the functions that are used in 
 * both the CORE and the piwik.php statistics logging engine.
 */
class Piwik_Common 
{
	const REFERER_TYPE_DIRECT_ENTRY		= 1;
	const REFERER_TYPE_SEARCH_ENGINE	= 2;
	const REFERER_TYPE_WEBSITE			= 3;
	const REFERER_TYPE_PARTNER			= 4;
	const REFERER_TYPE_NEWSLETTER		= 5;
	const REFERER_TYPE_CAMPAIGN			= 6;
	
	const HTML_ENCODING_QUOTE_STYLE		= ENT_COMPAT;
	
	
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
	
	static function isUrl( $url )
	{
		return ereg('^http[s]?://[A-Za-z0-9\/_.-]', $url);
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

			/* Undo the damage caused by magic_quotes */
			if (get_magic_quotes_gpc()) 
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
			&& !is_bool($value)
		)
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
				throw new Exception("\$varName '$varName' doesn't have value in \$_REQUEST and doesn't have a" .
						" \$varDefault value");
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
					throw new Exception("\$varName '$varName' doesn't have a correct type in \$_REQUEST and doesn't " .
							"have a \$varDefault value");
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
	
	
	static public function generateUniqId()
	{
		return md5(uniqid(rand(), true));
	}
	
	/**
	* get the visitor os
	* 
	* @param string $userAgent
	* @param array $osList
	* 
	* @return string 
	*/
	static public function getOs($userAgent)
	{
		
		require_once PIWIK_DATAFILES_INCLUDE_PATH . "/OS.php";
		$osNameToId = $GLOBALS['Piwik_Oslist'];
		
		foreach($osNameToId as $key => $value)
		{
			if ($ok = ereg($key, $userAgent))
			{
				return $value;
			}
		}
		return 'UNK';
	}
		
	/**
	* get visitor browser 
	* 
	* @param string $userAgent
	* @return array array(  'name' 			=> '',
							'major_number' 	=> '',
							'minor_number' 	=> '',
							'version' 		=> '' // major_number.minor_number
						);
	*/
	static public function getBrowserInfo($userAgent)
	{
		
		require_once PIWIK_DATAFILES_INCLUDE_PATH . "/Browsers.php";
		
		$browsers = $GLOBALS['Piwik_BrowserList'];
		
		$info = array(
			'name' 			=> 'UNK',
			'major_number' 	=> '',
			'minor_number' 	=> '',
			'version' 		=> ''
		);
		
		$browser = '';
		foreach($browsers as $key => $value) 
		{
			if(!empty($browser)) $browser .= "|";
			$browser .= $key;
		}
		
		$results = array();
		
		// added fix for Mozilla Suite detection
		if ((preg_match_all("/(mozilla)[\/\sa-z;.0-9-(]+rv:([0-9]+)([.0-9a-z]+)\) gecko\/[0-9]{8}$/i", $userAgent, $results)) 
		||	(preg_match_all("/($browser)[\/\sa-z(]*([0-9]+)([\.0-9a-z]+)?/i", $userAgent, $results))
			)
		 {
			$count = count($results[0])-1;
			
			// browser code
			$info['name'] = $browsers[strtolower($results[1][$count])];
			
			// majeur version number (7 in mozilla 1.7
			$info['major_number'] = $results[2][$count];
			
			// is an minor version number ? If not, 0
			$match = array();
			
			preg_match('/([.\0-9]+)?([\.a-z0-9]+)?/i', $results[3][$count], $match);
			
			if(isset($match[1])) 
			{
				// find minor version number (7 in mozilla 1.7, 9 in firefox 0.9.3)
				$info['minor_number'] = substr($match[1], 0, 2);
			} 
			else 
			{
				$info['minor_number'] = '.0';
			}
			
			$info['version'] = $info['major_number'] . $info['minor_number'];
		}	
		return $info;	
	}

	
	/**
	* Returns the best possible IP in the format A.B.C.D
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
		else
		{
			return Piwik_Common::getFirstIpFromList($_SERVER['REMOTE_ADDR']);
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
			return Piwik_Common::sanitizeInputValues(substr($ip, 0, $p));
		}
		return Piwik_Common::sanitizeInputValues($ip);
	}
	
		
	/**
	* Returns the continent of a given country
	* 
	* @param string Country 2 letters isocode
	* 
	* @return string Continent (3 letters code : afr, asi, eur, amn, ams, oce)
	*/
	function getContinent($country)
	{
		require_once PIWIK_DATAFILES_INCLUDE_PATH . "/Countries.php";
		
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
	* Returns the visitor country based only on the Browser Lang information
	* 
	* @param string $lang browser lang
	* 
	* @return string 
	*/
	function getCountry( $lang )
	{
		require_once PIWIK_DATAFILES_INCLUDE_PATH . "/Countries.php";
		
		$countryList = $GLOBALS['Piwik_CountryList'];
		
		$replaceLangCodeByCountryCode = array(
			// replace cs language (Serbia Montenegro country code) with czech country code
			'cs' => 'cz',
			// replace sv language (El Salvador country code) with sweden country code
			'sv' => 'se',
			// replace fa language (Unknown country code) with Iran country code
			'fa' => 'ir',
			// replace ja language (Unknown country code) with japan country code
			'ja' => 'jp',
			// replace ko language (Unknown country code) with corée country code
			'ko' => 'kr',
			// replace he language (Unknown country code) with Israel country code
			'he' => 'il',
			// replace da language (Unknown country code) with Danemark country code
			'da' => 'dk',
			// replace gb code with UK country code
			'gb' => 'uk',
			);
		
		
		if(empty($lang) || strlen($lang) < 2)
		{
			return 'xx';
		}
		
		$lang = str_replace(	array_keys($replaceLangCodeByCountryCode), 
								array_values($replaceLangCodeByCountryCode), 
								$lang
					);			

        // Ex: "fr"
		if(strlen($lang) == 2)
		{
			if(isset($countryList[$lang]))
			{
				return $lang;
			}
		}

		// when comma
		$offcomma = strpos($lang, ',');

		if($offcomma == 2)
		{
			// in 'fr,en-us', keep first two chars
			$domain = substr($lang, 0, 2);
			if(isset($countryList[$domain]))
			{
				return $domain;
			}

			// catch the second language Ex: "fr" in "en,fr"
			$domain = substr($lang, 3, 2);
			if(isset($countryList[$domain]))
			{
				return $domain;
			}
		}

		// detect second code Ex: "be" in "fr-be"
		$off = strpos($lang, '-');
		if($off!==false)
		{
			$domain = substr($lang, $off+1, 2);
			
			if(isset($countryList[$domain]))
			{
				return $domain;
			}
		}
		
		// catch the second language Ex: "fr" in "en;q=1.0,fr;q=0.9"
		if(preg_match("/^[a-z]{2};q=[01]\.[0-9],(?P<domain>[a-z]{2});/", $lang, $parts))
		{
			$domain = $parts['domain'];

			if(isset($GLOBALS['countryList'][$domain][0]))
			{
				return $domain;
			}
		}
		
		// finally try with the first ever langage code
		$domain = substr($lang, 0, 2);
		if(isset($countryList[$domain]))
		{
			return $domain;
		}
		
		// at this point we really can't guess the country
		return 'xx';
	}
	
	/**
	* Returns the value of a GET parameter $parameter in an URL query $urlQuery
	* 
	* @param string $urlQuery result of parse_url()['query'] and htmlentitied (& is &amp;)
	* @param string $param
	* 
	* @return string|bool Parameter value if found (can be the empty string!), false if not found
	*/
	static public function getParameterFromQueryString( $urlQuery, $parameter)
	{	
		$refererQuery = '&amp;'.trim(str_replace(array('%20'), ' ', '&amp;'.$urlQuery));
		$word = '&amp;'.$parameter.'=';
				
		if( $off = strrpos($refererQuery, $word))
		{
			$off += strlen($word); // &amp;q=
			$str = substr($refererQuery, $off);
			$len = strpos($str, '&amp;');
			if($len === false)
			{
				$len = strlen($str);
			}
			$toReturn = substr($refererQuery, $off, $len);
			return $toReturn;
		}
		else
		{
			return false;
		}
	}
	
}


