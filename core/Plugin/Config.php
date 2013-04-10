<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
     * @param string $pluginName      name of the plugin
     * @param string $configFileName  name of the plugin file; defaults to local.config.php
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
        $pluginConfig = @include(PIWIK_USER_PATH . '/plugins/' . $this->pluginName . '/config/' . $this->configFileName);

        return $pluginConfig;
    }

    /**
     * Store local plugin configuration
     *
     * @param array $pluginConfig
     */
    public function store($pluginConfig)
    {
        file_put_contents(PIWIK_USER_PATH . '/plugins/' . $this->pluginName . '/config/' . $this->configFileName, "<?php\nreturn " . var_export($pluginConfig, true) . ";\n");
    }
}
