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

namespace Piwik\Settings;

/**
 * Settings manager.
 *
 * @package Piwik
 * @subpackage Settings
 */
class Manager
{
    private static $settings = array();

    /**
     * Returns all available plugin settings. A plugin has to specify a file named `settings.php` containing a class
     * named `Settings` that extends `Piwik\Plugin\Settings` in order to be considered as a plugin setting. Otherwise
     * the settings for a plugin won't be available.
     *
     * @return \Piwik\Plugin\Settings[]   An array containing array([pluginName] => [setting instance]).
     */
    public static function getAllPluginSettings()
    {
        if (empty(static::$settings)) {

            $settings = array();

            $pluginNames = \Piwik\Plugin\Manager::getInstance()->getLoadedPluginsName();
            foreach ($pluginNames as $pluginName) {
                $settings[$pluginName] = self::getPluginSettingsClass($pluginName);
            }

            static::$settings = array_filter($settings);
        }

        return static::$settings;
    }

    /**
     * Removes all settings made for a specific plugin. Useful while uninstalling a plugin.
     *
     * @param string $pluginName
     */
    public static function cleanupPluginSettings($pluginName)
    {
        $settings = self::getPluginSettingsClass($pluginName);

        if (!empty($settings)) {
            $settings->removeAllPluginSettings();
        }
    }

    /**
     * Gets all plugins settings that have at least one settings a user is allowed to change.
     *
     * @return \Piwik\Plugin\Settings[]   An array containing array([pluginName] => [setting instance]).
     */
    public static function getPluginSettingsForCurrentUser()
    {
        $settings = static::getAllPluginSettings();

        $settingsForUser = array();
        foreach ($settings as $pluginName => $setting) {
            $forUser = $setting->getSettingsForCurrentUser();
            if (!empty($forUser)) {
                $settingsForUser[$pluginName] = $setting;
            }
        }

        return $settingsForUser;
    }

    /**
     * Detects whether there are plugin settings available that the current user can change.
     *
     * @return bool
     */
    public static function hasPluginSettingsForCurrentUser()
    {
        $settings = static::getPluginSettingsForCurrentUser();

        return !empty($settings);
    }

    /**
     * Tries to find a settings class for the specified plugin name. Returns null in case the plugin does not specify
     * any settings, an instance of the settings class otherwise.
     *
     * @param string $pluginName
     * @return \Piwik\Plugin\Settings|null
     */
    private static function getPluginSettingsClass($pluginName)
    {
        $klassName = 'Piwik\\Plugins\\' . $pluginName . '\\Settings';

        if (class_exists($klassName) && is_subclass_of($klassName, 'Piwik\\Plugin\\Settings')) {
            return new $klassName($pluginName);
        }
    }

}
