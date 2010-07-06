<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * Simple class to access the configuration file
 * 
 * This is essentially a simple version of Zend_Config that we wrote 
 * because of performance reasons. 
 * The Tracker module can't afford a dependency with the Zend_Framework.
 * 
 * It's using the php.net/parse_ini_file function to parse the configuration files.
 * It can be used to access both user config.ini.php and piwik global.ini.php config file.
 * 
 * @package Piwik
 * @subpackage Piwik_Tracker
 */
class Piwik_Tracker_Config
{
	static private $instance = null;
	
	/*
	 * For Unit tests, the locally overwritten config/config.ini.php should not interfere with Integration tests.
	 * We therefore overwrite the config files with the default values from global.ini.php when running these tests
	 * @see setTestEnvironment()
	 */
	static public $toRestoreFromGlobalConfig = array('Debug', 'General', 'Tracker');
	
	/**
	 * Returns singleton
	 *
	 * @return Piwik_Tracker_Config
	 */
	static public function getInstance()
	{
		if (self::$instance == null)
		{			
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	/**
	 * Contains configuration files values
	 *
	 * @var array
	 */
	public $config = array();
	protected $initialized = false;
	protected $configGlobal = false;
	protected $configUser = false;
	
	public function init($pathIniFileUser = null, $pathIniFileGlobal = null)
	{
		if(is_null($pathIniFileGlobal))
		{
			$pathIniFileGlobal = PIWIK_USER_PATH . '/config/global.ini.php'; 
		}
		$this->configGlobal = _parse_ini_file($pathIniFileGlobal, true);

		if(is_null($pathIniFileUser))
		{
			$pathIniFileUser = PIWIK_USER_PATH . '/config/config.ini.php'; 
		}
		$this->configUser = _parse_ini_file($pathIniFileUser, true);
		if($this->configUser)
		{
			foreach($this->configUser as $section => &$sectionValues)
			{ 
				foreach($sectionValues as $name => &$value)
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
			}
		}
		$this->initialized = true;
	}
	
	/**
	 * Magic get methods catching calls to $config->var_name
	 * Returns the value if found in the 
	 *
	 * @param string $name
	 * @return mixed The value requested, usually a string
	 * @throws exception if the value requested not found in both files
	 */
	public function __get( $name )
	{
		if(!$this->initialized)
		{
			$this->init();
		}
		
		$section = array();
		if(isset($this->configGlobal[$name]))
		{
			$section = array_merge($section, $this->configGlobal[$name]);
		}
		if(isset($this->configUser[$name]))
		{
			$section = array_merge($section, $this->configUser[$name]);
		}
		if(isset($this->config[$name]))
		{
			$section = array_merge($section, $this->config[$name]);
		}
		return count($section) ? $section : null;
	}
	
	/**
	 * If called, we use the database_tests credentials
	 * and test configuration overrides
	 */
	public function setTestEnvironment()
	{
		foreach(self::$toRestoreFromGlobalConfig as $section) {
			if(isset($this->configGlobal[$section]))
			{
				$this->configUser = $this->configGlobal[$section];
			}
		}
		$this->database = $this->database_tests;
		$this->PluginsInstalled = array();	
	}
}
