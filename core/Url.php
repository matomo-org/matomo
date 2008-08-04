<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Url.php 498 2008-05-29 03:08:13Z matt $
 * 
 * @package Piwik_Helper
 */

/**
 * @package Piwik_Helper
 *
 */
class Piwik_Url 
{
	static function getArrayFromCurrentQueryString()
	{	
		$queryString = Piwik_Url::getCurrentQueryString();
		$queryString = htmlspecialchars($queryString);
		$urlValues = Piwik_Common::getArrayFromQueryString($queryString);
		return $urlValues;
	}
	
	static function getCurrentQueryStringWithParametersModified( $params )
	{
		$urlValues = self::getArrayFromCurrentQueryString();

		foreach($params as $key => $value)
		{
			$urlValues[$key] = $value;
		}
		
		$query = http_build_query($urlValues, "", "&");
		
		if(strlen($query) > 0)
		{
			return '?'.$query;
		}
		else
		{
			return '';
		}
	}
	
	static public function redirectToUrl( $url )
	{
		header("Location: $url");
		exit;
	}
	
	static public function getReferer()
	{
		if(!empty($_SERVER['HTTP_REFERER']))
		{
			return $_SERVER['HTTP_REFERER'];
		}
		return false;
	}

	static public function getCurrentUrl()
	{
		return	self::getCurrentHost()
				. self::getCurrentScriptName() 
				. self::getCurrentQueryString();
	}
	
	static public function getCurrentUrlWithoutQueryString()
	{
		
		return	self::getCurrentHost()
				. self::getCurrentScriptName() ;
	}
	
	/**
	 * Ending with /
	 */
	static public function getCurrentUrlWithoutFileName()
	{
		
		$host = self::getCurrentHost();
		$queryString = self::getCurrentScriptName() ;
		
		//add a fake letter case /test/test2/ returns /test which is not expected
		$urlDir = dirname ($queryString . 'x');
		// if we are in a subpath we add a trailing slash
		if(strlen($urlDir) > 1)
		{
			$urlDir .= '/';
		}
		return $host.$urlDir;
	}
	
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
}

