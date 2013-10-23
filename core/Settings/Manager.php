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
 * Settings manager
 *
 * @package Piwik
 * @subpackage Manager
 */
class Manager
{
    private static $settings = array();

    /**
     * @return \Piwik\Plugin\Settings[]
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

    public static function cleanupPluginSettings($pluginName)
    {
        $settings = self::getPluginSettingsClass($pluginName);

        if (!empty($settings)) {
            $settings->removeAllPluginSettings();
        }
    }

    public static function cleanupUserSettings($userLogin)
    {
        foreach (static::getAllPluginSettings() as $setting) {
            $setting->removeAllSettingsForUser($userLogin);
        }
    }

    /**
     * @return bool
     */
    public static function hasPluginSettingsForCurrentUser()
    {
        $settings = static::getAllPluginSettings();

        foreach ($settings as $setting) {
            $forUser = $setting->getSettingsForCurrentUser();
            if (!empty($forUser)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $pluginName
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
