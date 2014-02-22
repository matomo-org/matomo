<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugin;

use Piwik\Common;
use Piwik\Config as PiwikConfig;
use Piwik\EventDispatcher;
use Piwik\Filesystem;
use Piwik\Option;
use Piwik\Plugin;
use Piwik\Singleton;
use Piwik\Theme;
use Piwik\Translate;
use Piwik\Updater;

require_once PIWIK_INCLUDE_PATH . '/core/EventDispatcher.php';

/**
 * The singleton that manages plugin loading/unloading and installation/uninstallation.
 *
 * @method static \Piwik\Plugin\Manager getInstance()
 */
class Manager extends Singleton
{
    protected $pluginsToLoad = array();

    protected $doLoadPlugins = true;
    /**
     * @var Plugin[]
     */
    protected $loadedPlugins = array();
    /**
     * Default theme used in Piwik.
     */
    const DEFAULT_THEME = "Zeitgeist";

    protected $doLoadAlwaysActivatedPlugins = true;

    // These are always activated and cannot be deactivated
    protected $pluginToAlwaysActivate = array(
        'CoreHome',
        'CoreUpdater',
        'CoreAdminHome',
        'CoreConsole',
        'CorePluginsAdmin',
        'CoreVisualizations',
        'Installation',
        'SitesManager',
        'UsersManager',
        'API',
        'Proxy',
        'LanguagesManager',

        // default Piwik theme, always enabled
        self::DEFAULT_THEME,
    );

    // Plugins bundled with core package, disabled by default
    protected $corePluginsDisabledByDefault = array(
        'DBStats',
        'DevicesDetection',
        'ExampleCommand',
        'ExampleSettingsPlugin',
        'ExampleUI',
        'ExampleVisualization',
        'ExamplePluginTemplate',
    );

    // Themes bundled with core package, disabled by default
    protected $coreThemesDisabledByDefault = array(
        'ExampleTheme',
        'LeftMenu',
        'Zeitgeist',
    );

    public function getPluginsToLoadDuringTests()
    {
        $toLoad = array();
        foreach($this->readPluginsDirectory() as $plugin) {
            $forceDisable = array(
                'ExampleVisualization', // adds an icon
                'LoginHttpAuth',  // other Login plugins would conflict
            );
            if(in_array($plugin, $forceDisable)) {
                continue;
            }

            // Load all default plugins
            $isPluginBundledWithCore = $this->isPluginBundledWithCore($plugin);

            // Load plugins from submodules
            $isPluginOfficiallySupported = $this->isPluginOfficialAndNotBundledWithCore($plugin);

            $loadPlugin = $isPluginBundledWithCore || $isPluginOfficiallySupported;

            // Do not enable other Themes
            $disabledThemes = $this->coreThemesDisabledByDefault;

            // PleineLune is officially supported, yet we don't want to enable another theme in tests (we test for Morpheus)
            $disabledThemes[] = "PleineLune";

            $isThemeDisabled = in_array($plugin, $disabledThemes);

            $loadPlugin = $loadPlugin && !$isThemeDisabled;
            if($loadPlugin) {
                $toLoad[] = $plugin;
            }
        }
        return $toLoad;
    }

    public function getCorePluginsDisabledByDefault()
    {
        return array_merge( $this->corePluginsDisabledByDefault, $this->coreThemesDisabledByDefault);
    }

    // If a plugin hooks onto at least an event starting with "Tracker.", we load the plugin during tracker
    const TRACKER_EVENT_PREFIX = 'Tracker.';

    /**
     * @param $pluginName
     * @return bool
     */
    public function isPluginOfficialAndNotBundledWithCore($pluginName)
    {
        static $gitModules;
        if(empty($gitModules)) {
            $gitModules = file_get_contents(PIWIK_INCLUDE_PATH . '/.gitmodules');
        }
        // All submodules are officially maintained plugins
        $isSubmodule = false !== strpos($gitModules, "plugins/" . $pluginName . "\n");
        return $isSubmodule;
    }

    /**
     * Update Plugins config
     *
     * @param array $plugins Plugins
     */
    private function updatePluginsConfig()
    {
        $section = PiwikConfig::getInstance()->Plugins;
        $section['Plugins'] = $this->pluginsToLoad;
        PiwikConfig::getInstance()->Plugins = $section;
    }

    /**
     * Update Plugins_Tracker config
     *
     * @param array $plugins Plugins
     */
    private function updatePluginsTrackerConfig($plugins)
    {
        $section = PiwikConfig::getInstance()->Plugins_Tracker;
        $section['Plugins_Tracker'] = $plugins;
        PiwikConfig::getInstance()->Plugins_Tracker = $section;
    }

    /**
     * Update PluginsInstalled config
     *
     * @param array $plugins Plugins
     */
    private function updatePluginsInstalledConfig($plugins)
    {
        $section = PiwikConfig::getInstance()->PluginsInstalled;
        $section['PluginsInstalled'] = $plugins;
        PiwikConfig::getInstance()->PluginsInstalled = $section;
    }

    /**
     * Returns true if plugin is always activated
     *
     * @param string $name Name of plugin
     * @return bool
     */
    private function isPluginAlwaysActivated($name)
    {
        return in_array($name, $this->pluginToAlwaysActivate);
    }

    /**
     * Returns true if the plugin can be uninstalled. Any non-core plugin can be uninstalled.
     *
     * @param $name
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
        || $this->isPluginAlwaysActivated($name);
    }

    /**
     * Returns `true` if plugin is loaded (in memory).
     *
     * @param string $name Name of plugin, eg, `'Acions'`.
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
     * @return array
     */
    public function readPluginsDirectory()
    {
        $pluginsName = _glob(self::getPluginsDirectory() . '*', GLOB_ONLYDIR);
        $result = array();
        if ($pluginsName != false) {
            foreach ($pluginsName as $path) {
                if (self::pluginStructureLooksValid($path)) {
                    $result[] = basename($path);
                }
            }
        }
        return $result;
    }

    public static function getPluginsDirectory()
    {
        return PIWIK_INCLUDE_PATH . '/plugins/';
    }

    /**
     * Deactivate plugin
     *
     * @param string $pluginName Name of plugin
     */
    public function deactivatePlugin($pluginName)
    {
        // execute deactivate() to let the plugin do cleanups
        $this->executePluginDeactivate($pluginName);

        $this->unloadPluginFromMemory($pluginName);

        $this->removePluginFromConfig($pluginName);

        $this->clearCache($pluginName);
    }


    /**
     * Uninstalls a Plugin (deletes plugin files from the disk)
     * Only deactivated plugins can be uninstalled
     *
     * @param $pluginName
     * @throws \Exception
     * @return bool
     */
    public function uninstallPlugin($pluginName)
    {
        if ($this->isPluginLoaded($pluginName)) {
            throw new \Exception("To uninstall the plugin $pluginName, first disable it in Piwik > Settings > Plugins");
        }
        $this->returnLoadedPluginsInfo();

        $this->executePluginDeactivate($pluginName);
        $this->executePluginUninstall($pluginName);

        $this->removePluginFromPluginsInstalledConfig($pluginName);

        $this->unloadPluginFromMemory($pluginName);

        $this->removePluginFromConfig($pluginName);

        Option::delete('version_' . $pluginName);
        \Piwik\Settings\Manager::cleanupPluginSettings($pluginName);
        $this->clearCache($pluginName);

        self::deletePluginFromFilesystem($pluginName);
        if ($this->isPluginInFilesystem($pluginName)) {
            return false;
        }
        return true;
    }

    /**
     * @param string $pluginName
     */
    private function clearCache($pluginName)
    {
        Filesystem::deleteAllCacheOnUpdate($pluginName);
    }

    public static function deletePluginFromFilesystem($plugin)
    {
        Filesystem::unlinkRecursive(PIWIK_INCLUDE_PATH . '/plugins/' . $plugin, $deleteRootToo = true);
    }

    /**
     * Install loaded plugins
     *
     * @return array Error messages of plugin install fails
     */
    public function installLoadedPlugins()
    {
        $messages = array();
        foreach ($this->getLoadedPlugins() as $plugin) {
            try {
                $this->installPluginIfNecessary($plugin);
            } catch (\Exception $e) {
                $messages[] = $e->getMessage();
            }
        }
        return $messages;
    }

    /**
     * Activate the specified plugin and install (if needed)
     *
     * @param string $pluginName Name of plugin
     * @throws \Exception
     */
    public function activatePlugin($pluginName)
    {
        $plugins = PiwikConfig::getInstance()->Plugins['Plugins'];
        if (in_array($pluginName, $plugins)) {
            throw new \Exception("Plugin '$pluginName' already activated.");
        }

        if (!$this->isPluginInFilesystem($pluginName)) {
            throw new \Exception("Plugin '$pluginName' cannot be found in the filesystem in plugins/ directory.");
            return;
        }
        $this->deactivateThemeIfTheme($pluginName);

        // Load plugin
        $plugin = $this->loadPlugin($pluginName);
        if ($plugin === null) {
            throw new \Exception("The plugin '$pluginName' was found in the filesystem, but could not be loaded.'");
            return;
        }
        $this->installPluginIfNecessary($plugin);
        $plugin->activate();

        EventDispatcher::getInstance()->postPendingEventsTo($plugin);

        $this->pluginsToLoad[] = $pluginName;

        $this->updatePluginsConfig();
        PiwikConfig::getInstance()->forceSave();

        $this->clearCache($pluginName);

    }

    protected function isPluginInFilesystem($pluginName)
    {
        $existingPlugins = $this->readPluginsDirectory();
        $isPluginInFilesystem = array_search($pluginName, $existingPlugins) !== false;
        return Filesystem::isValidFilename($pluginName)
        && $isPluginInFilesystem;
    }

    /**
     * Returns the currently enabled theme.
     * 
     * If no theme is enabled, the **Zeitgeist** plugin is returned (this is the base and default theme).
     *
     * @return Plugin
     * @api
     */
    public function getThemeEnabled()
    {
        $plugins = $this->getLoadedPlugins();

        $theme = false;
        foreach ($plugins as $plugin) {
            /* @var $plugin Plugin */
            if ($plugin->isTheme()
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

    public function getNumberOfActivatedPlugins()
    {
        $counter = 0;

        $pluginNames = $this->getLoadedPluginsName();
        foreach ($pluginNames as $pluginName) {
            if ($this->isPluginActivated($pluginName)) {
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
     * @api
     */
    public function returnLoadedPluginsInfo()
    {
        $language = Translate::getLanguageToLoad();

        $plugins = array();

        $listPlugins = array_merge(
            $this->readPluginsDirectory(),
            PiwikConfig::getInstance()->Plugins['Plugins']
        );
        $listPlugins = array_unique($listPlugins);
        foreach ($listPlugins as $pluginName) {
            // Hide plugins that are never going to be used
            if($this->isPluginBogus($pluginName)) {
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
                $this->loadTranslation($pluginName, $language);
                $this->loadPlugin($pluginName);
                $info = array(
                    'activated'       => $this->isPluginActivated($pluginName),
                    'alwaysActivated' => $this->isPluginAlwaysActivated($pluginName),
                    'uninstallable'   => $this->isPluginUninstallable($pluginName),
                );
            }

            $plugins[$pluginName] = $info;
        }
        $this->loadPluginTranslations();

        $loadedPlugins = $this->getLoadedPlugins();
        foreach ($loadedPlugins as $oPlugin) {
            $pluginName = $oPlugin->getPluginName();
            $plugins[$pluginName]['info'] = $oPlugin->getInformation();
        }
        return $plugins;
    }


    protected static function isManifestFileFound($path)
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
        // Reading the plugins from the global.ini.php config file
        $pluginsBundledWithPiwik = PiwikConfig::getInstance()->getFromGlobalConfig('Plugins');
        $pluginsBundledWithPiwik = $pluginsBundledWithPiwik['Plugins'];

        return (!empty($pluginsBundledWithPiwik)
                    && in_array($name, $pluginsBundledWithPiwik))
                || in_array($name, $this->getCorePluginsDisabledByDefault())
                || $name == self::DEFAULT_THEME;
    }

    protected function isPluginThirdPartyAndBogus($pluginName)
    {
        if($this->isPluginBundledWithCore($pluginName)) {
            return false;
        }
        if($this->isPluginBogus($pluginName)) {
            return true;
        }

        $path = $this->getPluginsDirectory() . $pluginName;
        if(!$this->isManifestFileFound($path)) {
            return true;
        }
        return false;
    }


    /**
     * Load the specified plugins.
     *
     * @param array $pluginsToLoad Array of plugins to load.
     */
    public function loadPlugins(array $pluginsToLoad)
    {
        // case no plugins to load
        if (is_null($pluginsToLoad)) {
            $pluginsToLoad = array();
        }
        $pluginsToLoad = array_unique($pluginsToLoad);
        $this->pluginsToLoad = $pluginsToLoad;
        $this->reloadPlugins();
    }

    /**
     * Disable plugin loading.
     */
    public function doNotLoadPlugins()
    {
        $this->doLoadPlugins = false;
    }

    /**
     * Disable loading of "always activated" plugins.
     */
    public function doNotLoadAlwaysActivatedPlugins()
    {
        $this->doLoadAlwaysActivatedPlugins = false;
    }

    /**
     * Load translations for loaded plugins
     *
     * @param bool|string $language Optional language code
     */
    public function loadPluginTranslations($language = false)
    {
        if (empty($language)) {
            $language = Translate::getLanguageToLoad();
        }
        $plugins = $this->getLoadedPlugins();

        foreach ($plugins as $plugin) {
            $this->loadTranslation($plugin, $language);
        }
    }

    /**
     * Execute postLoad() hook for loaded plugins
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
     * @return array
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
     *         'UserSettings' => Plugin $pluginObject,
     *     );
     *
     * @return Plugin[]
     */
    public function getLoadedPlugins()
    {
        return $this->loadedPlugins;
    }

    /**
     * Returns a list of all names of currently activated plugin eg,
     *
     *     array(
     *         'UserCountry'
     *         'UserSettings'
     *     );
     *
     * @return string[]
     */
    public function getActivatedPlugins()
    {
        return $this->pluginsToLoad;
    }

    /**
     * Returns a Plugin object by name.
     *
     * @param string $name The name of the plugin, eg, `'Actions'`.
     * @throws \Exception If the plugin has not been loaded.
     * @return Plugin
     */
    public function getLoadedPlugin($name)
    {
        if (!isset($this->loadedPlugins[$name])) {
            throw new \Exception("The plugin '$name' has not been loaded.");
        }
        return $this->loadedPlugins[$name];
    }

    /**
     * Load the plugins classes installed.
     * Register the observers for every plugin.
     */
    private function reloadPlugins()
    {
        if ($this->doLoadAlwaysActivatedPlugins) {
            $this->pluginsToLoad = array_merge($this->pluginsToLoad, $this->pluginToAlwaysActivate);
        }

        $this->pluginsToLoad = array_unique($this->pluginsToLoad);

        foreach ($this->pluginsToLoad as $pluginName) {
            if (!$this->isPluginLoaded($pluginName)
                && !$this->isPluginThirdPartyAndBogus($pluginName)
            ) {
                $newPlugin = $this->loadPlugin($pluginName);
                if ($newPlugin === null) {
                    continue;
                }

                EventDispatcher::getInstance()->postPendingEventsTo($newPlugin);
            }
        }
    }

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
     * Used in tests
     *
     * @return array
     */
    public static function getAllPluginsNames()
    {
        $pluginsToLoad = array_merge(
            PiwikConfig::getInstance()->Plugins['Plugins'],
            self::getInstance()->readPluginsDirectory(),
            self::getInstance()->getCorePluginsDisabledByDefault()
        );
        $pluginsToLoad = array_values(array_unique($pluginsToLoad));
        return $pluginsToLoad;
    }


    /**
     * Loads the plugin filename and instantiates the plugin with the given name, eg. UserCountry
     *
     * @param string $pluginName
     * @throws \Exception
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
     * @param $pluginName
     * @return Plugin
     * @throws \Exception
     */
    protected function makePluginClass($pluginName)
    {
        $pluginFileName = sprintf("%s/%s.php", $pluginName, $pluginName);
        $pluginClassName = $pluginName;

        if (!Filesystem::isValidFilename($pluginName)) {
            throw new \Exception(sprintf("The plugin filename '%s' is not a valid filename", $pluginFileName));
        }

        $path = self::getPluginsDirectory() . $pluginFileName;


        if (!file_exists($path)) {
            // Create the smallest minimal Piwik Plugin
            // Eg. Used for Zeitgeist default theme which does not have a Zeitgeist.php file
            return new Plugin($pluginName);
        }

        require_once $path;

        $namespacedClass = $this->getClassNamePlugin($pluginName);
        if (!class_exists($namespacedClass, false)) {
            throw new \Exception("The class $pluginClassName couldn't be found in the file '$path'");
        }
        $newPlugin = new $namespacedClass;

        if (!($newPlugin instanceof Plugin)) {
            throw new \Exception("The plugin $pluginClassName in the file $path must inherit from Plugin.");
        }
        return $newPlugin;
    }

    protected function getClassNamePlugin($pluginName)
    {
        $className = $pluginName;
        if ($pluginName == 'API') {
            $className = 'Plugin';
        }
        return "\\Piwik\\Plugins\\$pluginName\\$className";
    }

    /**
     * Unload plugin
     *
     * @param Plugin|string $plugin
     * @throws \Exception
     */
    public function unloadPlugin($plugin)
    {
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
     */
    public function unloadPlugins()
    {
        $pluginsLoaded = $this->getLoadedPlugins();
        foreach ($pluginsLoaded as $plugin) {
            $this->unloadPlugin($plugin);
        }
    }

    /**
     * Install a specific plugin
     *
     * @param Plugin $plugin
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
     * @param Plugin $newPlugin
     */
    private function addLoadedPlugin($pluginName, Plugin $newPlugin)
    {
        $this->loadedPlugins[$pluginName] = $newPlugin;
    }

    /**
     * Load translation
     *
     * @param Plugin $plugin
     * @param string $langCode
     * @throws \Exception
     * @return bool whether the translation was found and loaded
     */
    private function loadTranslation($plugin, $langCode)
    {
        // we are in Tracker mode if Loader is not (yet) loaded
        if (!class_exists('Piwik\\Loader', false)) {
            return false;
        }

        if (is_string($plugin)) {
            $pluginName = $plugin;
        } else {
            $pluginName = $plugin->getPluginName();
        }

        $path = self::getPluginsDirectory() . $pluginName . '/lang/%s.json';

        $defaultLangPath = sprintf($path, $langCode);
        $defaultEnglishLangPath = sprintf($path, 'en');

        $translationsLoaded = false;

        // merge in english translations as default first
        if (file_exists($defaultEnglishLangPath)) {
            $translations = $this->getTranslationsFromFile($defaultEnglishLangPath);
            $translationsLoaded = true;
            if (isset($translations[$pluginName])) {
                // only merge translations of plugin - prevents overwritten strings
                Translate::mergeTranslationArray(array($pluginName => $translations[$pluginName]));
            }
        }

        // merge in specific language translations (to overwrite english defaults)
        if (file_exists($defaultLangPath)) {
            $translations = $this->getTranslationsFromFile($defaultLangPath);
            $translationsLoaded = true;
            if (isset($translations[$pluginName])) {
                // only merge translations of plugin - prevents overwritten strings
                Translate::mergeTranslationArray(array($pluginName => $translations[$pluginName]));
            }
        }

        return $translationsLoaded;
    }

    /**
     * Return names of all installed plugins.
     *
     * @return array
     * @api
     */
    public function getInstalledPluginsName()
    {
        $pluginNames = PiwikConfig::getInstance()->PluginsInstalled['PluginsInstalled'];
        return $pluginNames;
    }

    /**
     * Returns names of plugins that should be loaded, but cannot be since their
     * files cannot be found.
     *
     * @return array
     * @api
     */
    public function getMissingPlugins()
    {
        $missingPlugins = array();
        if (isset(PiwikConfig::getInstance()->Plugins['Plugins'])) {
            $plugins = PiwikConfig::getInstance()->Plugins['Plugins'];
            foreach ($plugins as $pluginName) {
                // if a plugin is listed in the config, but is not loaded, it does not exist in the folder
                if (!self::getInstance()->isPluginLoaded($pluginName)
                    && !$this->isPluginBogus($pluginName)
                ) {
                    $missingPlugins[] = $pluginName;
                }
            }
        }
        return $missingPlugins;
    }

    /**
     * Install a plugin, if necessary
     *
     * @param Plugin $plugin
     */
    private function installPluginIfNecessary(Plugin $plugin)
    {
        $pluginName = $plugin->getPluginName();

        $saveConfig = false;

        // is the plugin already installed or is it the first time we activate it?
        $pluginsInstalled = $this->getInstalledPluginsName();

        if (!in_array($pluginName, $pluginsInstalled)) {
            $this->executePluginInstall($plugin);
            $pluginsInstalled[] = $pluginName;
            $this->updatePluginsInstalledConfig($pluginsInstalled);
            Updater::recordComponentSuccessfullyUpdated($plugin->getPluginName(), $plugin->getVersion());
            $saveConfig = true;
        }

        if ($this->isTrackerPlugin($plugin)) {
            $pluginsTracker = PiwikConfig::getInstance()->Plugins_Tracker['Plugins_Tracker'];
            if (is_null($pluginsTracker)) {
                $pluginsTracker = array();
            }
            if (!in_array($pluginName, $pluginsTracker)) {
                $pluginsTracker[] = $pluginName;
                $this->updatePluginsTrackerConfig($pluginsTracker);
                $saveConfig = true;
            }
        }

        if ($saveConfig) {
            PiwikConfig::getInstance()->forceSave();
        }
    }

    protected function isTrackerPlugin(Plugin $plugin)
    {
        $hooks = $plugin->getListHooksRegistered();
        $hookNames = array_keys($hooks);
        foreach ($hookNames as $name) {
            if (strpos($name, self::TRACKER_EVENT_PREFIX) === 0) {
                return true;
            }
            if ($name === 'Request.initAuthenticationObject') {
                return true;
            }
        }
        return false;
    }

    private static function pluginStructureLooksValid($path)
    {
        $name = basename($path);
        return file_exists($path . "/" . $name . ".php")
        || self::isManifestFileFound($path);
    }

    /**
     * @param $pluginName
     */
    private function removePluginFromPluginsInstalledConfig($pluginName)
    {
        $pluginsInstalled = PiwikConfig::getInstance()->PluginsInstalled['PluginsInstalled'];
        $key = array_search($pluginName, $pluginsInstalled);
        if ($key !== false) {
            unset($pluginsInstalled[$key]);
        }

        $this->updatePluginsInstalledConfig($pluginsInstalled);
    }

    private function removePluginFromTrackerConfig($pluginName)
    {
        $pluginsTracker = PiwikConfig::getInstance()->Plugins_Tracker['Plugins_Tracker'];
        if (!is_null($pluginsTracker)) {
            $key = array_search($pluginName, $pluginsTracker);
            if ($key !== false) {
                unset($pluginsTracker[$key]);
                $this->updatePluginsTrackerConfig($pluginsTracker);
            }
        }
    }

    /**
     * @param  string $pathToTranslationFile
     * @throws \Exception
     * @return mixed
     */
    private function getTranslationsFromFile($pathToTranslationFile)
    {
        $data         = file_get_contents($pathToTranslationFile);
        $translations = json_decode($data, true);

        if (is_null($translations) && Common::hasJsonErrorOccurred()) {
            $jsonError = Common::getLastJsonError();

            $message = sprintf('Not able to load translation file %s: %s', $pathToTranslationFile, $jsonError);

            throw new \Exception($message);
        }

        return $translations;
    }

    /**
     * @param $pluginName
     * @return bool
     */
    private function isPluginBogus($pluginName)
    {
        $bogusPlugins = array(
            'PluginMarketplace', //defines a plugin.json but 1.x Piwik plugin
            'DoNotTrack', // Removed in 2.0.3
            'AnonymizeIP', // Removed in 2.0.3
        );
        return in_array($pluginName, $bogusPlugins);
    }

    private function deactivateThemeIfTheme($pluginName)
    {
        // Only one theme enabled at a time
        $themeEnabled = $this->getThemeEnabled();
        if ($themeEnabled->getPluginName() != self::DEFAULT_THEME) {
            $themeAlreadyEnabled = $themeEnabled->getPluginName();

            $plugin = $this->loadPlugin($pluginName);
            if ($plugin->isTheme()) {
                $this->deactivatePlugin($themeAlreadyEnabled);
            }
        }
    }

    /**
     * @param $pluginName
     */
    private function executePluginDeactivate($pluginName)
    {
        if (!$this->isPluginBogus($pluginName)) {
            $plugin = $this->loadPlugin($pluginName);
            if ($plugin !== null) {
                $plugin->deactivate();
            }
        }
    }

    /**
     * @param $pluginName
     */
    private function unloadPluginFromMemory($pluginName)
    {
        $key = array_search($pluginName, $this->pluginsToLoad);
        if ($key !== false) {
            unset($this->pluginsToLoad[$key]);
        }
    }

    /**
     * @param $pluginName
     */
    private function removePluginFromConfig($pluginName)
    {
        $this->updatePluginsConfig();
        $this->removePluginFromTrackerConfig($pluginName);
        PiwikConfig::getInstance()->forceSave();
    }

    /**
     * @param $pluginName
     */
    private function executePluginUninstall($pluginName)
    {
        try {
            $plugin = $this->getLoadedPlugin($pluginName);
            $plugin->uninstall();
        } catch (\Exception $e) {
        }
    }
}

/**
 */
class PluginException extends \Exception
{
    function __construct($pluginName, $message)
    {
        parent::__construct("There was a problem installing the plugin " . $pluginName . ": " . $message . "
				If this plugin has already been installed, and if you want to hide this message</b>, you must add the following line under the
				[PluginsInstalled]
				entry in your config/config.ini.php file:
				PluginsInstalled[] = $pluginName");
    }
}
