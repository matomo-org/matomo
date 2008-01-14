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

require_once "Zend/Config/Ini.php";
require_once "Zend/Registry.php";

/**
 * 
 * @package Piwik_Helper
 */
class Piwik_Config
{
	protected $urlToPiwikHelpMissingValueInConfigurationFile = 
		'http://dev.piwik.org/trac/browser/trunk/config/global.ini.php?format=raw';
		
	protected $defaultConfig 				= null;
	protected $userConfig 					= null;
	protected $pathIniFileUserConfig 		= null;
	protected $pathIniFileDefaultConfig 	= null;
	
	static public function getDefaultUserConfigPath()
	{
		return PIWIK_INCLUDE_PATH . '/config/config.ini.php';
	}
	function __construct($pathIniFileUserConfig = null)
	{
		Zend_Registry::set('config', $this);
		
		$this->pathIniFileDefaultConfig = PIWIK_INCLUDE_PATH . '/config/global.ini.php';
		if(is_null($pathIniFileUserConfig))
		{	
			$this->pathIniFileUserConfig = self::getDefaultUserConfigPath();
		}
		else
		{
			$this->pathIniFileUserConfig = $pathIniFileUserConfig;
		}
		
		$this->defaultConfig = new Zend_Config_Ini($this->pathIniFileDefaultConfig, null, true);
		
		if(!is_file($this->pathIniFileUserConfig))
		{
			throw new Exception("The configuration file {$this->pathIniFileUserConfig} has not been found.");
		}
		$this->userConfig = new Zend_Config_Ini($this->pathIniFileUserConfig, null, true);
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
		if(!is_null($this->userConfig))
		{
			$this->userConfig->$name = $value;
		}
		else
		{
			$this->defaultConfig->$name = $value;
		}
	}
	
	public function __get($name)
    {
        if( !is_null($this->userConfig)
        	&& null !== ($valueInUserConfig = $this->userConfig->$name))
        {
        	return $valueInUserConfig;
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

