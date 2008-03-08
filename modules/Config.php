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
 * TODO rewrite__set __get __destruct
 * tests: install 
 * test dashboard has been installed (means config file written dashboard in PluginsInstalled
 * test activate/deactivate plugins
 * rewrite logic behind saving arrays, very bad at the moment
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
	protected $configFileUpdated 			= false;
	public    $doWriteFileWhenUpdated		= true;
	
	// see http://bugs.php.net/bug.php?id=34206
	protected $correctCwd;
	
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
		
		// see http://bugs.php.net/bug.php?id=34206
		$this->correctCwd = getcwd();
	}
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
						$configFile .= $name." = $value\n";						
					}
				}
				$configFile .= "\n";
			}

			chdir($this->correctCwd);
			file_put_contents($this->getDefaultUserConfigPath(), $configFile );
		}
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

