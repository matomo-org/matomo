<?php
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
	
	private function __construct() 
	{
	}

	public function get($name)
	{
		$this->autoload();
		if(isset($this->all[$name]))
		{
			return $this->all[$name];
		}
		$value = Piwik_FetchOne( 'SELECT option_value 
							FROM ' . Piwik::prefixTable('option') . ' 
							WHERE option_name = ?', $name);
		if($value === false)
		{
			return false;
		}
		$this->all[$name] = $value;
		return $value;
	}
	
	public function set($name, $value, $autoload = 0)
	{
		$autoload = (int)$autoload;
		Piwik_Query('INSERT INTO '. Piwik::prefixTable('option') . ' (option_name, option_value, autoload) '.
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
		try {
			$all = Piwik_FetchAll('SELECT option_value, option_name ' .
									' FROM '. Piwik::prefixTable('option') . 
									' WHERE autoload = 1');
		} catch(Exception $e) {
			// this would fail for users who upgraded between 0.2.10 and 0.2.13 where option table didn't have the autoload field yet
		}
		foreach($this->all as $option)
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

function Piwik_UpdateOption($name, $value, $autoload = 0)
{
	Piwik_Option::getInstance()->set($name, $value, $autoload);
}
