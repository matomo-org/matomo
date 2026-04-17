<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugin;

use Piwik\Application\Kernel\PluginList;
use Piwik\Cache;
use Piwik\Columns\Dimension;
use Piwik\Common;
use Piwik\Config;
use Piwik\Config as PiwikConfig;
use Piwik\Container\StaticContainer;
use Piwik\Development;
use Piwik\EventDispatcher;
use Piwik\Exception\PluginDeactivatedException;
use Piwik\Exception\PluginNotFoundException;
use Piwik\Filesystem;
use Piwik\Log;
use Piwik\Notification;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugin\Dimension\ConversionDimension;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Settings\Storage as SettingsStorage;
use Piwik\SettingsPiwik;
use Piwik\SettingsServer;
use Piwik\Theme;
use Piwik\Translation\Translator;
use Piwik\Updater;

/**
 * The singleton that manages plugin loading/unloading and installation/uninstallation.
 */
class Manager
{
    public const LAST_PLUGIN_ACTIVATION_TIME_OPTION_PREFIX = 'LastPluginActivation.';
    public const LAST_PLUGIN_DEACTIVATION_TIME_OPTION_PREFIX = 'LastPluginDeactivation.';

    /**
     * @return self
     */
    public static function getInstance()
    {
        return StaticContainer::get('Piwik\Plugin\Manager');
    }

    /**
     * @var list<string>
     */
    protected $pluginsToLoad = [];

    /**
     * @var bool
     */
    protected $doLoadPlugins = true;

    /**
     * @var array<string, string>
     */
    protected static $pluginsToPathCache = [];

    /**
     * @var array<string, string>
     */
    protected static $pluginsToWebRootDirCache = [];

    /**
     * @var Plugin[]|null
     */
    private $pluginsLoadedAndActivated;

    /**
     * @var Plugin[]
     */
    protected $loadedPlugins = [];
    /**
     * Default theme used in Matomo.
     */
    public const DEFAULT_THEME = "Morpheus";

    /**
     * @var bool
     */
    protected $doLoadAlwaysActivatedPlugins = true;

    /**
     * These are always activated and cannot be deactivated
     * @var string[]
     */
    protected static $pluginToAlwaysActivate = array(
        'FeatureFlags',
        'BulkTracking',
        'CoreVue',
        'CoreHome',
        'CoreUpdater',
        'CoreAdminHome',
        'CoreConsole',
        'CorePluginsAdmin',
        'CoreVisualizations',
        'Installation',
        'SitesManager',
        'UsersManager',
        'Intl',
        'API',
        'Proxy',
        'LanguagesManager',
        'WebsiteMeasurable',

        // default Piwik theme, always enabled
        self::DEFAULT_THEME,
    );

    /**
     * @var list<string>
     */
    private $trackerPluginsNotToLoad = [];

    /**
     * @var PluginList
     */
    private $pluginList;

    public function __construct(PluginList $pluginList)
    {
        $this->pluginList = $pluginList;
    }

    /**
     * Loads plugin that are enabled
     * @return void
     */
    public function loadActivatedPlugins()
    {
        $pluginsToLoad = $this->getActivatedPluginsFromConfig();
        if (!SettingsPiwik::isInternetEnabled()) {
            $pluginsToLoad = array_filter($pluginsToLoad, function ($name) {
                $plugin = Manager::makePluginClass($name);
                return !$plugin->requiresInternetConnection();
            });
        }
        $this->loadPlugins($pluginsToLoad);
    }

    /**
     * Called during Tracker
     * @return void
     */
    public function loadCorePluginsDuringTracker()
    {
        $pluginsToLoad = $this->pluginList->getActivatedPlugins();
        $pluginsToLoad = array_diff($pluginsToLoad, $this->getTrackerPluginsNotToLoad());
        $this->loadPlugins($pluginsToLoad);
    }

    /**
     * @return list<string> names of plugins that have been loaded
     */
    public function loadTrackerPlugins()
    {
        $cacheId = 'PluginsTracker';
        $cache = Cache::getEagerCache();

        if ($cache->contains($cacheId)) {
            /** @var list<string> $pluginsTracker */
            $pluginsTracker = $cache->fetch($cacheId);
        } else {
            $this->unloadPlugins();
            $this->loadActivatedPlugins();

            $pluginsTracker = [];

            foreach ($this->loadedPlugins as $pluginName => $plugin) {
                if ($this->isTrackerPlugin($plugin)) {
                    $pluginsTracker[] = $pluginName;
                }
            }

            if (!empty($pluginsTracker)) {
                $cache->save($cacheId, $pluginsTracker);
            }
        }

        if (empty($pluginsTracker)) {
            $this->unloadPlugins();
            return [];
        }

        $pluginsTracker = array_diff($pluginsTracker, $this->getTrackerPluginsNotToLoad());
        $this->doNotLoadAlwaysActivatedPlugins();
        $this->loadPlugins($pluginsTracker);

        // we could simply unload all plugins first before loading plugins but this way it is much faster
        // since we won't have to create each plugin again and we won't have to parse each plugin metadata file
        // again etc
        $this->makeSureOnlyActivatedPluginsAreLoaded();

        return $pluginsTracker;
    }

    /**
     * Do not load the specified plugins (used during testing, to disable Provider plugin)
     * @param list<string> $plugins
     * @return void
     */
    public function setTrackerPluginsNotToLoad($plugins)
    {
        $this->trackerPluginsNotToLoad = $plugins;
    }

    /**
     * Get list of plugins to not load
     *
     * @return list<string>
     */
    public function getTrackerPluginsNotToLoad()
    {
        return $this->trackerPluginsNotToLoad;
    }

    // If a plugin hooks onto at least an event starting with "Tracker.", we load the plugin during tracker
    public const TRACKER_EVENT_PREFIX = 'Tracker.';

    /**
     * @param string $pluginName
     * @return bool
     */
    public function isPluginOfficialAndNotBundledWithCore($pluginName)
    {
        static $gitModules;
        if (empty($gitModules)) {
            $gitModules = file_get_contents(PIWIK_INCLUDE_PATH . '/.gitmodules');
        }
        // All submodules are officially maintained plugins
        return false !== strpos($gitModules, "plugins/" . $pluginName . "\n");
    }

    /**
     * Update Plugins config
     *
     * @param string[] $pluginsToLoad Plugins
     */
    private function updatePluginsConfig($pluginsToLoad): void
    {
        $pluginsToLoad = $this->pluginList->sortPluginsAndRespectDependencies($pluginsToLoad);
        $section = PiwikConfig::getInstance()->Plugins;
        $section['Plugins'] = $pluginsToLoad;
        PiwikConfig::getInstance()->Plugins = $section;
    }

    /**
     * Update PluginsInstalled config
     *
     * @param string[] $plugins Plugins
     */
    private function updatePluginsInstalledConfig($plugins)
    {
        $section = PiwikConfig::getInstance()->PluginsInstalled;
        $section['PluginsInstalled'] = $plugins;
        PiwikConfig::getInstance()->PluginsInstalled = $section;
    }

    /**
     * @return void
     */
    public function clearPluginsInstalledConfig()
    {
        $this->updatePluginsInstalledConfig([]);
        PiwikConfig::getInstance()->forceSave();
    }

    /**
     * Returns true if plugin is always activated
     *
     * @param string $name Name of plugin
     * @return bool
     */
    private function isPluginAlwaysActivated($name)
    {
        return in_array($name, self::$pluginToAlwaysActivate);
    }

    /**
     * Returns true if the plugin can be uninstalled. Any non-core plugin can be uninstalled.
     *
     * @param string $name
     * @return bool
     */
    private function isPluginUninstallable($name)
    {
        return !$this->isPluginBundledWithCore($name);
    }

    /**
     * Returns `true` if a plugin has been activated.
     *
     * @param string $name Name of plugin, eg, `'Actions'`.
     * @return bool
     * @api
     */
    public function isPluginActivated($name)
    {
        return in_array($name, $this->pluginsToLoad)
        || ($this->doLoadAlwaysActivatedPlugins && $this->isPluginAlwaysActivated($name));
    }

    /**
     * Returns `true` if a plugin requires an working internet connection
     *
     * @param string $name Name of plugin, eg, `'Actions'`.
     * @return bool
     */
    public function doesPluginRequireInternetConnection($name)
    {
        $plugin = $this->makePluginClass($name);
        return $plugin->requiresInternetConnection();
    }

    /**
     * Checks whether the given plugin is activated, if not triggers an exception.
     *
     * @param  string $pluginName
     * @throws PluginDeactivatedException|PluginNotFoundException
     */
    public function checkIsPluginActivated($pluginName): void
    {
        if (!$this->isPluginInFilesystem($pluginName)) {
            throw new PluginNotFoundException($pluginName);
        }
        if (!$this->isPluginActivated($pluginName)) {
            throw new PluginDeactivatedException($pluginName);
        }
    }

    /**
     * Returns `true` if plugin is loaded (in memory).
     *
     * @param string $name Name of plugin, eg, `'Actions'`.
     * @return bool
     * @api
     */
    public function isPluginLoaded($name)
    {
        return isset($this->loadedPlugins[$name]);
    }

    /**
     * Reads the directories inside the plugins/ directory and returns their names in an array
     *
     * @return list<string>
     */
    public function readPluginsDirectory()
    {
        $result = array();
        foreach (self::getPluginsDirectories() as $pluginsDir) {
            $pluginsName = _glob($pluginsDir . '*', GLOB_ONLYDIR);
            if ($pluginsName != false) {
                foreach ($pluginsName as $path) {
                    if (self::pluginStructureLooksValid($path)) {
                        $result[] = basename($path);
                    }
                }
            }
        }

        sort($result);

        return $result;
    }

    /**
     * @return void
     */
    public static function initPluginDirectories()
    {
        $envDirs = getenv('MATOMO_PLUGIN_DIRS');
        if (!empty($envDirs)) {
            // we expect it in the format `absoluteStorageDir1;webrootPathRelative1:absoluteStorageDir2;webrootPathRelative1`
            if (empty($GLOBALS['MATOMO_PLUGIN_DIRS'])) {
                $GLOBALS['MATOMO_PLUGIN_DIRS'] = array();
            }

            $envDirs = explode(':', $envDirs);
            foreach ($envDirs as $envDir) {
                $envDir = explode(';', $envDir);
                $absoluteDir = rtrim($envDir[0], '/') . '/';
                $GLOBALS['MATOMO_PLUGIN_DIRS'][] = array(
                    'pluginsPathAbsolute' => $absoluteDir,
                    'webrootDirRelativeToMatomo' => isset($envDir[1]) ? $envDir[1] : null,
                );
            }
        }

        if (!empty($GLOBALS['MATOMO_PLUGIN_DIRS'])) {
            foreach ($GLOBALS['MATOMO_PLUGIN_DIRS'] as $pluginDir => &$settings) {
                if (!isset($settings['pluginsPathAbsolute'])) {
                    throw new \Exception('Missing "pluginsPathAbsolute" configuration for plugin dir');
                }
                if (!isset($settings['webrootDirRelativeToMatomo'])) {
                    throw new \Exception('Missing "webrootDirRelativeToMatomo" configuration for plugin dir');
                }
            }

            $pluginDirs = self::getPluginsDirectories();
            if (count($pluginDirs) > 1) {
                self::registerPluginDirAutoload($pluginDirs);
            }
        }

        $envCopyDir =  getenv('MATOMO_PLUGIN_COPY_DIR');
        if (!empty($envCopyDir)) {
            $GLOBALS['MATOMO_PLUGIN_COPY_DIR'] = $envCopyDir;
        }

        if (
            !empty($GLOBALS['MATOMO_PLUGIN_COPY_DIR'])
            && !in_array($GLOBALS['MATOMO_PLUGIN_COPY_DIR'], self::getPluginsDirectories())
        ) {
            throw new \Exception('"MATOMO_PLUGIN_COPY_DIR" dir must be one of "MATOMO_PLUGIN_DIRS" directories');
        }
    }

    /**
     * Registers a new autoloader to support the loading of Matomo plugin classes when the plugins are installed
     * outside the Matomo plugins folder.
     * @param string[] $pluginDirs
     * @return void
     */
    public static function registerPluginDirAutoload($pluginDirs)
    {
        spl_autoload_register(function ($className) use ($pluginDirs) {
            if (strpos($className, 'Piwik\Plugins\\') === 0) {
                $withoutPrefix = str_replace('Piwik\Plugins\\', '', $className);
                $path = str_replace('\\', DIRECTORY_SEPARATOR, $withoutPrefix) . '.php';
                foreach ($pluginDirs as $pluginsDirectory) {
                    if (file_exists($pluginsDirectory . $path)) {
                        require_once $pluginsDirectory . $path;
                    }
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public static function getAlternativeWebRootDirectories()
    {
        $dirs = [];

        if (!empty($GLOBALS['MATOMO_PLUGIN_DIRS'])) {
            foreach ($GLOBALS['MATOMO_PLUGIN_DIRS'] as $pluginDir) {
                $absolute = rtrim($pluginDir['pluginsPathAbsolute'], '/') . '/';
                $relative = rtrim($pluginDir['webrootDirRelativeToMatomo'], '/') . '/';
                $dirs[$absolute] = $relative;
            }
        }

        return $dirs;
    }

    /**
     * @return string[]
     */
    public function getWebRootDirectoriesForCustomPluginDirs()
    {
        return array_intersect_key(self::$pluginsToWebRootDirCache, array_flip($this->pluginsToLoad));
    }

    /**
     * @return list<string>
     */
    public function getPluginUmdsToLoadOnDemand()
    {
        $pluginsToLoadOnDemand = [];
        foreach ($this->getPluginsLoadedAndActivated() as $plugin) {
            if (method_exists($plugin, 'shouldLoadUmdOnDemand') && $plugin->shouldLoadUmdOnDemand()) {
                $pluginsToLoadOnDemand[] = $plugin->getPluginName();
            }
        }
        return $pluginsToLoadOnDemand;
    }

    /**
     * Returns the path to all plugins directories. Each plugins directory may contain several plugins.
     * All paths have a trailing slash '/'.
     * @return string[]
     * @api
     */
    public static function getPluginsDirectories()
    {
        $dirs = array(self::getPluginsDirectory());

        if (!empty($GLOBALS['MATOMO_PLUGIN_DIRS'])) {
            $extraDirs = array_map(function ($dir) {
                return rtrim($dir['pluginsPathAbsolute'], '/') . '/';
            }, $GLOBALS['MATOMO_PLUGIN_DIRS']);
            $dirs = array_merge($dirs, $extraDirs);
        }

        return $dirs;
    }

    private static function getPluginRealPath(string $path): string
    {
        if (strpos($path, '../') !== false) {
            // for tests, only do it when needed re performance etc
            $real = realpath($path);
            if ($real && Common::stringEndsWith($path, '/')) {
                return rtrim($real, '/') . '/';
            }
            if ($real) {
                return $real;
            }
        }
        return $path;
    }

    /**
     * Gets the path to a specific plugin. If the plugin does not exist in any plugins folder, the default plugins
     * folder will be assumed.
     *
     * @param string $pluginName
     * @return string
     * @api
     */
    public static function getPluginDirectory($pluginName)
    {
        if (isset(self::$pluginsToPathCache[$pluginName])) {
            return self::$pluginsToPathCache[$pluginName];
        }

        $corePluginsDir = PIWIK_INCLUDE_PATH . '/plugins/' . $pluginName;
        if (is_dir($corePluginsDir)) {
            // for faster performance
            self::$pluginsToPathCache[$pluginName] = self::getPluginRealPath($corePluginsDir);
            return self::$pluginsToPathCache[$pluginName];
        }

        foreach (self::getAlternativeWebRootDirectories() as $dir => $relative) {
            $path = $dir . $pluginName;
            if (is_dir($path)) {
                self::$pluginsToPathCache[$pluginName] = self::getPluginRealPath($path);
                self::$pluginsToWebRootDirCache[$pluginName] = rtrim($relative, '/');
                return $path;
            }
        }

        // assume default directory when plugin does not exist just yet
        return self::getPluginsDirectory() . $pluginName;
    }

    /**
     * Returns the plugin directory path relative to Matomo's root directory.
     */
    public static function getRelativePluginDirectory(string $pluginName): string
    {
        $result = self::getPluginDirectory($pluginName);

        $matomoPath = rtrim(PIWIK_INCLUDE_PATH, '/') . '/';
        $webroots = array_merge(
            Manager::getAlternativeWebRootDirectories(),
            [$matomoPath => '/']
        );

        foreach ($webroots as $webrootAbsolute => $webrootRelative) {
            if (strpos($result, $webrootAbsolute) === 0) {
                $result = str_replace($webrootAbsolute, $webrootRelative, $result);
                break;
            }
        }

        $result = ltrim($result, '/');

        return $result;
    }

    /**
     * Returns the path to the directory where core plugins are located. Please note since Matomo 3.9
     * plugins may also be located in other directories and therefore this method has been deprecated.
     * @internal since Matomo 3.9.0 use {@link (getPluginsDirectories())} or {@link getPluginDirectory($pluginName)} instead
     * @return string
     */
    public static function getPluginsDirectory()
    {
        $path = rtrim(PIWIK_INCLUDE_PATH, '/') . '/plugins/';
        $path = self::getPluginRealPath($path);
        return $path;
    }

    /**
     * Deactivate plugin
     *
     * @param string $pluginName Name of plugin
     * @return void
     */
    public function deactivatePlugin($pluginName)
    {
        $plugins = $this->pluginList->getActivatedPlugins();
        if (!in_array($pluginName, $plugins)) {
            // plugin is already deactivated
            return;
        }

        $this->clearCache($pluginName);

        // execute deactivate() to let the plugin do cleanups
        $this->executePluginDeactivate($pluginName);

        $this->savePluginTime(self::LAST_PLUGIN_DEACTIVATION_TIME_OPTION_PREFIX, $pluginName);

        $this->unloadPluginFromMemory($pluginName);

        $this->removePluginFromConfig($pluginName);

        /**
         * Event triggered after a plugin has been deactivated.
         *
         * @param string $pluginName The plugin that has been deactivated.
         */
        Piwik::postEvent('PluginManager.pluginDeactivated', array($pluginName));
    }

    /**
     * Tries to find the given components such as a Menu or Tasks implemented by plugins.
     * This method won't cache the found components. If you need to find the same component multiple times you might
     * want to cache the result to save a tiny bit of time.
     *
     * @param string $componentName     The name of the component you want to look for. In case you request a
     *                                  component named 'Menu' it'll look for a file named 'Menu.php' within the
     *                                  root of all plugin folders that implement a class named
     *                                  Piwik\Plugin\$PluginName\Menu.
     * @param string $expectedSubclass  If not empty, a check will be performed whether a found file extends the
     *                                  given subclass. If the requested file exists but does not extend this class
     *                                  a warning will be shown to advice a developer to extend this certain class.
     *
     * @template T of object
     * @phpstan-param class-string<T>|''|false|null $expectedSubclass
     *
     * @return array<class-string<T>>
     */
    public function findComponents($componentName, $expectedSubclass)
    {
        $plugins    = $this->getPluginsLoadedAndActivated();
        $components = array();

        foreach ($plugins as $plugin) {
            $component = $plugin->findComponent($componentName, $expectedSubclass);

            if (!empty($component)) {
                $components[] = $component;
            }
        }

        return $components;
    }

    /**
     * @template T of object
     * @param string $directoryWithinPlugin
     * @param class-string<T>|''|false|null $expectedSubclass
     * @return array<class-string<T>>
     */
    public function findMultipleComponents($directoryWithinPlugin, $expectedSubclass)
    {
        $plugins = $this->getPluginsLoadedAndActivated();
        $found   = array();

        foreach ($plugins as $plugin) {
            $components = $plugin->findMultipleComponents($directoryWithinPlugin, $expectedSubclass);

            if (!empty($components)) {
                $found = array_merge($found, $components);
            }
        }

        return $found;
    }

    /**
     * Uninstalls a Plugin (deletes plugin files from the disk)
     * Only deactivated plugins can be uninstalled
     *
     * @param string $pluginName
     * @return bool
     */
    public function uninstallPlugin($pluginName)
    {
        if ($this->isPluginLoaded($pluginName)) {
            throw new \Exception("To uninstall the plugin $pluginName, first disable it in Matomo > Settings > Plugins");
        }
        $this->loadAllPluginsAndGetTheirInfo();

        SettingsStorage\Backend\PluginSettingsTable::removeAllSettingsForPlugin($pluginName);
        SettingsStorage\Backend\MeasurableSettingsTable::removeAllSettingsForPlugin($pluginName);

        $this->executePluginDeactivate($pluginName);
        $this->executePluginUninstall($pluginName);

        $this->removePluginFromPluginsInstalledConfig($pluginName);

        $this->unloadPluginFromMemory($pluginName);

        $this->removePluginFromConfig($pluginName);
        $this->removeInstalledVersionFromOptionTable($pluginName);
        $this->clearCache($pluginName);

        self::deletePluginFromFilesystem($pluginName);
        if ($this->isPluginInFilesystem($pluginName)) {
            return false;
        }

        /**
         * Event triggered after a plugin has been uninstalled.
         *
         * @param string $pluginName The plugin that has been uninstalled.
         */
        Piwik::postEvent('PluginManager.pluginUninstalled', array($pluginName));

        return true;
    }

    private function clearCache(string $pluginName): void
    {
        $this->resetTransientCache();
        Filesystem::deleteAllCacheOnUpdate($pluginName);
    }

    /**
     * @param string $plugin
     * @return void
     */
    public static function deletePluginFromFilesystem($plugin)
    {
        $pluginDir = self::getPluginDirectory($plugin);
        if (strpos($pluginDir, PIWIK_INCLUDE_PATH) === 0) {
            // only delete files for plugins within matomo directory...
            Filesystem::unlinkRecursive($pluginDir, $deleteRootToo = true);
        }
    }

    /**
     * Install loaded plugins
     */
    public function installLoadedPlugins(): void
    {
        Log::debug("Loaded plugins: " . implode(", ", array_keys($this->getLoadedPlugins())));

        $pluginsActivated = $this->pluginList->getActivatedPlugins();

        foreach ($this->getLoadedPlugins() as $plugin) {
            $this->installPluginIfNecessary($plugin);

            $pluginName = $plugin->getPluginName();

            // if a new plugin was added that is always activated, ensure activate is called once nevertheless
            // otherwise the `activate` method of the plugin might never get called.
            if ($this->isPluginAlwaysActivated($pluginName) && !in_array($pluginName, $pluginsActivated)) {
                $this->activatePlugin($pluginName);
            }
        }
    }

    /**
     * Activate the specified plugin and install (if needed)
     *
     * @param string $pluginName Name of plugin
     * @return void
     */
    public function activatePlugin($pluginName)
    {
        $plugins = $this->pluginList->getActivatedPlugins();
        if (in_array($pluginName, $plugins)) {
            // plugin is already activated
            return;
        }

        if (!$this->isPluginInFilesystem($pluginName)) {
            throw new PluginNotFoundException($pluginName);
        }
        $this->deactivateThemeIfTheme($pluginName);

        // Load plugin
        $plugin = $this->loadPlugin($pluginName);
        if ($plugin === null) {
            throw new \Exception("The plugin '$pluginName' was found in the filesystem, but could not be loaded.'");
        }
        $this->installPluginIfNecessary($plugin);
        $plugin->activate();

        $this->savePluginTime(self::LAST_PLUGIN_ACTIVATION_TIME_OPTION_PREFIX, $pluginName);

        EventDispatcher::getInstance()->postPendingEventsTo($plugin);

        $this->pluginsToLoad[] = $pluginName;

        $this->updatePluginsConfig($this->pluginsToLoad);
        PiwikConfig::getInstance()->forceSave();

        $this->clearCache($pluginName);

        /**
         * Event triggered after a plugin has been activated.
         *
         * @param string $pluginName The plugin that has been activated.
         */
        Piwik::postEvent('PluginManager.pluginActivated', array($pluginName));
    }

    /**
     * @param string $pluginName
     * @return bool
     */
    public function isPluginInFilesystem($pluginName)
    {
        $existingPlugins = $this->readPluginsDirectory();
        $isPluginInFilesystem = array_search($pluginName, $existingPlugins) !== false;
        return $this->isValidPluginName($pluginName)
        && $isPluginInFilesystem;
    }

    /**
     * Returns the currently enabled theme.
     *
     * If no theme is enabled, the **Morpheus** plugin is returned (this is the base and default theme).
     *
     * @api
     */
    public function getThemeEnabled(): ?Plugin
    {
        $plugins = $this->getLoadedPlugins();

        $theme = null;
        foreach ($plugins as $plugin) {
            /* @var $plugin Plugin */
            if (
                $plugin->isTheme()
                && $this->isPluginActivated($plugin->getPluginName())
            ) {
                if ($plugin->getPluginName() != self::DEFAULT_THEME) {
                    return $plugin; // enabled theme (not default)
                }
                $theme = $plugin; // default theme
            }
        }
        return $theme;
    }

    /**
     * @param string $themeName
     * @throws \Exception
     * @return Theme
     */
    public function getTheme($themeName)
    {
        $plugins = $this->getLoadedPlugins();

        foreach ($plugins as $plugin) {
            if ($plugin->isTheme() && $plugin->getPluginName() == $themeName) {
                return new Theme($plugin);
            }
        }
        throw new \Exception('Theme not found : ' . $themeName);
    }

    /**
     * @return int
     */
    public function getNumberOfActivatedPluginsExcludingAlwaysActivated()
    {
        $counter = 0;

        $pluginNames = $this->getLoadedPluginsName();
        foreach ($pluginNames as $pluginName) {
            if (
                $this->isPluginActivated($pluginName)
                && !$this->isPluginAlwaysActivated($pluginName)
            ) {
                $counter++;
            }
        }

        return $counter;
    }

    /**
     * Returns info regarding all plugins. Loads plugins that can be loaded.
     *
     * @return array An array that maps plugin names with arrays of plugin information. Plugin
     *               information consists of the following entries:
     *
     *               - **activated**: Whether the plugin is activated.
     *               - **alwaysActivated**: Whether the plugin should always be activated,
     *                                      or not.
     *               - **uninstallable**: Whether the plugin is uninstallable or not.
     *               - **invalid**: If the plugin is invalid, this property will be set to true.
     *                              If the plugin is not invalid, this property will not exist.
     *               - **info**: If the plugin was loaded, will hold the plugin information.
     *                           See {@link Piwik\Plugin::getInformation()}.
     * @phpstan-return array<string, array{info?: array<string, mixed>, activated: bool, alwaysActivated: bool, uninstallable: bool, invalid?: bool, missingRequirements?: string}>
     * @api
     */
    public function loadAllPluginsAndGetTheirInfo()
    {
        /** @var Translator $translator */
        $translator = StaticContainer::get('Piwik\Translation\Translator');

        $plugins = array();

        $listPlugins = array_merge(
            $this->readPluginsDirectory(),
            $this->pluginList->getActivatedPlugins()
        );
        $listPlugins = array_unique($listPlugins);
        $internetFeaturesEnabled = SettingsPiwik::isInternetEnabled();
        foreach ($listPlugins as $pluginName) {
            // Hide plugins that are never going to be used
            if ($this->isPluginBogus($pluginName)) {
                continue;
            }

            // If the plugin is not core and looks bogus, do not load
            if ($this->isPluginThirdPartyAndBogus($pluginName)) {
                $info = array(
                    'invalid'         => true,
                    'activated'       => false,
                    'alwaysActivated' => false,
                    'uninstallable'   => true,
                );
            } else {
                $translator->addDirectory(self::getPluginDirectory($pluginName) . '/lang');
                $this->loadPlugin($pluginName);
                $info = array(
                    'activated'       => $this->isPluginActivated($pluginName),
                    'alwaysActivated' => $this->isPluginAlwaysActivated($pluginName),
                    'uninstallable'   => $this->isPluginUninstallable($pluginName),
                );
            }

            $plugins[$pluginName] = $info;
        }

        $loadedPlugins = $this->getLoadedPlugins();
        foreach ($loadedPlugins as $oPlugin) {
            $pluginName = $oPlugin->getPluginName();

            $info = array(
                'info' => $oPlugin->getInformation(),
                'activated'       => $this->isPluginActivated($pluginName),
                'alwaysActivated' => $this->isPluginAlwaysActivated($pluginName),
                'missingRequirements' => $oPlugin->getMissingDependenciesAsString(),
                'uninstallable' => $this->isPluginUninstallable($pluginName),
            );
            $plugins[$pluginName] = $info;
        }

        return $plugins;
    }

    protected static function isManifestFileFound(string $path): bool
    {
        return file_exists($path . "/" . MetadataLoader::PLUGIN_JSON_FILENAME);
    }

    /**
     * Returns `true` if the plugin is bundled with core or `false` if it is third party.
     *
     * @param string $name The name of the plugin, eg, `'Actions'`.
     * @return bool
     */
    public function isPluginBundledWithCore($name)
    {
        return $this->isPluginEnabledByDefault($name)
        || in_array($name, $this->pluginList->getCorePluginsDisabledByDefault())
        || $name == self::DEFAULT_THEME;
    }

    /**
     * @param string $pluginName
     * @return bool
     * @ignore
     */
    public function isPluginThirdPartyAndBogus($pluginName)
    {
        if ($this->isPluginBundledWithCore($pluginName)) {
            return false;
        }
        if ($this->isPluginBogus($pluginName)) {
            return true;
        }

        $path = self::getPluginDirectory($pluginName);

        if (!$this->isManifestFileFound($path)) {
            return true;
        }
        return false;
    }

    /**
     * Load AND activates the specified plugins. It will also overwrite all previously loaded plugins, so it acts
     * as a setter.
     *
     * @param string[] $pluginsToLoad Array of plugins to load.
     * @return void
     */
    public function loadPlugins(array $pluginsToLoad)
    {
        $this->resetTransientCache();
        $this->pluginsToLoad = $this->makePluginsToLoad($pluginsToLoad);
        $this->reloadActivatedPlugins();
    }

    /**
     * Disable plugin loading.
     * @return void
     */
    public function doNotLoadPlugins()
    {
        $this->doLoadPlugins = false;
    }

    /**
     * Disable loading of "always activated" plugins.
     * @return void
     */
    public function doNotLoadAlwaysActivatedPlugins()
    {
        $this->doLoadAlwaysActivatedPlugins = false;
    }

    /**
     * Execute postLoad() hook for loaded plugins
     * @return void
     */
    public function postLoadPlugins()
    {
        $plugins = $this->getLoadedPlugins();
        foreach ($plugins as $plugin) {
            $plugin->postLoad();
        }
    }

    /**
     * Returns an array containing the plugins class names (eg. 'UserCountry' and NOT 'UserCountry')
     *
     * @return list<string>
     */
    public function getLoadedPluginsName()
    {
        return array_keys($this->getLoadedPlugins());
    }

    /**
     * Returns an array mapping loaded plugin names with their plugin objects, eg,
     *
     *     array(
     *         'UserCountry' => Plugin $pluginObject,
     *         'UserLanguage' => Plugin $pluginObject,
     *     );
     *
     * @return Plugin[]
     */
    public function getLoadedPlugins()
    {
        return $this->loadedPlugins;
    }

    /**
     * @param  string $piwikVersion
     * @return Plugin[]
     */
    public function getIncompatiblePlugins($piwikVersion)
    {
        $plugins = $this->getLoadedPlugins();

        $incompatible = [];
        foreach ($plugins as $plugin) {
            if ($plugin->hasMissingDependencies($piwikVersion)) {
                $incompatible[] = $plugin;
            }
        }

        return $incompatible;
    }

    /**
     * Returns an array of plugins that are currently loaded and activated,
     * mapping loaded plugin names with their plugin objects, eg,
     *
     *     array(
     *         'UserCountry' => Plugin $pluginObject,
     *         'UserLanguage' => Plugin $pluginObject,
     *     );
     *
     * @return Plugin[]
     */
    public function getPluginsLoadedAndActivated()
    {
        if (is_null($this->pluginsLoadedAndActivated)) {
            $enabled = $this->getActivatedPlugins();

            if (empty($enabled)) {
                return array();
            }

            $plugins = $this->getLoadedPlugins();
            $enabled = array_combine($enabled, $enabled);
            $plugins = array_intersect_key($plugins, $enabled);

            $this->pluginsLoadedAndActivated = $plugins;
        }

        return $this->pluginsLoadedAndActivated;
    }

    /**
     * Returns a list of all names of currently activated plugin eg,
     *
     *     array(
     *         'UserCountry'
     *         'UserLanguage'
     *     );
     *
     * @return string[]
     */
    public function getActivatedPlugins()
    {
        return $this->pluginsToLoad;
    }

    /**
     * @return string[]
     */
    public function getActivatedPluginsFromConfig()
    {
        $plugins = $this->pluginList->getActivatedPlugins();

        return $this->makePluginsToLoad($plugins);
    }

    /**
     * Returns a Plugin object by name.
     *
     * @param string $name The name of the plugin, eg, `'Actions'`.
     * @return Plugin
     */
    public function getLoadedPlugin($name)
    {
        if (!isset($this->loadedPlugins[$name]) || is_null($this->loadedPlugins[$name])) {
            throw new \Exception("The plugin '$name' has not been loaded.");
        }
        return $this->loadedPlugins[$name];
    }

    /**
     * Load the plugins classes installed.
     * Register the observers for every plugin.
     */
    private function reloadActivatedPlugins(): void
    {
        $pluginsToPostPendingEventsTo = [];
        foreach ($this->pluginsToLoad as $pluginName) {
            $pluginsToPostPendingEventsTo = $this->reloadActivatedPlugin($pluginName, $pluginsToPostPendingEventsTo);
        }

        // post pending events after all plugins are successfully loaded
        foreach ($pluginsToPostPendingEventsTo as $plugin) {
            EventDispatcher::getInstance()->postPendingEventsTo($plugin);
        }
    }

    /**
     * @param list<Plugin> $pluginsToPostPendingEventsTo
     * @return list<Plugin>
     */
    private function reloadActivatedPlugin(string $pluginName, array $pluginsToPostPendingEventsTo): array
    {
        if ($this->isPluginLoaded($pluginName) || $this->isPluginThirdPartyAndBogus($pluginName)) {
            return $pluginsToPostPendingEventsTo;
        }

        $newPlugin = $this->loadPlugin($pluginName);

        if ($newPlugin === null) {
            return $pluginsToPostPendingEventsTo;
        }

        $requirements = $newPlugin->getMissingDependencies();

        if (!empty($requirements)) {
            foreach ($requirements as $requirement) {
                $possiblePluginName = $requirement['requirement'];
                if (in_array($possiblePluginName, $this->pluginsToLoad, $strict = true)) {
                    $pluginsToPostPendingEventsTo = $this->reloadActivatedPlugin($possiblePluginName, $pluginsToPostPendingEventsTo);
                }
            }
        }

        if ($newPlugin->hasMissingDependencies()) {
            $this->unloadPluginFromMemory($pluginName);

            // at this state we do not know yet whether current user has super user access. We do not even know
            // if someone is actually logged in.
            $message  = Piwik::translate('CorePluginsAdmin_WeCouldNotLoadThePluginAsItHasMissingDependencies', array($pluginName, $newPlugin->getMissingDependenciesAsString()));
            $message .= ' ';
            $message .= Piwik::translate('General_PleaseContactYourPiwikAdministrator');

            $notification = new Notification($message);
            $notification->context = Notification::CONTEXT_ERROR;
            Notification\Manager::notify('PluginManager_PluginUnloaded', $notification);
            return $pluginsToPostPendingEventsTo;
        }

        if (
            $newPlugin->isPremiumFeature()
            && SettingsPiwik::isInternetEnabled()
            && !Development::isEnabled()
            && $this->isPluginActivated('Marketplace')
            && $this->isPluginActivated($pluginName)
        ) {
            $cacheKey = 'MarketplacePluginMissingLicense' . $pluginName;
            $cache = self::getLicenseCache();

            if ($cache->contains($cacheKey)) {
                $pluginLicenseInfo = $cache->fetch($cacheKey);
            } elseif (!SettingsServer::isTrackerApiRequest()) {
                // prevent requesting license info during a tracker request see https://github.com/matomo-org/matomo/issues/14401
                // as possibly many instances would try to do this at the same time
                try {
                    $plugins = StaticContainer::get('Piwik\Plugins\Marketplace\Plugins');
                    $licenseInfo = $plugins->getLicenseValidInfo($pluginName);
                } catch (\Exception $e) {
                    $licenseInfo = [];
                }

                $pluginLicenseInfo = ['missing' => !empty($licenseInfo['isMissingLicense'])];
                $sixHours = 3600 * 6;
                $cache->save($cacheKey, $pluginLicenseInfo, $sixHours);
            } else {
                // tracker mode, we assume it is not missing until cache is written
                $pluginLicenseInfo = ['missing' => false];
            }

            if (!empty($pluginLicenseInfo['missing']) && (!defined('PIWIK_TEST_MODE') || !PIWIK_TEST_MODE)) {
                $this->unloadPluginFromMemory($pluginName);
                return $pluginsToPostPendingEventsTo;
            }
        }

        $pluginsToPostPendingEventsTo[] = $newPlugin;

        return $pluginsToPostPendingEventsTo;
    }

    /**
     * @return \Matomo\Cache\Lazy
     */
    public static function getLicenseCache()
    {
        return Cache::getLazyCache();
    }

    /**
     * @return list<string>
     */
    public function getIgnoredBogusPlugins()
    {
        $ignored = array();
        foreach ($this->pluginsToLoad as $pluginName) {
            if ($this->isPluginThirdPartyAndBogus($pluginName)) {
                $ignored[] = $pluginName;
            }
        }
        return $ignored;
    }

    /**
     * Returns the name of all plugins found in this Piwik instance
     * (including those not enabled and themes)
     *
     * @return list<string>
     */
    public static function getAllPluginsNames()
    {
        $pluginList = StaticContainer::get('Piwik\Application\Kernel\PluginList');

        $pluginsToLoad = array_merge(
            self::getInstance()->readPluginsDirectory(),
            $pluginList->getCorePluginsDisabledByDefault()
        );
        $pluginsToLoad = array_values(array_unique($pluginsToLoad));
        return $pluginsToLoad;
    }

    /**
     * Return the list of plugins that are always activated
     *
     * @return list<string>
     */
    public static function getAlwaysActivatedPlugins(): array
    {
        return self::$pluginToAlwaysActivate;
    }

    /**
     * Loads the plugin filename and instantiates the plugin with the given name, eg. UserCountry.
     * Contrary to loadPlugins() it does not activate the plugin, it only loads it.
     *
     * @param string $pluginName
     * @return Plugin|null
     */
    public function loadPlugin($pluginName)
    {
        if (isset($this->loadedPlugins[$pluginName])) {
            return $this->loadedPlugins[$pluginName];
        }

        $newPlugin = $this->makePluginClass($pluginName);

        $this->addLoadedPlugin($pluginName, $newPlugin);
        return $newPlugin;
    }

    /**
     * @param string $pluginName
     * @return bool
     */
    public function isValidPluginName($pluginName)
    {
        return (bool) preg_match('/^[a-zA-Z]([a-zA-Z0-9_]){0,59}$/D', $pluginName);
    }

    /**
     * @param string $pluginName
     * @return Plugin
     */
    protected function makePluginClass($pluginName)
    {
        $pluginClassName = $pluginName;

        if (!$this->isValidPluginName($pluginName)) {
            throw new \Exception(sprintf("The plugin name '%s' is not a valid plugin name", $pluginName));
        }

        $path = self::getPluginDirectory($pluginName);
        $path = sprintf('%s/%s.php', $path, $pluginName);

        if (!file_exists($path)) {
            // Create the smallest minimal Piwik Plugin
            // Eg. Used for Morpheus default theme which does not have a Morpheus.php file
            return new Plugin($pluginName);
        }

        require_once $path;

        $namespacedClass = $this->getClassNamePlugin($pluginName);
        if (!class_exists($namespacedClass, false)) {
            throw new \Exception("The class $pluginClassName couldn't be found in the file '$path'");
        }
        $newPlugin = new $namespacedClass();

        if (!($newPlugin instanceof Plugin)) {
            throw new \Exception("The plugin $pluginClassName in the file $path must inherit from Plugin.");
        }
        return $newPlugin;
    }

    protected function getClassNamePlugin(string $pluginName): string
    {
        $className = $pluginName;
        if ($pluginName === 'API') {
            $className = 'Plugin';
        }
        return "\\Piwik\\Plugins\\$pluginName\\$className";
    }

    private function resetTransientCache(): void
    {
        $this->pluginsLoadedAndActivated = null;
    }

    /**
     * Unload plugin
     *
     * @param Plugin|string $plugin
     * @return void
     */
    public function unloadPlugin($plugin)
    {
        $this->resetTransientCache();

        if (!($plugin instanceof Plugin)) {
            $oPlugin = $this->loadPlugin($plugin);
            if ($oPlugin === null) {
                unset($this->loadedPlugins[$plugin]);
                return;
            }

            $plugin = $oPlugin;
        }

        unset($this->loadedPlugins[$plugin->getPluginName()]);
    }

    /**
     * Unload all loaded plugins
     * @return void
     */
    public function unloadPlugins()
    {
        $this->resetTransientCache();

        $pluginsLoaded = $this->getLoadedPlugins();
        foreach ($pluginsLoaded as $plugin) {
            $this->unloadPlugin($plugin);
        }
    }

    /**
     * Install a specific plugin
     *
     * @return void
     * @throws \Piwik\Plugin\PluginException if installation fails
     */
    private function executePluginInstall(Plugin $plugin)
    {
        try {
            $plugin->install();
        } catch (\Exception $e) {
            throw new \Piwik\Plugin\PluginException($plugin->getPluginName(), $e->getMessage());
        }
    }

    /**
     * Add a plugin in the loaded plugins array
     *
     * @param string $pluginName plugin name without prefix (eg. 'UserCountry')
     * @return void
     * @internal
     */
    public function addLoadedPlugin($pluginName, Plugin $newPlugin)
    {
        $this->resetTransientCache();

        $this->loadedPlugins[$pluginName] = $newPlugin;
    }

    /**
     * Return names of all installed plugins.
     *
     * @return list<string>
     * @api
     */
    public function getInstalledPluginsName()
    {
        return Config::getInstance()->PluginsInstalled['PluginsInstalled'];
    }

    /**
     * Returns names of plugins that should be loaded, but cannot be since their
     * files cannot be found.
     *
     * @return list<string>
     * @api
     */
    public function getMissingPlugins()
    {
        $missingPlugins = [];

        $plugins = $this->pluginList->getActivatedPlugins();

        foreach ($plugins as $pluginName) {
            // if a plugin is listed in the config, but is not loaded, it does not exist in the folder
            if (
                !$this->isPluginLoaded($pluginName) && !$this->isPluginBogus($pluginName) &&
                !($this->doesPluginRequireInternetConnection($pluginName) && !SettingsPiwik::isInternetEnabled())
            ) {
                $missingPlugins[] = $pluginName;
            }
        }

        return $missingPlugins;
    }

    /**
     * Install a plugin, if necessary
     */
    private function installPluginIfNecessary(Plugin $plugin): void
    {
        $pluginName = $plugin->getPluginName();
        $saveConfig = false;

        // is the plugin already installed or is it the first time we activate it?
        $pluginsInstalled = $this->getInstalledPluginsName();

        if (!$this->isPluginInstalled($pluginName)) {
            $this->executePluginInstall($plugin);
            $pluginsInstalled[] = $pluginName;
            $this->updatePluginsInstalledConfig($pluginsInstalled);
            $updater = new Updater();
            $updater->markComponentSuccessfullyUpdated($plugin->getPluginName(), $plugin->getVersion(), $isNew = true);
            $saveConfig = true;

            /**
             * Event triggered after a new plugin has been installed.
             *
             * Note: Might be triggered more than once if the config file is not writable
             *
             * @param string $pluginName The plugin that has been installed.
             */
            Piwik::postEvent('PluginManager.pluginInstalled', array($pluginName));
        }

        if ($saveConfig) {
            PiwikConfig::getInstance()->forceSave();
            $this->clearCache($pluginName);
        }
    }

    /**
     * @return bool
     */
    public function isTrackerPlugin(Plugin $plugin)
    {
        if (!$this->isPluginInstalled($plugin->getPluginName())) {
            return false;
        }

        if ($plugin->isTrackerPlugin()) {
            return true;
        }

        $dimensions = VisitDimension::getDimensions($plugin);
        if (!empty($dimensions)) {
            return true;
        }

        $dimensions = ActionDimension::getDimensions($plugin);
        if (!empty($dimensions)) {
            return true;
        }

        $hooks = $plugin->registerEvents();
        $hookNames = array_keys($hooks);
        foreach ($hookNames as $name) {
            if (strpos($name, self::TRACKER_EVENT_PREFIX) === 0) {
                return true;
            }
            if ($name === 'Request.initAuthenticationObject') {
                return true;
            }
        }

        $dimensions = ConversionDimension::getDimensions($plugin);
        if (!empty($dimensions)) {
            return true;
        }

        return false;
    }

    private static function pluginStructureLooksValid(string $path): bool
    {
        $name = basename($path);
        return file_exists($path . "/" . $name . ".php")
        || self::isManifestFileFound($path);
    }

    private function removePluginFromPluginsInstalledConfig(string $pluginName): void
    {
        $pluginsInstalled = Config::getInstance()->PluginsInstalled['PluginsInstalled'];
        $key = array_search($pluginName, $pluginsInstalled);
        if ($key !== false) {
            unset($pluginsInstalled[$key]);
        }

        $this->updatePluginsInstalledConfig($pluginsInstalled);
    }

    private function removePluginFromPluginsConfig(string $pluginName): void
    {
        $pluginsEnabled = $this->pluginList->getActivatedPlugins();
        $key = array_search($pluginName, $pluginsEnabled);
        if ($key !== false) {
            unset($pluginsEnabled[$key]);
        }
        $this->updatePluginsConfig($pluginsEnabled);
    }

    private function isPluginBogus(string $pluginName): bool
    {
        $bogusPlugins = array(
            'PluginMarketplace', //defines a plugin.json but 1.x Piwik plugin
            'DoNotTrack', // Removed in 2.0.3
            'AnonymizeIP', // Removed in 2.0.3
            'wp-optimize', // a WP plugin that has a plugin.json file but is not a matomo plugin
        );
        return in_array($pluginName, $bogusPlugins);
    }

    private function deactivateThemeIfTheme(string $pluginName): void
    {
        // Only one theme enabled at a time
        $themeEnabled = $this->getThemeEnabled();
        if (
            $themeEnabled
            && $themeEnabled->getPluginName() != self::DEFAULT_THEME
        ) {
            $themeAlreadyEnabled = $themeEnabled->getPluginName();

            $plugin = $this->loadPlugin($pluginName);
            if ($plugin && $plugin->isTheme()) {
                $this->deactivatePlugin($themeAlreadyEnabled);
            }
        }
    }

    private function executePluginDeactivate(string $pluginName): void
    {
        if (!$this->isPluginBogus($pluginName)) {
            $plugin = $this->loadPlugin($pluginName);
            if ($plugin !== null) {
                $plugin->deactivate();
            }
        }
    }

    private function unloadPluginFromMemory(string $pluginName): void
    {
        $this->unloadPlugin($pluginName);

        $key = array_search($pluginName, $this->pluginsToLoad);
        if ($key !== false) {
            unset($this->pluginsToLoad[$key]);
        }
    }

    private function removePluginFromConfig(string $pluginName): void
    {
        $this->removePluginFromPluginsConfig($pluginName);
        PiwikConfig::getInstance()->forceSave();
    }

    private function executePluginUninstall(string $pluginName): void
    {
        try {
            $plugin = $this->getLoadedPlugin($pluginName);
            $plugin->uninstall();
        } catch (\Exception $e) {
        }

        if (empty($plugin)) {
            return;
        }

        try {
            $visitDimensions = VisitDimension::getAllDimensions();

            foreach (VisitDimension::getDimensions($plugin) as $dimension) {
                $this->uninstallDimension(VisitDimension::INSTALLER_PREFIX, $dimension, $visitDimensions);
            }
        } catch (\Exception $e) {
        }

        try {
            $actionDimensions = ActionDimension::getAllDimensions();

            foreach (ActionDimension::getDimensions($plugin) as $dimension) {
                $this->uninstallDimension(ActionDimension::INSTALLER_PREFIX, $dimension, $actionDimensions);
            }
        } catch (\Exception $e) {
        }

        try {
            $conversionDimensions = ConversionDimension::getAllDimensions();

            foreach (ConversionDimension::getDimensions($plugin) as $dimension) {
                $this->uninstallDimension(ConversionDimension::INSTALLER_PREFIX, $dimension, $conversionDimensions);
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * @param VisitDimension|ActionDimension|ConversionDimension $dimension
     * @param VisitDimension[]|ActionDimension[]|ConversionDimension[] $allDimensions
     */
    private function doesAnotherPluginDefineSameColumnWithDbEntry($dimension, $allDimensions): bool
    {
        $module = $dimension->getModule();
        $columnName = $dimension->getColumnName();

        foreach ($allDimensions as $dim) {
            if (
                $dim->getColumnName() === $columnName &&
                $dim->hasColumnType() &&
                $dim->getModule() !== $module
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $prefix column installer prefix
     * @param ConversionDimension|VisitDimension|ActionDimension $dimension
     * @param VisitDimension[]|ActionDimension[]|ConversionDimension[] $allDimensions
     */
    private function uninstallDimension(string $prefix, Dimension $dimension, $allDimensions): void
    {
        if (!$this->doesAnotherPluginDefineSameColumnWithDbEntry($dimension, $allDimensions)) {
            $dimension->uninstall();

            $this->removeInstalledVersionFromOptionTable($prefix . $dimension->getColumnName());
        }
    }

    /**
     * @param string $pluginName
     * @param bool $checkPluginExistsInFilesystem if enabled, it won't rely on the information in the config file only
     *                                            but also check the filesystem if the plugin really is installed.
     *                                            For performance reasons this is not the case by default.
     * @return bool
     */
    public function isPluginInstalled($pluginName, $checkPluginExistsInFilesystem = false)
    {
        $pluginsInstalled = $this->getInstalledPluginsName();
        $isInstalledInConfig = in_array($pluginName, $pluginsInstalled);

        if ($isInstalledInConfig && $checkPluginExistsInFilesystem) {
            return $this->isPluginInFilesystem($pluginName);
        }

        return $isInstalledInConfig;
    }

    private function removeInstalledVersionFromOptionTable(string $name): void
    {
        $updater = new Updater();
        $updater->markComponentSuccessfullyUninstalled($name);
    }

    private function makeSureOnlyActivatedPluginsAreLoaded(): void
    {
        foreach ($this->getLoadedPlugins() as $pluginName => $plugin) {
            if (!in_array($pluginName, $this->pluginsToLoad)) {
                $this->unloadPlugin($plugin);
            }
        }
    }

    /**
     * Reading the plugins from the global.ini.php config file
     *
     * @return array
     */
    protected function getPluginsFromGlobalIniConfigFile()
    {
        return $this->pluginList->getPluginsBundledWithPiwik();
    }

    protected function isPluginEnabledByDefault(string $name): bool
    {
        $pluginsBundledWithPiwik = $this->getPluginsFromGlobalIniConfigFile();
        if (empty($pluginsBundledWithPiwik)) {
            return false;
        }
        return in_array($name, $pluginsBundledWithPiwik);
    }

    /**
     * @param list<string> $pluginsToLoad
     * @return list<string>
     */
    private function makePluginsToLoad(array $pluginsToLoad)
    {
        $pluginsToLoad = array_unique($pluginsToLoad);
        if ($this->doLoadAlwaysActivatedPlugins) {
            $pluginsToLoad = array_merge($pluginsToLoad, self::$pluginToAlwaysActivate);
        }
        $pluginsToLoad = array_unique($pluginsToLoad);
        return $this->pluginList->sortPlugins($pluginsToLoad);
    }

    /**
     * @return void
     */
    public function loadPluginTranslations()
    {
        /** @var Translator $translator */
        $translator = StaticContainer::get('Piwik\Translation\Translator');
        foreach ($this->getAllPluginsNames() as $pluginName) {
            $translator->addDirectory(self::getPluginDirectory($pluginName) . '/lang');
        }
    }

    /**
     * @return bool
     */
    public function hasPremiumFeatures()
    {
        foreach ($this->getPluginsLoadedAndActivated() as $activatedPlugin) {
            if ($activatedPlugin->isPremiumFeature()) {
                return true;
            }
        }
        return false;
    }

    private function savePluginTime(string $timingName, string $pluginName): void
    {
        $optionName = $timingName . $pluginName;

        try {
            Option::set($optionName, (string)time());
        } catch (\Exception $e) {
            if (SettingsPiwik::isMatomoInstalled()) {
                throw $e;
            }
            // we ignore any error while Matomo is not installed yet. refs #16741
        }
    }

    public function getVersion(string $pluginName): ?string
    {
        if ($this->isPluginLoaded($pluginName)) {
            return (string) $this->getLoadedPlugin($pluginName)->getVersion();
        } elseif (!$this->isPluginInFilesystem($pluginName)) {
            return null;
        } else {
            try {
                $plugin = new Plugin($pluginName);
                return (string) $plugin->getVersion();
            } catch (\Exception $e) {
                return null;
            }
        }
    }
}
