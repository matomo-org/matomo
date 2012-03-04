<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * Backward compatibility ayer
 *
 * @todo remove this in 2.0
 * @since 1.7
 * @deprecated 1.7
 * @see Piwik::createConfigObject()
 *
 * @package Piwik
 * @subpackage Piwik_Config
 */
class Piwik_Config_Compat_Array
{
	private $data;

	/**
	 * Constructor
	 *
	 * @param array $data configuration section
	 */
	public function __construct(array $data)
	{
		$this->data = $data;
	}

	/**
	 * Get value by name
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		$tmp = $this->data[$name];
		return is_array($tmp) ? new Piwik_Config_Compat_Array($tmp) : $tmp;
	}

	/**
	 * Set name, value pair
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		if (is_object($value) && get_class($value) == 'Piwik_Config_Compat_Array')
		{
			$value = $value->toArray();
		}

		$this->data[$name] = $value;
	}

	/**
	 * Convert object to array
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->data;
	}
}

class Piwik_Config_Compat
{
	private $config;
	private $data;
	private $enabled;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->config = Piwik_Config_Writer::getInstance();
		$this->data = array();
		$this->enabled = true;
	}

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		if ($this->enabled)
		{
			$this->config->forceSave();
			Piwik_Config::getInstance()->clear();
		}
		else
		{
			$this->config->clear();
		}
	}

	/**
	 * Get value by name
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		if (!isset($this->data[$name]))
		{
			$this->data[$name] = $this->config->__get($name);
		}

		$tmp = $this->data[$name];
		return is_array($tmp) ? new Piwik_Config_Compat_Array($tmp) : $tmp;
	}

	/**
	 * Set name, value pair
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		if (is_object($value) && get_class($value) == 'Piwik_Config_Compat_Array')
		{
			$value = $value->toArray();
		}

		$this->config->__set($name, $value);
	}

	/**
	 * Disable saving of configuration changes
	 */
	public function disableSavingConfigurationFileUpdates()
	{
		$this->enabled = false;
	}
}
