<?php
class Piwik_Url 
{
	
	static public function getCurrentUrl()
	{
		return    self::getCurrentHost()
				. self::getCurrentScriptName() 
				. self::getCurrentQueryString();
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
			&& ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == true)
			)
		{
			$url = 'https';
		}
		else
		{
			$url = 'http';
		}
		
		$url .= '://' . $_SERVER['HTTP_HOST'];
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

//echo Piwik_Url::getCurrentCompleteUrl();