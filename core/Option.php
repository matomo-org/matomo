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
 * Piwik_Option provides a very simple mechanism to save/retrieve key-values pair
 * from the database (persistent key-value datastore).
 * 
 * This is useful to save Piwik-wide preferences, configuration values.
 * 
 * This should not be used to store user preferences nor website preferences. 
 *
 * @package Piwik
 */
class Piwik_Option
{
	private $all = array();

	static private $instance = null;
	/**
	 * @return Piwik_Option
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
	
	private function __construct() {}

	/**
	 * Returns the option value for the requested option $name
	 *
	 * @param string $name 
	 * @return string|false if not found
	 */
	public function get($name)
	{
		$this->autoload();
		if(isset($this->all[$name]))
		{
			return $this->all[$name];
		}
		$value = Piwik_FetchOne( 'SELECT option_value 
							FROM `' . Piwik::prefixTable('option') . '`
							WHERE option_name = ?', $name);
		if($value === false)
		{
			return false;
		}
		$this->all[$name] = $value;
		return $value;
	}
	
	/**
	 * Sets the option value in the database
	 *
	 * @param string $name
	 * @param string $value
	 * @param int $autoload if set to 1, this option value will be automatically loaded; should be set to 1 for options that will always be used in the Piwik request.
	 */
	public function set($name, $value, $autoload = 0)
	{
		$autoload = (int)$autoload;
		Piwik_Query('INSERT INTO `'. Piwik::prefixTable('option') . '` (option_name, option_value, autoload) '.
					' VALUES (?, ?, ?) '.
					' ON DUPLICATE KEY UPDATE option_value = ?', 
					array($name, $value, $autoload, $value));
		$this->all[$name] = $value;
	}
	
	private function autoload()
	{
		static $loaded = false;
		if($loaded)
		{
			return;
		}
		$all = Piwik_FetchAll('SELECT option_value, option_name
								FROM `'. Piwik::prefixTable('option') . '` 
								WHERE autoload = 1');
		foreach($all as $option)
		{
			$this->all[$option['option_name']] = $option['option_value'];
		}
		$loaded = true;
	}
}

function Piwik_GetOption($name)
{
	return Piwik_Option::getInstance()->get($name);
}

function Piwik_SetOption($name, $value, $autoload = 0)
{
	Piwik_Option::getInstance()->set($name, $value, $autoload);
}
