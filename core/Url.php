<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * Class to retrieve absolute URL or URI components of the current URL,
 * and handle URL redirection.
 *
 * @package Piwik
 */
class Piwik_Url 
{
	/**
	 * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 *
	 * @return string
	 */
	static public function getCurrentUrl()
	{
		return self::getCurrentScheme() . '://'
			. self::getCurrentHost()
			. self::getCurrentScriptName() 
			. self::getCurrentQueryString();
	}

	/**
	 * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return "http://example.org/dir1/dir2/index.php"
	 *
	 * @return string
	 */
	static public function getCurrentUrlWithoutQueryString()
	{
		return self::getCurrentScheme() . '://'
			. self::getCurrentHost()
			. self::getCurrentScriptName();
	}

	/**
	 * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return "http://example.org/dir1/dir2/"
	 *
	 * @return string with trailing slash
	 */
	static public function getCurrentUrlWithoutFileName()
	{
		return self::getCurrentScheme() . '://'
			. self::getCurrentHost()
			. self::getCurrentScriptPath();
	}

	/**
	 * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return "/dir1/dir2/"
	 *
	 * @return string with trailing slash
	 */
	static public function getCurrentScriptPath()
	{
		$queryString = self::getCurrentScriptName() ;
		
		//add a fake letter case /test/test2/ returns /test which is not expected
		$urlDir = dirname ($queryString . 'x');
		$urlDir = str_replace('\\', '/', $urlDir);
		// if we are in a subpath we add a trailing slash
		if(strlen($urlDir) > 1)
		{
			$urlDir .= '/';
		}
		return $urlDir;
	}

	/**
	 * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return "/dir1/dir2/index.php"
	 *
	 * @return string
	 */
	static public function getCurrentScriptName()
	{
		$url = '';

		if( !empty($_SERVER['REQUEST_URI']) ) 
		{
			$url = $_SERVER['REQUEST_URI'];

			// strip http://host (Apache+Rails anomaly)
			if(preg_match('~^https?://[^/]+($|/.*)~D', $url, $matches))
			{
				$url = $matches[1];
			}

			// strip parameters
			if(($pos = strpos($url, "?")) !== false) 
			{
				$url = substr($url, 0, $pos);
			} 

			// strip path_info
			if(isset($_SERVER['PATH_INFO']))
			{
				$url = substr($url, 0, -strlen($_SERVER['PATH_INFO']));
			}
		} 

	 	/**
		 * SCRIPT_NAME is our fallback, though it may not be set correctly
		 *
		 * @see http://php.net/manual/en/reserved.variables.php
		 */
		if(empty($url))
		{
			if(isset($_SERVER['SCRIPT_NAME']))
			{
				$url = $_SERVER['SCRIPT_NAME'];
			}
			elseif(isset($_SERVER['SCRIPT_FILENAME']))
			{
				$url = $_SERVER['SCRIPT_FILENAME'];
			}
			elseif(isset($_SERVER['argv']))
			{
				$url = $_SERVER['argv'][0];
			}
		}

		if(!isset($url[0]) || $url[0] !== '/')
		{
			$url = '/' . $url;
		}
		return $url;
	}

	/**
	 * If the current URL is 'http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return 'http'
	 *
	 * @return string 'https' or 'http'
	 */
	static public function getCurrentScheme()
	{
		$assume_secure_protocol = @Piwik_Config::getInstance()->General['assume_secure_protocol'];
		if($assume_secure_protocol
			|| (isset($_SERVER['HTTPS'])
				&& ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] === true))
			)
		{
			return 'https';
		}
		return 'http';
	}

	/*
	 * Validate "Host" (untrusted user input)
	 *
	 * @param string $host         Contents of Host: header from Request
	 * @param array  $trustedHosts An array of trusted hosts
	 *
	 * @return boolean True if valid; false otherwise
	 */
	public function isValidHost($host, $trustedHosts)
	{
		// Only punctuation we allow is '[', ']', ':', '.' and '-'
		$hostLength = Piwik_Common::strlen($host);
		if ($hostLength !== strcspn($host, '`~!@#$%^&*()_+={}\\|;"\'<>,?/ '))
		{
    			return false;
		}

		$untrustedHost = Piwik_Common::mb_strtolower($host);
		$hostRegex     = Piwik_Common::mb_strtolower(str_replace('.', '\.', '/(^|.)' . implode('|', $trustedHosts) . '(:[0-9]+)?$/'));

		return 0 !== preg_match($hostRegex, rtrim($untrustedHost, '.'));
	}

	/**
	 * Get host
	 *
	 * @return string|false
	 */
	static public function getHost()
	{
		if (isset($_SERVER['HTTP_HOST'])
			&& strlen($host = $_SERVER['HTTP_HOST'])
			&& (!($trustedHosts = @Piwik_Config::getInstance()->General['trusted_hosts'])
			|| self::isValidHost($host, $trustedHosts)))
		{
			return $host;
		}

		return false;
	}

	/**
	 * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return "example.org"
	 *
	 * @param string $default Default value to return if host unknown
	 * @return string
	 */
	static public function getCurrentHost($default = 'unknown')
	{
		$hostHeaders = @Piwik_Config::getInstance()->General['proxy_host_headers'];
		if(!is_array($hostHeaders))
		{
			$hostHeaders = array();
		}

		$host = self::getHost();
		$default = Piwik_Common::sanitizeInputValue($host ? $host : $default);

		return Piwik_IP::getNonProxyIpFromHeader($default, $hostHeaders);
	}

	/**
	 * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return "?param1=value1&param2=value2"
	 *
	 * @return string
	 */
	static public function getCurrentQueryString()
	{
		$url = '';	
		if(isset($_SERVER['QUERY_STRING'])
			&& !empty($_SERVER['QUERY_STRING']))
		{
			$url .= "?".$_SERVER['QUERY_STRING'];
		}
		return $url;
	}

	/**
	 * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return 
	 *  array
	 *    'param1' => string 'value1'
	 *    'param2' => string 'value2'
	 * 
	 * @return array
	 */
	static function getArrayFromCurrentQueryString()
	{	
		$queryString = self::getCurrentQueryString();
		$urlValues = Piwik_Common::getArrayFromQueryString($queryString);
		return $urlValues;
	}

	/**
	 * Given an array of name-values, it will return the current query string 
	 * with the new requested parameter key-values;
	 * If a parameter wasn't found in the current query string, the new key-value will be added to the returned query string.  
	 *
	 * @param array $params array ( 'param3' => 'value3' )
	 * @return string ?param2=value2&param3=value3
	 */
	static function getCurrentQueryStringWithParametersModified( $params )
	{
		$urlValues = self::getArrayFromCurrentQueryString();
		foreach($params as $key => $value)
		{
			$urlValues[$key] = $value;
		}
		$query = self::getQueryStringFromParameters($urlValues);
		if(strlen($query) > 0)
		{
			return '?'.$query;
		}
		return '';
	}

	/**
	 * Given an array of parameters name->value, returns the query string.
	 * Also works with array values using the php array syntax for GET parameters.
	 *
	 * @param array $parameters eg. array( 'param1' => 10, 'param2' => array(1,2))
	 * @return string eg. "param1=10&param2[]=1&param2[]=2"
	 */
	static public function getQueryStringFromParameters($parameters)
	{
		$query = '';
		foreach($parameters as $name => $value)
		{
			if(is_null($value)
				|| $value === false)
			{
				continue;
			}
			if(is_array($value))
			{
				foreach($value as $theValue)
				{
					$query .= $name . "[]=" . urlencode($theValue) . "&";
				}
			}
			else
			{
				$query .= $name . "=" . urlencode($value) . "&";
			}
		}
		$query = substr($query, 0, -1);
		return $query;
	}

	/**
	 * Redirects the user to the referrer if found. 
	 * If the user doesn't have a referrer set, it redirects to the current URL without query string.
	 */
	static public function redirectToReferer()
	{
		$referrer = self::getReferer();
		if($referrer !== false)
		{	
			self::redirectToUrl($referrer);
		}
		self::redirectToUrl(self::getCurrentUrlWithoutQueryString());
	}

	/**
	 * Redirects the user to the specified URL
	 *
	 * @param string $url
	 */
	static public function redirectToUrl( $url )
	{
		if(Piwik_Common::isLookLikeUrl($url)
			|| strpos($url, 'index.php') === 0)
		{
			@header("Location: $url");
		}
		else
		{
			echo "Invalid URL to redirect to.";
		}
		exit;
	}

	/**
	 * Returns the HTTP_REFERER header, false if not found.
	 *
	 * @return string|false
	 */
	static public function getReferer()
	{
		if(!empty($_SERVER['HTTP_REFERER']))
		{
			return $_SERVER['HTTP_REFERER'];
		}
		return false;
	}

	/**
	 * Is the URL on the same host?
	 *
	 * @param string $url
	 * @return bool True if local; false otherwise.
	 */
	static public function isLocalUrl($url)
	{
		if(empty($url))
		{
			return true;
		}

		// handle host name mangling
		$requestUri = isset($_SERVER['SCRIPT_URI']) ? $_SERVER['SCRIPT_URI'] : '';
		$parseRequest = @parse_url($requestUri);
		$hosts = array(	self::getHost(), self::getCurrentHost() );
		if(isset($parseRequest['host']))
		{
			$hosts[] = $parseRequest['host'];
		}

		// drop port numbers from hostnames and IP addresses
		$hosts = array_map(array('Piwik_IP', 'sanitizeIp'), $hosts);

		// compare scheme and host
		$parsedUrl = @parse_url($url);
		$scheme = $parsedUrl['scheme'];
		$host = Piwik_IP::sanitizeIp($parsedUrl['host']);
		return (in_array($scheme, array('http', 'https')) && in_array($host, $hosts));
	}
}
