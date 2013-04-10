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
 * @see core/Menu/Abstract.php
 * @see core/Menu/Main.php
 * @see core/Menu/Admin.php
 * @see core/Menu/Top.php
 * @see core/PluginsFunctions/WidgetsList.php
 * @see core/PluginsFunctions/Sql.php
 */
require_once PIWIK_INCLUDE_PATH . '/core/Menu/Abstract.php';
require_once PIWIK_INCLUDE_PATH . '/core/Menu/Main.php';
require_once PIWIK_INCLUDE_PATH . '/core/Menu/Admin.php';
require_once PIWIK_INCLUDE_PATH . '/core/Menu/Top.php';
require_once PIWIK_INCLUDE_PATH . '/core/PluginsFunctions/WidgetsList.php';
require_once PIWIK_INCLUDE_PATH . '/core/PluginsFunctions/Sql.php';

/**
 * Plugin manager
 *
 * @package Piwik
 * @subpackage Piwik_PluginsManager
 */
class Piwik_PluginsManager
{
    /**
     * @var Event_Dispatcher
     */
    public $dispatcher;

    protected $pluginsToLoad = array();

    protected $doLoadPlugins = true;
    protected $loadedPlugins = array();

    protected $doLoadAlwaysActivatedPlugins = true;
    protected $pluginToAlwaysActivate = array(
        'CoreHome',
        'CoreUpdater',
        'CoreAdminHome',
        'CorePluginsAdmin',
        'Installation',
        'SitesManager',
        'UsersManager',
        'API',
        'Proxy',
        'LanguagesManager',
    );

    static private $instance = null;

    /**
     * Returns the singleton Piwik_PluginsManager
     *
     * @return Piwik_PluginsManager
     */
    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->dispatcher = Event_Dispatcher::getInstance();
    }

    /**
     * Update Plugins config
     *
     * @param array $plugins Plugins
     */
    private function updatePluginsConfig($plugins)
    {
        $section = Piwik_Config::getInstance()->Plugins;
        $section['Plugins'] = $plugins;
        Piwik_Config::getInstance()->Plugins = $section;
    }

    /**
     * Update Plugins_Tracker config
     *
     * @param array $plugins Plugins
     */
    private function updatePluginsTrackerConfig($plugins)
    {
        $section = Piwik_Config::getInstance()->Plugins_Tracker;
        $section['Plugins_Tracker'] = $plugins;
        Piwik_Config::getInstance()->Plugins_Tracker = $section;
    }

    /**
     * Update PluginsInstalled config
     *
     * @param array $plugins Plugins
     */
    private function updatePluginsInstalledConfig($plugins)
    {
        $section = Piwik_Config::getInstance()->PluginsInstalled;
        $section['PluginsInstalled'] = $plugins;
        Piwik_Config::getInstance()->PluginsInstalled = $section;
    }

    /**
     * Returns true if plugin is always activated
     *
     * @param string $name  Name of plugin
     * @return bool
     */
    public function isPluginAlwaysActivated($name)
    {
        return in_array($name, $this->pluginToAlwaysActivate);
    }

    /**
     * Returns true if plugin has been activated
     *
     * @param string $name  Name of plugin
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
     * @param string $name  Name of plugin
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
        $pluginsName = _glob(PIWIK_INCLUDE_PATH . '/plugins/*', GLOB_ONLYDIR);
        $result = array();
        if ($pluginsName != false) {
            foreach ($pluginsName as $path) {
                $name = basename($path);
                if (file_exists($path . '/' . $name . '.php')) // only add folder if a Plugin/Plugin.php file exists
                {
                    $result[] = $name;
                }
            }
        }
        return $result;
    }

    /**
     * Deactivate plugin
     *
     * @param string $pluginName  Name of plugin
     */
    public function deactivatePlugin($pluginName)
    {
        $plugins = $this->pluginsToLoad;
        $key = array_search($pluginName, $plugins);

        $plugin = $this->loadPlugin($pluginName);
        if ($plugin !== null) {
            $plugin->deactivate();
        }

        if ($key !== false) {
            unset($plugins[$key]);
        }
        $this->updatePluginsConfig($plugins);

        $pluginsTracker = Piwik_Config::getInstance()->Plugins_Tracker['Plugins_Tracker'];
        if (!is_null($pluginsTracker)) {
            $key = array_search($pluginName, $pluginsTracker);
            if ($key !== false) {
                unset($pluginsTracker[$key]);
                $this->updatePluginsTrackerConfig($pluginsTracker);
            }
        }

        // Delete merged js/css files to force regenerations to exclude the deactivated plugin
        Piwik_Config::getInstance()->forceSave();
        Piwik::deleteAllCacheOnUpdate();
    }

    /**
     * Install loaded plugins
     */
    public function installLoadedPlugins()
    {
        foreach ($this->getLoadedPlugins() as $plugin) {
            try {
                $this->installPluginIfNecessary($plugin);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
    }

    /**
     * Activate the specified plugin and install (if needed)
     *
     * @param string $pluginName  Name of plugin
     * @throws Exception
     */
    public function activatePlugin($pluginName)
    {
        $plugins = Piwik_Config::getInstance()->Plugins['Plugins'];
        if (in_array($pluginName, $plugins)) {
            throw new Exception("Plugin '$pluginName' already activated.");
        }

        $existingPlugins = $this->readPluginsDirectory();
        if (array_search($pluginName, $existingPlugins) === false) {
            // ToDo: This fails in tracker-mode. We should log this however.
            //Piwik::log(sprintf("Unable to find the plugin '%s' in activatePlugin.", $pluginName));
            return;
        }

        $plugin = $this->loadPlugin($pluginName);
        if ($plugin === null) {
            return;
        }

        $this->installPluginIfNecessary($plugin);

        $plugin->activate();

        // we add the plugin to the list of activated plugins
        if (!in_array($pluginName, $plugins)) {
            $plugins[] = $pluginName;
        } else {
            // clean up if we find a dupe
            $plugins = array_unique($plugins);
        }

        // the config file will automatically be saved with the new plugin
        $this->updatePluginsConfig($plugins);
        Piwik_Config::getInstance()->forceSave();

        // Delete merged js/css files to force regenerations to include the activated plugin
        Piwik::deleteAllCacheOnUpdate();
    }

    /**
     * Load the specified plugins
     *
     * @param array $pluginsToLoad  Array of plugins to load
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
     * @param bool|string $language  Optional language code
     */
    public function loadPluginTranslations($language = false)
    {
        if (empty($language)) {
            $language = Piwik_Translate::getInstance()->getLanguageToLoad();
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
     * Returns an array containing the plugins class names (eg. 'Piwik_UserCountry' and NOT 'UserCountry')
     *
     * @return array
     */
    public function getLoadedPluginsName()
    {
        return array_map('get_class', $this->getLoadedPlugins());
    }

    /**
     * Returns an array of key,value with the following format: array(
     *        'UserCountry' => Piwik_Plugin $pluginObject,
     *        'UserSettings' => Piwik_Plugin $pluginObject,
     *    );
     *
     * @return array
     */
    public function getLoadedPlugins()
    {
        return $this->loadedPlugins;
    }

    /**
     * Returns the given Piwik_Plugin object
     *
     * @param string $name
     * @throws Exception
     * @return array
     */
    public function getLoadedPlugin($name)
    {
        if (!isset($this->loadedPlugins[$name])) {
            throw new Exception("The plugin '$name' has not been loaded.");
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
            if (!$this->isPluginLoaded($pluginName)) {
                $newPlugin = $this->loadPlugin($pluginName);
                if ($newPlugin === null) {
                    continue;
                }

                if ($this->doLoadPlugins
                    && $this->isPluginActivated($pluginName)
                ) {
                    $this->addPluginObservers($newPlugin);
                }
            }
        }
    }

    /**
     * Loads the plugin filename and instantiates the plugin with the given name, eg. UserCountry
     * Do NOT give the class name ie. Piwik_UserCountry, but give the plugin name ie. UserCountry
     *
     * @param string $pluginName
     * @throws Exception
     * @return Piwik_Plugin|null
     */
    public function loadPlugin($pluginName)
    {
        if (isset($this->loadedPlugins[$pluginName])) {
            return $this->loadedPlugins[$pluginName];
        }
        $pluginFileName = sprintf("%s/%s.php", $pluginName, $pluginName);
        $pluginClassName = sprintf('Piwik_%s', $pluginName);

        if (!Piwik_Common::isValidFilename($pluginName)) {
            throw new Exception(sprintf("The plugin filename '%s' is not a valid filename", $pluginFileName));
        }

        $path = PIWIK_INCLUDE_PATH . '/plugins/' . $pluginFileName;

        if (!file_exists($path)) {
            // ToDo: We should log this - but this will crash in Tracker mode since core/Piwik is not loaded
            //Piwik::log(sprintf("Unable to load plugin '%s' because '%s' couldn't be found.", $pluginName, $path));
            throw new Exception(sprintf("Unable to load plugin '%s' because '%s' couldn't be found.", $pluginName, $path));
        }

        // Don't remove this.
        // Our autoloader can't find plugins/PluginName/PluginName.php
        require_once $path; // prefixed by PIWIK_INCLUDE_PATH

        if (!class_exists($pluginClassName, false)) {
            throw new Exception("The class $pluginClassName couldn't be found in the file '$path'");
        }
        $newPlugin = new $pluginClassName();

        if (!($newPlugin instanceof Piwik_Plugin)) {
            throw new Exception("The plugin $pluginClassName in the file $path must inherit from Piwik_Plugin.");
        }

        $this->addLoadedPlugin($pluginName, $newPlugin);

        return $newPlugin;
    }

    /**
     * Unload plugin
     *
     * @param Piwik_Plugin $plugin
     * @throws Exception
     */
    public function unloadPlugin($plugin)
    {
        if (!($plugin instanceof Piwik_Plugin)) {
            $oPlugin = $this->loadPlugin($plugin);
            if ($oPlugin === null) {
                unset($this->loadedPlugins[$plugin]);
                return;
            }

            $plugin = $oPlugin;
        }
        $hooks = $plugin->getListHooksRegistered();

        foreach ($hooks as $hookName => $methodToCall) {
            $success = $this->dispatcher->removeObserver(array($plugin, $methodToCall), $hookName);
            if ($success !== true) {
                throw new Exception("Error unloading plugin = " . $plugin->getPluginName() . ", method = $methodToCall, hook = $hookName ");
            }
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
     * @param Piwik_Plugin $plugin
     * @throws Piwik_PluginsManager_PluginException if installation fails
     */
    private function installPlugin(Piwik_Plugin $plugin)
    {
        try {
            $plugin->install();
        } catch (Exception $e) {
            throw new Piwik_PluginsManager_PluginException($plugin->getPluginName(), $e->getMessage());
        }
    }


    /**
     * For the given plugin, add all the observers of this plugin.
     *
     * @param Piwik_Plugin $plugin
     */
    private function addPluginObservers(Piwik_Plugin $plugin)
    {
        $hooks = $plugin->getListHooksRegistered();

        foreach ($hooks as $hookName => $methodToCall) {
            $this->dispatcher->addObserver(array($plugin, $methodToCall), $hookName);
        }
    }

    /**
     * Add a plugin in the loaded plugins array
     *
     * @param string $pluginName  plugin name without prefix (eg. 'UserCountry')
     * @param Piwik_Plugin $newPlugin
     */
    private function addLoadedPlugin($pluginName, Piwik_Plugin $newPlugin)
    {
        $this->loadedPlugins[$pluginName] = $newPlugin;
    }

    /**
     * Load translation
     *
     * @param Piwik_Plugin $plugin
     * @param string $langCode
     * @throws Exception
     * @return void
     */
    private function loadTranslation($plugin, $langCode)
    {
        // we are in Tracker mode if Piwik_Loader is not (yet) loaded
        if (!class_exists('Piwik_Loader', false)) {
            return;
        }

        $infos = $plugin->getInformation();
        if (!isset($infos['translationAvailable'])) {
            $infos['translationAvailable'] = false;
        }
        $translationAvailable = $infos['translationAvailable'];

        if (!$translationAvailable) {
            return;
        }

        $pluginName = $plugin->getPluginName();

        $path = PIWIK_INCLUDE_PATH . '/plugins/' . $pluginName . '/lang/%s.php';

        $defaultLangPath = sprintf($path, $langCode);
        $defaultEnglishLangPath = sprintf($path, 'en');

        $translations = array();

        if (file_exists($defaultLangPath)) {
            require $defaultLangPath;
        } elseif (file_exists($defaultEnglishLangPath)) {
            require $defaultEnglishLangPath;
        } else {
            throw new Exception("Language file not found for the plugin '$pluginName'.");
        }
        Piwik_Translate::getInstance()->mergeTranslationArray($translations);
    }

    /**
     * Return names of installed plugins
     *
     * @return array
     */
    public function getInstalledPluginsName()
    {
        $pluginNames = Piwik_Config::getInstance()->PluginsInstalled['PluginsInstalled'];
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
        if (isset(Piwik_Config::getInstance()->Plugins['Plugins'])) {
            $plugins = Piwik_Config::getInstance()->Plugins['Plugins'];
            foreach ($plugins as $pluginName) {
                // if a plugin is listed in the config, but is not loaded, it does not exist in the folder
                if (!Piwik_PluginsManager::getInstance()->isPluginLoaded($pluginName)) {
                    $missingPlugins[] = $pluginName;
                }
            }
        }
        return $missingPlugins;
    }

    /**
     * Install a plugin, if necessary
     *
     * @param Piwik_Plugin $plugin
     */
    private function installPluginIfNecessary(Piwik_Plugin $plugin)
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

        $information = $plugin->getInformation();

        // if the plugin is to be loaded during the statistics logging
        if (isset($information['TrackerPlugin'])
            && $information['TrackerPlugin'] === true
        ) {
            $pluginsTracker = Piwik_Config::getInstance()->Plugins_Tracker['Plugins_Tracker'];
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
            Piwik_Config::getInstance()->forceSave();
        }
    }
}

/**
 * @package Piwik
 * @subpackage Piwik_PluginsManager
 */
class Piwik_PluginsManager_PluginException extends Exception
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

/**
 * Post an event to the dispatcher which will notice the observers
 *
 * @param string $eventName  The event name
 * @param mixed $object     Object, array or string that the listeners can read and/or modify.
 *                            Listeners can call $object =& $notification->getNotificationObject(); to fetch and then modify this variable.
 * @param array $info       Additional array of data that can be used by the listeners, but not edited
 * @param bool $pending    Should the notification be posted to plugins that register after the notification was sent?
 * @return void
 */
function Piwik_PostEvent($eventName, &$object = null, $info = array(), $pending = false)
{
    $notification = new Piwik_Event_Notification($object, $eventName, $info);
    Piwik_PluginsManager::getInstance()->dispatcher->postNotification($notification, $pending, $bubble = false);
}

/**
 * Register an action to execute for a given event
 *
 * @param string $hookName  Name of event
 * @param function $function  Callback hook
 */
function Piwik_AddAction($hookName, $function)
{
    Piwik_PluginsManager::getInstance()->dispatcher->addObserver($function, $hookName);
}

/**
 * Event notification
 *
 * @package Piwik
 *
 * @see Event_Notification, libs/Event/Notification.php
 * @link http://pear.php.net/package/Event_Dispatcher/docs/latest/Event_Dispatcher/Event_Notification.html
 */
class Piwik_Event_Notification extends Event_Notification
{
    static $showProfiler = false;

    /**
     * Use notification counter to profile runtime execution
     * time and memory usage.
     */
    function increaseNotificationCount( /* array($className|object, $method) */)
    {
        parent::increaseNotificationCount();
        if (self::$showProfiler && func_num_args() == 1) {
            $callback = func_get_arg(0);
            if (is_array($callback)) {
                $className = is_object($callback[0]) ? get_class($callback[0]) : $callback[0];
                $method = $callback[1];

                echo "after $className -> $method <br />";
                echo "-";
                Piwik::printTimer();
                echo "<br />";
                echo "-";
                Piwik::printMemoryLeak();
                echo "<br />";
            }
        }
    }
}
