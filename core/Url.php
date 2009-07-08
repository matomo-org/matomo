<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Helper
 */

/**
 * @package Piwik_Helper
 *
 */
class Piwik_Url 
{
	/**
	 * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * @return string
	 */
	static public function getCurrentUrl()
	{
		return	self::getCurrentHost()
				. self::getCurrentScriptName() 
				. self::getCurrentQueryString();
	}
	
	/**
	 * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return "http://example.org/dir1/dir2/index.php"
	 * @return string
	 */
	static public function getCurrentUrlWithoutQueryString()
	{
		return	self::getCurrentHost()
				. self::getCurrentScriptName() ;
	}
	
	/**
	 * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return "http://example.org/dir1/dir2/"
	 * @return string with trailing slash
	 */
	static public function getCurrentUrlWithoutFileName()
	{
		$host = self::getCurrentHost();
		$urlDir = self::getCurrentScriptPath();
		return $host.$urlDir;
	}

	/**
	 * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return "/dir1/dir2/"
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
	 * @return string
	 */
	static public function getCurrentScriptName()
	{
		$url = '';
		if( !empty($_SERVER['PATH_INFO']) ) 
		{ 
			$url = $_SERVER['PATH_INFO'];
		} 
		else if( !empty($_SERVER['REQUEST_URI']) ) 
		{
			if( ($pos = strpos($_SERVER['REQUEST_URI'], "?")) !== false ) 
			{
				$url = substr($_SERVER['REQUEST_URI'], 0, $pos);
			} 
			else 
			{
				$url = $_SERVER['REQUEST_URI'];
			}
		} 
		
		if(empty($url))
		{
			$url = $_SERVER['SCRIPT_NAME'];
		}
		return $url;
	}

	/**
	 * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return "http://example.org"
	 * @return string
	 */
	static public function getCurrentHost()
	{
		if(isset($_SERVER['HTTPS'])
			&& ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] === true)
			)
		{
			$url = 'https';
		}
		else
		{
			$url = 'http';
		}
		
		$url .= '://';
		
		if(isset($_SERVER['HTTP_HOST']))
		{
			$url .= $_SERVER['HTTP_HOST'];
		}
		else
		{
			$url .= 'unknown';
		}
		return $url;
	}
		
	/**
	 * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return "?param1=value1&param2=value2"
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
	 * @param $parameters eg. array( 'param1' => 10, 'param2' => array(1,2))
	 * @return string eg. "param1=10&param2[]=1&param2[]=2"
	 */
	static public function getQueryStringFromParameters($parameters)
	{
		$query = '';
		foreach($parameters as $name => $value)
		{
			if(empty($value))
			{
				continue;
			}
			if(is_array($value))
			{
				foreach($value as $theValue)
				{
					$query .= $name . "[]=" . $theValue . "&";
				}
			}
			else
			{
				$query .= $name . "=" . $value . "&";
			}
		}
		$query = substr($query, 0, -1);
		return $query;
	}
	
	/**
	 * Redirects the user to the Referer if found. 
	 * If the user doesn't have a referer set, it redirects to the current URL without query string.
	 *
	 * @return void http Location: header sent
	 */
	static public function redirectToReferer()
	{
		$referer = self::getReferer();
		if($referer !== false)
		{	
			self::redirectToUrl($referer);
		}
		self::redirectToUrl(self::getCurrentUrlWithoutQueryString());
	}
	
	/**
	 * Redirects the user to the specified URL
	 *
	 * @param string $url
	 * @return void http Location: header sent
	 */
	static public function redirectToUrl( $url )
	{
		header("Location: $url");
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

}
