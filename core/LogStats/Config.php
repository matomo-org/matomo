<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Config.php 450 2008-04-20 22:33:27Z matt $
 * 
 * @package Piwik_LogStats
 */

/**
 * Simple class to access the configuration file
 * 
 * This is essentially a simple version of Zend_Config that we wrote 
 * because of performance reasons. 
 * The LogStats module can't afford a dependency with the Zend_Framework.
 * 
 * It's using the php.net/parse_ini_file function to parse the configuration files.
 * It can be used to access both user config.ini.php and piwik global.ini.php config file.
 * 
 * @package Piwik_LogStats
 */
class Piwik_LogStats_Config
{
	static private $instance = null;
	
	/**
	 * Returns singleton
	 *
	 * @return Piwik_LogStats_Config
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
	
	private function __construct()
	{
		$pathIniFileUser = 'config/config.ini.php';
		$pathIniFileGlobal = 'config/global.ini.php';
		$this->configUser = parse_ini_file($pathIniFileUser, true);
		$this->configGlobal = parse_ini_file($pathIniFileGlobal, true);
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


