<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Config.php 546 2008-07-02 07:13:16Z matt $
 * 
 * @package Piwik_Helper
 */

require_once "Zend/Config/Ini.php";
require_once "Zend/Registry.php";

/**
 * This class is used to access configuration files values.
 * You can also set these values, the updated configuration files will be written at the end of the script execution.
 * 
 * Example reading a value from the configuration file:
 * 	$minValue = Zend_Registry::get('config')->General->minimum_memory_limit;
 * 
 * will read the value minimumMemoryLimit under the [General] section of the config file
 * 
 * @package Piwik_Helper
 */
class Piwik_Config
{
	/**
	 * When the user modifies the configuration file and there is one value missing, we suggest the default config file
	 *
	 * @var string
	 */
	protected $urlToPiwikHelpMissingValueInConfigurationFile = 
		'http://dev.piwik.org/trac/browser/trunk/config/global.ini.php?format=raw';

	protected $defaultConfig 				= null;
	protected $userConfig 					= null;
	protected $pathIniFileUserConfig 		= null;
	protected $pathIniFileDefaultConfig 	= null;
	protected $configFileUpdated 			= false;
	protected $doWriteFileWhenUpdated		= true;
	
	/**
	 * Storing the correct cwd() because the value is not correct in the destructor
	 * "The working directory in the script shutdown phase can be different with some SAPIs (e.g. Apache)."
	 * 
	 * @see http://bugs.php.net/bug.php?id=34206
	 */
	protected $correctCwd;
	
	/**
	 * Returns default relative path for configuration file
	 *
	 * @return string
	 */
	static public function getDefaultUserConfigPath()
	{
		return Piwik_Common::getPathToPiwikRoot() . '/config/config.ini.php';
	}

	static public function getDefaultDefaultConfigPath()
	{
		return Piwik_Common::getPathToPiwikRoot() . '/config/global.ini.php';
	}
	
	/**
	 * Builds the Config object, given the optional path for the user INI file
	 * If not specified, it will use the default path
	 *
	 * @param string $pathIniFileUserConfig
	 */
	function __construct($pathIniFileUserConfig = null, $pathIniFileDefaultConfig = null)
	{
		if(is_null($pathIniFileUserConfig))
		{	
			$pathIniFileUserConfig = self::getDefaultUserConfigPath();
		}
		$this->pathIniFileUserConfig = $pathIniFileUserConfig;
		
		if(is_null($pathIniFileDefaultConfig))
		{	
			$pathIniFileDefaultConfig = self::getDefaultDefaultConfigPath();
		}
		$this->pathIniFileDefaultConfig = $pathIniFileDefaultConfig;
		
		// see http://bugs.php.net/bug.php?id=34206
		$this->correctCwd = getcwd();
	}
	
	/**
	 * By default, when calling setting configuration values using
	 * $config->database = array(...)
	 * Piwik will automatically save the updated config file in __destruct()
	 * This can be disabled (when setting partial configuration values during the installation process for example)
	 *  
	 * @return void
	 */
	public function disableSavingConfigurationFileUpdates()
	{
		$this->doWriteFileWhenUpdated = false;
	}
	
	public function init()
	{
		$this->defaultConfig = new Zend_Config_Ini($this->pathIniFileDefaultConfig, null, true);
		if(!Zend_Loader::isReadable($this->pathIniFileUserConfig))
		{
			throw new Exception("The configuration file {$this->pathIniFileUserConfig} has not been found.");
		}
		$this->userConfig = new Zend_Config_Ini($this->pathIniFileUserConfig, null, true);
		
	}
	/**
	 * At the script shutdown, we save the new configuration file, if the user has set some values 
	 */
	function __destruct()
	{
		if($this->configFileUpdated === true 
			&& $this->doWriteFileWhenUpdated === true)
		{
			$configFile = "; <?php exit; ?> DO NOT REMOVE THIS LINE\n";
			$configFile .= "; file automatically generated or modified by Piwik; you can manually override the default values in global.ini.php by redefining them in this file.\n";
			
			foreach($this->userConfig as $section => $arraySection)
			{
				$arraySection = $arraySection->toArray();
				$configFile .= "[$section]\n";
				foreach($arraySection as $name => $value)
				{
					if(is_numeric($name))
					{
						$name = $section;
						$value = array($value);
					}
					
					if(is_array($value))
					{
						foreach($value as $currentValue)
						{
							$currentValue = htmlentities($currentValue);
							$configFile .= $name."[] = $currentValue\n";
						}
					}
					else
					{
						$value = htmlentities($value);
						if(!is_numeric($value))
						{
							$value = "\"$value\"";
						}
						$configFile .= $name.' = '.$value."\n";						
					}
				}
				$configFile .= "\n";
			}
			chdir($this->correctCwd);
			file_put_contents($this->getDefaultUserConfigPath(), $configFile );
		}
	}
	
	/**
	 * If called, we use the database_tests credentials
	 * @return void
	 */
	public function setTestEnvironment()
	{
		$this->database = $this->database_tests;
	}
	
	/**
	 * Called when setting configuration values eg. 
	 * 	Zend_Registry::get('config')->superuser = $_SESSION['superuser_infos'];
	 *
	 * The values will be saved in the configuration file at the end of the script @see __destruct()
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		$this->cachedConfigArray = array();
		$this->checkWritePermissionOnFile();
		if(is_null($this->userConfig))
		{
			$this->userConfig = new Zend_Config(array(), true);
		}
		if(is_array($value) 
			|| $this->userConfig->$name != $value)
		{
			$this->configFileUpdated = true;
		}
		$this->userConfig->$name = $value;
	}
	
	protected function checkWritePermissionOnFile() 
	{
		static $enoughPermission = null;
		
		if(is_null($enoughPermission))
		{
			if($this->doWriteFileWhenUpdated)
			{
				Piwik::checkDirectoriesWritableOrDie( array('/config') );
			}
			$enoughPermission = true;
		}
		return $enoughPermission;
	}
	
	protected $cachedConfigArray = array();
	
	/**
	 * Loop through the Default and the User configuration objects and cache them in arrays.
	 * This slightly helps reducing the Zend overhead when accessing config entries hundreds of times.
	 * @return void
	 */
	protected function cacheConfigArray()
	{
		$allSections = array(); 
		foreach($this->defaultConfig as $sectionName => $valueInDefaultConfig)
		{
			$allSections[] = $sectionName;
		}
		foreach($this->userConfig as $sectionName => $valueInUserConfig)
		{
			$allSections[] = $sectionName;
		}
		$allSections = array_unique($allSections);
		
		foreach($allSections as $sectionName)
		{
			$section = array();
			if(($valueInDefaultConfig = $this->defaultConfig->$sectionName) !== null)
			{
				$valueInDefaultConfig = $valueInDefaultConfig->toArray();
				$section = array_merge($section, $valueInDefaultConfig);
			}
			if( !is_null($this->userConfig)
				&& null !== ($valueInUserConfig = $this->userConfig->$sectionName))
			{
				$valueInUserConfig = $valueInUserConfig->toArray();
				foreach($valueInUserConfig as $name => &$value)
				{
					if(is_array($value)) 
					{
						$value = array_map("html_entity_decode", $value);
					} 
					else 
					{
						$value = html_entity_decode($value);
					}
				}
				$section = array_merge($section, $valueInUserConfig);
			}
			$this->cachedConfigArray[$sectionName] = new Zend_Config($section);
		}
	}
	
	/**
	 * Called when getting a configuration value, eg. 	Zend_Registry::get('config')->superuser->login
	 *
	 * @param string $name
	 * @return mixed value 
	 * 
	 * @throws exception if the value was not found in the configuration file
	 */
	public function __get($name)
	{
		if(empty($this->cachedConfigArray))
		{
			$this->cacheConfigArray();
		}
		return $this->cachedConfigArray[$name];
	}
}
