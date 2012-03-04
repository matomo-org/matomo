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
 * This class extends the base Piwik_Config class to save in-memory changes to the local
 * configuration file.
 *
 * Example reading a value from the configuration:
 *
 *    $minValue = Piwik_Config_Writer::getInstance()->General['minimum_memory_limit'];
 *
 * will read the value minimum_memory_limit under the [General] section of the config file.
 *
 * Example setting a section in the configuration:
 *
 *    $brandingConfig = array(
 *        'use_custom_logo' => 1,
 *    );
 *    Piwik_Config_Writer::getInstance()->setConfigSection('branding', $brandingConfig);
 *
 * Example setting an option within a section in the configuration:
 *
 *    Piwik_Config_Writer::getInstance()->setConfigOption('branding', 'use_custom_logo', '1');
 *
 * @package Piwik
 * @subpackage Piwik_Config
 */
class Piwik_Config_Writer extends Piwik_Config
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
	 * Read configuration files into memory
	 *
	 * @throws Exception if file is not read/writable or contains no configuration
	 */
	public function init()
	{
		Piwik::checkDirectoriesWritableOrDie( array('/config/') );

		if(file_exists($this->pathLocal)
			&& !is_writable($this->pathLocal))
		{
			throw new Exception(Piwik_TranslateException('General_ExceptionUnwritableFileDisabledMethod', array($this->pathLocal)));
		}

		parent::init();
	}

	/**
	 * Comparison function
	 *
	 * @param mixed $elem1
	 * @param mixed $elem2
	 * @return int;
	 */
	static function compareElements($elem1, $elem2)
	{
		if (is_array($elem1)) {
			if (is_array($elem2))
			{
				return strcmp(serialize($elem1), serialize($elem2));
			}
			return 1;
		}
		if (is_array($elem2))
			return -1;

		if ((string)$elem1 === (string)$elem2)
			return 0;

		return ((string)$elem1 > (string)$elem2) ? 1 : -1;
	}

	/**
	 * Compare arrays and return difference, such that:
	 *
	 *     $modified = array_merge($original, $difference);
	 *
	 * @param array $original original array
	 * @param array $modified modified array
	 * @return array differences between original and modified
	 */
	public function array_unmerge($original, $modified)
	{
		// return key/value pairs for keys in $modified but not in $original
		// return key/value pairs for keys in both $modified and $original, but values differ
		// ignore keys that are in $original but not in $modified

		return array_udiff_assoc($modified, $original, array(__CLASS__, 'compareElements'));
	}

	/**
	 * Encode HTML entities
	 *
	 * @param mixed $values
	 * @return mixed
	 */
	protected function encodeValues($values)
	{
		if(is_array($values))
		{
			foreach($values as &$value)
			{
				$value = $this->encodeValues($value);
			}
		}
		else
		{
			$values = htmlentities($values, ENT_COMPAT);
		}
		return $values;
	}

	/**
	 * Write user configuration file
	 *
	 * @param array $configLocal
	 * @param array $configGlobal
	 * @param array $configCache
	 * @param string $pathLocal
	 */
	public function writeConfig($configLocal, $configGlobal, $configCache, $pathLocal)
	{
		$dirty = false;

		$output = "; <?php exit; ?> DO NOT REMOVE THIS LINE\n";
		$output .= "; file automatically generated or modified by Piwik; you can manually override the default values in global.ini.php by redefining them in this file.\n";

		if ($configCache)
		{
			foreach($configLocal as $name => $section)
			{
				if (!isset($configCache[$name]))
				{
					$configCache[$name] = $this->decodeValues($section);
				}
			}

			foreach($configCache as $name => $section)
			{
				$configLocal = $this->array_unmerge($configGlobal[$name], $configCache[$name]);
				if (count($configLocal) == 0)
				{
					continue;
				}

				$dirty = true;

				$output .= "[$name]\n";

				foreach($configLocal as $name => $value)
				{
					$value = $this->encodeValues($value);

					if(is_numeric($name))
					{
						$name = $section;
						$value = array($value);
					}

					if(is_array($value))
					{
						foreach($value as $currentValue)
						{
							$output .= $name."[] = \"$currentValue\"\n";
						}
					}
					else
					{
						if(!is_numeric($value))
						{
							$value = "\"$value\"";
						}
						$output .= $name.' = '.$value."\n";
					}
				}
				$output .= "\n";
			}

			if ($dirty)
			{
				@file_put_contents($pathLocal, $output );
			}
		}

		$this->clear();
	}

	/**
	 * Force save
	 */
	public function forceSave()
	{
		$this->writeConfig($this->configLocal, $this->configGlobal, $this->configCache, $this->pathLocal);
	}

	/**
	 * At the script shutdown, we save the new configuration file, if the user has set some values
	 */
	public function __destruct()
	{
		$this->forceSave();
	}
}
