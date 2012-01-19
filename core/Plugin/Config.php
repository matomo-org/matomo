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
 * Read / write local plugin-specific configuration
 * 
 * @package Piwik
 */
class Piwik_Plugin_Config
{
	private $pluginName;
	private $configFileName;

	/**
	 * Constructor
	 *
	 * @param string $pluginName
	 * @param string $configFileName
	 */
	public function __construct($pluginName, $configFileName = 'local.config.php')
	{
		$this->pluginName = $pluginName;
		$this->configFileName = $configFileName;
	}

	/**
	 * Load local plugin configuration
	 *
	 * @return array
	 */
	public function load()
	{
		$pluginConfig = @include(PIWIK_INCLUDE_PATH . '/plugins/' . $this->pluginName . '/config/' . $this->configFileName);

		return $pluginConfig;
	}

	/**
	 * Store local plugin configuration
	 *
	 * @param array $pluginConfig
	 */
	public function store($pluginConfig)
	{
		file_put_contents(PIWIK_INCLUDE_PATH . '/plugins/' . $this->pluginName . '/config/' . $this->configFileName, "<?php\nreturn ".var_export($pluginConfig, true).";\n");
	}
}
