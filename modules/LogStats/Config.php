<?php

/**
 * Simple class to access the configuration file
 * 
 * This is essentially a very simple version of Zend_Config that we wrote 
 * because of performance concerns. 
 * The LogStats module can't afford a dependency with the Zend_Framework.
 * 
 * @package Piwik_LogStats
 */
class Piwik_LogStats_Config
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
	
	public $config = array();
	
	private function __construct()
	{
		$pathIniFileUser = PIWIK_INCLUDE_PATH . '/config/config.ini.php';
		$pathIniFileGlobal = PIWIK_INCLUDE_PATH . '/config/global.ini.php';
		$this->configUser = parse_ini_file($pathIniFileUser, true);
		$this->configGlobal = parse_ini_file($pathIniFileGlobal, true);
	}
	
	public function __get( $name )
	{
		if(isset($this->configUser[$name]))
		{
			return $this->configUser[$name];
		}
		if(isset($this->configGlobal[$name]))
		{
			return $this->configGlobal[$name];
		}
		throw new Exception("The config element $name is not available in the configuration (check the configuration file).");
	}
}


