<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\Container\StaticContainer;
use Piwik\Plugin\Dependency;
use Piwik\Plugin\Manager;
use Piwik\Plugin\MetadataLoader;

if (!class_exists('Piwik\Plugin')) {
  
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
 *         public function registerEvents()
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
     * perfect but efficient. If the cache is used we need to make sure to call setId() before usage as there
     * is maybe a different key set since last usage.
     *
     * @var \Matomo\Cache\Eager
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

        $cacheId = 'Plugin' . $pluginName . 'Metadata';
        $cache = Cache::getEagerCache();

        if ($cache->contains($cacheId)) {
            $this->pluginInformation = $cache->fetch($cacheId);
        } else {
            $this->reloadPluginInformation();

            $cache->save($cacheId, $this->pluginInformation);
        }
    }

    public function reloadPluginInformation()
    {
        $metadataLoader = new MetadataLoader($this->pluginName);
        $this->pluginInformation = $metadataLoader->load();

        if ($this->hasDefinedPluginInformationInPluginClass() && $metadataLoader->hasPluginJson()) {
            throw new \Exception('Plugin ' . $this->pluginName . ' has defined the method getInformation() and as well as having a plugin.json file. Please delete the getInformation() method from the plugin class. Alternatively, you may delete the plugin directory from plugins/' . $this->pluginName);
        }
    }

    private function createCacheIfNeeded()
    {
        if (is_null($this->cache)) {
            $this->cache = Cache::getEagerCache();
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
     * Returns plugin information, including:
     *
     * - 'description' => string        // 1-2 sentence description of the plugin
     * - 'author' => string             // plugin author
     * - 'author_homepage' => string    // author homepage URL (or email "mailto:youremail@example.org")
     * - 'homepage' => string           // plugin homepage URL
     * - 'license' => string            // plugin license
     * - 'version' => string            // plugin version number; examples and 3rd party plugins must not use Version::VERSION; 3rd party plugins must increment the version number with each plugin release
     * - 'theme' => bool                // Whether this plugin is a theme (a theme is a plugin, but a plugin is not necessarily a theme)
     *
     * @return array
     */
    public function getInformation()
    {
        return $this->pluginInformation;
    }

    final public function isPremiumFeature()
    {
        return !empty($this->pluginInformation['price']['base']);
    }

    /**
     * Returns a list of events with associated event observers.
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
     * @since 2.15.0
     */
    public function registerEvents()
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
     * Defines whether the whole plugin requires a working internet connection
     * If set to true, the plugin will be automatically unloaded if `enable_internet_features` is 0,
     * even if the plugin is activated
     *
     * @return bool
     */
    public function requiresInternetConnection()
    {
        return false;
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
     * @return string|null  Null if the requested component does not exist or an instance of the found
     *                         component.
     */
    public function findComponent($componentName, $expectedSubclass)
    {
        $this->createCacheIfNeeded();

        $cacheId = 'Plugin' . $this->pluginName . $componentName . $expectedSubclass;

        $pluginsDir = Manager::getPluginDirectory($this->pluginName);

        $componentFile = sprintf('%s/%s.php', $pluginsDir, $componentName);

        if ($this->cache->contains($cacheId)) {
            $classname = $this->cache->fetch($cacheId);

            if (empty($classname)) {
                return null; // might by "false" in case has no menu, widget, ...
            }

            if (file_exists($componentFile)) {
                include_once $componentFile;
            }
        } else {
            $this->cache->save($cacheId, false); // prevent from trying to load over and over again for instance if there is no Menu for a plugin

            if (!file_exists($componentFile)) {
                return null;
            }

            require_once $componentFile;

            $classname = sprintf('Piwik\\Plugins\\%s\\%s', $this->pluginName, $componentName);

            if (!class_exists($classname)) {
                return null;
            }

            if (!empty($expectedSubclass) && !is_subclass_of($classname, $expectedSubclass)) {
                Log::warning(sprintf('Cannot use component %s for plugin %s, class %s does not extend %s',
                    $componentName, $this->pluginName, $classname, $expectedSubclass));
                return null;
            }

            $this->cache->save($cacheId, $classname);
        }

        return $classname;
    }

    public function findMultipleComponents($directoryWithinPlugin, $expectedSubclass)
    {
        $this->createCacheIfNeeded();

        $cacheId = 'Plugin' . $this->pluginName . $directoryWithinPlugin . $expectedSubclass;

        if ($this->cache->contains($cacheId)) {
            $components = $this->cache->fetch($cacheId);

            if ($this->includeComponents($components)) {
                return $components;
            } else {
                // problem including one cached file, refresh cache
            }
        }

        $components = $this->doFindMultipleComponents($directoryWithinPlugin, $expectedSubclass);

        $this->cache->save($cacheId, $components);

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

        $dependency = $this->makeDependency($piwikVersion);
        return $dependency->getMissingDependencies($this->pluginInformation['require']);
    }

    /**
     * Returns a string (translated) describing the missing requirements for this plugin and the given Piwik version
     *
     * @param string $piwikVersion
     * @return string "AnonymousPiwikUsageMeasurement requires PIWIK >=3.0.0"
     */
    public function getMissingDependenciesAsString($piwikVersion = null)
    {
        if ($this->requiresInternetConnection() && !SettingsPiwik::isInternetEnabled()) {
            return Piwik::translate('CorePluginsAdmin_PluginRequiresInternet');
        }

        if (empty($this->pluginInformation['require'])) {
            return '';
        }
        $dependency = $this->makeDependency($piwikVersion);

        $missingDependencies = $dependency->getMissingDependencies($this->pluginInformation['require']);

        if(empty($missingDependencies)) {
            return '';
        }

        $causedBy = array();
        foreach ($missingDependencies as $dependency) {
            $causedBy[] = ucfirst($dependency['requirement']) . ' ' . $dependency['causedBy'];
        }

        return Piwik::translate("CorePluginsAdmin_PluginRequirement", array(
            $this->getPluginName(),
            implode(', ', $causedBy)
        ));
    }

    /**
     * Schedules re-archiving of this plugin's reports from when this plugin was last
     * deactivated to now. If the last time core:archive was run is earlier than the
     * plugin's last deactivation time, then we use that time instead.
     *
     * Note: this only works for CLI archiving setups.
     *
     * Note: the time frame is limited by the `[General] rearchive_reports_in_past_last_n_months`
     * INI config value.
     *
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function schedulePluginReArchiving()
    {
        $lastDeactivationTime = $this->getPluginLastDeactivationTime();

        $dateTime = null;

        $lastCronArchiveTime = (int) Option::get(CronArchive::OPTION_ARCHIVING_FINISHED_TS);
        if (empty($lastCronArchiveTime)) {
            $dateTime = $lastDeactivationTime;
        } else if (empty($lastDeactivationTime)) {
            $dateTime = null; // use default earliest time
        } else {
            $lastCronArchiveTime = Date::factory($lastCronArchiveTime);
            $dateTime = $lastDeactivationTime->isEarlier($lastCronArchiveTime) ? $lastDeactivationTime : $lastCronArchiveTime;
        }

        if (empty($dateTime)) { // sanity check
            $dateTime = null;
        }

        $archiveInvalidator = StaticContainer::get(ArchiveInvalidator::class);
        $archiveInvalidator->scheduleReArchiving('all', $this->getPluginName(), $report = null, $dateTime);
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
        if ($namespaceOrClassName && preg_match("/Piwik\\\\Plugins\\\\([a-zA-Z_0-9]+)\\\\/", $namespaceOrClassName, $matches)) {
            return $matches[1];
        } else {
            return false;
        }
    }

    /**
     * Override this method in your plugin class if you want your plugin to be loaded during tracking.
     *
     * Note: If you define your own dimension or handle a tracker event, your plugin will automatically
     * be detected as a tracker plugin.
     *
     * @return bool
     * @internal
     */
    public function isTrackerPlugin()
    {
        return false;
    }

    /**
     * @return Date|null
     * @throws \Exception
     */
    public function getPluginLastActivationTime()
    {
        $optionName = Manager::LAST_PLUGIN_ACTIVATION_TIME_OPTION_PREFIX . $this->pluginName;
        $time = Option::get($optionName);
        if (empty($time)) {
            return null;
        }
        return Date::factory((int) $time);
    }

    /**
     * @return Date|null
     * @throws \Exception
     */
    public function getPluginLastDeactivationTime()
    {
        $optionName = Manager::LAST_PLUGIN_DEACTIVATION_TIME_OPTION_PREFIX . $this->pluginName;
        $time = Option::get($optionName);
        if (empty($time)) {
            return null;
        }
        return Date::factory((int) $time);
    }

    /**
     * @param $directoryWithinPlugin
     * @param $expectedSubclass
     * @return array
     */
    private function doFindMultipleComponents($directoryWithinPlugin, $expectedSubclass)
    {
        $components = array();

        $pluginsDir = Manager::getPluginDirectory($this->pluginName);
        $baseDir = $pluginsDir . '/' . $directoryWithinPlugin;

        $files   = Filesystem::globr($baseDir, '*.php');

        foreach ($files as $file) {
            require_once $file;

            $fileName  = str_replace(array($baseDir . '/', '.php'), '', $file);
            $klassName = sprintf('Piwik\\Plugins\\%s\\%s\\%s', $this->pluginName, str_replace('/', '\\', $directoryWithinPlugin), str_replace('/', '\\', $fileName));

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

    /**
     * @param $piwikVersion
     * @return Dependency
     */
    private function makeDependency($piwikVersion)
    {
        $dependency = new Dependency();

        if (!is_null($piwikVersion)) {
            $dependency->setPiwikVersion($piwikVersion);
        }
        return $dependency;
    }

    /**
     * Get all changes for this plugin
     *
     * @return array    Array of changes
     *                  [{"title":"abc","description":"xyz","linkName":"def","link":"https://link","version":"1.2.3"}]
     */
    public function getChanges()
    {
        $file = Manager::getPluginDirectory($this->pluginName).'/changes.json';
        if (file_exists($file)) {
            $json = file_get_contents($file);
            if ($json) {
                $changes = json_decode($json, true);
                if ($changes && is_array($changes)) {
                    return array_reverse($changes);
                }
            }
        }
        return [];
    }

}

}
    
