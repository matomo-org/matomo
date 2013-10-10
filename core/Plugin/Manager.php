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

namespace Piwik\Plugin;

use Piwik\Config;
use Piwik\EventDispatcher;
use Piwik\Filesystem;
use Piwik\Option;
use Piwik\Plugin;
use Piwik\Singleton;
use Piwik\Translate;
use Piwik\Updater;

require_once PIWIK_INCLUDE_PATH . '/core/EventDispatcher.php';

/**
 * Plugin manager
 *
 * @package Piwik
 * @subpackage Manager
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
    protected $pluginToAlwaysActivate = array(
        'CoreHome',
        'CoreUpdater',
        'CoreAdminHome',
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

    protected $corePluginsDisabledByDefault = array(
        'AnonymizeIP',
        'DBStats',
        'DevicesDetection',
        'TreemapVisualization', // should be moved to marketplace
    );

    // If a plugin hooks onto at least an event starting with "Tracker.", we load the plugin during tracker
    const TRACKER_EVENT_PREFIX = 'Tracker.';

    /**
     * Update Plugins config
     *
     * @param array $plugins Plugins
     */
    private function updatePluginsConfig($plugins)
    {
        $section = Config::getInstance()->Plugins;
        $section['Plugins'] = $plugins;
        Config::getInstance()->Plugins = $section;
    }

    /**
     * Update Plugins_Tracker config
     *
     * @param array $plugins Plugins
     */
    private function updatePluginsTrackerConfig($plugins)
    {
        $section = Config::getInstance()->Plugins_Tracker;
        $section['Plugins_Tracker'] = $plugins;
        Config::getInstance()->Plugins_Tracker = $section;
    }

    /**
     * Update PluginsInstalled config
     *
     * @param array $plugins Plugins
     */
    private function updatePluginsInstalledConfig($plugins)
    {
        $section = Config::getInstance()->PluginsInstalled;
        $section['PluginsInstalled'] = $plugins;
        Config::getInstance()->PluginsInstalled = $section;
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
     * Returns true if plugin has been activated
     *
     * @param string $name Name of plugin
     * @return bool
     */
    public function isPluginActivated($name)
    {
        return in_array($name, $this->pluginsToLoad)
        || $this->isPluginAlwaysActivated($name);
    }

    /**
     * Returns true if plugin is loaded (in memory)
     *
     * @param string $name Name of plugin
     * @return bool
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
     * Uninstalls a Plugin (deletes plugin files from the disk)
     * Only deactivated plugins can be uninstalled
     *
     * @param $pluginName
     * @throws \Exception
     * @return bool
     */
    public function uninstallPlugin($pluginName)
    {
        if ($this->isPluginActivated($pluginName)) {
            throw new \Exception("To uninstall the plugin $pluginName, first disable it in Piwik > Settings > Plugins");
        }
        if (!$this->isPluginInFilesystem($pluginName)) {
            throw new \Exception("You are trying to uninstall the plugin $pluginName but it was not found in the directory piwik/plugins/");
        }

        $this->returnLoadedPluginsInfo();
        $plugin = $this->getLoadedPlugin($pluginName);
        $plugin->uninstall();
        Option::delete('version_' . $pluginName);

        $this->removePluginFromPluginsConfig($pluginName);
        $this->removePluginFromPluginsInstalledConfig($pluginName);
        $this->removePluginFromTrackerConfig($pluginName);
        Config::getInstance()->forceSave();

        Filesystem::deleteAllCacheOnUpdate();

        self::deletePluginFromFilesystem($pluginName);
        if ($this->isPluginInFilesystem($pluginName)) {
            return false;
        }
        return true;
    }

    public static function deletePluginFromFilesystem($plugin)
    {
        Filesystem::unlinkRecursive(PIWIK_INCLUDE_PATH . '/plugins/' . $plugin, $deleteRootToo = true);
    }

    /**
     * Deactivate plugin
     *
     * @param string $pluginName Name of plugin
     * @param array|bool $plugins Array of plugin names
     * @return array|bool
     */
    public function deactivatePlugin($pluginName, $plugins = false)
    {
        if (empty($plugins)) {
            $plugins = $this->pluginsToLoad;
        }

        $plugin = $this->loadPlugin($pluginName);
        if ($plugin !== null) {
            $plugin->deactivate();
        }

        $this->removePluginFromPluginsConfig($pluginName, $plugins);
        $this->removePluginFromTrackerConfig($pluginName);

        Config::getInstance()->forceSave();
        Filesystem::deleteAllCacheOnUpdate();

        return $plugins;
    }

    /**
     * Install loaded plugins
     */
    public function installLoadedPlugins()
    {
        foreach ($this->getLoadedPlugins() as $plugin) {
            try {
                $this->installPluginIfNecessary($plugin);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }

    /**
     * Activate the specified plugin and install (if needed)
     *
     * @param string $pluginName Name of plugin
     * @throws \Exception
     */
    public function activatePlugin($pluginName)
    {
        $plugins = Config::getInstance()->Plugins['Plugins'];
        if (in_array($pluginName, $plugins)) {
            throw new \Exception("Plugin '$pluginName' already activated.");
        }

        if (!$this->isPluginInFilesystem($pluginName)) {
            // ToDo: This fails in tracker-mode. We should log this however.
            //Piwik::log(sprintf("Unable to find the plugin '%s' in activatePlugin.", $pluginName));
            return;
        }

        // Only one theme enabled at a time
        $themeEnabled = $this->getThemeEnabled();
        if ($themeEnabled && $themeEnabled->getPluginName() != self::DEFAULT_THEME) {
            $themeAlreadyEnabled = $themeEnabled->getPluginName();
            $plugin = $this->loadPlugin($pluginName);
            if ($plugin->isTheme()) {
                $plugins = $this->deactivatePlugin($themeAlreadyEnabled, $plugins);
            }
        }

        // Load plugin
        $plugin = $this->loadPlugin($pluginName);
        if ($plugin === null) {
            return;
        }
        $this->installPluginIfNecessary($plugin);
        $plugin->activate();

        // we add the plugin to the list of activated plugins
        if (!in_array($pluginName, $plugins)) {
            $plugins[] = $pluginName;
        }
        $plugins = array_unique($plugins);


        // the config file will automatically be saved with the new plugin
        $this->updatePluginsConfig($plugins);
        Config::getInstance()->forceSave();

        Filesystem::deleteAllCacheOnUpdate();
    }

    protected function isPluginInFilesystem($pluginName)
    {
        $existingPlugins = $this->readPluginsDirectory();
        $isPluginInFilesystem = array_search($pluginName, $existingPlugins) !== false;
        return Filesystem::isValidFilename($pluginName)
        && $isPluginInFilesystem;
    }

    /**
     * Returns the name of the non default theme currently enabled.
     *
     * If Zeitgeist is enabled, returns false (nb: Zeitgeist cannot be disabled)
     *
     * @return Plugin
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
     * Loads in memory the Plugins specified in the config.ini.php file
     *
     * @return array
     */
    public function returnLoadedPluginsInfo()
    {
        $plugins = array();

        $listPlugins = array_merge(
            $this->readPluginsDirectory(),
            Config::getInstance()->Plugins['Plugins']
        );
        $listPlugins = array_unique($listPlugins);
        foreach ($listPlugins as $pluginName) {
            // If the plugin is not core and looks bogus, do not load
            if ($this->isPluginThirdPartyAndBogus($pluginName)) {
                $info = array(
                    'invalid'         => true,
                    'activated'       => false,
                    'alwaysActivated' => false,
                    'uninstallable'   => true,
                );
            } else {
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

    public function isPluginBundledWithCore($name)
    {
        // Reading the plugins from the global.ini.php config file
        $pluginsBundledWithPiwik = Config::getInstance()->getFromDefaultConfig('Plugins');
        $pluginsBundledWithPiwik = $pluginsBundledWithPiwik['Plugins'];

        return (!empty($pluginsBundledWithPiwik)
            && in_array($name, $pluginsBundledWithPiwik))
        || in_array($name, $this->corePluginsDisabledByDefault);
    }

    protected function isPluginThirdPartyAndBogus($pluginName)
    {
        $path = $this->getPluginsDirectory() . $pluginName;
        $isBogus = !$this->isPluginBundledWithCore($pluginName)
            && !$this->isManifestFileFound($path);
        return $isBogus;
    }


    /**
     * Load the specified plugins
     *
     * @param array $pluginsToLoad Array of plugins to load
     */
    public function loadPlugins(array $pluginsToLoad)
    {
        // case no plugins to load
        if (is_null($pluginsToLoad)) {
            $pluginsToLoad = array();
        }
        $this->pluginsToLoad = $pluginsToLoad;
        $this->reloadPlugins();
    }

    /**
     * Disable plugin loading
     */
    public function doNotLoadPlugins()
    {
        $this->doLoadPlugins = false;
    }

    /**
     * Disable loading of "always activated" plugins
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
     *
     * @see Piwik_Plugin::postLoad()
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
     * Returns an array of key,value with the following format: array(
     *        'UserCountry' => Plugin $pluginObject,
     *        'UserSettings' => Plugin $pluginObject,
     *    );
     *
     * @return Plugin[]
     */
    public function getLoadedPlugins()
    {
        return $this->loadedPlugins;
    }

    /**
     * Returns the given Plugin object
     *
     * @param string $name
     * @throws \Exception
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
        $this->pluginsToLoad = array_unique($this->pluginsToLoad);

        if ($this->doLoadAlwaysActivatedPlugins) {
            $this->pluginsToLoad = array_merge($this->pluginsToLoad, $this->pluginToAlwaysActivate);
        }

        foreach ($this->pluginsToLoad as $pluginName) {
            if (!$this->isPluginLoaded($pluginName)
                && !$this->isPluginThirdPartyAndBogus($pluginName)
            ) {
                $newPlugin = $this->loadPlugin($pluginName);
                if ($newPlugin === null) {
                    continue;
                }
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
     * Loads the plugin filename and instantiates the plugin with the given name, eg. UserCountry
     * Do NOT give the class name ie. UserCountry, but give the plugin name ie. UserCountry
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

        EventDispatcher::getInstance()->postPendingEventsTo($newPlugin);

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
     * Install loaded plugins
     */
    private function installPlugins()
    {
        foreach ($this->getLoadedPlugins() as $plugin) {
            $this->installPlugin($plugin);
        }
    }

    /**
     * Install a specific plugin
     *
     * @param Plugin $plugin
     * @throws \Piwik\Plugin\Manager_PluginException if installation fails
     */
    private function installPlugin(Plugin $plugin)
    {
        try {
            $plugin->install();
        } catch (\Exception $e) {
            throw new \Piwik\Plugin\PluginException($plugin->getPluginName(), $e->getMessage());
        }
        Updater::recordComponentSuccessfullyUpdated($plugin->getPluginName(), $plugin->getVersion());
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
        if (!class_exists('Piwik\Loader', false)) {
            return false;
        }

        $pluginName = $plugin->getPluginName();

        $path = self::getPluginsDirectory() . $pluginName . '/lang/%s.json';

        $defaultLangPath = sprintf($path, $langCode);
        $defaultEnglishLangPath = sprintf($path, 'en');

        if (file_exists($defaultLangPath)) {
            $data = file_get_contents($defaultLangPath);
            $translations = json_decode($data, true);
        } elseif (file_exists($defaultEnglishLangPath)) {
            $data = file_get_contents($defaultEnglishLangPath);
            $translations = json_decode($data, true);
        } else {
            return false;
        }

        if (isset($translations[$pluginName])) {
            // only merge translations of plugin - prevents overwritten strings
            Translate::mergeTranslationArray(array($pluginName => $translations[$pluginName]));
        }
        return true;
    }

    /**
     * Return names of installed plugins
     *
     * @return array
     */
    public function getInstalledPluginsName()
    {
        $pluginNames = Config::getInstance()->PluginsInstalled['PluginsInstalled'];
        return $pluginNames;
    }

    /**
     * Returns names of plugins that should be loaded, but cannot be since their
     * files cannot be found.
     *
     * @return array
     */
    public function getMissingPlugins()
    {
        $missingPlugins = array();
        if (isset(Config::getInstance()->Plugins['Plugins'])) {
            $plugins = Config::getInstance()->Plugins['Plugins'];
            foreach ($plugins as $pluginName) {
                // if a plugin is listed in the config, but is not loaded, it does not exist in the folder
                if (!self::getInstance()->isPluginLoaded($pluginName)) {
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
            $this->installPlugin($plugin);
            $pluginsInstalled[] = $pluginName;
            $this->updatePluginsInstalledConfig($pluginsInstalled);
            $saveConfig = true;
        }

        if ($this->isTrackerPlugin($plugin)) {
            $pluginsTracker = Config::getInstance()->Plugins_Tracker['Plugins_Tracker'];
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
            Config::getInstance()->forceSave();
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
        $pluginsInstalled = Config::getInstance()->PluginsInstalled['PluginsInstalled'];
        $key = array_search($pluginName, $pluginsInstalled);
        if ($key !== false) {
            unset($pluginsInstalled[$key]);
        }

        $this->updatePluginsInstalledConfig($pluginsInstalled);
    }

    /**
     * @param $pluginName
     * @param $plugins
     * @return mixed
     */
    private function removePluginFromPluginsConfig($pluginName, $plugins = false)
    {
        if (empty($plugins)) {
            $plugins = $this->pluginsToLoad;
        }

        $key = array_search($pluginName, $plugins);

        if ($key !== false) {
            unset($plugins[$key]);
        }

        $this->updatePluginsConfig($plugins);
    }

    /**
     * @param $pluginName
     */
    private function removePluginFromTrackerConfig($pluginName)
    {
        $pluginsTracker = Config::getInstance()->Plugins_Tracker['Plugins_Tracker'];
        if (!is_null($pluginsTracker)) {
            $key = array_search($pluginName, $pluginsTracker);
            if ($key !== false) {
                unset($pluginsTracker[$key]);
                $this->updatePluginsTrackerConfig($pluginsTracker);
            }
        }
    }
}

/**
 * @package Piwik
 * @subpackage Manager
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

