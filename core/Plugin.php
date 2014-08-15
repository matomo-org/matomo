<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Cache\PersistentCache;
use Piwik\Plugin\Dependency;
use Piwik\Plugin\MetadataLoader;

/**
 * @see Piwik\Plugin\MetadataLoader
 */
require_once PIWIK_INCLUDE_PATH . '/core/Plugin/MetadataLoader.php';

/**
 * Base class of all Plugin Descriptor classes.
 *
 * Any plugin that wants to add event observers to one of Piwik's {@hook # hooks},
 * or has special installation/uninstallation logic must implement this class.
 * Plugins that can specify everything they need to in the _plugin.json_ files,
 * such as themes, don't need to implement this class.
 *
 * Class implementations should be named after the plugin they are a part of
 * (eg, `class UserCountry extends Plugin`).
 *
 * ### Plugin Metadata
 *
 * In addition to providing a place for plugins to install/uninstall themselves
 * and add event observers, this class is also responsible for loading metadata
 * found in the plugin.json file.
 *
 * The plugin.json file must exist in the root directory of a plugin. It can
 * contain the following information:
 *
 * - **description**: An internationalized string description of what the plugin
 *                    does.
 * - **homepage**: The URL to the plugin's website.
 * - **authors**: A list of author arrays with keys for 'name', 'email' and 'homepage'
 * - **license**: The license the code uses (eg, GPL, MIT, etc.).
 * - **license_homepage**: URL to website describing the license used.
 * - **version**: The plugin version (eg, 1.0.1).
 * - **theme**: `true` or `false`. If `true`, the plugin will be treated as a theme.
 *
 * ### Examples
 *
 * **How to extend**
 *
 *     use Piwik\Common;
 *     use Piwik\Plugin;
 *     use Piwik\Db;
 *
 *     class MyPlugin extends Plugin
 *     {
 *         public function getListHooksRegistered()
 *         {
 *             return array(
 *                 'API.getReportMetadata' => 'getReportMetadata',
 *                 'Another.event'         => array(
 *                                                'function' => 'myOtherPluginFunction',
 *                                                'after'    => true // executes this callback after others
 *                                            )
 *             );
 *         }
 *
 *         public function install()
 *         {
 *             Db::exec("CREATE TABLE " . Common::prefixTable('mytable') . "...");
 *         }
 *
 *         public function uninstall()
 *         {
 *             Db::exec("DROP TABLE IF EXISTS " . Common::prefixTable('mytable'));
 *         }
 *
 *         public function getReportMetadata(&$metadata)
 *         {
 *             // ...
 *         }
 *
 *         public function myOtherPluginFunction()
 *         {
 *             // ...
 *         }
 *     }
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
     * As the cache is used quite often we avoid having to create instances all the time. We reuse it which is not
     * perfect but efficient. If the cache is used we need to make sure to call setCacheKey() before usage as there
     * is maybe a different key set since last usage.
     *
     * @var PersistentCache
     */
    private $cache;

    /**
     * Constructor.
     *
     * @param string|bool $pluginName A plugin name to force. If not supplied, it is set
     *                                to the last part of the class name.
     * @throws \Exception If plugin metadata is defined in both the getInformation() method
     *                    and the **plugin.json** file.
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

        $this->cache = new PersistentCache('Plugin' . $pluginName);
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
     * Returns plugin information, including:
     *
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
     * @deprecated
     */
    public function getInformation()
    {
        return $this->pluginInformation;
    }

    /**
     * Returns a list of hooks with associated event observers.
     *
     * Derived classes should use this method to associate callbacks with events.
     *
     * @return array eg,
     *
     *                   array(
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
     * This method is executed after a plugin is loaded and translations are registered.
     * Useful for initialization code that uses translated strings.
     */
    public function postLoad()
    {
        return;
    }

    /**
     * Installs the plugin. Derived classes should implement this class if the plugin
     * needs to:
     *
     * - create tables
     * - update existing tables
     * - etc.
     *
     * @throws \Exception if installation of fails for some reason.
     */
    public function install()
    {
        return;
    }

    /**
     * Uninstalls the plugins. Derived classes should implement this method if the changes
     * made in {@link install()} need to be undone during uninstallation.
     *
     * In most cases, if you have an {@link install()} method, you should provide
     * an {@link uninstall()} method.
     *
     * @throws \Exception if uninstallation of fails for some reason.
     */
    public function uninstall()
    {
        return;
    }

    /**
     * Executed every time the plugin is enabled.
     */
    public function activate()
    {
        return;
    }

    /**
     * Executed every time the plugin is disabled.
     */
    public function deactivate()
    {
        return;
    }

    /**
     * Returns the plugin version number.
     *
     * @return string
     */
    final public function getVersion()
    {
        $info = $this->getInformation();
        return $info['version'];
    }

    /**
     * Returns `true` if this plugin is a theme, `false` if otherwise.
     *
     * @return bool
     */
    public function isTheme()
    {
        $info = $this->getInformation();
        return !empty($info['theme']) && (bool)$info['theme'];
    }

    /**
     * Returns the plugin's base class name without the namespace,
     * e.g., `"UserCountry"` when the plugin class is `"Piwik\Plugins\UserCountry\UserCountry"`.
     *
     * @return string
     */
    final public function getPluginName()
    {
        return $this->pluginName;
    }

    /**
     * Tries to find a component such as a Menu or Tasks within this plugin.
     *
     * @param string $componentName      The name of the component you want to look for. In case you request a
     *                                   component named 'Menu' it'll look for a file named 'Menu.php' within the
     *                                   root of the plugin folder that implements a class named
     *                                   Piwik\Plugin\$PluginName\Menu . If such a file exists but does not implement
     *                                   this class it'll silently ignored.
     * @param string $expectedSubclass   If not empty, a check will be performed whether a found file extends the
     *                                   given subclass. If the requested file exists but does not extend this class
     *                                   a warning will be shown to advice a developer to extend this certain class.
     *
     * @return \stdClass|null  Null if the requested component does not exist or an instance of the found
     *                         component.
     */
    public function findComponent($componentName, $expectedSubclass)
    {
        $this->cache->setCacheKey('Plugin' . $this->pluginName . $componentName . $expectedSubclass);

        $componentFile = sprintf('%s/plugins/%s/%s.php', PIWIK_INCLUDE_PATH, $this->pluginName, $componentName);

        if ($this->cache->has()) {
            $klassName = $this->cache->get();

            if (empty($klassName)) {
                return; // might by "false" in case has no menu, widget, ...
            }

            if (file_exists($componentFile)) {
                include_once $componentFile;
            }

        } else {
            $this->cache->set(false); // prevent from trying to load over and over again for instance if there is no Menu for a plugin

            if (!file_exists($componentFile)) {
                return;
            }

            require_once $componentFile;

            $klassName = sprintf('Piwik\\Plugins\\%s\\%s', $this->pluginName, $componentName);

            if (!class_exists($klassName)) {
                return;
            }

            if (!empty($expectedSubclass) && !is_subclass_of($klassName, $expectedSubclass)) {
                Log::warning(sprintf('Cannot use component %s for plugin %s, class %s does not extend %s',
                    $componentName, $this->pluginName, $klassName, $expectedSubclass));
                return;
            }

            $this->cache->set($klassName);
        }

        return new $klassName;
    }

    public function findMultipleComponents($directoryWithinPlugin, $expectedSubclass)
    {
        $this->cache->setCacheKey('Plugin' . $this->pluginName . $directoryWithinPlugin . $expectedSubclass);

        if ($this->cache->has()) {
            $components = $this->cache->get();

            if($this->includeComponents($components)) {
                return $components;
            } else {
                // problem including one cached file, refresh cache
            }
        }

        $components = $this->doFindMultipleComponents($directoryWithinPlugin, $expectedSubclass);

        $this->cache->set($components);

        return $components;
    }

    /**
     * Detect whether there are any missing dependencies.
     *
     * @param null $piwikVersion Defaults to the current Piwik version
     * @return bool
     */
    public function hasMissingDependencies($piwikVersion = null)
    {
        $requirements = $this->getMissingDependencies($piwikVersion);

        return !empty($requirements);
    }

    public function getMissingDependencies($piwikVersion = null)
    {
        if (empty($this->pluginInformation['require'])) {
            return array();
        }

        $dependency = new Dependency();

        if (!is_null($piwikVersion)) {
            $dependency->setPiwikVersion($piwikVersion);
        }

        return $dependency->getMissingDependencies($this->pluginInformation['require']);
    }

    /**
     * Extracts the plugin name from a backtrace array. Returns `false` if we can't find one.
     *
     * @param array $backtrace The result of {@link debug_backtrace()} or
     *                         [Exception::getTrace()](http://www.php.net/manual/en/exception.gettrace.php).
     * @return string|false
     */
    public static function getPluginNameFromBacktrace($backtrace)
    {
        foreach ($backtrace as $tracepoint) {
            // try and discern the plugin name
            if (isset($tracepoint['class'])) {
                $className = self::getPluginNameFromNamespace($tracepoint['class']);
                if ($className) {
                    return $className;
                }
            }
        }
        return false;
    }

    /**
     * Extracts the plugin name from a namespace name or a fully qualified class name. Returns `false`
     * if we can't find one.
     *
     * @param string $namespaceOrClassName The namespace or class string.
     * @return string|false
     */
    public static function getPluginNameFromNamespace($namespaceOrClassName)
    {
        if (preg_match("/Piwik\\\\Plugins\\\\([a-zA-Z_0-9]+)\\\\/", $namespaceOrClassName, $matches)) {
            return $matches[1];
        } else {
            return false;
        }
    }

    /**
     * @param $directoryWithinPlugin
     * @param $expectedSubclass
     * @return array
     */
    private function doFindMultipleComponents($directoryWithinPlugin, $expectedSubclass)
    {
        $components = array();

        $baseDir = PIWIK_INCLUDE_PATH . '/plugins/' . $this->pluginName . '/' . $directoryWithinPlugin;
        $files   = Filesystem::globr($baseDir, '*.php');

        foreach ($files as $file) {
            require_once $file;

            $fileName  = str_replace(array($baseDir . '/', '.php'), '', $file);
            $klassName = sprintf('Piwik\\Plugins\\%s\\%s\\%s', $this->pluginName, $directoryWithinPlugin, str_replace('/', '\\', $fileName));

            if (!class_exists($klassName)) {
                continue;
            }

            if (!empty($expectedSubclass) && !is_subclass_of($klassName, $expectedSubclass)) {
                continue;
            }

            $klass = new \ReflectionClass($klassName);

            if ($klass->isAbstract()) {
                continue;
            }

            $components[$file] = $klassName;
        }
        return $components;
    }

    /**
     * @param $components
     * @return bool true if all files were included, false if any file cannot be read
     */
    private function includeComponents($components)
    {
        foreach ($components as $file => $klass) {
            if (!is_readable($file)) {
                return false;
            }
        }
        foreach ($components as $file => $klass) {
            include_once $file;
        }
        return true;
    }
}
