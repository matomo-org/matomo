<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\CacheId;
use Piwik\Container\StaticContainer;
use Piwik\Plugin;
use Piwik\Cache as PiwikCache;
use Piwik\Settings\Measurable\MeasurableSettings;
use \Piwik\Settings\Plugin\UserSettings;
use \Piwik\Settings\Plugin\SystemSettings;

/**
 * Base class of all plugin settings providers. Plugins that define their own configuration settings
 * can extend this class to easily make their settings available to Piwik users.
 *
 * Descendants of this class should implement the {@link init()} method and call the
 * {@link addSetting()} method for each of the plugin's settings.
 *
 * For an example, see the {@link Piwik\Plugins\ExampleSettingsPlugin\ExampleSettingsPlugin} plugin.
 */
class SettingsProvider
{
    /**
     * @var Plugin\Manager
     */
    private $pluginManager;

    public function __construct(Plugin\Manager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    /**
     *
     * Get user settings implemented by a specific plugin (if implemented by this plugin).
     * @param string $pluginName
     * @return SystemSettings|null
     */
    public function getSystemSettings($pluginName)
    {
        $plugin = $this->getLoadedAndActivated($pluginName);

        if ($plugin) {
            $settings = $plugin->findComponent('SystemSettings', 'Piwik\\Settings\\Plugin\\SystemSettings');

            if ($settings) {
                return StaticContainer::get($settings);
            }
        }
    }

    /**
     * Get user settings implemented by a specific plugin (if implemented by this plugin).
     * @param string $pluginName
     * @return UserSettings|null
     */
    public function getUserSettings($pluginName)
    {
        $plugin = $this->getLoadedAndActivated($pluginName);

        if ($plugin) {
            $settings = $plugin->findComponent('UserSettings', 'Piwik\\Settings\\Plugin\\UserSettings');

            if ($settings) {
                return StaticContainer::get($settings);
            }
        }
    }

    /**
     * Returns all available system settings. A plugin has to specify a file named `SystemSettings.php` containing a
     * class named `SystemSettings` that extends `Piwik\Settings\Plugin\SystemSettings` in order to be considered as
     * a system setting. Otherwise the settings for a plugin won't be available.
     *
     * @return SystemSettings[]   An array containing array([pluginName] => [setting instance]).
     */
    public function getAllSystemSettings()
    {
        $cacheId = CacheId::languageAware('AllSystemSettings');
        $cache = PiwikCache::getTransientCache();

        if (!$cache->contains($cacheId)) {
            $pluginNames = $this->pluginManager->getActivatedPlugins();
            $byPluginName = array();

            foreach ($pluginNames as $plugin) {
                $component = $this->getSystemSettings($plugin);

                if (!empty($component)) {
                    $byPluginName[$plugin] = $component;
                }
            }

            $cache->save($cacheId, $byPluginName);
        }

        return $cache->fetch($cacheId);
    }

    /**
     * Returns all available user settings. A plugin has to specify a file named `UserSettings.php` containing a class
     * named `UserSettings` that extends `Piwik\Settings\Plugin\UserSettings` in order to be considered as a plugin
     * setting. Otherwise the settings for a plugin won't be available.
     *
     * @return UserSettings[]   An array containing array([pluginName] => [setting instance]).
     */
    public function getAllUserSettings()
    {
        $cacheId = CacheId::languageAware('AllUserSettings');
        $cache = PiwikCache::getTransientCache();

        if (!$cache->contains($cacheId)) {
            $pluginNames = $this->pluginManager->getActivatedPlugins();
            $byPluginName = array();

            foreach ($pluginNames as $plugin) {
                $component = $this->getUserSettings($plugin);

                if (!empty($component)) {
                    $byPluginName[$plugin] = $component;
                }
            }

            $cache->save($cacheId, $byPluginName);
        }

        return $cache->fetch($cacheId);
    }

    /**
     * @api
     *
     * Get measurable settings for a specific plugin.
     *
     * @param string $pluginName    The name of a plugin.
     * @param int $idSite           The ID of a site. If a site is about to be created pass idSite = 0.
     * @param string|null $idType   If null, idType will be detected automatically if the site already exists. Only
     *                              needed to set a value when idSite = 0 (this is the case when a site is about)
     *                              to be created.
     *
     * @return MeasurableSettings|null  Returns null if no MeasurableSettings implemented by this plugin or when plugin
     *                                  is not loaded and activated. Returns an instance of the settings otherwise.
     */
    public function getMeasurableSettings($pluginName, $idSite, $idType = null)
    {
        $plugin = $this->getLoadedAndActivated($pluginName);

        if ($plugin) {
            $component = $plugin->findComponent('MeasurableSettings', 'Piwik\\Settings\\Measurable\\MeasurableSettings');

            if ($component) {
                return StaticContainer::getContainer()->make($component, array(
                    'idSite' => $idSite,
                    'idMeasurableType' => $idType
                ));
            }
        }
    }

    /**
     * @api
     *
     * Get all available measurable settings implemented by loaded and activated plugins.
     *
     * @param int $idSite           The ID of a site. If a site is about to be created pass idSite = 0.
     * @param string|null $idMeasurableType   If null, idType will be detected automatically if the site already exists.
     *                                        Only needed to set a value when idSite = 0 (this is the case when a site
     *                                        is about) to be created.
     *
     * @return MeasurableSettings[]
     */
    public function getAllMeasurableSettings($idSite, $idMeasurableType = null)
    {
        $pluginNames = $this->pluginManager->getActivatedPlugins();
        $byPluginName = array();

        foreach ($pluginNames as $plugin) {
            $component = $this->getMeasurableSettings($plugin, $idSite, $idMeasurableType);

            if (!empty($component)) {
                $byPluginName[$plugin] = $component;
            }
        }

        return $byPluginName;
    }

    private function getLoadedAndActivated($pluginName)
    {
        if (!$this->pluginManager->isPluginLoaded($pluginName)) {
            return;
        }

        try {
            if (!$this->pluginManager->isPluginActivated($pluginName)) {
                return;
            }

            $plugin = $this->pluginManager->getLoadedPlugin($pluginName);
        } catch (\Exception $e) {
            // we are not allowed to use possible settings from this plugin, plugin is not active
            return;
        }

        return $plugin;
    }

}
