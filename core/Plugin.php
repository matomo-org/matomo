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
namespace Piwik;

use Piwik\Plugin\MetadataLoader;

/**
 * @see Piwik\Plugin\MetadataLoader
 */
require_once PIWIK_INCLUDE_PATH . '/core/Plugin/MetadataLoader.php';

/**
 * Abstract class to define a Plugin.
 * Any plugin has to at least implement the abstract methods of this class.
 *
 * @package Piwik
 *
 * @api
 */
class Plugin
{
    /**
     * Name of this plugin.
     *
     * @var string
     */
    protected $pluginName;

    /**
     * Holds plugin metadata.
     *
     * @var array
     */
    private $pluginInformation;

    /**
     * Constructor.
     *
     * @param string|bool $pluginName A plugin name to force. If not supplied, it is set
     *                                to last part of the class name.
     *
     * @throws \Exception
     */
    public function __construct($pluginName = false)
    {
        if (empty($pluginName)) {
            $pluginName = explode('\\', get_class($this));
            $pluginName = end($pluginName);
        }
        $this->pluginName = $pluginName;

        $metadataLoader = new MetadataLoader($pluginName);
        $this->pluginInformation = $metadataLoader->load();

        if ($this->hasDefinedPluginInformationInPluginClass() && $metadataLoader->hasPluginJson()) {
            throw new \Exception('Plugin ' . $pluginName . ' has defined the method getInformation() and as well as having a plugin.json file. Please delete the getInformation() method from the plugin class. Alternatively, you may delete the plugin directory from plugins/' . $pluginName);
        }
    }

    private function hasDefinedPluginInformationInPluginClass()
    {
        $myClassName = get_class();
        $pluginClassName = get_class($this);

        if ($pluginClassName == $myClassName) {
            // plugin has not defined its own class
            return false;
        }

        $foo = new \ReflectionMethod(get_class($this), 'getInformation');
        $declaringClass = $foo->getDeclaringClass()->getName();

        return $declaringClass != $myClassName;
    }

    /**
     * Returns the plugin details
     * - 'description' => string        // 1-2 sentence description of the plugin
     * - 'author' => string             // plugin author
     * - 'author_homepage' => string    // author homepage URL (or email "mailto:youremail@example.org")
     * - 'homepage' => string           // plugin homepage URL
     * - 'license' => string            // plugin license
     * - 'license_homepage' => string   // license homepage URL
     * - 'version' => string            // plugin version number; examples and 3rd party plugins must not use Version::VERSION; 3rd party plugins must increment the version number with each plugin release
     * - 'theme' => bool                // Whether this plugin is a theme (a theme is a plugin, but a plugin is not necessarily a theme)
     *
     * @return array
     */
    public function getInformation()
    {
        return $this->pluginInformation;
    }

    /**
     * Returns the list of hooks registered with the methods names
     *
     * @return array eg, array(
     *                       'API.getReportMetadata' => 'myPluginFunction',
     *                       'Another.event'         => array(
     *                                                      'function' => 'myOtherPluginFunction',
     *                                                      'after'    => true // execute after callbacks w/o ordering
     *                                                  )
     *                       'Yet.Another.event'     => array(
     *                                                      'function' => 'myOtherPluginFunction',
     *                                                      'before'   => true // execute before callbacks w/o ordering
     *                                                  )
     *                   )
     */
    public function getListHooksRegistered()
    {
        return array();
    }

    /**
     * Executed after loading plugin and registering translations
     * Useful for code that uses translated strings from the plugin.
     */
    public function postLoad()
    {
        return;
    }

    /**
     * Install the plugin
     * - create tables
     * - update existing tables
     * - etc.
     */
    public function install()
    {
        return;
    }

    /**
     * Remove the created resources during the install
     */
    public function uninstall()
    {
        return;
    }

    /**
     * Executed every time the plugin is enabled
     */
    public function activate()
    {
        return;
    }

    /**
     * Executed every time the plugin is disabled
     */
    public function deactivate()
    {
        return;
    }

    /**
     * Returns the plugin version number
     *
     * @return string
     */
    final public function getVersion()
    {
        $info = $this->getInformation();
        return $info['version'];
    }

    /**
     * Whether this plugin is a theme
     *
     * @return bool
     */
    final public function isTheme()
    {
        $info = $this->getInformation();
        return !empty($info['theme']) && (bool)$info['theme'];
    }

    /**
     * Returns the plugin's base class name without the "Piwik_" prefix,
     * e.g., "UserCountry" when the plugin class is "UserCountry"
     *
     * @return string
     */
    final public function getPluginName()
    {
        return $this->pluginName;
    }

    /**
     * Extracts the plugin name from a backtrace array. Returns false if we can't find one.
     *
     * @param array $backtrace The result of the debug_backtrace() or Exception::getTrace().
     * @return string|false
     */
    public static function getPluginNameFromBacktrace($backtrace)
    {
        foreach ($backtrace as $tracepoint) {
            // try and discern the plugin name
            if (isset($tracepoint['class'])
                && preg_match("/Piwik\\\\Plugins\\\\([a-zA-Z_0-9]+)\\\\/", $tracepoint['class'], $matches)
            ) {
                return $matches[1];
            }
        }
        return false;
    }
}