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
 * A lightweight class to access the configuration file(s).
 *
 * For general performance (and specifically, the Tracker), we use deferred (lazy) initialization
 * and cache sections.  We also avoid any dependency on Zend Framework's Zend_Config.
 *
 * We use a parse_ini_file() wrapper to parse the configuration files, in case php's built-in
 * function is disabled.
 *
 * Example reading a value from the configuration:
 *
 *     $minValue = Piwik_Config::getInstance()->General['minimum_memory_limit'];
 *
 * will read the value minimum_memory_limit under the [General] section of the config file.
 *
 * Note: if you want to save your changes, you have to use Piwik_Config_Writer
 *
 * @package Piwik
 * @subpackage Piwik_Config
 */
class Piwik_Config
{
	static private $instance = null;

	/**
	 * Returns the singleton Piwik_Config
	 *
	 * @return Piwik_Config
	 */
	static public function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Enable test environment
	 *
	 * @param string $pathLocal
	 * @param string $pathGlobal
	 */
	public function setTestEnvironment($pathLocal = null, $pathGlobal = null)
	{
		$this->clear();

		if ($pathLocal)
		{
			$this->pathLocal = $pathLocal;
		}

		if ($pathGlobal)
		{
			$this->pathGlobal = $pathGlobal;
		}

		$this->init();
		if(isset($this->configGlobal['database_tests'])
			|| isset($this->configLocal['database_tests']))
		{
			$this->__get('database_tests');
			$this->configCache['database'] = $this->configCache['database_tests'];
		}
	}

	/**
	 * Contains configuration files values
	 *
	 * @var array
	 */
	protected $initialized = false;
	protected $configGlobal = array();
	protected $configLocal = array();
	protected $configCache = array();
	protected $pathGlobal = null;
	protected $pathLocal = null;

	protected function __construct()
	{
		$this->pathGlobal = self::getGlobalConfigPath();
		$this->pathLocal = self::getLocalConfigPath();
	}

	/**
	 * Returns absolute path to the global configuration file
	 *
	 * @return string
	 */
	static public function getGlobalConfigPath()
	{
		return PIWIK_USER_PATH .'/config/global.ini.php';
	}

	/**
	 * Backward compatibility stub
	 *
	 * @todo remove in 2.0
	 * @since 1.7
	 * @deprecated 1.7
	 */
	static public function getDefaultDefaultConfigPath()
	{
		return self::getGlobalConfigPath();
	}

	/**
	 * Returns absolute path to the local configuration file
	 *
	 * @return string
	 */
	static public function getLocalConfigPath()
	{
		return PIWIK_USER_PATH .'/config/config.ini.php';
	}

	/**
	 * Is local configuration file writable?
	 *
	 * @return bool
	 */
	public function isFileWritable()
	{
		return is_writable($this->pathLocal);
	}

	/**
	 * Clear in-memory configuration so it can be reloaded
	 */
	public function clear()
	{
		$this->configGlobal = array();
		$this->configLocal = array();
		$this->configCache = array();
		$this->initialized = false;

		$this->pathGlobal = self::getGlobalConfigPath();
		$this->pathLocal = self::getLocalConfigPath();
	}

	/**
	 * Read configuration from files into memory
	 *
	 * @throws Exception if local config file is not readable; exits for other errors
	 */
	public function init()
	{
		$this->initialized = true;

		// read defaults from global.ini.php
		if(!is_readable($this->pathGlobal))
		{
			Piwik_ExitWithMessage(Piwik_TranslateException('General_ExceptionConfigurationFileNotFound', array($this->pathGlobal)));
		}

		$this->configGlobal = _parse_ini_file($this->pathGlobal, true);
		if(empty($this->configGlobal))
		{
			Piwik_ExitWithMessage(Piwik_TranslateException('General_ExceptionUnreadableFileDisabledMethod', array($this->pathGlobal, "parse_ini_file()")));
		}

		// read the local settings from config.ini.php
		if(!is_readable($this->pathLocal))
		{
			throw new Exception(Piwik_TranslateException('General_ExceptionConfigurationFileNotFound', array($this->pathLocal)));
		}

		$this->configLocal = _parse_ini_file($this->pathLocal, true);
		if(empty($this->configLocal))
		{
			Piwik_ExitWithMessage(Piwik_TranslateException('General_ExceptionUnreadableFileDisabledMethod', array($this->pathLocal, "parse_ini_file()")));
		}
	}

	/**
	 * Decode HTML entities
	 *
	 * @param mixed $values
	 * @return mixed
	 */
	protected function decodeValues($values)
	{
		if(is_array($values))
		{
			foreach($values as &$value)
			{
				$value = $this->decodeValues($value);
			}
		}
		else
		{
			$values = html_entity_decode($values, ENT_COMPAT);
		}
		return $values;
	}

	/**
	 * Magic get methods catching calls to $config->var_name
	 * Returns the value if found in the configuration
	 *
	 * @param string $name
	 * @return string|array The value requested, returned by reference
	 * @throws Exception if the value requested not found in both files
	 */
	public function &__get( $name )
	{
		if(!$this->initialized)
		{
			$this->init();
		}

		// check cache for merged section
		if (isset($this->configCache[$name]))
		{
			$tmp =& $this->configCache[$name];
			return $tmp;
		}

		$section = null;

		// merge corresponding sections from global and local settings
		if(isset($this->configGlobal[$name]))
		{
			$section = $this->configGlobal[$name];
		}
		if(isset($this->configLocal[$name]))
		{
			// local settings override the global defaults
			$section = $section
				? array_merge($section, $this->configLocal[$name])
				: $this->configLocal[$name];
		}

		if ($section === null)
		{
			throw new Exception("Error while trying to read a specific config file entry <b>'$name'</b> from your configuration files.</b>If you just completed a Piwik upgrade, please check that the file config/global.ini.php was overwritten by the latest Piwik version.");
		}

		// cache merged section for later
		$this->configCache[$name] = $this->decodeValues($section);
		$tmp =& $this->configCache[$name];
		return $tmp;
	}

	/**
	 * Set value
	 *
	 * @param string $name This corresponds to the section name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		$this->configCache[$name] = $value;
	}
}
