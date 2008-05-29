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
	public    $doWriteFileWhenUpdated		= true;
	
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
		return 'config/config.ini.php';
	}

	/**
	 * Builds the Config object, given the optional path for the user INI file
	 * If not specified, it will use the default path
	 *
	 * @param string $pathIniFileUserConfig
	 */
	function __construct($pathIniFileUserConfig = null)
	{
		Zend_Registry::set('config', $this);
		
		$this->pathIniFileDefaultConfig = 'config/global.ini.php';
		if(is_null($pathIniFileUserConfig))
		{	
			$this->pathIniFileUserConfig = self::getDefaultUserConfigPath();
		}
		else
		{
			$this->pathIniFileUserConfig = $pathIniFileUserConfig;
		}
		
		$this->defaultConfig = new Zend_Config_Ini($this->pathIniFileDefaultConfig, null, true);
		
		if(!Zend_Loader::isReadable($this->pathIniFileUserConfig))
		{
			throw new Exception("The configuration file {$this->pathIniFileUserConfig} has not been found.");
		}
		$this->userConfig = new Zend_Config_Ini($this->pathIniFileUserConfig, null, true);
		
		// see http://bugs.php.net/bug.php?id=34206
		$this->correctCwd = getcwd();
	}
	
	/**
	 * At the script shutdown, we save the new configuration file, if the user has set some values 
	 *
	 */
	function __destruct()
	{
		// saves the config file if changed
		if($this->configFileUpdated === true 
			&& $this->doWriteFileWhenUpdated === true)
		{
		
			$configFile = "; <?php exit; ?> DO NOT REMOVE THIS LINE\n";
			$configFile .= "; file automatically generated during the piwik installation process (and updated later by some other modules)\n";
			
			foreach($this->userConfig as $section => $arraySection)
			{
				$arraySection = $arraySection->toArray();
//				print("<pre>saving $section => ".var_export($arraySection,true)." <br>");
				
				$configFile .= "[$section]\n";
				//echo "array section"; var_dump($arraySection);

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
							$configFile .= $name."[] = $currentValue\n";
						}
					}
					else
					{	
						// hack 
						// we add " " around the password because when requesting this data using Zend_Config
						// the toArray removes the " around the value
						if( ($section == 'database' || $section == 'database_tests')
							&& $name == 'password')
						{
							$value = '"'.$value.'"';	
						}
						
						$configFile .= $name." = $value\n";						
					}
				}
				$configFile .= "\n";
			}

			chdir($this->correctCwd);
			file_put_contents($this->getDefaultUserConfigPath(), $configFile );
		}
	}
	
	/**
	 * If called, we use the "testing" environment, which means using the database_tests and log_tests sections 
	 * for DB & Log configuration.
	 * 
	 * @return void
	 *
	 */
	public function setTestEnvironment()
	{
		$this->database = $this->database_tests;
		$this->log = $this->log_tests;
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
		if(!is_null($this->userConfig))
		{
			if($this->userConfig->$name != $value)
			{
				$this->configFileUpdated = true;
			}
			$this->userConfig->$name = $value;
		}
		else
		{
			$this->defaultConfig->$name = $value;
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

