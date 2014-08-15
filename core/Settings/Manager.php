<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings;

use Piwik\Plugin\Manager as PluginManager;

/**
 * Settings manager.
 *
 */
class Manager
{
    private static $settings = array();
    private static $numPluginsChecked = 0;

    /**
     * Returns all available plugin settings, even settings for inactive plugins. A plugin has to specify a file named
     * `Settings.php` containing a class named `Settings` that extends `Piwik\Plugin\Settings` in order to be
     * considered as a plugin setting. Otherwise the settings for a plugin won't be available.
     *
     * @return \Piwik\Plugin\Settings[]   An array containing array([pluginName] => [setting instance]).
     */
    public static function getAllPluginSettings()
    {
        $numActivatedPlugins = PluginManager::getInstance()->getNumberOfActivatedPlugins();

        if (static::$numPluginsChecked != $numActivatedPlugins) {
            static::$numPluginsChecked = $numActivatedPlugins;
            static::$settings = array();
        }

        if (empty(static::$settings)) {

            $settings = PluginManager::getInstance()->findComponents('Settings', 'Piwik\\Plugin\\Settings');
            $byPluginName = array();

            foreach ($settings as $setting) {
                $byPluginName[$setting->getPluginName()] = $setting;
            }

            static::$settings = $byPluginName;
        }

        return static::$settings;
    }

    private static function isActivatedPlugin($pluginName)
    {
        return PluginManager::getInstance()->isPluginActivated($pluginName);
    }

    /**
     * Removes all settings made for a specific plugin. Useful while uninstalling a plugin.
     *
     * @param string $pluginName
     */
    public static function cleanupPluginSettings($pluginName)
    {
        $pluginManager = PluginManager::getInstance();

        if (!$pluginManager->isPluginLoaded($pluginName)) {
            return;
        }

        $plugin   = $pluginManager->loadPlugin($pluginName);
        $settings = $plugin->findComponent('Settings', 'Piwik\\Plugin\\Settings');

        if (!empty($settings)) {
            $settings->removeAllPluginSettings();
        }
    }

    /**
     * Gets all plugins settings that have at least one settings a user is allowed to change. Only the settings for
     * activated plugins are returned.
     *
     * @return \Piwik\Plugin\Settings[]   An array containing array([pluginName] => [setting instance]).
     */
    public static function getPluginSettingsForCurrentUser()
    {
        $settings = static::getAllPluginSettings();

        $settingsForUser = array();
        foreach ($settings as $pluginName => $setting) {
            if (!static::isActivatedPlugin($pluginName)) {
                continue;
            }

            $forUser = $setting->getSettingsForCurrentUser();
            if (!empty($forUser)) {
                $settingsForUser[$pluginName] = $setting;
            }
        }

        return $settingsForUser;
    }

    public static function hasPluginSettingsForCurrentUser($pluginName)
    {
        $pluginNames = array_keys(static::getPluginSettingsForCurrentUser());

        return in_array($pluginName, $pluginNames);
    }

    /**
     * Detects whether there are settings for activated plugins available that the current user can change.
     *
     * @return bool
     */
    public static function hasPluginsSettingsForCurrentUser()
    {
        $settings = static::getPluginSettingsForCurrentUser();

        return !empty($settings);
    }

}
