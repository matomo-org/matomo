<?php
/**
 * 
 * @package Piwik
 */
require_once "Zend/Config/Ini.php";
require_once "Zend/Registry.php";
class Piwik_Config
{
	protected $urlToPiwikHelpMissingValueInConfigurationFile = 
		'http://piwik.svn.sourceforge.net/viewvc/piwik/trunk/config/global.ini.php';
		
	protected $defaultConfig 				= null;
	protected $userConfig 					= null;
	protected $pathIniFileUserConfig 		= null;
	protected $pathIniFileDefaultConfig 	= null;
	
	function __construct($pathIniFileUserConfig = null)
	{
		if(is_null($pathIniFileUserConfig))
		{	
			$this->pathIniFileUserConfig = PIWIK_INCLUDE_PATH . '/config/config.ini.php';
			$this->pathIniFileDefaultConfig = PIWIK_INCLUDE_PATH . '/config/global.ini.php';
		}
		$this->userConfig = new Zend_Config_Ini($this->pathIniFileUserConfig, null, true);
		$this->defaultConfig = new Zend_Config_Ini($this->pathIniFileDefaultConfig, null, true);
		
		Zend_Registry::set('config', $this);
		
		$this->setPrefixTables();
	}
	
	public function setTestEnvironment()
	{
		$this->database = $this->database_tests;
		$this->log = $this->log_tests;
		$this->setPrefixTables();
	}
	
	private function setPrefixTables()
	{		
		Zend_Registry::set('tablesPrefix', $this->database->tables_prefix);
	}
	
	public function __set($name, $value)
	{
		$this->userConfig->$name = $value;
	}
	
	public function __get($name)
    {
        if(null !== ($valueInUserConfig = $this->userConfig->$name))
        {
//        	return $valueInUserConfig;
        }
        if(null !== ($valueInDefaultConfig = $this->defaultConfig->$name))
        {
        	return $valueInDefaultConfig;
        }
        
        throw new Exception("The configuration parameter $name couldn't be found in your configuration file.
						<br>Try to replace your default configuration file ({$this->pathIniFileDefaultConfig}) with 
					the <a href='".$this->urlToPiwikHelpMissingValueInConfigurationFile."'>default piwik configuration file</a> ");
    }
	
}

